<?php
include '../_base.php';
require_admin();

$id = intval($_GET['id'] ?? 0);
$stm = $db->prepare('SELECT * FROM users WHERE id = ?');
$stm->execute([$id]);
$member = $stm->fetch();

if (!$member) {
    flash('error', 'Member not found.');
    redirect('/admin/members.php');
}

// Handle block/unblock
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_block'])) {
    if ($member->id == auth()->id) {
        flash('error', 'You cannot block your own account.');
    } else {
        $newStatus = $member->status === 'blocked' ? 'active' : 'blocked';
        $stm = $db->prepare('UPDATE users SET status = ? WHERE id = ?');
        $stm->execute([$newStatus, $id]);

        // If blocking, clear their session (they'll be forced out on next request)
        if ($newStatus === 'blocked') {
            // Clear login attempts too
            $stm2 = $db->prepare('DELETE FROM login_attempts WHERE email = ?');
            $stm2->execute([$member->email]);
        }

        flash('success', $newStatus === 'blocked' ? 'User has been blocked.' : 'User has been unblocked.');
    }
    redirect("/admin/member-detail.php?id=$id");
}

// Update member role/status
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_member'])) {
    $role   = clean($_POST['role'] ?? $member->role);
    $status = clean($_POST['status'] ?? $member->status);

    $stm = $db->prepare('UPDATE users SET role = ?, status = ? WHERE id = ?');
    $stm->execute([$role, $status, $id]);

    flash('success', 'Member updated.');
    redirect("/admin/member-detail.php?id=$id");
}

// Get member orders
$stm = $db->prepare('SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC');
$stm->execute([$id]);
$orders = $stm->fetchAll();

// Get recent login attempts
$stm = $db->prepare('SELECT COUNT(*) FROM login_attempts WHERE email = ? AND attempted_at > DATE_SUB(NOW(), INTERVAL 15 MINUTE)');
$stm->execute([$member->email]);
$recentAttempts = (int)$stm->fetchColumn();

include '../_head.php';
?>

<section class="brand-header" style="background: #1d1d1f;">
    <div class="container">
        <h1>Member Detail</h1>
        <p><?= clean($member->name) ?></p>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="admin-nav">
            <a href="<?= $base ?>/admin/members.php" class="active">Members</a>
            <a href="<?= $base ?>/admin/products.php">Products</a>
            <a href="<?= $base ?>/admin/orders.php">Orders</a>
            <a href="<?= $base ?>/admin/brand-videos.php">Brand Videos</a>
            <a href="<?= $base ?>/admin/analytics.php">Analytics</a>
        </div>

        <div class="profile-layout">
            <div class="profile-sidebar">
                <div class="profile-photo">
                    <img src="<?= $base ?>/uploads/<?= $member->photo ?>" alt="">
                    <h3><?= clean($member->name) ?></h3>
                    <p><?= clean($member->email) ?></p>
                    <p><?= clean($member->phone ?? 'No phone') ?></p>
                    <?php if ($member->status === 'blocked'): ?>
                        <div style="margin-top:10px;padding:8px 14px;background:#ffe0e0;color:#c0392b;border-radius:8px;font-size:13px;font-weight:600;">
                            <i class="fa-solid fa-ban"></i> Account Blocked
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Block / Unblock Button -->
                <?php if ($member->role !== 'admin' || $member->id !== auth()->id): ?>
                <form method="POST" style="padding:0 15px 10px;" onsubmit="return confirm('<?= $member->status === 'blocked' ? 'Unblock this user?' : 'Block this user? They will not be able to login.' ?>')">
                    <input type="hidden" name="toggle_block" value="1">
                    <?php if ($member->status === 'blocked'): ?>
                        <button type="submit" class="btn btn-primary btn-block" style="background:#27ae60;border-color:#27ae60;">
                            <i class="fa-solid fa-lock-open"></i> Unblock User
                        </button>
                    <?php else: ?>
                        <button type="submit" class="btn btn-primary btn-block" style="background:#e74c3c;border-color:#e74c3c;">
                            <i class="fa-solid fa-ban"></i> Block User
                        </button>
                    <?php endif; ?>
                </form>
                <?php endif; ?>

                <form method="POST" style="padding:0 15px 15px;">
                    <input type="hidden" name="update_member" value="1">
                    <div class="form-group">
                        <label>Role</label>
                        <select name="role" class="sort-select" style="width:100%">
                            <option value="member" <?= $member->role === 'member' ? 'selected' : '' ?>>Member</option>
                            <option value="admin" <?= $member->role === 'admin' ? 'selected' : '' ?>>Admin</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Status</label>
                        <select name="status" class="sort-select" style="width:100%">
                            <option value="active" <?= $member->status === 'active' ? 'selected' : '' ?>>Active</option>
                            <option value="inactive" <?= $member->status === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                            <option value="blocked" <?= $member->status === 'blocked' ? 'selected' : '' ?>>Blocked</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary btn-block">Update</button>
                </form>
            </div>

            <div class="profile-content">
                <h2>Member Info</h2>
                <table class="data-table" style="margin-bottom:30px;">
                    <tr><td><strong>ID</strong></td><td><?= $member->id ?></td></tr>
                    <tr><td><strong>Name</strong></td><td><?= clean($member->name) ?></td></tr>
                    <tr><td><strong>Email</strong></td><td><?= clean($member->email) ?></td></tr>
                    <tr><td><strong>Phone</strong></td><td><?= clean($member->phone ?? '-') ?></td></tr>
                    <tr><td><strong>Address</strong></td><td><?= clean($member->address ?? '-') ?></td></tr>
                    <tr><td><strong>Status</strong></td><td>
                        <span class="status-badge status-<?= $member->status ?>"><?= ucfirst($member->status) ?></span>
                    </td></tr>
                    <tr><td><strong>Email Verified</strong></td><td>
                        <?php if ($member->email_verified): ?>
                            <span class="status-badge status-active">Verified</span>
                        <?php else: ?>
                            <span class="status-badge status-inactive">Unverified</span>
                        <?php endif; ?>
                    </td></tr>
                    <tr><td><strong>Failed Logins (15min)</strong></td><td>
                        <?php if ($recentAttempts >= 3): ?>
                            <span style="color:#e74c3c;font-weight:600;"><i class="fa-solid fa-lock"></i> <?= $recentAttempts ?> (Temporarily Locked)</span>
                        <?php elseif ($recentAttempts > 0): ?>
                            <span style="color:#f39c12;font-weight:600;"><?= $recentAttempts ?> attempt(s)</span>
                        <?php else: ?>
                            <span style="color:#27ae60;">None</span>
                        <?php endif; ?>
                    </td></tr>
                    <tr><td><strong>Joined</strong></td><td><?= date('d M Y, h:i A', strtotime($member->created_at)) ?></td></tr>
                </table>

                <h2>Orders (<?= count($orders) ?>)</h2>
                <?php if ($orders): ?>
                <table class="data-table">
                    <thead><tr><th>Order #</th><th>Date</th><th>Total</th><th>Status</th><th>Action</th></tr></thead>
                    <tbody>
                        <?php foreach ($orders as $o): ?>
                        <tr>
                            <td>#<?= str_pad($o->id, 5, '0', STR_PAD_LEFT) ?></td>
                            <td><?= date('d M Y', strtotime($o->created_at)) ?></td>
                            <td><?= format_price($o->total) ?></td>
                            <td><span class="status-badge status-<?= $o->status ?>"><?= ucfirst($o->status) ?></span></td>
                            <td><a href="<?= $base ?>/admin/order-detail.php?id=<?= $o->id ?>" class="btn btn-sm btn-outline">View</a></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php else: ?>
                    <p style="color:#888;">No orders yet.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<?php include '../_foot.php'; ?>
