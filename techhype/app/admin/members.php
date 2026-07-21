<?php
include '../_base.php';
require_admin();

$search = clean($_GET['search'] ?? '');
$where = '1=1';
$params = [];

if ($search) {
    $where = '(name LIKE ? OR email LIKE ? OR phone LIKE ?)';
    $params = ["%$search%", "%$search%", "%$search%"];
}

// Separate admins and customers
$stm = $db->prepare("SELECT * FROM users WHERE $where AND role = 'admin' ORDER BY created_at DESC");
$stm->execute($params);
$admins = $stm->fetchAll();

$stm = $db->prepare("SELECT * FROM users WHERE $where AND role = 'member' ORDER BY created_at DESC");
$stm->execute($params);
$customers = $stm->fetchAll();

$members = array_merge($admins, $customers);

include '../_head.php';
?>

<section class="brand-header" style="background: #1d1d1f;">
    <div class="container">
        <h1><i class="fa-solid fa-users"></i> Member Management</h1>
        <p><?= count($members) ?> member(s) found</p>
    </div>
</section>

<section class="section">
    <div class="container">
        <!-- Admin Nav -->
        <div class="admin-nav">
            <a href="<?= $base ?>/admin/members.php" class="active">Members</a>
            <a href="<?= $base ?>/admin/products.php">Products</a>
            <a href="<?= $base ?>/admin/orders.php">Orders</a>
            <a href="<?= $base ?>/admin/brand-videos.php">Brand Videos</a>
            <a href="<?= $base ?>/admin/analytics.php">Analytics</a>
        </div>

        <!-- Search -->
        <form method="GET" class="admin-search">
            <input type="text" name="search" placeholder="Search members by name, email, phone..." value="<?= $search ?>">
            <button type="submit" class="btn btn-primary">Search</button>
            <?php if ($search): ?><a href="<?= $base ?>/admin/members.php" class="btn btn-outline">Clear</a><?php endif; ?>
        </form>

        <!-- Admins Section -->
        <?php if ($admins): ?>
        <h3 style="margin-bottom:14px;font-size:16px;font-weight:600;"><i class="fa-solid fa-shield" style="color:var(--primary);margin-right:6px;"></i> Admins (<?= count($admins) ?>)</h3>
        <table class="data-table" style="margin-bottom:40px;">
            <thead>
                <tr>
                    <th>Photo</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Status</th>
                    <th>Joined</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($admins as $m): ?>
                <tr>
                    <td><img src="<?= $base ?>/uploads/<?= $m->photo ?>" class="table-photo" alt=""></td>
                    <td><strong><?= clean($m->name) ?></strong></td>
                    <td><?= clean($m->email) ?></td>
                    <td><span class="status-badge status-<?= $m->status ?>"><?= ucfirst($m->status) ?></span></td>
                    <td><?= date('d M Y', strtotime($m->created_at)) ?></td>
                    <td><a href="<?= $base ?>/admin/member-detail.php?id=<?= $m->id ?>" class="btn btn-sm btn-outline">View</a></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>

        <!-- Customers Section -->
        <h3 style="margin-bottom:14px;font-size:16px;font-weight:600;"><i class="fa-solid fa-users" style="color:var(--primary);margin-right:6px;"></i> Customers (<?= count($customers) ?>)</h3>
        <?php if ($customers): ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Photo</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Verified</th>
                    <th>Status</th>
                    <th>Joined</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($customers as $m): ?>
                <tr>
                    <td><img src="<?= $base ?>/uploads/<?= $m->photo ?>" class="table-photo" alt=""></td>
                    <td><strong><?= clean($m->name) ?></strong></td>
                    <td><?= clean($m->email) ?></td>
                    <td>
                        <?php if ($m->email_verified): ?>
                            <span class="status-badge status-active">Verified</span>
                        <?php else: ?>
                            <span class="status-badge status-inactive">Unverified</span>
                        <?php endif; ?>
                    </td>
                    <td><span class="status-badge status-<?= $m->status ?>"><?= ucfirst($m->status) ?></span></td>
                    <td><?= date('d M Y', strtotime($m->created_at)) ?></td>
                    <td>
                        <div style="display:flex;gap:6px;align-items:center;">
                            <a href="<?= $base ?>/admin/member-detail.php?id=<?= $m->id ?>" class="btn btn-sm btn-outline">View</a>
                            <?php if ($m->status === 'blocked'): ?>
                                <form method="POST" action="<?= $base ?>/admin/member-detail.php?id=<?= $m->id ?>" style="display:inline;">
                                    <input type="hidden" name="toggle_block" value="1">
                                    <button type="submit" class="btn btn-sm" style="background:#27ae60;color:#fff;border:none;padding:6px 12px;border-radius:6px;font-size:12px;cursor:pointer;" title="Unblock">
                                        <i class="fa-solid fa-lock-open"></i>
                                    </button>
                                </form>
                            <?php elseif ($m->id !== auth()->id): ?>
                                <form method="POST" action="<?= $base ?>/admin/member-detail.php?id=<?= $m->id ?>" style="display:inline;" onsubmit="return confirm('Block this user?')">
                                    <input type="hidden" name="toggle_block" value="1">
                                    <button type="submit" class="btn btn-sm" style="background:#e74c3c;color:#fff;border:none;padding:6px 12px;border-radius:6px;font-size:12px;cursor:pointer;" title="Block">
                                        <i class="fa-solid fa-ban"></i>
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
        <div class="empty-state">
            <i class="fa-solid fa-users"></i>
            <h3>No customers yet</h3>
            <p>Customers will appear here after they register.</p>
        </div>
        <?php endif; ?>
    </div>
</section>

<?php include '../_foot.php'; ?>
