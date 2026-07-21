<?php
include '../_base.php';
require_admin();

$id = intval($_GET['id'] ?? 0);
$stm = $db->prepare('SELECT o.*, u.name as user_name, u.email as user_email FROM orders o JOIN users u ON o.user_id = u.id WHERE o.id = ?');
$stm->execute([$id]);
$order = $stm->fetch();

if (!$order) {
    flash('error', 'Order not found.');
    redirect('/admin/orders.php');
}

// Update status
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['status'])) {
    $status = clean($_POST['status']);
    $db->prepare('UPDATE orders SET status = ? WHERE id = ?')->execute([$status, $id]);
    flash('success', 'Order status updated.');
    redirect("/admin/order-detail.php?id=$id");
}

$stm = $db->prepare('SELECT oi.*, p.name, p.brand, p.specs FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ?');
$stm->execute([$id]);
$items = $stm->fetchAll();

include '../_head.php';
?>

<section class="brand-header" style="background: #1d1d1f;">
    <div class="container">
        <h1>Order #<?= str_pad($order->id, 5, '0', STR_PAD_LEFT) ?></h1>
        <p><?= date('d M Y, h:i A', strtotime($order->created_at)) ?></p>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="admin-nav">
            <a href="<?= $base ?>/admin/members.php">Members</a>
            <a href="<?= $base ?>/admin/products.php">Products</a>
            <a href="<?= $base ?>/admin/orders.php" class="active">Orders</a>
            <a href="<?= $base ?>/admin/brand-videos.php">Brand Videos</a>
            <a href="<?= $base ?>/admin/analytics.php">Analytics</a>
        </div>

        <div class="checkout-layout">
            <div>
                <h3>Order Items</h3>
                <table class="data-table">
                    <thead><tr><th>Product</th><th>Price</th><th>Qty</th><th>Subtotal</th></tr></thead>
                    <tbody>
                        <?php foreach ($items as $item): ?>
                        <tr>
                            <td><strong><?= clean($item->name) ?></strong><br><small><?= clean($item->brand) ?> | <?= clean($item->specs ?? '') ?></small></td>
                            <td><?= format_price($item->price) ?></td>
                            <td><?= $item->quantity ?></td>
                            <td><?= format_price($item->price * $item->quantity) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr><td colspan="3" style="text-align:right"><strong>Total:</strong></td><td><strong><?= format_price($order->total) ?></strong></td></tr>
                    </tfoot>
                </table>
            </div>

            <div class="checkout-summary">
                <h3>Order Info</h3>
                <div class="summary-items">
                    <div class="summary-item"><div>Customer</div><span><?= clean($order->user_name) ?></span></div>
                    <div class="summary-item"><div>Email</div><span><?= clean($order->user_email) ?></span></div>
                    <div class="summary-item"><div>Ship To</div><span><?= clean($order->shipping_name) ?></span></div>
                    <div class="summary-item"><div>Phone</div><span><?= clean($order->shipping_phone) ?></span></div>
                    <div class="summary-item"><div>Address</div><span><?= clean($order->shipping_address) ?></span></div>
                    <div class="summary-item"><div>Payment</div><span><?= strtoupper($order->payment_method) ?></span></div>
                    <?php if ($order->notes): ?>
                    <div class="summary-item"><div>Notes</div><span><?= clean($order->notes) ?></span></div>
                    <?php endif; ?>
                </div>

                <form method="POST" style="margin-top:20px;">
                    <div class="form-group">
                        <label>Update Status</label>
                        <select name="status" class="sort-select" style="width:100%">
                            <?php foreach (['pending','processing','shipped','delivered','cancelled'] as $s): ?>
                                <option value="<?= $s ?>" <?= $order->status === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary btn-block">Update Status</button>
                </form>
                <a href="<?= $base ?>/receipt.php?id=<?= $order->id ?>" class="btn btn-outline btn-block" style="margin-top:10px">View Receipt</a>
            </div>
        </div>
    </div>
</section>

<?php include '../_foot.php'; ?>
