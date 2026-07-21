<?php
include '../_base.php';
require_admin();

$id = intval($_GET['id'] ?? 0);
$editing = false;
$product = null;

if ($id) {
    $stm = $db->prepare('SELECT * FROM products WHERE id = ?');
    $stm->execute([$id]);
    $product = $stm->fetch();
    if ($product) $editing = true;
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name        = clean($_POST['name'] ?? '');
    $brand       = clean($_POST['brand'] ?? '');
    $category    = clean($_POST['category'] ?? '');
    $price       = floatval($_POST['price'] ?? 0);
    $sale_price  = $_POST['sale_price'] ? floatval($_POST['sale_price']) : null;
    $specs       = clean($_POST['specs'] ?? '');
    $description = clean($_POST['description'] ?? '');
    $stock       = intval($_POST['stock'] ?? 0);
    $status      = clean($_POST['status'] ?? 'active');

    if (!$name) $errors[] = 'Product name is required.';
    if (!$brand) $errors[] = 'Brand is required.';
    if ($price <= 0) $errors[] = 'Price must be greater than 0.';

    // Photo upload
    $photo = $editing ? $product->photo : 'default-product.png';
    if (isset($_FILES['photo']) && $_FILES['photo']['size'] > 0) {
        $uploaded = upload_photo($_FILES['photo']);
        if ($uploaded) {
            if ($editing) delete_photo($product->photo);
            $photo = $uploaded;
        } else {
            $errors[] = 'Invalid photo.';
        }
    }

    // Gallery uploads
    $existingGallery = [];
    if ($editing) {
        $existingGallery = json_decode($product->gallery ?? '[]', true) ?: [];
    }
    // Keep existing gallery images that weren't removed
    $keepGallery = $_POST['keep_gallery'] ?? [];
    $gallery = array_values(array_intersect($existingGallery, $keepGallery));
    // Add new gallery uploads
    if (!empty($_FILES['gallery']['name'][0])) {
        foreach ($_FILES['gallery']['tmp_name'] as $i => $tmp) {
            if ($_FILES['gallery']['size'][$i] > 0) {
                $gFile = [
                    'name' => $_FILES['gallery']['name'][$i],
                    'tmp_name' => $tmp,
                    'size' => $_FILES['gallery']['size'][$i],
                    'error' => $_FILES['gallery']['error'][$i],
                ];
                $gUploaded = upload_photo($gFile);
                if ($gUploaded) $gallery[] = $gUploaded;
            }
        }
    }

    // Build colors JSON with nested storage
    $colors = [];
    if (!empty($_POST['color_name'])) {
        foreach ($_POST['color_name'] as $ci => $cName) {
            if (empty($cName)) continue;
            $color = [
                'name' => $cName,
                'hex' => $_POST['color_hex'][$ci] ?? '#000000',
                'image' => $_POST['color_image_existing'][$ci] ?? '',
            ];
            // Upload color-specific image
            if (!empty($_FILES['color_image']['tmp_name'][$ci]) && $_FILES['color_image']['size'][$ci] > 0) {
                $cFile = [
                    'name' => $_FILES['color_image']['name'][$ci],
                    'tmp_name' => $_FILES['color_image']['tmp_name'][$ci],
                    'size' => $_FILES['color_image']['size'][$ci],
                    'error' => $_FILES['color_image']['error'][$ci],
                ];
                $cUploaded = upload_photo($cFile);
                if ($cUploaded) $color['image'] = $cUploaded;
            }
            // Nested storage for this color
            $color['storage'] = [];
            if (!empty($_POST['storage_label'][$ci])) {
                foreach ($_POST['storage_label'][$ci] as $si => $sLabel) {
                    if (empty($sLabel)) continue;
                    $color['storage'][] = [
                        'label' => $sLabel,
                        'price' => $_POST['storage_price'][$ci][$si] ?? '',
                        'sale'  => $_POST['storage_sale'][$ci][$si] ?? '',
                    ];
                }
            }
            $colors[] = $color;
        }
    }

    $colorsJson = json_encode($colors);
    $galleryJson = json_encode($gallery);

    // Build band colors JSON (for watches)
    $bandColors = [];
    if ($category === 'watch' && !empty($_POST['band_color_name'])) {
        foreach ($_POST['band_color_name'] as $bi => $bName) {
            if (empty($bName)) continue;
            $bandColor = [
                'name'  => $bName,
                'hex'   => $_POST['band_color_hex'][$bi] ?? '#000000',
                'image' => $_POST['band_color_image_existing'][$bi] ?? '',
            ];
            if (!empty($_FILES['band_color_image']['tmp_name'][$bi]) && $_FILES['band_color_image']['size'][$bi] > 0) {
                $bcFile = [
                    'name'     => $_FILES['band_color_image']['name'][$bi],
                    'tmp_name' => $_FILES['band_color_image']['tmp_name'][$bi],
                    'size'     => $_FILES['band_color_image']['size'][$bi],
                    'error'    => $_FILES['band_color_image']['error'][$bi],
                ];
                $bcUploaded = upload_photo($bcFile);
                if ($bcUploaded) $bandColor['image'] = $bcUploaded;
            }
            $bandColors[] = $bandColor;
        }
    }

    // Build specifications JSON
    $specFields = [
        // Mobile/Tablet/Laptop
        'display_size', 'display_resolution', 'display_technology',
        'sub_display_size', 'sub_display_resolution', 'sub_display_technology',
        'rear_camera', 'front_camera', 'rear_camera_fnumber', 'video_resolution',
        'chipset', 'battery_capacity', 'cpu_speed', 'cpu_type',
        'memory_gb', 'storage_gb', 'dimensions', 'weight', 'sim_count', 'os', 'connectivity',
        // Audio
        'audio_driver_size', 'audio_type', 'audio_frequency_response', 'audio_impedance',
        'audio_sensitivity', 'audio_anc', 'audio_battery_life', 'audio_charging_time',
        'audio_bluetooth', 'audio_codec', 'audio_water_resistance', 'audio_microphone',
        'audio_noise_cancelling', 'audio_controls', 'audio_cable_length',
        // Console
        'console_cpu', 'console_gpu', 'console_memory', 'console_storage_type',
        'console_optical_drive', 'console_max_resolution', 'console_frame_rate',
        'console_ray_tracing', 'console_hdr', 'console_audio_output',
        'console_usb_ports', 'console_hdmi', 'console_wifi', 'console_bluetooth',
        'console_backwards_compat', 'console_power_consumption',
    ];
    $specifications = [];
    foreach ($specFields as $sf) {
        $val = trim($_POST['spec_' . $sf] ?? '');
        if ($val !== '') $specifications[$sf] = $val;
    }
    if (!empty($bandColors)) {
        $specifications['band_colors'] = $bandColors;
    }
    $specsJson = json_encode($specifications);

    if (empty($errors)) {
        if ($editing) {
            $stm = $db->prepare('UPDATE products SET name=?, brand=?, category=?, price=?, sale_price=?, specs=?, description=?, specifications=?, photo=?, stock=?, status=?, colors=?, gallery=? WHERE id=?');
            $stm->execute([$name, $brand, $category, $price, $sale_price, $specs, $description, $specsJson, $photo, $stock, $status, $colorsJson, $galleryJson, $id]);
            flash('success', 'Product updated.');
        } else {
            $stm = $db->prepare('INSERT INTO products (name, brand, category, price, sale_price, specs, description, specifications, photo, stock, status, colors, gallery) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
            $stm->execute([$name, $brand, $category, $price, $sale_price, $specs, $description, $specsJson, $photo, $stock, $status, $colorsJson, $galleryJson]);
            flash('success', 'Product created.');
        }
        redirect('/admin/products.php');
    }
}

// Fetch all products for "copy specs" and "copy colors" features
$allProducts = $db->query('SELECT id, name, brand, specifications, colors FROM products ORDER BY brand, name')->fetchAll();

$brands = ['Samsung','Apple','Sony','Google','Xiaomi','Oppo','Vivo','Nothing','iQOO'];
$categories = ['mobile','tablet','console','audio','watch','accessory'];

// Parse existing colors, gallery, and specifications for editing
$existingColors = [];
$existingGallery = [];
$existingSpecs = [];
$existingBandColors = [];
if ($editing) {
    $existingColors = json_decode($product->colors ?? '[]', true) ?: [];
    $existingGallery = json_decode($product->gallery ?? '[]', true) ?: [];
    $existingSpecs = json_decode($product->specifications ?? '{}', true) ?: [];
    $existingBandColors = $existingSpecs['band_colors'] ?? [];
}

include '../_head.php';
?>

<section class="brand-header" style="background: #1d1d1f;">
    <div class="container">
        <h1><?= $editing ? 'Edit' : 'Add' ?> Product</h1>
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

        <?php if ($errors): ?>
            <div class="alert alert-error">
                <?php foreach ($errors as $e): ?><div><?= $e ?></div><?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div class="auth-card" style="max-width:900px;">
            <form method="POST" enctype="multipart/form-data">
                <!-- Basic Info -->
                <h3 style="margin-bottom:15px;border-bottom:2px solid #eee;padding-bottom:10px;">Basic Info</h3>
                <div class="form-row">
                    <div class="form-group">
                        <label>Product Name *</label>
                        <input type="text" name="name" required value="<?= clean($_POST['name'] ?? ($product->name ?? '')) ?>">
                    </div>
                    <div class="form-group">
                        <label>Brand *</label>
                        <select name="brand" required class="sort-select" style="width:100%;padding:12px;">
                            <option value="">Select Brand</option>
                            <?php foreach ($brands as $b): ?>
                                <option value="<?= $b ?>" <?= ($product->brand ?? '') === $b ? 'selected' : '' ?>><?= $b ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Category *</label>
                        <select name="category" required class="sort-select" style="width:100%;padding:12px;">
                            <?php foreach ($categories as $c): ?>
                                <option value="<?= $c ?>" <?= ($product->category ?? '') === $c ? 'selected' : '' ?>><?= ucfirst($c) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Specs</label>
                        <input type="text" name="specs" value="<?= clean($_POST['specs'] ?? ($product->specs ?? '')) ?>" placeholder="256GB | 12GB RAM | 50MP">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Price (RM) *</label>
                        <input type="number" name="price" step="0.01" required value="<?= $_POST['price'] ?? ($product->price ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label>Sale Price (RM)</label>
                        <input type="number" name="sale_price" step="0.01" value="<?= $_POST['sale_price'] ?? ($product->sale_price ?? '') ?>">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Stock</label>
                        <input type="number" name="stock" value="<?= $_POST['stock'] ?? ($product->stock ?? 0) ?>">
                    </div>
                    <div class="form-group">
                        <label>Status</label>
                        <select name="status" class="sort-select" style="width:100%;padding:12px;">
                            <option value="active" <?= ($product->status ?? 'active') === 'active' ? 'selected' : '' ?>>Active</option>
                            <option value="inactive" <?= ($product->status ?? '') === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" rows="8" style="resize:vertical;min-height:120px;background:#fff;color:#333;font-size:14px;line-height:1.6;"><?= clean($_POST['description'] ?? ($product->description ?? '')) ?></textarea>
                </div>
                <!-- Specifications -->
                <h3 style="margin:25px 0 15px;border-bottom:2px solid #eee;padding-bottom:10px;">Specifications</h3>

                <div style="margin-bottom:18px;padding:12px 15px;background:#e8f4fd;border-radius:10px;border:1px solid #b8daf0;">
                    <label style="font-size:13px;font-weight:600;display:block;margin-bottom:6px;"><i class="fa-solid fa-copy"></i> Copy specs from an existing product</label>
                    <div style="display:flex;gap:10px;align-items:center;">
                        <select id="copySpecsSelect" class="sort-select" style="flex:1;padding:10px 15px;">
                            <option value="">-- Select a product --</option>
                            <?php
                            $lastBrand = '';
                            foreach ($allProducts as $ap):
                                if ($ap->brand !== $lastBrand):
                                    if ($lastBrand) echo '</optgroup>';
                                    echo '<optgroup label="' . clean($ap->brand) . '">';
                                    $lastBrand = $ap->brand;
                                endif;
                            ?>
                                <option value="<?= $ap->id ?>" <?= $editing && $ap->id == $id ? 'disabled' : '' ?>><?= clean($ap->name) ?></option>
                            <?php endforeach;
                            if ($lastBrand) echo '</optgroup>'; ?>
                        </select>
                        <button type="button" id="copySpecsBtn" class="btn btn-primary btn-sm" style="white-space:nowrap;padding:10px 18px;">Copy Specs</button>
                    </div>
                    <small style="color:#5a8bb0;margin-top:6px;display:block;">This will fill all spec fields below. You can still edit them after copying.</small>
                </div>

                <div id="defaultSpecsSection">
                <p style="font-size:13px;color:#888;margin-bottom:10px;">Main Display</p>
                <div class="form-row">
                    <div class="form-group">
                        <label>Main Display Size</label>
                        <input type="text" name="spec_display_size" value="<?= $existingSpecs['display_size'] ?? '' ?>" placeholder="e.g. 6.9 inches">
                    </div>
                    <div class="form-group">
                        <label>Main Display Resolution</label>
                        <input type="text" name="spec_display_resolution" value="<?= $existingSpecs['display_resolution'] ?? '' ?>" placeholder="e.g. 3120 x 1440 (Quad HD+)">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Main Display Technology</label>
                        <input type="text" name="spec_display_technology" value="<?= $existingSpecs['display_technology'] ?? '' ?>" placeholder="e.g. Dynamic AMOLED 2X">
                    </div>
                    <div class="form-group"></div>
                </div>

                <p style="font-size:13px;color:#888;margin:10px 0;">Sub / Cover Display (for foldable phones, leave empty if not applicable)</p>
                <div class="form-row">
                    <div class="form-group">
                        <label>Sub Display Size</label>
                        <input type="text" name="spec_sub_display_size" value="<?= $existingSpecs['sub_display_size'] ?? '' ?>" placeholder="e.g. 3.4 inches">
                    </div>
                    <div class="form-group">
                        <label>Sub Display Resolution</label>
                        <input type="text" name="spec_sub_display_resolution" value="<?= $existingSpecs['sub_display_resolution'] ?? '' ?>" placeholder="e.g. 720 x 748">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Sub Display Technology</label>
                        <input type="text" name="spec_sub_display_technology" value="<?= $existingSpecs['sub_display_technology'] ?? '' ?>" placeholder="e.g. Super AMOLED">
                    </div>
                    <div class="form-group"></div>
                </div>

                <p style="font-size:13px;color:#888;margin:10px 0;">Camera</p>
                <div class="form-row">
                    <div class="form-group">
                        <label>Rear Camera Resolution</label>
                        <input type="text" name="spec_rear_camera" value="<?= $existingSpecs['rear_camera'] ?? '' ?>" placeholder="e.g. 200MP + 50MP + 10MP + 12MP">
                    </div>
                    <div class="form-group">
                        <label>Front Camera Resolution</label>
                        <input type="text" name="spec_front_camera" value="<?= $existingSpecs['front_camera'] ?? '' ?>" placeholder="e.g. 12MP">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Rear Camera F-Number</label>
                        <input type="text" name="spec_rear_camera_fnumber" value="<?= $existingSpecs['rear_camera_fnumber'] ?? '' ?>" placeholder="e.g. F1.7 / F3.4 / F2.4 / F2.2">
                    </div>
                    <div class="form-group">
                        <label>Video Recording Resolution</label>
                        <input type="text" name="spec_video_resolution" value="<?= $existingSpecs['video_resolution'] ?? '' ?>" placeholder="e.g. 8K @ 30fps, 4K @ 120fps">
                    </div>
                </div>
                <p style="font-size:13px;color:#888;margin:10px 0;">Performance</p>
                <div class="form-row">
                    <div class="form-group">
                        <label>Chipset</label>
                        <input type="text" name="spec_chipset" value="<?= $existingSpecs['chipset'] ?? '' ?>" placeholder="e.g. Snapdragon 8 Elite / Dimensity 9400">
                    </div>
                    <div class="form-group">
                        <label>CPU Speed</label>
                        <input type="text" name="spec_cpu_speed" value="<?= $existingSpecs['cpu_speed'] ?? '' ?>" placeholder="e.g. 3.39 GHz, 3.1 GHz, 2.9 GHz">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Battery Capacity (mAh)</label>
                        <input type="text" name="spec_battery_capacity" value="<?= $existingSpecs['battery_capacity'] ?? '' ?>" placeholder="e.g. 5000">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>CPU Type</label>
                        <input type="text" name="spec_cpu_type" value="<?= $existingSpecs['cpu_type'] ?? '' ?>" placeholder="e.g. Snapdragon 8 Elite for Galaxy">
                    </div>
                    <div class="form-group">
                        <label>Memory (GB)</label>
                        <input type="text" name="spec_memory_gb" value="<?= $existingSpecs['memory_gb'] ?? '' ?>" placeholder="e.g. 12">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Storage (GB)</label>
                        <input type="text" name="spec_storage_gb" value="<?= $existingSpecs['storage_gb'] ?? '' ?>" placeholder="e.g. 256 / 512 / 1024">
                    </div>
                    <div class="form-group">
                        <label>Dimensions (HxWxD, mm)</label>
                        <input type="text" name="spec_dimensions" value="<?= $existingSpecs['dimensions'] ?? '' ?>" placeholder="e.g. 162.8 x 77.6 x 8.2">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Weight (g)</label>
                        <input type="text" name="spec_weight" value="<?= $existingSpecs['weight'] ?? '' ?>" placeholder="e.g. 218">
                    </div>
                    <div class="form-group">
                        <label>Number of SIM</label>
                        <input type="text" name="spec_sim_count" value="<?= $existingSpecs['sim_count'] ?? '' ?>" placeholder="e.g. Dual SIM (Nano + eSIM)">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Operating System</label>
                        <input type="text" name="spec_os" value="<?= $existingSpecs['os'] ?? '' ?>" placeholder="e.g. Android 15 / One UI 7 / iOS 18">
                    </div>
                    <div class="form-group">
                        <label>Connectivity</label>
                        <select name="spec_connectivity" style="width:100%;padding:10px 15px;border:1px solid #ddd;border-radius:8px;font-family:inherit;font-size:14px;">
                            <option value="">-- Not applicable --</option>
                            <option value="Wi-Fi Only" <?= ($existingSpecs['connectivity'] ?? '') === 'Wi-Fi Only' ? 'selected' : '' ?>>Wi-Fi Only</option>
                            <option value="Wi-Fi + LTE" <?= ($existingSpecs['connectivity'] ?? '') === 'Wi-Fi + LTE' ? 'selected' : '' ?>>Wi-Fi + LTE (4G)</option>
                            <option value="Wi-Fi + 5G" <?= ($existingSpecs['connectivity'] ?? '') === 'Wi-Fi + 5G' ? 'selected' : '' ?>>Wi-Fi + 5G</option>
                        </select>
                    </div>
                </div>
                </div>

                <!-- Audio Specs (shown when category = audio) -->
                <div id="audioSpecsSection" style="display:none;">
                <p style="font-size:13px;color:#888;margin:10px 0;"><strong>Audio Specifications</strong></p>
                <div class="form-row">
                    <div class="form-group">
                        <label>Type</label>
                        <input type="text" name="spec_audio_type" value="<?= $existingSpecs['audio_type'] ?? '' ?>" placeholder="e.g. Over-Ear / In-Ear / On-Ear / Speaker">
                    </div>
                    <div class="form-group">
                        <label>Driver Size</label>
                        <input type="text" name="spec_audio_driver_size" value="<?= $existingSpecs['audio_driver_size'] ?? '' ?>" placeholder="e.g. 30mm / 40mm">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Frequency Response</label>
                        <input type="text" name="spec_audio_frequency_response" value="<?= $existingSpecs['audio_frequency_response'] ?? '' ?>" placeholder="e.g. 4 Hz - 40,000 Hz">
                    </div>
                    <div class="form-group">
                        <label>Impedance</label>
                        <input type="text" name="spec_audio_impedance" value="<?= $existingSpecs['audio_impedance'] ?? '' ?>" placeholder="e.g. 48 ohm">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Sensitivity</label>
                        <input type="text" name="spec_audio_sensitivity" value="<?= $existingSpecs['audio_sensitivity'] ?? '' ?>" placeholder="e.g. 102 dB/mW">
                    </div>
                    <div class="form-group">
                        <label>Active Noise Cancelling</label>
                        <input type="text" name="spec_audio_anc" value="<?= $existingSpecs['audio_anc'] ?? '' ?>" placeholder="e.g. Yes (Adaptive ANC)">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Battery Life</label>
                        <input type="text" name="spec_audio_battery_life" value="<?= $existingSpecs['audio_battery_life'] ?? '' ?>" placeholder="e.g. 30 hours (ANC on)">
                    </div>
                    <div class="form-group">
                        <label>Charging Time</label>
                        <input type="text" name="spec_audio_charging_time" value="<?= $existingSpecs['audio_charging_time'] ?? '' ?>" placeholder="e.g. 3.5 hours (3 min = 3 hrs playback)">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Bluetooth Version</label>
                        <input type="text" name="spec_audio_bluetooth" value="<?= $existingSpecs['audio_bluetooth'] ?? '' ?>" placeholder="e.g. Bluetooth 5.3">
                    </div>
                    <div class="form-group">
                        <label>Audio Codec</label>
                        <input type="text" name="spec_audio_codec" value="<?= $existingSpecs['audio_codec'] ?? '' ?>" placeholder="e.g. LDAC, AAC, SBC, LC3">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Water Resistance</label>
                        <input type="text" name="spec_audio_water_resistance" value="<?= $existingSpecs['audio_water_resistance'] ?? '' ?>" placeholder="e.g. IPX4 / IP67">
                    </div>
                    <div class="form-group">
                        <label>Microphone</label>
                        <input type="text" name="spec_audio_microphone" value="<?= $existingSpecs['audio_microphone'] ?? '' ?>" placeholder="e.g. Dual beamforming microphones">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Noise Cancelling Type</label>
                        <input type="text" name="spec_audio_noise_cancelling" value="<?= $existingSpecs['audio_noise_cancelling'] ?? '' ?>" placeholder="e.g. Hybrid ANC / Feedforward">
                    </div>
                    <div class="form-group">
                        <label>Controls</label>
                        <input type="text" name="spec_audio_controls" value="<?= $existingSpecs['audio_controls'] ?? '' ?>" placeholder="e.g. Touch sensor, Speak-to-Chat">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Cable Length (if wired)</label>
                        <input type="text" name="spec_audio_cable_length" value="<?= $existingSpecs['audio_cable_length'] ?? '' ?>" placeholder="e.g. 1.2m detachable">
                    </div>
                    <div class="form-group"></div>
                </div>
                <p style="font-size:13px;color:#888;margin:10px 0;">Physical</p>
                <div class="form-row">
                    <div class="form-group">
                        <label>Dimensions</label>
                        <input type="text" name="spec_dimensions" value="<?= $existingSpecs['dimensions'] ?? '' ?>" placeholder="e.g. 250 x 190 x 35 mm">
                    </div>
                    <div class="form-group">
                        <label>Weight (g)</label>
                        <input type="text" name="spec_weight" value="<?= $existingSpecs['weight'] ?? '' ?>" placeholder="e.g. 250">
                    </div>
                </div>
                </div>

                <!-- Console Specs (shown when category = console) -->
                <div id="consoleSpecsSection" style="display:none;">
                <p style="font-size:13px;color:#888;margin:10px 0;"><strong>Console Specifications</strong></p>
                <div class="form-row">
                    <div class="form-group">
                        <label>CPU</label>
                        <input type="text" name="spec_console_cpu" value="<?= $existingSpecs['console_cpu'] ?? '' ?>" placeholder="e.g. AMD Zen 2 8-core @ 3.5 GHz">
                    </div>
                    <div class="form-group">
                        <label>GPU</label>
                        <input type="text" name="spec_console_gpu" value="<?= $existingSpecs['console_gpu'] ?? '' ?>" placeholder="e.g. AMD RDNA 2, 10.28 TFLOPS">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Memory (RAM)</label>
                        <input type="text" name="spec_console_memory" value="<?= $existingSpecs['console_memory'] ?? '' ?>" placeholder="e.g. 16 GB GDDR6">
                    </div>
                    <div class="form-group">
                        <label>Storage Type</label>
                        <input type="text" name="spec_console_storage_type" value="<?= $existingSpecs['console_storage_type'] ?? '' ?>" placeholder="e.g. Custom 825GB SSD">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Optical Drive</label>
                        <input type="text" name="spec_console_optical_drive" value="<?= $existingSpecs['console_optical_drive'] ?? '' ?>" placeholder="e.g. 4K UHD Blu-ray / Digital Only">
                    </div>
                    <div class="form-group">
                        <label>Max Resolution</label>
                        <input type="text" name="spec_console_max_resolution" value="<?= $existingSpecs['console_max_resolution'] ?? '' ?>" placeholder="e.g. 4K @ 120fps / 8K">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Frame Rate</label>
                        <input type="text" name="spec_console_frame_rate" value="<?= $existingSpecs['console_frame_rate'] ?? '' ?>" placeholder="e.g. Up to 120fps">
                    </div>
                    <div class="form-group">
                        <label>Ray Tracing</label>
                        <input type="text" name="spec_console_ray_tracing" value="<?= $existingSpecs['console_ray_tracing'] ?? '' ?>" placeholder="e.g. Yes (Hardware-accelerated)">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>HDR Support</label>
                        <input type="text" name="spec_console_hdr" value="<?= $existingSpecs['console_hdr'] ?? '' ?>" placeholder="e.g. HDR10, Dolby Vision">
                    </div>
                    <div class="form-group">
                        <label>Audio Output</label>
                        <input type="text" name="spec_console_audio_output" value="<?= $existingSpecs['console_audio_output'] ?? '' ?>" placeholder="e.g. Tempest 3D AudioTech">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>USB Ports</label>
                        <input type="text" name="spec_console_usb_ports" value="<?= $existingSpecs['console_usb_ports'] ?? '' ?>" placeholder="e.g. 2x USB-C, 1x USB-A">
                    </div>
                    <div class="form-group">
                        <label>HDMI</label>
                        <input type="text" name="spec_console_hdmi" value="<?= $existingSpecs['console_hdmi'] ?? '' ?>" placeholder="e.g. HDMI 2.1">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Wi-Fi</label>
                        <input type="text" name="spec_console_wifi" value="<?= $existingSpecs['console_wifi'] ?? '' ?>" placeholder="e.g. Wi-Fi 6E (802.11ax)">
                    </div>
                    <div class="form-group">
                        <label>Bluetooth</label>
                        <input type="text" name="spec_console_bluetooth" value="<?= $existingSpecs['console_bluetooth'] ?? '' ?>" placeholder="e.g. Bluetooth 5.1">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Backwards Compatibility</label>
                        <input type="text" name="spec_console_backwards_compat" value="<?= $existingSpecs['console_backwards_compat'] ?? '' ?>" placeholder="e.g. PS4 games supported">
                    </div>
                    <div class="form-group">
                        <label>Power Consumption</label>
                        <input type="text" name="spec_console_power_consumption" value="<?= $existingSpecs['console_power_consumption'] ?? '' ?>" placeholder="e.g. 350W max">
                    </div>
                </div>
                <p style="font-size:13px;color:#888;margin:10px 0;">Physical</p>
                <div class="form-row">
                    <div class="form-group">
                        <label>Dimensions</label>
                        <input type="text" name="spec_dimensions" value="<?= $existingSpecs['dimensions'] ?? '' ?>" placeholder="e.g. 390 x 260 x 104 mm">
                    </div>
                    <div class="form-group">
                        <label>Weight (g)</label>
                        <input type="text" name="spec_weight" value="<?= $existingSpecs['weight'] ?? '' ?>" placeholder="e.g. 3900">
                    </div>
                </div>
                </div>

                <!-- Product Photo -->
                <h3 style="margin:25px 0 15px;border-bottom:2px solid #eee;padding-bottom:10px;">Product Photo</h3>
                <div class="form-group">
                    <label>Main Product Photo</label>
                    <div class="dropzone dropzone-single" id="photoDropzone">
                        <input type="file" name="photo" accept="image/*" style="display:none;" id="photoInput">
                        <div class="dropzone-icon"><i class="fa-solid fa-cloud-arrow-up"></i></div>
                        <div class="dropzone-text">Drag & drop image here or <strong>click to browse</strong></div>
                        <div class="dropzone-hint">JPG, PNG, WebP accepted</div>
                        <div class="dropzone-preview" id="photoPreview">
                            <?php if ($editing && $product->photo !== 'default-product.png'): ?>
                            <div class="dropzone-preview-item">
                                <img src="<?= $base ?>/uploads/<?= $product->photo ?>" alt="">
                                <small class="preview-name"><?= $product->photo ?></small>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Gallery Images -->
                <h3 style="margin:25px 0 15px;border-bottom:2px solid #eee;padding-bottom:10px;">Gallery Images</h3>
                <?php if (!empty($existingGallery)): ?>
                <div class="form-group">
                    <label>Current Gallery</label>
                    <div style="display:flex;gap:10px;flex-wrap:wrap;margin-bottom:10px;">
                        <?php foreach ($existingGallery as $gi => $gImg): ?>
                        <div style="position:relative;border:1px solid #ddd;border-radius:8px;overflow:hidden;">
                            <img src="<?= $base ?>/uploads/<?= $gImg ?>" style="width:80px;height:80px;object-fit:cover;">
                            <label style="display:block;text-align:center;padding:4px;font-size:12px;background:#f5f5f5;">
                                <input type="checkbox" name="keep_gallery[]" value="<?= $gImg ?>" checked> Keep
                            </label>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
                <div class="form-group">
                    <label>Add Gallery Images</label>
                    <div class="dropzone" id="galleryDropzone">
                        <div id="galleryInputs"></div>
                        <div class="dropzone-icon"><i class="fa-solid fa-images"></i></div>
                        <div class="dropzone-text">Drag & drop images here or <strong>click to browse</strong></div>
                        <div class="dropzone-hint">Multiple images supported. JPG, PNG, WebP accepted</div>
                        <div class="dropzone-preview" id="galleryPreview"></div>
                    </div>
                </div>

                <!-- Color Variants -->
                <h3 style="margin:25px 0 15px;border-bottom:2px solid #eee;padding-bottom:10px;" id="colorVariantsTitle">Color Variants</h3>
                <div id="colorsContainer">
                    <?php if (!empty($existingColors)):
                        foreach ($existingColors as $ci => $c): ?>
                    <div class="color-block" style="border:1px solid #ddd;border-radius:10px;padding:15px;margin-bottom:15px;background:#fafafa;">
                        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;">
                            <strong>Color #<?= $ci + 1 ?></strong>
                            <button type="button" class="btn btn-outline btn-sm remove-color" style="color:red;border-color:red;">Remove</button>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label>Pick a Color</label>
                                <select class="color-preset-select sort-select" style="width:100%;padding:12px;" onchange="applyColorPreset(this)">
                                    <option value="">-- Choose a color --</option>
                                    <option value="#1a1a1a|Black" <?= $c['hex'] === '#1a1a1a' ? 'selected' : '' ?>>⬛ Black</option>
                                    <option value="#ffffff|White" <?= $c['hex'] === '#ffffff' ? 'selected' : '' ?>>⬜ White</option>
                                    <option value="#e5e4e2|Silver" <?= $c['hex'] === '#e5e4e2' || $c['hex'] === '#c0c0c0' ? 'selected' : '' ?>>🩶 Silver</option>
                                    <option value="#7D7C78|Gray" <?= $c['hex'] === '#7D7C78' || $c['hex'] === '#383838' || $c['hex'] === '#6e6e6e' || $c['hex'] === '#8a8a8a' ? 'selected' : '' ?>>🩶 Gray</option>
                                    <option value="#3C3D3A|Titanium Black" <?= $c['hex'] === '#3C3D3A' ? 'selected' : '' ?>>⬛ Titanium Black</option>
                                    <option value="#8999AD|Titanium Blue" <?= $c['hex'] === '#8999AD' ? 'selected' : '' ?>>🔵 Titanium Blue</option>
                                    <option value="#d2b48c|Beige / Gold" <?= $c['hex'] === '#d2b48c' || $c['hex'] === '#c8a85a' || $c['hex'] === '#daa520' ? 'selected' : '' ?>>🟡 Beige / Gold</option>
                                    <option value="#e8c87a|Light Gold" <?= $c['hex'] === '#e8c87a' ? 'selected' : '' ?>>🟡 Light Gold</option>
                                    <option value="#4169e1|Blue" <?= $c['hex'] === '#4169e1' || $c['hex'] === '#3a5ba0' || $c['hex'] === '#415fff' ? 'selected' : '' ?>>🔵 Blue</option>
                                    <option value="#1b2a4a|Navy / Dark Blue" <?= $c['hex'] === '#1b2a4a' ? 'selected' : '' ?>>🔵 Navy</option>
                                    <option value="#87ceeb|Light Blue" <?= $c['hex'] === '#87ceeb' || $c['hex'] === '#a8c8e8' ? 'selected' : '' ?>>🩵 Light Blue</option>
                                    <option value="#2e8b57|Green" <?= $c['hex'] === '#2e8b57' || $c['hex'] === '#5a7a5a' || $c['hex'] === '#7ab89c' ? 'selected' : '' ?>>🟢 Green</option>
                                    <option value="#98ff98|Mint" <?= $c['hex'] === '#98ff98' ? 'selected' : '' ?>>🟢 Mint</option>
                                    <option value="#6b4c8a|Purple" <?= $c['hex'] === '#6b4c8a' || $c['hex'] === '#6a5acd' || $c['hex'] === '#7e5f8a' || $c['hex'] === '#c8a2c8' ? 'selected' : '' ?>>🟣 Purple</option>
                                    <option value="#f5c6d0|Pink" <?= $c['hex'] === '#f5c6d0' ? 'selected' : '' ?>>🩷 Pink</option>
                                    <option value="#ff3b30|Red" <?= $c['hex'] === '#ff3b30' ? 'selected' : '' ?>>🔴 Red</option>
                                    <option value="#ffd700|Yellow" <?= $c['hex'] === '#ffd700' || $c['hex'] === '#fffdd0' ? 'selected' : '' ?>>🟡 Yellow</option>
                                    <option value="#ff6700|Orange" <?= $c['hex'] === '#ff6700' || $c['hex'] === '#f47920' ? 'selected' : '' ?>>🟠 Orange</option>
                                    <option value="custom|Custom..." >🎨 Custom...</option>
                                </select>
                                <input type="hidden" name="color_hex[]" value="<?= $c['hex'] ?>">
                                <input type="hidden" name="color_name[]" value="<?= clean($c['name']) ?>">
                                <div class="custom-color-row" style="display:none;margin-top:8px;gap:8px;align-items:center;">
                                    <input type="color" class="custom-color-picker" value="<?= $c['hex'] ?>" style="width:50px;height:40px;padding:2px;cursor:pointer;">
                                </div>
                                <div style="margin-top:8px;display:flex;align-items:center;gap:8px;">
                                    <span class="color-preview-dot" style="width:24px;height:24px;border-radius:50%;background:<?= $c['hex'] ?>;border:2px solid #ddd;display:inline-block;flex-shrink:0;"></span>
                                    <input type="text" class="color-name-edit" value="<?= clean($c['name']) ?>" placeholder="Edit color name..." style="flex:1;padding:6px 10px;border:1px solid #ddd;border-radius:6px;font-size:13px;">
                                    <button type="button" class="btn-edit-name" title="Edit name" style="background:none;border:none;cursor:pointer;font-size:14px;color:#888;">✏️</button>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Color Image (optional)</label>
                            <div class="dropzone dropzone-mini color-image-dropzone">
                                <input type="file" name="color_image[]" accept="image/*" style="display:none;">
                                <input type="hidden" name="color_image_existing[]" value="<?= $c['image'] ?? '' ?>">
                                <div class="dropzone-icon"><i class="fa-solid fa-cloud-arrow-up"></i></div>
                                <div class="dropzone-text">Drop image or <strong>click</strong></div>
                                <div class="dropzone-preview">
                                    <?php if (!empty($c['image'])): ?>
                                    <div class="dropzone-preview-item">
                                        <img src="<?= $base ?>/uploads/<?= $c['image'] ?>" alt="">
                                        <button type="button" class="remove-preview" onclick="removeColorImagePreview(this)">&times;</button>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <!-- Storage for this color -->
                        <div style="margin-top:10px;padding:10px;background:#fff;border-radius:8px;border:1px solid #eee;">
                            <strong style="font-size:14px;">Storage / RAM Options & Prices for this color:</strong>
                            <div class="storage-list" style="margin-top:8px;">
                                <?php if (!empty($c['storage'])):
                                    foreach ($c['storage'] as $si => $s): ?>
                                <div class="storage-row" style="display:flex;gap:8px;align-items:center;margin-bottom:6px;">
                                    <input type="text" name="storage_label[<?= $ci ?>][]" value="<?= clean($s['label']) ?>" placeholder="e.g. 12GB + 256GB" style="flex:2;">
                                    <input type="text" name="storage_price[<?= $ci ?>][]" value="<?= $s['price'] ?>" placeholder="e.g. RM 5,299" style="flex:1;">
                                    <input type="text" name="storage_sale[<?= $ci ?>][]" value="<?= $s['sale'] ?? '' ?>" placeholder="Sale (optional)" style="flex:1;">
                                    <button type="button" class="remove-storage" style="background:none;border:none;color:red;cursor:pointer;font-size:18px;">&times;</button>
                                </div>
                                <?php endforeach;
                                endif; ?>
                            </div>
                            <button type="button" class="btn btn-outline btn-sm add-storage" style="margin-top:6px;font-size:12px;">+ Add Storage</button>
                        </div>
                    </div>
                    <?php endforeach;
                    endif; ?>
                </div>
                <div style="display:flex;gap:10px;margin-bottom:20px;flex-wrap:wrap;">
                    <button type="button" id="addColorBtn" class="btn btn-outline">+ Add Color Variant</button>
                    <div style="display:flex;gap:8px;align-items:center;">
                        <select id="copyColorsSelect" class="sort-select" style="padding:10px 15px;min-width:220px;">
                            <option value="">-- Copy colors from --</option>
                            <?php
                            $lastBrand2 = '';
                            foreach ($allProducts as $ap):
                                if ($ap->brand !== $lastBrand2):
                                    if ($lastBrand2) echo '</optgroup>';
                                    echo '<optgroup label="' . clean($ap->brand) . '">';
                                    $lastBrand2 = $ap->brand;
                                endif;
                            ?>
                                <option value="<?= $ap->id ?>" <?= $editing && $ap->id == $id ? 'disabled' : '' ?>><?= clean($ap->name) ?></option>
                            <?php endforeach;
                            if ($lastBrand2) echo '</optgroup>'; ?>
                        </select>
                        <button type="button" id="copyColorsBtn" class="btn btn-primary btn-sm" style="white-space:nowrap;padding:10px 18px;"><i class="fa-solid fa-palette"></i> Copy Colors</button>
                    </div>
                </div>

                <!-- Band Color Variants (Watch only) -->
                <div id="bandColorSection" style="display:none;">
                    <h3 style="margin:25px 0 15px;border-bottom:2px solid #eee;padding-bottom:10px;">Band Color Variants</h3>
                    <div id="bandColorsContainer">
                        <?php if (!empty($existingBandColors)):
                            foreach ($existingBandColors as $bi => $bc): ?>
                        <div class="band-color-block" style="border:1px solid #ddd;border-radius:10px;padding:15px;margin-bottom:15px;background:#fafafa;">
                            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;">
                                <strong>Band Color #<?= $bi + 1 ?></strong>
                                <button type="button" class="btn btn-outline btn-sm remove-band-color" style="color:red;border-color:red;">Remove</button>
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label>Pick a Band Color</label>
                                    <select class="band-color-preset-select sort-select" style="width:100%;padding:12px;" onchange="applyBandColorPreset(this)">
                                        <option value="">-- Choose a color --</option>
                                        <option value="#1a1a1a|Black" <?= ($bc['hex'] ?? '') === '#1a1a1a' ? 'selected' : '' ?>>⬛ Black</option>
                                        <option value="#ffffff|White" <?= ($bc['hex'] ?? '') === '#ffffff' ? 'selected' : '' ?>>⬜ White</option>
                                        <option value="#e5e4e2|Silver" <?= ($bc['hex'] ?? '') === '#e5e4e2' ? 'selected' : '' ?>>🩶 Silver</option>
                                        <option value="#7D7C78|Gray" <?= ($bc['hex'] ?? '') === '#7D7C78' ? 'selected' : '' ?>>🩶 Gray</option>
                                        <option value="#d2b48c|Beige / Gold" <?= ($bc['hex'] ?? '') === '#d2b48c' ? 'selected' : '' ?>>🟡 Beige / Gold</option>
                                        <option value="#4169e1|Blue" <?= ($bc['hex'] ?? '') === '#4169e1' ? 'selected' : '' ?>>🔵 Blue</option>
                                        <option value="#1b2a4a|Navy" <?= ($bc['hex'] ?? '') === '#1b2a4a' ? 'selected' : '' ?>>🔵 Navy</option>
                                        <option value="#87ceeb|Light Blue" <?= ($bc['hex'] ?? '') === '#87ceeb' ? 'selected' : '' ?>>🩵 Light Blue</option>
                                        <option value="#2e8b57|Green" <?= ($bc['hex'] ?? '') === '#2e8b57' ? 'selected' : '' ?>>🟢 Green</option>
                                        <option value="#6b4c8a|Purple" <?= ($bc['hex'] ?? '') === '#6b4c8a' ? 'selected' : '' ?>>🟣 Purple</option>
                                        <option value="#f5c6d0|Pink" <?= ($bc['hex'] ?? '') === '#f5c6d0' ? 'selected' : '' ?>>🩷 Pink</option>
                                        <option value="#ff3b30|Red" <?= ($bc['hex'] ?? '') === '#ff3b30' ? 'selected' : '' ?>>🔴 Red</option>
                                        <option value="#ff6700|Orange" <?= ($bc['hex'] ?? '') === '#ff6700' ? 'selected' : '' ?>>🟠 Orange</option>
                                        <option value="#8b4513|Brown" <?= ($bc['hex'] ?? '') === '#8b4513' ? 'selected' : '' ?>>🟤 Brown</option>
                                        <option value="#cd853f|Tan / Leather" <?= ($bc['hex'] ?? '') === '#cd853f' ? 'selected' : '' ?>>🟤 Tan / Leather</option>
                                        <option value="custom|Custom...">🎨 Custom...</option>
                                    </select>
                                    <input type="hidden" name="band_color_hex[]" value="<?= $bc['hex'] ?? '#000000' ?>">
                                    <input type="hidden" name="band_color_name[]" value="<?= clean($bc['name'] ?? '') ?>">
                                    <div class="band-custom-color-row" style="display:none;margin-top:8px;gap:8px;align-items:center;">
                                        <input type="color" class="band-custom-color-picker" value="<?= $bc['hex'] ?? '#000000' ?>" style="width:50px;height:40px;padding:2px;cursor:pointer;">
                                    </div>
                                    <div style="margin-top:8px;display:flex;align-items:center;gap:8px;">
                                        <span class="band-color-preview-dot" style="width:24px;height:24px;border-radius:50%;background:<?= $bc['hex'] ?? '#ccc' ?>;border:2px solid #ddd;display:inline-block;flex-shrink:0;"></span>
                                        <input type="text" class="band-color-name-edit" value="<?= clean($bc['name'] ?? '') ?>" placeholder="Pick a color above..." style="flex:1;padding:6px 10px;border:1px solid #ddd;border-radius:6px;font-size:13px;">
                                    </div>
                                </div>
                                <div class="form-group" style="margin-top:10px;">
                                    <label>Band Image (optional)</label>
                                    <div class="dropzone dropzone-mini band-color-image-dropzone">
                                        <input type="file" name="band_color_image[]" accept="image/*" style="display:none;">
                                        <input type="hidden" name="band_color_image_existing[]" value="<?= $bc['image'] ?? '' ?>">
                                        <div class="dropzone-icon"><i class="fa-solid fa-cloud-arrow-up"></i></div>
                                        <div class="dropzone-text">Drop image or <strong>click</strong></div>
                                        <div class="dropzone-preview">
                                            <?php if (!empty($bc['image'])): ?>
                                            <div class="dropzone-preview-item">
                                                <img src="<?= $base ?>/uploads/<?= $bc['image'] ?>" alt="">
                                                <button type="button" class="dropzone-remove" onclick="removeBandColorImagePreview(this)">×</button>
                                            </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach;
                        endif; ?>
                    </div>
                    <button type="button" id="addBandColorBtn" class="btn btn-outline" style="margin-bottom:20px;">+ Add Band Color</button>
                </div>

                <div class="form-row" style="margin-top:20px;">
                    <button type="submit" class="btn btn-primary"><?= $editing ? 'Update' : 'Create' ?> Product</button>
                    <a href="<?= $base ?>/admin/products.php" class="btn btn-outline">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</section>

<script>
// === Copy Specs from existing product ===
var allProductSpecs = {
    <?php foreach ($allProducts as $ap):
        $apSpecs = json_decode($ap->specifications ?? '{}', true) ?: [];
        unset($apSpecs['band_colors']);
    ?>
    <?= $ap->id ?>: <?= json_encode($apSpecs) ?>,
    <?php endforeach; ?>
};

var allProductColors = {
    <?php foreach ($allProducts as $ap):
        $apColors = json_decode($ap->colors ?? '[]', true) ?: [];
    ?>
    <?= $ap->id ?>: <?= json_encode($apColors) ?>,
    <?php endforeach; ?>
};

var specFieldNames = [
    'display_size', 'display_resolution', 'display_technology',
    'sub_display_size', 'sub_display_resolution', 'sub_display_technology',
    'rear_camera', 'front_camera', 'rear_camera_fnumber', 'video_resolution',
    'chipset', 'battery_capacity', 'cpu_speed', 'cpu_type',
    'memory_gb', 'storage_gb', 'dimensions', 'weight', 'sim_count', 'os', 'connectivity',
    'audio_driver_size', 'audio_type', 'audio_frequency_response', 'audio_impedance',
    'audio_sensitivity', 'audio_anc', 'audio_battery_life', 'audio_charging_time',
    'audio_bluetooth', 'audio_codec', 'audio_water_resistance', 'audio_microphone',
    'audio_noise_cancelling', 'audio_controls', 'audio_cable_length',
    'console_cpu', 'console_gpu', 'console_memory', 'console_storage_type',
    'console_optical_drive', 'console_max_resolution', 'console_frame_rate',
    'console_ray_tracing', 'console_hdr', 'console_audio_output',
    'console_usb_ports', 'console_hdmi', 'console_wifi', 'console_bluetooth',
    'console_backwards_compat', 'console_power_consumption'
];

document.getElementById('copySpecsBtn').addEventListener('click', function() {
    var select = document.getElementById('copySpecsSelect');
    var id = select.value;
    if (!id) {
        alert('Please select a product first.');
        return;
    }
    var specs = allProductSpecs[id];
    if (!specs) {
        alert('No specs found for this product.');
        return;
    }
    specFieldNames.forEach(function(field) {
        var input = document.querySelector('[name="spec_' + field + '"]');
        if (input) {
            if (input.tagName === 'SELECT') {
                // For select fields like connectivity
                var opts = input.options;
                for (var i = 0; i < opts.length; i++) {
                    if (opts[i].value === (specs[field] || '')) {
                        input.selectedIndex = i;
                        break;
                    }
                }
            } else {
                input.value = specs[field] || '';
            }
        }
    });
    // Flash effect to show it worked
    var fields = document.querySelectorAll('[name^="spec_"]');
    fields.forEach(function(f) {
        if (f.value) {
            f.style.transition = 'background 0.3s';
            f.style.background = '#d4edda';
            setTimeout(function() { f.style.background = ''; }, 1000);
        }
    });
});

// Copy Colors & Storage from another product
document.getElementById('copyColorsBtn').addEventListener('click', function() {
    var select = document.getElementById('copyColorsSelect');
    var id = select.value;
    if (!id) {
        alert('Please select a product first.');
        return;
    }
    var colors = allProductColors[id];
    if (!colors || !colors.length) {
        alert('No color variants found for this product.');
        return;
    }
    if (!confirm('This will replace all current color variants. Continue?')) return;

    // Clear existing colors
    document.getElementById('colorsContainer').innerHTML = '';
    colorIndex = 0;

    colors.forEach(function(c) {
        var ci = colorIndex++;
        var block = createColorBlock(ci);
        document.getElementById('colorsContainer').insertAdjacentHTML('beforeend', block);

        var lastBlock = document.getElementById('colorsContainer').lastElementChild;

        // Set color hex and name
        lastBlock.querySelector('[name="color_hex[]"]').value = c.hex || '#000000';
        lastBlock.querySelector('[name="color_name[]"]').value = c.name || '';

        // Update preview dot
        var dot = lastBlock.querySelector('.color-preview-dot');
        if (dot) dot.style.background = c.hex || '#ccc';

        // Update name edit field
        var nameEdit = lastBlock.querySelector('.color-name-edit');
        if (nameEdit) { nameEdit.value = c.name || ''; nameEdit.readOnly = false; }

        // Try to match preset dropdown
        var presetSelect = lastBlock.querySelector('.color-preset-select');
        if (presetSelect) {
            var matched = false;
            for (var i = 0; i < presetSelect.options.length; i++) {
                var parts = presetSelect.options[i].value.split('|');
                if (parts[0] && parts[0].toLowerCase() === (c.hex || '').toLowerCase()) {
                    presetSelect.selectedIndex = i;
                    matched = true;
                    break;
                }
            }
            if (!matched) {
                // Select "Custom"
                for (var i = 0; i < presetSelect.options.length; i++) {
                    if (presetSelect.options[i].value.startsWith('custom')) {
                        presetSelect.selectedIndex = i;
                        break;
                    }
                }
                var customRow = lastBlock.querySelector('.custom-color-row');
                if (customRow) {
                    customRow.style.display = 'flex';
                    var picker = customRow.querySelector('.custom-color-picker');
                    if (picker) picker.value = c.hex || '#000000';
                }
            }
        }

        // Populate storage rows
        if (c.storage && c.storage.length) {
            var storageList = lastBlock.querySelector('.storage-list');
            storageList.innerHTML = '';
            c.storage.forEach(function(s) {
                var row = document.createElement('div');
                row.className = 'storage-row';
                row.style.cssText = 'display:flex;gap:8px;align-items:center;margin-bottom:6px;';
                row.innerHTML = '<input type="text" name="storage_label[' + ci + '][]" value="' + (s.label || '') + '" placeholder="e.g. 12GB + 256GB" style="flex:2;">' +
                    '<input type="text" name="storage_price[' + ci + '][]" value="' + (s.price || '') + '" placeholder="e.g. RM 5,299" style="flex:1;">' +
                    '<input type="text" name="storage_sale[' + ci + '][]" value="' + (s.sale || '') + '" placeholder="Sale (optional)" style="flex:1;">' +
                    '<button type="button" class="remove-storage" style="background:none;border:none;color:red;cursor:pointer;font-size:18px;">&times;</button>';
                storageList.appendChild(row);
            });
        }
    });

    // Flash effect
    var blocks = document.querySelectorAll('.color-block');
    blocks.forEach(function(b) {
        b.style.transition = 'background 0.3s';
        b.style.background = '#d4edda';
        setTimeout(function() { b.style.background = '#fafafa'; }, 1200);
    });

    // Re-init dropzones for copied color blocks
    setTimeout(function() {
        document.querySelectorAll('.color-image-dropzone').forEach(function(z) {
            if (!z._dropzoneInit) { initColorImageDropzone(z); z._dropzoneInit = true; }
        });
    }, 100);
});

var colorIndex = <?= max(count($existingColors), 0) ?>;

var colorOptions = `
    <option value="">-- Choose a color --</option>
    <option value="#1a1a1a|Black">⬛ Black</option>
    <option value="#ffffff|White">⬜ White</option>
    <option value="#e5e4e2|Silver">🩶 Silver</option>
    <option value="#7D7C78|Gray">🩶 Gray</option>
    <option value="#3C3D3A|Titanium Black">⬛ Titanium Black</option>
    <option value="#7D7C78|Titanium Gray">🩶 Titanium Gray</option>
    <option value="#8999AD|Titanium Blue">🔵 Titanium Blue</option>
    <option value="#E5E4E2|Titanium White">⬜ Titanium White</option>
    <option value="#d2b48c|Beige / Gold">🟡 Beige / Gold</option>
    <option value="#e8c87a|Light Gold">🟡 Light Gold</option>
    <option value="#daa520|Amber Yellow">🟡 Amber Yellow</option>
    <option value="#4169e1|Blue">🔵 Blue</option>
    <option value="#1b2a4a|Navy / Dark Blue">🔵 Navy</option>
    <option value="#87ceeb|Light Blue / Ice Blue">🩵 Light Blue</option>
    <option value="#5b8cbf|Sky Blue">🩵 Sky Blue</option>
    <option value="#2e8b57|Green">🟢 Green</option>
    <option value="#5a7a5a|Natural Green">🟢 Natural Green</option>
    <option value="#98ff98|Mint">🟢 Mint</option>
    <option value="#6b4c8a|Purple">🟣 Purple</option>
    <option value="#6a5acd|Cobalt Violet">🟣 Cobalt Violet</option>
    <option value="#c8a2c8|Lilac / Lavender">🟣 Lilac / Lavender</option>
    <option value="#9a7fb8|Thin Purple">🟣 Thin Purple</option>
    <option value="#f5c6d0|Pink">🩷 Pink</option>
    <option value="#ff3b30|Red">🔴 Red</option>
    <option value="#ffd700|Yellow">🟡 Yellow</option>
    <option value="#fffdd0|Cream">🟡 Cream</option>
    <option value="#ff6700|Orange">🟠 Orange</option>
    <option value="#c8a85a|Vibrant Gold">🟡 Vibrant Gold</option>
    <option value="custom|Custom...">🎨 Custom...</option>
`;

function createColorBlock(ci) {
    return `
    <div class="color-block" style="border:1px solid #ddd;border-radius:10px;padding:15px;margin-bottom:15px;background:#fafafa;">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;">
            <strong>Color #${ci + 1}</strong>
            <button type="button" class="btn btn-outline btn-sm remove-color" style="color:red;border-color:red;">Remove</button>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label>Pick a Color</label>
                <select class="color-preset-select sort-select" style="width:100%;padding:12px;" onchange="applyColorPreset(this)">
                    ${colorOptions}
                </select>
                <input type="hidden" name="color_hex[]" value="#000000">
                <input type="hidden" name="color_name[]" value="">
                <div class="custom-color-row" style="display:none;margin-top:8px;gap:8px;align-items:center;">
                    <input type="color" class="custom-color-picker" value="#000000" style="width:50px;height:40px;padding:2px;cursor:pointer;">
                </div>
                <div style="margin-top:8px;display:flex;align-items:center;gap:8px;">
                    <span class="color-preview-dot" style="width:24px;height:24px;border-radius:50%;background:#ccc;border:2px solid #ddd;display:inline-block;flex-shrink:0;"></span>
                    <input type="text" class="color-name-edit" value="" placeholder="Pick a color above..." style="flex:1;padding:6px 10px;border:1px solid #ddd;border-radius:6px;font-size:13px;" readonly>
                    <button type="button" class="btn-edit-name" title="Edit name" style="background:none;border:none;cursor:pointer;font-size:14px;color:#888;">✏️</button>
                </div>
            </div>
        </div>
        <div class="form-group">
            <label>Color Image (optional)</label>
            <div class="dropzone dropzone-mini color-image-dropzone">
                <input type="file" name="color_image[]" accept="image/*" style="display:none;">
                <input type="hidden" name="color_image_existing[]" value="">
                <div class="dropzone-icon"><i class="fa-solid fa-cloud-arrow-up"></i></div>
                <div class="dropzone-text">Drop image or <strong>click</strong></div>
                <div class="dropzone-preview"></div>
            </div>
        </div>
        <div style="margin-top:10px;padding:10px;background:#fff;border-radius:8px;border:1px solid #eee;">
            <strong style="font-size:14px;">Storage / RAM Options & Prices for this color:</strong>
            <div class="storage-list" style="margin-top:8px;">
                <div class="storage-row" style="display:flex;gap:8px;align-items:center;margin-bottom:6px;">
                    <input type="text" name="storage_label[${ci}][]" placeholder="e.g. 12GB + 256GB" style="flex:2;">
                    <input type="text" name="storage_price[${ci}][]" placeholder="e.g. RM 5,299" style="flex:1;">
                    <input type="text" name="storage_sale[${ci}][]" placeholder="Sale (optional)" style="flex:1;">
                    <button type="button" class="remove-storage" style="background:none;border:none;color:red;cursor:pointer;font-size:18px;">&times;</button>
                </div>
            </div>
            <button type="button" class="btn btn-outline btn-sm add-storage" style="margin-top:6px;font-size:12px;">+ Add Storage</button>
        </div>
    </div>`;
}

// Auto-detect color name from hex
var colorNameMap = [
    {hex:'#000000',name:'Black'},{hex:'#1a1a1a',name:'Black'},{hex:'#333333',name:'Dark Gray'},
    {hex:'#555555',name:'Gray'},{hex:'#7D7C78',name:'Gray'},{hex:'#808080',name:'Gray'},
    {hex:'#a9a9a9',name:'Dark Silver'},{hex:'#c0c0c0',name:'Silver'},{hex:'#e5e4e2',name:'Silver'},
    {hex:'#d3d3d3',name:'Light Gray'},{hex:'#f5f5f5',name:'Off White'},{hex:'#ffffff',name:'White'},
    {hex:'#3C3D3A',name:'Titanium Black'},{hex:'#8999AD',name:'Titanium Blue'},
    {hex:'#d2b48c',name:'Beige'},{hex:'#daa520',name:'Gold'},{hex:'#c8a85a',name:'Gold'},
    {hex:'#ffd700',name:'Yellow Gold'},{hex:'#f5f5dc',name:'Ivory'},
    {hex:'#ff0000',name:'Red'},{hex:'#ff3b30',name:'Red'},{hex:'#cc0000',name:'Dark Red'},
    {hex:'#ff6347',name:'Coral Red'},{hex:'#dc143c',name:'Crimson'},
    {hex:'#ff6700',name:'Orange'},{hex:'#ff8c00',name:'Dark Orange'},{hex:'#ffa500',name:'Orange'},
    {hex:'#f47920',name:'Tangerine'},{hex:'#ff4500',name:'Red Orange'},
    {hex:'#ffff00',name:'Yellow'},{hex:'#fffdd0',name:'Cream'},{hex:'#ffe4b5',name:'Moccasin'},
    {hex:'#2e8b57',name:'Green'},{hex:'#008000',name:'Green'},{hex:'#006400',name:'Dark Green'},
    {hex:'#5a7a5a',name:'Natural Green'},{hex:'#90ee90',name:'Light Green'},{hex:'#98ff98',name:'Mint'},
    {hex:'#00ff7f',name:'Spring Green'},{hex:'#20b2aa',name:'Teal'},
    {hex:'#0000ff',name:'Blue'},{hex:'#4169e1',name:'Royal Blue'},{hex:'#415fff',name:'Blue'},
    {hex:'#1e90ff',name:'Dodger Blue'},{hex:'#87ceeb',name:'Sky Blue'},{hex:'#a8c8e8',name:'Ice Blue'},
    {hex:'#5b8cbf',name:'Steel Blue'},{hex:'#1b2a4a',name:'Navy'},{hex:'#000080',name:'Navy'},
    {hex:'#191970',name:'Midnight Blue'},{hex:'#00bfff',name:'Deep Sky Blue'},
    {hex:'#6b4c8a',name:'Purple'},{hex:'#6a5acd',name:'Violet'},{hex:'#7e5f8a',name:'Purple'},
    {hex:'#c8a2c8',name:'Lilac'},{hex:'#9a7fb8',name:'Lavender'},{hex:'#800080',name:'Deep Purple'},
    {hex:'#ee82ee',name:'Violet'},{hex:'#da70d6',name:'Orchid'},{hex:'#dda0dd',name:'Plum'},
    {hex:'#ff69b4',name:'Hot Pink'},{hex:'#f5c6d0',name:'Pink'},{hex:'#ffc0cb',name:'Pink'},
    {hex:'#ff1493',name:'Deep Pink'},{hex:'#ffb6c1',name:'Light Pink'},
    {hex:'#8b4513',name:'Brown'},{hex:'#a0522d',name:'Sienna'},{hex:'#cd853f',name:'Peru'},
];

function hexToRgb(hex) {
    hex = hex.replace('#','');
    return {
        r: parseInt(hex.substring(0,2),16),
        g: parseInt(hex.substring(2,4),16),
        b: parseInt(hex.substring(4,6),16)
    };
}

function getColorName(hex) {
    var target = hexToRgb(hex);
    var closest = 'Custom Color';
    var minDist = Infinity;
    colorNameMap.forEach(function(c) {
        var rgb = hexToRgb(c.hex);
        var dist = Math.sqrt(
            Math.pow(target.r - rgb.r, 2) +
            Math.pow(target.g - rgb.g, 2) +
            Math.pow(target.b - rgb.b, 2)
        );
        if (dist < minDist) {
            minDist = dist;
            closest = c.name;
        }
    });
    return closest;
}

// Apply color from dropdown preset
function applyColorPreset(select) {
    var block = select.closest('.color-block') || select.closest('.form-group').parentElement.parentElement;
    var val = select.value;
    var hexInput = block.querySelector('input[name="color_hex[]"]');
    var nameInput = block.querySelector('input[name="color_name[]"]');
    var previewDot = block.querySelector('.color-preview-dot');
    var previewName = block.querySelector('.color-preview-name');
    var customRow = block.querySelector('.custom-color-row');

    var nameEdit = block.querySelector('.color-name-edit');

    if (val === 'custom') {
        // Show custom picker
        customRow.style.display = 'flex';
        var picker = customRow.querySelector('.custom-color-picker');
        picker.oninput = picker.onchange = function() {
            hexInput.value = picker.value;
            previewDot.style.background = picker.value;
            // Auto-detect color name
            var autoName = getColorName(picker.value);
            nameInput.value = autoName;
            nameEdit.value = autoName;
            nameEdit.removeAttribute('readonly');
        };
    } else if (val) {
        customRow.style.display = 'none';
        var parts = val.split('|');
        hexInput.value = parts[0];
        nameInput.value = parts[1];
        previewDot.style.background = parts[0];
        nameEdit.value = parts[1];
        nameEdit.removeAttribute('readonly');
    } else {
        customRow.style.display = 'none';
        hexInput.value = '#000000';
        nameInput.value = '';
        previewDot.style.background = '#ccc';
        nameEdit.value = '';
        nameEdit.setAttribute('readonly', true);
    }
}

// Sync editable name field to hidden input
document.addEventListener('input', function(e) {
    if (e.target.classList.contains('color-name-edit')) {
        var block = e.target.closest('.color-block');
        block.querySelector('input[name="color_name[]"]').value = e.target.value;
    }
});

// Edit name button - focus the name field
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('btn-edit-name') || e.target.closest('.btn-edit-name')) {
        var block = (e.target.closest('.btn-edit-name') || e.target).closest('.color-block');
        var nameEdit = block.querySelector('.color-name-edit');
        nameEdit.removeAttribute('readonly');
        nameEdit.focus();
        nameEdit.select();
    }
});

// Add new color
document.getElementById('addColorBtn').addEventListener('click', function() {
    var container = document.getElementById('colorsContainer');
    container.insertAdjacentHTML('beforeend', createColorBlock(colorIndex));
    colorIndex++;
    renumberColors();
});

// Remove color
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('remove-color')) {
        e.target.closest('.color-block').remove();
        renumberColors();
    }
});

// Add storage row within a color
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('add-storage')) {
        var block = e.target.closest('.color-block');
        var allBlocks = document.querySelectorAll('.color-block');
        var ci = Array.from(allBlocks).indexOf(block);
        var list = block.querySelector('.storage-list');
        var row = `<div class="storage-row" style="display:flex;gap:8px;align-items:center;margin-bottom:6px;">
            <input type="text" name="storage_label[${ci}][]" placeholder="e.g. 12GB + 256GB" style="flex:2;">
            <input type="text" name="storage_price[${ci}][]" placeholder="e.g. RM 5,299" style="flex:1;">
            <input type="text" name="storage_sale[${ci}][]" placeholder="Sale (optional)" style="flex:1;">
            <button type="button" class="remove-storage" style="background:none;border:none;color:red;cursor:pointer;font-size:18px;">&times;</button>
        </div>`;
        list.insertAdjacentHTML('beforeend', row);
    }
});

// Remove storage row
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('remove-storage')) {
        e.target.closest('.storage-row').remove();
    }
});

// Renumber storage input names after add/remove
function renumberColors() {
    var blocks = document.querySelectorAll('.color-block');
    blocks.forEach(function(block, ci) {
        block.querySelector('strong').textContent = 'Color #' + (ci + 1);
        block.querySelectorAll('.storage-row').forEach(function(row) {
            row.querySelectorAll('input[name^="storage_label"]').forEach(function(inp) {
                inp.name = 'storage_label[' + ci + '][]';
            });
            row.querySelectorAll('input[name^="storage_price"]').forEach(function(inp) {
                inp.name = 'storage_price[' + ci + '][]';
            });
            row.querySelectorAll('input[name^="storage_sale"]').forEach(function(inp) {
                inp.name = 'storage_sale[' + ci + '][]';
            });
        });
    });
}

// === Band Color logic (for watches) ===
var bandColorIndex = <?= max(count($existingBandColors), 0) ?>;

var bandColorOptions = `
    <option value="">-- Choose a color --</option>
    <option value="#1a1a1a|Black">⬛ Black</option>
    <option value="#ffffff|White">⬜ White</option>
    <option value="#e5e4e2|Silver">🩶 Silver</option>
    <option value="#7D7C78|Gray">🩶 Gray</option>
    <option value="#d2b48c|Beige / Gold">🟡 Beige / Gold</option>
    <option value="#4169e1|Blue">🔵 Blue</option>
    <option value="#1b2a4a|Navy">🔵 Navy</option>
    <option value="#87ceeb|Light Blue">🩵 Light Blue</option>
    <option value="#2e8b57|Green">🟢 Green</option>
    <option value="#6b4c8a|Purple">🟣 Purple</option>
    <option value="#f5c6d0|Pink">🩷 Pink</option>
    <option value="#ff3b30|Red">🔴 Red</option>
    <option value="#ff6700|Orange">🟠 Orange</option>
    <option value="#8b4513|Brown">🟤 Brown</option>
    <option value="#cd853f|Tan / Leather">🟤 Tan / Leather</option>
    <option value="custom|Custom...">🎨 Custom...</option>
`;

function createBandColorBlock(bi) {
    return `
    <div class="band-color-block" style="border:1px solid #ddd;border-radius:10px;padding:15px;margin-bottom:15px;background:#fafafa;">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;">
            <strong>Band Color #${bi + 1}</strong>
            <button type="button" class="btn btn-outline btn-sm remove-band-color" style="color:red;border-color:red;">Remove</button>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label>Pick a Band Color</label>
                <select class="band-color-preset-select sort-select" style="width:100%;padding:12px;" onchange="applyBandColorPreset(this)">
                    ${bandColorOptions}
                </select>
                <input type="hidden" name="band_color_hex[]" value="#000000">
                <input type="hidden" name="band_color_name[]" value="">
                <div class="band-custom-color-row" style="display:none;margin-top:8px;gap:8px;align-items:center;">
                    <input type="color" class="band-custom-color-picker" value="#000000" style="width:50px;height:40px;padding:2px;cursor:pointer;">
                </div>
                <div style="margin-top:8px;display:flex;align-items:center;gap:8px;">
                    <span class="band-color-preview-dot" style="width:24px;height:24px;border-radius:50%;background:#ccc;border:2px solid #ddd;display:inline-block;flex-shrink:0;"></span>
                    <input type="text" class="band-color-name-edit" value="" placeholder="Pick a color above..." style="flex:1;padding:6px 10px;border:1px solid #ddd;border-radius:6px;font-size:13px;">
                </div>
            </div>
            <div class="form-group" style="margin-top:10px;">
                <label>Band Image (optional)</label>
                <div class="dropzone dropzone-mini band-color-image-dropzone">
                    <input type="file" name="band_color_image[]" accept="image/*" style="display:none;">
                    <input type="hidden" name="band_color_image_existing[]" value="">
                    <div class="dropzone-icon"><i class="fa-solid fa-cloud-arrow-up"></i></div>
                    <div class="dropzone-text">Drop image or <strong>click</strong></div>
                    <div class="dropzone-preview"></div>
                </div>
            </div>
        </div>
    </div>`;
}

function applyBandColorPreset(select) {
    var block = select.closest('.band-color-block');
    var val = select.value;
    var hexInput = block.querySelector('input[name="band_color_hex[]"]');
    var nameInput = block.querySelector('input[name="band_color_name[]"]');
    var previewDot = block.querySelector('.band-color-preview-dot');
    var customRow = block.querySelector('.band-custom-color-row');
    var nameEdit = block.querySelector('.band-color-name-edit');

    if (val === 'custom') {
        customRow.style.display = 'flex';
        var picker = customRow.querySelector('.band-custom-color-picker');
        picker.oninput = picker.onchange = function() {
            hexInput.value = picker.value;
            previewDot.style.background = picker.value;
            var autoName = getColorName(picker.value);
            nameInput.value = autoName;
            nameEdit.value = autoName;
        };
    } else if (val) {
        customRow.style.display = 'none';
        var parts = val.split('|');
        hexInput.value = parts[0];
        nameInput.value = parts[1];
        previewDot.style.background = parts[0];
        nameEdit.value = parts[1];
    } else {
        customRow.style.display = 'none';
        hexInput.value = '#000000';
        nameInput.value = '';
        previewDot.style.background = '#ccc';
        nameEdit.value = '';
    }
}

// Sync band color name edit
document.addEventListener('input', function(e) {
    if (e.target.classList.contains('band-color-name-edit')) {
        var block = e.target.closest('.band-color-block');
        block.querySelector('input[name="band_color_name[]"]').value = e.target.value;
    }
});

// Add band color
document.getElementById('addBandColorBtn').addEventListener('click', function() {
    var container = document.getElementById('bandColorsContainer');
    container.insertAdjacentHTML('beforeend', createBandColorBlock(bandColorIndex));
    bandColorIndex++;
    renumberBandColors();
    var newBlock = container.lastElementChild;
    var dropzone = newBlock.querySelector('.band-color-image-dropzone');
    if (dropzone) initBandColorImageDropzone(dropzone);
});

// Remove band color
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('remove-band-color')) {
        e.target.closest('.band-color-block').remove();
        renumberBandColors();
    }
});

function renumberBandColors() {
    var blocks = document.querySelectorAll('.band-color-block');
    blocks.forEach(function(block, bi) {
        block.querySelector('strong').textContent = 'Band Color #' + (bi + 1);
    });
}

// Show/hide sections based on category
function toggleCategorySections() {
    var catSelect = document.querySelector('select[name="category"]');
    var cat = catSelect.value;
    var bandSection = document.getElementById('bandColorSection');
    var colorTitle = document.getElementById('colorVariantsTitle');
    var defaultSection = document.getElementById('defaultSpecsSection');
    var audioSection = document.getElementById('audioSpecsSection');
    var consoleSection = document.getElementById('consoleSpecsSection');

    // Band color (watch only)
    if (cat === 'watch') {
        bandSection.style.display = 'block';
        colorTitle.textContent = 'Case Color Variants';
    } else {
        bandSection.style.display = 'none';
        colorTitle.textContent = 'Color Variants';
    }

    // Default specs (hide for audio & console)
    defaultSection.style.display = (cat === 'audio' || cat === 'console') ? 'none' : 'block';
    // Audio specs
    audioSection.style.display = cat === 'audio' ? 'block' : 'none';
    // Console specs
    consoleSection.style.display = cat === 'console' ? 'block' : 'none';
}
document.querySelector('select[name="category"]').addEventListener('change', toggleCategorySections);
toggleCategorySections();

// === Drag & Drop Helpers ===
function setupDropzone(zone, fileInput, onFiles) {
    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(function(evt) {
        zone.addEventListener(evt, function(e) { e.preventDefault(); e.stopPropagation(); });
    });
    zone.addEventListener('dragenter', function() { zone.classList.add('dragover'); });
    zone.addEventListener('dragover', function() { zone.classList.add('dragover'); });
    zone.addEventListener('dragleave', function() { zone.classList.remove('dragover'); });
    zone.addEventListener('drop', function(e) {
        zone.classList.remove('dragover');
        var files = e.dataTransfer.files;
        if (files.length) onFiles(files);
    });
    zone.addEventListener('click', function(e) {
        if (e.target.closest('.remove-preview') || e.target.closest('.remove-gallery-new')) return;
        fileInput.click();
    });
    fileInput.addEventListener('change', function() {
        if (this.files.length) onFiles(this.files);
        this.value = '';
    });
}

function previewFile(file, callback) {
    var reader = new FileReader();
    reader.onload = function(e) { callback(e.target.result); };
    reader.readAsDataURL(file);
}

// === Main Product Photo Drop Zone ===
(function() {
    var zone = document.getElementById('photoDropzone');
    var input = document.getElementById('photoInput');
    var preview = document.getElementById('photoPreview');

    setupDropzone(zone, input, function(files) {
        // Transfer the dropped file to the hidden input via DataTransfer
        var dt = new DataTransfer();
        dt.items.add(files[0]);
        input.files = dt.files;

        previewFile(files[0], function(src) {
            preview.innerHTML = '<div class="dropzone-preview-item">' +
                '<img src="' + src + '" alt="">' +
                '<button type="button" class="remove-preview" onclick="removePhotoPreview()">&times;</button>' +
                '<small class="preview-name">' + files[0].name + '</small></div>';
        });
    });
})();

function removePhotoPreview() {
    document.getElementById('photoPreview').innerHTML = '';
    var input = document.getElementById('photoInput');
    input.value = '';
}

// === Gallery Drop Zone ===
var galleryCount = 0;
(function() {
    var zone = document.getElementById('galleryDropzone');
    var hiddenInput = document.createElement('input');
    hiddenInput.type = 'file';
    hiddenInput.accept = 'image/*';
    hiddenInput.multiple = true;
    hiddenInput.style.display = 'none';
    zone.appendChild(hiddenInput);

    setupDropzone(zone, hiddenInput, function(files) {
        Array.from(files).forEach(function(file) {
            var id = galleryCount++;
            // Create a hidden file input for each file
            var fileInput = document.createElement('input');
            fileInput.type = 'file';
            fileInput.name = 'gallery[]';
            fileInput.accept = 'image/*';
            fileInput.style.display = 'none';
            fileInput.setAttribute('data-gallery-id', id);

            var dt = new DataTransfer();
            dt.items.add(file);
            fileInput.files = dt.files;
            document.getElementById('galleryInputs').appendChild(fileInput);

            previewFile(file, function(src) {
                var div = document.createElement('div');
                div.className = 'dropzone-preview-item';
                div.setAttribute('data-gallery-id', id);
                div.innerHTML = '<img src="' + src + '" alt="">' +
                    '<button type="button" class="remove-gallery-new" data-gallery-id="' + id + '">&times;</button>' +
                    '<small class="preview-name">' + file.name + '</small>';
                document.getElementById('galleryPreview').appendChild(div);
            });
        });
    });
})();

// Remove a newly added gallery image
document.addEventListener('click', function(e) {
    var btn = e.target.closest('.remove-gallery-new');
    if (btn) {
        var id = btn.getAttribute('data-gallery-id');
        var preview = document.querySelector('#galleryPreview .dropzone-preview-item[data-gallery-id="' + id + '"]');
        if (preview) preview.remove();
        var input = document.querySelector('#galleryInputs input[data-gallery-id="' + id + '"]');
        if (input) input.remove();
    }
});

// === Color Image Drop Zones ===
function initColorImageDropzone(zone) {
    var fileInput = zone.querySelector('input[type="file"]');
    var previewArea = zone.querySelector('.dropzone-preview');

    setupDropzone(zone, fileInput, function(files) {
        var dt = new DataTransfer();
        dt.items.add(files[0]);
        fileInput.files = dt.files;
        // Clear existing image reference
        var existingInput = zone.querySelector('input[name="color_image_existing[]"]');
        if (existingInput) existingInput.value = '';

        previewFile(files[0], function(src) {
            previewArea.innerHTML = '<div class="dropzone-preview-item">' +
                '<img src="' + src + '" alt="">' +
                '<button type="button" class="remove-preview" onclick="removeColorImagePreview(this)">&times;</button>' +
                '<small class="preview-name">' + files[0].name + '</small></div>';
        });
    });
}

function removeColorImagePreview(btn) {
    var zone = btn.closest('.color-image-dropzone');
    zone.querySelector('.dropzone-preview').innerHTML = '';
    var fileInput = zone.querySelector('input[type="file"]');
    fileInput.value = '';
    var existingInput = zone.querySelector('input[name="color_image_existing[]"]');
    if (existingInput) existingInput.value = '';
}

// Init all existing color image dropzones
document.querySelectorAll('.color-image-dropzone').forEach(initColorImageDropzone);

// === Band Color Image Drop Zones ===
function initBandColorImageDropzone(zone) {
    var fileInput = zone.querySelector('input[type="file"]');
    var previewArea = zone.querySelector('.dropzone-preview');

    setupDropzone(zone, fileInput, function(files) {
        var dt = new DataTransfer();
        dt.items.add(files[0]);
        fileInput.files = dt.files;
        var existingInput = zone.querySelector('input[name="band_color_image_existing[]"]');
        if (existingInput) existingInput.value = '';

        previewFile(files[0], function(src) {
            previewArea.innerHTML = '<div class="dropzone-preview-item">' +
                '<img src="' + src + '" alt="">' +
                '<button type="button" class="dropzone-remove" onclick="removeBandColorImagePreview(this)">×</button>' +
                '</div>';
        });
    });
}

function removeBandColorImagePreview(btn) {
    var zone = btn.closest('.band-color-image-dropzone');
    zone.querySelector('.dropzone-preview').innerHTML = '';
    var fileInput = zone.querySelector('input[type="file"]');
    fileInput.value = '';
    var existingInput = zone.querySelector('input[name="band_color_image_existing[]"]');
    if (existingInput) existingInput.value = '';
}

document.querySelectorAll('.band-color-image-dropzone').forEach(initBandColorImageDropzone);

// Re-init after adding a new color block
var origAddColorClick = document.getElementById('addColorBtn').onclick;
var addColorBtn = document.getElementById('addColorBtn');
addColorBtn.addEventListener('click', function() {
    setTimeout(function() {
        var blocks = document.querySelectorAll('.color-image-dropzone');
        var lastZone = blocks[blocks.length - 1];
        if (lastZone && !lastZone._dropzoneInit) {
            initColorImageDropzone(lastZone);
            lastZone._dropzoneInit = true;
        }
    }, 50);
});
</script>

<?php include '../_foot.php'; ?>
