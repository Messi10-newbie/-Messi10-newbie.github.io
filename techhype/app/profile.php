<?php
include '_base.php';
require_login();

$user = auth();
$errors = [];
$tab = $_GET['tab'] ?? 'profile';

// Update Profile
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $name    = clean($_POST['name'] ?? '');
    $phone   = clean($_POST['phone'] ?? '');
    $address = clean($_POST['address'] ?? '');

    if (!$name) $errors[] = 'Name is required.';

    // Photo upload
    $photo = $user->photo;
    if (isset($_FILES['photo']) && $_FILES['photo']['size'] > 0) {
        $uploaded = upload_photo($_FILES['photo']);
        if ($uploaded) {
            delete_photo($user->photo);
            $photo = $uploaded;
        } else {
            $errors[] = 'Invalid photo format.';
        }
    }

    if (empty($errors)) {
        $stm = $db->prepare('UPDATE users SET name = ?, phone = ?, address = ?, photo = ? WHERE id = ?');
        $stm->execute([$name, $phone, $address, $photo, $user->id]);

        // Refresh session
        $stm = $db->prepare('SELECT * FROM users WHERE id = ?');
        $stm->execute([$user->id]);
        $_SESSION['user'] = $stm->fetch();

        flash('success', 'Profile updated successfully!');
        redirect('/profile.php');
    }
    $tab = 'profile';
}

// Update Password
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_password'])) {
    $current = $_POST['current_password'] ?? '';
    $new     = $_POST['new_password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if (!password_verify($current, $user->password)) $errors[] = 'Current password is incorrect.';
    if (strlen($new) < 6) $errors[] = 'New password must be at least 6 characters.';
    if ($new !== $confirm) $errors[] = 'Passwords do not match.';

    if (empty($errors)) {
        $hash = password_hash($new, PASSWORD_DEFAULT);
        $stm = $db->prepare('UPDATE users SET password = ? WHERE id = ?');
        $stm->execute([$hash, $user->id]);

        // Refresh session
        $stm = $db->prepare('SELECT * FROM users WHERE id = ?');
        $stm->execute([$user->id]);
        $_SESSION['user'] = $stm->fetch();

        flash('success', 'Password updated successfully!');
        redirect('/profile.php?tab=password');
    }
    $tab = 'password';
}

include '_head.php';
$user = auth(); // refresh
?>

<section class="section">
    <div class="container">
        <div class="profile-layout">
            <!-- Sidebar -->
            <div class="profile-sidebar">
                <div class="profile-photo">
                    <img src="<?= $base ?>/uploads/<?= $user->photo ?>" alt="<?= clean($user->name) ?>">
                    <h3><?= clean($user->name) ?></h3>
                    <p><?= clean($user->email) ?></p>
                </div>
                <ul class="profile-menu">
                    <li><a href="?tab=profile" class="<?= $tab === 'profile' ? 'active' : '' ?>"><i class="fa-solid fa-user"></i> Profile</a></li>
                    <li><a href="?tab=password" class="<?= $tab === 'password' ? 'active' : '' ?>"><i class="fa-solid fa-lock"></i> Password</a></li>
                    <li><a href="<?= $base ?>/rewards.php"><i class="fa-solid fa-coins"></i> Rewards <span style="background:var(--gradient-primary);color:#fff;font-size:10px;padding:2px 8px;border-radius:20px;margin-left:6px;"><?= number_format(get_points_balance()) ?> pts</span></a></li>
                    <li><a href="<?= $base ?>/orders.php"><i class="fa-solid fa-box"></i> My Orders</a></li>
                    <li><a href="<?= $base ?>/logout.php"><i class="fa-solid fa-right-from-bracket"></i> Logout</a></li>
                </ul>
            </div>

            <!-- Content -->
            <div class="profile-content">
                <?php if ($errors): ?>
                    <div class="alert alert-error">
                        <?php foreach ($errors as $e): ?>
                            <div><?= $e ?></div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <?php if ($tab === 'profile'): ?>
                <h2>Update Profile</h2>
                <form method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label>Full Name *</label>
                        <input type="text" name="name" required value="<?= clean($user->name) ?>">
                    </div>
                    <div class="form-group">
                        <label>Email (cannot change)</label>
                        <input type="email" value="<?= clean($user->email) ?>" disabled>
                    </div>
                    <div class="form-group">
                        <label>Phone</label>
                        <input type="text" name="phone" value="<?= clean($user->phone ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label>Address</label>
                        <textarea name="address" rows="3"><?= clean($user->address ?? '') ?></textarea>
                    </div>
                    <div class="form-group">
                        <label>Profile Photo</label>
                        <input type="file" name="photo" accept="image/*">
                        <small>Current: <?= $user->photo ?></small>
                    </div>
                    <button type="submit" name="update_profile" class="btn btn-primary">Update Profile</button>
                </form>

                <?php elseif ($tab === 'password'): ?>
                <h2>Change Password</h2>
                <form method="POST">
                    <div class="form-group">
                        <label>Current Password *</label>
                        <input type="password" name="current_password" required>
                    </div>
                    <div class="form-group">
                        <label>New Password *</label>
                        <input type="password" name="new_password" required minlength="6">
                    </div>
                    <div class="form-group">
                        <label>Confirm New Password *</label>
                        <input type="password" name="confirm_password" required>
                    </div>
                    <button type="submit" name="update_password" class="btn btn-primary">Update Password</button>
                </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<?php include '_foot.php'; ?>
