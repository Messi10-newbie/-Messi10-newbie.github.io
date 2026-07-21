<?php
include '_base.php';

if (is_login()) redirect('/');

$step = 'email'; // email -> reset
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['email']) && !isset($_POST['token'])) {
        // Step 1: Generate token
        $email = clean($_POST['email'] ?? '');
        $stm = $db->prepare('SELECT id FROM users WHERE email = ?');
        $stm->execute([$email]);
        $user = $stm->fetch();

        if ($user) {
            $token = bin2hex(random_bytes(16));
            $stm = $db->prepare('UPDATE users SET reset_token = ? WHERE id = ?');
            $stm->execute([$token, $user->id]);
            $step = 'reset';
            $_SESSION['reset_token'] = $token;
            $_SESSION['reset_email'] = $email;
        } else {
            $errors[] = 'Email not found.';
        }
    } elseif (isset($_POST['token'])) {
        // Step 2: Reset password
        $token = $_POST['token'] ?? '';
        $password = $_POST['password'] ?? '';
        $confirm = $_POST['confirm'] ?? '';

        if (strlen($password) < 6) $errors[] = 'Password must be at least 6 characters.';
        if ($password !== $confirm) $errors[] = 'Passwords do not match.';

        if (empty($errors)) {
            $stm = $db->prepare('SELECT id FROM users WHERE reset_token = ? AND reset_token IS NOT NULL');
            $stm->execute([$token]);
            $user = $stm->fetch();

            if ($user) {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $stm = $db->prepare('UPDATE users SET password = ?, reset_token = NULL WHERE id = ?');
                $stm->execute([$hash, $user->id]);
                unset($_SESSION['reset_token'], $_SESSION['reset_email']);
                flash('success', 'Password reset successful! Please login.');
                redirect('/login.php');
            } else {
                $errors[] = 'Invalid reset token.';
            }
        }
        $step = 'reset';
    }
}

if (isset($_SESSION['reset_token'])) {
    $step = 'reset';
}

include '_head.php';
?>

<section class="section">
    <div class="container">
        <div class="auth-card">
            <h2>Reset Password</h2>

            <?php if ($errors): ?>
                <div class="alert alert-error">
                    <?php foreach ($errors as $e): ?>
                        <div><?= $e ?></div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php if ($step === 'email'): ?>
                <p class="auth-subtitle">Enter your email to reset your password</p>
                <form method="POST">
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email" required>
                    </div>
                    <button type="submit" class="btn btn-primary btn-block">Continue</button>
                </form>
            <?php else: ?>
                <p class="auth-subtitle">Enter your new password</p>
                <form method="POST">
                    <input type="hidden" name="token" value="<?= $_SESSION['reset_token'] ?? '' ?>">
                    <div class="form-group">
                        <label>New Password</label>
                        <input type="password" name="password" required minlength="6">
                    </div>
                    <div class="form-group">
                        <label>Confirm Password</label>
                        <input type="password" name="confirm" required>
                    </div>
                    <button type="submit" class="btn btn-primary btn-block">Reset Password</button>
                </form>
            <?php endif; ?>

            <div class="auth-links">
                <a href="<?= $base ?>/login.php">Back to Login</a>
            </div>
        </div>
    </div>
</section>

<?php include '_foot.php'; ?>
