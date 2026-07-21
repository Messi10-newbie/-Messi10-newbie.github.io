<?php
include '_base.php';
require_login();

$user = auth();

// Already verified
if (is_student_verified($user->id)) {
    flash('error', 'You are already verified as a student.');
    redirect('/rewards.php');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/rewards.php');
}

$studentId    = clean($_POST['student_id'] ?? '');
$university   = clean($_POST['university'] ?? '');
$studentEmail = clean($_POST['student_email'] ?? '');

$errors = [];

if (!$studentId || strlen($studentId) < 3) {
    $errors[] = 'Please enter a valid Student ID.';
}
if (!$university) {
    $errors[] = 'Please select your university.';
}
if (!$studentEmail || !filter_var($studentEmail, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Please enter a valid student email.';
}
// Basic check: student email should contain .edu or university domain
if ($studentEmail && !preg_match('/\.(edu|ac)\b/i', $studentEmail)) {
    $errors[] = 'Please use your university email (must contain .edu or .ac).';
}

if (!empty($errors)) {
    flash('error', implode('<br>', $errors));
    redirect('/rewards.php');
}

// Save verification
$stm = $db->prepare('INSERT INTO student_verifications (user_id, student_id, university, student_email) VALUES (?, ?, ?, ?)');
$stm->execute([$user->id, $studentId, $university, $studentEmail]);

// Grant student voucher: 35% off, max RM 100, valid 90 days
$code = 'STU-' . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 8));
$expiresAt = date('Y-m-d H:i:s', strtotime('+90 days'));
$stm = $db->prepare('INSERT INTO vouchers (user_id, code, type, value, max_discount, min_spend, expires_at) VALUES (?, ?, ?, ?, ?, ?, ?)');
$stm->execute([$user->id, $code, 'percent', 35, 100, 50, $expiresAt]);

// Bonus: give 200 welcome points
add_points($user->id, 200, 'earn', 'Student verification bonus');

flash('success', 'Student verified! You received a <strong>15% OFF voucher</strong> (' . $code . ') + <strong>200 bonus points</strong>!');
redirect('/rewards.php');
