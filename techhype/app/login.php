<?php
include '_base.php';

if (is_login()) redirect('/');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = clean($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    // Check if login is temporarily blocked
    if (is_login_blocked($email)) {
        $remaining = get_block_remaining($email);
        $mins = ceil($remaining / 60);
        flash('error', "Too many failed attempts. Please try again in $mins minute(s).");
    } else {
        // Check for blocked account first (regardless of password)
        $stm = $db->prepare('SELECT * FROM users WHERE email = ?');
        $stm->execute([$email]);
        $user = $stm->fetch();

        if ($user && $user->status === 'blocked') {
            flash('error', 'Your account has been blocked. Please contact support.');
        } elseif ($user && $user->status === 'active' && password_verify($password, $user->password)) {
            // Check if email is verified
            if (!$user->email_verified && $user->role !== 'admin') {
                flash('error', 'Please verify your email before logging in. Check your inbox for the verification link.');
            } else {
                clear_failed_attempts($email);
                $_SESSION['user'] = $user;
                flash('success', 'Welcome back, ' . $user->name . '!');
                redirect(is_admin() ? '/admin/members.php' : '/');
            }
        } else {
            record_failed_attempt($email);
            $attemptsLeft = 3 - get_failed_attempts($email);
            if ($attemptsLeft <= 0) {
                flash('error', 'Too many failed attempts. Your login has been temporarily blocked for 15 minutes.');
            } else {
                flash('error', "Invalid email or password. $attemptsLeft attempt(s) remaining.");
            }
        }
    }
}

include '_head.php';
?>

<section class="section">
    <div class="container">
        <div class="auth-card">
            <h2>Login</h2>
            <p class="auth-subtitle">Welcome back to TechHype</p>
            <form method="POST">
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" required value="<?= clean($_POST['email'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" required>
                </div>
                <button type="submit" class="btn btn-primary btn-block">Login</button>
            </form>
            <div class="auth-links">
                <a href="<?= $base ?>/reset.php">Forgot Password?</a>
                <a href="<?= $base ?>/resend-verify.php">Resend Verification Email</a>
                <span>Don't have an account? <a href="<?= $base ?>/register.php">Register</a></span>
            </div>
        </div>
    </div>
</section>

<?php include '_foot.php'; ?>
