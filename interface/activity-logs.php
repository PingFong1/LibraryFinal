<?php
require_once '../controller/UserController.php';
require_once '../controller/ActivityLogController.php';
session_start();

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$activityLogController = new ActivityLogController();
$logs = $activityLogController->getLogs();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Activity & Security Logs - Library Management System</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.7.2/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
    <div class="d-flex">
        <?php include 'includes/sidebarModal.php'; ?>

        <div class="main-content flex-grow-1">
            <!-- Header Section -->
            <div class="d-flex justify-content-between align-items-center py-3 px-4">
                <div>
                    <h2 class="mb-0">Activity & Security Logs</h2>
                    <p class="text-muted mb-0">View all system activities and security events</p>
                </div>
                <!-- User Profile Section -->
                <div>
                    <div class="fw-bold"><?php echo htmlspecialchars($_SESSION['full_name']); ?></div>
                    <span class="badge bg-primary"><?php echo ucfirst($_SESSION['role']); ?></span>
                </div>
            </div>
            <hr>

            <div class="container-fluid px-4">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Time</th>
                                        <th>User</th>
                                        <th>Event Type</th>
                                        <th>Description</th>
                                        <th>IP Address</th>
                                        <th>Details</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($logs)): ?>
                                        <tr>
                                            <td colspan="6" class="text-center">No logs found.</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($logs as $log): ?>
                                            <tr>
                                                <td><?php echo date('Y-m-d H:i:s', strtotime($log['time'])); ?></td>
                                                <td><?php echo htmlspecialchars($log['username'] ?? 'System'); ?></td>
                                                <td>
                                                    <span class="badge <?php echo getActionTypeBadgeClass($log['action_type']); ?>">
                                                        <?php echo htmlspecialchars($log['action_type']); ?>
                                                    </span>
                                                </td>
                                                <td><?php echo htmlspecialchars($log['action_description']); ?></td>
                                                <td><?php echo htmlspecialchars($log['ip_address']); ?></td>
                                                <td>
                                                    <?php if ($log['log_type'] === 'mfa' && $log['attempt_count']): ?>
                                                        <span class="badge bg-info">
                                                            Attempt #<?php echo htmlspecialchars($log['attempt_count']); ?>
                                                        </span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
function getActionTypeBadgeClass($actionType) {
    $classes = [
        'login' => 'bg-primary',
        'logout' => 'bg-secondary',
        'create' => 'bg-success',
        'update' => 'bg-warning',
        'mfa_success' => 'bg-success',
        'mfa_failed' => 'bg-danger',
        'security' => 'bg-info'
    ];
    
    return $classes[$actionType] ?? 'bg-secondary';
}
?>
