<?php
// ============================================================================
// Email Helper using PHPMailer + Gmail SMTP
// ============================================================================

require_once __DIR__ . '/PHPMailer/PHPMailer.php';
require_once __DIR__ . '/PHPMailer/SMTP.php';
require_once __DIR__ . '/PHPMailer/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

define('MAIL_FROM_EMAIL', 'support.techhype@gmail.com');
define('MAIL_FROM_NAME', 'TechHype');
define('MAIL_PASSWORD', 'bcgn pajc gnfs bent');

/**
 * Send an email using Gmail SMTP
 */
function send_mail($to, $subject, $htmlBody) {
    $mail = new PHPMailer(true);

    try {
        // SMTP settings
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = MAIL_FROM_EMAIL;
        $mail->Password   = MAIL_PASSWORD;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        // From / To
        $mail->setFrom(MAIL_FROM_EMAIL, MAIL_FROM_NAME);
        $mail->addAddress($to);

        // Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $htmlBody;
        $mail->AltBody = strip_tags(str_replace(['<br>', '<br/>', '<br />'], "\n", $htmlBody));

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log('Mail Error: ' . $mail->ErrorInfo);
        return false;
    }
}

/**
 * Send verification email
 */
function send_verification_email($email, $name, $token) {
    $baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http')
               . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost:8888');
    $verifyUrl = $baseUrl . '/verify.php?token=' . urlencode($token);

    $subject = 'Verify Your TechHype Account';
    $body = '
    <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px;">
        <div style="text-align: center; padding: 20px 0; border-bottom: 2px solid #0071e3;">
            <h1 style="margin: 0; font-size: 28px;">
                <span style="color: #1d1d1f; font-weight: 800;">Tech</span><span style="color: #0071e3; font-weight: 800;">Hype</span>
            </h1>
        </div>

        <div style="padding: 30px 0;">
            <h2 style="color: #1d1d1f; margin-bottom: 15px;">Welcome, ' . htmlspecialchars($name) . '!</h2>
            <p style="color: #555; font-size: 16px; line-height: 1.6;">
                Thank you for registering at TechHype. Please verify your email address by clicking the button below:
            </p>

            <div style="text-align: center; margin: 30px 0;">
                <a href="' . $verifyUrl . '"
                   style="display: inline-block; background: #0071e3; color: #fff; padding: 14px 40px;
                          border-radius: 10px; text-decoration: none; font-size: 16px; font-weight: 600;">
                    Verify My Email
                </a>
            </div>

            <p style="color: #888; font-size: 13px;">
                Or copy and paste this link into your browser:<br>
                <a href="' . $verifyUrl . '" style="color: #0071e3; word-break: break-all;">' . $verifyUrl . '</a>
            </p>

            <p style="color: #888; font-size: 13px; margin-top: 20px;">
                This link will expire in 24 hours. If you did not create an account, please ignore this email.
            </p>
        </div>

        <div style="border-top: 1px solid #eee; padding-top: 15px; text-align: center; color: #aaa; font-size: 12px;">
            &copy; ' . date('Y') . ' TechHype. All rights reserved.
        </div>
    </div>';

    return send_mail($email, $subject, $body);
}
