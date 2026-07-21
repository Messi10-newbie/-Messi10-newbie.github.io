<?php
include '../_base.php';
require_admin();

// Duplicate product
if (isset($_GET['duplicate'])) {
    $id = intval($_GET['duplicate']);
    $stm = $db->prepare('SELECT * FROM products WHERE id = ?');
    $stm->execute([$id]);
    $p = $stm->fetch();
    if ($p) {
        // Copy the main photo file so each product has its own copy
        $newPhoto = $p->photo;
        if ($p->photo && $p->photo !== 'default-product.png') {
            $srcPath = __DIR__ . '/../uploads/' . $p->photo;
            if (file_exists($srcPath)) {
                $ext = pathinfo($p->photo, PATHINFO_EXTENSION);
                $newPhoto = uniqid() . '.' . $ext;
                copy($srcPath, __DIR__ . '/../uploads/' . $newPhoto);
            }
        }

        // Copy gallery files too
        $newGallery = $p->gallery;
        if ($p->gallery) {
            $galleryArr = json_decode($p->gallery, true);
            if (is_array($galleryArr)) {
                $newGalleryArr = [];
                foreach ($galleryArr as $gFile) {
                    $gSrc = __DIR__ . '/../uploads/' . $gFile;
                    if (file_exists($gSrc)) {
                        $gExt = pathinfo($gFile, PATHINFO_EXTENSION);
                        $gNew = uniqid() . '.' . $gExt;
                        copy($gSrc, __DIR__ . '/../uploads/' . $gNew);
                        $newGalleryArr[] = $gNew;
                    } else {
                        $newGalleryArr[] = $gFile;
                    }
                }
                $newGallery = json_encode($newGalleryArr);
            }
        }

        $stm = $db->prepare('INSERT INTO products (name, brand, category, price, sale_price, specs, description, specifications, photo, stock, status, colors, gallery) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
        $stm->execute([
            $p->name . ' (Copy)',
            $p->brand, $p->category, $p->price, $p->sale_price,
            $p->specs, $p->description, $p->specifications,
            $newPhoto, $p->stock, 'active',
            $p->colors, $newGallery,
        ]);
        $newId = $db->lastInsertId();
        flash('success', 'Product duplicated! You can now edit the copy.');
        redirect("/admin/product-form.php?id=$newId");
    }
    redirect('/admin/products.php');
}

// Delete product
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stm = $db->prepare('SELECT photo FROM products WHERE id = ?');
    $stm->execute([$id]);
    $p = $stm->fetch();
    if ($p) {
        // Delete related order items first
        $db->prepare('DELETE FROM order_items WHERE product_id = ?')->execute([$id]);
        delete_photo($p->photo);
        $db->prepare('DELETE FROM products WHERE id = ?')->execute([$id]);
        flash('success', 'Product deleted.');
    }
    redirect('/admin/products.php');
}

$search = clean($_GET['search'] ?? '');
$brandFilter = clean($_GET['brand'] ?? '');
$where = '1=1';
$params = [];

if ($search) {
    $where .= ' AND (name LIKE ? OR brand LIKE ? OR specs LIKE ?)';
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}
if ($brandFilter) {
    $where .= ' AND brand = ?';
    $params[] = $brandFilter;
}

// Add sort_order column if not exists
try { $db->exec('ALTER TABLE products ADD COLUMN sort_order INT DEFAULT 0'); } catch (Exception $e) {}

// Get all brands for filter tabs
$allBrands = $db->query('SELECT DISTINCT brand FROM products ORDER BY brand ASC')->fetchAll(PDO::FETCH_COLUMN);

$stm = $db->prepare("SELECT * FROM products WHERE $where ORDER BY sort_order ASC, created_at DESC");
$stm->execute($params);
$products = $stm->fetchAll();

include '../_head.php';
?>

<section class="brand-header" style="background: #1d1d1f;">
    <div class="container">
        <h1><i class="fa-solid fa-box"></i> Product Management</h1>
        <p><?= count($products) ?> product(s)</p>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="admin-nav">
            <a href="<?= $base ?>/admin/members.php">Members</a>
            <a href="<?= $base ?>/admin/products.php" class="active">Products</a>
            <a href="<?= $base ?>/admin/orders.php">Orders</a>
            <a href="<?= $base ?>/admin/brand-videos.php">Brand Videos</a>
            <a href="<?= $base ?>/admin/analytics.php">Analytics</a>
        </div>

        <div class="admin-toolbar">
            <form method="GET" class="admin-search">
                <input type="text" name="search" placeholder="Search products..." value="<?= $search ?>">
                <?php if ($brandFilter): ?><input type="hidden" name="brand" value="<?= $brandFilter ?>"><?php endif; ?>
                <button type="submit" class="btn btn-primary">Search</button>
                <?php if ($search): ?><a href="<?= $base ?>/admin/products.php<?= $brandFilter ? '?brand=' . urlencode($brandFilter) : '' ?>" class="btn btn-outline">Clear</a><?php endif; ?>
            </form>
            <a href="<?= $base ?>/admin/product-form.php" class="btn btn-primary"><i class="fa-solid fa-plus"></i> Add Product</a>
        </div>

        <!-- Brand Filter -->
        <div class="filter-tabs" style="justify-content:flex-start;margin-bottom:20px;">
            <a href="<?= $base ?>/admin/products.php<?= $search ? '?search=' . urlencode($search) : '' ?>" class="filter-btn <?= !$brandFilter ? 'active' : '' ?>">All</a>
            <?php foreach ($allBrands as $b): ?>
            <a href="<?= $base ?>/admin/products.php?brand=<?= urlencode($b) ?><?= $search ? '&search=' . urlencode($search) : '' ?>" class="filter-btn <?= $brandFilter === $b ? 'active' : '' ?>"><?= clean($b) ?></a>
            <?php endforeach; ?>
        </div>

        <div style="margin-bottom:16px;display:flex;align-items:center;gap:12px;">
            <span style="font-size:13px;color:#86868b;"><i class="fa-solid fa-grip-vertical"></i> Drag rows to reorder products</span>
            <button id="saveOrderBtn" class="btn btn-primary btn-sm" style="display:none;" onclick="saveOrder()"><i class="fa-solid fa-check"></i> Save Order</button>
            <span id="orderStatus" style="font-size:13px;font-weight:500;"></span>
        </div>

        <table class="data-table" id="productsTable">
            <thead>
                <tr>
                    <th style="width:40px;">#</th>
                    <th>Photo</th>
                    <th>Name</th>
                    <th>Brand</th>
                    <th>Category</th>
                    <th>Price</th>
                    <th>Stock</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="sortableBody">
                <?php foreach ($products as $p): ?>
                <tr data-id="<?= $p->id ?>" class="draggable-row">
                    <td class="drag-handle" style="cursor:grab;text-align:center;color:#86868b;"><i class="fa-solid fa-grip-vertical"></i></td>
                    <td>
                        <?php if ($p->photo !== 'default-product.png'): ?>
                            <img src="<?= $base ?>/uploads/<?= $p->photo ?>" class="table-photo" alt="">
                        <?php else: ?>
                            <div class="table-photo-icon"><i class="fa-solid fa-mobile-screen"></i></div>
                        <?php endif; ?>
                    </td>
                    <td><strong><?= clean($p->name) ?></strong></td>
                    <td><?= clean($p->brand) ?></td>
                    <td><?= ucfirst($p->category) ?></td>
                    <td>
                        <?php if ($p->sale_price): ?>
                            <span class="old-price"><?= format_price($p->price) ?></span><br>
                            <?= format_price($p->sale_price) ?>
                        <?php else: ?>
                            <?= format_price($p->price) ?>
                        <?php endif; ?>
                    </td>
                    <td><?= $p->stock ?></td>
                    <td><span class="status-badge status-<?= $p->status ?>"><?= ucfirst($p->status) ?></span></td>
                    <td>
                        <a href="<?= $base ?>/admin/product-form.php?id=<?= $p->id ?>" class="btn btn-sm btn-outline">Edit</a>
                        <a href="<?= $base ?>/admin/products.php?duplicate=<?= $p->id ?>" class="btn btn-sm btn-outline" style="color:#6c5ce7;border-color:#6c5ce7;" onclick="return confirm('Duplicate this product?')">Duplicate</a>
                        <a href="<?= $base ?>/admin/products.php?delete=<?= $p->id ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this product?')">Delete</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

<script>
// Drag and drop reorder
const tbody = document.getElementById('sortableBody');
let dragRow = null;
let orderChanged = false;

tbody.addEventListener('dragstart', function(e) {
    dragRow = e.target.closest('tr');
    if (!dragRow) return;
    dragRow.style.opacity = '0.4';
    e.dataTransfer.effectAllowed = 'move';
});

tbody.addEventListener('dragend', function(e) {
    if (dragRow) dragRow.style.opacity = '1';
    document.querySelectorAll('.draggable-row').forEach(r => r.classList.remove('drag-over'));
    dragRow = null;
});

tbody.addEventListener('dragover', function(e) {
    e.preventDefault();
    e.dataTransfer.dropEffect = 'move';
    const target = e.target.closest('tr');
    if (!target || target === dragRow) return;

    document.querySelectorAll('.draggable-row').forEach(r => r.classList.remove('drag-over'));
    target.classList.add('drag-over');

    const rect = target.getBoundingClientRect();
    const midY = rect.top + rect.height / 2;
    if (e.clientY < midY) {
        tbody.insertBefore(dragRow, target);
    } else {
        tbody.insertBefore(dragRow, target.nextSibling);
    }
});

tbody.addEventListener('drop', function(e) {
    e.preventDefault();
    orderChanged = true;
    document.getElementById('saveOrderBtn').style.display = 'inline-flex';
    document.getElementById('orderStatus').textContent = 'Order changed — click Save Order';
    document.getElementById('orderStatus').style.color = '#e67e22';
});

// Make rows draggable
document.querySelectorAll('.draggable-row').forEach(row => {
    row.setAttribute('draggable', 'true');
});

function saveOrder() {
    const rows = document.querySelectorAll('#sortableBody tr');
    const order = [];
    rows.forEach(r => order.push(r.dataset.id));

    const btn = document.getElementById('saveOrderBtn');
    const status = document.getElementById('orderStatus');
    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Saving...';

    fetch('<?= $base ?>/admin/reorder-products.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ order: order })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            status.textContent = 'Order saved!';
            status.style.color = '#27ae60';
            btn.style.display = 'none';
            orderChanged = false;
        } else {
            status.textContent = 'Error saving order';
            status.style.color = '#e74c3c';
        }
        btn.disabled = false;
        btn.innerHTML = '<i class="fa-solid fa-check"></i> Save Order';
    })
    .catch(() => {
        status.textContent = 'Error saving order';
        status.style.color = '#e74c3c';
        btn.disabled = false;
        btn.innerHTML = '<i class="fa-solid fa-check"></i> Save Order';
    });
}
</script>
    </div>
</section>

<?php include '../_foot.php'; ?>
