<?php
session_start();
require '_base.php';
require '_functions.php';

$stalls    = getStalls($pdo);
$openStalls = array_filter($stalls, fn($s) => $s['is_open']);
$menuCount  = array_sum(array_map(fn($s) => count($s['menu_names']), $stalls));
$cuisines   = count(array_unique(array_column($stalls, 'cuisine')));
$pageTitle = 'Home';
$cssPath   = '';
?>
<?php include '_head.php'; ?>

<!-- ── Hero ──────────────────────────────────────────────────────────────── -->
<section class="an-hero text-white">
    <div class="container text-center pb-5">
        <p class="mb-2 fw-semibold" style="color:rgba(255,255,255,.7);letter-spacing:2px;text-transform:uppercase;font-size:.85rem;">Campus Food Court Pre-Order</p>
        <h1 class="fw-bold mb-2">Browse stalls, order food, and pick a time slot</h1>
        <p class="mb-3" style="color:rgba(255,255,255,.8)">Skip the queue — your food is ready when you are.</p>
        <div class="hero-chips">
            <span class="hero-chip"><i class="bi bi-lightning-charge-fill"></i> Instant ordering</span>
            <span class="hero-chip"><i class="bi bi-clock-history"></i> Pick-up slots</span>
            <span class="hero-chip"><i class="bi bi-qr-code-scan"></i> Cashless payment</span>
        </div>
    </div>
</section>

<!-- ── Search Card ───────────────────────────────────────────────────────── -->
<div class="container">
    <div class="search-card shadow-lg">
        <form id="searchForm" action="order.php" method="GET">
            <div class="row g-3 align-items-end">
                <div class="col-12 col-md-6">
                    <div class="an-label"><i class="bi bi-shop me-1"></i>Choose a stall (optional — you can mix stalls in one order)</div>
                    <select name="stall_id" id="stall_id" class="an-input">
                        <option value="">All stalls — full menu</option>
                        <?php foreach ($openStalls as $stall): ?>
                        <option value="<?= $stall['stall_id'] ?>"><?= h($stall['stall_name']) ?> — <?= h($stall['cuisine']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-12 col-md-3">
                    <button type="submit" class="btn-an-search w-100 py-3">
                        <i class="bi bi-bag-check me-2"></i>Start Order
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- ── Live Stats Strip ──────────────────────────────────────────────────── -->
<section class="container mt-4">
    <div class="row g-3 text-center stats-strip reveal">
        <div class="col-4">
            <div class="stat-pill">
                <div class="stat-num" data-countup="<?= count($openStalls) ?>">0</div>
                <div class="stat-label">Stalls Open Now</div>
            </div>
        </div>
        <div class="col-4">
            <div class="stat-pill">
                <div class="stat-num" data-countup="<?= $menuCount ?>">0</div>
                <div class="stat-label">Menu Items</div>
            </div>
        </div>
        <div class="col-4">
            <div class="stat-pill">
                <div class="stat-num" data-countup="<?= $cuisines ?>">0</div>
                <div class="stat-label">Cuisines</div>
            </div>
        </div>
    </div>
</section>

<!-- ── Stall Search + Grid ───────────────────────────────────────────────── -->
<section class="page-section" id="stalls">
    <div class="container">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4">
            <h2 class="section-title fs-5 mb-0">
                <i class="bi bi-stars text-warning"></i> All Stalls
            </h2>
            <div class="stall-search-wrap">
                <i class="bi bi-search stall-search-icon"></i>
                <input type="text" id="stallSearch" class="stall-search-input"
                       placeholder="Search stalls or food items…" autocomplete="off">
            </div>
        </div>

        <div class="row g-3" id="stallGrid">
            <?php foreach ($stalls as $stall):
                $closed = !(bool)$stall['is_open'];
                $searchData = strtolower($stall['stall_name'] . ' ' . $stall['cuisine'] . ' ' . implode(' ', $stall['menu_names']));
            ?>
            <div class="col-6 col-md-4 col-lg-3 stall-col reveal" data-search="<?= h($searchData) ?>">
                <?php if ($closed): ?>
                <div class="stall-card stall-closed h-100 text-center p-3">
                    <div class="stall-emoji fs-1 mb-1"><?= cuisineEmoji($stall['cuisine']) ?></div>
                    <div class="fw-bold text-dark"><?= h($stall['stall_name']) ?></div>
                    <div class="small text-muted mb-2"><?= h($stall['cuisine']) ?></div>
                    <span class="badge-closed">Closed</span>
                </div>
                <?php else: ?>
                <a href="order.php?stall_id=<?= $stall['stall_id'] ?>" class="text-decoration-none d-block h-100">
                    <div class="stall-card stall-tilt h-100 text-center p-3">
                        <div class="stall-emoji fs-1 mb-1"><?= cuisineEmoji($stall['cuisine']) ?></div>
                        <div class="fw-bold text-dark"><?= h($stall['stall_name']) ?></div>
                        <div class="small text-muted"><?= h($stall['cuisine']) ?></div>
                        <span class="badge-open"><span class="pulse-dot"></span>Open</span>
                    </div>
                </a>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>

        <p id="noStallResults" class="text-center text-muted py-4" style="display:none">
            <i class="bi bi-search d-block fs-2 mb-2 opacity-50"></i>
            No stalls or menu items match your search.
        </p>
    </div>
</section>

<!-- ── Why Use It ─────────────────────────────────────────────────────────── -->
<section class="page-section bg-white border-top border-bottom">
    <div class="container">
        <h2 class="section-title fs-5 mb-4 justify-content-center"><i class="bi bi-shield-check text-primary"></i> Why use <?= SITE_NAME ?>?</h2>
        <div class="row g-4 text-center">
            <div class="col-6 col-md-3 reveal">
                <i class="bi bi-lightning-fill fs-1 text-warning d-block mb-2"></i>
                <h6 class="fw-bold">Fast Ordering</h6>
                <p class="small text-muted mb-0">Order before you arrive and skip the queue.</p>
            </div>
            <div class="col-6 col-md-3 reveal">
                <i class="bi bi-cash-coin fs-1 text-success d-block mb-2"></i>
                <h6 class="fw-bold">Campus Friendly</h6>
                <p class="small text-muted mb-0">Built for lunch rushes, lab breaks, and student life.</p>
            </div>
            <div class="col-6 col-md-3 reveal">
                <i class="bi bi-headset fs-1 text-primary d-block mb-2"></i>
                <h6 class="fw-bold">Pickup Slots</h6>
                <p class="small text-muted mb-0">Set a collection window that suits the stall.</p>
            </div>
            <div class="col-6 col-md-3 reveal">
                <i class="bi bi-printer fs-1 text-info d-block mb-2"></i>
                <h6 class="fw-bold">Easy to Deploy</h6>
                <p class="small text-muted mb-0">PHP, MySQL, and XAMPP are enough to run the app locally.</p>
            </div>
        </div>
    </div>
</section>

<?php include '_foot.php'; ?>
