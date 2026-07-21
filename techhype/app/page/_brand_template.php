<?php include '../_head.php'; ?>

<!-- Brand Header -->
<section class="brand-header" style="background: <?= $brand_color ?>;">
    <div class="container">
        <h1><?= $brand ?></h1>
        <p><?= $brand_desc ?></p>
    </div>
</section>

<!-- Filter Tabs -->
<section class="section">
    <div class="container">
        <div class="filter-tabs">
            <button class="filter-btn active" data-filter="all">All</button>
            <?php
            $categories = array_unique(array_column($products, 'cat'));
            foreach ($categories as $cat): ?>
                <button class="filter-btn" data-filter="<?= strtolower($cat) ?>"><?= $cat ?></button>
            <?php endforeach; ?>
        </div>

        <div class="shop-toolbar">
            <p>Showing <strong><?= count($products) ?></strong> products</p>
            <select class="sort-select" id="sortSelect">
                <option value="default">Sort by: Default</option>
                <option value="low">Price: Low to High</option>
                <option value="high">Price: High to Low</option>
            </select>
        </div>

        <div class="products-grid" id="productsGrid">
            <?php foreach ($products as $p): ?>
            <?php
                // Get first color's storage for initial display
                $firstColor = $p['colors'][0] ?? null;
                $initialStorage = $firstColor['storage'] ?? [];
            ?>
            <div class="product-card" data-category="<?= strtolower($p['cat']) ?>" data-price="<?= intval(str_replace(['RM ', ',', ' '], '', $p['sale'] ?: $p['price'])) ?>">
                <?php if (!empty($p['images'])): ?>
                <div class="product-img product-slider">
                    <div class="slider-track">
                        <?php foreach ($p['images'] as $img): ?>
                        <div class="slider-slide"><img src="../images/<?= $img ?>" alt="<?= $p['name'] ?>"></div>
                        <?php endforeach; ?>
                    </div>
                    <?php if (count($p['images']) > 1): ?>
                    <button class="slider-btn slider-prev"><i class="fa-solid fa-chevron-left"></i></button>
                    <button class="slider-btn slider-next"><i class="fa-solid fa-chevron-right"></i></button>
                    <div class="slider-dots">
                        <?php for ($i = 0; $i < count($p['images']); $i++): ?>
                        <span class="slider-dot <?= $i === 0 ? 'active' : '' ?>" data-index="<?= $i ?>"></span>
                        <?php endfor; ?>
                    </div>
                    <?php endif; ?>
                    <div class="product-actions">
                        <button class="action-btn"><i class="fa-solid fa-heart"></i></button>
                        <button class="action-btn"><i class="fa-solid fa-cart-shopping"></i></button>
                        <button class="action-btn quick-view-btn"><i class="fa-solid fa-eye"></i></button>
                    </div>
                    <?php if ($p['badge']): ?>
                    <span class="product-badge <?= str_contains($p['badge'], '%') ? 'sale' : '' ?>"><?= $p['badge'] ?></span>
                    <?php endif; ?>
                </div>
                <?php else: ?>
                <div class="product-img product-img-icon">
                    <div class="product-icon"><i class="fa-solid <?= $p['icon'] ?? 'fa-mobile-screen' ?>"></i></div>
                    <div class="product-actions">
                        <button class="action-btn"><i class="fa-solid fa-heart"></i></button>
                        <button class="action-btn"><i class="fa-solid fa-cart-shopping"></i></button>
                        <button class="action-btn quick-view-btn"><i class="fa-solid fa-eye"></i></button>
                    </div>
                    <?php if ($p['badge']): ?>
                    <span class="product-badge <?= str_contains($p['badge'], '%') ? 'sale' : '' ?>"><?= $p['badge'] ?></span>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
                <div class="product-info">
                    <span class="product-brand"><?= $brand ?></span>
                    <h4><?= $p['name'] ?></h4>
                    <div class="product-specs"><?= $p['specs'] ?></div>

                    <?php if (!empty($p['colors'])): ?>
                    <div class="variant-colors">
                        <?php foreach ($p['colors'] as $i => $c): ?>
                        <span class="variant-color-dot <?= $i === 0 ? 'active' : '' ?>"
                              style="background: <?= $c['hex'] ?>;"
                              title="<?= $c['name'] ?>"
                              <?= !empty($c['image']) ? 'data-image="../images/' . $c['image'] . '"' : '' ?>
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
                        <?php if ($p['sale']): ?>
                        <span class="old-price"><?= $p['price'] ?></span> <?= $p['sale'] ?>
                        <?php else: ?>
                        <?= $p['price'] ?>
                        <?php endif; ?>
                    </p>
                    <button class="btn btn-primary btn-sm add-to-cart">Add to Cart</button>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
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

<?php include '../_foot.php'; ?>
