<?php
include '_base.php';

if (!is_login()) {
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH'])) {
        http_response_code(401);
        echo json_encode(['error' => 'login_required']);
        exit;
    }
    flash('error', 'Please login to use wishlist.');
    redirect('/login.php');
}

$productId = intval($_POST['product_id'] ?? $_GET['id'] ?? 0);
if (!$productId) redirect('/products.php');

$userId = auth()->id;

// Check if already in wishlist
$stm = $db->prepare('SELECT id FROM wishlist WHERE user_id = ? AND product_id = ?');
$stm->execute([$userId, $productId]);
$exists = $stm->fetch();

if ($exists) {
    $db->prepare('DELETE FROM wishlist WHERE user_id = ? AND product_id = ?')->execute([$userId, $productId]);
    $action = 'removed';
} else {
    $db->prepare('INSERT INTO wishlist (user_id, product_id) VALUES (?, ?)')->execute([$userId, $productId]);
    $action = 'added';
}

// AJAX response
if (!empty($_SERVER['HTTP_X_REQUESTED_WITH'])) {
    $stm = $db->prepare('SELECT COUNT(*) FROM wishlist WHERE user_id = ?');
    $stm->execute([$userId]);
    $count = $stm->fetchColumn();
    echo json_encode(['action' => $action, 'count' => $count]);
    exit;
}

flash('success', $action === 'added' ? 'Added to wishlist!' : 'Removed from wishlist.');
redirect($_SERVER['HTTP_REFERER'] ?? '/products.php');
