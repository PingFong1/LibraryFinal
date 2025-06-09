<?php
require_once '../controller/MFAController.php';
require_once '../controller/Session.php';

Session::start();
Session::requireLogin();

$mfaController = new MFAController();
$success_message = null;
$error_message = null;
$qr_data = null;
$backup_codes = null;

// Handle MFA setup
if (isset($_POST['setup_mfa'])) {
    $setupResult = $mfaController->setupMFA($_SESSION['user_id']);
    if ($setupResult) {
        $qr_data = $setupResult;
    } else {
        $error_message = "Failed to set up MFA";
    }
}

// Handle MFA enable/verify
if (isset($_POST['verify_mfa'])) {
    $result = $mfaController->enableMFA($_SESSION['user_id'], $_POST['verification_code']);
    if ($result['success']) {
        $success_message = $result['message'];
    } else {
        $error_message = $result['message'];
    }
}

// Handle MFA disable
if (isset($_POST['disable_mfa'])) {
    $result = $mfaController->disableMFA($_SESSION['user_id'], $_POST['verification_code']);
    if ($result['success']) {
        $success_message = $result['message'];
    } else {
        $error_message = $result['message'];
    }
}

$mfa_enabled = $mfaController->isMFAEnabled($_SESSION['user_id']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MFA Settings - Library Management System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/remixicon/3.5.0/remixicon.min.css" rel="stylesheet">
</head>
<body class="bg-gray-50">
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-2xl mx-auto bg-white rounded-lg shadow-sm p-6">
            <h1 class="text-2xl font-bold mb-6">Two-Factor Authentication Settings</h1>

            <?php if ($success_message): ?>
                <div class="bg-green-50 border border-green-200 text-green-600 px-4 py-3 rounded-lg mb-4">
                    <?php echo htmlspecialchars($success_message); ?>
                </div>
            <?php endif; ?>

            <?php if ($error_message): ?>
                <div class="bg-red-50 border border-red-200 text-red-600 px-4 py-3 rounded-lg mb-4">
                    <?php echo htmlspecialchars($error_message); ?>
                </div>
            <?php endif; ?>

            <?php if (!$mfa_enabled && !$qr_data): ?>
                <div class="mb-6">
                    <p class="text-gray-600 mb-4">
                        Two-factor authentication adds an extra layer of security to your account. 
                        When enabled, you'll need to enter both your password and a verification code 
                        from your authenticator app to sign in.
                    </p>
                    <form method="POST" action="">
                        <button type="submit" 
                                name="setup_mfa" 
                                class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition">
                            Set Up Two-Factor Authentication
                        </button>
                    </form>
                </div>
            <?php endif; ?>

            <?php if ($qr_data): ?>
                <div class="mb-6">
                    <h2 class="text-xl font-semibold mb-4">Set Up Authenticator App</h2>
                    <ol class="list-decimal list-inside space-y-4 mb-6">
                        <li>Install Google Authenticator or a compatible app on your mobile device</li>
                        <li>Scan the QR code below with your authenticator app</li>
                        <li>Enter the 6-digit verification code shown in your app</li>
                    </ol>

                    <div class="mb-6">
                        <img src="<?php echo htmlspecialchars($qr_data['qr_url']); ?>" 
                             alt="QR Code" 
                             class="border p-4 rounded-lg">
                        <p class="mt-2 text-sm text-gray-500">
                            Can't scan? Enter this code manually: 
                            <code class="bg-gray-100 px-2 py-1 rounded"><?php echo htmlspecialchars($qr_data['secret']); ?></code>
                        </p>
                    </div>

                    <form method="POST" action="" class="space-y-4">
                        <div>
                            <label for="verification_code" class="block text-sm font-medium text-gray-700 mb-2">
                                Verification Code
                            </label>
                            <input type="text" 
                                   id="verification_code" 
                                   name="verification_code" 
                                   required 
                                   pattern="[0-9]{6}"
                                   maxlength="6"
                                   class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                   placeholder="Enter 6-digit code">
                        </div>
                        <button type="submit" 
                                name="verify_mfa" 
                                class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 transition">
                            Verify and Enable
                        </button>
                    </form>

                    <?php if ($qr_data['backup_codes']): ?>
                        <div class="mt-8 p-4 bg-gray-50 rounded-lg">
                            <h3 class="text-lg font-semibold mb-2">Backup Codes</h3>
                            <p class="text-sm text-gray-600 mb-4">
                                Save these backup codes in a secure place. You can use them to sign in if you lose access 
                                to your authenticator app. Each code can only be used once.
                            </p>
                            <div class="grid grid-cols-2 gap-2">
                                <?php foreach ($qr_data['backup_codes'] as $code): ?>
                                    <code class="bg-white px-3 py-1 rounded border text-center">
                                        <?php echo htmlspecialchars($code); ?>
                                    </code>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <?php if ($mfa_enabled): ?>
                <div class="mb-6">
                    <div class="flex items-center mb-4">
                        <div class="w-4 h-4 bg-green-500 rounded-full mr-2"></div>
                        <span class="font-medium">Two-factor authentication is enabled</span>
                    </div>
                    <form method="POST" action="" class="space-y-4">
                        <div>
                            <label for="verification_code" class="block text-sm font-medium text-gray-700 mb-2">
                                Enter verification code to disable 2FA
                            </label>
                            <input type="text" 
                                   id="verification_code" 
                                   name="verification_code" 
                                   required 
                                   pattern="[0-9]{6}"
                                   maxlength="6"
                                   class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                   placeholder="Enter 6-digit code">
                        </div>
                        <button type="submit" 
                                name="disable_mfa" 
                                class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700 transition"
                                onclick="return confirm('Are you sure you want to disable two-factor authentication? This will make your account less secure.')">
                            Disable Two-Factor Authentication
                        </button>
                    </form>
                </div>
            <?php endif; ?>

            <div class="mt-6 pt-6 border-t">
                <a href="dashboard.php" class="text-blue-600 hover:text-blue-800">
                    &larr; Back to Dashboard
                </a>
            </div>
        </div>
    </div>
</body>
</html> 