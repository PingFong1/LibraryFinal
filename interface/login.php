<?php
require_once '../controller/UserController.php';
session_start();

if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userController = new UserController();
    $result = $userController->login($_POST['username'], $_POST['password']);
    
    if ($result['success']) {
        if ($result['mfa_required']) {
            header("Location: verify_mfa.php");
            exit();
        }
        header("Location: dashboard.php");
        exit();
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
    <title>Library Management System - Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/remixicon/3.5.0/remixicon.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&display=swap');
        body {
            font-family: 'Inter', sans-serif;
        }
        input[type="text"], input[type="password"] {
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
        }
        input[type="text"]:focus, input[type="password"]:focus {
            border-color: #7c3aed; /* Purple border on focus */
            box-shadow: 0 0 0 3px rgba(124, 58, 237, 0.3); /* Purple shadow on focus */
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
            <!-- Add blur effect with overlay -->
            <div class="absolute inset-0 backdrop-blur-[3px] bg-blue-900/40"></div>
            
            <div class="text-center relative z-10">
                <h1 class="text-3xl font-bold text-white mb-4">Welcome to Library Management System</h1>
                <p class="text-gray-100">Borrow and manage your books with ease.</p>
            </div>
        </div>

        <!-- Right side with login form -->
        <div class="w-full lg:w-1/2 flex items-center justify-center p-8">
            <div class="w-full max-w-md">
                <h2 class="text-2xl font-bold text-gray-800 mb-2">Login</h2>
                <p class="text-gray-500 mb-8">Acces your account</p>

                <?php if (isset($error_message)): ?>
                    <div class="bg-red-50 border border-red-200 text-red-600 px-4 py-3 rounded-lg mb-4">
                        <span><?php echo htmlspecialchars($error_message); ?></span>
                    </div>
                <?php endif; ?>

                <form method="POST" action="" class="space-y-6">
                    <div>
                        <label for="username" class="block text-sm font-medium text-gray-700 mb-2">
                           Username
                        </label>
                        <input 
                            type="text" 
                            id="username" 
                            name="username" 
                            required 
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500"
                            placeholder="Enter Email or Username"
                        >
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                            Password
                        </label>
                        <div class="relative">
                            <input 
                                type="password" 
                                id="password" 
                                name="password" 
                                required 
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500"
                                placeholder="Enter Password"
                            >
                            <button type="button" 
                                    class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-700"
                                    onclick="togglePassword()">
                                <i id="eyeIcon" class="ri-eye-off-line text-xl"></i>
                            </button>
                        </div>
                    </div>

                    <button 
                        type="submit" 
                        class="w-full bg-blue-900 text-white py-3 rounded-lg hover:bg-blue-700 transition duration-300"
                    >
                        Login
                    </button>
                </form>
                <script>
                    function togglePassword() {
                        const passwordInput = document.getElementById('password');
                        const eyeIcon = document.getElementById('eyeIcon');
                        
                        if (passwordInput.type === 'password') {
                            passwordInput.type = 'text';
                            eyeIcon.className = 'ri-eye-line text-xl transition-transform duration-300 rotate-180';
                        } else {
                            passwordInput.type = 'password';
                            eyeIcon.className = 'ri-eye-off-line text-xl transition-transform duration-300 rotate-0';
                        }
                    }
                </script>
            </div>
        </div>
    </div>
</body>
</html>