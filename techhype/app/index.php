<?php
include '_base.php';

// Featured products (on sale)
$stm = $db->query('SELECT * FROM products WHERE sale_price IS NOT NULL AND status = "active" ORDER BY sort_order ASC, created_at DESC LIMIT 4');
$deals = $stm->fetchAll();

// Top selling products (by quantity sold)
$stm = $db->query('
    SELECT p.*, COALESCE(SUM(oi.quantity), 0) AS total_sold
    FROM products p
    LEFT JOIN order_items oi ON p.id = oi.product_id
    WHERE p.status = "active"
    GROUP BY p.id
    HAVING total_sold > 0
    ORDER BY total_sold DESC
    LIMIT 5
');
$topSelling = $stm->fetchAll();

// Latest products
$stm = $db->query('SELECT * FROM products WHERE status = "active" ORDER BY sort_order ASC, created_at DESC LIMIT 8');
$latest = $stm->fetchAll();

include '_head.php';
?>

<!-- Hero Section -->
<section class="hero">
    <div class="container hero-content">
        <span class="hero-tag">New Arrivals 2026</span>
        <h1>Next-Gen Tech.<br>At Your Fingertips.</h1>
        <p>Explore the latest smartphones, tablets, laptops, and gaming consoles from top brands worldwide.</p>
        <div class="hero-buttons">
            <a href="<?= $base ?>/products.php" class="btn btn-primary">Shop Now</a>
            <a href="#deals" class="btn btn-outline-light">View Deals</a>
        </div>
    </div>
</section>

<!-- Category Icons -->
<section class="section category-section">
    <div class="container">
        <div class="category-icons">
            <a href="<?= $base ?>/products.php?category=mobile" class="cat-icon">
                <div class="cat-circle"><i class="fa-solid fa-mobile-screen"></i></div>
                <span>Mobiles</span>
            </a>
            <a href="<?= $base ?>/products.php?category=tablet" class="cat-icon">
                <div class="cat-circle"><i class="fa-solid fa-tablet-screen-button"></i></div>
                <span>Tablets</span>
            </a>
            <a href="<?= $base ?>/products.php?category=console" class="cat-icon">
                <div class="cat-circle"><i class="fa-solid fa-gamepad"></i></div>
                <span>Consoles</span>
            </a>
            <a href="<?= $base ?>/products.php?category=audio" class="cat-icon">
                <div class="cat-circle"><i class="fa-solid fa-headphones"></i></div>
                <span>Audio</span>
            </a>
            <a href="<?= $base ?>/products.php?category=watch" class="cat-icon">
                <div class="cat-circle"><i class="fa-solid fa-clock"></i></div>
                <span>Watches</span>
            </a>
        </div>
    </div>
</section>

<!-- Brands Section -->
<section class="section section-gray" id="brands">
    <div class="container">
        <h2 class="section-title">Shop by Brand</h2>
        <div class="brands-grid">
            <?php
            $brands = ['Samsung','Apple','Vivo','Oppo','Xiaomi','Nothing','Google','Sony','iQOO'];
            foreach ($brands as $b): ?>
            <a href="<?= $base ?>/page/brand.php?name=<?= urlencode($b) ?>" class="brand-card">
                <i class="fa-solid fa-mobile-screen"></i>
                <h4><?= $b ?></h4>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Hot Deals (from DB) -->
<?php if ($deals): ?>
<section class="section" id="deals">
    <div class="container">
        <h2 class="section-title">Hot Deals</h2>
        <div class="products-grid">
            <?php foreach ($deals as $p):
                $colors = json_decode($p->colors ?? '[]', true) ?: [];
                $firstColor = $colors[0] ?? null;
                $initialStorage = $firstColor['storage'] ?? [];
            ?>
            <div class="product-card">
                <a href="<?= $base ?>/product-detail.php?id=<?= $p->id ?>">
                    <div class="product-img product-img-icon">
                        <?php if ($p->photo !== 'default-product.png'): ?>
                            <img src="<?= $base ?>/uploads/<?= $p->photo ?>" alt="">
                        <?php else: ?>
                            <div class="product-icon"><i class="fa-solid fa-mobile-screen"></i></div>
                        <?php endif; ?>
                        <span class="product-badge sale">-<?= round((1 - $p->sale_price / $p->price) * 100) ?>%</span>
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

                    <p class="product-price"><span class="old-price"><?= format_price($p->price) ?></span> <?= format_price($p->sale_price) ?></p>
                    <form method="POST" action="<?= $base ?>/cart-add.php">
                        <input type="hidden" name="id" value="<?= $p->id ?>">
                        <button type="submit" class="btn btn-primary btn-sm add-to-cart" style="width:100%">Add to Cart</button>
                    </form>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Promo Banner -->
<section class="promo-banner">
    <div class="container promo-content">
        <h2><i class="fa-solid fa-bolt"></i> Flash Sale - This Weekend Only!</h2>
        <p>Up to 40% off on selected smartphones, laptops & gaming consoles. Don't miss out!</p>
        <a href="<?= $base ?>/products.php" class="btn btn-primary">Grab the Deal</a>
    </div>
</section>

<!-- Top Selling Products -->
<?php if ($topSelling): ?>
<section class="section section-gray">
    <div class="container">
        <h2 class="section-title"><i class="fa-solid fa-fire" style="color:#ff3b30;"></i> Top Selling Products</h2>
        <div class="top-selling-grid">
            <?php foreach ($topSelling as $rank => $p): ?>
            <a href="<?= $base ?>/product-detail.php?id=<?= $p->id ?>" class="top-selling-card">
                <span class="top-selling-rank"><?= $rank + 1 ?></span>
                <?php if ($p->photo !== 'default-product.png'): ?>
                    <img src="<?= $base ?>/uploads/<?= $p->photo ?>" alt="<?= clean($p->name) ?>">
                <?php else: ?>
                    <div style="width:100px;height:100px;margin:0 auto 12px;display:flex;align-items:center;justify-content:center;background:#f5f5f7;border-radius:12px;"><i class="fa-solid fa-mobile-screen" style="font-size:32px;color:#999;"></i></div>
                <?php endif; ?>
                <h4><?= clean($p->name) ?></h4>
                <div class="sold-count"><i class="fa-solid fa-fire-flame-curved"></i> <?= $p->total_sold ?> sold</div>
                <div class="top-price"><?= format_price($p->sale_price ?: $p->price) ?></div>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Latest Products (from DB) -->
<?php if ($latest): ?>
<section class="section">
    <div class="container">
        <h2 class="section-title">Latest Products</h2>
        <div class="products-grid">
            <?php foreach ($latest as $p):
                $colors = json_decode($p->colors ?? '[]', true) ?: [];
                $firstColor = $colors[0] ?? null;
                $initialStorage = $firstColor['storage'] ?? [];
            ?>
            <div class="product-card">
                <a href="<?= $base ?>/product-detail.php?id=<?= $p->id ?>">
                    <div class="product-img product-img-icon">
                        <?php if ($p->photo !== 'default-product.png'): ?>
                            <img src="<?= $base ?>/uploads/<?= $p->photo ?>" alt="">
                        <?php else: ?>
                            <div class="product-icon"><i class="fa-solid fa-mobile-screen"></i></div>
                        <?php endif; ?>
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
        <div style="text-align:center;margin-top:30px;">
            <a href="<?= $base ?>/products.php" class="btn btn-outline">View All Products</a>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Why Choose Us -->
<section class="section section-gray">
    <div class="container">
        <h2 class="section-title">Why Shop With Us</h2>
        <div class="features-grid">
            <div class="feature-card">
                <i class="fa-solid fa-shield-halved"></i>
                <h4>100% Authentic</h4>
                <p>All products are genuine with official manufacturer warranty.</p>
            </div>
            <div class="feature-card">
                <i class="fa-solid fa-truck-fast"></i>
                <h4>Fast Delivery</h4>
                <p>Free shipping on orders above RM 500. Delivered in 2-4 days.</p>
            </div>
            <div class="feature-card">
                <i class="fa-solid fa-credit-card"></i>
                <h4>Easy EMI</h4>
                <p>0% EMI available on major credit cards. Pay in easy installments.</p>
            </div>
            <div class="feature-card">
                <i class="fa-solid fa-headset"></i>
                <h4>24/7 Support</h4>
                <p>Expert tech support available around the clock for all products.</p>
            </div>
        </div>
    </div>
</section>

<!-- Newsletter -->
<section class="section">
    <div class="container newsletter">
        <h2>Get Tech Updates</h2>
        <p>Subscribe to get notified about new launches, deals, and exclusive offers.</p>
        <form class="newsletter-form">
            <input type="email" placeholder="Enter your email address">
            <button type="submit" class="btn btn-primary">Subscribe</button>
        </form>
    </div>
</section>

<?php include '_foot.php'; ?>
