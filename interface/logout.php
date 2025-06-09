<?php
session_start();
require_once '../controller/ActivityLogController.php';

if (isset($_SESSION['user_id'])) {
    $activityLogController = new ActivityLogController();
    $activityLogController->logActivity(
        $_SESSION['user_id'],
        'logout',
        'User logged out'
    );
    
    // Then proceed with session destruction
    session_destroy();
    header("Location: login.php");
    exit();
}
?>