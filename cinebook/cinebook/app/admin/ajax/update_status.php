<?php
session_start();
require '../../_base.php';
require '../../_functions.php';
require '../../_mail.php';

header('Content-Type: application/json');

$orderId = (int)($_POST['order_id'] ?? 0);
$status  = $_POST['status'] ?? '';
$allowed = ['pending', 'preparing', 'delayed', 'ready', 'collected', 'cancelled'];

if ($orderId <= 0 || !in_array($status, $allowed, true)) {
    echo json_encode(['ok' => false, 'msg' => 'Invalid input.']);
    exit;
}

// Only email when the status actually changes to "ready" (avoids duplicate emails)
$prev = $pdo->prepare("SELECT order_status FROM orders WHERE order_id = ?");
$prev->execute([$orderId]);
$prevStatus = $prev->fetchColumn();

$stmt = $pdo->prepare("UPDATE orders SET order_status = ? WHERE order_id = ?");
$stmt->execute([$status, $orderId]);

$emailSent = false;
if ($status === 'ready' && $prevStatus !== 'ready') {
    $order = getOrder($pdo, $orderId);
    if ($order) {
        $emailSent = sendOrderReadyEmail($order);
    }
}

echo json_encode(['ok' => true, 'status' => $status, 'email_sent' => $emailSent]);
