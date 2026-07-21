<?php
include '_base.php';

$id = intval($_GET['id'] ?? 0);
$stm = $db->prepare('SELECT * FROM products WHERE id = ? AND status = "active"');
$stm->execute([$id]);
$product = $stm->fetch();

if (!$product) {
    flash('error', 'Product not found.');
    redirect('/products.php');
}

// Related products
$stm = $db->prepare('SELECT * FROM products WHERE brand = ? AND id != ? AND status = "active" LIMIT 4');
$stm->execute([$product->brand, $product->id]);
$related = $stm->fetchAll();

$colors = json_decode($product->colors ?? '[]', true) ?: [];
$gallery = json_decode($product->gallery ?? '[]', true) ?: [];
$firstColor = $colors[0] ?? null;
$initialStorage = $firstColor['storage'] ?? [];
$specData_raw = json_decode($product->specifications ?? '{}', true) ?: [];
$bandColors = $specData_raw['band_colors'] ?? [];
$isWatch = $product->category === 'watch';

// Handle review submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_review'])) {
    $revName    = clean($_POST['reviewer_name'] ?? '');
    $revRating  = intval($_POST['rating'] ?? 5);
    $revTitle   = clean($_POST['review_title'] ?? '');
    $revComment = clean($_POST['review_comment'] ?? '');
    $userId     = is_login() ? auth()->id : null;

    if ($revName && $revRating >= 1 && $revRating <= 5 && $revComment) {
        $stm = $db->prepare('INSERT INTO reviews (product_id, user_id, reviewer_name, rating, title, comment) VALUES (?, ?, ?, ?, ?, ?)');
        $stm->execute([$product->id, $userId, $revName, $revRating, $revTitle, $revComment]);
        flash('success', 'Thank you for your review!');
        redirect("/product-detail.php?id={$product->id}#reviews");
    }
}

// Get reviews
$stm = $db->prepare('SELECT * FROM reviews WHERE product_id = ? ORDER BY created_at DESC');
$stm->execute([$product->id]);
$reviews = $stm->fetchAll();

// Calculate average rating
$avgRating = 0;
$ratingCounts = [1=>0, 2=>0, 3=>0, 4=>0, 5=>0];
if (count($reviews)) {
    $totalRating = 0;
    foreach ($reviews as $r) {
        $totalRating += $r->rating;
        $ratingCounts[$r->rating]++;
    }
    $avgRating = round($totalRating / count($reviews), 1);
}

include '_head.php';
?>

<section class="section">
    <div class="container product-detail">
        <div class="product-gallery">
            <div class="gallery-main">
                <?php if ($product->photo !== 'default-product.png'): ?>
                    <img id="mainImage" src="<?= $base ?>/uploads/<?= $product->photo ?>" alt="<?= clean($product->name) ?>">
                <?php else: ?>
                    <div class="product-img-icon" style="height:450px;border-radius:8px;">
                        <div class="product-icon" style="font-size:100px;"><i class="fa-solid fa-mobile-screen"></i></div>
                    </div>
                <?php endif; ?>
            </div>
            <?php if (count($gallery) > 1): ?>
            <div class="gallery-thumbs">
                <?php foreach ($gallery as $i => $img): ?>
                <img class="gallery-thumb <?= $i === 0 ? 'active' : '' ?>" src="<?= $base ?>/uploads/<?= $img ?>" alt="<?= clean($product->name) ?>" onclick="changeMainImage(this, '<?= $base ?>/uploads/<?= $img ?>')">
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>

        <div class="product-details">
            <span class="product-brand"><?= clean($product->brand) ?></span>
            <h1><?= clean($product->name) ?></h1>
            <div class="product-specs" style="font-size:14px;margin:10px 0;"><?= clean($product->specs ?? '') ?></div>

            <p class="detail-price">
                <?php if ($product->sale_price): ?>
                    <span class="old-price" style="font-size:18px;"><?= format_price($product->price) ?></span>
                    <?= format_price($product->sale_price) ?>
                <?php else: ?>
                    <?= format_price($product->price) ?>
                <?php endif; ?>
            </p>

            <?php if ($colors): ?>
            <div style="margin: 15px 0;">
                <label style="font-size:14px;font-weight:600;display:block;margin-bottom:8px;"><?= $isWatch ? 'Case Color' : 'Color' ?>: <span id="colorName"><?= $colors[0]['name'] ?? '' ?></span></label>
                <div class="variant-colors">
                    <?php foreach ($colors as $i => $c): ?>
                    <span class="variant-color-dot <?= $i === 0 ? 'active' : '' ?>"
                          style="background: <?= $c['hex'] ?>; width: 28px; height: 28px;"
                          title="<?= $c['name'] ?>"
                          data-name="<?= $c['name'] ?>"
                          data-image="<?= !empty($c['image']) ? $base . '/uploads/' . $c['image'] : '' ?>"
                          data-storage='<?= json_encode($c['storage'] ?? [], JSON_HEX_APOS) ?>'></span>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <?php if ($isWatch && !empty($bandColors)): ?>
            <div style="margin: 15px 0;">
                <label style="font-size:14px;font-weight:600;display:block;margin-bottom:8px;">Band Color: <span id="bandColorName"><?= $bandColors[0]['name'] ?? '' ?></span></label>
                <div class="variant-colors" id="bandColorDots">
                    <?php foreach ($bandColors as $i => $bc): ?>
                    <span class="variant-band-color-dot <?= $i === 0 ? 'active' : '' ?>"
                          style="background: <?= $bc['hex'] ?>; width: 28px; height: 28px; border-radius: 50%; display: inline-block; cursor: pointer; border: 2px solid <?= $i === 0 ? 'var(--primary)' : '#ddd' ?>; transition: border-color 0.2s;"
                          title="<?= $bc['name'] ?>"
                          data-name="<?= $bc['name'] ?>"></span>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <?php if (!empty($initialStorage)): ?>
            <div style="margin: 15px 0;">
                <label style="font-size:14px;font-weight:600;display:block;margin-bottom:8px;">Storage</label>
                <div class="variant-storage" id="detailStorage">
                    <?php foreach ($initialStorage as $i => $s): ?>
                    <button type="button" class="variant-storage-btn <?= $i === 0 ? 'active' : '' ?>" data-price="<?= $s['price'] ?>" data-sale="<?= $s['sale'] ?? '' ?>"><?= $s['label'] ?></button>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <div class="detail-options">
                <div class="option-group">
                    <label>Stock: <strong style="color: <?= $product->stock > 0 ? '#34c759' : '#ff3b30' ?>">
                        <?= $product->stock > 0 ? $product->stock . ' available' : 'Out of stock' ?>
                    </strong></label>
                </div>
                <div class="option-group">
                    <label>Quantity</label>
                    <form method="POST" action="<?= $base ?>/cart-add.php" class="qty-form">
                        <input type="hidden" name="id" value="<?= $product->id ?>">
                        <input type="hidden" name="selected_color" id="selectedColor" value="<?= $colors[0]['name'] ?? '' ?>">
                        <input type="hidden" name="selected_band_color" id="selectedBandColor" value="<?= $bandColors[0]['name'] ?? '' ?>">
                        <input type="hidden" name="selected_storage" id="selectedStorage" value="<?= $initialStorage[0]['label'] ?? '' ?>">
                        <input type="hidden" name="selected_price" id="selectedPrice" value="">
                        <div class="qty-selector">
                            <button type="button" class="qty-btn" onclick="changeQty(-1)">-</button>
                            <input type="number" name="qty" value="1" min="1" max="<?= $product->stock ?>" id="qtyInput">
                            <button type="button" class="qty-btn" onclick="changeQty(1)">+</button>
                        </div>
                </div>
            </div>

            <div class="detail-buttons">
                <?php if ($product->stock > 0): ?>
                    <button type="submit" class="btn btn-primary btn-lg"><i class="fa-solid fa-cart-shopping"></i> Add to Cart</button>
                <?php else: ?>
                    <button class="btn btn-primary btn-lg" disabled>Out of Stock</button>
                <?php endif; ?>
                    </form>
            </div>

            <div class="detail-meta">
                <p><i class="fa-solid fa-truck"></i> Free shipping on orders above RM 500</p>
                <p><i class="fa-solid fa-rotate-left"></i> 30-day return policy</p>
                <p><i class="fa-solid fa-shield"></i> Official warranty</p>
            </div>
        </div>
    </div>
</section>

<!-- Description / Specs Tabs Section -->
<section class="section section-gray">
    <div class="container">
        <div class="detail-tabs">
            <button class="detail-tab active" data-tab="description">Description</button>
            <button class="detail-tab" data-tab="specifications">Specifications</button>
            <button class="detail-tab" data-tab="features">Features</button>
            <button class="detail-tab" data-tab="reviews">Reviews (<?= count($reviews) ?>)</button>
        </div>

        <div class="detail-tab-content" id="tab-description" style="display:block;">
            <div class="tab-panel">
                <?php if (!empty($product->description)): ?>
                    <p><?= nl2br(clean($product->description)) ?></p>
                <?php else: ?>
                    <p>The <?= clean($product->brand) ?> <?= clean($product->name) ?> delivers cutting-edge technology with premium design. Built for performance and style, this device offers an exceptional experience for every user.</p>
                <?php endif; ?>
            </div>
        </div>

        <div class="detail-tab-content" id="tab-specifications" style="display:none;">
            <div class="tab-panel">
                <?php
                $specData = json_decode($product->specifications ?? '{}', true) ?: [];
                // Get other products from same brand for comparison
                $stmCompare = $db->prepare('SELECT id, name, photo, brand, price, sale_price, specifications, colors FROM products WHERE id != ? AND status = "active" ORDER BY brand, name');
                $stmCompare->execute([$product->id]);
                $compareProducts = $stmCompare->fetchAll();
                ?>

                <!-- Compare Selector -->
                <div style="margin-bottom:20px;display:flex;align-items:center;gap:15px;flex-wrap:wrap;">
                    <label style="font-weight:600;font-size:14px;">Compare with:</label>
                    <select id="compareSelect" class="sort-select" style="padding:10px 15px;min-width:250px;">
                        <option value="">-- Select a product to compare --</option>
                        <?php
                        $currentBrand = null;
                        foreach ($compareProducts as $cp):
                            if ($cp->brand !== $currentBrand):
                                if ($currentBrand !== null) echo '</optgroup>';
                                $currentBrand = $cp->brand;
                                echo '<optgroup label="' . clean($cp->brand) . '">';
                            endif;
                        ?>
                        <option value="<?= $cp->id ?>"><?= clean($cp->name) ?></option>
                        <?php endforeach; ?>
                        <?php if ($currentBrand !== null) echo '</optgroup>'; ?>
                    </select>
                </div>

                <!-- Comparison Table -->
                <table class="specs-table compare-table">
                    <thead>
                        <tr>
                            <th style="width:25%;background:#f8f8f8;"></th>
                            <th style="width:37.5%;text-align:center;background:#e8f4fd;">
                                <?php if ($product->photo !== 'default-product.png'): ?>
                                <img src="<?= $base ?>/uploads/<?= $product->photo ?>" style="width:80px;height:80px;object-fit:contain;display:block;margin:0 auto 8px;">
                                <?php endif; ?>
                                <strong style="color:var(--primary);"><?= clean($product->name) ?></strong>
                            </th>
                            <th id="compareHeader" style="width:37.5%;text-align:center;background:#f0f0f0;color:#aaa;">
                                <div style="padding:20px 0;">
                                    <i class="fa-solid fa-plus" style="font-size:24px;"></i>
                                    <div style="margin-top:8px;font-weight:normal;">Select a product</div>
                                </div>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Category-specific spec labels
                        if ($product->category === 'audio') {
                            $specLabels = [
                                'audio_type' => 'Type',
                                'audio_driver_size' => 'Driver Size',
                                'audio_frequency_response' => 'Frequency Response',
                                'audio_impedance' => 'Impedance',
                                'audio_sensitivity' => 'Sensitivity',
                                'audio_anc' => 'Active Noise Cancelling',
                                'audio_noise_cancelling' => 'Noise Cancelling Type',
                                'audio_battery_life' => 'Battery Life',
                                'audio_charging_time' => 'Charging Time',
                                'audio_bluetooth' => 'Bluetooth',
                                'audio_codec' => 'Audio Codec',
                                'audio_water_resistance' => 'Water Resistance',
                                'audio_microphone' => 'Microphone',
                                'audio_controls' => 'Controls',
                                'audio_cable_length' => 'Cable Length',
                                'dimensions' => 'Dimensions',
                                'weight' => 'Weight (g)',
                            ];
                        } elseif ($product->category === 'console') {
                            $specLabels = [
                                'console_cpu' => 'CPU',
                                'console_gpu' => 'GPU',
                                'console_memory' => 'Memory (RAM)',
                                'console_storage_type' => 'Storage',
                                'console_optical_drive' => 'Optical Drive',
                                'console_max_resolution' => 'Max Resolution',
                                'console_frame_rate' => 'Frame Rate',
                                'console_ray_tracing' => 'Ray Tracing',
                                'console_hdr' => 'HDR Support',
                                'console_audio_output' => 'Audio Output',
                                'console_usb_ports' => 'USB Ports',
                                'console_hdmi' => 'HDMI',
                                'console_wifi' => 'Wi-Fi',
                                'console_bluetooth' => 'Bluetooth',
                                'console_backwards_compat' => 'Backwards Compatibility',
                                'console_power_consumption' => 'Power Consumption',
                                'dimensions' => 'Dimensions',
                                'weight' => 'Weight (g)',
                            ];
                        } else {
                            $specLabels = [
                                'display_size' => 'Size (Main Display)',
                                'display_resolution' => 'Resolution (Main Display)',
                                'display_technology' => 'Technology (Main Display)',
                                'sub_display_size' => 'Size (Sub Display)',
                                'sub_display_resolution' => 'Resolution (Sub Display)',
                                'sub_display_technology' => 'Technology (Sub Display)',
                                'rear_camera' => 'Rear Camera - Resolution (Multiple)',
                                'front_camera' => 'Front Camera - Resolution',
                                'rear_camera_fnumber' => 'Rear Camera - F Number (Multiple)',
                                'video_resolution' => 'Video Recording Resolution',
                                'chipset' => 'Chipset',
                                'battery_capacity' => 'Battery Capacity (mAh, Typical)',
                                'cpu_speed' => 'CPU Speed',
                                'cpu_type' => 'CPU Type',
                                'memory_gb' => 'Memory (GB)',
                                'storage_gb' => 'Storage (GB)',
                                'dimensions' => 'Dimensions (HxWxD, mm)',
                                'weight' => 'Weight (g)',
                                'sim_count' => 'Number of SIM',
                                'os' => 'Operating System',
                                'connectivity' => 'Connectivity',
                            ];
                        }
                        foreach ($specLabels as $key => $label): ?>
                        <tr>
                            <td class="spec-label"><strong><?= $label ?></strong></td>
                            <td class="spec-value" style="text-align:center;color:#1a73e8;"><?= !empty($specData[$key]) ? clean($specData[$key]) : '<span style="color:#ccc;">—</span>' ?></td>
                            <td class="spec-value compare-value" data-key="<?= $key ?>" style="text-align:center;color:#333;"><span style="color:#ccc;">—</span></td>
                        </tr>
                        <?php endforeach; ?>
                        <tr>
                            <td class="spec-label"><strong>Price</strong></td>
                            <td class="spec-value" style="text-align:center;color:#1a73e8;font-weight:600;"><?= format_price($product->sale_price ?: $product->price) ?></td>
                            <td class="spec-value compare-value" data-key="price" style="text-align:center;font-weight:600;"><span style="color:#ccc;">—</span></td>
                        </tr>
                    </tbody>
                </table>
                <p style="text-align:center;margin-top:15px;font-size:12px;color:#888;">* Specifications may vary depending on region and model.</p>

                <!-- Hidden JSON data for comparison -->
                <script>
                var compareData = {
                    <?php foreach ($compareProducts as $cp):
                        $cpSpecs = json_decode($cp->specifications ?? '{}', true) ?: [];
                        $cpPrice = $cp->sale_price ?: $cp->price;
                    ?>
                    <?= $cp->id ?>: {
                        name: <?= json_encode(clean($cp->name)) ?>,
                        photo: <?= json_encode($cp->photo !== 'default-product.png' ? $base . '/uploads/' . $cp->photo : '') ?>,
                        price: <?= json_encode(format_price($cpPrice)) ?>,
                        specs: <?= json_encode($cpSpecs) ?>
                    },
                    <?php endforeach; ?>
                };
                </script>
            </div>
        </div>

        <div class="detail-tab-content" id="tab-features" style="display:none;">
            <div class="tab-panel">
                <div class="features-list">
                    <?php
                    // Generate features based on category
                    if ($product->category === 'audio') {
                        $featureIcons = [
                            ['icon' => 'fa-headphones', 'title' => 'Premium Sound Quality', 'desc' => 'Crystal-clear audio with deep bass and wide soundstage for an immersive listening experience.'],
                            ['icon' => 'fa-volume-xmark', 'title' => 'Active Noise Cancelling', 'desc' => 'Block out the world with industry-leading noise cancellation technology.'],
                            ['icon' => 'fa-battery-full', 'title' => 'Long Battery Life', 'desc' => 'All-day battery that keeps your music playing from morning to night.'],
                            ['icon' => 'fa-bluetooth-b', 'title' => 'Wireless Freedom', 'desc' => 'Latest Bluetooth technology for stable, high-quality wireless audio streaming.'],
                            ['icon' => 'fa-microphone', 'title' => 'Crystal-Clear Calls', 'desc' => 'Advanced microphones with noise reduction for crystal-clear voice calls.'],
                            ['icon' => 'fa-droplet', 'title' => 'Water Resistant', 'desc' => 'Sweat and splash-proof design perfect for workouts and outdoor use.'],
                        ];
                    } elseif ($product->category === 'console') {
                        $featureIcons = [
                            ['icon' => 'fa-microchip', 'title' => 'Next-Gen Performance', 'desc' => 'Blazing-fast processor and GPU deliver stunning visuals and smooth gameplay.'],
                            ['icon' => 'fa-tv', 'title' => '4K Gaming', 'desc' => 'Experience games in breathtaking 4K resolution with high frame rates.'],
                            ['icon' => 'fa-wand-magic-sparkles', 'title' => 'Ray Tracing', 'desc' => 'Realistic lighting, reflections, and shadows powered by hardware ray tracing.'],
                            ['icon' => 'fa-hard-drive', 'title' => 'Ultra-Fast SSD', 'desc' => 'Near-instant load times and seamless world transitions with custom SSD.'],
                            ['icon' => 'fa-volume-high', 'title' => '3D Audio', 'desc' => 'Immersive spatial audio that puts you at the center of the action.'],
                            ['icon' => 'fa-gamepad', 'title' => 'Backwards Compatible', 'desc' => 'Play thousands of games from previous generations on day one.'],
                        ];
                    } else {
                        $featureIcons = [
                            ['icon' => 'fa-camera', 'title' => 'Pro Camera System', 'desc' => 'Capture stunning photos and videos with advanced camera technology.'],
                            ['icon' => 'fa-microchip', 'title' => 'Powerful Processor', 'desc' => 'Lightning-fast performance for multitasking, gaming, and productivity.'],
                            ['icon' => 'fa-battery-full', 'title' => 'All-Day Battery', 'desc' => 'Long-lasting battery that keeps up with your busy lifestyle.'],
                            ['icon' => 'fa-display', 'title' => 'Stunning Display', 'desc' => 'Vibrant screen with rich colors and sharp details for immersive viewing.'],
                            ['icon' => 'fa-wifi', 'title' => '5G Connectivity', 'desc' => 'Ultra-fast 5G network support for seamless streaming and downloads.'],
                            ['icon' => 'fa-shield-halved', 'title' => 'Built to Last', 'desc' => 'Durable design with water and dust resistance for everyday protection.'],
                        ];
                    }
                    foreach ($featureIcons as $f): ?>
                    <div class="feature-item">
                        <div class="feature-icon-circle"><i class="fa-solid <?= $f['icon'] ?>"></i></div>
                        <div>
                            <h4><?= $f['title'] ?></h4>
                            <p><?= $f['desc'] ?></p>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <!-- Reviews Tab -->
        <div class="detail-tab-content" id="tab-reviews" style="display:none;">
            <div class="tab-panel" id="reviews">
                <!-- Rating Summary -->
                <div class="review-summary">
                    <div class="review-avg">
                        <div class="avg-number"><?= $avgRating ?></div>
                        <div class="avg-stars">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <i class="fa-<?= $i <= round($avgRating) ? 'solid' : 'regular' ?> fa-star"></i>
                            <?php endfor; ?>
                        </div>
                        <div class="avg-count"><?= count($reviews) ?> review<?= count($reviews) !== 1 ? 's' : '' ?></div>
                    </div>
                    <div class="review-bars">
                        <?php for ($i = 5; $i >= 1; $i--): ?>
                        <div class="rating-bar-row">
                            <span><?= $i ?> <i class="fa-solid fa-star" style="font-size:11px;color:#f5a623;"></i></span>
                            <div class="rating-bar">
                                <div class="rating-bar-fill" style="width: <?= count($reviews) ? round($ratingCounts[$i] / count($reviews) * 100) : 0 ?>%"></div>
                            </div>
                            <span class="rating-bar-count"><?= $ratingCounts[$i] ?></span>
                        </div>
                        <?php endfor; ?>
                    </div>
                </div>

                <!-- Review Form -->
                <div class="review-form-section">
                    <h3>Write a Review</h3>
                    <form method="POST" class="review-form">
                        <div class="form-row">
                            <div class="form-group">
                                <label>Your Name *</label>
                                <input type="text" name="reviewer_name" required value="<?= is_login() ? clean(auth()->name) : '' ?>" placeholder="Enter your name">
                            </div>
                            <div class="form-group">
                                <label>Review Title</label>
                                <input type="text" name="review_title" placeholder="e.g. Amazing phone!">
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Rating *</label>
                            <div class="star-rating-input">
                                <?php for ($i = 5; $i >= 1; $i--): ?>
                                <input type="radio" name="rating" value="<?= $i ?>" id="star<?= $i ?>" <?= $i === 5 ? 'checked' : '' ?>>
                                <label for="star<?= $i ?>"><i class="fa-solid fa-star"></i></label>
                                <?php endfor; ?>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Your Review *</label>
                            <textarea name="review_comment" rows="4" required placeholder="Share your experience with this product..."></textarea>
                        </div>
                        <button type="submit" name="submit_review" class="btn btn-primary">Submit Review</button>
                    </form>
                </div>

                <!-- Reviews List -->
                <?php if ($reviews): ?>
                <div class="reviews-list">
                    <?php foreach ($reviews as $r): ?>
                    <div class="review-card">
                        <div class="review-header">
                            <div class="review-user">
                                <div class="review-avatar"><?= strtoupper(substr($r->reviewer_name, 0, 1)) ?></div>
                                <div>
                                    <strong><?= clean($r->reviewer_name) ?></strong>
                                    <span class="review-date"><?= date('M d, Y', strtotime($r->created_at)) ?></span>
                                </div>
                            </div>
                            <div class="review-stars">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <i class="fa-<?= $i <= $r->rating ? 'solid' : 'regular' ?> fa-star"></i>
                                <?php endfor; ?>
                            </div>
                        </div>
                        <?php if ($r->title): ?>
                        <h4 class="review-title"><?= clean($r->title) ?></h4>
                        <?php endif; ?>
                        <p class="review-text"><?= nl2br(clean($r->comment)) ?></p>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <div style="text-align:center;padding:30px;color:#888;">
                    <i class="fa-regular fa-comment-dots" style="font-size:40px;margin-bottom:10px;display:block;"></i>
                    <p>No reviews yet. Be the first to review this product!</p>
                </div>
                <?php endif; ?>
            </div>
        </div>

    </div>
</section>

<?php if ($related): ?>
<section class="section">
    <div class="container">
        <h2 class="section-title">Related Products</h2>
        <div class="products-grid">
            <?php foreach ($related as $r): ?>
            <div class="product-card">
                <a href="<?= $base ?>/product-detail.php?id=<?= $r->id ?>">
                    <div class="product-img product-img-icon">
                        <?php if ($r->photo !== 'default-product.png'): ?>
                            <img src="<?= $base ?>/uploads/<?= $r->photo ?>" alt="<?= clean($r->name) ?>">
                        <?php else: ?>
                            <div class="product-icon"><i class="fa-solid fa-mobile-screen"></i></div>
                        <?php endif; ?>
                    </div>
                </a>
                <div class="product-info">
                    <span class="product-brand"><?= clean($r->brand) ?></span>
                    <h4><?= clean($r->name) ?></h4>
                    <p class="product-price">
                        <?php if ($r->sale_price): ?>
                            <span class="old-price"><?= format_price($r->price) ?></span> <?= format_price($r->sale_price) ?>
                        <?php else: ?>
                            <?= format_price($r->price) ?>
                        <?php endif; ?>
                    </p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<script>
function changeMainImage(thumb, src) {
    document.getElementById('mainImage').src = src;
    document.querySelectorAll('.gallery-thumb').forEach(t => t.classList.remove('active'));
    thumb.classList.add('active');
}

function changeQty(delta) {
    const input = document.getElementById('qtyInput');
    let val = parseInt(input.value) + delta;
    if (val < 1) val = 1;
    if (val > <?= $product->stock ?>) val = <?= $product->stock ?>;
    input.value = val;
}

// Tab switching
document.querySelectorAll('.detail-tab').forEach(tab => {
    tab.addEventListener('click', () => {
        document.querySelectorAll('.detail-tab').forEach(t => t.classList.remove('active'));
        document.querySelectorAll('.detail-tab-content').forEach(c => c.style.display = 'none');
        tab.classList.add('active');
        document.getElementById('tab-' + tab.dataset.tab).style.display = 'block';
    });
});

// Color dot selection + image swap + storage rebuild
document.querySelectorAll('.product-details .variant-color-dot').forEach(dot => {
    dot.addEventListener('click', () => {
        dot.parentElement.querySelectorAll('.variant-color-dot').forEach(d => d.classList.remove('active'));
        dot.classList.add('active');

        // Update color name label
        var colorName = dot.dataset.name || '';
        var nameEl = document.getElementById('colorName');
        if (nameEl) nameEl.textContent = colorName;

        // Set hidden field
        document.getElementById('selectedColor').value = colorName;

        // Swap image
        var img = dot.dataset.image;
        if (img) {
            var galleryImg = document.querySelector('.gallery-main img');
            if (galleryImg) galleryImg.src = img;
        }

        // Rebuild storage buttons
        var storageData = [];
        try { storageData = JSON.parse(dot.dataset.storage || '[]'); } catch(e) {}
        var container = document.getElementById('detailStorage');
        if (container && storageData.length) {
            container.innerHTML = '';
            storageData.forEach(function(s, i) {
                var btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'variant-storage-btn' + (i === 0 ? ' active' : '');
                btn.dataset.price = s.price;
                btn.dataset.sale = s.sale || '';
                btn.textContent = s.label;
                btn.addEventListener('click', handleStorageClick);
                container.appendChild(btn);
            });
            // Update price & hidden field to first storage
            updateDetailPrice(storageData[0].price, storageData[0].sale);
            document.getElementById('selectedStorage').value = storageData[0].label;
            document.getElementById('selectedPrice').value = storageData[0].sale || storageData[0].price;
        }
    });
});

function updateDetailPrice(price, sale) {
    var priceEl = document.querySelector('.detail-price');
    if (sale) {
        priceEl.innerHTML = '<span class="old-price" style="font-size:18px;">' + price + '</span> ' + sale;
    } else {
        priceEl.innerHTML = price;
    }
}

function handleStorageClick() {
    this.parentElement.querySelectorAll('.variant-storage-btn').forEach(b => b.classList.remove('active'));
    this.classList.add('active');
    updateDetailPrice(this.dataset.price, this.dataset.sale);
    // Set hidden fields
    document.getElementById('selectedStorage').value = this.textContent;
    document.getElementById('selectedPrice').value = this.dataset.sale || this.dataset.price;
}

// Attach to initial storage buttons
document.querySelectorAll('.product-details .variant-storage-btn').forEach(btn => {
    btn.addEventListener('click', handleStorageClick);
});

// Band color dot selection
document.querySelectorAll('.product-details .variant-band-color-dot').forEach(dot => {
    dot.addEventListener('click', () => {
        dot.parentElement.querySelectorAll('.variant-band-color-dot').forEach(d => {
            d.classList.remove('active');
            d.style.borderColor = '#ddd';
        });
        dot.classList.add('active');
        dot.style.borderColor = 'var(--primary)';

        var bandName = dot.dataset.name || '';
        var nameEl = document.getElementById('bandColorName');
        if (nameEl) nameEl.textContent = bandName;

        document.getElementById('selectedBandColor').value = bandName;
    });
});

// Spec Comparison
var compareSelect = document.getElementById('compareSelect');
if (compareSelect) {
    compareSelect.addEventListener('change', function() {
        var id = this.value;
        var header = document.getElementById('compareHeader');
        var cells = document.querySelectorAll('.compare-value');

        if (!id || !compareData[id]) {
            header.innerHTML = '<div style="padding:20px 0;"><i class="fa-solid fa-plus" style="font-size:24px;"></i><div style="margin-top:8px;font-weight:normal;">Select a product</div></div>';
            header.style.background = '#f0f0f0';
            header.style.color = '#aaa';
            cells.forEach(function(cell) {
                cell.innerHTML = '<span style="color:#ccc;">—</span>';
            });
            return;
        }

        var data = compareData[id];
        header.style.background = '#f0f8f0';
        header.style.color = '#333';
        header.innerHTML = (data.photo ? '<img src="' + data.photo + '" style="width:80px;height:80px;object-fit:contain;display:block;margin:0 auto 8px;">' : '') +
            '<strong style="color:#2e8b57;">' + data.name + '</strong>';

        cells.forEach(function(cell) {
            var key = cell.dataset.key;
            if (key === 'price') {
                cell.innerHTML = '<strong>' + data.price + '</strong>';
            } else if (data.specs[key]) {
                cell.textContent = data.specs[key];
                cell.style.color = '#333';
            } else {
                cell.innerHTML = '<span style="color:#ccc;">—</span>';
            }
        });
    });
}
</script>

<?php include '_foot.php'; ?>
