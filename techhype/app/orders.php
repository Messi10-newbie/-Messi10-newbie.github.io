<?php
include '_base.php';
require_login();

// Handle order cancellation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_order'])) {
    $orderId = intval($_POST['cancel_order']);
    $stm = $db->prepare('SELECT * FROM orders WHERE id = ? AND user_id = ? AND status = "pending"');
    $stm->execute([$orderId, auth()->id]);
    $cancelOrder = $stm->fetch();
    if ($cancelOrder) {
        // Restore stock
        $stm = $db->prepare('SELECT product_id, quantity FROM order_items WHERE order_id = ?');
        $stm->execute([$orderId]);
        $cancelItems = $stm->fetchAll();
        foreach ($cancelItems as $ci) {
            $db->prepare('UPDATE products SET stock = stock + ? WHERE id = ?')->execute([$ci->quantity, $ci->product_id]);
        }
        $db->prepare('UPDATE orders SET status = "cancelled" WHERE id = ?')->execute([$orderId]);

        // Refund points: reverse any earn/redeem for this order
        $stm = $db->prepare('SELECT * FROM points_log WHERE order_id = ? AND user_id = ?');
        $stm->execute([$orderId, auth()->id]);
        $pointsEntries = $stm->fetchAll();
        foreach ($pointsEntries as $pe) {
            $refundPts = -$pe->points; // reverse the transaction
            $refundType = 'refund';
            $desc = 'Refund for cancelled Order #' . str_pad($orderId, 5, '0', STR_PAD_LEFT);
            add_points(auth()->id, $refundPts, $refundType, $desc, $orderId);
        }

        flash('success', 'Order #' . str_pad($orderId, 5, '0', STR_PAD_LEFT) . ' has been cancelled. Points have been adjusted.');
    } else {
        flash('error', 'Order cannot be cancelled.');
    }
    redirect('/orders.php');
}

$stm = $db->prepare('SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC');
$stm->execute([auth()->id]);
$orders = $stm->fetchAll();

include '_head.php';
?>

<section class="brand-header" style="background: #1d1d1f;">
    <div class="container">
        <h1>My Orders</h1>
        <p>Track your order history</p>
    </div>
</section>

<section class="section">
    <div class="container">
        <?php if (empty($orders)): ?>
            <div class="empty-state">
                <i class="fa-solid fa-box-open"></i>
                <h3>No orders yet</h3>
                <p>Start shopping to see your orders here.</p>
                <a href="<?= $base ?>/products.php" class="btn btn-primary">Shop Now</a>
            </div>
        <?php else: ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Order #</th>
                    <th>Date</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $o): ?>
                <tr>
                    <td><strong>#<?= str_pad($o->id, 5, '0', STR_PAD_LEFT) ?></strong></td>
                    <td><?= date('d M Y, h:i A', strtotime($o->created_at)) ?></td>
                    <td><?= format_price($o->total) ?></td>
                    <td><span class="status-badge status-<?= $o->status ?>"><?= ucfirst($o->status) ?></span></td>
                    <td>
                        <a href="<?= $base ?>/order-detail.php?id=<?= $o->id ?>" class="btn btn-sm btn-outline">View</a>
                        <a href="<?= $base ?>/receipt.php?id=<?= $o->id ?>" class="btn btn-sm btn-primary">Receipt</a>
                        <?php if ($o->status === 'pending'): ?>
                        <form method="POST" style="display:inline" onsubmit="return confirm('Are you sure you want to cancel this order?')">
                            <input type="hidden" name="cancel_order" value="<?= $o->id ?>">
                            <button type="submit" class="btn-cancel">Cancel</button>
                        </form>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</section>

<?php include '_foot.php'; ?>
