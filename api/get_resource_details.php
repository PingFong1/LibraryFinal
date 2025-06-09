<?php
require_once '../config/Database.php';
require_once '../controller/ResourceController.php';

header('Content-Type: application/json');

if (!isset($_GET['resource_id']) || !isset($_GET['type'])) {
    echo json_encode(['success' => false, 'message' => 'Missing parameters']);
    exit;
}

$resourceController = new ResourceController();
$resource = $resourceController->getResourceById($_GET['resource_id']);

if ($resource) {
    echo json_encode([
        'success' => true,
        'resource' => $resource
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Resource not found'
    ]);
} 