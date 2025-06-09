<?php
session_start();
require_once '../controller/MFAController.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$mfaController = new MFAController();

// Handle form submission for enabling MFA
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['verification_code'])) {
    $result = $mfaController->enableMFA($_SESSION['user_id'], $_POST['verification_code']);
    if ($result['success']) {
        $_SESSION['success_message'] = "MFA has been enabled successfully!";
        header("Location: dashboard.php");
        exit();
    } else {
        $error_message = $result['message'];
    }
}

// Get MFA setup data
$setupData = $mfaController->setupMFA($_SESSION['user_id']);

// Debug output
error_log("MFA Setup Data: " . print_r($setupData, true));

if (!$setupData) {
    die("Error: Failed to generate MFA setup data. Please try again.");
}

// Ensure we have all required data
if (!isset($setupData['secret']) || !isset($setupData['qr_url']) || !isset($setupData['backup_codes'])) {
    die("Error: Incomplete MFA setup data generated. Please try again.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Set Up Two-Factor Authentication - Library Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="card shadow">
                    <div class="card-body p-4">
                        <h2 class="text-center mb-4">Set Up Two-Factor Authentication</h2>
                        
                        <?php if (isset($error_message)): ?>
                            <div class="alert alert-danger"><?php echo htmlspecialchars($error_message); ?></div>
                        <?php endif; ?>

                        <!-- Debug info (hidden in production) -->
                        <?php if (isset($_SESSION['debug'])): ?>
                        <div class="alert alert-info">
                            <strong>Debug Info:</strong><br>
                            QR URL: <?php echo htmlspecialchars($setupData['qr_url']); ?><br>
                            Secret: <?php echo htmlspecialchars($setupData['secret']); ?>
                        </div>
                        <?php endif; ?>

                        <div class="text-center mb-4">
                            <img src="<?php echo htmlspecialchars($setupData['qr_url']); ?>" 
                                 alt="QR Code" 
                                 class="img-fluid mb-3"
                                 style="max-width: 200px;">
                            
                            <?php if (empty($setupData['qr_url'])): ?>
                                <div class="alert alert-warning">
                                    Error: QR code URL is empty. Please try refreshing the page.
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="mb-4">
                            <h5>Setup Instructions:</h5>
                            <ol class="list-group list-group-numbered mb-3">
                                <li class="list-group-item">Install Google Authenticator (or any TOTP app) on your phone</li>
                                <li class="list-group-item">Scan the QR code with the app</li>
                                <li class="list-group-item">Enter the 6-digit code shown in your app below</li>
                            </ol>
                        </div>

                        <div class="mb-4">
                            <h5>Manual Setup</h5>
                            <p class="mb-2">If you can't scan the QR code, enter this code manually in your app:</p>
                            <div class="alert alert-secondary text-monospace">
                                <?php echo chunk_split(htmlspecialchars($setupData['secret']), 4, ' '); ?>
                            </div>
                        </div>

                        <div class="mb-4">
                            <h5>Backup Codes</h5>
                            <p class="mb-2">Save these backup codes in a secure place. You can use them to access your account if you lose your phone:</p>
                            <div class="alert alert-warning">
                                <?php foreach ($setupData['backup_codes'] as $code): ?>
                                    <code class="d-block mb-1"><?php echo htmlspecialchars($code); ?></code>
                                <?php endforeach; ?>
                            </div>
                            <div class="alert alert-info">
                                <i class="bi bi-info-circle"></i> Each backup code can only be used once.
                            </div>
                        </div>

                        <form method="POST" class="needs-validation" novalidate>
                            <div class="mb-3">
                                <label for="verification_code" class="form-label">Verification Code</label>
                                <input type="text" 
                                       class="form-control form-control-lg text-center" 
                                       id="verification_code" 
                                       name="verification_code" 
                                       pattern="[0-9]{6}" 
                                       maxlength="6" 
                                       required>
                                <div class="invalid-feedback">
                                    Please enter the 6-digit code from your authenticator app.
                                </div>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    Enable Two-Factor Authentication
                                </button>
                                <a href="dashboard.php" class="btn btn-outline-secondary">
                                    Cancel
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Form validation
        (function () {
            'use strict'
            var forms = document.querySelectorAll('.needs-validation')
            Array.prototype.slice.call(forms).forEach(function (form) {
                form.addEventListener('submit', function (event) {
                    if (!form.checkValidity()) {
                        event.preventDefault()
                        event.stopPropagation()
                    }
                    form.classList.add('was-validated')
                }, false)
            })
        })()
    </script>
</body>
</html> 