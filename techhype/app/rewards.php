<?php
include '_base.php';
require_login();

$user = auth();
$balance = get_points_balance();
$catalog = get_rewards_catalog();

// Handle redeem
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['redeem_reward'])) {
    $rewardId = $_POST['redeem_reward'];
    $reward = null;
    foreach ($catalog as $r) {
        if ($r['id'] === $rewardId) { $reward = $r; break; }
    }

    if (!$reward) {
        flash('error', 'Invalid reward.');
    } elseif ($balance < $reward['points']) {
        flash('error', 'Not enough points to redeem this reward.');
    } else {
        // Create voucher
        $code = generate_voucher_code();
        $expiresAt = date('Y-m-d H:i:s', strtotime('+30 days'));
        $stm = $db->prepare('INSERT INTO vouchers (user_id, code, type, value, max_discount, min_spend, expires_at) VALUES (?, ?, ?, ?, ?, ?, ?)');
        $stm->execute([$user->id, $code, $reward['type'], $reward['value'], $reward['max_discount'], $reward['min_spend'], $expiresAt]);

        // Deduct points
        add_points($user->id, -$reward['points'], 'redeem', 'Redeemed: ' . $reward['name'] . ' (' . $code . ')');

        flash('success', 'Redeemed! Your voucher code: ' . $code . ' (valid for 30 days)');
    }
    redirect('/rewards.php');
}

// Refresh balance after possible redeem
$balance = get_points_balance();
$vouchers = get_user_vouchers($user->id);

// Points history
$stm = $db->prepare('SELECT * FROM points_log WHERE user_id = ? ORDER BY created_at DESC LIMIT 50');
$stm->execute([$user->id]);
$history = $stm->fetchAll();

include '_head.php';
?>

<section class="brand-header" style="background: #1d1d1f;">
    <div class="container">
        <h1>My Rewards</h1>
        <p>Earn points on every purchase, redeem for vouchers & discounts</p>
    </div>
</section>

<section class="section">
    <div class="container">
        <!-- Points Balance Card -->
        <div class="rewards-balance-card">
            <div class="rewards-balance-left">
                <span class="rewards-label">Your Points Balance</span>
                <div class="rewards-points"><?= number_format($balance) ?></div>
                <span class="rewards-value">Worth <?= format_price(points_to_rm($balance)) ?> in discounts</span>
            </div>
            <div class="rewards-balance-right">
                <i class="fa-solid fa-coins"></i>
            </div>
        </div>

        <!-- How It Works -->
        <div class="rewards-how">
            <h3 class="rewards-section-title">How It Works</h3>
            <div class="rewards-steps">
                <div class="rewards-step">
                    <div class="rewards-step-icon"><i class="fa-solid fa-cart-shopping"></i></div>
                    <h4>Shop</h4>
                    <p>Place an order on TechHype</p>
                </div>
                <div class="rewards-step-arrow"><i class="fa-solid fa-arrow-right"></i></div>
                <div class="rewards-step">
                    <div class="rewards-step-icon"><i class="fa-solid fa-star"></i></div>
                    <h4>Earn</h4>
                    <p>Get 1 point for every RM 1 spent</p>
                </div>
                <div class="rewards-step-arrow"><i class="fa-solid fa-arrow-right"></i></div>
                <div class="rewards-step">
                    <div class="rewards-step-icon"><i class="fa-solid fa-gift"></i></div>
                    <h4>Redeem</h4>
                    <p>Exchange points for vouchers & perks</p>
                </div>
            </div>
        </div>

        <!-- Redeem Rewards -->
        <h3 class="rewards-section-title">Redeem Rewards</h3>
        <div class="rewards-grid">
            <?php foreach ($catalog as $r): ?>
            <div class="reward-card">
                <div class="reward-card-icon" style="background: <?= $r['color'] ?>;">
                    <i class="fa-solid <?= $r['icon'] ?>"></i>
                </div>
                <div class="reward-card-info">
                    <h4><?= $r['name'] ?></h4>
                    <p><?= $r['desc'] ?></p>
                    <div class="reward-card-cost">
                        <i class="fa-solid fa-coins"></i> <?= number_format($r['points']) ?> points
                    </div>
                </div>
                <form method="POST">
                    <?php if ($balance >= $r['points']): ?>
                    <button type="submit" name="redeem_reward" value="<?= $r['id'] ?>" class="btn btn-primary btn-sm" onclick="return confirm('Redeem <?= $r['name'] ?> for <?= number_format($r['points']) ?> points?')">Redeem</button>
                    <?php else: ?>
                    <button type="button" class="btn btn-sm btn-disabled" disabled title="Need <?= number_format($r['points'] - $balance) ?> more points">
                        <?= number_format($r['points'] - $balance) ?> more
                    </button>
                    <?php endif; ?>
                </form>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- My Vouchers -->
        <h3 class="rewards-section-title" style="margin-top:50px;">My Vouchers</h3>
        <?php if (empty($vouchers)): ?>
            <div class="empty-state" style="padding:40px;">
                <i class="fa-solid fa-ticket"></i>
                <h3>No vouchers yet</h3>
                <p>Redeem your points above to get discount vouchers!</p>
            </div>
        <?php else: ?>
        <div class="vouchers-grid">
            <?php foreach ($vouchers as $v): ?>
            <div class="voucher-card">
                <div class="voucher-left">
                    <?php if ($v->type === 'fixed'): ?>
                        <div class="voucher-amount"><?= format_price($v->value) ?></div>
                        <div class="voucher-type">OFF</div>
                    <?php elseif ($v->type === 'percent'): ?>
                        <div class="voucher-amount"><?= intval($v->value) ?>%</div>
                        <div class="voucher-type">OFF</div>
                    <?php else: ?>
                        <div class="voucher-amount"><i class="fa-solid fa-truck-fast"></i></div>
                        <div class="voucher-type">FREE SHIP</div>
                    <?php endif; ?>
                </div>
                <div class="voucher-right">
                    <div class="voucher-code"><?= $v->code ?></div>
                    <?php if ($v->min_spend > 0): ?>
                        <div class="voucher-condition">Min. spend <?= format_price($v->min_spend) ?></div>
                    <?php endif; ?>
                    <?php if ($v->max_discount): ?>
                        <div class="voucher-condition">Max discount <?= format_price($v->max_discount) ?></div>
                    <?php endif; ?>
                    <div class="voucher-expiry">Expires: <?= date('d M Y', strtotime($v->expires_at)) ?></div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <!-- Points History -->
        <h3 class="rewards-section-title" style="margin-top:50px;">Points History</h3>
        <?php if (empty($history)): ?>
            <div class="empty-state">
                <i class="fa-solid fa-coins"></i>
                <h3>No points yet</h3>
                <p>Start shopping to earn reward points!</p>
                <a href="<?= $base ?>/products.php" class="btn btn-primary">Shop Now</a>
            </div>
        <?php else: ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Description</th>
                    <th>Type</th>
                    <th>Points</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($history as $h): ?>
                <tr>
                    <td><?= date('d M Y, h:i A', strtotime($h->created_at)) ?></td>
                    <td><?= clean($h->description) ?></td>
                    <td>
                        <?php if ($h->type === 'earn'): ?>
                            <span class="status-badge status-active">Earned</span>
                        <?php elseif ($h->type === 'redeem'): ?>
                            <span class="status-badge status-pending">Redeemed</span>
                        <?php else: ?>
                            <span class="status-badge status-cancelled">Refund</span>
                        <?php endif; ?>
                    </td>
                    <td class="points-<?= $h->points > 0 ? 'positive' : 'negative' ?>">
                        <?= $h->points > 0 ? '+' : '' ?><?= number_format($h->points) ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</section>

<?php include '_foot.php'; ?>
