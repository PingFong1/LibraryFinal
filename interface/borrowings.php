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

// Handle return resource action
$returnMessage = '';
$returnStatus = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['return_borrowing_id'])) {
    $returnResult = $borrowingController->returnResource($_POST['return_borrowing_id']);
    
    if ($returnResult['success']) {
        $returnMessage = "Resource returned successfully.";
        if ($returnResult['fine_amount'] > 0) {
            $returnMessage .= " Fine amount: $" . number_format($returnResult['fine_amount'], 2);
        }
        $returnStatus = 'success';
    } else {
        $returnMessage = "Failed to return resource: " . $returnResult['error'];
        $returnStatus = 'danger';
    }
}

// Fetch borrowings
$borrowings = $borrowingController->getAllBorrowings();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Borrowing Monitoring | Library Management System</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet"> 
    <style>
        .borrowing-monitoring-container {
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
        .borrower-details {
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div class="d-flex">
        <!-- Sidebar -->
        <?php include 'includes/sidebarModal.php'; ?>
        
        <!-- Main Content -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="borrowing-monitoring-container">
                <?php if (!empty($returnMessage)): ?>
                    <div class="alert alert-<?php echo $returnStatus; ?> alert-dismissible fade show" role="alert">
                        <?php echo htmlspecialchars($returnMessage); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <div class="page-header d-flex justify-content-between align-items-center">
                    <h2 class="mb-0">
                        <i></i>Borrowing Monitoring
                    </h2>
                    <div class="box p-3 border rounded">
                    <span class="me-2">Total Borrowings: <?php echo count($borrowings); ?></span>
                    </div>
                    
                </div>
                
                <?php if (empty($borrowings)): ?>
                    <div class="alert alert-info">No active borrowings at the moment.</div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>Borrower</th>
                                    <th>Resource</th>
                                    <th>Borrow Date</th>
                                    <th>Due Date</th>
                                    <th>Status</th>
                                    <th>Approved By</th>
                                    <th>Approval Date</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($borrowings as $borrowing): 
                                    $overdueInfo = $borrowingController->calculateOverdueStatus($borrowing['due_date']);
                                ?>
                                    <tr>
                                        <td class="borrower-details" data-bs-toggle="modal" data-bs-target="#borrowerModal<?php echo $borrowing['user_id']; ?>">
                                            <strong><?php echo htmlspecialchars($borrowing['first_name'] . ' ' . $borrowing['last_name']); ?></strong>
                                            <br>
                                            <small class="text-muted"><?php echo htmlspecialchars($borrowing['role']); ?></small>
                                        </td>
                                        <td>
                                            <?php echo htmlspecialchars($borrowing['resource_title']); ?>
                                            <br>
                                            <small class="text-muted"><?php echo htmlspecialchars($borrowing['resource_type']); ?></small>
                                        </td>
                                        <td><?php echo date('M d, Y', strtotime($borrowing['borrow_date'])); ?></td>
                                        <td><?php echo date('M d, Y', strtotime($borrowing['due_date'])); ?></td>
                                        <td>
                                            <span class="badge <?php echo $overdueInfo['class']; ?> status-badge">
                                                <?php 
                                                echo $overdueInfo['status'];
                                                if (isset($overdueInfo['days_overdue'])) {
                                                    echo ' (' . $overdueInfo['days_overdue'] . ' days)';
                                                } elseif (isset($overdueInfo['days_remaining'])) {
                                                    echo ' (' . $overdueInfo['days_remaining'] . ' days)';
                                                }
                                                ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if ($borrowing['approved_by']): ?>
                                                <?php echo htmlspecialchars($borrowing['approved_by']); ?>
                                                <br>
                                                <small class="text-muted"><?php echo htmlspecialchars($borrowing['approver_role']); ?></small>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($borrowing['approved_at']): ?>
                                                <?php echo date('M d, Y H:i', strtotime($borrowing['approved_at'])); ?>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-success" data-bs-toggle="modal" 
                                                    data-bs-target="#returnModal<?php echo $borrowing['borrowing_id']; ?>">
                                                <i class="bi bi-arrow-return-left"></i> Return
                                            </button>
                                        </td>
                                    </tr>

                                    <!-- Return Confirmation Modal -->
                                    <div class="modal fade" id="returnModal<?php echo $borrowing['borrowing_id']; ?>" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Return Resource Confirmation</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <p>Confirm return of <strong><?php echo htmlspecialchars($borrowing['resource_title']); ?></strong> borrowed by <?php echo htmlspecialchars($borrowing['first_name'] . ' ' . $borrowing['last_name']); ?>?</p>
                                                    
                                                    <?php if ($overdueInfo['status'] === 'Overdue'): ?>
                                                        <div class="alert alert-warning">
                                                            This resource is overdue by <?php echo $overdueInfo['days_overdue']; ?> days. 
                                                            A fine will be automatically calculated upon return.
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="modal-footer">
                                                    <form method="POST">
                                                        <input type="hidden" name="return_borrowing_id" value="<?php echo $borrowing['borrowing_id']; ?>">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                        <button type="submit" class="btn btn-primary">Confirm Return</button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Borrower Details Modal -->
                                    <div class="modal fade" id="borrowerModal<?php echo $borrowing['user_id']; ?>" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Borrower Details</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <p><strong>Name:</strong> <?php echo htmlspecialchars($borrowing['first_name'] . ' ' . $borrowing['last_name']); ?></p>
                                                    <p><strong>Email:</strong> <?php echo htmlspecialchars($borrowing['email']); ?></p>
                                                    <p><strong>Role:</strong> <?php echo htmlspecialchars($borrowing['role']); ?></p>
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