<?php
session_start();
require '_base.php';
require '_functions.php';

$ref    = trim($_GET['ref'] ?? '');
$order  = null;
$items  = [];
$error  = '';

if ($ref !== '') {
    // stall_id is NULL for orders that mix items from several stalls,
    // so join the stall loosely and fall back to the stalls on the items.
    $stmt = $pdo->prepare("
        SELECT o.*, s.stall_name, s.cuisine, ps.slot_date, ps.slot_time
        FROM orders o
        LEFT JOIN stalls s ON s.stall_id = o.stall_id
        JOIN pickup_slots ps ON ps.slot_id = o.slot_id
        WHERE o.order_reference = ?
    ");
    $stmt->execute([$ref]);
    $order = $stmt->fetch() ?: null;

    if ($order) {
        $items = getOrderItems($pdo, (int)$order['order_id']);
        if (empty($order['stall_name'])) {
            $stallNames = array_values(array_unique(array_column($items, 'stall_name')));
            $order['stall_name'] = implode(', ', $stallNames);
            $order['cuisine'] = count($stallNames) > 1 ? 'Multiple stalls' : '';
        }
    } else {
        $error = 'No order found with that reference. Please check and try again.';
    }
}

$statuses = [
    'pending'   => ['label' => 'Order Received',    'icon' => 'bi-clock',              'desc' => 'Your order has been received and is waiting to be confirmed by the stall.'],
    'preparing' => ['label' => 'Preparing',          'icon' => 'bi-fire',               'desc' => 'The stall is now cooking and preparing your food.'],
    'delayed'   => ['label' => 'Running a Bit Late', 'icon' => 'bi-hourglass-split',    'desc' => 'It\'s busy right now! Your order is taking a little longer than usual. Please hang tight.'],
    'ready'     => ['label' => 'Ready for Pickup!',  'icon' => 'bi-bag-check-fill',     'desc' => 'Your food is ready! Head to the stall to collect your order.'],
    'collected' => ['label' => 'Collected',          'icon' => 'bi-check-circle-fill',  'desc' => 'Order collected. Enjoy your meal!'],
    'cancelled' => ['label' => 'Cancelled',          'icon' => 'bi-x-circle-fill',      'desc' => 'This order has been cancelled. Please contact the stall if you have questions.'],
];

$flowSteps = ['pending', 'preparing', 'ready', 'collected'];

$pageTitle = 'Track Order';
$cssPath   = '';
?>
<?php include '_head.php'; ?>

<section class="an-hero text-white" style="padding:40px 0 60px">
    <div class="container text-center">
        <h1 class="fw-bold mb-2">Track Your Order</h1>
        <p class="mb-0" style="opacity:.85">Enter your order reference to see live status updates.</p>
    </div>
</section>

<div class="container page-section">
    <div class="row justify-content-center">
        <div class="col-lg-7">

            <!-- Search form -->
            <div class="card border-0 shadow-sm mb-4" style="border-radius:var(--radius);margin-top:-28px;position:relative;z-index:10">
                <div class="card-body p-4">
                    <form method="GET" action="" class="d-flex gap-2">
                        <input type="text" name="ref" class="an-input flex-grow-1"
                               placeholder="e.g. ORD-A1B2C3D4"
                               value="<?= h($ref) ?>" required>
                        <button type="submit" class="btn-an-search px-4">
                            <i class="bi bi-search me-1"></i>Track
                        </button>
                    </form>
                </div>
            </div>

            <?php if ($error): ?>
            <div class="alert alert-danger border-0 shadow-sm"><?= h($error) ?></div>
            <?php endif; ?>

            <?php if ($order):
                $currentStatus = $order['order_status'];
                $isCancelled   = $currentStatus === 'cancelled';
                $isDelayed     = $currentStatus === 'delayed';
                $statusInfo    = $statuses[$currentStatus] ?? $statuses['pending'];

                // For the step bar, treat delayed as still in "preparing"
                $barStatus = $isDelayed ? 'preparing' : $currentStatus;
                $currentIdx = array_search($barStatus, $flowSteps);
            ?>

            <!-- Status banner -->
            <div class="status-banner mb-4 <?= $isCancelled ? 'status-cancelled' : ($currentStatus === 'ready' ? 'status-ready' : ($isDelayed ? 'status-delayed' : '')) ?>">
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
            <!-- Progress stepper -->
            <div class="card border-0 shadow-sm mb-4 p-4" style="border-radius:var(--radius)">
                <div class="track-steps">
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
                            <?= $active && $isDelayed && $step === 'preparing' ? 'Running Late' : $info['label'] ?>
                        </div>
                    </div>
                    <?php if ($i < count($flowSteps) - 1): ?>
                    <div class="ts-connector <?= $done ? 'ts-connector-done' : '' ?>"></div>
                    <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Order details -->
            <div class="card border-0 shadow-sm" style="border-radius:var(--radius)">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start mb-3 flex-wrap gap-2">
                        <div>
                            <div class="an-label mb-0">Order Reference</div>
                            <div class="fw-bold fs-5" style="font-family:monospace;color:var(--an-primary)">#<?= h($order['order_reference']) ?></div>
                        </div>
                        <div class="text-end">
                            <div class="an-label mb-0">Stall</div>
                            <div class="fw-semibold"><?= h($order['stall_name']) ?></div>
                            <div class="small text-muted"><?= h($order['cuisine']) ?></div>
                        </div>
                    </div>

                    <hr>

                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <div class="an-label mb-0">Customer</div>
                            <div class="fw-semibold"><?= h($order['customer_name']) ?></div>
                        </div>
                        <div class="col-6 text-end">
                            <div class="an-label mb-0">Pickup Slot</div>
                            <div class="fw-semibold"><?= fmtDate($order['slot_date']) ?></div>
                            <div class="small text-muted"><?= h($order['slot_time']) ?></div>
                        </div>
                    </div>

                    <div class="an-label mb-2">Items Ordered</div>
                    <?php foreach ($items as $item): ?>
                    <div class="price-row">
                        <span>
                            <?= (int)$item['quantity'] ?> × <?= h($item['item_name']) ?>
                            <span class="text-muted small">· <?= h($item['stall_name']) ?></span>
                            <?php if (!empty($item['options'])): ?>
                            <br><span class="opt-summary"><?= h($item['options']) ?></span>
                            <?php endif; ?>
                        </span>
                        <span><?= fmtMoney((float)$item['quantity'] * (float)$item['unit_price']) ?></span>
                    </div>
                    <?php endforeach; ?>

                    <div class="border-top mt-3 pt-3 d-flex justify-content-between align-items-center">
                        <span class="fw-bold">Total</span>
                        <span class="price-total"><?= fmtMoney((float)$order['total_price']) ?></span>
                    </div>
                </div>
            </div>

            <div class="text-center mt-4">
                <a href="index.php" class="btn btn-outline-secondary me-2"><i class="bi bi-house me-1"></i>Home</a>
                <a href="track.php?ref=<?= urlencode($order['order_reference']) ?>" class="btn-an-search text-decoration-none"><i class="bi bi-arrow-clockwise me-1"></i>Refresh</a>
            </div>

            <?php endif; ?>

            <?php if (!$order && $ref === ''): ?>
            <div class="text-center py-4 text-muted">
                <i class="bi bi-receipt fs-1 d-block mb-3 opacity-50"></i>
                <p class="mb-0">Enter your order reference number above to check the status of your food order.</p>
            </div>
            <?php endif; ?>

        </div>
    </div>
</div>

<?php include '_foot.php'; ?>
