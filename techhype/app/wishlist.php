<?php
include '_base.php';
require_login();

// Handle remove
if (isset($_GET['remove'])) {
    $db->prepare('DELETE FROM wishlist WHERE user_id = ? AND product_id = ?')->execute([auth()->id, intval($_GET['remove'])]);
    flash('success', 'Removed from wishlist.');
    redirect('/wishlist.php');
}

$stm = $db->prepare('SELECT p.* FROM wishlist w JOIN products p ON w.product_id = p.id WHERE w.user_id = ? ORDER BY w.created_at DESC');
$stm->execute([auth()->id]);
$products = $stm->fetchAll();

include '_head.php';
?>

<section class="brand-header" style="background: #1d1d1f;">
    <div class="container">
        <h1><i class="fa-solid fa-heart"></i> My Wishlist</h1>
        <p><?= count($products) ?> item(s)</p>
    </div>
</section>

<section class="section">
    <div class="container">
        <?php if (empty($products)): ?>
            <div class="empty-state">
                <i class="fa-regular fa-heart"></i>
                <h3>Your wishlist is empty</h3>
                <p>Browse products and click the heart icon to save them here.</p>
                <a href="<?= $base ?>/products.php" class="btn btn-primary">Browse Products</a>
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
                    <p class="product-price">
                        <?php if ($p->sale_price): ?>
                            <span class="old-price"><?= format_price($p->price) ?></span> <?= format_price($p->sale_price) ?>
                        <?php else: ?>
                            <?= format_price($p->price) ?>
                        <?php endif; ?>
                    </p>
                    <div style="display:flex;gap:8px;">
                        <form method="POST" action="<?= $base ?>/cart-add.php" style="flex:1">
                            <input type="hidden" name="id" value="<?= $p->id ?>">
                            <button type="submit" class="btn btn-primary btn-sm" style="width:100%">Add to Cart</button>
                        </form>
                        <a href="<?= $base ?>/wishlist.php?remove=<?= $p->id ?>" class="btn btn-sm" style="border:1px solid #ff3b30;color:#ff3b30;" onclick="return confirm('Remove from wishlist?')"><i class="fa-solid fa-trash"></i></a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</section>

<?php include '_foot.php'; ?>
