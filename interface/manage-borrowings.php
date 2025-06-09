<?php
require_once '../controller/Session.php';
require_once '../controller/BorrowingController.php';

Session::start();

// Check if user is staff/admin
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'staff' && $_SESSION['role'] !== 'admin')) {
    header("Location: ../login.php");
    exit();
}

$borrowingController = new BorrowingController();

// Handle approval action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['approve_borrowing'])) {
    $result = $borrowingController->approveBorrowing($_POST['borrowing_id'], $_SESSION['user_id']);
    $message = $result ? "Borrowing request approved successfully!" : "Unable to approve request.";
}

// Get pending borrowings
$pendingBorrowings = $borrowingController->getPendingBorrowings();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Borrowing Requests</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="assets/css/custom.css" rel="stylesheet">
</head>
<body>
    <div class="d-flex">
        <!-- Sidebar -->
        <?php include 'includes/sidebarModal.php'; ?>

        <!-- Main Content -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="container mt-4">
                <h2 class="mb-4">Pending Borrowing Requests</h2>

                <?php if (isset($message)): ?>
                    <div class="alert <?php echo strpos($message, 'successfully') !== false ? 'alert-success' : 'alert-danger'; ?> alert-dismissible fade show">
                        <?php echo $message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if (empty($pendingBorrowings)): ?>
                    <div class="alert alert-info">
                        No pending borrowing requests at this time.
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Request Date</th>
                                    <th>Member ID</th>
                                    <th>Member Name</th>
                                    <th>Role</th>
                                    <th>Resource</th>
                                    <th>Accession No.</th>
                                    <th>Type</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($pendingBorrowings as $borrowing): ?>
                                    <tr>
                                        <td><?php echo date('Y-m-d H:i', strtotime($borrowing['borrow_date'])); ?></td>
                                        <td><?php echo htmlspecialchars($borrowing['membership_id']); ?></td>
                                        <td><?php echo htmlspecialchars($borrowing['first_name'] . ' ' . $borrowing['last_name']); ?></td>
                                        <td><?php echo ucfirst(htmlspecialchars($borrowing['user_role'])); ?></td>
                                        <td><?php echo htmlspecialchars($borrowing['resource_title']); ?></td>
                                        <td><?php echo htmlspecialchars($borrowing['accession_number']); ?></td>
                                        <td><?php echo ucfirst(htmlspecialchars($borrowing['resource_type'])); ?></td>
                                        <td>
                                            <form method="POST" class="d-inline">
                                                <input type="hidden" name="borrowing_id" value="<?php echo $borrowing['borrowing_id']; ?>">
                                                <button type="submit" name="approve_borrowing" class="btn btn-success btn-sm">
                                                    <i class="bi bi-check-circle"></i> Approve
                                                </button>
                                            </form>
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

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 