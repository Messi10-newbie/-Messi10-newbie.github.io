<?php
// ─── Order Confirmation Email ─────────────────────────────────────────────────

require_once __DIR__ . '/lib/phpmailer/Exception.php';
require_once __DIR__ . '/lib/phpmailer/PHPMailer.php';
require_once __DIR__ . '/lib/phpmailer/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;

/** True when SMTP credentials are configured. */
function mailEnabled(): bool {
    return defined('MAIL_PASS') && MAIL_PASS !== '';
}

/** Human-readable reason the last sendOrderConfirmationEmail() call failed, or '' if it succeeded. */
function lastMailError(): string {
    return $GLOBALS['__last_mail_error'] ?? '';
}

/**
 * Send the order receipt to the customer. Returns true on success.
 * Failures are logged and never break the page.
 */
function sendOrderConfirmationEmail(array $order, array $orderItems): bool {
    $GLOBALS['__last_mail_error'] = '';
    if (!mailEnabled()) {
        $GLOBALS['__last_mail_error'] = 'Email is not configured (MAIL_PASS is empty in _base.php).';
        return false;
    }
    if (empty($order['customer_email'])) {
        $GLOBALS['__last_mail_error'] = 'The order has no customer email address.';
        return false;
    }

    try {
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host       = MAIL_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = MAIL_USER;
        $mail->Password   = MAIL_PASS;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = MAIL_PORT;
        $mail->CharSet    = 'UTF-8';

        // Some XAMPP installs ship without a CA certificate bundle, making Gmail's
        // TLS cert unverifiable ("certificate verify failed"). Skip peer verification
        // so email works on those machines too (fine for local development).
        $mail->SMTPOptions = [
            'ssl' => [
                'verify_peer'       => false,
                'verify_peer_name'  => false,
                'allow_self_signed' => true,
            ],
        ];

        $mail->setFrom(MAIL_USER, MAIL_FROM_NAME);
        $mail->addAddress($order['customer_email'], $order['customer_name']);

        $ref = $order['order_reference'];
        $mail->Subject = SITE_NAME . " — Order Confirmed #$ref";
        $mail->isHTML(true);
        $mail->Body    = buildOrderEmailHtml($order, $orderItems);
        $mail->AltBody = buildOrderEmailText($order, $orderItems);

        $mail->send();
        return true;
    } catch (Throwable $e) {
        $GLOBALS['__last_mail_error'] = $e->getMessage();
        error_log('Order email failed: ' . $e->getMessage());
        return false;
    }
}

/**
 * Notify the customer their order is ready for collection. Returns true on success.
 * Failures are logged and never break the page.
 */
function sendOrderReadyEmail(array $order): bool {
    $GLOBALS['__last_mail_error'] = '';
    if (!mailEnabled()) {
        $GLOBALS['__last_mail_error'] = 'Email is not configured (MAIL_PASS is empty in _base.php).';
        return false;
    }
    if (empty($order['customer_email'])) {
        $GLOBALS['__last_mail_error'] = 'The order has no customer email address.';
        return false;
    }

    try {
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host       = MAIL_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = MAIL_USER;
        $mail->Password   = MAIL_PASS;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = MAIL_PORT;
        $mail->CharSet    = 'UTF-8';
        $mail->SMTPOptions = [
            'ssl' => [
                'verify_peer'       => false,
                'verify_peer_name'  => false,
                'allow_self_signed' => true,
            ],
        ];

        $mail->setFrom(MAIL_USER, MAIL_FROM_NAME);
        $mail->addAddress($order['customer_email'], $order['customer_name']);

        $ref = $order['order_reference'];
        $mail->Subject = SITE_NAME . " — Your Order #$ref is Ready! 🔔";
        $mail->isHTML(true);
        $mail->Body    = buildOrderReadyEmailHtml($order);
        $mail->AltBody = SITE_NAME . " — Order #$ref is ready for collection!\n"
            . 'Pickup: ' . fmtDate($order['slot_date']) . ' at ' . $order['slot_time'] . "\n"
            . 'Show your order reference at the counter to collect.';

        $mail->send();
        return true;
    } catch (Throwable $e) {
        $GLOBALS['__last_mail_error'] = $e->getMessage();
        error_log('Order-ready email failed: ' . $e->getMessage());
        return false;
    }
}

/** Branded "order ready" notification (inline styles — email clients ignore stylesheets). */
function buildOrderReadyEmailHtml(array $order): string {
    $ref      = h($order['order_reference']);
    $name     = h($order['customer_name']);
    $date     = fmtDate($order['slot_date']);
    $time     = h($order['slot_time']);
    $siteName = h(SITE_NAME);

    return <<<HTML
<!DOCTYPE html>
<html>
<body style="margin:0;padding:24px 12px;background:#eaf0fb;font-family:Segoe UI,Arial,sans-serif">
  <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:560px;margin:0 auto">
    <tr><td>
      <table role="presentation" width="100%" cellpadding="0" cellspacing="0"
             style="background:linear-gradient(135deg,#14532d,#22c55e);border-radius:22px 22px 0 0;color:#fff">
        <tr><td style="padding:32px;text-align:center">
          <div style="font-size:26px;font-weight:800;letter-spacing:-.5px">🧺 {$siteName}</div>
          <div style="margin-top:14px;font-size:15px;opacity:.9">🔔 Your order is ready for collection!</div>
          <div style="margin-top:18px;display:inline-block;background:rgba(255,255,255,.14);border:1px solid rgba(255,255,255,.3);border-radius:999px;padding:8px 22px;font-family:Courier New,monospace;font-size:20px;font-weight:700;letter-spacing:2px">#{$ref}</div>
        </td></tr>
      </table>

      <table role="presentation" width="100%" cellpadding="0" cellspacing="0"
             style="background:#ffffff;border-radius:0 0 22px 22px;box-shadow:0 6px 24px rgba(31,45,80,.14)">
        <tr><td style="padding:28px 32px">
          <p style="margin:0 0 4px;font-size:15px;color:#1e2433">Hi <strong>{$name}</strong>,</p>
          <p style="margin:0 0 20px;font-size:14px;color:#55617a">Good news — your food is ready! Head over to the food court and show the reference above at the counter.</p>

          <table role="presentation" width="100%" cellpadding="0" cellspacing="0"
                 style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:14px">
            <tr>
              <td style="padding:14px 18px">
                <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.6px;color:#166534">Pickup Date</div>
                <div style="font-size:15px;font-weight:700;color:#1e2433">{$date}</div>
              </td>
              <td style="padding:14px 18px;text-align:right">
                <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.6px;color:#166534">Time Slot</div>
                <div style="font-size:15px;font-weight:700;color:#1e2433">{$time}</div>
              </td>
            </tr>
          </table>

          <p style="margin:20px 0 0;font-size:13px;color:#55617a">Please collect your order soon so it stays fresh. 😋</p>
        </td></tr>
      </table>

      <p style="text-align:center;font-size:12px;color:#8a93a6;margin:18px 0 0">
        {$siteName} · Campus Food Court · This is an automated notification, please do not reply.
      </p>
    </td></tr>
  </table>
</body>
</html>
HTML;
}

/** Branded HTML receipt (inline styles — email clients ignore stylesheets). */
function buildOrderEmailHtml(array $order, array $orderItems): string {
    $ref    = h($order['order_reference']);
    $name   = h($order['customer_name']);
    $date   = fmtDate($order['slot_date']);
    $time   = h($order['slot_time']);
    $total  = fmtMoney((float)$order['total_price']);

    $rows = '';
    foreach ($orderItems as $it) {
        $line = fmtMoney((float)$it['quantity'] * (float)$it['unit_price']);
        $sub  = [];
        if (!empty($it['stall_name'])) $sub[] = h($it['stall_name']);
        if (!empty($it['options']))    $sub[] = h($it['options']);
        $opts = $sub
            ? '<br><span style="color:#8a93a6;font-size:12px;font-style:italic">' . implode(' · ', $sub) . '</span>'
            : '';
        $rows .= '<tr>
            <td style="padding:10px 0;border-bottom:1px dashed #dde3ef;font-size:14px;color:#1e2433">'
                . (int)$it['quantity'] . ' × ' . h($it['item_name']) . $opts . '</td>
            <td style="padding:10px 0;border-bottom:1px dashed #dde3ef;font-size:14px;color:#1e2433;text-align:right;white-space:nowrap">'
                . $line . '</td>
        </tr>';
    }

    $notes = !empty($order['notes'])
        ? '<p style="margin:18px 0 0;font-size:13px;color:#55617a"><strong>Notes:</strong> ' . h($order['notes']) . '</p>'
        : '';

    $siteName = h(SITE_NAME);

    return <<<HTML
<!DOCTYPE html>
<html>
<body style="margin:0;padding:24px 12px;background:#eaf0fb;font-family:Segoe UI,Arial,sans-serif">
  <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:560px;margin:0 auto">
    <tr><td>
      <table role="presentation" width="100%" cellpadding="0" cellspacing="0"
             style="background:linear-gradient(135deg,#10202c,#2c5f8a);border-radius:22px 22px 0 0;color:#fff">
        <tr><td style="padding:32px;text-align:center">
          <div style="font-size:26px;font-weight:800;letter-spacing:-.5px">🧺 {$siteName}</div>
          <div style="margin-top:14px;font-size:15px;opacity:.85">Your order is confirmed! 🎉</div>
          <div style="margin-top:18px;display:inline-block;background:rgba(255,255,255,.14);border:1px solid rgba(255,255,255,.3);border-radius:999px;padding:8px 22px;font-family:Courier New,monospace;font-size:20px;font-weight:700;letter-spacing:2px">#{$ref}</div>
        </td></tr>
      </table>

      <table role="presentation" width="100%" cellpadding="0" cellspacing="0"
             style="background:#ffffff;border-radius:0 0 22px 22px;box-shadow:0 6px 24px rgba(31,45,80,.14)">
        <tr><td style="padding:28px 32px">
          <p style="margin:0 0 4px;font-size:15px;color:#1e2433">Hi <strong>{$name}</strong>,</p>
          <p style="margin:0 0 20px;font-size:14px;color:#55617a">Thanks for your order! Show the reference above when you collect.</p>

          <table role="presentation" width="100%" cellpadding="0" cellspacing="0"
                 style="background:#f2f6fd;border-radius:14px;margin-bottom:22px">
            <tr>
              <td style="padding:14px 18px">
                <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.6px;color:#55617a">Pickup Date</div>
                <div style="font-size:15px;font-weight:700;color:#1e2433">{$date}</div>
              </td>
              <td style="padding:14px 18px;text-align:right">
                <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.6px;color:#55617a">Time Slot</div>
                <div style="font-size:15px;font-weight:700;color:#1e2433">{$time}</div>
              </td>
            </tr>
          </table>

          <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.6px;color:#55617a;margin-bottom:4px">Your Items</div>
          <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
            {$rows}
            <tr>
              <td style="padding:14px 0 0;font-size:16px;font-weight:800;color:#1e2433">Total Paid</td>
              <td style="padding:14px 0 0;font-size:18px;font-weight:800;color:#bc6c25;text-align:right">{$total}</td>
            </tr>
          </table>
          {$notes}
        </td></tr>
      </table>

      <p style="text-align:center;font-size:12px;color:#8a93a6;margin:18px 0 0">
        {$siteName} · Campus Food Court · This is an automated receipt, please do not reply.
      </p>
    </td></tr>
  </table>
</body>
</html>
HTML;
}

/** Plain-text fallback for clients that block HTML. */
function buildOrderEmailText(array $order, array $orderItems): string {
    $lines = [
        SITE_NAME . ' — Order Confirmed',
        'Reference: #' . $order['order_reference'],
        'Pickup: ' . fmtDate($order['slot_date']) . ' at ' . $order['slot_time'],
        '',
        'Items:',
    ];
    foreach ($orderItems as $it) {
        $line = '  ' . (int)$it['quantity'] . ' x ' . $it['item_name']
              . ' — ' . fmtMoney((float)$it['quantity'] * (float)$it['unit_price']);
        if (!empty($it['stall_name'])) {
            $line .= ' [' . $it['stall_name'] . ']';
        }
        if (!empty($it['options'])) {
            $line .= ' (' . $it['options'] . ')';
        }
        $lines[] = $line;
    }
    $lines[] = '';
    $lines[] = 'Total Paid: ' . fmtMoney((float)$order['total_price']);
    if (!empty($order['notes'])) {
        $lines[] = 'Notes: ' . $order['notes'];
    }
    return implode("\n", $lines);
}
