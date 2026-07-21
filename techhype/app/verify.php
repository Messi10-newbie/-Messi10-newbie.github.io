<?php
include '_base.php';

$token = $_GET['token'] ?? '';
$message = '';
$success = false;

if ($token) {
    $stm = $db->prepare('SELECT * FROM users WHERE verify_token = ? AND email_verified = 0');
    $stm->execute([$token]);
    $user = $stm->fetch();

    if ($user) {
        $db->prepare('UPDATE users SET email_verified = 1, verify_token = NULL WHERE id = ?')->execute([$user->id]);
        $success = true;
        $message = 'Your email has been verified successfully! You can now login.';
    } else {
        $message = 'Invalid or expired verification link.';
    }
} else {
    $message = 'No verification token provided.';
}

include '_head.php';
?>

<section class="section">
    <div class="container">
        <div class="auth-card" style="text-align:center;">
            <?php if ($success): ?>
                <i class="fa-solid fa-circle-check" style="font-size:60px;color:#34c759;margin-bottom:20px;"></i>
                <h2>Email Verified!</h2>
                <p style="color:#555;margin:15px 0;"><?= $message ?></p>
                <a href="<?= $base ?>/login.php" class="btn btn-primary">Login Now</a>
            <?php else: ?>
                <i class="fa-solid fa-circle-xmark" style="font-size:60px;color:#ff3b30;margin-bottom:20px;"></i>
                <h2>Verification Failed</h2>
                <p style="color:#555;margin:15px 0;"><?= $message ?></p>
                <a href="<?= $base ?>/register.php" class="btn btn-primary">Register Again</a>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php include '_foot.php'; ?>
