<?php
include '../_base.php';
require_admin();

$search = clean($_GET['search'] ?? '');
$where = '1=1';
$params = [];

if ($search) {
    $where = '(o.id LIKE ? OR u.name LIKE ? OR u.email LIKE ? OR o.shipping_name LIKE ?)';
    $params = ["%$search%", "%$search%", "%$search%", "%$search%"];
}

$stm = $db->prepare("SELECT o.*, u.name as user_name, u.email as user_email FROM orders o JOIN users u ON o.user_id = u.id WHERE $where ORDER BY o.created_at DESC");
$stm->execute($params);
$orders = $stm->fetchAll();

include '../_head.php';
?>

<section class="brand-header" style="background: #1d1d1f;">
    <div class="container">
        <h1><i class="fa-solid fa-clipboard-list"></i> Order Management</h1>
        <p><?= count($orders) ?> order(s)</p>
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

        <form method="GET" class="admin-search">
            <input type="text" name="search" placeholder="Search by order ID, customer name, email..." value="<?= $search ?>">
            <button type="submit" class="btn btn-primary">Search</button>
            <?php if ($search): ?><a href="<?= $base ?>/admin/orders.php" class="btn btn-outline">Clear</a><?php endif; ?>
        </form>

        <table class="data-table">
            <thead>
                <tr>
                    <th>Order #</th>
                    <th>Customer</th>
                    <th>Total</th>
                    <th>Payment</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $o): ?>
                <tr>
                    <td><strong>#<?= str_pad($o->id, 5, '0', STR_PAD_LEFT) ?></strong></td>
                    <td><?= clean($o->user_name) ?><br><small><?= clean($o->user_email) ?></small></td>
                    <td><?= format_price($o->total) ?></td>
                    <td><?= strtoupper($o->payment_method) ?></td>
                    <td><span class="status-badge status-<?= $o->status ?>"><?= ucfirst($o->status) ?></span></td>
                    <td><?= date('d M Y', strtotime($o->created_at)) ?></td>
                    <td><a href="<?= $base ?>/admin/order-detail.php?id=<?= $o->id ?>" class="btn btn-sm btn-outline">View</a></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>

<?php include '../_foot.php'; ?>
