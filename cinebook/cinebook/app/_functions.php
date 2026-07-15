<?php
// ─── Helper Functions ─────────────────────────────────────────────────────────

function generateOrderRef(): string {
    return 'ORD-' . strtoupper(substr(bin2hex(random_bytes(4)), 0, 8));
}

function fmtTime(string $time): string {
    return date('h:i A', strtotime($time));
}

function fmtDate(string $date): string {
    return date('D, d M Y', strtotime($date));
}

function fmtMoney(float $amount): string {
    return 'RM ' . number_format($amount, 2);
}

function h(string $str): string {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

/**
 * Rating for a menu item. Uses the DB `rating` column if present,
 * otherwise a stable pseudo-rating (3.8–5.0) derived from the item
 * so the same item always shows the same stars.
 */
function itemRating(array $item): float {
    if (isset($item['rating']) && $item['rating'] !== null && $item['rating'] !== '') {
        return max(0.0, min(5.0, (float)$item['rating']));
    }
    $seed = (int)($item['menu_item_id'] ?? crc32((string)($item['item_name'] ?? 'x')));
    return round(3.8 + (abs($seed) % 13) / 10, 1);
}

/**
 * Number of reviews for a menu item. Uses the DB `rating_count` column
 * if present, otherwise a stable derived count.
 */
function itemRatingCount(array $item): int {
    if (isset($item['rating_count']) && $item['rating_count'] !== null && $item['rating_count'] !== '') {
        return (int)$item['rating_count'];
    }
    $seed = (int)($item['menu_item_id'] ?? crc32((string)($item['item_name'] ?? 'x')));
    return 12 + (abs($seed) % 240);
}

/** Render 5 Bootstrap-icon stars (full / half / empty) for a rating. */
function renderStars(float $rating): string {
    $full = (int)floor($rating);
    $half = ($rating - $full) >= 0.5;
    $html = '';
    for ($i = 1; $i <= 5; $i++) {
        if ($i <= $full) {
            $html .= '<i class="bi bi-star-fill"></i>';
        } elseif ($half && $i === $full + 1) {
            $html .= '<i class="bi bi-star-half"></i>';
        } else {
            $html .= '<i class="bi bi-star"></i>';
        }
    }
    return $html;
}

/** Emoji for a stall based on its cuisine, with a sensible fallback. */
function cuisineEmoji(string $cuisine): string {
    $map = [
        'Malay'    => '🍛', 'Chinese' => '🥡', 'Indian'   => '🫓',
        'Western'  => '🍔', 'Japanese'=> '🍣', 'Korean'   => '🍜',
        'Thai'     => '🍲', 'Dessert' => '🧋', 'Snacks'   => '🍟',
        'Beverages'=> '🥤', 'Drinks'  => '🥤', 'Rice'     => '🍚',
        'Noodles'  => '🍜', 'Seafood' => '🦐', 'Vegetarian' => '🥗',
    ];
    return $map[$cuisine] ?? '🍜';
}

function redirect(string $url): never {
    header('Location: ' . $url);
    exit;
}

/**
 * Drink customization config. Each group has a label, whether it's multi-select,
 * and its choices as [name => extra price].
 */
function drinkOptionConfig(): array {
    return [
        'cup'    => ['label' => 'Cup',     'multi' => false, 'choices' => ['Regular' => 0.0, 'Large' => 1.50]],
        'sugar'  => ['label' => 'Sugar',   'multi' => false, 'choices' => ['Normal' => 0.0, 'Less (50%)' => 0.0, 'No Sugar' => 0.0]],
        'ice'    => ['label' => 'Ice',     'multi' => false, 'choices' => ['Normal' => 0.0, 'Less (50%)' => 0.0, 'No Ice' => 0.0]],
        'addons' => ['label' => 'Add-ons', 'multi' => true,  'choices' => ['Boba' => 1.50, 'Coconut Jelly' => 1.00, 'Grass Jelly' => 1.00, 'Pudding' => 1.20]],
    ];
}

/** Drink customization applies to dessert/snack stalls. */
function isDrinkStall(array $stall): bool {
    return in_array($stall['cuisine'] ?? '', ['Dessert', 'Snacks'], true);
}

/**
 * Turn a per-item selection ($sel with keys cup, sugar, ice, addons[]) into a
 * ['extra' => float, 'label' => string] describing the price bump and choices.
 */
function buildDrinkCustomization(array $sel): array {
    $cfg   = drinkOptionConfig();
    $extra = 0.0;
    $parts = [];

    foreach (['cup', 'sugar', 'ice'] as $key) {
        $val = trim((string)($sel[$key] ?? ''));
        if ($val !== '' && isset($cfg[$key]['choices'][$val])) {
            $extra   += (float)$cfg[$key]['choices'][$val];
            $parts[]  = $cfg[$key]['label'] . ': ' . $val;
        }
    }

    $addons = $sel['addons'] ?? [];
    if (is_array($addons)) {
        foreach ($addons as $addon) {
            $addon = (string)$addon;
            if (isset($cfg['addons']['choices'][$addon])) {
                $extra   += (float)$cfg['addons']['choices'][$addon];
                $parts[]  = '+' . $addon;
            }
        }
    }

    return ['extra' => round($extra, 2), 'label' => implode(', ', $parts)];
}

/** Add the order_items.options column if a legacy DB doesn't have it yet. */
function ensureOrderItemOptions(PDO $pdo): void {
    $exists = (int)$pdo->query("
        SELECT COUNT(*) FROM information_schema.columns
        WHERE table_schema = DATABASE()
          AND table_name = 'order_items'
          AND column_name = 'options'
    ")->fetchColumn();

    if ($exists === 0) {
        $pdo->exec("ALTER TABLE order_items ADD COLUMN options VARCHAR(255) NULL AFTER unit_price");
    }
}

/**
 * Allow orders.stall_id to be NULL on a legacy DB. A NULL stall_id means the
 * order mixes items from several stalls; the stalls involved are always
 * derivable from order_items → menu_items → stalls.
 */
function ensureMultiStallOrders(PDO $pdo): void {
    $nullable = $pdo->query("
        SELECT IS_NULLABLE FROM information_schema.columns
        WHERE table_schema = DATABASE()
          AND table_name = 'orders'
          AND column_name = 'stall_id'
    ")->fetchColumn();

    if ($nullable === 'NO') {
        $pdo->exec("ALTER TABLE orders MODIFY stall_id INT NULL");
    }
}

/** Add the menu_items.image_path column if a legacy DB doesn't have it yet. */
function ensureMenuItemImages(PDO $pdo): void {
    $exists = (int)$pdo->query("
        SELECT COUNT(*) FROM information_schema.columns
        WHERE table_schema = DATABASE()
          AND table_name = 'menu_items'
          AND column_name = 'image_path'
    ")->fetchColumn();

    if ($exists === 0) {
        $pdo->exec("ALTER TABLE menu_items ADD COLUMN image_path VARCHAR(255) NULL AFTER is_available");
    }
}

/**
 * Store an uploaded menu-item photo in images/menu/ and return its path
 * relative to the app root (e.g. "images/menu/item12_66a1b2c3.jpg").
 * Returns null and sets $error when the upload is missing or invalid.
 */
function saveMenuItemImage(array $file, int $menuItemId, ?string &$error = null): ?string {
    $error = null;
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return null;
    }
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $error = 'The photo upload failed — please try again.';
        return null;
    }
    if ($file['size'] > 3 * 1024 * 1024) {
        $error = 'The photo is too large (max 3 MB).';
        return null;
    }

    $info = @getimagesize($file['tmp_name']);
    $extByMime = ['image/jpeg' => 'jpg', 'image/png' => 'png'];
    $mime = $info['mime'] ?? '';
    if (!$info || !isset($extByMime[$mime])) {
        $error = 'Please upload a JPG, JPEG, or PNG image.';
        return null;
    }

    $dir = __DIR__ . '/images/menu';
    if (!is_dir($dir) && !mkdir($dir, 0775, true)) {
        $error = 'Could not create the images/menu folder.';
        return null;
    }

    $name = 'item' . $menuItemId . '_' . substr(bin2hex(random_bytes(4)), 0, 8) . '.' . $extByMime[$mime];
    if (!move_uploaded_file($file['tmp_name'], $dir . '/' . $name)) {
        $error = 'Could not save the uploaded photo.';
        return null;
    }
    return 'images/menu/' . $name;
}

/** Delete a previously saved menu photo (only touches files inside images/menu/). */
function deleteMenuItemImage(?string $imagePath): void {
    if (!$imagePath || !str_starts_with($imagePath, 'images/menu/')) {
        return;
    }
    $full = __DIR__ . '/' . $imagePath;
    if (is_file($full)) {
        @unlink($full);
    }
}

// ─── CAPTCHA (session-based math challenge — no external service needed) ─────

/** Create a new captcha question, store the answer in the session, return the question text. */
function captchaGenerate(): string {
    $a  = random_int(1, 9);
    $b  = random_int(1, 9);
    $op = random_int(0, 1) === 0 ? '+' : '×';
    $_SESSION['captcha_answer'] = $op === '+' ? $a + $b : $a * $b;
    $question = "$a $op $b = ?";
    $_SESSION['captcha_question'] = $question;
    return $question;
}

/** Check the submitted captcha answer. The answer is single-use: it's cleared after checking. */
function captchaVerify(string $input): bool {
    $expected = $_SESSION['captcha_answer'] ?? null;
    unset($_SESSION['captcha_answer'], $_SESSION['captcha_question']);
    return $expected !== null && trim($input) !== '' && (int)trim($input) === (int)$expected;
}

function sessionHas(string $key): bool {
    return isset($_SESSION[$key]) && !empty($_SESSION[$key]);
}

function getStalls(PDO $pdo): array {
    $stalls = $pdo->query("SELECT * FROM stalls WHERE is_active = 1 ORDER BY sort_order, stall_name")->fetchAll();
    $items  = $pdo->query("SELECT stall_id, item_name FROM menu_items WHERE is_available = 1")->fetchAll();
    $byStall = [];
    foreach ($items as $it) {
        $byStall[$it['stall_id']][] = $it['item_name'];
    }
    foreach ($stalls as &$s) {
        $s['menu_names'] = $byStall[$s['stall_id']] ?? [];
    }
    unset($s);
    return $stalls;
}

function toggleStallOpen(PDO $pdo, int $stallId): bool {
    $stmt = $pdo->prepare("UPDATE stalls SET is_open = 1 - is_open WHERE stall_id = ?");
    $stmt->execute([$stallId]);
    $row = $pdo->prepare("SELECT is_open FROM stalls WHERE stall_id = ?");
    $row->execute([$stallId]);
    return (bool)$row->fetchColumn();
}

function getStallsWithMenu(PDO $pdo): array {
    $stalls = getStalls($pdo);
    if (empty($stalls)) {
        return [];
    }

    $items = $pdo->query("
        SELECT m.*, s.stall_name, s.cuisine, s.description AS stall_description, s.starting_price
        FROM menu_items m
        JOIN stalls s ON s.stall_id = m.stall_id
        WHERE m.is_available = 1 AND s.is_active = 1
        ORDER BY s.sort_order, m.sort_order, m.item_name
    ")->fetchAll();

    $grouped = [];
    foreach ($stalls as $stall) {
        $stall['items'] = [];
        $grouped[$stall['stall_id']] = $stall;
    }

    foreach ($items as $item) {
        if (isset($grouped[$item['stall_id']])) {
            $grouped[$item['stall_id']]['items'][] = $item;
        }
    }

    return array_values($grouped);
}

function getMenuItems(PDO $pdo): array {
    return $pdo->query("
        SELECT m.*, s.stall_name, s.cuisine, s.description AS stall_description, s.starting_price
        FROM menu_items m
        JOIN stalls s ON s.stall_id = m.stall_id
        WHERE m.is_available = 1 AND s.is_active = 1
        ORDER BY s.sort_order, m.sort_order, m.item_name
    ")->fetchAll();
}

function getMenuItem(PDO $pdo, int $id): array {
    $stmt = $pdo->prepare("
        SELECT m.*, s.stall_name, s.cuisine, s.description AS stall_description, s.starting_price
        FROM menu_items m
        JOIN stalls s ON s.stall_id = m.stall_id
        WHERE m.menu_item_id = ?
    ");
    $stmt->execute([$id]);
    return $stmt->fetch() ?: [];
}

/**
 * Keep pickup slots current. The seed data uses fixed dates, so once they
 * pass they never come back. When no active slot is today-or-later, shift the
 * whole active set forward so the earliest lands on today and refill capacity.
 */
function ensureUpcomingSlots(PDO $pdo): void {
    $hasUpcoming = (int)$pdo->query(
        "SELECT COUNT(*) FROM pickup_slots WHERE is_active = 1 AND slot_date >= CURDATE()"
    )->fetchColumn();
    if ($hasUpcoming > 0) {
        return;
    }

    $minDate = $pdo->query(
        "SELECT MIN(slot_date) FROM pickup_slots WHERE is_active = 1"
    )->fetchColumn();
    if (!$minDate) {
        return;
    }

    $shift = $pdo->prepare(
        "UPDATE pickup_slots
         SET slot_date = slot_date + INTERVAL DATEDIFF(CURDATE(), ?) DAY,
             remaining = capacity
         WHERE is_active = 1"
    );
    $shift->execute([$minDate]);
}

function getPickupSlots(PDO $pdo): array {
    return $pdo->query("
        SELECT *
        FROM pickup_slots
        WHERE is_active = 1
        ORDER BY slot_date, slot_time
    ")->fetchAll();
}

function getPickupSlot(PDO $pdo, int $id): array {
    $stmt = $pdo->prepare("SELECT * FROM pickup_slots WHERE slot_id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch() ?: [];
}

function getCartItems(PDO $pdo, array $cart): array {
    $ids = array_keys(array_filter($cart, fn ($qty) => (int)$qty > 0));
    if (empty($ids)) {
        return [];
    }

    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $stmt = $pdo->prepare("
        SELECT m.*, s.stall_name, s.cuisine, s.description AS stall_description, s.starting_price
        FROM menu_items m
        JOIN stalls s ON s.stall_id = m.stall_id
        WHERE m.menu_item_id IN ($placeholders)
    ");
    $stmt->execute($ids);
    $items = $stmt->fetchAll();

    $indexed = [];
    foreach ($items as $item) {
        $itemId = (int)$item['menu_item_id'];
        $quantity = max(1, (int)($cart[$itemId] ?? 0));
        $item['quantity'] = $quantity;
        $item['line_total'] = $quantity * (float)$item['price'];
        $indexed[$itemId] = $item;
    }

    return array_values($indexed);
}

function cartCount(array $cart): int {
    return array_sum(array_map('intval', $cart));
}

function cartTotal(array $items): float {
    return array_reduce($items, fn ($sum, $item) => $sum + (float)$item['line_total'], 0.0);
}

function getOrder(PDO $pdo, int $id): array {
    $stmt = $pdo->prepare("
        SELECT o.*, ps.slot_date, ps.slot_time, ps.capacity, ps.remaining
        FROM orders o
        JOIN pickup_slots ps ON ps.slot_id = o.slot_id
        WHERE o.order_id = ?
    ");
    $stmt->execute([$id]);
    return $stmt->fetch() ?: [];
}

function getOrderItems(PDO $pdo, int $orderId): array {
    $stmt = $pdo->prepare("
        SELECT oi.*, m.item_name, m.item_desc AS item_description, s.stall_name
        FROM order_items oi
        JOIN menu_items m ON m.menu_item_id = oi.menu_item_id
        JOIN stalls s ON s.stall_id = m.stall_id
        WHERE oi.order_id = ?
        ORDER BY s.sort_order, m.sort_order, m.item_name
    ");
    $stmt->execute([$orderId]);
    return $stmt->fetchAll();
}
