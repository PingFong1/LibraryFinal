<?php
require_once '../config/Database.php';
require_once '../controller/Session.php';
require_once '../controller/ActivityLogController.php';

class BorrowingController {
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function borrowResource($userId, $resourceId) {
        try {
            $this->conn->beginTransaction();
            
            // Get user's borrowing days limit
            $stmt = $this->conn->prepare("SELECT borrowing_days_limit FROM users WHERE user_id = :user_id");
            $stmt->bindParam(':user_id', $userId);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $borrowingDaysLimit = $user['borrowing_days_limit'] ?? 7; // Default to 7 if not set
            
            // Calculate due date based on user's borrowing days limit
            $dueDate = date('Y-m-d', strtotime("+{$borrowingDaysLimit} days"));
            
            // Check if resource is available
            $stmt = $this->conn->prepare("SELECT status FROM library_resources WHERE resource_id = :resource_id");
            $stmt->bindParam(":resource_id", $resourceId);
            $stmt->execute();
            $resource = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($resource['status'] !== 'available') {
                $this->conn->rollBack();
                return [
                    'success' => false,
                    'message' => 'This resource is not available for borrowing.'
                ];
            }

            // Check user's current active borrowings against their limit
            $stmt = $this->conn->prepare("
                SELECT 
                    COUNT(b.borrowing_id) as active_borrowings,
                    u.max_books
                FROM users u
                LEFT JOIN borrowings b ON u.user_id = b.user_id 
                AND b.status IN ('active', 'overdue', 'pending')
                WHERE u.user_id = :user_id
                GROUP BY u.user_id, u.max_books
            ");
            $stmt->bindParam(":user_id", $userId);
            $stmt->execute();
            $borrowingInfo = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($borrowingInfo['active_borrowings'] >= $borrowingInfo['max_books']) {
                $this->conn->rollBack();
                return [
                    'success' => false,
                    'message' => 'You have reached your maximum borrowing limit of ' . $borrowingInfo['max_books'] . ' items.'
                ];
            }
            
            // Create pending borrowing request
            $stmt = $this->conn->prepare("INSERT INTO borrowings (user_id, resource_id, status) 
                                        VALUES (:user_id, :resource_id, 'pending')");
            $stmt->bindParam(":user_id", $userId);
            $stmt->bindParam(":resource_id", $resourceId);
            
            if ($stmt->execute()) {
                // Update resource status to pending
                $stmt = $this->conn->prepare("UPDATE library_resources 
                                            SET status = 'pending' 
                                            WHERE resource_id = :resource_id");
                $stmt->bindParam(":resource_id", $resourceId);
                $stmt->execute();
                
                $this->conn->commit();
                return [
                    'success' => true,
                    'message' => 'Resource borrowing request submitted successfully.'
                ];
            }
            
            $this->conn->rollBack();
            return [
                'success' => false,
                'message' => 'Failed to submit borrowing request.'
            ];
        } catch(PDOException $e) {
            $this->conn->rollBack();
            error_log("Borrow resource error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'An error occurred while processing your request.'
            ];
        }
    }

    public function approveBorrowing($borrowingId) {
        try {
            $this->conn->beginTransaction();

            // Get borrowing and user details
            $stmt = $this->conn->prepare("
                SELECT b.*, u.borrowing_days_limit 
                FROM borrowings b
                JOIN users u ON b.user_id = u.user_id
                WHERE b.borrowing_id = :borrowing_id
            ");
            $stmt->bindParam(':borrowing_id', $borrowingId);
            $stmt->execute();
            $borrowing = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$borrowing) {
                throw new Exception("Borrowing record not found");
            }

            // Use user's borrowing_days_limit or default to 7 days
            $borrowingDaysLimit = $borrowing['borrowing_days_limit'] ?? 7;
            
            // Calculate due date based on user's borrowing days limit
            $dueDate = date('Y-m-d H:i:s', strtotime("+{$borrowingDaysLimit} days"));

            // Update borrowing record
            $updateStmt = $this->conn->prepare("
                UPDATE borrowings 
                SET status = 'active',
                    due_date = :due_date,
                    approved_by = :approved_by,
                    approved_at = CURRENT_TIMESTAMP
                WHERE borrowing_id = :borrowing_id
            ");

            $updateStmt->bindParam(':due_date', $dueDate);
            $updateStmt->bindParam(':approved_by', $_SESSION['user_id']);
            $updateStmt->bindParam(':borrowing_id', $borrowingId);
            $updateStmt->execute();

            // Update resource status
            $updateResourceStmt = $this->conn->prepare("
                UPDATE library_resources 
                SET status = 'borrowed' 
                WHERE resource_id = :resource_id
            ");
            $updateResourceStmt->bindParam(':resource_id', $borrowing['resource_id']);
            $updateResourceStmt->execute();

            // Log the activity
            $activityLogger = new ActivityLogController();
            
            // Get resource and user details for logging
            $stmt = $this->conn->prepare("
                SELECT lr.title, u.first_name, u.last_name, u.membership_id 
                FROM borrowings b
                JOIN library_resources lr ON b.resource_id = lr.resource_id
                JOIN users u ON b.user_id = u.user_id
                WHERE b.borrowing_id = :borrowing_id
            ");
            $stmt->bindParam(':borrowing_id', $borrowingId);
            $stmt->execute();
            $details = $stmt->fetch(PDO::FETCH_ASSOC);

            $description = sprintf(
                "Approved borrowing request - Resource: %s, Borrower: %s %s (ID: %s), Due Date: %s",
                $details['title'],
                $details['first_name'],
                $details['last_name'],
                $details['membership_id'],
                $dueDate
            );

            $activityLogger->logActivity($_SESSION['user_id'], 'approve_borrowing', $description);

            $this->conn->commit();
            return [
                'success' => true,
                'due_date' => $dueDate
            ];
        } catch (Exception $e) {
            $this->conn->rollBack();
            error_log("Error in approving borrowing: " . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    public function returnResource($borrowing_id) {
        try {
            $this->conn->beginTransaction();

            // Get borrowing details with resource type
            $borrow_query = "SELECT b.*, lr.category as resource_type 
                            FROM borrowings b
                            JOIN library_resources lr ON b.resource_id = lr.resource_id
                            WHERE b.borrowing_id = :borrowing_id 
                            AND b.status IN ('active', 'overdue')";
            $stmt = $this->conn->prepare($borrow_query);
            $stmt->bindParam(":borrowing_id", $borrowing_id);
            $stmt->execute();
            $borrowing = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$borrowing) {
                throw new Exception("Borrowing record not found");
            }

            // Get fine configuration
            $fine_query = "SELECT fine_amount 
                          FROM fine_configurations 
                          WHERE resource_type = :resource_type";
            $stmt = $this->conn->prepare($fine_query);
            $stmt->bindParam(":resource_type", $borrowing['resource_type']);
            $stmt->execute();
            $fine_config = $stmt->fetch(PDO::FETCH_ASSOC);

            // Debug log
            error_log("Resource Type: " . $borrowing['resource_type']);
            error_log("Fine Config: " . print_r($fine_config, true));

            $fine_rate = $fine_config ? $fine_config['fine_amount'] : 1.00;
            
            // Calculate fine if overdue
            $current_date = date('Y-m-d H:i:s');
            $fine_amount = 0;

            if (strtotime($current_date) > strtotime($borrowing['due_date'])) {
                $overdue_days = ceil((strtotime($current_date) - strtotime($borrowing['due_date'])) / (60 * 60 * 24));
                $fine_amount = $overdue_days * $fine_rate;
                error_log("Overdue days: $overdue_days, Fine rate: $fine_rate, Total fine: $fine_amount");
            }

            // Update borrowing record
            $return_query = "UPDATE borrowings 
                             SET return_date = :return_date, 
                                 status = 'returned', 
                                 fine_amount = :fine_amount,
                                 returned_by = :staff_id
                             WHERE borrowing_id = :borrowing_id";
            $stmt = $this->conn->prepare($return_query);
            $stmt->bindParam(":return_date", $current_date);
            $stmt->bindParam(":fine_amount", $fine_amount);
            $stmt->bindParam(":borrowing_id", $borrowing_id);
            $stmt->bindParam(":staff_id", $_SESSION['user_id']);
            $stmt->execute();
    
            // Update resource status back to available
            $update_query = "UPDATE library_resources 
                             SET status = 'available' 
                             WHERE resource_id = :resource_id";
            $stmt = $this->conn->prepare($update_query);
            $stmt->bindParam(":resource_id", $borrowing['resource_id']);
            $stmt->execute();
    
            // Commit transaction
            $this->conn->commit();
    
            return [
                'success' => true,
                'fine_amount' => $fine_amount,
                'return_date' => $current_date
            ];
        } catch (Exception $e) {
            // Rollback transaction
            $this->conn->rollBack();
            error_log("Return resource error: " . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    public function getAllBorrowings() {
        try {
            $conn = (new Database())->getConnection();
            $query = "SELECT 
                        b.borrowing_id, 
                        b.borrow_date, 
                        b.due_date, 
                        b.status,
                        b.fine_amount,
                        b.approved_at,
                        u.user_id,
                        u.first_name, 
                        u.last_name, 
                        u.email, 
                        u.role,
                        lr.title AS resource_title,
                        lr.category AS resource_type,
                        CONCAT(u_staff.first_name, ' ', u_staff.last_name) as approved_by,
                        u_staff.role as approver_role
                    FROM borrowings b
                    JOIN users u ON b.user_id = u.user_id
                    JOIN library_resources lr ON b.resource_id = lr.resource_id
                    LEFT JOIN users u_staff ON b.approved_by = u_staff.user_id
                    WHERE b.status IN ('active', 'overdue')
                    ORDER BY b.due_date ASC";
            
            $stmt = $conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Borrowing monitoring error: " . $e->getMessage());
            return [];
        }
    }

    public function calculateOverdueStatus($dueDate) {
        $now = new DateTime();
        $due = new DateTime($dueDate);
        
        if ($now > $due) {
            $interval = $now->diff($due);
            return [
                'status' => 'Overdue',
                'days_overdue' => $interval->days,
                'class' => 'text-danger'
            ];
        }
        
        $interval = $due->diff($now);
        if ($interval->days <= 3) {
            return [
                'status' => 'Due Soon',
                'days_remaining' => $interval->days,
                'class' => 'text-warning'
            ];
        }
        
        return [
            'status' => 'Active',
            'days_remaining' => $interval->days,
            'class' => 'text-success'
        ];
    }

    public function getUserBorrowingHistory($user_id) {
        try {
            $query = "SELECT 
                        b.borrowing_id, 
                        lr.title, 
                        b.borrow_date, 
                        b.due_date, 
                        b.return_date, 
                        b.status, 
                        b.fine_amount
                      FROM borrowings b
                      JOIN library_resources lr ON b.resource_id = lr.resource_id
                      WHERE b.user_id = :user_id
                      ORDER BY b.borrow_date DESC";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":user_id", $user_id);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get borrowing history error: " . $e->getMessage());
            return [];
        }
    }

    public function getAvailableBooks($type = 'book') {
        try {
            $query = "SELECT lr.*, b.* 
                      FROM library_resources lr
                      JOIN books b ON lr.resource_id = b.resource_id
                      WHERE lr.status = 'available'
                      ORDER BY lr.created_at DESC";
            
            $stmt = $this->conn->prepare($query);
            
            $stmt->execute();
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return $results;
        } catch (PDOException $e) {
            error_log("Get available resources error: " . $e->getMessage());
            return [];
        }
    }

    public function getAvailableMedia($type = 'media') {
        try {
            $query = "SELECT lr.*, mr.* 
                      FROM library_resources lr
                      JOIN media_resources mr ON lr.resource_id = mr.resource_id
                      WHERE lr.status = 'available'
                      ORDER BY lr.created_at DESC";
            
            $stmt = $this->conn->prepare($query);
            
            $stmt->execute();
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return $results;
        } catch (PDOException $e) {
            error_log("Get available media error: " . $e->getMessage());
            return [];
        }
    }
    
    public function getAvailablePeriodicals($type = 'periodical') {
        try {
            $query = "SELECT lr.*, p.* 
                      FROM library_resources lr
                      JOIN periodicals p ON lr.resource_id = p.resource_id
                      WHERE lr.status = 'available'
                      ORDER BY lr.created_at DESC";
            
            $stmt = $this->conn->prepare($query);
            
            $stmt->execute();
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return $results;
        } catch (PDOException $e) {
            error_log("Get available periodicals error: " . $e->getMessage());
            return [];
        }
    }

    public function getMonthlyBorrowings($year) {
        try {
            $query = "SELECT MONTH(borrow_date) as month, COUNT(*) as borrow_count
                      FROM borrowings
                      WHERE YEAR(borrow_date) = :year
                      GROUP BY MONTH(borrow_date)";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":year", $year, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get monthly borrowings error: " . $e->getMessage());
            return [];
        }
    }
    public function getOverdueBorrowings() {
        try {
            $query = "SELECT 
                        b.borrowing_id, 
                        b.borrow_date, 
                        b.due_date, 
                        u.first_name, 
                        u.last_name, 
                        u.email, 
                        u.role,
                        lr.title AS resource_title,
                        lr.category AS resource_type,
                        DATEDIFF(CURRENT_DATE, b.due_date) as days_overdue,
                        fc.fine_amount as daily_fine_rate,
                        DATEDIFF(CURRENT_DATE, b.due_date) * fc.fine_amount as fine_amount
                    FROM borrowings b
                    JOIN users u ON b.user_id = u.user_id
                    JOIN library_resources lr ON b.resource_id = lr.resource_id
                    JOIN fine_configurations fc ON lr.category = fc.resource_type
                    WHERE b.status = 'overdue' 
                        OR (b.status = 'active' AND b.due_date < CURRENT_DATE)
                    ORDER BY b.due_date ASC";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get overdue borrowings error: " . $e->getMessage());
            return [];
        }
    }

    // Add new method to manage fine configurations
    public function updateFineConfiguration($resource_type, $fine_amount) {
        try {
            // Convert resource type to lowercase for consistency
            $resource_type = strtolower($resource_type);
            
            // First check if the configuration exists
            $check_query = "SELECT * FROM fine_configurations WHERE resource_type = :resource_type";
            $stmt = $this->conn->prepare($check_query);
            $stmt->bindParam(":resource_type", $resource_type);
            $stmt->execute();
            
            if ($stmt->rowCount() > 0) {
                // Update existing configuration
                $query = "UPDATE fine_configurations 
                         SET fine_amount = :fine_amount 
                         WHERE resource_type = :resource_type";
            } else {
                // Insert new configuration
                $query = "INSERT INTO fine_configurations (resource_type, fine_amount) 
                         VALUES (:resource_type, :fine_amount)";
            }
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":resource_type", $resource_type);
            $stmt->bindParam(":fine_amount", $fine_amount);
            
            $success = $stmt->execute();
            
            // Debug log
            error_log("Updating fine configuration - Type: $resource_type, Amount: $fine_amount, Success: " . ($success ? 'true' : 'false'));
            
            return $success;
        } catch (Exception $e) {
            error_log("Update fine configuration error: " . $e->getMessage());
            return false;
        }
    }

    public function getFineConfigurations() {
        try {
            $query = "SELECT * FROM fine_configurations ORDER BY resource_type";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Get fine configurations error: " . $e->getMessage());
            return [];
        }
    }

    public function getPendingBorrowings() {
        try {
            $query = "SELECT 
                        b.borrowing_id,
                        b.borrow_date,
                        u.first_name,
                        u.last_name,
                        u.membership_id,
                        u.role as user_role,
                        lr.title as resource_title,
                        lr.accession_number,
                        lr.category as resource_type
                    FROM borrowings b
                    JOIN users u ON b.user_id = u.user_id
                    JOIN library_resources lr ON b.resource_id = lr.resource_id
                    WHERE b.status = 'pending'
                    ORDER BY b.borrow_date ASC";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get pending borrowings error: " . $e->getMessage());
            return [];
        }
    }

    public function getRecentApprovals() {
        try {
            $query = "SELECT 
                        b.borrowing_id,
                        b.approved_at,
                        b.due_date,
                        u_borrower.membership_id,
                        CONCAT(u_borrower.first_name, ' ', u_borrower.last_name) as borrower_name,
                        lr.title as resource_title,
                        lr.accession_number,
                        CONCAT(u_staff.first_name, ' ', u_staff.last_name) as staff_name,
                        u_staff.role as staff_role
                    FROM borrowings b
                    JOIN users u_borrower ON b.user_id = u_borrower.user_id
                    JOIN users u_staff ON b.approved_by = u_staff.user_id
                    JOIN library_resources lr ON b.resource_id = lr.resource_id
                    WHERE b.status = 'active'
                    AND b.approved_at IS NOT NULL
                    ORDER BY b.approved_at DESC
                    LIMIT 10";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get recent approvals error: " . $e->getMessage());
            return [];
        }
    }

    // Add this new method to get all approved borrowings for audit
    public function getApprovedBorrowings() {
        try {
            $query = "SELECT 
                        b.borrowing_id, 
                        b.borrow_date, 
                        b.due_date,
                        b.return_date,
                        b.status,
                        b.approved_at,
                        u.first_name, 
                        u.last_name, 
                        u.email, 
                        u.role,
                        lr.title AS resource_title,
                        lr.category AS resource_type,
                        CONCAT(u_staff.first_name, ' ', u_staff.last_name) as approved_by,
                        u_staff.role as approver_role,
                        CONCAT(u_returner.first_name, ' ', u_returner.last_name) as returned_by,
                        u_returner.role as returner_role
                    FROM borrowings b
                    JOIN users u ON b.user_id = u.user_id
                    JOIN library_resources lr ON b.resource_id = lr.resource_id
                    LEFT JOIN users u_staff ON b.approved_by = u_staff.user_id
                    LEFT JOIN users u_returner ON b.returned_by = u_returner.user_id
                    WHERE b.approved_by IS NOT NULL
                    ORDER BY b.approved_at DESC";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get approved borrowings error: " . $e->getMessage());
            return [];
        }
    }

    public function getAvailableAndPendingBooks($userId) {
        try {
            $query = "SELECT DISTINCT lr.*, bb.*,
                      CASE WHEN b.status = 'pending' AND b.user_id = :user_id THEN 1 ELSE 0 END as pending
                      FROM library_resources lr
                      LEFT JOIN books bb ON lr.resource_id = bb.resource_id
                      LEFT JOIN (
                          SELECT resource_id, status, user_id 
                          FROM borrowings 
                          WHERE user_id = :user_id AND status = 'pending'
                      ) b ON lr.resource_id = b.resource_id
                      WHERE lr.category = 'book' 
                      AND (lr.status = 'available' 
                          OR (b.status = 'pending' AND b.user_id = :user_id))
                      GROUP BY lr.resource_id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $userId);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get available and pending books error: " . $e->getMessage());
            return [];
        }
    }

    public function getAvailableAndPendingMedia($userId) {
        try {
            $query = "SELECT DISTINCT lr.*, mr.*,
                      CASE WHEN b.status = 'pending' AND b.user_id = :user_id THEN 1 ELSE 0 END as pending
                      FROM library_resources lr
                      LEFT JOIN media_resources mr ON lr.resource_id = mr.resource_id
                      LEFT JOIN (
                          SELECT resource_id, status, user_id 
                          FROM borrowings 
                          WHERE user_id = :user_id AND status = 'pending'
                      ) b ON lr.resource_id = b.resource_id
                      WHERE lr.category = 'media' 
                      AND (lr.status = 'available' 
                          OR (b.status = 'pending' AND b.user_id = :user_id))
                      GROUP BY lr.resource_id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $userId);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get available and pending media error: " . $e->getMessage());
            return [];
        }
    }

    public function getAvailableAndPendingPeriodicals($userId) {
        try {
            $query = "SELECT DISTINCT lr.*, p.*,
                      CASE WHEN b.status = 'pending' AND b.user_id = :user_id THEN 1 ELSE 0 END as pending
                      FROM library_resources lr
                      LEFT JOIN periodicals p ON lr.resource_id = p.resource_id
                      LEFT JOIN (
                          SELECT resource_id, status, user_id 
                          FROM borrowings 
                          WHERE user_id = :user_id AND status = 'pending'
                      ) b ON lr.resource_id = b.resource_id
                      WHERE lr.category = 'periodical' 
                      AND (lr.status = 'available' 
                          OR (b.status = 'pending' AND b.user_id = :user_id))
                      GROUP BY lr.resource_id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $userId);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get available and pending periodicals error: " . $e->getMessage());
            return [];
        }
    }

    public function displayBorrowingHistory($user_id) {
        try {
            // Modified query to include overdue calculation
            $query = "SELECT 
                        b.borrowing_id,
                        lr.title,
                        b.borrow_date,
                        b.due_date,
                        b.return_date,
                        b.status,
                        b.fine_amount,
                        CASE 
                            WHEN b.return_date IS NULL AND CURRENT_DATE > b.due_date THEN 'overdue'
                            WHEN b.return_date IS NOT NULL THEN 'returned'
                            ELSE 'active'
                        END as current_status,
                        CASE 
                            WHEN b.return_date IS NULL AND CURRENT_DATE > b.due_date 
                            THEN DATEDIFF(CURRENT_DATE, b.due_date)
                            ELSE 0
                        END as days_overdue,
                        fc.fine_amount as daily_fine_rate,
                        CASE 
                            WHEN b.return_date IS NULL AND CURRENT_DATE > b.due_date 
                            THEN DATEDIFF(CURRENT_DATE, b.due_date) * fc.fine_amount
                            ELSE b.fine_amount
                        END as calculated_fine
                    FROM borrowings b
                    JOIN library_resources lr ON b.resource_id = lr.resource_id
                    JOIN fine_configurations fc ON lr.category = fc.resource_type
                    WHERE b.user_id = :user_id
                    ORDER BY b.borrow_date DESC";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Error in borrowing history: " . $e->getMessage());
            return false;
        }
    }
}
