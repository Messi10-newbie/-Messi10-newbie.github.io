<?php
include '_base.php';
require_login();

$id = intval($_GET['id'] ?? 0);
$stm = $db->prepare('SELECT * FROM orders WHERE id = ? AND user_id = ?');
$stm->execute([$id, auth()->id]);
$order = $stm->fetch();

if (!$order) {
    flash('error', 'Order not found.');
    redirect('/orders.php');
}

$stm = $db->prepare('SELECT oi.*, p.name, p.brand, p.specs, p.photo FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ?');
$stm->execute([$order->id]);
$items = $stm->fetchAll();

// Handle review submission from delivered order
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_order_review'])) {
    $productId  = intval($_POST['product_id'] ?? 0);
    $rating     = intval($_POST['rating'] ?? 5);
    $title      = clean($_POST['review_title'] ?? '');
    $comment    = clean($_POST['review_comment'] ?? '');
    $userName   = clean(auth()->name);

    if ($productId && $rating >= 1 && $rating <= 5 && $comment) {
        // Check if already reviewed this product for this order
        $stm = $db->prepare('SELECT id FROM reviews WHERE product_id = ? AND user_id = ?');
        $stm->execute([$productId, auth()->id]);
        if (!$stm->fetch()) {
            $stm = $db->prepare('INSERT INTO reviews (product_id, user_id, reviewer_name, rating, title, comment) VALUES (?, ?, ?, ?, ?, ?)');
            $stm->execute([$productId, auth()->id, $userName, $rating, $title, $comment]);

            // Bonus points for reviewing
            add_points(auth()->id, 50, 'earn', 'Review bonus for ' . clean($_POST['product_name'] ?? 'product'));

            flash('success', 'Review submitted! You earned 50 bonus points.');
        } else {
            flash('error', 'You have already reviewed this product.');
        }
    } else {
        flash('error', 'Please fill in your rating and comment.');
    }
    redirect("/order-detail.php?id={$order->id}#reviews");
}

// Check which products the user has already reviewed
$reviewedProducts = [];
if ($order->status === 'delivered') {
    foreach ($items as $item) {
        $stm = $db->prepare('SELECT id FROM reviews WHERE product_id = ? AND user_id = ?');
        $stm->execute([$item->product_id, auth()->id]);
        if ($stm->fetch()) {
            $reviewedProducts[] = $item->product_id;
        }
    }
}

include '_head.php';
?>

<section class="brand-header" style="background: #1d1d1f;">
    <div class="container">
        <h1>Order #<?= str_pad($order->id, 5, '0', STR_PAD_LEFT) ?></h1>
        <p><?= date('d M Y, h:i A', strtotime($order->created_at)) ?></p>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="checkout-layout">
            <div>
                <h3>Order Items</h3>
                <table class="data-table">
                    <thead>
                        <tr><th>Product</th><th>Price</th><th>Qty</th><th>Subtotal</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $item): ?>
                        <tr>
                            <td><strong><?= clean($item->name) ?></strong><br><small><?= clean($item->brand) ?> | <?= clean($item->specs ?? '') ?></small></td>
                            <td><?= format_price($item->price) ?></td>
                            <td><?= $item->quantity ?></td>
                            <td><?= format_price($item->price * $item->quantity) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr><td colspan="3" style="text-align:right"><strong>Total:</strong></td><td><strong><?= format_price($order->total) ?></strong></td></tr>
                    </tfoot>
                </table>
            </div>
            <div class="checkout-summary">
                <h3>Order Info</h3>
                <div class="summary-items">
                    <div class="summary-item"><div>Status</div><span class="status-badge status-<?= $order->status ?>"><?= ucfirst($order->status) ?></span></div>
                    <div class="summary-item"><div>Payment</div><span><?= strtoupper($order->payment_method) ?></span></div>
                    <div class="summary-item"><div>Ship To</div><span><?= clean($order->shipping_name) ?></span></div>
                    <div class="summary-item"><div>Phone</div><span><?= clean($order->shipping_phone) ?></span></div>
                    <div class="summary-item"><div>Address</div><span><?= clean($order->shipping_address) ?></span></div>
                    <?php if ($order->notes): ?>
                    <div class="summary-item"><div>Notes</div><span><?= clean($order->notes) ?></span></div>
                    <?php endif; ?>
                </div>
                <div style="margin-top:20px">
                    <a href="<?= $base ?>/receipt.php?id=<?= $order->id ?>" class="btn btn-primary btn-block">View Receipt</a>
                    <?php if ($order->status === 'pending'): ?>
                    <form method="POST" action="<?= $base ?>/orders.php" onsubmit="return confirm('Are you sure you want to cancel this order?')" style="margin-top:10px">
                        <input type="hidden" name="cancel_order" value="<?= $order->id ?>">
                        <button type="submit" class="btn-cancel" style="width:100%;padding:10px;border-radius:10px;font-size:14px;">Cancel Order</button>
                    </form>
                    <?php endif; ?>
                    <a href="<?= $base ?>/orders.php" class="btn btn-outline btn-block" style="margin-top:10px">Back to Orders</a>
                </div>
            </div>
        </div>
    </div>
</section>

<?php if ($order->status === 'delivered'): ?>
<section class="section" id="reviews">
    <div class="container">
        <h2 class="section-title" style="display:flex;align-items:center;gap:10px;">
            <i class="fa-solid fa-star" style="color:#f5a623;"></i> Rate Your Products
        </h2>
        <p style="color:var(--text-muted);margin-bottom:25px;font-size:14px;">Your order has been delivered! Share your experience and earn <strong style="color:#f39c12;">50 bonus points</strong> per review.</p>

        <div class="order-review-grid">
            <?php foreach ($items as $item): ?>
            <div class="order-review-card">
                <div class="order-review-product">
                    <div class="order-review-img">
                        <?php if ($item->photo !== 'default-product.png'): ?>
                            <img src="<?= $base ?>/uploads/<?= $item->photo ?>" alt="">
                        <?php else: ?>
                            <i class="fa-solid fa-mobile-screen" style="font-size:30px;color:#ccc;"></i>
                        <?php endif; ?>
                    </div>
                    <div>
                        <strong><?= clean($item->name) ?></strong>
                        <small style="display:block;color:#888;"><?= clean($item->brand) ?> | <?= clean($item->specs ?? '') ?></small>
                    </div>
                </div>

                <?php if (in_array($item->product_id, $reviewedProducts)): ?>
                <div class="order-review-done">
                    <i class="fa-solid fa-circle-check"></i> You've reviewed this product
                </div>
                <?php else: ?>
                <form method="POST" class="order-review-form">
                    <input type="hidden" name="product_id" value="<?= $item->product_id ?>">
                    <input type="hidden" name="product_name" value="<?= clean($item->name) ?>">

                    <div class="order-review-stars">
                        <label>Rating</label>
                        <div class="star-rating-input">
                            <?php for ($i = 5; $i >= 1; $i--): ?>
                            <input type="radio" name="rating" value="<?= $i ?>" id="star<?= $i ?>_<?= $item->product_id ?>" <?= $i === 5 ? 'checked' : '' ?>>
                            <label for="star<?= $i ?>_<?= $item->product_id ?>"><i class="fa-solid fa-star"></i></label>
                            <?php endfor; ?>
                        </div>
                    </div>

                    <div class="form-group" style="margin-bottom:10px;">
                        <input type="text" name="review_title" placeholder="Review title (optional)" style="font-size:13px;">
                    </div>
                    <div class="form-group" style="margin-bottom:12px;">
                        <textarea name="review_comment" rows="3" required placeholder="Share your experience with this product..." style="font-size:13px;"></textarea>
                    </div>
                    <button type="submit" name="submit_order_review" class="btn btn-primary btn-sm">
                        <i class="fa-solid fa-paper-plane"></i> Submit Review (+50 pts)
                    </button>
                </form>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?php include '_foot.php'; ?>
