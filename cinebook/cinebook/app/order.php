<?php
session_start();
require '_base.php';
require '_functions.php';

// Optional: a stall chosen on the home page just scrolls the menu to that stall.
$focusStallId = (int)($_GET['stall_id'] ?? 0);

$stalls = array_values(array_filter(getStallsWithMenu($pdo), fn ($s) => (bool)$s['is_open'] && !empty($s['items'])));
if (empty($stalls)) {
    redirect('index.php');
}

// Every available item across all open stalls, indexed by id.
// Drink customization depends on the stall the item belongs to.
$menuById = [];
foreach ($stalls as $stall) {
    $stallIsDrink = isDrinkStall($stall);
    foreach ($stall['items'] as $item) {
        $item['is_drink'] = $stallIsDrink;
        $menuById[(int)$item['menu_item_id']] = $item;
    }
}

ensureUpcomingSlots($pdo);

$slotsStmt = $pdo->query("SELECT * FROM pickup_slots WHERE is_active = 1 AND remaining > 0 AND slot_date >= CURDATE() ORDER BY slot_date, slot_time");
$pickupSlots = $slotsStmt->fetchAll();

$drinkOptions = drinkOptionConfig();
$hasDrinkStall = (bool)array_filter($stalls, 'isDrinkStall');

$errors = [];
$selectedItems = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $customerName = trim($_POST['customer_name'] ?? '');
    $customerPhone = trim($_POST['customer_phone'] ?? '');
    $customerEmail = trim($_POST['customer_email'] ?? '');
    $notes = trim($_POST['notes'] ?? '');
    $slotId = (int)($_POST['slot_id'] ?? 0);

    $cartRaw = json_decode($_POST['cart_json'] ?? '[]', true);
    if (!is_array($cartRaw)) { $cartRaw = []; }

    if ($customerName === '') $errors[] = 'Customer name is required.';
    if ($customerPhone === '') $errors[] = 'Phone number is required.';
    if ($customerEmail === '' || !filter_var($customerEmail, FILTER_VALIDATE_EMAIL)) $errors[] = 'A valid email address is required.';
    if ($slotId <= 0) $errors[] = 'Please choose a pickup slot.';
    if (!captchaVerify($_POST['captcha'] ?? '')) $errors[] = 'Wrong security answer — please try the new question.';

    foreach ($cartRaw as $line) {
        $itemId = (int)($line['id'] ?? 0);
        $qty    = max(0, (int)($line['qty'] ?? 0));
        if ($itemId <= 0 || $qty <= 0 || !isset($menuById[$itemId])) {
            continue;
        }
        $item        = $menuById[$itemId];
        $unitPrice   = (float)$item['price'];
        $optionLabel = null;
        if ($item['is_drink']) {
            $custom = buildDrinkCustomization([
                'cup'    => $line['cup']    ?? '',
                'sugar'  => $line['sugar']  ?? '',
                'ice'    => $line['ice']    ?? '',
                'addons' => $line['addons'] ?? [],
            ]);
            $unitPrice  += $custom['extra'];
            $optionLabel = $custom['label'] !== '' ? $custom['label'] : null;
        }
        $selectedItems[] = [
            'menu_item_id' => $itemId,
            'item_name'    => $item['item_name'],
            'stall_id'     => (int)$item['stall_id'],
            'stall_name'   => $item['stall_name'],
            'price'        => $unitPrice,
            'quantity'     => $qty,
            'line_total'   => $qty * $unitPrice,
            'options'      => $optionLabel,
        ];
    }

    if (empty($selectedItems)) {
        $errors[] = 'Please choose at least one menu item.';
    }

    $slot = null;
    if ($slotId > 0) {
        $slotLookup = $pdo->prepare("SELECT * FROM pickup_slots WHERE slot_id = ? AND is_active = 1 AND remaining > 0 AND slot_date >= CURDATE()");
        $slotLookup->execute([$slotId]);
        $slot = $slotLookup->fetch() ?: null;
        if (!$slot) {
            $errors[] = 'The selected pickup slot is no longer available.';
        }
    }

    if (empty($errors)) {
        $totalPrice = array_reduce($selectedItems, fn ($sum, $item) => $sum + $item['line_total'], 0.0);
        $orderRef = generateOrderRef();

        $refCheck = $pdo->prepare("SELECT order_id FROM orders WHERE order_reference = ?");
        $refCheck->execute([$orderRef]);
        while ($refCheck->fetch()) {
            $orderRef = generateOrderRef();
            $refCheck->execute([$orderRef]);
        }

        // One stall → keep it on the order; several stalls → NULL (see order_items)
        $stallIds = array_values(array_unique(array_column($selectedItems, 'stall_id')));
        $orderStallId = count($stallIds) === 1 ? $stallIds[0] : null;
        $stallNames = array_values(array_unique(array_column($selectedItems, 'stall_name')));

        try {
            // DDL implicitly commits in MySQL, so these must run before the transaction
            ensureOrderItemOptions($pdo);
            ensureMultiStallOrders($pdo);

            $pdo->beginTransaction();

            $insertOrder = $pdo->prepare("
                INSERT INTO orders (order_reference, stall_id, slot_id, customer_name, customer_phone, customer_email, notes, total_price)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $insertOrder->execute([
                $orderRef,
                $orderStallId,
                $slotId,
                $customerName,
                $customerPhone,
                $customerEmail,
                $notes !== '' ? $notes : null,
                $totalPrice,
            ]);

            $orderId = (int)$pdo->lastInsertId();
            $insertItem = $pdo->prepare("
                INSERT INTO order_items (order_id, menu_item_id, quantity, unit_price, options)
                VALUES (?, ?, ?, ?, ?)
            ");

            foreach ($selectedItems as $item) {
                $insertItem->execute([$orderId, $item['menu_item_id'], $item['quantity'], $item['price'], $item['options'] ?? null]);
            }

            $updateSlot = $pdo->prepare("UPDATE pickup_slots SET remaining = remaining - 1 WHERE slot_id = ? AND remaining > 0");
            $updateSlot->execute([$slotId]);

            $pdo->commit();

            $_SESSION['last_order_id']    = $orderId;
            $_SESSION['last_order_ref']   = $orderRef;
            $_SESSION['last_order_total'] = $totalPrice;
            $_SESSION['last_stall_name']  = implode(', ', $stallNames);
            redirect('payment.php');
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            error_log('Order failed: ' . $e->getMessage());
            $errors[] = 'Could not place the order right now. Please try again.';
        }
    }
}

// New question on every render — a failed attempt always gets a fresh one
$captchaQuestion = captchaGenerate();

$pageTitle = 'Order Food';
$cssPath = '';
?>
<?php include '_head.php'; ?>

<div class="container page-section">
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-4 overflow-hidden" style="border-radius:var(--radius)">
                <div class="order-hero p-4 text-white">
                    <div class="d-flex align-items-center gap-3 flex-wrap">
                        <div class="stall-badge"><i class="bi bi-shop"></i></div>
                        <div>
                            <div class="text-uppercase small opacity-75">Food Court Menu</div>
                            <h3 class="mb-1 fw-bold">Order from any stall</h3>
                            <p class="mb-0 opacity-75">Mix and match — add food and drinks from different stalls into one order.</p>
                        </div>
                    </div>
                </div>
            </div>

            <?php if (!empty($errors)): ?>
            <div class="alert alert-danger border-0 shadow-sm">
                <strong>Please fix the following:</strong>
                <ul class="mb-0 mt-2">
                    <?php foreach ($errors as $error): ?>
                    <li><?= h($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>

            <form method="POST" action="" id="orderForm">
                <input type="hidden" name="cart_json" id="cartJson" value="<?= h($_POST['cart_json'] ?? '') ?>">
                <div class="card border-0 shadow-sm mb-4" style="border-radius:var(--radius)">
                    <div class="card-body p-4">
                        <h5 class="fw-bold mb-3">Menu — All Stalls</h5>
                        <?php foreach ($stalls as $sIdx => $stall): $stallIsDrink = isDrinkStall($stall); ?>
                        <div id="stall-<?= (int)$stall['stall_id'] ?>" style="scroll-margin-top:90px" class="<?= $sIdx > 0 ? 'mt-4' : '' ?>">
                            <div class="d-flex align-items-center gap-2 border-bottom pb-2 mb-3">
                                <span class="fs-3"><?= cuisineEmoji($stall['cuisine']) ?></span>
                                <div>
                                    <div class="fw-bold"><?= h($stall['stall_name']) ?></div>
                                    <div class="small text-muted"><?= h($stall['cuisine']) ?> · <?= h($stall['description']) ?></div>
                                </div>
                            </div>
                            <div class="row g-3">
                                <?php foreach ($stall['items'] as $item): ?>
                                <div class="col-md-6">
                                    <div class="menu-item-card h-100 d-flex flex-column"
                                         data-item-id="<?= (int)$item['menu_item_id'] ?>"
                                         data-item-name="<?= h($item['item_name']) ?>"
                                         data-stall="<?= h($stall['stall_name']) ?>"
                                         data-price="<?= h((string)$item['price']) ?>"
                                         data-drink="<?= $stallIsDrink ? '1' : '0' ?>">
                                        <?php if (!empty($item['image_path'])): ?>
                                        <img src="<?= h($item['image_path']) ?>" alt="<?= h($item['item_name']) ?>"
                                             class="mb-2" style="width:100%;height:130px;object-fit:cover;border-radius:var(--radius-sm,10px)">
                                        <?php endif; ?>
                                        <div class="flex-grow-1">
                                            <div class="fw-semibold"><?= h($item['item_name']) ?></div>
                                            <div class="small text-muted mb-2"><?= h($item['item_desc']) ?></div>
                                            <?php $rating = itemRating($item); ?>
                                            <div class="rating-stars mb-2" title="<?= $rating ?> out of 5">
                                                <?= renderStars($rating) ?>
                                                <span class="rating-score"><?= number_format($rating, 1) ?></span>
                                                <span class="rating-count">(<?= itemRatingCount($item) ?>)</span>
                                            </div>
                                            <div class="fw-bold text-primary"><?= fmtMoney((float)$item['price']) ?></div>
                                        </div>
                                        <button type="button" class="btn-an-primary add-item-btn mt-3 w-100">
                                            <i class="bi bi-<?= $stallIsDrink ? 'sliders' : 'plus-lg' ?> me-1"></i><?= $stallIsDrink ? 'Customise' : 'Add to order' ?>
                                        </button>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="card border-0 shadow-sm" style="border-radius:var(--radius)">
                    <div class="card-body p-4">
                        <h5 class="fw-bold mb-3">Customer Details</h5>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="an-label">Full Name</label>
                                <input type="text" name="customer_name" class="an-input" value="<?= h($_POST['customer_name'] ?? '') ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="an-label">Phone Number</label>
                                <input type="text" name="customer_phone" class="an-input" value="<?= h($_POST['customer_phone'] ?? '') ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="an-label">Email Address</label>
                                <input type="email" name="customer_email" class="an-input" value="<?= h($_POST['customer_email'] ?? '') ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="an-label">Pickup Slot</label>
                                <select name="slot_id" class="an-input" required>
                                    <option value="">Select a pickup slot</option>
                                    <?php foreach ($pickupSlots as $slot): ?>
                                    <option value="<?= $slot['slot_id'] ?>" <?= (int)($_POST['slot_id'] ?? 0) === (int)$slot['slot_id'] ? 'selected' : '' ?>>
                                        <?= fmtDate($slot['slot_date']) ?> - <?= h($slot['slot_time']) ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="an-label">Notes</label>
                                <textarea name="notes" class="an-input" rows="3" placeholder="Allergy note, no spicy, extra sauce, etc."><?= h($_POST['notes'] ?? '') ?></textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="an-label"><i class="bi bi-shield-lock me-1"></i>Security Check</label>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="fw-bold px-3 py-2 rounded" style="background:#f2f6fd;border:1px dashed #b8c4dd;font-family:monospace;font-size:1.05rem;white-space:nowrap;user-select:none">
                                        <?= h($captchaQuestion) ?>
                                    </span>
                                    <input type="text" name="captcha" class="an-input" placeholder="Answer" inputmode="numeric" autocomplete="off" required style="max-width:120px">
                                </div>
                                <div class="small text-muted mt-1">Solve the math question to prove you're human.</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-grid mt-4">
                    <button type="submit" class="btn-an-search py-3">
                        <i class="bi bi-check2-circle me-2"></i>Place Order
                    </button>
                </div>
            </form>
        </div>

        <div class="col-lg-4">
            <div class="summary-card sticky-top" style="top:92px">
                <div class="card-header"><i class="bi bi-bag-check me-2"></i>Order Summary</div>
                <div class="p-3">
                    <div class="price-row">
                        <span class="small text-muted">Stalls open</span>
                        <span class="fw-semibold"><?= count($stalls) ?></span>
                    </div>
                    <div class="small text-muted">You can combine food and drinks from different stalls in a single order.</div>
                    <hr>
                    <div class="small fw-semibold mb-2">Your Items</div>
                    <div id="cartLines"></div>
                    <p id="cartEmpty" class="text-muted small mb-0">No items yet — add from the menu.</p>
                    <div class="border-top mt-3 pt-3 d-flex justify-content-between align-items-center">
                        <span class="fw-bold">Total</span>
                        <span class="price-total" id="cartTotal">RM 0.00</span>
                    </div>
                </div>
            </div>

            <a href="index.php" class="btn btn-outline-secondary w-100 mt-3">
                <i class="bi bi-arrow-left me-1"></i>Back to Home
            </a>
        </div>
    </div>
</div>

<?php if ($hasDrinkStall): ?>
<!-- ── Customise Modal ────────────────────────────────────────────────────── -->
<div class="modal fade" id="customiseModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0" style="border-radius:var(--radius)">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title fw-bold" id="cmTitle">Customise</h5>
                    <div class="text-muted small" id="cmBase"></div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <?php foreach (['cup', 'sugar', 'ice'] as $grp): $g = $drinkOptions[$grp]; $first = true; ?>
                <div class="cm-group" data-group="<?= $grp ?>">
                    <div class="cm-group-head">
                        <span class="cm-group-title"><?= h($g['label']) ?></span>
                        <span class="cm-req">Required</span>
                    </div>
                    <?php foreach ($g['choices'] as $name => $extra): ?>
                    <label class="cm-opt">
                        <span class="cm-opt-name"><?= h($name) ?></span>
                        <span class="cm-opt-right">
                            <span class="cm-opt-price"><?= $extra > 0 ? '+' . fmtMoney((float)$extra) : 'Free' ?></span>
                            <input type="radio" name="cm_<?= $grp ?>" value="<?= h($name) ?>"
                                   data-price="<?= number_format((float)$extra, 2, '.', '') ?>" <?= $first ? 'checked' : '' ?>>
                        </span>
                    </label>
                    <?php $first = false; endforeach; ?>
                </div>
                <?php endforeach; ?>

                <div class="cm-group" data-group="addons">
                    <div class="cm-group-head">
                        <span class="cm-group-title"><?= h($drinkOptions['addons']['label']) ?></span>
                        <span class="cm-opt-optional">Optional</span>
                    </div>
                    <?php foreach ($drinkOptions['addons']['choices'] as $name => $extra): ?>
                    <label class="cm-opt">
                        <span class="cm-opt-name"><?= h($name) ?></span>
                        <span class="cm-opt-right">
                            <span class="cm-opt-price">+<?= fmtMoney((float)$extra) ?></span>
                            <input type="checkbox" class="cm-addon" value="<?= h($name) ?>"
                                   data-price="<?= number_format((float)$extra, 2, '.', '') ?>">
                        </span>
                    </label>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="modal-footer d-flex justify-content-between align-items-center">
                <div class="qty-modal">
                    <button type="button" class="qty-btn qty-minus" id="cmMinus">−</button>
                    <span class="qty-modal-val" id="cmQty">1</span>
                    <button type="button" class="qty-btn qty-plus" id="cmPlus">+</button>
                </div>
                <button type="button" class="btn-an-search border-0 px-4" id="cmAdd">Add · <span id="cmTotal">RM 0.00</span></button>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const CFG      = <?= json_encode($drinkOptions) ?>;
    const PRODUCTS = <?= json_encode((object)array_reduce($menuById, function ($a, $m) {
                        $a[(int)$m['menu_item_id']] = [
                            'name'  => $m['item_name'],
                            'price' => (float)$m['price'],
                            'stall' => $m['stall_name'],
                            'drink' => (bool)$m['is_drink'],
                        ];
                        return $a;
                    }, [])) ?>;
    let INITIAL = [];
    try { INITIAL = JSON.parse(<?= json_encode($_POST['cart_json'] ?? '[]') ?>) || []; } catch (e) { INITIAL = []; }

    const linesEl = document.getElementById('cartLines');
    const emptyEl = document.getElementById('cartEmpty');
    const totalEl = document.getElementById('cartTotal');
    const jsonEl  = document.getElementById('cartJson');
    const form    = document.getElementById('orderForm');
    if (!linesEl || !jsonEl) return;

    const cart = [];

    function money(n) { return 'RM ' + (Math.round(n * 100) / 100).toFixed(2); }
    function escapeHtml(s) {
        return String(s).replace(/[&<>"]/g, function (c) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[c];
        });
    }

    function computeOpts(o) {
        let extra = 0; const parts = [];
        ['cup', 'sugar', 'ice'].forEach(function (g) {
            const v = o[g];
            if (v && CFG[g] && CFG[g].choices[v] !== undefined) { extra += CFG[g].choices[v]; parts.push(CFG[g].label + ': ' + v); }
        });
        (o.addons || []).forEach(function (a) {
            if (CFG.addons && CFG.addons.choices[a] !== undefined) { extra += CFG.addons.choices[a]; parts.push('+' + a); }
        });
        return { extra: Math.round(extra * 100) / 100, label: parts.join(', ') };
    }

    function render() {
        linesEl.innerHTML = '';
        let total = 0;
        cart.forEach(function (line) {
            const lt = line.unit * line.qty; total += lt;
            const row = document.createElement('div');
            row.className = 'cart-line';
            row.innerHTML =
                '<div class="cart-line-main">' +
                    '<div class="cart-line-name">' + escapeHtml(line.name) + '</div>' +
                    '<div class="text-muted" style="font-size:.72rem"><i class="bi bi-shop me-1"></i>' + escapeHtml(line.stall) + '</div>' +
                    (line.label ? '<div class="opt-summary">' + escapeHtml(line.label) + '</div>' : '') +
                    '<div class="cart-line-controls">' +
                        '<button type="button" class="qty-btn cl-minus">−</button>' +
                        '<span class="cl-qty">' + line.qty + '</span>' +
                        '<button type="button" class="qty-btn qty-plus cl-plus">+</button>' +
                        '<button type="button" class="cl-remove" title="Remove">&times;</button>' +
                    '</div>' +
                '</div>' +
                '<div class="cart-line-price">' + money(lt) + '</div>';
            row.querySelector('.cl-minus').addEventListener('click', function () { line.qty = Math.max(1, line.qty - 1); render(); });
            row.querySelector('.cl-plus').addEventListener('click', function () { line.qty = Math.min(20, line.qty + 1); render(); });
            row.querySelector('.cl-remove').addEventListener('click', function () { const i = cart.indexOf(line); if (i >= 0) cart.splice(i, 1); render(); });
            linesEl.appendChild(row);
        });
        emptyEl.style.display = cart.length ? 'none' : '';
        totalEl.textContent = money(total);
        jsonEl.value = JSON.stringify(cart.map(function (l) {
            return { id: l.id, qty: l.qty, cup: l.opts.cup || '', sugar: l.opts.sugar || '', ice: l.opts.ice || '', addons: l.opts.addons || [] };
        }));
    }

    function addLine(id, name, price, opts, qty, stall) {
        const c = computeOpts(opts);
        cart.push({ id: id, name: name, stall: stall, base: price, opts: opts, label: c.label, unit: price + c.extra, qty: qty });
        render();
    }

    function addSimple(id, name, price, stall) {
        const existing = cart.find(function (l) { return l.id === id && !l.label; });
        if (existing) { existing.qty = Math.min(20, existing.qty + 1); render(); }
        else { addLine(id, name, price, {}, 1, stall); }
    }

    // Modal wiring (drinks only)
    const modalEl = document.getElementById('customiseModal');
    const bsModal = (modalEl && window.bootstrap) ? new bootstrap.Modal(modalEl) : null;
    let current = null, cmQty = 1;

    function readModal() {
        const o = {};
        ['cup', 'sugar', 'ice'].forEach(function (g) {
            const r = modalEl.querySelector('input[name="cm_' + g + '"]:checked');
            if (r) o[g] = r.value;
        });
        const addons = [];
        modalEl.querySelectorAll('.cm-addon:checked').forEach(function (cb) { addons.push(cb.value); });
        o.addons = addons;
        return o;
    }

    function updateModalTotal() {
        if (!current) return;
        const c = computeOpts(readModal());
        document.getElementById('cmTotal').textContent = money((current.price + c.extra) * cmQty);
    }

    if (modalEl) {
        modalEl.addEventListener('change', updateModalTotal);
        document.getElementById('cmMinus').addEventListener('click', function () { cmQty = Math.max(1, cmQty - 1); document.getElementById('cmQty').textContent = cmQty; updateModalTotal(); });
        document.getElementById('cmPlus').addEventListener('click', function () { cmQty = Math.min(20, cmQty + 1); document.getElementById('cmQty').textContent = cmQty; updateModalTotal(); });
        document.getElementById('cmAdd').addEventListener('click', function () {
            if (!current) return;
            addLine(current.id, current.name, current.price, readModal(), cmQty, current.stall);
            bsModal.hide();
        });
    }

    function openModal(id, name, price, stall) {
        current = { id: id, name: name, price: price, stall: stall }; cmQty = 1;
        document.getElementById('cmTitle').textContent = name;
        document.getElementById('cmBase').textContent = stall + ' · Base ' + money(price);
        document.getElementById('cmQty').textContent = '1';
        ['cup', 'sugar', 'ice'].forEach(function (g) {
            const first = modalEl.querySelector('input[name="cm_' + g + '"]');
            if (first) first.checked = true;
        });
        modalEl.querySelectorAll('.cm-addon').forEach(function (cb) { cb.checked = false; });
        updateModalTotal();
        bsModal.show();
    }

    document.querySelectorAll('.add-item-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const card = btn.closest('.menu-item-card');
            const id = parseInt(card.dataset.itemId, 10);
            const name = card.dataset.itemName;
            const price = parseFloat(card.dataset.price);
            const stall = card.dataset.stall;
            if (card.dataset.drink === '1' && bsModal) { openModal(id, name, price, stall); }
            else { addSimple(id, name, price, stall); }
        });
    });

    if (form) {
        form.addEventListener('submit', function (e) {
            if (cart.length === 0) { e.preventDefault(); alert('Please add at least one item to your order.'); }
        });
    }

    // Restore the cart if a submit failed validation
    INITIAL.forEach(function (l) {
        const id = parseInt(l.id, 10); const p = PRODUCTS[id];
        if (!p) return;
        const opts = p.drink ? { cup: l.cup || '', sugar: l.sugar || '', ice: l.ice || '', addons: l.addons || [] } : {};
        addLine(id, p.name, p.price, opts, Math.max(1, parseInt(l.qty, 10) || 1), p.stall);
    });

    render();

    // A stall picked on the home page scrolls its menu section into view
    <?php if ($focusStallId > 0): ?>
    const focusEl = document.getElementById('stall-<?= $focusStallId ?>');
    if (focusEl) { focusEl.scrollIntoView({ behavior: 'smooth', block: 'start' }); }
    <?php endif; ?>
});
</script>

<?php include '_foot.php'; ?>
