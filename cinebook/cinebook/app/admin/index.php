<?php
session_start();
require '../_base.php';
require '../_functions.php';

$rootPath = '../';
$cssPath = '../';

ensureMenuItemImages($pdo);

// ── Add a menu item to a stall ─────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'add_menu_item') {
    $stallId   = (int)($_POST['stall_id'] ?? 0);
    $name      = trim($_POST['item_name'] ?? '');
    $desc      = trim($_POST['item_desc'] ?? '');
    $price     = (float)($_POST['price'] ?? 0);
    $isPopular = isset($_POST['is_popular']) ? 1 : 0;

    $stallOk = false;
    if ($stallId > 0) {
        $chk = $pdo->prepare("SELECT 1 FROM stalls WHERE stall_id = ? AND is_active = 1");
        $chk->execute([$stallId]);
        $stallOk = (bool)$chk->fetchColumn();
    }

    if ($stallOk && $name !== '' && $desc !== '' && $price > 0) {
        $sortStmt = $pdo->prepare("SELECT COALESCE(MAX(sort_order), 0) + 1 FROM menu_items WHERE stall_id = ?");
        $sortStmt->execute([$stallId]);
        $sortOrder = (int)$sortStmt->fetchColumn();

        $ins = $pdo->prepare("
            INSERT INTO menu_items (stall_id, item_name, item_desc, price, sort_order, is_popular, is_available)
            VALUES (?, ?, ?, ?, ?, ?, 1)
        ");
        $ins->execute([$stallId, $name, $desc, $price, $sortOrder, $isPopular]);
        $newItemId = (int)$pdo->lastInsertId();
        $_SESSION['admin_flash'] = 'Added "' . $name . '" to the menu.';

        // Optional photo dropped into the form
        $imgErr = null;
        $imgPath = saveMenuItemImage($_FILES['item_image'] ?? [], $newItemId, $imgErr);
        if ($imgPath) {
            $upd = $pdo->prepare("UPDATE menu_items SET image_path = ? WHERE menu_item_id = ?");
            $upd->execute([$imgPath, $newItemId]);
            $_SESSION['admin_flash'] = 'Added "' . $name . '" to the menu with its photo.';
        } elseif ($imgErr) {
            $_SESSION['admin_flash_error'] = 'Item added, but the photo was not saved: ' . $imgErr;
        }
    } else {
        $_SESSION['admin_flash_error'] = 'Please choose a stall and enter an item name, description, and a price above 0.';
    }
    redirect('index.php');
}

// ── Attach / replace a photo on an existing menu item ─────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'set_item_image') {
    $itemId = (int)($_POST['menu_item_id'] ?? 0);
    $row = null;
    if ($itemId > 0) {
        $chk = $pdo->prepare("SELECT menu_item_id, item_name, image_path FROM menu_items WHERE menu_item_id = ?");
        $chk->execute([$itemId]);
        $row = $chk->fetch() ?: null;
    }

    $imgErr = null;
    $imgPath = $row ? saveMenuItemImage($_FILES['item_image'] ?? [], $itemId, $imgErr) : null;
    if ($row && $imgPath) {
        deleteMenuItemImage($row['image_path'] ?? null);
        $upd = $pdo->prepare("UPDATE menu_items SET image_path = ? WHERE menu_item_id = ?");
        $upd->execute([$imgPath, $itemId]);
        $_SESSION['admin_flash'] = 'Photo saved for "' . $row['item_name'] . '".';
    } else {
        $_SESSION['admin_flash_error'] = $imgErr ?: 'Please choose a menu item and drop a JPG, JPEG, or PNG photo.';
    }
    redirect('index.php');
}

// ── Add a new stall ────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'add_stall') {
    $name    = trim($_POST['stall_name'] ?? '');
    $cuisine = trim($_POST['cuisine'] ?? '');
    $desc    = trim($_POST['description'] ?? '');
    $price   = (float)($_POST['starting_price'] ?? 0);

    $dupe = false;
    if ($name !== '') {
        $chk = $pdo->prepare("SELECT 1 FROM stalls WHERE stall_name = ? AND is_active = 1");
        $chk->execute([$name]);
        $dupe = (bool)$chk->fetchColumn();
    }

    if ($dupe) {
        $_SESSION['admin_flash_error'] = 'A stall named "' . $name . '" already exists.';
    } elseif ($name !== '' && $cuisine !== '' && $desc !== '' && $price > 0) {
        $sortOrder = (int)$pdo->query("SELECT COALESCE(MAX(sort_order), 0) + 1 FROM stalls")->fetchColumn();
        $ins = $pdo->prepare("
            INSERT INTO stalls (stall_name, cuisine, description, starting_price, sort_order, is_open, is_active)
            VALUES (?, ?, ?, ?, ?, 1, 1)
        ");
        $ins->execute([$name, $cuisine, $desc, $price, $sortOrder]);
        $_SESSION['admin_flash'] = 'Stall "' . $name . '" added. Now add its menu items below.';
    } else {
        $_SESSION['admin_flash_error'] = 'Please fill in the stall name, cuisine, description, and a starting price above 0.';
    }
    redirect('index.php');
}

$flashOk  = $_SESSION['admin_flash'] ?? '';
$flashErr = $_SESSION['admin_flash_error'] ?? '';
unset($_SESSION['admin_flash'], $_SESSION['admin_flash_error']);

$allStalls   = $pdo->query("SELECT * FROM stalls WHERE is_active = 1 ORDER BY sort_order, stall_name")->fetchAll();
$totalOrders = (int)$pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$totalRevenue = (float)($pdo->query("SELECT COALESCE(SUM(total_price), 0) FROM orders WHERE order_status <> 'cancelled'")->fetchColumn() ?: 0);
$totalSlots = (int)$pdo->query("SELECT COUNT(*) FROM pickup_slots WHERE is_active = 1")->fetchColumn();
$totalStalls = (int)$pdo->query("SELECT COUNT(*) FROM stalls WHERE is_active = 1")->fetchColumn();

// Multi-stall orders have stall_id NULL — list the stalls from their items instead
$recentOrders = $pdo->query("
    SELECT o.*, ps.slot_date, ps.slot_time,
           COALESCE(s.stall_name, (
               SELECT GROUP_CONCAT(DISTINCT s2.stall_name ORDER BY s2.stall_name SEPARATOR ', ')
               FROM order_items oi
               JOIN menu_items m2 ON m2.menu_item_id = oi.menu_item_id
               JOIN stalls s2 ON s2.stall_id = m2.stall_id
               WHERE oi.order_id = o.order_id
           )) AS stall_name
    FROM orders o
    LEFT JOIN stalls s ON s.stall_id = o.stall_id
    JOIN pickup_slots ps ON ps.slot_id = o.slot_id
    ORDER BY o.order_date DESC
    LIMIT 20
")->fetchAll();

$allMenuItems = $pdo->query("
    SELECT m.menu_item_id, m.item_name, m.image_path, s.stall_name
    FROM menu_items m
    JOIN stalls s ON s.stall_id = m.stall_id
    WHERE s.is_active = 1
    ORDER BY s.sort_order, m.sort_order, m.item_name
")->fetchAll();

$popularItems = $pdo->query("
    SELECT m.item_name, s.stall_name, COUNT(*) AS qty
    FROM order_items oi
    JOIN menu_items m ON m.menu_item_id = oi.menu_item_id
    JOIN stalls s ON s.stall_id = m.stall_id
    GROUP BY m.item_name, s.stall_name
    ORDER BY qty DESC, m.item_name
    LIMIT 8
")->fetchAll();

$pageTitle = 'Admin Dashboard';
?>
<?php include '../_head.php'; ?>

<style>
.img-drop {
    border: 2px dashed #b8c4dd;
    border-radius: 12px;
    background: #f8faff;
    padding: 18px;
    text-align: center;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 14px;
    min-height: 110px;
    transition: border-color .15s, background .15s;
}
.img-drop:hover { border-color: var(--an-primary, #1d4666); }
.img-drop.drag-over { border-color: var(--an-primary, #1d4666); background: #eef4ff; }
.img-drop .img-drop-preview {
    width: 96px; height: 96px; object-fit: cover;
    border-radius: 10px; flex: 0 0 auto;
}
</style>

<div class="container page-section">
    <?php if ($flashOk): ?>
    <div class="alert alert-success border-0 shadow-sm"><i class="bi bi-check-circle me-2"></i><?= h($flashOk) ?></div>
    <?php endif; ?>
    <?php if ($flashErr): ?>
    <div class="alert alert-danger border-0 shadow-sm"><i class="bi bi-exclamation-triangle me-2"></i><?= h($flashErr) ?></div>
    <?php endif; ?>

    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="menu-item-card">
                <div class="an-label mb-1">Orders</div>
                <div class="fs-3 fw-bold text-primary"><?= number_format($totalOrders) ?></div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="menu-item-card">
                <div class="an-label mb-1">Revenue</div>
                <div class="fs-3 fw-bold text-success">RM <?= number_format($totalRevenue, 2) ?></div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="menu-item-card">
                <div class="an-label mb-1">Pickup Slots</div>
                <div class="fs-3 fw-bold" style="color:#0f766e"><?= number_format($totalSlots) ?></div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="menu-item-card">
                <div class="an-label mb-1">Active Stalls</div>
                <div class="fs-3 fw-bold" style="color:#7c3aed"><?= number_format($totalStalls) ?></div>
            </div>
        </div>
    </div>

    <!-- ── Stall Open/Closed Manager ─────────────────────────────────────── -->
    <div class="card border-0 shadow-sm mb-4" style="border-radius:var(--radius)">
        <div class="card-body p-4">
            <h5 class="fw-bold mb-3"><i class="bi bi-shop me-2"></i>Stall Status</h5>
            <div class="row g-2">
                <?php foreach ($allStalls as $stall): ?>
                <div class="col-6 col-md-4 col-lg-3">
                    <div class="d-flex align-items-center justify-content-between border rounded p-2 gap-2" style="border-radius:var(--radius-sm)!important">
                        <div>
                            <div class="fw-semibold small"><?= h($stall['stall_name']) ?></div>
                            <div class="text-muted" style="font-size:.72rem"><?= h($stall['cuisine']) ?></div>
                        </div>
                        <button class="toggle-stall-btn btn btn-sm <?= $stall['is_open'] ? 'btn-success' : 'btn-danger' ?>"
                                data-stall-id="<?= (int)$stall['stall_id'] ?>"
                                title="<?= $stall['is_open'] ? 'Click to close' : 'Click to open' ?>">
                            <?= $stall['is_open'] ? 'Open' : 'Closed' ?>
                        </button>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- ── Add Stall ─────────────────────────────────────────────────────── -->
    <div class="card border-0 shadow-sm mb-4" style="border-radius:var(--radius)">
        <div class="card-body p-4">
            <h5 class="fw-bold mb-3"><i class="bi bi-shop-window me-2"></i>Add Stall</h5>
            <form method="POST" action="index.php">
                <input type="hidden" name="action" value="add_stall">
                <div class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="an-label">Stall Name</label>
                        <input type="text" name="stall_name" class="an-input" maxlength="120" placeholder="e.g. Uncle Lim's Kitchen" required>
                    </div>
                    <div class="col-md-2">
                        <label class="an-label">Cuisine</label>
                        <select name="cuisine" class="an-input" required>
                            <option value="">Select cuisine</option>
                            <?php foreach (['Malay','Chinese','Indian','Western','Japanese','Korean','Thai','Rice','Noodles','Seafood','Vegetarian','Dessert','Snacks','Beverages'] as $c): ?>
                            <option value="<?= $c ?>"><?= cuisineEmoji($c) ?> <?= $c ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="an-label">Description</label>
                        <input type="text" name="description" class="an-input" maxlength="255" placeholder="Short tagline shown to customers" required>
                    </div>
                    <div class="col-md-2">
                        <label class="an-label">Starting Price (RM)</label>
                        <input type="number" name="starting_price" class="an-input" min="0.01" step="0.01" placeholder="0.00" required>
                    </div>
                    <div class="col-md-1">
                        <button type="submit" class="btn-an-primary w-100"><i class="bi bi-plus-lg"></i></button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- ── Add Menu Item ─────────────────────────────────────────────────── -->
    <div class="card border-0 shadow-sm mb-4" style="border-radius:var(--radius)">
        <div class="card-body p-4">
            <h5 class="fw-bold mb-3"><i class="bi bi-plus-circle me-2"></i>Add Menu Item</h5>
            <form method="POST" action="index.php" enctype="multipart/form-data">
                <input type="hidden" name="action" value="add_menu_item">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="an-label">Stall</label>
                        <select name="stall_id" class="an-input" required>
                            <option value="">Select a stall</option>
                            <?php foreach ($allStalls as $stall): ?>
                            <option value="<?= (int)$stall['stall_id'] ?>"><?= h($stall['stall_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="an-label">Item Name</label>
                        <input type="text" name="item_name" class="an-input" maxlength="120" placeholder="e.g. Chicken Rice" required>
                    </div>
                    <div class="col-md-4">
                        <label class="an-label">Description</label>
                        <input type="text" name="item_desc" class="an-input" maxlength="255" placeholder="Short description" required>
                    </div>
                    <div class="col-md-2">
                        <label class="an-label">Price (RM)</label>
                        <input type="number" name="price" class="an-input" min="0.01" step="0.01" placeholder="0.00" required>
                    </div>
                    <div class="col-md-8">
                        <label class="an-label">Photo (optional)</label>
                        <div class="img-drop">
                            <input type="file" name="item_image" accept=".jpg,.jpeg,.png,image/jpeg,image/png" hidden>
                            <img class="img-drop-preview" alt="" style="display:none">
                            <div class="img-drop-inner">
                                <i class="bi bi-cloud-arrow-up fs-3 d-block"></i>
                                <div class="small fw-semibold">Drag &amp; drop a photo here, or click to browse</div>
                                <div class="small text-muted">JPG, JPEG or PNG · max 3 MB</div>
                                <div class="small fw-semibold text-primary img-drop-name"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 d-flex flex-column justify-content-end">
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" name="is_popular" id="isPopular" value="1">
                            <label class="form-check-label small" for="isPopular">Mark as Popular</label>
                        </div>
                        <button type="submit" class="btn-an-primary w-100"><i class="bi bi-plus-lg me-1"></i>Add Item</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- ── Menu Item Photos ──────────────────────────────────────────────── -->
    <div class="card border-0 shadow-sm mb-4" style="border-radius:var(--radius)">
        <div class="card-body p-4">
            <h5 class="fw-bold mb-1"><i class="bi bi-image me-2"></i>Menu Item Photos</h5>
            <p class="text-muted small mb-3">Add or replace the photo shown on the menu for any food or drink item.</p>
            <form method="POST" action="index.php" enctype="multipart/form-data">
                <input type="hidden" name="action" value="set_item_image">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="an-label">Menu Item</label>
                        <select name="menu_item_id" id="photoItemSelect" class="an-input" required>
                            <option value="">Select an item</option>
                            <?php foreach ($allMenuItems as $mi): ?>
                            <option value="<?= (int)$mi['menu_item_id'] ?>" data-image="<?= h($mi['image_path'] ?? '') ?>">
                                <?= h($mi['stall_name']) ?> — <?= h($mi['item_name']) ?><?= !empty($mi['image_path']) ? ' 📷' : '' ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                        <div id="currentPhotoWrap" class="mt-3" style="display:none">
                            <div class="an-label mb-1">Current photo (will be replaced)</div>
                            <img id="currentPhoto" src="" alt="Current photo"
                                 style="width:100%;max-width:220px;height:120px;object-fit:cover;border-radius:10px;border:1px solid #dde3ef">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="an-label">New Photo</label>
                        <div class="img-drop">
                            <input type="file" name="item_image" accept=".jpg,.jpeg,.png,image/jpeg,image/png" hidden required>
                            <img class="img-drop-preview" alt="" style="display:none">
                            <div class="img-drop-inner">
                                <i class="bi bi-cloud-arrow-up fs-3 d-block"></i>
                                <div class="small fw-semibold">Drag &amp; drop a photo here, or click to browse</div>
                                <div class="small text-muted">JPG, JPEG or PNG · max 3 MB</div>
                                <div class="small fw-semibold text-primary img-drop-name"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn-an-primary w-100"><i class="bi bi-upload me-1"></i>Save Photo</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm" style="border-radius:var(--radius)">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-3">Recent Orders</h5>
                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead>
                                <tr>
                                    <th>Ref</th>
                                    <th>Stall</th>
                                    <th>Customer</th>
                                    <th>Pickup</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentOrders as $order): ?>
                                <tr>
                                    <td class="fw-semibold text-primary">#<?= h($order['order_reference']) ?></td>
                                    <td><?= h($order['stall_name']) ?></td>
                                    <td>
                                        <div class="fw-semibold"><?= h($order['customer_name']) ?></div>
                                        <div class="small text-muted"><?= h($order['customer_phone']) ?></div>
                                    </td>
                                    <td><?= fmtDate($order['slot_date']) ?><br><span class="small text-muted"><?= h($order['slot_time']) ?></span></td>
                                    <td class="fw-semibold"><?= fmtMoney((float)$order['total_price']) ?></td>
                                    <td>
                                        <select class="status-select form-select form-select-sm"
                                                data-order-id="<?= (int)$order['order_id'] ?>"
                                                style="min-width:140px">
                                            <?php foreach (['pending','preparing','delayed','ready','collected','cancelled'] as $s): ?>
                                            <option value="<?= $s ?>" <?= $order['order_status'] === $s ? 'selected' : '' ?>><?= ucfirst($s === 'delayed' ? 'Running Late' : $s) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php if (empty($recentOrders)): ?>
                                <tr><td colspan="6" class="text-center text-muted py-4">No orders yet.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-4" style="border-radius:var(--radius)">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-3">Top Ordered Items</h5>
                    <?php foreach ($popularItems as $item): ?>
                    <div class="price-row">
                        <span><?= h($item['item_name']) ?></span>
                        <span class="text-muted small"><?= h($item['stall_name']) ?> · <?= (int)$item['qty'] ?></span>
                    </div>
                    <?php endforeach; ?>
                    <?php if (empty($popularItems)): ?>
                    <p class="text-muted small mb-0">No sales data yet.</p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card border-0 shadow-sm" style="border-radius:var(--radius)">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-3">Manage Setup</h5>
                    <p class="text-muted small mb-3">Add menu items above. Use phpMyAdmin for deeper edits to stalls and pickup slots.</p>
                    <a href="../index.php" class="btn btn-outline-secondary w-100 mb-2">Public Site</a>
                    <a href="../../database.sql" class="btn-an-search text-center text-decoration-none d-block">View Schema File</a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.querySelectorAll('.toggle-stall-btn').forEach(function(btn) {
    btn.addEventListener('click', function() {
        var stallId = this.dataset.stallId;
        var fd = new FormData();
        fd.append('stall_id', stallId);
        fetch('ajax/toggle_stall.php', { method: 'POST', body: fd })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (data.ok) {
                    btn.textContent = data.is_open ? 'Open' : 'Closed';
                    btn.className = 'toggle-stall-btn btn btn-sm ' + (data.is_open ? 'btn-success' : 'btn-danger');
                    btn.title = data.is_open ? 'Click to close' : 'Click to open';
                }
            });
    });
});

// ── Drag & drop photo zones ────────────────────────────────────────────────
document.querySelectorAll('.img-drop').forEach(function(zone) {
    var input   = zone.querySelector('input[type="file"]');
    var preview = zone.querySelector('.img-drop-preview');
    var nameEl  = zone.querySelector('.img-drop-name');
    if (!input) return;

    function showFile(files) {
        if (!files || !files.length) return;
        var f = files[0];
        if (!/\.(jpe?g|png)$/i.test(f.name)) {
            alert('Please choose a JPG, JPEG, or PNG image.');
            input.value = '';
            return;
        }
        preview.src = URL.createObjectURL(f);
        preview.style.display = '';
        nameEl.textContent = f.name;
    }

    zone.addEventListener('click', function() { input.click(); });
    input.addEventListener('change', function() { showFile(input.files); });

    ['dragenter', 'dragover'].forEach(function(ev) {
        zone.addEventListener(ev, function(e) { e.preventDefault(); zone.classList.add('drag-over'); });
    });
    ['dragleave', 'drop'].forEach(function(ev) {
        zone.addEventListener(ev, function(e) { e.preventDefault(); zone.classList.remove('drag-over'); });
    });
    zone.addEventListener('drop', function(e) {
        if (e.dataTransfer && e.dataTransfer.files && e.dataTransfer.files.length) {
            input.files = e.dataTransfer.files;
            showFile(input.files);
        }
    });
});

// Show the current photo of the item picked in the photo manager
var photoSelect = document.getElementById('photoItemSelect');
if (photoSelect) {
    photoSelect.addEventListener('change', function() {
        var opt  = this.options[this.selectedIndex];
        var img  = (opt && opt.dataset.image) || '';
        var wrap = document.getElementById('currentPhotoWrap');
        if (img) {
            document.getElementById('currentPhoto').src = '../' + img;
            wrap.style.display = '';
        } else {
            wrap.style.display = 'none';
        }
    });
}

document.querySelectorAll('.status-select').forEach(function(sel) {
    sel.addEventListener('change', function() {
        var orderId = this.dataset.orderId;
        var status  = this.value;
        var fd = new FormData();
        fd.append('order_id', orderId);
        fd.append('status', status);
        fetch('ajax/update_status.php', { method: 'POST', body: fd })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (!data.ok) alert('Could not update status.');
            });
    });
});
</script>
<?php include '../_foot.php'; ?>
