<?php
include '_base.php';
include 'lib/mail.php';

if (is_login()) redirect('/');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = clean($_POST['email'] ?? '');

    $stm = $db->prepare('SELECT * FROM users WHERE email = ? AND email_verified = 0');
    $stm->execute([$email]);
    $user = $stm->fetch();

    if ($user) {
        // Generate new token
        $token = bin2hex(random_bytes(32));
        $db->prepare('UPDATE users SET verify_token = ? WHERE id = ?')->execute([$token, $user->id]);

        $emailSent = send_verification_email($email, $user->name, $token);
        if ($emailSent) {
            flash('success', 'Verification email sent! Please check your inbox.');
        } else {
            flash('error', 'Failed to send email. Please try again later.');
        }
    } else {
        // Don't reveal if email exists or not
        flash('success', 'If that email exists and is unverified, a verification link has been sent.');
    }
    redirect('/login.php');
}

include '_head.php';
?>

<section class="section">
    <div class="container">
        <div class="auth-card">
            <h2>Resend Verification</h2>
            <p class="auth-subtitle">Enter your email to receive a new verification link</p>
            <form method="POST">
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" required placeholder="Enter your email address">
                </div>
                <button type="submit" class="btn btn-primary btn-block">Send Verification Email</button>
            </form>
            <div class="auth-links">
                <a href="<?= $base ?>/login.php">Back to Login</a>
            </div>
        </div>
    </div>
</section>

<?php include '_foot.php'; ?>
