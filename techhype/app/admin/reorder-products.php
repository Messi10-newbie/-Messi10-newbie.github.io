<?php
include '../_base.php';
require_admin();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Invalid method']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$order = $data['order'] ?? [];

if (empty($order)) {
    echo json_encode(['error' => 'No order data']);
    exit;
}

// Add column if not exists
try {
    $db->exec('ALTER TABLE products ADD COLUMN sort_order INT DEFAULT 0');
} catch (Exception $e) {
    // Column already exists
}

$stm = $db->prepare('UPDATE products SET sort_order = ? WHERE id = ?');
foreach ($order as $position => $id) {
    $stm->execute([$position, intval($id)]);
}

echo json_encode(['success' => true]);
