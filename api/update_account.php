<?php
require_once '../controller/UserController.php';
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if ($data) {
    // Hash the password if provided
    if (!empty($data['password'])) {
        $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
    }

    $userController = new UserController();
    $result = $userController->updateCredentials($_SESSION['user_id'], $data);

    if ($result) {
        // Update session username if it was changed
        if (isset($data['username'])) {
            $_SESSION['username'] = $data['username'];
        }
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update credentials']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid data']);
} 