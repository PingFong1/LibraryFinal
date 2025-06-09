<?php
require_once '../controller/Session.php';
require_once '../controller/BorrowingController.php';

Session::start();

// Check if user is logged in and has appropriate role
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'staff')) {
    header("Location: ../login.php");
    exit();
}

// Get approved borrowings
$borrowingController = new BorrowingController();
$approvedBorrowings = $borrowingController->getApprovedBorrowings();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Audit | Library Management System</title>
    
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
                <div class="page-header d-flex justify-content-between align-items-center">
                    <h2 class="mb-0">
                        <i class="bi bi-clipboard-check"></i> Borrowing Audit
                    </h2>
                    <div class="box p-3 border rounded">
                        <span class="me-2">Total Records: <?php echo count($approvedBorrowings); ?></span>
                    </div>
                </div>

                <?php if (empty($approvedBorrowings)): ?>
                    <div class="alert alert-info">No approved borrowings found.</div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>Borrower</th>
                                    <th>Resource</th>
                                    <th>Borrow Date</th>
                                    <th>Due Date</th>
                                    <th>Approved By</th>
                                    <th>Approval Date</th>
                                    <th>Return by</th>
                                    <th>Return Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($approvedBorrowings as $borrowing): ?>
                                    <tr>
                                        <td>
                                            <?php echo htmlspecialchars($borrowing['first_name'] . ' ' . $borrowing['last_name']); ?>
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
                                            <?php echo htmlspecialchars($borrowing['approved_by']); ?>
                                            <br>
                                            <small class="text-muted"><?php echo htmlspecialchars($borrowing['approver_role']); ?></small>
                                        </td>
                                        <td><?php echo date('M d, Y H:i', strtotime($borrowing['approved_at'])); ?></td>
                                        <td>
                                            <?php if ($borrowing['returned_by']): ?>
                                                <?php echo htmlspecialchars($borrowing['returned_by']); ?>
                                                <br>
                                                <small class="text-muted"><?php echo htmlspecialchars($borrowing['returner_role']); ?></small>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php echo $borrowing['return_date'] ? date('M d, Y', strtotime($borrowing['return_date'])) : '-'; ?>
                                        </td>
                                    </tr>
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
?> 