<?php
include '../_base.php';
$brand = 'Google';
$brand_desc = 'Google Pixel phones with the best of Google built in.';
$brand_color = '#4285f4';

$stm = $db->prepare("SELECT * FROM products WHERE brand = ? AND status = 'active' ORDER BY sort_order ASC, created_at DESC");
$stm->execute([$brand]);
$rows = $stm->fetchAll();

$products = [];
foreach ($rows as $p) {
    $colors = json_decode($p->colors ?? '[]', true) ?: [];
    $gallery = json_decode($p->gallery ?? '[]', true) ?: [];
    $products[] = [
        'name'   => $p->name,
        'cat'    => ucfirst($p->category),
        'specs'  => $p->specs ?? '',
        'price'  => 'RM ' . number_format($p->price, 2),
        'sale'   => $p->sale_price ? 'RM ' . number_format($p->sale_price, 2) : '',
        'badge'  => $p->sale_price ? '-' . round((1 - $p->sale_price / $p->price) * 100) . '%' : '',
        'images' => $gallery,
        'colors' => $colors,
    ];
}

include '_brand_template.php';
?>
