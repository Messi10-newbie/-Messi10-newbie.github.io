<?php
include '_base.php';
require_login();

$id = intval($_GET['id'] ?? 0);

// Allow admin or owner to view
if (is_admin()) {
    $stm = $db->prepare('SELECT o.*, u.name as user_name, u.email as user_email FROM orders o JOIN users u ON o.user_id = u.id WHERE o.id = ?');
    $stm->execute([$id]);
} else {
    $stm = $db->prepare('SELECT o.*, u.name as user_name, u.email as user_email FROM orders o JOIN users u ON o.user_id = u.id WHERE o.id = ? AND o.user_id = ?');
    $stm->execute([$id, auth()->id]);
}
$order = $stm->fetch();

if (!$order) {
    flash('error', 'Order not found.');
    redirect('/orders.php');
}

$stm = $db->prepare('SELECT oi.*, p.name, p.brand, p.specs FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ?');
$stm->execute([$order->id]);
$items = $stm->fetchAll();

// Calculate items subtotal
$itemsSubtotal = 0;
foreach ($items as $item) {
    $itemsSubtotal += $item->price * $item->quantity;
}

// Get points log for this order
$stm = $db->prepare('SELECT * FROM points_log WHERE order_id = ? ORDER BY id ASC');
$stm->execute([$order->id]);
$pointsLogs = $stm->fetchAll();

$pointsRedeemed = 0;
$pointsEarned = 0;
foreach ($pointsLogs as $pl) {
    if ($pl->type === 'redeem') $pointsRedeemed = abs($pl->points);
    if ($pl->type === 'earn') $pointsEarned = $pl->points;
}
$pointsDiscount = points_to_rm($pointsRedeemed);

// Get voucher used for this order
$stm = $db->prepare('SELECT * FROM vouchers WHERE used_order_id = ? AND status = "used"');
$stm->execute([$order->id]);
$usedVoucher = $stm->fetch();

$voucherDiscount = 0;
if ($usedVoucher) {
    // Calculate what the voucher discount was: subtotal - points discount - final total
    $voucherDiscount = round($itemsSubtotal - $pointsDiscount - $order->total, 2);
    if ($voucherDiscount < 0) $voucherDiscount = 0;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt #<?= str_pad($order->id, 5, '0', STR_PAD_LEFT) ?> - TechHype</title>
    <link rel="stylesheet" href="<?= $base ?>/css/app.css">
    <style>
        body { background: #f5f5f7; }
        .receipt { max-width: 700px; margin: 30px auto; background: #fff; padding: 40px; border-radius: 12px; box-shadow: 0 2px 20px rgba(0,0,0,0.08); }
        .receipt-header { text-align: center; border-bottom: 2px solid #eee; padding-bottom: 20px; margin-bottom: 20px; }
        .receipt-header h1 { font-size: 28px; color: #1d1d1f; }
        .receipt-header h1 span { color: #0071e3; }
        .receipt-header p { color: #888; font-size: 13px; }
        .receipt-info { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px; }
        .receipt-info div { font-size: 13px; }
        .receipt-info strong { display: block; margin-bottom: 3px; }
        .receipt table { width: 100%; border-collapse: collapse; margin: 20px 0; font-size: 14px; }
        .receipt th, .receipt td { padding: 10px; text-align: left; border-bottom: 1px solid #eee; }
        .receipt th { background: #f5f5f7; font-weight: 600; }
        .receipt-total { text-align: right; font-size: 20px; font-weight: 700; color: #0071e3; margin: 15px 0; }
        .receipt-footer { text-align: center; font-size: 12px; color: #999; margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee; }
        .print-btn { text-align: center; margin: 20px 0; }
        @media print { .print-btn { display: none; } body { background: #fff; } .receipt { box-shadow: none; margin: 0; } }
    </style>
</head>
<body>
    <div class="receipt">
        <div class="receipt-header">
            <h1>Tech<span>Hype</span></h1>
            <p>123 Tech Street, KL | +60 12-345 6789 | support@techhype.com</p>
            <h2 style="margin-top:15px;font-size:18px;">E-Receipt</h2>
            <p>Order #<?= str_pad($order->id, 5, '0', STR_PAD_LEFT) ?> | <?= date('d M Y, h:i A', strtotime($order->created_at)) ?></p>
        </div>

        <div class="receipt-info">
            <div>
                <strong>Bill To:</strong>
                <?= clean($order->user_name) ?><br>
                <?= clean($order->user_email) ?>
            </div>
            <div>
                <strong>Ship To:</strong>
                <?= clean($order->shipping_name) ?><br>
                <?= clean($order->shipping_phone) ?><br>
                <?= clean($order->shipping_address) ?>
            </div>
        </div>

        <table>
            <thead>
                <tr><th>#</th><th>Product</th><th>Price</th><th>Qty</th><th>Subtotal</th></tr>
            </thead>
            <tbody>
                <?php $n = 1; foreach ($items as $item): ?>
                <tr>
                    <td><?= $n++ ?></td>
                    <td><?= clean($item->name) ?> <small style="color:#888">(<?= clean($item->brand) ?>)</small></td>
                    <td><?= format_price($item->price) ?></td>
                    <td><?= $item->quantity ?></td>
                    <td><?= format_price($item->price * $item->quantity) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Totals Breakdown -->
        <div style="border-top:2px solid #eee;padding-top:15px;margin-top:10px;">
            <div style="display:flex;justify-content:space-between;font-size:14px;padding:6px 0;">
                <span>Subtotal</span>
                <span><?= format_price($itemsSubtotal) ?></span>
            </div>

            <?php if ($usedVoucher && $voucherDiscount > 0): ?>
            <div style="display:flex;justify-content:space-between;font-size:14px;padding:6px 0;color:#27ae60;">
                <span><i class="fa-solid fa-ticket" style="margin-right:4px;"></i> Voucher (<?= $usedVoucher->code ?>)
                    <?php if ($usedVoucher->type === 'percent'): ?>
                        — <?= intval($usedVoucher->value) ?>% OFF
                    <?php elseif ($usedVoucher->type === 'shipping'): ?>
                        — Free Shipping
                    <?php endif; ?>
                </span>
                <span>-<?= format_price($voucherDiscount) ?></span>
            </div>
            <?php endif; ?>

            <?php if ($pointsRedeemed > 0): ?>
            <div style="display:flex;justify-content:space-between;font-size:14px;padding:6px 0;color:#e67e22;">
                <span><i class="fa-solid fa-coins" style="margin-right:4px;"></i> Points Redeemed (<?= number_format($pointsRedeemed) ?> pts)</span>
                <span>-<?= format_price($pointsDiscount) ?></span>
            </div>
            <?php endif; ?>

            <div style="display:flex;justify-content:space-between;font-size:20px;font-weight:700;color:#0071e3;padding:12px 0 6px;border-top:2px solid #eee;margin-top:8px;">
                <span>Total Paid</span>
                <span><?= format_price($order->total) ?></span>
            </div>

            <?php if ($pointsEarned > 0): ?>
            <div style="text-align:center;background:#fff9e6;border:1px solid #f0d78c;border-radius:8px;padding:10px;margin-top:12px;font-size:13px;">
                <i class="fa-solid fa-star" style="color:#f39c12;"></i>
                You earned <strong style="color:#f39c12;"><?= number_format($pointsEarned) ?> points</strong> from this order!
            </div>
            <?php endif; ?>
        </div>

        <div style="font-size:13px;color:#666;margin-top:20px;">
            <p><strong>Payment Method:</strong> <?= strtoupper($order->payment_method) ?></p>
            <p><strong>Status:</strong> <?= ucfirst($order->status) ?></p>
            <?php if ($order->notes): ?>
            <p><strong>Notes:</strong> <?= clean($order->notes) ?></p>
            <?php endif; ?>
        </div>

        <div class="receipt-footer">
            <p>Thank you for shopping with TechHype!</p>
            <p>This is a computer-generated receipt. No signature required.</p>
        </div>

        <div class="print-btn">
            <button onclick="window.print()" class="btn btn-primary">Print Receipt</button>
            <a href="<?= $base ?>/orders.php" class="btn btn-outline">Back to Orders</a>
        </div>
    </div>
</body>
</html>
