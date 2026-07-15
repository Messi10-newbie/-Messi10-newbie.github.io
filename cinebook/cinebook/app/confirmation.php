<?php
session_start();
require '_base.php';
require '_functions.php';
require '_mail.php';

$orderId  = (int)($_SESSION['last_order_id'] ?? 0);
$orderRef = $_SESSION['last_order_ref'] ?? '';

if ($orderId <= 0 || $orderRef === '') {
    redirect('index.php');
}

$order      = getOrder($pdo, $orderId);
$orderItems = getOrderItems($pdo, $orderId);
if (!$order) { redirect('index.php'); }

// Email the receipt once, right after payment (guard against double-send on refresh)
$emailSent = false;
$emailError = '';
if (empty($_SESSION['mail_sent_' . $orderId])) {
    $emailSent = sendOrderConfirmationEmail($order, $orderItems);
    $emailError = lastMailError();
    if ($emailSent) {
        $_SESSION['mail_sent_' . $orderId] = true;
    }
}

unset($_SESSION['last_order_id'], $_SESSION['last_order_ref'], $_SESSION['last_order_total'], $_SESSION['last_stall_name']);

$statuses = [
    'pending'   => ['label' => 'Order Received',    'icon' => 'bi-clock',             'desc' => 'Waiting to be confirmed by the stall.'],
    'preparing' => ['label' => 'Preparing',          'icon' => 'bi-fire',              'desc' => 'The stall is cooking your food.'],
    'delayed'   => ['label' => 'Running a Bit Late', 'icon' => 'bi-hourglass-split',   'desc' => 'Busy right now — your order is taking a little longer.'],
    'ready'     => ['label' => 'Ready for Pickup!',  'icon' => 'bi-bag-check-fill',    'desc' => 'Head to the stall to collect your order!'],
    'collected' => ['label' => 'Collected',          'icon' => 'bi-check-circle-fill', 'desc' => 'Enjoy your meal!'],
    'cancelled' => ['label' => 'Cancelled',          'icon' => 'bi-x-circle-fill',     'desc' => 'This order was cancelled.'],
];

$flowSteps  = ['pending', 'preparing', 'ready', 'collected'];
$curStatus  = $order['order_status'];
$isCancelled = $curStatus === 'cancelled';
$isDelayed   = $curStatus === 'delayed';
$barStatus   = $isDelayed ? 'preparing' : $curStatus;
$currentIdx  = array_search($barStatus, $flowSteps);
$statusInfo  = $statuses[$curStatus] ?? $statuses['pending'];

$pageTitle = 'Order Confirmed';
$cssPath   = '';
?>
<?php include '_head.php'; ?>

<div class="container page-section">
    <div class="row justify-content-center">
        <div class="col-lg-8">

            <div class="card border-0 shadow-sm overflow-hidden" style="border-radius:var(--radius)">
                <div class="order-hero p-4 text-white text-center">
                    <div class="mb-2"><i class="bi bi-check-circle-fill fs-1"></i></div>
                    <h2 class="fw-bold mb-2">Order Confirmed</h2>
                    <p class="mb-3 opacity-75">Your pre-order has been placed successfully.</p>
                    <?php if ($emailSent): ?>
                    <p class="small mb-2" style="opacity:.85"><i class="bi bi-envelope-check me-1"></i>A receipt was emailed to <?= h($order['customer_email']) ?></p>
                    <?php elseif ($emailError !== ''): ?>
                    <p class="small mb-2" style="opacity:.9;background:rgba(0,0,0,.25);border-radius:8px;padding:6px 12px;display:inline-block"><i class="bi bi-envelope-exclamation me-1"></i>Receipt email could not be sent: <?= h($emailError) ?></p>
                    <?php endif; ?>
                    <div class="booking-ref" style="font-size:1.4rem;letter-spacing:1px">#<?= h($orderRef) ?></div>
                </div>
                <div class="card-body p-4">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="an-label mb-1">Customer</div>
                            <div class="fw-semibold"><?= h($order['customer_name']) ?></div>
                            <div class="text-muted small"><?= h($order['customer_phone']) ?></div>
                            <div class="text-muted small"><?= h($order['customer_email']) ?></div>
                        </div>
                        <div class="col-md-6 text-md-end">
                            <div class="an-label mb-1">Pickup Slot</div>
                            <div class="fw-semibold"><?= fmtDate($order['slot_date']) ?></div>
                            <div class="text-muted small"><?= h($order['slot_time']) ?></div>
                        </div>
                    </div>

                    <hr class="my-4">

                    <div class="an-label mb-2">Items</div>
                    <?php foreach ($orderItems as $item): ?>
                    <div class="price-row">
                        <span>
                            <?= (int)$item['quantity'] ?> x <?= h($item['item_name']) ?>
                            <span class="text-muted small">· <?= h($item['stall_name']) ?></span>
                            <?php if (!empty($item['options'])): ?>
                            <br><span class="opt-summary"><?= h($item['options']) ?></span>
                            <?php endif; ?>
                        </span>
                        <span><?= fmtMoney((float)$item['quantity'] * (float)$item['unit_price']) ?></span>
                    </div>
                    <?php endforeach; ?>

                    <div class="border-top mt-3 pt-3 d-flex justify-content-between align-items-center">
                        <span class="fw-bold">Total Paid</span>
                        <span class="price-total"><?= fmtMoney((float)$order['total_price']) ?></span>
                    </div>

                    <?php if (!empty($order['notes'])): ?>
                    <div class="mt-4">
                        <div class="an-label mb-1">Notes</div>
                        <div class="text-muted small"><?= h($order['notes']) ?></div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="d-flex gap-2 justify-content-center mt-4 flex-wrap">
                <a href="index.php" class="btn btn-outline-secondary"><i class="bi bi-house me-1"></i>Back Home</a>
                <button class="btn-an-search border-0" data-bs-toggle="modal" data-bs-target="#trackModal">
                    <i class="bi bi-broadcast me-1"></i>Track Order
                </button>
                <a href="order.php" class="btn btn-outline-secondary"><i class="bi bi-bag-plus me-1"></i>New Order</a>
            </div>
        </div>
    </div>
</div>

<!-- ── Track Order Modal ──────────────────────────────────────────────────── -->
<div class="modal fade" id="trackModal" tabindex="-1" aria-labelledby="trackModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius:var(--radius)">

            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold" id="trackModalLabel">
                    <i class="bi bi-broadcast me-2" style="color:var(--an-primary)"></i>Order Status
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body p-4">

                <!-- Status banner -->
                <div class="status-banner mb-4 <?= $isCancelled ? 'status-cancelled' : ($curStatus === 'ready' ? 'status-ready' : ($isDelayed ? 'status-delayed' : '')) ?>">
                    <div class="d-flex align-items-center gap-3">
                        <div class="status-icon-wrap">
                            <i class="bi <?= $statusInfo['icon'] ?>"></i>
                        </div>
                        <div>
                            <div class="status-main-label"><?= $statusInfo['label'] ?></div>
                            <div class="status-desc"><?= $statusInfo['desc'] ?></div>
                        </div>
                    </div>
                </div>

                <?php if (!$isCancelled): ?>
                <!-- Step bar -->
                <div class="track-steps mb-4">
                    <?php foreach ($flowSteps as $i => $step):
                        $done   = $currentIdx !== false && $i < $currentIdx;
                        $active = $currentIdx !== false && $i === $currentIdx;
                        $info   = $statuses[$step];
                    ?>
                    <div class="track-step <?= $done ? 'ts-done' : ($active ? 'ts-active' : '') ?>">
                        <div class="ts-circle">
                            <?php if ($done): ?>
                                <i class="bi bi-check-lg"></i>
                            <?php elseif ($active && $isDelayed && $step === 'preparing'): ?>
                                <i class="bi bi-hourglass-split"></i>
                            <?php else: ?>
                                <i class="bi <?= $info['icon'] ?>"></i>
                            <?php endif; ?>
                        </div>
                        <div class="ts-label">
                            <?= ($active && $isDelayed && $step === 'preparing') ? 'Running Late' : $info['label'] ?>
                        </div>
                    </div>
                    <?php if ($i < count($flowSteps) - 1): ?>
                    <div class="ts-connector <?= $done ? 'ts-connector-done' : '' ?>"></div>
                    <?php endif; ?>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>

                <!-- Order ref & slot -->
                <div class="d-flex justify-content-between align-items-center p-3 rounded" style="background:rgba(91,155,213,.12);border:1.5px solid rgba(29,70,102,.25)">
                    <div>
                        <div class="an-label mb-0">Order Reference</div>
                        <div class="fw-bold" style="font-family:monospace;color:var(--an-primary)">#<?= h($orderRef) ?></div>
                    </div>
                    <div class="text-end">
                        <div class="an-label mb-0">Pickup</div>
                        <div class="fw-semibold small"><?= fmtDate($order['slot_date']) ?></div>
                        <div class="text-muted small"><?= h($order['slot_time']) ?></div>
                    </div>
                </div>
            </div>

            <div class="modal-footer border-0 pt-0 gap-2">
                <a href="track.php?ref=<?= urlencode($orderRef) ?>" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-box-arrow-up-right me-1"></i>Full Tracking Page
                </a>
                <button type="button" class="btn-an-search py-2 px-4 border-0" data-bs-dismiss="modal">Close</button>
            </div>

        </div>
    </div>
</div>

<?php include '_foot.php'; ?>
