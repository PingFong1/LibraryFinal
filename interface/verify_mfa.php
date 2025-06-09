<?php
require_once '../controller/MFAController.php';
require_once '../controller/UserController.php';
require_once '../controller/ActivityLogController.php';
require_once '../controller/Session.php';

Session::start();

// Check if we have a pending MFA verification
if (!isset($_SESSION['mfa_pending']) || !isset($_SESSION['temp_user_id'])) {
    header("Location: login.php");
    exit();
}

$error_message = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mfaController = new MFAController();
    $result = $mfaController->verifyMFA($_SESSION['temp_user_id'], $_POST['code']);
    
    if ($result['success']) {
        // Get user data
        $userController = new UserController();
        $user = $userController->getUserById($_SESSION['temp_user_id']);
        
        if ($user) {
            // Set all session variables
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['full_name'] = $user['first_name'] . ' ' . $user['last_name'];
            
            // Clean up temporary session data
            unset($_SESSION['mfa_pending']);
            unset($_SESSION['temp_user_id']);
            unset($_SESSION['temp_username']);
            
            // Log the successful login
            $activityLogger = new ActivityLogController();
            $activityLogger->logActivity(
                $user['user_id'],
                'login',
                'User logged in successfully with MFA'
            );
            
            header("Location: dashboard.php");
            exit();
        }
    } else {
        $error_message = $result['message'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Library Management System - MFA Verification</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/remixicon/3.5.0/remixicon.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&display=swap');
        body {
            font-family: 'Inter', sans-serif;
        }
        input[type="text"] {
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
        }
        input[type="text"]:focus {
            border-color: #7c3aed;
            box-shadow: 0 0 0 3px rgba(124, 58, 237, 0.3);
        }
    </style>
</head>
<body class="bg-white min-h-screen flex items-center justify-center">
    <div class="w-full min-h-screen flex">
        <!-- Left side with illustration -->
        <div class="hidden lg:flex lg:w-1/2 relative items-center justify-center p-12 overflow-hidden" 
             style="background-image: url('../uploads/images/7a731ad3cc969be40ba7ae6417c7aa85.jpg'); 
                    background-size: cover; 
                    background-position: center;
                    background-repeat: no-repeat;">
            <div class="absolute inset-0 backdrop-blur-[3px] bg-blue-900/40"></div>
            
            <div class="text-center relative z-10">
                <h1 class="text-3xl font-bold text-white mb-4">Two-Factor Authentication</h1>
                <p class="text-gray-100">Please enter your verification code to continue.</p>
            </div>
        </div>

        <!-- Right side with verification form -->
        <div class="w-full lg:w-1/2 flex items-center justify-center p-8">
            <div class="w-full max-w-md">
                <h2 class="text-2xl font-bold text-gray-800 mb-2">Enter Verification Code</h2>
                <p class="text-gray-500 mb-8">Please enter the 6-digit code from your authenticator app</p>

                <?php if ($error_message): ?>
                    <div class="bg-red-50 border border-red-200 text-red-600 px-4 py-3 rounded-lg mb-4">
                        <span><?php echo htmlspecialchars($error_message); ?></span>
                    </div>
                <?php endif; ?>

                <form method="POST" action="" class="space-y-6">
                    <div>
                        <label for="code" class="block text-sm font-medium text-gray-700 mb-2">
                            Verification Code
                        </label>
                        <input 
                            type="text" 
                            id="code" 
                            name="code" 
                            required 
                            pattern="[0-9]{6}"
                            maxlength="6"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500"
                            placeholder="Enter 6-digit code"
                            autocomplete="one-time-code"
                        >
                    </div>

                    <button 
                        type="submit" 
                        class="w-full bg-blue-900 text-white py-3 rounded-lg hover:bg-blue-700 transition duration-300"
                    >
                        Verify
                    </button>
                </form>

                <div class="mt-4 text-center">
                    <p class="text-sm text-gray-600">
                        Lost access to your authenticator?
                        <a href="#" onclick="showBackupCodeForm()" class="text-blue-600 hover:text-blue-800">
                            Use a backup code
                        </a>
                    </p>
                </div>

                <!-- Backup Code Form (hidden by default) -->
                <form id="backupCodeForm" method="POST" action="" class="hidden space-y-6 mt-6 pt-6 border-t">
                    <div>
                        <label for="backup_code" class="block text-sm font-medium text-gray-700 mb-2">
                            Backup Code
                        </label>
                        <input 
                            type="text" 
                            id="backup_code" 
                            name="code" 
                            pattern="[a-f0-9]{8}"
                            maxlength="8"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500"
                            placeholder="Enter backup code"
                        >
                    </div>

                    <button 
                        type="submit" 
                        class="w-full bg-gray-800 text-white py-3 rounded-lg hover:bg-gray-700 transition duration-300"
                    >
                        Use Backup Code
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script>
        function showBackupCodeForm() {
            document.getElementById('backupCodeForm').classList.remove('hidden');
        }
    </script>
</body>
</html> 