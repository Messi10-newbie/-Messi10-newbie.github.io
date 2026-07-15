<?php
session_start();
require '../../_base.php';
require '../../_functions.php';

header('Content-Type: application/json');

$stallId = (int)($_POST['stall_id'] ?? 0);
if ($stallId <= 0) {
    echo json_encode(['ok' => false]);
    exit;
}

$isOpen = toggleStallOpen($pdo, $stallId);
echo json_encode(['ok' => true, 'is_open' => $isOpen]);
