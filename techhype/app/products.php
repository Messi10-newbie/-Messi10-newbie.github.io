<?php
include '_base.php';

// Build query
$where = ['p.status = "active"'];
$params = [];

$search    = clean($_GET['search'] ?? '');
$brand     = clean($_GET['brand'] ?? '');
$category  = clean($_GET['category'] ?? '');
$sort      = clean($_GET['sort'] ?? '');
$minPrice  = $_GET['min_price'] ?? '';
$maxPrice  = $_GET['max_price'] ?? '';
$page      = max(1, intval($_GET['page'] ?? 1));
$perPage   = 12;

if ($search) {
    $where[] = '(p.name LIKE ? OR p.brand LIKE ? OR p.specs LIKE ?)';
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}
if ($brand) {
    $where[] = 'p.brand = ?';
    $params[] = $brand;
}
if ($category) {
    $where[] = 'p.category = ?';
    $params[] = $category;
}
if ($minPrice !== '') {
    $where[] = 'COALESCE(p.sale_price, p.price) >= ?';
    $params[] = floatval($minPrice);
}
if ($maxPrice !== '') {
    $where[] = 'COALESCE(p.sale_price, p.price) <= ?';
    $params[] = floatval($maxPrice);
}

$whereSQL = implode(' AND ', $where);
$orderSQL = 'p.sort_order ASC, p.created_at DESC';
if ($sort === 'low') $orderSQL = 'COALESCE(p.sale_price, p.price) ASC';
if ($sort === 'high') $orderSQL = 'COALESCE(p.sale_price, p.price) DESC';
if ($sort === 'name') $orderSQL = 'p.name ASC';

// Count total for pagination
$countStm = $db->prepare("SELECT COUNT(*) FROM products p WHERE $whereSQL");
$countStm->execute($params);
$totalProducts = $countStm->fetchColumn();
$totalPages = max(1, ceil($totalProducts / $perPage));
$offset = ($page - 1) * $perPage;

$stm = $db->prepare("SELECT p.* FROM products p WHERE $whereSQL ORDER BY $orderSQL LIMIT $perPage OFFSET $offset");
$stm->execute($params);
$products = $stm->fetchAll();

// Build query string for pagination links
function buildQuery($overrides = []) {
    $params = $_GET;
    foreach ($overrides as $k => $v) $params[$k] = $v;
    return http_build_query($params);
}

include '_head.php';
?>

<!-- Page Header -->
<section class="brand-header" style="background: #1d1d1f;">
    <div class="container">
        <h1><?= $brand ?: ($category ? ucfirst($category) . 's' : ($search ? "Search: \"$search\"" : 'All Products')) ?></h1>
        <p><?= $totalProducts ?> products found</p>
    </div>
</section>

<section class="section">
    <div class="container">
        <!-- Toolbar -->
        <div class="shop-toolbar">
            <div class="filter-tabs">
                <a href="<?= $base ?>/products.php" class="filter-btn <?= !$category ? 'active' : '' ?>">All</a>
                <a href="<?= $base ?>/products.php?category=mobile<?= $brand ? "&brand=$brand" : '' ?>" class="filter-btn <?= $category === 'mobile' ? 'active' : '' ?>">Mobiles</a>
                <a href="<?= $base ?>/products.php?category=tablet<?= $brand ? "&brand=$brand" : '' ?>" class="filter-btn <?= $category === 'tablet' ? 'active' : '' ?>">Tablets</a>
                <a href="<?= $base ?>/products.php?category=console<?= $brand ? "&brand=$brand" : '' ?>" class="filter-btn <?= $category === 'console' ? 'active' : '' ?>">Consoles</a>
                <a href="<?= $base ?>/products.php?category=audio<?= $brand ? "&brand=$brand" : '' ?>" class="filter-btn <?= $category === 'audio' ? 'active' : '' ?>">Audio</a>
            </div>
            <form method="GET" class="sort-form">
                <?php if ($brand): ?><input type="hidden" name="brand" value="<?= $brand ?>"><?php endif; ?>
                <?php if ($category): ?><input type="hidden" name="category" value="<?= $category ?>"><?php endif; ?>
                <?php if ($search): ?><input type="hidden" name="search" value="<?= $search ?>"><?php endif; ?>
                <select name="sort" class="sort-select" onchange="this.form.submit()">
                    <option value="">Sort by: Latest</option>
                    <option value="low" <?= $sort === 'low' ? 'selected' : '' ?>>Price: Low to High</option>
                    <option value="high" <?= $sort === 'high' ? 'selected' : '' ?>>Price: High to Low</option>
                    <option value="name" <?= $sort === 'name' ? 'selected' : '' ?>>Name: A-Z</option>
                </select>
            </form>
        </div>

        <!-- Price Filter -->
        <form method="GET" class="price-filter">
            <?php if ($brand): ?><input type="hidden" name="brand" value="<?= $brand ?>"><?php endif; ?>
            <?php if ($category): ?><input type="hidden" name="category" value="<?= $category ?>"><?php endif; ?>
            <?php if ($search): ?><input type="hidden" name="search" value="<?= $search ?>"><?php endif; ?>
            <?php if ($sort): ?><input type="hidden" name="sort" value="<?= $sort ?>"><?php endif; ?>
            <label><i class="fa-solid fa-filter"></i> Price Range:</label>
            <input type="number" name="min_price" placeholder="Min (RM)" value="<?= clean($minPrice) ?>" step="1" min="0">
            <span>—</span>
            <input type="number" name="max_price" placeholder="Max (RM)" value="<?= clean($maxPrice) ?>" step="1" min="0">
            <button type="submit" class="btn btn-primary btn-sm">Filter</button>
            <?php if ($minPrice !== '' || $maxPrice !== ''): ?>
                <a href="<?= $base ?>/products.php?<?= http_build_query(array_diff_key($_GET, ['min_price'=>'','max_price'=>'','page'=>''])) ?>" class="btn btn-outline btn-sm">Clear</a>
            <?php endif; ?>
        </form>

        <!-- Products Grid -->
        <?php if (empty($products)): ?>
            <div class="empty-state">
                <i class="fa-solid fa-box-open"></i>
                <h3>No products found</h3>
                <p>Try a different search or browse our categories.</p>
                <a href="<?= $base ?>/products.php" class="btn btn-primary">View All Products</a>
            </div>
        <?php else: ?>
        <div class="products-grid">
            <?php foreach ($products as $p):
                $colors = json_decode($p->colors ?? '[]', true) ?: [];
                $firstColor = $colors[0] ?? null;
                $initialStorage = $firstColor['storage'] ?? [];
            ?>
            <div class="product-card">
                <a href="<?= $base ?>/product-detail.php?id=<?= $p->id ?>">
                    <div class="product-img product-img-icon">
                        <?php if ($p->photo !== 'default-product.png'): ?>
                            <img src="<?= $base ?>/uploads/<?= $p->photo ?>" alt="<?= clean($p->name) ?>">
                        <?php else: ?>
                            <div class="product-icon"><i class="fa-solid fa-<?= $p->category === 'mobile' ? 'mobile-screen' : ($p->category === 'laptop' ? 'laptop' : ($p->category === 'tablet' ? 'tablet-screen-button' : ($p->category === 'console' ? 'gamepad' : ($p->category === 'audio' ? 'headphones' : 'mobile-screen')))) ?>"></i></div>
                        <?php endif; ?>
                        <?php if ($p->sale_price): ?>
                            <span class="product-badge sale">-<?= round((1 - $p->sale_price / $p->price) * 100) ?>%</span>
                        <?php endif; ?>
                        <button class="wishlist-btn <?= is_wishlisted($p->id) ? 'active' : '' ?>" data-id="<?= $p->id ?>" title="Add to Wishlist">
                            <i class="<?= is_wishlisted($p->id) ? 'fa-solid' : 'fa-regular' ?> fa-heart"></i>
                        </button>
                    </div>
                </a>
                <div class="product-info">
                    <span class="product-brand"><?= clean($p->brand) ?></span>
                    <h4><a href="<?= $base ?>/product-detail.php?id=<?= $p->id ?>"><?= clean($p->name) ?></a></h4>
                    <div class="product-specs"><?= clean($p->specs ?? '') ?></div>

                    <?php if (!empty($colors)): ?>
                    <div class="variant-colors">
                        <?php foreach ($colors as $i => $c): ?>
                        <span class="variant-color-dot <?= $i === 0 ? 'active' : '' ?>"
                              style="background: <?= $c['hex'] ?>;"
                              title="<?= $c['name'] ?>"
                              <?= !empty($c['image']) ? 'data-image="' . $base . '/uploads/' . $c['image'] . '"' : '' ?>
                              data-storage='<?= json_encode($c['storage'] ?? [], JSON_HEX_APOS) ?>'></span>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>

                    <?php if (!empty($initialStorage)): ?>
                    <div class="variant-storage">
                        <?php foreach ($initialStorage as $i => $s): ?>
                        <button class="variant-storage-btn <?= $i === 0 ? 'active' : '' ?>" data-price="<?= $s['price'] ?>" data-sale="<?= $s['sale'] ?? '' ?>"><?= $s['label'] ?></button>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>

                    <p class="product-price">
                        <?php if ($p->sale_price): ?>
                            <span class="old-price"><?= format_price($p->price) ?></span> <?= format_price($p->sale_price) ?>
                        <?php else: ?>
                            <?= format_price($p->price) ?>
                        <?php endif; ?>
                    </p>
                    <form method="POST" action="<?= $base ?>/cart-add.php">
                        <input type="hidden" name="id" value="<?= $p->id ?>">
                        <button type="submit" class="btn btn-primary btn-sm add-to-cart" style="width:100%">Add to Cart</button>
                    </form>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
        <div class="pagination">
            <?php if ($page > 1): ?>
                <a href="<?= $base ?>/products.php?<?= buildQuery(['page' => $page - 1]) ?>"><i class="fa-solid fa-chevron-left"></i></a>
            <?php else: ?>
                <span class="disabled"><i class="fa-solid fa-chevron-left"></i></span>
            <?php endif; ?>

            <?php
            $start = max(1, $page - 2);
            $end = min($totalPages, $page + 2);
            if ($start > 1): ?>
                <a href="<?= $base ?>/products.php?<?= buildQuery(['page' => 1]) ?>">1</a>
                <?php if ($start > 2): ?><span class="disabled">...</span><?php endif; ?>
            <?php endif; ?>

            <?php for ($i = $start; $i <= $end; $i++): ?>
                <?php if ($i == $page): ?>
                    <span class="active"><?= $i ?></span>
                <?php else: ?>
                    <a href="<?= $base ?>/products.php?<?= buildQuery(['page' => $i]) ?>"><?= $i ?></a>
                <?php endif; ?>
            <?php endfor; ?>

            <?php if ($end < $totalPages): ?>
                <?php if ($end < $totalPages - 1): ?><span class="disabled">...</span><?php endif; ?>
                <a href="<?= $base ?>/products.php?<?= buildQuery(['page' => $totalPages]) ?>"><?= $totalPages ?></a>
            <?php endif; ?>

            <?php if ($page < $totalPages): ?>
                <a href="<?= $base ?>/products.php?<?= buildQuery(['page' => $page + 1]) ?>"><i class="fa-solid fa-chevron-right"></i></a>
            <?php else: ?>
                <span class="disabled"><i class="fa-solid fa-chevron-right"></i></span>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
</section>

<?php include '_foot.php'; ?>
