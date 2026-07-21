<?php
include '_base.php';
require_login();

if (empty($_SESSION['cart'])) {
    flash('error', 'Your cart is empty.');
    redirect('/cart.php');
}

// Get cart items
$items = [];
$total = 0;
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
    $stm = $db->prepare('SELECT * FROM products WHERE id = ?');
    $stm->execute([$productId]);
    $p = $stm->fetch();
    if ($p) {
        if ($variantPrice) {
            $price = floatval(str_replace(['RM ', ',', ' '], '', $variantPrice));
        } else {
            $price = $p->sale_price ?? $p->price;
        }
        $subtotal = $price * $qty;
        $total += $subtotal;
        $items[] = (object)[
            'product' => $p,
            'qty' => $qty,
            'price' => $price,
            'subtotal' => $subtotal,
            'color' => is_array($item) ? ($item['color'] ?? '') : '',
            'band_color' => is_array($item) ? ($item['band_color'] ?? '') : '',
            'storage' => is_array($item) ? ($item['storage'] ?? '') : '',
        ];
    }
}

$errors = [];
$user = auth();
$pointsBalance = get_points_balance();
$maxRedeemable = $pointsBalance;
$maxDiscount = points_to_rm($maxRedeemable);
if ($maxDiscount > $total) {
    $maxRedeemable = (int)($total * 100);
    $maxDiscount = $total;
}

// Get user's active vouchers
$userVouchers = get_user_vouchers($user->id);
$appliedVoucher = null;
$voucherDiscount = 0;

// Handle voucher apply/remove
if (isset($_POST['apply_voucher_code'])) {
    $_SESSION['checkout_voucher'] = clean($_POST['apply_voucher_code']);
    redirect('/checkout.php');
}
if (isset($_GET['remove_voucher'])) {
    unset($_SESSION['checkout_voucher']);
    redirect('/checkout.php');
}

// Check if a voucher is applied
if (!empty($_SESSION['checkout_voucher'])) {
    $vCode = $_SESSION['checkout_voucher'];
    $stm = $db->prepare('SELECT * FROM vouchers WHERE code = ? AND user_id = ? AND status = "active" AND expires_at > NOW()');
    $stm->execute([$vCode, $user->id]);
    $appliedVoucher = $stm->fetch();
    if ($appliedVoucher) {
        $voucherDiscount = apply_voucher_discount($appliedVoucher, $total);
    } else {
        unset($_SESSION['checkout_voucher']);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name    = clean($_POST['name'] ?? '');
    $phone   = clean($_POST['phone'] ?? '');
    $address = clean($_POST['address'] ?? '');
    $payment = clean($_POST['payment'] ?? 'cod');
    $notes   = clean($_POST['notes'] ?? '');
    $usePoints = isset($_POST['use_points']) ? intval($_POST['redeem_points']) : 0;

    // Validate points
    if ($usePoints > 0) {
        if ($usePoints > $pointsBalance) $usePoints = $pointsBalance;
        if ($usePoints < 0) $usePoints = 0;
        $discount = points_to_rm($usePoints);
        if ($discount > $total) {
            $usePoints = (int)($total * 100);
            $discount = $total;
        }
    } else {
        $usePoints = 0;
        $discount = 0;
    }

    // Re-check voucher discount
    $postVoucherDiscount = 0;
    if ($appliedVoucher) {
        $postVoucherDiscount = apply_voucher_discount($appliedVoucher, $total);
    }

    $finalTotal = $total - $discount - $postVoucherDiscount;

    if (!$name) $errors[] = 'Name is required.';
    if (!$phone) $errors[] = 'Phone is required.';
    if (!$address) $errors[] = 'Address is required.';

    if (empty($errors)) {
        // Create order
        $stm = $db->prepare('INSERT INTO orders (user_id, total, shipping_name, shipping_phone, shipping_address, payment_method, notes) VALUES (?, ?, ?, ?, ?, ?, ?)');
        $stm->execute([$user->id, $finalTotal, $name, $phone, $address, $payment, $notes]);
        $order_id = $db->lastInsertId();

        // Create order items + reduce stock
        foreach ($items as $item) {
            $stm = $db->prepare('INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)');
            $stm->execute([$order_id, $item->product->id, $item->qty, $item->price]);

            $stm = $db->prepare('UPDATE products SET stock = stock - ? WHERE id = ?');
            $stm->execute([$item->qty, $item->product->id]);
        }

        // Redeem points (deduct)
        if ($usePoints > 0) {
            add_points($user->id, -$usePoints, 'redeem', 'Redeemed for Order #' . str_pad($order_id, 5, '0', STR_PAD_LEFT), $order_id);
        }

        // Mark voucher as used
        if ($appliedVoucher && $postVoucherDiscount > 0) {
            $db->prepare('UPDATE vouchers SET status = "used", used_order_id = ? WHERE id = ?')->execute([$order_id, $appliedVoucher->id]);
            unset($_SESSION['checkout_voucher']);
        }

        // Earn points based on final total
        $earnedPoints = rm_to_points($finalTotal);
        if ($earnedPoints > 0) {
            add_points($user->id, $earnedPoints, 'earn', 'Earned from Order #' . str_pad($order_id, 5, '0', STR_PAD_LEFT), $order_id);
        }

        // Clear cart
        unset($_SESSION['cart']);

        flash('success', 'Order placed successfully! You earned ' . $earnedPoints . ' points.');
        redirect("/receipt.php?id=$order_id");
    }
}

include '_head.php';
?>

<section class="brand-header" style="background: #1d1d1f;">
    <div class="container">
        <h1>Checkout</h1>
        <p>Complete your order</p>
    </div>
</section>

<section class="section">
    <div class="container">
        <?php if ($errors): ?>
            <div class="alert alert-error">
                <?php foreach ($errors as $e): ?><div><?= $e ?></div><?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div class="checkout-layout">
            <div class="checkout-form">
                <h3>Shipping Details</h3>
                <form method="POST">
                    <div class="form-group">
                        <label>Full Name *</label>
                        <input type="text" name="name" required value="<?= clean($_POST['name'] ?? $user->name) ?>">
                    </div>
                    <div class="form-group">
                        <label>Phone *</label>
                        <input type="text" name="phone" required value="<?= clean($_POST['phone'] ?? $user->phone ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label>Shipping Address *</label>
                        <textarea name="address" rows="3" required><?= clean($_POST['address'] ?? $user->address ?? '') ?></textarea>
                    </div>
                    <div class="form-group">
                        <label style="font-weight:600;margin-bottom:10px;">Payment Method</label>
                        <div class="payment-methods">
                            <label class="payment-option selected" onclick="selectPayment(this)">
                                <input type="radio" name="payment" value="cod" checked>
                                <i class="fa-solid fa-money-bill-wave"></i>
                                <div class="payment-option-info">
                                    <h4>Cash on Delivery</h4>
                                    <p>Pay when you receive your order</p>
                                </div>
                            </label>
                            <label class="payment-option" onclick="selectPayment(this)">
                                <input type="radio" name="payment" value="card">
                                <i class="fa-solid fa-credit-card"></i>
                                <div class="payment-option-info">
                                    <h4>Credit / Debit Card</h4>
                                    <p>Visa, Mastercard, AMEX accepted</p>
                                </div>
                            </label>
                            <label class="payment-option" onclick="selectPayment(this)">
                                <input type="radio" name="payment" value="bank">
                                <i class="fa-solid fa-building-columns"></i>
                                <div class="payment-option-info">
                                    <h4>Online Banking (FPX)</h4>
                                    <p>Direct bank transfer via FPX</p>
                                </div>
                            </label>
                            <label class="payment-option" onclick="selectPayment(this)">
                                <input type="radio" name="payment" value="ewallet">
                                <i class="fa-solid fa-wallet"></i>
                                <div class="payment-option-info">
                                    <h4>E-Wallet</h4>
                                    <p>Touch 'n Go, GrabPay, Boost</p>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- Card Details (shown when card selected) -->
                    <div class="card-form" id="cardForm">
                        <h4 style="margin-bottom:15px;font-size:14px;"><i class="fa-solid fa-lock" style="color:#0071e3;"></i> Card Details</h4>
                        <div class="form-group">
                            <label>Card Number</label>
                            <input type="text" name="card_number" placeholder="1234 5678 9012 3456" maxlength="19">
                        </div>
                        <div class="form-row" style="display:flex;gap:15px;">
                            <div class="form-group" style="flex:1">
                                <label>Expiry Date</label>
                                <input type="text" name="card_expiry" placeholder="MM/YY" maxlength="5">
                            </div>
                            <div class="form-group" style="flex:1">
                                <label>CVV</label>
                                <input type="text" name="card_cvv" placeholder="123" maxlength="4">
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Cardholder Name</label>
                            <input type="text" name="card_name" placeholder="Name on card">
                        </div>
                    </div>

                    <!-- Bank Selection (shown when bank selected) -->
                    <div class="card-form" id="bankForm">
                        <h4 style="margin-bottom:15px;font-size:14px;"><i class="fa-solid fa-building-columns" style="color:#0071e3;"></i> Select Your Bank</h4>
                        <div class="form-group">
                            <select name="bank_name" class="sort-select" style="width:100%">
                                <option value="">-- Select Bank --</option>
                                <option value="maybank">Maybank</option>
                                <option value="cimb">CIMB Bank</option>
                                <option value="publicbank">Public Bank</option>
                                <option value="rhb">RHB Bank</option>
                                <option value="hongleong">Hong Leong Bank</option>
                                <option value="ambank">AmBank</option>
                                <option value="bankislam">Bank Islam</option>
                                <option value="bsn">BSN</option>
                            </select>
                        </div>
                    </div>

                    <!-- E-Wallet Selection (shown when ewallet selected) -->
                    <div class="card-form" id="ewalletForm">
                        <h4 style="margin-bottom:15px;font-size:14px;"><i class="fa-solid fa-wallet" style="color:#0071e3;"></i> Select E-Wallet</h4>
                        <div class="form-group">
                            <select name="ewallet_name" class="sort-select" style="width:100%">
                                <option value="">-- Select E-Wallet --</option>
                                <option value="tng">Touch 'n Go eWallet</option>
                                <option value="grabpay">GrabPay</option>
                                <option value="boost">Boost</option>
                                <option value="shopeepay">ShopeePay</option>
                            </select>
                        </div>
                    </div>

                    <!-- Apply Voucher -->
                    <div class="voucher-apply-box">
                        <label style="font-weight:600;font-size:14px;display:block;margin-bottom:10px;">
                            <i class="fa-solid fa-ticket" style="color:var(--primary);margin-right:6px;"></i> Voucher
                        </label>
                        <?php if ($appliedVoucher): ?>
                        <div class="voucher-applied">
                            <div class="voucher-applied-info">
                                <span class="voucher-applied-code"><?= $appliedVoucher->code ?></span>
                                <span class="voucher-applied-saving">-<?= format_price($voucherDiscount) ?></span>
                            </div>
                            <a href="?remove_voucher=1" class="voucher-remove"><i class="fa-solid fa-xmark"></i></a>
                        </div>
                        <?php else: ?>
                            <?php if (!empty($userVouchers)): ?>
                            <div class="voucher-select-list">
                                <?php foreach ($userVouchers as $v):
                                    $vDisc = apply_voucher_discount($v, $total);
                                    $canUse = $total >= $v->min_spend;
                                ?>
                                <div class="voucher-select-item <?= !$canUse ? 'voucher-unavailable' : '' ?>">
                                    <div class="voucher-select-left">
                                        <?php if ($v->type === 'fixed'): ?>
                                            <strong><?= format_price($v->value) ?> OFF</strong>
                                        <?php elseif ($v->type === 'percent'): ?>
                                            <strong><?= intval($v->value) ?>% OFF</strong>
                                        <?php else: ?>
                                            <strong>FREE SHIPPING</strong>
                                        <?php endif; ?>
                                        <small><?= $v->code ?></small>
                                        <?php if ($v->min_spend > 0): ?>
                                            <small style="color:<?= $canUse ? '#27ae60' : '#e74c3c' ?>;">Min. <?= format_price($v->min_spend) ?></small>
                                        <?php endif; ?>
                                    </div>
                                    <?php if ($canUse): ?>
                                    <form method="POST" style="margin:0;">
                                        <input type="hidden" name="apply_voucher_code" value="<?= $v->code ?>">
                                        <button type="submit" class="btn btn-sm btn-primary">Use</button>
                                    </form>
                                    <?php else: ?>
                                    <span style="font-size:11px;color:#999;">Min not met</span>
                                    <?php endif; ?>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <?php else: ?>
                            <div style="font-size:13px;color:var(--text-muted);padding:10px 0;">
                                No vouchers available. <a href="<?= $base ?>/rewards.php" style="color:var(--primary);font-weight:600;">Earn rewards</a>
                            </div>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>

                    <!-- Redeem Points -->
                    <?php if ($pointsBalance >= 100): ?>
                    <div class="redeem-points-box">
                        <label class="redeem-toggle">
                            <input type="checkbox" name="use_points" value="1" id="usePointsCheck" onchange="toggleRedeem()">
                            <i class="fa-solid fa-coins" style="color:#f39c12;margin-right:6px;"></i>
                            <strong>Redeem Points</strong>
                            <span style="color:var(--text-muted);margin-left:6px;">(<?= number_format($pointsBalance) ?> pts available = <?= format_price($maxDiscount) ?> off)</span>
                        </label>
                        <div class="redeem-slider" id="redeemSlider" style="display:none;">
                            <input type="range" name="redeem_points" id="redeemRange" min="100" max="<?= $maxRedeemable ?>" step="100" value="<?= $maxRedeemable ?>" oninput="updateRedeem()">
                            <div class="redeem-info">
                                <span>Using <strong id="redeemPts"><?= number_format($maxRedeemable) ?></strong> points</span>
                                <span>Discount: <strong id="redeemDiscount"><?= format_price($maxDiscount) ?></strong></span>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <div class="form-group" style="margin-top:15px;">
                        <label>Notes (optional)</label>
                        <textarea name="notes" rows="2"><?= clean($_POST['notes'] ?? '') ?></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary btn-block" id="placeOrderBtn">Place Order (<?= format_price($total - $voucherDiscount) ?>)</button>
                </form>
            </div>

            <div class="checkout-summary">
                <h3>Order Summary</h3>
                <div class="summary-items">
                    <?php foreach ($items as $item): ?>
                    <div class="summary-item">
                        <div>
                            <strong><?= clean($item->product->name) ?></strong>
                            <small>x<?= $item->qty ?></small>
                            <?php if ($item->color || $item->band_color || $item->storage): ?>
                            <small style="display:block;color:#888;font-size:11px;">
                                <?= $item->color ? ($item->band_color ? 'Case: ' : '') . clean($item->color) : '' ?>
                                <?= $item->band_color ? ' | Band: ' . clean($item->band_color) : '' ?>
                                <?= $item->storage ? ' | ' . clean($item->storage) : '' ?>
                            </small>
                            <?php endif; ?>
                        </div>
                        <span><?= format_price($item->subtotal) ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php if ($appliedVoucher && $voucherDiscount > 0): ?>
                <div class="summary-discount">
                    <span><i class="fa-solid fa-ticket"></i> Voucher (<?= $appliedVoucher->code ?>)</span>
                    <span style="color:#27ae60;font-weight:600;">-<?= format_price($voucherDiscount) ?></span>
                </div>
                <?php endif; ?>
                <div class="summary-total">
                    <span>Total</span>
                    <strong><?= format_price($total - $voucherDiscount) ?></strong>
                </div>
                <div class="summary-points-earn">
                    <i class="fa-solid fa-star" style="color:#f39c12;"></i>
                    You'll earn up to <strong><?= number_format(rm_to_points($total)) ?> points</strong> from this order
                </div>
            </div>
        </div>
    </div>
</section>

<script>
function selectPayment(el) {
    document.querySelectorAll('.payment-option').forEach(o => o.classList.remove('selected'));
    el.classList.add('selected');
    var val = el.querySelector('input[type="radio"]').value;
    document.getElementById('cardForm').classList.toggle('show', val === 'card');
    document.getElementById('bankForm').classList.toggle('show', val === 'bank');
    document.getElementById('ewalletForm').classList.toggle('show', val === 'ewallet');
}

var cartTotal = <?= $total ?>;
var voucherDiscount = <?= $voucherDiscount ?>;

function toggleRedeem() {
    var checked = document.getElementById('usePointsCheck').checked;
    document.getElementById('redeemSlider').style.display = checked ? 'block' : 'none';
    updateOrderBtn();
}
function updateRedeem() {
    var pts = parseInt(document.getElementById('redeemRange').value);
    var discount = pts / 100;
    document.getElementById('redeemPts').textContent = pts.toLocaleString();
    document.getElementById('redeemDiscount').textContent = 'RM ' + discount.toFixed(2);
    updateOrderBtn();
}
function updateOrderBtn() {
    var btn = document.getElementById('placeOrderBtn');
    var base = cartTotal - voucherDiscount;
    var checked = document.getElementById('usePointsCheck') && document.getElementById('usePointsCheck').checked;
    if (checked) {
        var pts = parseInt(document.getElementById('redeemRange').value);
        var pointsDiscount = pts / 100;
        var finalTotal = base - pointsDiscount;
        btn.textContent = 'Place Order (RM ' + finalTotal.toFixed(2) + ')';
    } else {
        btn.textContent = 'Place Order (RM ' + base.toFixed(2) + ')';
    }
}
</script>

<?php include '_foot.php'; ?>
