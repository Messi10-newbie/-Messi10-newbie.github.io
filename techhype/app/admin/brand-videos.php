<?php
ini_set('upload_max_filesize', '100M');
ini_set('post_max_size', '100M');
ini_set('max_execution_time', '300');

include '../_base.php';
require_admin();

$brands = ['Samsung','Apple','Vivo','Oppo','Xiaomi','Nothing','Google','Sony','iQOO'];
$videoDir = __DIR__ . '/../videos/';

if (!is_dir($videoDir)) mkdir($videoDir, 0777, true);

// Handle upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['brand'])) {
    $brand = clean($_POST['brand']);
    $filename = strtolower($brand) . '.mp4';

    if (isset($_FILES['video']) && $_FILES['video']['size'] > 0) {
        $ext = strtolower(pathinfo($_FILES['video']['name'], PATHINFO_EXTENSION));
        if ($ext === 'mp4') {
            // Remove old video if exists
            if (file_exists($videoDir . $filename)) {
                unlink($videoDir . $filename);
            }
            move_uploaded_file($_FILES['video']['tmp_name'], $videoDir . $filename);
            flash('success', "$brand video uploaded successfully!");
        } else {
            flash('error', 'Only MP4 files are allowed.');
        }
    } else {
        flash('error', 'Please select a video file.');
    }
    redirect('/admin/brand-videos.php');
}

// Handle delete
if (isset($_GET['delete'])) {
    $brand = clean($_GET['delete']);
    $filename = strtolower($brand) . '.mp4';
    if (file_exists($videoDir . $filename)) {
        unlink($videoDir . $filename);
        flash('success', "$brand video removed.");
    }
    redirect('/admin/brand-videos.php');
}

include '../_head.php';
?>

<section class="brand-header" style="background: #1d1d1f;">
    <div class="container">
        <h1><i class="fa-solid fa-video"></i> Brand Video Manager</h1>
        <p>Upload trailer videos for brand page headers</p>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="admin-nav">
            <a href="<?= $base ?>/admin/members.php">Members</a>
            <a href="<?= $base ?>/admin/products.php">Products</a>
            <a href="<?= $base ?>/admin/orders.php">Orders</a>
            <a href="<?= $base ?>/admin/brand-videos.php" class="active">Brand Videos</a>
            <a href="<?= $base ?>/admin/analytics.php">Analytics</a>
        </div>

        <!-- Upload Form -->
        <div style="background:#f9f9fb;border-radius:16px;padding:25px;margin-bottom:30px;">
            <h3 style="margin-bottom:15px;"><i class="fa-solid fa-upload"></i> Upload Brand Video</h3>
            <form method="POST" enctype="multipart/form-data" style="display:flex;gap:15px;align-items:flex-end;flex-wrap:wrap;">
                <div class="form-group" style="margin:0;min-width:200px;">
                    <label>Select Brand</label>
                    <select name="brand" class="sort-select" style="width:100%;padding:10px 15px;" required>
                        <option value="">-- Choose Brand --</option>
                        <?php foreach ($brands as $b): ?>
                        <option value="<?= $b ?>"><?= $b ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group" style="margin:0;flex:1;min-width:250px;">
                    <label>Video File (MP4 only)</label>
                    <input type="file" name="video" accept="video/mp4" required style="padding:8px;">
                </div>
                <button type="submit" class="btn btn-primary" style="height:42px;"><i class="fa-solid fa-cloud-arrow-up"></i> Upload</button>
            </form>
            <p style="font-size:12px;color:#888;margin-top:10px;">
                <i class="fa-solid fa-info-circle"></i> Recommended: Short trailer videos (15-60 seconds), under 50MB for best performance. Download trailers from YouTube using sites like y2mate.com
            </p>
        </div>

        <!-- Brand Videos List -->
        <table class="data-table">
            <thead>
                <tr>
                    <th>Brand</th>
                    <th>Video Status</th>
                    <th>File Size</th>
                    <th>Preview</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($brands as $b):
                    $filename = strtolower($b) . '.mp4';
                    $filepath = $videoDir . $filename;
                    $hasVideo = file_exists($filepath);
                    $fileSize = $hasVideo ? filesize($filepath) : 0;
                ?>
                <tr>
                    <td><strong><?= $b ?></strong></td>
                    <td>
                        <?php if ($hasVideo): ?>
                            <span class="status-badge status-active"><i class="fa-solid fa-check"></i> Uploaded</span>
                        <?php else: ?>
                            <span class="status-badge status-inactive"><i class="fa-solid fa-xmark"></i> No Video</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($hasVideo): ?>
                            <?php
                            if ($fileSize >= 1048576) {
                                echo round($fileSize / 1048576, 1) . ' MB';
                            } else {
                                echo round($fileSize / 1024) . ' KB';
                            }
                            ?>
                        <?php else: ?>
                            —
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($hasVideo): ?>
                            <video width="120" height="68" style="border-radius:6px;object-fit:cover;background:#000;" muted>
                                <source src="<?= $base ?>/videos/<?= $filename ?>" type="video/mp4">
                            </video>
                            <button onclick="this.previousElementSibling.play(); this.previousElementSibling.style.opacity=1;" class="btn btn-sm btn-outline" style="display:block;margin-top:4px;font-size:11px;padding:2px 8px;">
                                <i class="fa-solid fa-play"></i> Play
                            </button>
                        <?php else: ?>
                            <span style="color:#ccc;">—</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($hasVideo): ?>
                            <a href="<?= $base ?>/page/brand.php?name=<?= urlencode($b) ?>" class="btn btn-sm btn-outline" target="_blank"><i class="fa-solid fa-eye"></i> View Page</a>
                            <a href="<?= $base ?>/admin/brand-videos.php?delete=<?= urlencode($b) ?>" class="btn btn-sm btn-danger" onclick="return confirm('Remove <?= $b ?> video?')"><i class="fa-solid fa-trash"></i> Remove</a>
                        <?php else: ?>
                            <span style="color:#ccc;">Upload a video above</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>

<?php include '../_foot.php'; ?>
