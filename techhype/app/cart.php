<?php
include '_base.php';

// Update cart
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update'])) {
        foreach ($_POST['qty'] as $key => $qty) {
            $qty = intval($qty);
            if ($qty < 1) unset($_SESSION['cart'][$key]);
            else $_SESSION['cart'][$key]['qty'] = $qty;
        }
        flash('success', 'Cart updated.');
        redirect('/cart.php');
    }
    if (isset($_POST['remove'])) {
        $key = $_POST['remove'];
        unset($_SESSION['cart'][$key]);
        flash('success', 'Item removed from cart.');
        redirect('/cart.php');
    }
}

// Get cart items
$items = [];
$total = 0;
if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $key => $cartItem) {
        // Support old format (just id => qty) and new format (key => array)
        if (is_array($cartItem)) {
            $productId = $cartItem['product_id'];
            $qty = $cartItem['qty'];
            $color = $cartItem['color'] ?? '';
            $bandColor = $cartItem['band_color'] ?? '';
            $storage = $cartItem['storage'] ?? '';
            $variantPrice = $cartItem['price'] ?? '';
        } else {
            $productId = $key;
            $qty = $cartItem;
            $color = '';
            $bandColor = '';
            $storage = '';
            $variantPrice = '';
        }

        $stm = $db->prepare('SELECT * FROM products WHERE id = ?');
        $stm->execute([$productId]);
        $p = $stm->fetch();
        if ($p) {
            // Use variant price if set, otherwise default
            if ($variantPrice) {
                $price = floatval(str_replace(['RM ', ',', ' '], '', $variantPrice));
            } else {
                $price = $p->sale_price ?? $p->price;
            }
            $subtotal = $price * $qty;
            $total += $subtotal;
            $items[] = (object)[
                'key' => $key,
                'product' => $p,
                'qty' => $qty,
                'price' => $price,
                'subtotal' => $subtotal,
                'color' => $color,
                'band_color' => $bandColor,
                'storage' => $storage,
            ];
        }
    }
}

include '_head.php';
?>

<section class="brand-header" style="background: #1d1d1f;">
    <div class="container">
        <h1>Shopping Cart</h1>
        <p><?= count($items) ?> item(s) in your cart</p>
    </div>
</section>

<section class="section">
    <div class="container">
        <?php if (empty($items)): ?>
            <div class="empty-state">
                <i class="fa-solid fa-cart-shopping"></i>
                <h3>Your cart is empty</h3>
                <p>Browse our products and add something you like!</p>
                <a href="<?= $base ?>/products.php" class="btn btn-primary">Shop Now</a>
            </div>
        <?php else: ?>
        <form method="POST">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Price</th>
                        <th>Quantity</th>
                        <th>Subtotal</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $item): ?>
                    <tr>
                        <td>
                            <div class="cart-product">
                                <div class="cart-img">
                                    <?php if ($item->product->photo !== 'default-product.png'): ?>
                                        <img src="<?= $base ?>/uploads/<?= $item->product->photo ?>" alt="" style="width:60px;height:60px;object-fit:cover;border-radius:8px;">
                                    <?php else: ?>
                                        <i class="fa-solid fa-mobile-screen"></i>
                                    <?php endif; ?>
                                </div>
                                <div>
                                    <strong><?= clean($item->product->name) ?></strong>
                                    <small style="display:block;color:#888;"><?= clean($item->product->brand) ?> | <?= clean($item->product->specs ?? '') ?></small>
                                    <?php if ($item->color || $item->band_color || $item->storage): ?>
                                    <small style="display:block;margin-top:4px;">
                                        <?php if ($item->color): ?>
                                            <span style="display:inline-flex;align-items:center;gap:4px;background:#f0f0f0;padding:2px 8px;border-radius:4px;font-size:12px;">
                                                <span style="width:10px;height:10px;border-radius:50%;background:#888;display:inline-block;"></span>
                                                <?= $item->band_color ? 'Case: ' : '' ?><?= clean($item->color) ?>
                                            </span>
                                        <?php endif; ?>
                                        <?php if ($item->band_color): ?>
                                            <span style="display:inline-flex;align-items:center;gap:4px;background:#f0f0f0;padding:2px 8px;border-radius:4px;font-size:12px;">
                                                <span style="width:10px;height:10px;border-radius:50%;background:#888;display:inline-block;"></span>
                                                Band: <?= clean($item->band_color) ?>
                                            </span>
                                        <?php endif; ?>
                                        <?php if ($item->storage): ?>
                                            <span style="background:#f0f0f0;padding:2px 8px;border-radius:4px;font-size:12px;"><?= clean($item->storage) ?></span>
                                        <?php endif; ?>
                                    </small>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </td>
                        <td><?= format_price($item->price) ?></td>
                        <td><input type="number" name="qty[<?= $item->key ?>]" value="<?= $item->qty ?>" min="1" class="qty-input"></td>
                        <td><strong><?= format_price($item->subtotal) ?></strong></td>
                        <td><button type="submit" name="remove" value="<?= $item->key ?>" class="btn-delete"><i class="fa-solid fa-trash"></i></button></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="3" style="text-align:right"><strong>Total:</strong></td>
                        <td><strong class="detail-price" style="font-size:20px"><?= format_price($total) ?></strong></td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
            <div class="cart-actions">
                <button type="submit" name="update" class="btn btn-outline">Update Cart</button>
                <a href="<?= $base ?>/checkout.php" class="btn btn-primary">Proceed to Checkout</a>
            </div>
        </form>
        <?php endif; ?>
    </div>
</section>

<?php include '_foot.php'; ?>
