<?php
require_once '../controller/Session.php';
require_once '../controller/BorrowingController.php';

// Start the session and check login status
Session::start();

// Restrict access to admin and staff
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'staff')) {
    header("Location: ../login.php");
    exit();
}

// Create an instance of BorrowingController
$borrowingController = new BorrowingController();

// Fetch all borrowings with fines and overdue status
try {
    $conn = (new Database())->getConnection();
    $query = "SELECT 
                b.borrowing_id, 
                u.user_id,
                u.first_name, 
                u.last_name, 
                u.email, 
                u.role,
                lr.title AS resource_title,
                lr.category AS resource_type,
                b.borrow_date, 
                b.due_date, 
                b.return_date, 
                b.status,
                CASE 
                    WHEN b.return_date IS NOT NULL 
                    THEN DATEDIFF(b.return_date, b.due_date)
                    ELSE DATEDIFF(CURRENT_DATE, b.due_date)
                END as days_overdue,
                CASE 
                    WHEN lr.category = 'book' THEN bk.author
                    WHEN lr.category = 'periodical' THEN p.volume
                    WHEN lr.category = 'media' THEN m.media_type
                END AS resource_detail,
                fc.fine_amount as daily_fine_rate,
                CASE 
                    WHEN b.return_date IS NOT NULL 
                    THEN GREATEST(0, DATEDIFF(b.return_date, b.due_date)) * fc.fine_amount
                    WHEN b.due_date < CURRENT_DATE 
                    THEN DATEDIFF(CURRENT_DATE, b.due_date) * fc.fine_amount 
                    ELSE 0 
                END as calculated_fine,
                fp.payment_status,
                fp.amount_paid,
                fp.cash_received,
                fp.change_amount,
                fp.payment_date,
                proc.first_name as processor_first_name,
                proc.last_name as processor_last_name,
                proc.role as processor_role
              FROM borrowings b
              JOIN users u ON b.user_id = u.user_id
              JOIN library_resources lr ON b.resource_id = lr.resource_id
              JOIN fine_configurations fc ON lr.category = fc.resource_type
              LEFT JOIN books bk ON lr.resource_id = bk.resource_id AND lr.category = 'book'
              LEFT JOIN periodicals p ON lr.resource_id = p.resource_id AND lr.category = 'periodical'
              LEFT JOIN media_resources m ON lr.resource_id = m.resource_id AND lr.category = 'media'
              LEFT JOIN fine_payments fp ON b.borrowing_id = fp.borrowing_id
              LEFT JOIN users proc ON fp.processed_by = proc.user_id
              WHERE (b.status = 'overdue' OR 
                    (b.status = 'active' AND b.due_date < CURRENT_DATE) OR
                    (b.status = 'returned' AND b.return_date > b.due_date))
              ORDER BY b.due_date ASC";
    
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $fineResources = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Calculate total fines
    $totalFines = array_reduce($fineResources, function($carry, $borrowing) {
        return $carry + $borrowing['calculated_fine'];
    }, 0);
} catch (PDOException $e) {
    error_log("Payment history error: " . $e->getMessage());
    $fineResources = [];
    $totalFines = 0;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment History | Library Management System</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet"> 
    <style>
        body {
            background-color: #f4f6f9;
        }
        .payment-history-container {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            padding: 30px;
            margin-top: 30px;
        }
        .page-header {
            background-color: #003161;
            color: white;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="d-flex">
        <!-- Sidebar -->
        <?php include 'includes/sidebarModal.php'; ?>
        
        <!-- Main Content -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="payment-history-container">
                <div class="page-header d-flex justify-content-between align-items-center">
                    <h2 class="mb-0">
                        <i class="bi bi-cash-stack me-2"></i>Payment History
                    </h2>
                    <div class="box p-3 border rounded">
                        <span>Total Fines Collected: $<?php echo number_format($totalFines, 2); ?></span>
                    </div>
                </div>
                
                <?php if (empty($fineResources)): ?>
                    <div class="alert alert-info">No fines to display.</div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>Borrower</th>
                                    <th>Resource</th>
                                    <th>Borrow Date</th>
                                    <th>Due Date</th>
                                    <th>Days Overdue</th>
                                    <th>Fine Amount</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($fineResources as $borrowing): ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo htmlspecialchars($borrowing['first_name'] . ' ' . $borrowing['last_name']); ?></strong>
                                            <br>
                                            <small class="text-muted"><?php echo htmlspecialchars($borrowing['role']); ?></small>
                                        </td>
                                        <td>
                                            <?php echo htmlspecialchars($borrowing['resource_title']); ?>
                                            <br>
                                            <small class="text-muted">
                                                <?php echo htmlspecialchars($borrowing['resource_type']); 
                                                if (!empty($borrowing['resource_detail'])) {
                                                    echo ' - ' . htmlspecialchars($borrowing['resource_detail']);
                                                }
                                                ?>
                                            </small>
                                        </td>
                                        <td><?php echo date('M d, Y', strtotime($borrowing['borrow_date'])); ?></td>
                                        <td><?php echo date('M d, Y', strtotime($borrowing['due_date'])); ?></td>
                                        <td>
                                            <span class="text-danger">
                                                <?php echo $borrowing['days_overdue']; ?> days
                                            </span>
                                        </td>
                                        <td>
                                            <strong class="text-danger">$<?php echo number_format($borrowing['calculated_fine'], 2); ?></strong>
                                            <br>
                                            <small class="text-muted">Rate: $<?php echo number_format($borrowing['daily_fine_rate'], 2); ?>/day</small>
                                        </td>
                                        <td>
                                            <?php if ($borrowing['payment_status'] === 'paid'): ?>
                                                <span class="badge text-success">
                                                    <i class="bi bi-check-circle"></i> Paid
                                                </span>
                                            <?php else: ?>
                                                <span class="badge text-warning">
                                                    <i class="bi bi-exclamation-circle"></i> Unpaid
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-info" data-bs-toggle="modal" 
                                                    data-bs-target="#paymentModal<?php echo $borrowing['borrowing_id']; ?>">
                                                <i class="bi bi-eye"></i> View Details
                                            </button>
                                        </td>
                                    </tr>
                                    
                                    <!-- Payment Details Modal -->
                                    <div class="modal fade" id="paymentModal<?php echo $borrowing['borrowing_id']; ?>" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Payment Details</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="row mb-3">
                                                        <div class="col-md-6">
                                                            <h6>Resource</h6>
                                                            <p class="mb-1">
                                                                <?php echo htmlspecialchars($borrowing['resource_title']); ?>
                                                                <br>
                                                                <small class="text-muted">
                                                                    <?php echo htmlspecialchars($borrowing['resource_type']); 
                                                                    if (!empty($borrowing['resource_detail'])) {
                                                                        echo ' - ' . htmlspecialchars($borrowing['resource_detail']);
                                                                    }
                                                                    ?>
                                                                </small>
                                                            </p>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <h6>Borrower</h6>
                                                            <p class="mb-1">
                                                                <?php echo htmlspecialchars($borrowing['first_name'] . ' ' . $borrowing['last_name']); ?>
                                                                <br>
                                                                <small class="text-muted"><?php echo htmlspecialchars($borrowing['role']); ?></small>
                                                            </p>
                                                        </div>
                                                    </div>

                                                    <div class="row mb-3">
                                                        <div class="col-md-6">
                                                            <h6>Due Date</h6>
                                                            <p class="mb-1"><?php echo date('M d, Y', strtotime($borrowing['due_date'])); ?></p>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <h6>Days Overdue</h6>
                                                            <p class="mb-1 text-danger"><?php echo $borrowing['days_overdue']; ?> days</p>
                                                        </div>
                                                    </div>

                                                    <div class="card mb-3">
                                                        <div class="card-body">
                                                            <h6 class="card-title">Fine Information</h6>
                                                            <p class="card-text mb-1">
                                                                <strong>Amount:</strong> 
                                                                <span class="text-danger">$<?php echo number_format($borrowing['calculated_fine'], 2); ?></span>
                                                                <br>
                                                                <small class="text-muted">Daily Rate: $<?php echo number_format($borrowing['daily_fine_rate'], 2); ?></small>
                                                            </p>
                                                        </div>
                                                    </div>

                                                    <?php if ($borrowing['payment_status'] === 'paid'): ?>
                                                        <div class="alert alert-success">
                                                            <h6 class="alert-heading">Payment Completed</h6>
                                                            <p class="mb-1">
                                                                <strong>Amount Paid:</strong> $<?php echo number_format($borrowing['amount_paid'], 2); ?><br>
                                                                <strong>Cash Received:</strong> $<?php echo number_format($borrowing['cash_received'], 2); ?><br>
                                                                <strong>Change:</strong> $<?php echo number_format($borrowing['change_amount'], 2); ?><br>
                                                                <strong>Date:</strong> <?php echo date('M d, Y H:i:s', strtotime($borrowing['payment_date'])); ?><br>
                                                                <strong>Processed By:</strong> <?php echo htmlspecialchars($borrowing['processor_first_name'] . ' ' . $borrowing['processor_last_name']); ?> 
                                                                <small>(<?php echo htmlspecialchars($borrowing['processor_role']); ?>)</small>
                                                            </p>
                                                        </div>
                                                    <?php else: ?>
                                                        <div class="alert alert-warning">
                                                            <h6 class="alert-heading">Payment Pending</h6>
                                                            <p class="mb-0">
                                                                <?php echo $borrowing['return_date'] ? 
                                                                    'Item returned but fine is unpaid.' : 
                                                                    'Fine continues to accumulate until the resource is returned.'; ?>
                                                            </p>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <!-- Bootstrap JS and Popper.js -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
</body>
</html>