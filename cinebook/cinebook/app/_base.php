<?php
// ─── Database Configuration ───────────────────────────────────────────────────
define('DB_HOST', 'localhost');
define('DB_PORT', '3306');
define('DB_NAME', 'food_court');
define('DB_USER', 'root');
define('DB_PASS', '');
define('SITE_NAME', 'Campus Bites');
define('SITE_TAGLINE', 'Browse stalls, pre-order meals, and pick up on time.');

// ─── Email (Gmail SMTP) ───────────────────────────────────────────────────────
// MAIL_PASS must be a Google "App Password" (16 chars, no spaces), NOT your
// normal Gmail password. Create one at: https://myaccount.google.com/apppasswords
// Leave MAIL_PASS empty to disable email sending (the app still works).
define('MAIL_HOST', 'smtp.gmail.com');
define('MAIL_PORT', 587);
define('MAIL_USER', 'support.campusbites@gmail.com');
define('MAIL_PASS', 'nvxfpcoybjzaxzvz');   // ← paste your Gmail App Password here
define('MAIL_FROM_NAME', SITE_NAME);

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
} catch (PDOException $e) {
    die('<div style="padding:20px;font-family:sans-serif;color:#c00">
        <h2>Database Connection Failed</h2>
        <p>' . htmlspecialchars($e->getMessage()) . '</p>
        <p>Please ensure XAMPP MySQL is running and the database <strong>' . DB_NAME . '</strong> exists.</p>
        <p>Import <strong>database.sql</strong> through phpMyAdmin first.</p>
    </div>');
}
