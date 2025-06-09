<?php
require_once '../controller/Session.php';
require_once '../config/Database.php';

// Start the session and check login status
Session::start();

// Restrict access to admin and staff
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'staff')) {
    header("Location: ../login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $borrowing_id = $_POST['borrowing_id'] ?? '';
    $calculated_fine = $_POST['calculated_fine'] ?? 0;
    $amount_paid = $_POST['amount_paid'] ?? 0;
    $cash_received = $_POST['cash_received'] ?? 0;
    $change_amount = $cash_received - $amount_paid;
    $payment_notes = $_POST['payment_notes'] ?? '';

    if (empty($borrowing_id) || empty($amount_paid) || empty($cash_received)) {
        $_SESSION['error'] = "Missing required information";
        header("Location: overdue-management.php");
        exit();
    }

    if ($cash_received < $amount_paid) {
        $_SESSION['error'] = "Insufficient cash received";
        header("Location: overdue-management.php");
        exit();
    }

    try {
        $conn = (new Database())->getConnection();
        
        // Start transaction
        $conn->beginTransaction();

        // Insert payment record
        $insertPayment = $conn->prepare("
            INSERT INTO fine_payments (
                borrowing_id, 
                amount_paid,
                cash_received,
                change_amount, 
                payment_status,
                processed_by,
                payment_notes
            ) VALUES (
                :borrowing_id, 
                :amount_paid,
                :cash_received,
                :change_amount,
                'paid',
                :processed_by,
                :payment_notes
            )
        ");
        
        $insertPayment->execute([
            ':borrowing_id' => $borrowing_id,
            ':amount_paid' => $amount_paid,
            ':cash_received' => $cash_received,
            ':change_amount' => $change_amount,
            ':processed_by' => $_SESSION['user_id'],
            ':payment_notes' => $payment_notes
        ]);

        // Update borrowing record
        $updateBorrowing = $conn->prepare("
            UPDATE borrowings b
            SET fine_amount = (
                SELECT 
                    CASE 
                        WHEN DATEDIFF(CURRENT_DATE, b2.due_date) > 0 
                        THEN DATEDIFF(CURRENT_DATE, b2.due_date) * fc.fine_amount 
                        ELSE 0 
                    END - COALESCE((
                        SELECT SUM(fp.amount_paid) 
                        FROM fine_payments fp 
                        WHERE fp.borrowing_id = b2.borrowing_id 
                        AND fp.payment_status = 'paid'
                    ), 0)
                FROM borrowings b2
                JOIN library_resources lr ON b2.resource_id = lr.resource_id
                JOIN fine_configurations fc ON lr.category = fc.resource_type
                WHERE b2.borrowing_id = b.borrowing_id
            ),
            status = CASE 
                WHEN (
                    SELECT 
                        CASE 
                            WHEN DATEDIFF(CURRENT_DATE, b2.due_date) > 0 
                            THEN DATEDIFF(CURRENT_DATE, b2.due_date) * fc.fine_amount 
                            ELSE 0 
                        END - COALESCE((
                            SELECT SUM(fp.amount_paid) 
                            FROM fine_payments fp 
                            WHERE fp.borrowing_id = b2.borrowing_id 
                            AND fp.payment_status = 'paid'
                        ), 0)
                    FROM borrowings b2
                    JOIN library_resources lr ON b2.resource_id = lr.resource_id
                    JOIN fine_configurations fc ON lr.category = fc.resource_type
                    WHERE b2.borrowing_id = b.borrowing_id
                ) <= 0 THEN 
                    CASE 
                        WHEN return_date IS NOT NULL THEN 'returned'
                        ELSE 'active'
                    END
                ELSE status 
            END
            WHERE borrowing_id = :borrowing_id
        ");
        
        $updateBorrowing->execute([
            ':borrowing_id' => $borrowing_id
        ]);

        // Log the activity
        $logActivity = $conn->prepare("
            INSERT INTO activity_logs (user_id, action_type, action_description, ip_address)
            VALUES (:user_id, 'fine_payment', :description, :ip_address)
        ");
        
        $logActivity->execute([
            ':user_id' => $_SESSION['user_id'],
            ':description' => "Processed fine payment of $" . number_format($amount_paid, 2) . " for borrowing ID: " . $borrowing_id,
            ':ip_address' => $_SERVER['REMOTE_ADDR']
        ]);

        // Commit transaction
        $conn->commit();

        $_SESSION['success'] = "Payment processed successfully";
    } catch (PDOException $e) {
        // Rollback transaction on error
        $conn->rollBack();
        error_log("Payment processing error: " . $e->getMessage());
        $_SESSION['error'] = "Error processing payment. Please try again.";
    }
} else {
    $_SESSION['error'] = "Invalid request method";
}

header("Location: overdue-management.php");
exit();
