<?php
include '_base.php';

$id = intval($_POST['id'] ?? 0);
$qty = intval($_POST['qty'] ?? 1);
if ($qty < 1) $qty = 1;

$color      = trim($_POST['selected_color'] ?? '');
$bandColor  = trim($_POST['selected_band_color'] ?? '');
$storage    = trim($_POST['selected_storage'] ?? '');
$price      = trim($_POST['selected_price'] ?? '');

if ($id) {
    if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];

    // Create a unique key for product + color + band color + storage combination
    $cartKey = $id;
    if ($color || $bandColor || $storage) {
        $cartKey = $id . '|' . $color . '|' . $bandColor . '|' . $storage;
    }

    if (!isset($_SESSION['cart'][$cartKey])) {
        $_SESSION['cart'][$cartKey] = [
            'product_id' => $id,
            'qty' => $qty,
            'color' => $color,
            'band_color' => $bandColor,
            'storage' => $storage,
            'price' => $price,
        ];
    } else {
        $_SESSION['cart'][$cartKey]['qty'] += $qty;
    }

    flash('success', 'Product added to cart!');
}

$ref = $_SERVER['HTTP_REFERER'] ?? "$base/products.php";
header("Location: $ref");
exit;
