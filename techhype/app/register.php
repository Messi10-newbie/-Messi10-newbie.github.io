<?php
include '_base.php';
include 'lib/mail.php';

if (is_login()) redirect('/');

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = clean($_POST['name'] ?? '');
    $email    = clean($_POST['email'] ?? '');
    $phone    = clean($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm'] ?? '';

    if (!$name) $errors[] = 'Name is required.';
    if (!$email) $errors[] = 'Email is required.';
    if (!$password) $errors[] = 'Password is required.';
    if (strlen($password) < 6) $errors[] = 'Password must be at least 6 characters.';
    if ($password !== $confirm) $errors[] = 'Passwords do not match.';

    // Check email exists
    $stm = $db->prepare('SELECT id FROM users WHERE email = ?');
    $stm->execute([$email]);
    if ($stm->fetch()) $errors[] = 'Email already registered.';

    // Upload photo
    $photo = 'default.png';
    if (isset($_FILES['photo']) && $_FILES['photo']['size'] > 0) {
        $uploaded = upload_photo($_FILES['photo']);
        if ($uploaded) $photo = $uploaded;
        else $errors[] = 'Invalid photo. Use JPG/PNG/GIF under 5MB.';
    }

    if (empty($errors)) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $token = bin2hex(random_bytes(32));
        $stm = $db->prepare('INSERT INTO users (name, email, phone, password, photo, email_verified, verify_token) VALUES (?, ?, ?, ?, ?, 0, ?)');
        $stm->execute([$name, $email, $phone, $hash, $photo, $token]);

        // Send verification email
        $emailSent = send_verification_email($email, $name, $token);

        if ($emailSent) {
            flash('success', 'Registration successful! Please check your email to verify your account.');
        } else {
            flash('success', 'Registration successful! Verification email could not be sent, please contact support.');
        }
        redirect('/login.php');
    }
}

include '_head.php';
?>

<section class="section">
    <div class="container">
        <div class="auth-card">
            <h2>Register</h2>
            <p class="auth-subtitle">Create your TechHype account</p>

            <?php if ($errors): ?>
                <div class="alert alert-error">
                    <?php foreach ($errors as $e): ?>
                        <div><?= $e ?></div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label>Full Name *</label>
                    <input type="text" name="name" required value="<?= clean($_POST['name'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label>Email *</label>
                    <input type="email" name="email" required value="<?= clean($_POST['email'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label>Phone</label>
                    <input type="text" name="phone" value="<?= clean($_POST['phone'] ?? '') ?>">
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Password *</label>
                        <input type="password" name="password" required minlength="6">
                    </div>
                    <div class="form-group">
                        <label>Confirm Password *</label>
                        <input type="password" name="confirm" required>
                    </div>
                </div>
                <div class="form-group">
                    <label>Profile Photo</label>
                    <input type="file" name="photo" accept="image/*">
                </div>
                <button type="submit" class="btn btn-primary btn-block">Register</button>
            </form>
            <div class="auth-links">
                <span>Already have an account? <a href="<?= $base ?>/login.php">Login</a></span>
            </div>
        </div>
    </div>
</section>

<?php include '_foot.php'; ?>
