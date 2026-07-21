<?php
include '../_base.php';

$brandName = clean($_GET['name'] ?? '');
if (!$brandName) { redirect('/'); }

// Brand colors and descriptions
$brandInfo = [
    'Samsung'  => ['color' => '#1428a0', 'desc' => 'Explore the latest Samsung Galaxy smartphones, tablets, and wearables.', 'video' => 'samsung.mp4'],
    'Apple'    => ['color' => '#1d1d1f', 'desc' => 'Discover iPhone, iPad, MacBook, and more from Apple.', 'video' => 'apple.mp4'],
    'Vivo'     => ['color' => '#415fff', 'desc' => 'Discover Vivo smartphones with stunning cameras and sleek designs.', 'video' => 'vivo.mp4'],
    'Oppo'     => ['color' => '#1a8a3f', 'desc' => 'Explore OPPO smartphones with advanced camera technology.', 'video' => 'oppo.mp4'],
    'Xiaomi'   => ['color' => '#ff6700', 'desc' => 'Shop Xiaomi smartphones, tablets, and smart home devices.', 'video' => 'xiaomi.mp4'],
    'Nothing'  => ['color' => '#1d1d1f', 'desc' => 'Experience Nothing phones with unique transparent design.', 'video' => 'nothing.mp4'],
    'Google'   => ['color' => '#4285f4', 'desc' => 'Google Pixel phones with the best of Google built in.', 'video' => 'google.mp4'],
    'Sony'     => ['color' => '#000000', 'desc' => 'Sony Xperia phones, PlayStation, and audio products.', 'video' => 'sony.mp4'],
'iQOO'     => ['color' => '#ff4400', 'desc' => 'iQOO smartphones built for speed and performance.', 'video' => 'iqoo.mp4'],
];

$info = $brandInfo[$brandName] ?? ['color' => '#1d1d1f', 'desc' => "Explore products from $brandName."];

// Check if video file exists
$brand_video = $info['video'] ?? '';
$videoPath = __DIR__ . '/../videos/' . $brand_video;
$hasVideo = $brand_video && file_exists($videoPath);
$brand = $brandName;
$brand_color = $info['color'];
$brand_desc = $info['desc'];

// Fetch products from database
$sort = clean($_GET['sort'] ?? '');
$category = clean($_GET['category'] ?? '');

$where = ['p.status = "active"', 'p.brand = ?'];
$params = [$brandName];

if ($category) {
    $where[] = 'p.category = ?';
    $params[] = $category;
}

$whereSQL = implode(' AND ', $where);
$orderSQL = 'p.created_at DESC';
if ($sort === 'low') $orderSQL = 'COALESCE(p.sale_price, p.price) ASC';
if ($sort === 'high') $orderSQL = 'COALESCE(p.sale_price, p.price) DESC';

$stm = $db->prepare("SELECT p.* FROM products p WHERE $whereSQL ORDER BY $orderSQL");
$stm->execute($params);
$dbProducts = $stm->fetchAll();

// Get unique categories for filter tabs
$catStm = $db->prepare("SELECT DISTINCT category FROM products WHERE brand = ? AND status = 'active'");
$catStm->execute([$brandName]);
$dbCategories = $catStm->fetchAll(PDO::FETCH_COLUMN);

include '../_head.php';
?>

<!-- Brand Header -->
<?php if ($hasVideo): ?>
<section class="brand-header brand-video-header">
    <video class="brand-video" autoplay muted loop playsinline>
        <source src="<?= $base ?>/videos/<?= $brand_video ?>" type="video/mp4">
    </video>
    <div class="brand-video-overlay" style="background: linear-gradient(to bottom, <?= $brand_color ?>88, <?= $brand_color ?>dd);"></div>
    <div class="container brand-video-content">
        <h1><?= $brand ?></h1>
        <p><?= $brand_desc ?></p>
        <button class="video-mute-btn" onclick="toggleMute(this)">
            <i class="fa-solid fa-volume-xmark"></i>
        </button>
    </div>
</section>
<?php else: ?>
<section class="brand-header" style="background: <?= $brand_color ?>;">
    <div class="container">
        <h1><?= $brand ?></h1>
        <p><?= $brand_desc ?></p>
    </div>
</section>
<?php endif; ?>

<!-- Filter Tabs & Products -->
<section class="section">
    <div class="container">
        <div class="filter-tabs">
            <a href="?name=<?= urlencode($brandName) ?>" class="filter-btn <?= !$category ? 'active' : '' ?>">All</a>
            <?php foreach ($dbCategories as $cat): ?>
                <a href="?name=<?= urlencode($brandName) ?>&category=<?= $cat ?>" class="filter-btn <?= $category === $cat ? 'active' : '' ?>"><?= ucfirst($cat) ?>s</a>
            <?php endforeach; ?>
        </div>

        <div class="shop-toolbar">
            <p>Showing <strong><?= count($dbProducts) ?></strong> products</p>
            <form method="GET" class="sort-form">
                <input type="hidden" name="name" value="<?= $brandName ?>">
                <?php if ($category): ?><input type="hidden" name="category" value="<?= $category ?>"><?php endif; ?>
                <select name="sort" class="sort-select" onchange="this.form.submit()">
                    <option value="">Sort by: Latest</option>
                    <option value="low" <?= $sort === 'low' ? 'selected' : '' ?>>Price: Low to High</option>
                    <option value="high" <?= $sort === 'high' ? 'selected' : '' ?>>Price: High to Low</option>
                </select>
            </form>
        </div>

        <?php if (empty($dbProducts)): ?>
            <div class="empty-state">
                <i class="fa-solid fa-box-open"></i>
                <h3>No <?= $brand ?> products yet</h3>
                <p>Products will appear here once added via the admin panel.</p>
                <a href="<?= $base ?>/" class="btn btn-primary">Back to Home</a>
            </div>
        <?php else: ?>
        <div class="products-grid" id="productsGrid">
            <?php foreach ($dbProducts as $p):
                $colors = json_decode($p->colors ?? '[]', true) ?: [];
                $gallery = json_decode($p->gallery ?? '[]', true) ?: [];
                $firstColor = $colors[0] ?? null;
                $initialStorage = $firstColor['storage'] ?? [];
            ?>
            <div class="product-card" data-category="<?= $p->category ?>" data-price="<?= intval($p->sale_price ?: $p->price) ?>">
                <a href="<?= $base ?>/product-detail.php?id=<?= $p->id ?>">
                    <div class="product-img product-img-icon">
                        <?php if ($p->photo !== 'default-product.png'): ?>
                            <img src="<?= $base ?>/uploads/<?= $p->photo ?>" alt="<?= clean($p->name) ?>">
                        <?php else: ?>
                            <div class="product-icon"><i class="fa-solid fa-mobile-screen"></i></div>
                        <?php endif; ?>
                        <div class="product-actions">
                            <button class="action-btn"><i class="fa-solid fa-heart"></i></button>
                            <button class="action-btn"><i class="fa-solid fa-cart-shopping"></i></button>
                            <button class="action-btn quick-view-btn"><i class="fa-solid fa-eye"></i></button>
                        </div>
                        <?php if ($p->sale_price): ?>
                            <span class="product-badge sale">-<?= round((1 - $p->sale_price / $p->price) * 100) ?>%</span>
                        <?php endif; ?>
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
    </div>
</section>

<!-- Quick View Modal -->
<div class="modal-overlay" id="quickViewModal">
    <div class="modal-content">
        <button class="modal-close" id="modalClose"><i class="fa-solid fa-xmark"></i></button>
        <div class="modal-body">
            <div class="modal-icon"><i class="fa-solid fa-mobile-screen"></i></div>
            <div class="modal-details">
                <span class="product-brand" id="modalBrand"></span>
                <h2 id="modalName"></h2>
                <div class="product-specs" id="modalSpecs"></div>
                <p class="detail-price" id="modalPrice"></p>
                <div class="detail-buttons">
                    <button class="btn btn-primary"><i class="fa-solid fa-cart-shopping"></i> Add to Cart</button>
                    <button class="btn btn-outline"><i class="fa-solid fa-heart"></i> Wishlist</button>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if ($hasVideo): ?>
<script>
function toggleMute(btn) {
    var video = document.querySelector('.brand-video');
    video.muted = !video.muted;
    btn.innerHTML = video.muted
        ? '<i class="fa-solid fa-volume-xmark"></i>'
        : '<i class="fa-solid fa-volume-high"></i>';
}
</script>
<?php endif; ?>

<?php include '../_foot.php'; ?>
