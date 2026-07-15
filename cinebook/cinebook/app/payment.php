<?php
session_start();
require '_base.php';
require '_functions.php';

if (empty($_SESSION['last_order_id']) || empty($_SESSION['last_order_ref'])) {
    redirect('index.php');
}

$orderId   = (int)$_SESSION['last_order_id'];
$orderRef  = $_SESSION['last_order_ref'];
$total     = (float)($_SESSION['last_order_total'] ?? 0);
$stallName = $_SESSION['last_stall_name'] ?? 'Stall';

$pageTitle = 'Payment';
$cssPath   = '';
?>
<?php include '_head.php'; ?>

<div class="container page-section">
    <div class="row justify-content-center">
        <div class="col-lg-9">

            <!-- Page header -->
            <div class="mb-4">
                <h4 class="fw-bold mb-0"><i class="bi bi-credit-card-2-front me-2" style="color:var(--an-primary)"></i>Secure Payment</h4>
                <p class="text-muted small mb-0">Complete your payment to confirm your order from <strong><?= h($stallName) ?></strong></p>
            </div>

            <div class="row g-4">

                <!-- ── Left: Payment Form ── -->
                <div class="col-lg-7">
                    <div class="pay-card">

                        <!-- Method tabs -->
                        <div class="pay-methods mb-4">
                            <button class="pay-method-btn active" data-method="card">
                                <i class="bi bi-credit-card"></i> Card
                            </button>
                            <button class="pay-method-btn" data-method="tng">
                                <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/1/15/Touch_%27n_Go_eWallet_logo.svg/200px-Touch_%27n_Go_eWallet_logo.svg.png"
                                     alt="TNG" style="height:18px;object-fit:contain"> TnG
                            </button>
                            <button class="pay-method-btn" data-method="fpx">
                                <i class="bi bi-bank"></i> FPX
                            </button>
                            <button class="pay-method-btn" data-method="duitnow">
                                <i class="bi bi-qr-code"></i> DuitNow
                            </button>
                        </div>

                        <!-- ── CARD ── -->
                        <div id="method-card" class="method-panel">

                            <!-- Flip card preview -->
                            <div class="card-preview-wrap mb-4">
                                <div class="card-flip" id="cardFlip">
                                    <div class="card-front">
                                        <div class="d-flex justify-content-between align-items-start mb-3">
                                            <div class="card-chip"></div>
                                            <div class="card-network" id="cardNetwork">VISA</div>
                                        </div>
                                        <div class="card-number-display" id="cardNumDisplay">•••• •••• •••• ••••</div>
                                        <div class="d-flex justify-content-between align-items-end mt-3">
                                            <div>
                                                <div style="font-size:.6rem;opacity:.7;letter-spacing:1px">CARDHOLDER</div>
                                                <div class="fw-semibold" id="cardNameDisplay" style="font-size:.85rem;letter-spacing:1px">YOUR NAME</div>
                                            </div>
                                            <div class="text-end">
                                                <div style="font-size:.6rem;opacity:.7;letter-spacing:1px">EXPIRES</div>
                                                <div class="fw-semibold" id="cardExpDisplay" style="font-size:.85rem">MM / YY</div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-back">
                                        <div class="card-magstripe"></div>
                                        <div class="card-cvv-row">
                                            <div class="card-cvv-label">CVV</div>
                                            <div class="card-cvv-box" id="cardCvvDisplay">•••</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="an-label">Card Number</label>
                                    <input type="text" id="cardNumber" class="an-input" placeholder="1234 5678 9012 3456" maxlength="19" inputmode="numeric">
                                </div>
                                <div class="col-12">
                                    <label class="an-label">Cardholder Name</label>
                                    <input type="text" id="cardName" class="an-input" placeholder="Name as on card">
                                </div>
                                <div class="col-6">
                                    <label class="an-label">Expiry Date</label>
                                    <input type="text" id="cardExpiry" class="an-input" placeholder="MM / YY" maxlength="7" inputmode="numeric">
                                </div>
                                <div class="col-6">
                                    <label class="an-label">CVV</label>
                                    <input type="text" id="cardCvv" class="an-input" placeholder="•••" maxlength="3" inputmode="numeric">
                                </div>
                            </div>
                        </div>

                        <!-- ── TOUCH N GO ── -->
                        <div id="method-tng" class="method-panel" style="display:none">
                            <div class="ewallet-panel text-center">
                                <div class="ewallet-logo mb-3" style="background:linear-gradient(135deg,#00b4d8,#0077b6)">
                                    <i class="bi bi-phone-fill text-white" style="font-size:2.5rem"></i>
                                </div>
                                <h5 class="fw-bold mb-1">Touch 'n Go eWallet</h5>
                                <p class="text-muted small mb-4">Open your TnG app and scan the QR code to pay</p>
                                <div class="fake-qr mx-auto mb-4">
                                    <div class="qr-inner">
                                        <i class="bi bi-qr-code" style="font-size:5rem;color:#1a1a1a"></i>
                                    </div>
                                    <div class="small text-muted mt-2">Expires in <span id="tngTimer">05:00</span></div>
                                </div>
                                <div class="d-flex gap-2 justify-content-center">
                                    <span class="badge bg-success-subtle text-success px-3 py-2"><i class="bi bi-shield-check me-1"></i>Secured by TnG</span>
                                    <span class="badge bg-light text-muted px-3 py-2">RM <?= number_format($total, 2) ?></span>
                                </div>
                            </div>
                        </div>

                        <!-- ── FPX ── -->
                        <div id="method-fpx" class="method-panel" style="display:none">
                            <div class="text-center mb-4">
                                <div class="ewallet-logo mb-3" style="background:linear-gradient(135deg,#e85d04,#c44d00)">
                                    <i class="bi bi-bank text-white" style="font-size:2.5rem"></i>
                                </div>
                                <h5 class="fw-bold mb-1">Online Banking (FPX)</h5>
                                <p class="text-muted small">Select your bank to proceed</p>
                            </div>
                            <div class="bank-grid">
                                <?php
                                $banks = [
                                    ['Maybank2u','#f7c500','bi-bank2'],
                                    ['CIMB Clicks','#c00','bi-bank'],
                                    ['Public Bank','#003087','bi-bank'],
                                    ['RHB Bank','#004b87','bi-bank2'],
                                    ['Hong Leong','#e41e20','bi-bank'],
                                    ['AmBank','#d71920','bi-bank'],
                                    ['Bank Islam','#006837','bi-bank2'],
                                    ['BSN','#003087','bi-bank'],
                                ];
                                foreach ($banks as $b): ?>
                                <button class="bank-btn" type="button" data-bank="<?= h($b[0]) ?>">
                                    <i class="bi <?= $b[2] ?>" style="color:<?= $b[1] ?>;font-size:1.3rem"></i>
                                    <span><?= $b[0] ?></span>
                                </button>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <!-- ── DUITNOW ── -->
                        <div id="method-duitnow" class="method-panel" style="display:none">
                            <div class="ewallet-panel text-center">
                                <div class="ewallet-logo mb-3" style="background:linear-gradient(135deg,#e40046,#b3003a)">
                                    <i class="bi bi-qr-code-scan text-white" style="font-size:2.5rem"></i>
                                </div>
                                <h5 class="fw-bold mb-1">DuitNow QR</h5>
                                <p class="text-muted small mb-4">Scan with any Malaysian banking app</p>
                                <div class="fake-qr mx-auto mb-4">
                                    <div class="qr-inner">
                                        <i class="bi bi-qr-code" style="font-size:5rem;color:#1a1a1a"></i>
                                    </div>
                                    <div class="small text-muted mt-2">Ref: <?= h($orderRef) ?></div>
                                </div>
                                <div class="d-flex gap-2 justify-content-center flex-wrap">
                                    <span class="badge bg-danger-subtle text-danger px-3 py-2"><i class="bi bi-shield-check me-1"></i>Secured by DuitNow</span>
                                    <span class="badge bg-light text-muted px-3 py-2">RM <?= number_format($total, 2) ?></span>
                                </div>
                            </div>
                        </div>

                        <!-- Pay button -->
                        <div class="mt-4">
                            <button id="payBtn" class="btn-an-search w-100 py-3" style="font-size:1.05rem">
                                <i class="bi bi-lock-fill me-2"></i>Pay RM <?= number_format($total, 2) ?>
                            </button>
                            <p class="text-center text-muted small mt-2 mb-0">
                                <i class="bi bi-shield-lock-fill me-1" style="color:#16a34a"></i>Secured with 256-bit SSL encryption · PCI DSS compliant
                            </p>
                        </div>
                    </div>
                </div>

                <!-- ── Right: Order Summary ── -->
                <div class="col-lg-5">
                    <div class="summary-card sticky-top" style="top:90px">
                        <div class="card-header"><i class="bi bi-receipt me-2"></i>Order Summary</div>
                        <div class="p-3">
                            <div class="price-row">
                                <span class="small text-muted">Order Ref</span>
                                <span class="fw-bold" style="font-family:monospace;color:var(--an-primary)">#<?= h($orderRef) ?></span>
                            </div>
                            <div class="price-row">
                                <span class="small text-muted">Stall(s)</span>
                                <span class="text-end"><?= h($stallName) ?></span>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="fw-bold">Total</span>
                                <span class="price-total">RM <?= number_format($total, 2) ?></span>
                            </div>
                            <div class="mt-3 p-2 rounded" style="background:#f0fdf4;border:1px solid #bbf7d0">
                                <div class="small text-success"><i class="bi bi-shield-check me-1"></i>256-bit SSL Encrypted · Secure Checkout</div>
                            </div>
                        </div>
                    </div>
                    <a href="order.php" class="btn btn-outline-secondary w-100 mt-3">
                        <i class="bi bi-arrow-left me-1"></i>Back to Order
                    </a>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- ── Processing Overlay ─────────────────────────────────────────────────── -->
<div id="payOverlay" style="display:none;position:fixed;inset:0;background:rgba(255,248,240,.96);z-index:9999;display:none;flex-direction:column;align-items:center;justify-content:center">
    <div id="payStateProcessing" class="text-center">
        <div class="pay-spinner mb-4"></div>
        <h4 class="fw-bold mb-1">Processing Payment…</h4>
        <p class="text-muted">Please wait, do not close this page.</p>
    </div>
    <div id="payStateSuccess" class="text-center" style="display:none">
        <div class="pay-success-icon mb-4">
            <i class="bi bi-check-lg"></i>
        </div>
        <h4 class="fw-bold mb-1">Payment Successful!</h4>
        <p class="text-muted">Redirecting you to your order confirmation…</p>
    </div>
</div>

<script>
// ── Method tabs ────────────────────────────────────────────────────────────
document.querySelectorAll('.pay-method-btn').forEach(function(btn) {
    btn.addEventListener('click', function() {
        document.querySelectorAll('.pay-method-btn').forEach(function(b){ b.classList.remove('active'); });
        document.querySelectorAll('.method-panel').forEach(function(p){ p.style.display = 'none'; });
        btn.classList.add('active');
        document.getElementById('method-' + btn.dataset.method).style.display = '';
    });
});

// ── Bank selection ─────────────────────────────────────────────────────────
document.querySelectorAll('.bank-btn').forEach(function(btn) {
    btn.addEventListener('click', function() {
        document.querySelectorAll('.bank-btn').forEach(function(b){ b.classList.remove('selected'); });
        btn.classList.add('selected');
    });
});

// ── Card inputs → live preview ─────────────────────────────────────────────
var cardNumber = document.getElementById('cardNumber');
var cardName   = document.getElementById('cardName');
var cardExpiry = document.getElementById('cardExpiry');
var cardCvv    = document.getElementById('cardCvv');
var cardFlip   = document.getElementById('cardFlip');

if (cardNumber) {
    cardNumber.addEventListener('input', function() {
        var v = this.value.replace(/\D/g,'').substring(0,16);
        var formatted = v.replace(/(.{4})/g,'$1 ').trim();
        this.value = formatted;
        var display = formatted || '•••• •••• •••• ••••';
        while (display.replace(/\s/g,'').length < 16) display += '•';
        // reformat into groups
        var raw = display.replace(/\s/g,'');
        display = raw.match(/.{1,4}/g).join(' ');
        document.getElementById('cardNumDisplay').textContent = display;

        var net = 'VISA';
        if (v[0] === '5') net = 'MASTERCARD';
        else if (v[0] === '3') net = 'AMEX';
        else if (v[0] === '6') net = 'MAESTRO';
        document.getElementById('cardNetwork').textContent = net;
    });
}

if (cardName) {
    cardName.addEventListener('input', function() {
        document.getElementById('cardNameDisplay').textContent = this.value.toUpperCase() || 'YOUR NAME';
    });
}

if (cardExpiry) {
    cardExpiry.addEventListener('input', function() {
        var v = this.value.replace(/\D/g,'');
        if (v.length >= 3) v = v.substring(0,2) + ' / ' + v.substring(2,4);
        else if (v.length === 2) v = v + ' / ';
        this.value = v;
        document.getElementById('cardExpDisplay').textContent = v || 'MM / YY';
    });
}

if (cardCvv) {
    cardCvv.addEventListener('focus', function() { cardFlip && cardFlip.classList.add('flipped'); });
    cardCvv.addEventListener('blur',  function() { cardFlip && cardFlip.classList.remove('flipped'); });
    cardCvv.addEventListener('input', function() {
        var v = this.value.replace(/\D/g,'');
        this.value = v;
        document.getElementById('cardCvvDisplay').textContent = v || '•••';
    });
}

// ── TnG countdown timer ────────────────────────────────────────────────────
var tngTimer = document.getElementById('tngTimer');
if (tngTimer) {
    var secs = 300;
    setInterval(function() {
        if (secs <= 0) { tngTimer.textContent = '00:00'; return; }
        secs--;
        var m = Math.floor(secs / 60);
        var s = secs % 60;
        tngTimer.textContent = (m < 10 ? '0' : '') + m + ':' + (s < 10 ? '0' : '') + s;
    }, 1000);
}

// ── Pay button ─────────────────────────────────────────────────────────────
document.getElementById('payBtn').addEventListener('click', function() {
    var overlay     = document.getElementById('payOverlay');
    var processing  = document.getElementById('payStateProcessing');
    var success     = document.getElementById('payStateSuccess');

    overlay.style.display = 'flex';

    setTimeout(function() {
        processing.style.display = 'none';
        success.style.display = '';
    }, 2200);

    setTimeout(function() {
        window.location.href = 'confirmation.php';
    }, 3600);
});
</script>

<?php include '_foot.php'; ?>
