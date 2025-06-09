<?php
require_once '../controller/Session.php';
require_once '../controller/BorrowingController.php';

// Start the session and check login status
Session::start();

// Restrict access to admin only
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// Create an instance of BorrowingController
$borrowingController = new BorrowingController();

// Handle fine configuration updates
$returnMessage = '';
$returnStatus = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_fines') {
    $success = true;
    foreach ($_POST['fines'] as $resource_type => $amount) {
        if (!$borrowingController->updateFineConfiguration($resource_type, $amount)) {
            $success = false;
            break;
        }
    }
    if ($success) {
        $returnMessage = "Fine rates updated successfully.";
        $returnStatus = "success";
    } else {
        $returnMessage = "Error updating fine rates.";
        $returnStatus = "danger";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fine Configuration | Library Management System</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    
    <style>
        .fine-monitoring-container {
            padding: 20px;
        }
        .page-header {
            margin-bottom: 20px;
            padding: 15px 0;
            border-bottom: 1px solid #eee;
        }
        .status-badge {
            font-size: 0.85rem;
            padding: 5px 10px;
        }
        .box {
            background-color: #f8f9fa;
        }
    </style>
</head>
<body>
    <div class="d-flex">
        <!-- Sidebar -->
        <?php include 'includes/sidebarModal.php'; ?>
        
        <!-- Main Content -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="fine-monitoring-container">
                <?php if (!empty($returnMessage)): ?>
                    <div class="alert alert-<?php echo $returnStatus; ?> alert-dismissible fade show" role="alert">
                        <?php echo htmlspecialchars($returnMessage); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <div class="page-header d-flex justify-content-between align-items-center">
                    <h2 class="mb-0">
                        <i class="bi bi-currency-dollar bg me-2"></i>Fine Rate Configuration
                    </h2>
                    <div>
                        <span class="me-2"> <?php 
                            $fineConfigs = $borrowingController->getFineConfigurations();
                        
                        ?></span>
                    </div>
                </div>

                <div class="card shadow-sm">
                    <div class="card-body">
                        <form method="POST" class="row g-4">
                            <input type="hidden" name="action" value="update_fines">
                            
                            <?php foreach ($fineConfigs as $config): ?>
                            <div class="col-md-4">
                                <div class="card h-100">
                                    <div class="card-header bg-light">
                                        <h5 class="card-title mb-0">
                                            <i class="bi bi-<?php 
                                                echo $config['resource_type'] === 'book' ? 'book' : 
                                                    ($config['resource_type'] === 'periodical' ? 'newspaper' : 'camera-video'); 
                                            ?> me-2"></i>
                                            <?php echo ucfirst($config['resource_type']); ?>
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <label class="form-label">Fine Rate ($ per day)</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bi bi-currency-dollar"></i></span>
                                            <input type="number" 
                                                   class="form-control" 
                                                   name="fines[<?php echo $config['resource_type']; ?>]" 
                                                   value="<?php echo number_format($config['fine_amount'], 2); ?>"
                                                   step="0.01" 
                                                   min="0" 
                                                   required>
                                        </div>
                                        <small class="text-muted mt-2 d-block">
                                            Last updated: <?php echo date('M d, Y H:i', strtotime($config['updated_at'])); ?>
                                        </small>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                            
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-save me-2"></i>Update Fine Rates
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Bootstrap JS and Popper.js -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
</body>
</html>
