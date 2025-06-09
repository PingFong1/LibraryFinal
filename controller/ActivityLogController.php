<?php
require_once '../config/Database.php';

class ActivityLogController {
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function logActivity($userId, $actionType, $actionDescription) {
        try {
            $query = "INSERT INTO activity_logs (user_id, action_type, action_description, ip_address) 
                      VALUES (:user_id, :action_type, :action_description, :ip_address)";
            
            $stmt = $this->conn->prepare($query);
            
            $ipAddress = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
            
            $stmt->bindParam(":user_id", $userId);
            $stmt->bindParam(":action_type", $actionType);
            $stmt->bindParam(":action_description", $actionDescription);
            $stmt->bindParam(":ip_address", $ipAddress);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Log activity error: " . $e->getMessage());
            return false;
        }
    }

    public function logUserUpdate($adminId, $targetUserId, $changes) {
        try {
            $stmt = $this->conn->prepare("SELECT username FROM users WHERE user_id = :user_id");
            $stmt->bindParam(":user_id", $targetUserId);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $description = "Account details updated for user: " . $user['username'];
            
            return $this->logActivity($adminId, 'update', $description);
        } catch (PDOException $e) {
            error_log("Log user update error: " . $e->getMessage());
            return false;
        }
    }

    public function getLogs($limit = 100) {
        try {
            $query = "
                SELECT 
                    'activity' as log_type,
                    al.timestamp as time,
                    al.user_id,
                    u.username,
                    al.action_type,
                    al.action_description,
                    al.ip_address,
                    NULL as attempt_count
                FROM activity_logs al 
                LEFT JOIN users u ON al.user_id = u.user_id 
                
                UNION ALL
                
                SELECT 
                    'mfa' as log_type,
                    mvl.verification_time as time,
                    mvl.user_id,
                    u.username,
                    CASE 
                        WHEN mvl.success = 1 THEN 'mfa_success'
                        ELSE 'mfa_failed'
                    END as action_type,
                    CASE 
                        WHEN mvl.success = 1 THEN 'MFA verification successful'
                        ELSE 'MFA verification failed'
                    END as action_description,
                    mvl.ip_address,
                    mvl.attempt_count
                FROM mfa_verification_logs mvl
                JOIN users u ON mvl.user_id = u.user_id
                
                ORDER BY time DESC 
                LIMIT :limit";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get logs error: " . $e->getMessage());
            return [];
        }
    }
} 