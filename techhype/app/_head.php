<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TechHype - Your One-Stop Electronics Shop</title>
    <link rel="icon" href="<?= $base ?>/images/favicon.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="<?= $base ?>/css/app.css?v=<?= time() ?>">
</head>
<body>

<!-- Top Bar -->
<div class="topbar">
    <div class="container topbar-inner">
        <span><i class="fa-solid fa-truck-fast"></i> Free Shipping on Orders Above RM 500</span>
        <div class="topbar-right">
            <?php if (is_login()): ?>
                <span>Welcome, <?= clean(auth()->name) ?></span>
                <?php if (is_admin()): ?>
                    <a href="<?= $base ?>/admin/members.php"><i class="fa-solid fa-shield"></i> Admin</a>
                <?php endif; ?>
                <a href="<?= $base ?>/logout.php"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
            <?php else: ?>
                <a href="<?= $base ?>/login.php"><i class="fa-solid fa-right-to-bracket"></i> Login</a>
                <a href="<?= $base ?>/register.php"><i class="fa-solid fa-user-plus"></i> Register</a>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Voucher Banner -->
<?php if (is_login()):
    $__voucherCount = count(get_user_vouchers(auth()->id));
    $__pointsBal = get_points_balance();
    $__isStudent = is_student_verified();
?>
<div class="voucher-banner">
    <div class="container voucher-banner-inner">
        <a href="<?= $base ?>/rewards.php" class="voucher-banner-left">
            <i class="fa-solid fa-ticket"></i>
            <span>
                <?php if ($__voucherCount > 0): ?>
                    You have <strong><?= $__voucherCount ?> voucher<?= $__voucherCount > 1 ? 's' : '' ?></strong> ready to use!
                <?php else: ?>
                    Redeem your points for exclusive vouchers & discounts
                <?php endif; ?>
            </span>
        </a>
        <div class="voucher-banner-actions">
            <?php if (!$__isStudent): ?>
            <button class="student-voucher-btn" onclick="document.getElementById('studentModal').classList.add('show')">
                <i class="fa-solid fa-graduation-cap"></i> Student Discount
            </button>
            <?php else: ?>
            <span class="student-verified-badge">
                <i class="fa-solid fa-graduation-cap"></i> Student Verified
            </span>
            <?php endif; ?>
            <a href="<?= $base ?>/rewards.php" class="voucher-banner-right">
                <i class="fa-solid fa-coins"></i>
                <strong><?= number_format($__pointsBal) ?></strong> pts
            </a>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Student Verification Modal -->
<?php if (is_login() && !is_student_verified()): ?>
<div class="student-modal-overlay" id="studentModal" onclick="if(event.target===this)this.classList.remove('show')">
    <div class="student-modal">
        <button class="student-modal-close" onclick="document.getElementById('studentModal').classList.remove('show')">&times;</button>
        <div class="student-modal-header">
            <div class="student-modal-icon">
                <i class="fa-solid fa-graduation-cap"></i>
            </div>
            <h2>Student Verification</h2>
            <p>Verify your student status and get an exclusive <strong>35% OFF voucher</strong> + <strong>200 bonus points</strong>!</p>
        </div>
        <form method="POST" action="<?= $base ?>/student-verify.php" class="student-modal-form">
            <div class="form-group">
                <label><i class="fa-solid fa-id-card"></i> Student ID</label>
                <input type="text" name="student_id" placeholder="e.g. A12345678" required>
            </div>
            <div class="form-group">
                <label><i class="fa-solid fa-building-columns"></i> University</label>
                <select name="university" required>
                    <option value="">-- Select Your University --</option>
                    <?php foreach (get_malaysia_universities() as $uni): ?>
                    <option value="<?= clean($uni) ?>"><?= clean($uni) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label><i class="fa-solid fa-envelope"></i> Student Email</label>
                <input type="email" name="student_email" placeholder="e.g. yourname@student.university.edu.my" required>
                <small style="color:var(--text-muted);display:block;margin-top:4px;">Must be a .edu or .ac email address</small>
            </div>
            <div class="student-perks">
                <div class="student-perk"><i class="fa-solid fa-check-circle"></i> 15% OFF voucher (up to RM 100)</div>
                <div class="student-perk"><i class="fa-solid fa-check-circle"></i> 200 bonus reward points</div>
                <div class="student-perk"><i class="fa-solid fa-check-circle"></i> Valid for 90 days</div>
            </div>
            <button type="submit" class="btn btn-primary btn-block">
                <i class="fa-solid fa-graduation-cap"></i> Verify & Claim Student Voucher
            </button>
        </form>
    </div>
</div>
<?php endif; ?>

<!-- Navbar -->
<nav class="navbar">
    <div class="container navbar-inner">
        <a href="<?= $base ?>/" class="logo">Tech<span>Hype</span></a>
        <ul class="nav-links">
            <li><a href="<?= $base ?>/">Home</a></li>
            <li><a href="<?= $base ?>/products.php">Products</a></li>
            <li><a href="<?= $base ?>/about.php">About</a></li>
            <li class="dropdown">
                <a href="#">Brands <i class="fa-solid fa-chevron-down"></i></a>
                <div class="dropdown-menu">
                    <div class="dropdown-grid">
                        <a href="<?= $base ?>/page/brand.php?name=Samsung">Samsung</a>
                        <a href="<?= $base ?>/page/brand.php?name=Apple">Apple</a>
                        <a href="<?= $base ?>/page/brand.php?name=Sony">Sony</a>
                        <a href="<?= $base ?>/page/brand.php?name=Google">Google</a>
                        <a href="<?= $base ?>/page/brand.php?name=Xiaomi">Xiaomi</a>
<a href="<?= $base ?>/page/brand.php?name=Oppo">Oppo</a>
                        <a href="<?= $base ?>/page/brand.php?name=Vivo">Vivo</a>
                        <a href="<?= $base ?>/page/brand.php?name=Nothing">Nothing</a>
                        <a href="<?= $base ?>/page/brand.php?name=iQOO">iQOO</a>
                    </div>
                </div>
            </li>
            <li class="dropdown">
                <a href="#">Categories <i class="fa-solid fa-chevron-down"></i></a>
                <div class="dropdown-menu">
                    <div class="dropdown-list">
                        <a href="<?= $base ?>/products.php?category=mobile"><i class="fa-solid fa-mobile-screen"></i> Mobiles</a>
                        <a href="<?= $base ?>/products.php?category=tablet"><i class="fa-solid fa-tablet-screen-button"></i> Tablets</a>
                        <a href="<?= $base ?>/products.php?category=console"><i class="fa-solid fa-gamepad"></i> Consoles</a>
                        <a href="<?= $base ?>/products.php?category=audio"><i class="fa-solid fa-headphones"></i> Audio</a>
                    </div>
                </div>
            </li>
        </ul>
        <div class="nav-icons">
            <form action="<?= $base ?>/products.php" method="GET" class="nav-search">
                <input type="text" name="search" placeholder="Search..." value="<?= clean($_GET['search'] ?? '') ?>">
            </form>
            <?php if (is_login()): ?>
                <a href="<?= $base ?>/wishlist.php"><i class="fa-solid fa-heart"></i> <span class="badge wishlist-badge"><?= wishlist_count() ?></span></a>
            <?php endif; ?>
            <a href="<?= $base ?>/cart.php"><i class="fa-solid fa-cart-shopping"></i> <span class="badge"><?= cart_count() ?></span></a>
            <?php if (is_login()): ?>
                <a href="<?= $base ?>/profile.php"><i class="fa-solid fa-user"></i></a>
            <?php endif; ?>
        </div>
    </div>
</nav>

<?php $flash = get_flash(); if ($flash): ?>
<div class="alert alert-<?= $flash['type'] ?>">
    <div class="container"><?= $flash['message'] ?></div>
</div>
<?php endif; ?>
