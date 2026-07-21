<?php

// ============================================================================
// PHP Setups
// ============================================================================

session_start();

$base = '';

// Database connection
$db = new PDO('mysql:host=localhost;dbname=techhype;charset=utf8mb4', 'root', '', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
]);

// ============================================================================
// Helper Functions
// ============================================================================

function is_login() {
    return isset($_SESSION['user']);
}

function auth() {
    return $_SESSION['user'] ?? null;
}

function is_admin() {
    return is_login() && auth()->role === 'admin';
}

function redirect($url) {
    global $base;
    header("Location: $base$url");
    exit;
}

function flash($type, $message) {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function get_flash() {
    $flash = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $flash;
}

function clean($value) {
    return htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
}

function upload_photo($file, $folder = 'uploads') {
    $dir = __DIR__ . "/$folder/";
    if (!is_dir($dir)) mkdir($dir, 0777, true);

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

    if (!in_array($ext, $allowed)) return false;
    if ($file['size'] > 5 * 1024 * 1024) return false;

    $name = uniqid() . '.' . $ext;
    if (move_uploaded_file($file['tmp_name'], $dir . $name)) {
        return $name;
    }
    return false;
}

function delete_photo($name, $folder = 'uploads') {
    if ($name && $name !== 'default.png' && $name !== 'default-product.png') {
        $path = __DIR__ . "/$folder/$name";
        if (file_exists($path)) unlink($path);
    }
}

function require_login() {
    if (!is_login()) {
        flash('error', 'Please login first.');
        redirect('/login.php');
    }
}

function require_admin() {
    require_login();
    if (!is_admin()) {
        flash('error', 'Access denied.');
        redirect('/');
    }
}

function format_price($price) {
    return 'RM ' . number_format($price, 2);
}

function wishlist_count() {
    global $db;
    if (!is_login()) return 0;
    $stm = $db->prepare('SELECT COUNT(*) FROM wishlist WHERE user_id = ?');
    $stm->execute([auth()->id]);
    return $stm->fetchColumn();
}

function is_wishlisted($productId) {
    global $db;
    if (!is_login()) return false;
    $stm = $db->prepare('SELECT id FROM wishlist WHERE user_id = ? AND product_id = ?');
    $stm->execute([auth()->id, $productId]);
    return (bool)$stm->fetch();
}

// Auto-create points & vouchers tables
$db->exec("CREATE TABLE IF NOT EXISTS points_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    points INT NOT NULL,
    type ENUM('earn','redeem','refund') NOT NULL,
    description VARCHAR(255) NOT NULL,
    order_id INT DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$db->exec("CREATE TABLE IF NOT EXISTS vouchers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    code VARCHAR(20) NOT NULL UNIQUE,
    type ENUM('fixed','percent','shipping') NOT NULL,
    value DECIMAL(10,2) NOT NULL DEFAULT 0,
    max_discount DECIMAL(10,2) DEFAULT NULL,
    min_spend DECIMAL(10,2) DEFAULT 0,
    status ENUM('active','used','expired') DEFAULT 'active',
    used_order_id INT DEFAULT NULL,
    expires_at DATETIME NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

// Auto-create login_attempts table for brute force protection
$db->exec("CREATE TABLE IF NOT EXISTS login_attempts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(100) NOT NULL,
    ip_address VARCHAR(45) NOT NULL,
    attempted_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_ip (ip_address)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

// Add 'blocked' to users status enum if not already present
try {
    $db->exec("ALTER TABLE users MODIFY COLUMN status ENUM('active','inactive','blocked') DEFAULT 'active'");
} catch (Exception $e) {}

$db->exec("CREATE TABLE IF NOT EXISTS student_verifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL UNIQUE,
    student_id VARCHAR(50) NOT NULL,
    university VARCHAR(150) NOT NULL,
    student_email VARCHAR(150) NOT NULL,
    status ENUM('pending','verified','rejected') DEFAULT 'verified',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

function is_student_verified($userId = null) {
    global $db;
    if (!$userId && is_login()) $userId = auth()->id;
    if (!$userId) return false;
    $stm = $db->prepare('SELECT id FROM student_verifications WHERE user_id = ? AND status = "verified"');
    $stm->execute([$userId]);
    return (bool)$stm->fetch();
}

function get_malaysia_universities() {
    return [
        'Universiti Malaya (UM)',
        'Universiti Kebangsaan Malaysia (UKM)',
        'Universiti Putra Malaysia (UPM)',
        'Universiti Sains Malaysia (USM)',
        'Universiti Teknologi Malaysia (UTM)',
        'Universiti Teknologi MARA (UiTM)',
        'Universiti Islam Antarabangsa Malaysia (UIAM)',
        'Universiti Malaysia Sabah (UMS)',
        'Universiti Malaysia Sarawak (UNIMAS)',
        'Universiti Utara Malaysia (UUM)',
        'Universiti Pendidikan Sultan Idris (UPSI)',
        'Universiti Tun Hussein Onn Malaysia (UTHM)',
        'Universiti Teknikal Malaysia Melaka (UTeM)',
        'Universiti Malaysia Perlis (UniMAP)',
        'Universiti Malaysia Terengganu (UMT)',
        'Universiti Sultan Zainal Abidin (UniSZA)',
        'Universiti Malaysia Pahang Al-Sultan Abdullah (UMPSA)',
        'Universiti Malaysia Kelantan (UMK)',
        'Universiti Pertahanan Nasional Malaysia (UPNM)',
        'Taylor\'s University',
        'Sunway University',
        'Monash University Malaysia',
        'University of Nottingham Malaysia',
        'HELP University',
        'UCSI University',
        'Asia Pacific University (APU)',
        'Multimedia University (MMU)',
        'INTI International University',
        'SEGi University',
        'Limkokwing University',
        'Management and Science University (MSU)',
        'Tunku Abdul Rahman University of Management and Technology (TARUMT)',
        'Universiti Tunku Abdul Rahman (UTAR)',
        'Curtin University Malaysia',
        'Heriot-Watt University Malaysia',
        'Other',
    ];
}

function get_points_balance() {
    global $db;
    if (!is_login()) return 0;
    $stm = $db->prepare('SELECT COALESCE(SUM(points), 0) FROM points_log WHERE user_id = ?');
    $stm->execute([auth()->id]);
    return (int)$stm->fetchColumn();
}

function add_points($userId, $points, $type, $description, $orderId = null) {
    global $db;
    $stm = $db->prepare('INSERT INTO points_log (user_id, points, type, description, order_id) VALUES (?, ?, ?, ?, ?)');
    $stm->execute([$userId, $points, $type, $description, $orderId]);
}

function points_to_rm($points) {
    return $points / 100; // 100 points = RM 1
}

function rm_to_points($rm) {
    return (int)floor($rm); // RM 1 spent = 1 point earned
}

// Redeemable rewards catalog
function get_rewards_catalog() {
    return [
        ['id' => 'rm5',      'name' => 'RM 5 Voucher',           'desc' => 'RM 5 off any purchase',                    'points' => 500,   'icon' => 'fa-tag',            'type' => 'fixed',    'value' => 5,    'max_discount' => null, 'min_spend' => 50,   'color' => '#27ae60'],
        ['id' => 'rm10',     'name' => 'RM 10 Voucher',          'desc' => 'RM 10 off orders above RM 100',            'points' => 1000,  'icon' => 'fa-tag',            'type' => 'fixed',    'value' => 10,   'max_discount' => null, 'min_spend' => 100,  'color' => '#2980b9'],
        ['id' => 'rm25',     'name' => 'RM 25 Voucher',          'desc' => 'RM 25 off orders above RM 200',            'points' => 2500,  'icon' => 'fa-tags',           'type' => 'fixed',    'value' => 25,   'max_discount' => null, 'min_spend' => 200,  'color' => '#8e44ad'],
        ['id' => 'rm50',     'name' => 'RM 50 Voucher',          'desc' => 'RM 50 off orders above RM 500',            'points' => 5000,  'icon' => 'fa-gift',           'type' => 'fixed',    'value' => 50,   'max_discount' => null, 'min_spend' => 500,  'color' => '#e74c3c'],
        ['id' => 'ship',     'name' => 'Free Shipping',          'desc' => 'Free shipping on your next order',         'points' => 300,   'icon' => 'fa-truck-fast',     'type' => 'shipping', 'value' => 15,   'max_discount' => 15,   'min_spend' => 0,    'color' => '#f39c12'],
        ['id' => 'pct10',    'name' => '10% Off Voucher',        'desc' => '10% off (max RM 30) on orders above RM 80','points' => 1500,  'icon' => 'fa-percent',        'type' => 'percent',  'value' => 10,   'max_discount' => 30,   'min_spend' => 80,   'color' => '#00cec9'],
    ];
}

function generate_voucher_code() {
    return 'TH-' . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 8));
}

function get_user_vouchers($userId, $status = 'active') {
    global $db;
    $stm = $db->prepare('SELECT * FROM vouchers WHERE user_id = ? AND status = ? AND expires_at > NOW() ORDER BY created_at DESC');
    $stm->execute([$userId, $status]);
    return $stm->fetchAll();
}

function apply_voucher_discount($voucher, $cartTotal) {
    if ($cartTotal < $voucher->min_spend) return 0;
    if ($voucher->type === 'fixed' || $voucher->type === 'shipping') {
        $discount = $voucher->value;
    } elseif ($voucher->type === 'percent') {
        $discount = $cartTotal * ($voucher->value / 100);
    } else {
        return 0;
    }
    if ($voucher->max_discount && $discount > $voucher->max_discount) {
        $discount = $voucher->max_discount;
    }
    if ($discount > $cartTotal) $discount = $cartTotal;
    return round($discount, 2);
}

// ============================================================================
// Login Attempt Tracking & CAPTCHA
// ============================================================================

function get_client_ip() {
    return $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
}

function get_failed_attempts($email) {
    global $db;
    $stm = $db->prepare('SELECT COUNT(*) FROM login_attempts WHERE email = ? AND attempted_at > DATE_SUB(NOW(), INTERVAL 15 MINUTE)');
    $stm->execute([$email]);
    return (int)$stm->fetchColumn();
}

function record_failed_attempt($email) {
    global $db;
    $stm = $db->prepare('INSERT INTO login_attempts (email, ip_address) VALUES (?, ?)');
    $stm->execute([$email, get_client_ip()]);
}

function clear_failed_attempts($email) {
    global $db;
    $stm = $db->prepare('DELETE FROM login_attempts WHERE email = ?');
    $stm->execute([$email]);
}

function is_login_blocked($email) {
    return get_failed_attempts($email) >= 3;
}

function get_block_remaining($email) {
    global $db;
    $stm = $db->prepare('SELECT attempted_at FROM login_attempts WHERE email = ? ORDER BY attempted_at DESC LIMIT 1');
    $stm->execute([$email]);
    $last = $stm->fetchColumn();
    if (!$last) return 0;
    $unblockTime = strtotime($last) + (15 * 60);
    $remaining = $unblockTime - time();
    return max(0, $remaining);
}

function generate_captcha() {
    $ops = ['+', '-', 'x'];
    $op = $ops[array_rand($ops)];
    if ($op === '+') {
        $a = rand(1, 20);
        $b = rand(1, 20);
        $answer = $a + $b;
    } elseif ($op === '-') {
        $a = rand(5, 25);
        $b = rand(1, $a);
        $answer = $a - $b;
    } else {
        $a = rand(2, 9);
        $b = rand(2, 9);
        $answer = $a * $b;
    }
    $_SESSION['captcha_answer'] = $answer;
    return "$a $op $b";
}

function verify_captcha($input) {
    $answer = $_SESSION['captcha_answer'] ?? null;
    unset($_SESSION['captcha_answer']);
    return $answer !== null && (int)$input === (int)$answer;
}

function cart_count() {
    if (!isset($_SESSION['cart'])) return 0;
    $count = 0;
    foreach ($_SESSION['cart'] as $item) {
        $count += is_array($item) ? $item['qty'] : $item;
    }
    return $count;
}

function cart_total() {
    global $db;
    $total = 0;
    if (isset($_SESSION['cart'])) {
        foreach ($_SESSION['cart'] as $key => $item) {
            if (is_array($item)) {
                $productId = $item['product_id'];
                $qty = $item['qty'];
                $variantPrice = $item['price'] ?? '';
            } else {
                $productId = $key;
                $qty = $item;
                $variantPrice = '';
            }
            if ($variantPrice) {
                $price = floatval(str_replace(['RM ', ',', ' '], '', $variantPrice));
            } else {
                $stm = $db->prepare('SELECT price, sale_price FROM products WHERE id = ?');
                $stm->execute([$productId]);
                $p = $stm->fetch();
                $price = $p ? ($p->sale_price ?? $p->price) : 0;
            }
            $total += $price * $qty;
        }
    }
    return $total;
}
