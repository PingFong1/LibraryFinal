<?php
require_once '../config/Database.php';
require_once 'ResourceController.php';

if (isset($_GET['year'])) {
    $resourceController = new ResourceController();
    $monthlyBorrowings = $resourceController->getMonthlyBorrowings($_GET['year']);
    echo json_encode($monthlyBorrowings);
} 