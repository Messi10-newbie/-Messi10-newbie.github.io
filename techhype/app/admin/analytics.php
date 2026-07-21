<?php
include '../_base.php';
require_admin();

// ── Summary Stats ──
$totalRevenue = $db->query("SELECT COALESCE(SUM(total), 0) FROM orders WHERE status != 'cancelled'")->fetchColumn();
$totalOrders = $db->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$totalMembers = $db->query("SELECT COUNT(*) FROM users WHERE role = 'member'")->fetchColumn();
$totalProducts = $db->query("SELECT COUNT(*) FROM products")->fetchColumn();
$pendingOrders = $db->query("SELECT COUNT(*) FROM orders WHERE status = 'pending'")->fetchColumn();
$deliveredOrders = $db->query("SELECT COUNT(*) FROM orders WHERE status = 'delivered'")->fetchColumn();
$avgOrderValue = $totalOrders > 0 ? $totalRevenue / $totalOrders : 0;
$totalPointsIssued = $db->query("SELECT COALESCE(SUM(points), 0) FROM points_log WHERE type = 'earn'")->fetchColumn();
$totalVouchersUsed = $db->query("SELECT COUNT(*) FROM vouchers WHERE status = 'used'")->fetchColumn();
$studentCount = $db->query("SELECT COUNT(*) FROM student_verifications WHERE status = 'verified'")->fetchColumn();

// ── Revenue Last 12 Months ──
$revenueMonthly = [];
for ($i = 11; $i >= 0; $i--) {
    $month = date('Y-m', strtotime("-$i months"));
    $stm = $db->prepare("SELECT COALESCE(SUM(total), 0) FROM orders WHERE status != 'cancelled' AND DATE_FORMAT(created_at, '%Y-%m') = ?");
    $stm->execute([$month]);
    $revenueMonthly[] = ['label' => date('M Y', strtotime($month . '-01')), 'value' => (float)$stm->fetchColumn()];
}

// ── Orders Last 12 Months ──
$ordersMonthly = [];
for ($i = 11; $i >= 0; $i--) {
    $month = date('Y-m', strtotime("-$i months"));
    $stm = $db->prepare("SELECT COUNT(*) FROM orders WHERE DATE_FORMAT(created_at, '%Y-%m') = ?");
    $stm->execute([$month]);
    $ordersMonthly[] = ['label' => date('M', strtotime($month . '-01')), 'value' => (int)$stm->fetchColumn()];
}

// ── Orders by Status ──
$orderStatuses = $db->query("SELECT status, COUNT(*) as cnt FROM orders GROUP BY status")->fetchAll();

// ── Top 5 Best Selling Products ──
$topProducts = $db->query("SELECT p.name, p.brand, SUM(oi.quantity) as sold, SUM(oi.quantity * oi.price) as revenue
    FROM order_items oi JOIN products p ON oi.product_id = p.id
    JOIN orders o ON oi.order_id = o.id WHERE o.status != 'cancelled'
    GROUP BY oi.product_id ORDER BY sold DESC LIMIT 5")->fetchAll();

// ── Revenue by Brand ──
$brandRevenue = $db->query("SELECT p.brand, SUM(oi.quantity * oi.price) as revenue
    FROM order_items oi JOIN products p ON oi.product_id = p.id
    JOIN orders o ON oi.order_id = o.id WHERE o.status != 'cancelled'
    GROUP BY p.brand ORDER BY revenue DESC LIMIT 8")->fetchAll();

// ── Revenue by Category ──
$categoryRevenue = $db->query("SELECT p.category, SUM(oi.quantity * oi.price) as revenue, SUM(oi.quantity) as sold
    FROM order_items oi JOIN products p ON oi.product_id = p.id
    JOIN orders o ON oi.order_id = o.id WHERE o.status != 'cancelled'
    GROUP BY p.category ORDER BY revenue DESC")->fetchAll();

// ── New Members Last 12 Months ──
$membersMonthly = [];
for ($i = 11; $i >= 0; $i--) {
    $month = date('Y-m', strtotime("-$i months"));
    $stm = $db->prepare("SELECT COUNT(*) FROM users WHERE role = 'member' AND DATE_FORMAT(created_at, '%Y-%m') = ?");
    $stm->execute([$month]);
    $membersMonthly[] = ['label' => date('M', strtotime($month . '-01')), 'value' => (int)$stm->fetchColumn()];
}

// ── Payment Method Breakdown ──
$paymentMethods = $db->query("SELECT payment_method, COUNT(*) as cnt, SUM(total) as revenue FROM orders WHERE status != 'cancelled' GROUP BY payment_method ORDER BY revenue DESC")->fetchAll();

// ── Recent Orders ──
$recentOrders = $db->query("SELECT o.*, u.name as user_name FROM orders o JOIN users u ON o.user_id = u.id ORDER BY o.created_at DESC LIMIT 5")->fetchAll();

// ── Top Reviewers ──
$topReviewers = $db->query("SELECT reviewer_name, COUNT(*) as cnt, ROUND(AVG(rating),1) as avg_rating FROM reviews GROUP BY user_id ORDER BY cnt DESC LIMIT 5")->fetchAll();

include '../_head.php';
?>

<section class="brand-header" style="background: #1d1d1f;">
    <div class="container">
        <h1><i class="fa-solid fa-chart-line"></i> Analytics Dashboard</h1>
        <p>Business insights and performance data</p>
    </div>
</section>

<section class="section">
    <div class="container">
        <!-- Admin Nav -->
        <div class="admin-nav">
            <a href="<?= $base ?>/admin/members.php">Members</a>
            <a href="<?= $base ?>/admin/products.php">Products</a>
            <a href="<?= $base ?>/admin/orders.php">Orders</a>
            <a href="<?= $base ?>/admin/brand-videos.php">Brand Videos</a>
            <a href="<?= $base ?>/admin/analytics.php" class="active">Analytics</a>
        </div>

        <!-- Summary Cards -->
        <div class="analytics-cards">
            <div class="analytics-card" style="--card-accent:#6c5ce7;">
                <div class="analytics-card-icon"><i class="fa-solid fa-dollar-sign"></i></div>
                <div class="analytics-card-data">
                    <span class="analytics-card-label">Total Revenue</span>
                    <strong class="analytics-card-value"><?= format_price($totalRevenue) ?></strong>
                </div>
            </div>
            <div class="analytics-card" style="--card-accent:#00cec9;">
                <div class="analytics-card-icon"><i class="fa-solid fa-bag-shopping"></i></div>
                <div class="analytics-card-data">
                    <span class="analytics-card-label">Total Orders</span>
                    <strong class="analytics-card-value"><?= number_format($totalOrders) ?></strong>
                </div>
            </div>
            <div class="analytics-card" style="--card-accent:#fdcb6e;">
                <div class="analytics-card-icon"><i class="fa-solid fa-chart-simple"></i></div>
                <div class="analytics-card-data">
                    <span class="analytics-card-label">Avg Order Value</span>
                    <strong class="analytics-card-value"><?= format_price($avgOrderValue) ?></strong>
                </div>
            </div>
            <div class="analytics-card" style="--card-accent:#e17055;">
                <div class="analytics-card-icon"><i class="fa-solid fa-users"></i></div>
                <div class="analytics-card-data">
                    <span class="analytics-card-label">Members</span>
                    <strong class="analytics-card-value"><?= number_format($totalMembers) ?></strong>
                </div>
            </div>
            <div class="analytics-card" style="--card-accent:#0984e3;">
                <div class="analytics-card-icon"><i class="fa-solid fa-box"></i></div>
                <div class="analytics-card-data">
                    <span class="analytics-card-label">Products</span>
                    <strong class="analytics-card-value"><?= number_format($totalProducts) ?></strong>
                </div>
            </div>
            <div class="analytics-card" style="--card-accent:#e74c3c;">
                <div class="analytics-card-icon"><i class="fa-solid fa-clock"></i></div>
                <div class="analytics-card-data">
                    <span class="analytics-card-label">Pending Orders</span>
                    <strong class="analytics-card-value"><?= number_format($pendingOrders) ?></strong>
                </div>
            </div>
            <div class="analytics-card" style="--card-accent:#27ae60;">
                <div class="analytics-card-icon"><i class="fa-solid fa-ticket"></i></div>
                <div class="analytics-card-data">
                    <span class="analytics-card-label">Vouchers Used</span>
                    <strong class="analytics-card-value"><?= number_format($totalVouchersUsed) ?></strong>
                </div>
            </div>
            <div class="analytics-card" style="--card-accent:#8e44ad;">
                <div class="analytics-card-icon"><i class="fa-solid fa-graduation-cap"></i></div>
                <div class="analytics-card-data">
                    <span class="analytics-card-label">Verified Students</span>
                    <strong class="analytics-card-value"><?= number_format($studentCount) ?></strong>
                </div>
            </div>
        </div>

        <!-- Charts Row 1: Revenue + Orders -->
        <div class="analytics-charts-row">
            <div class="analytics-chart-box">
                <h3 class="analytics-chart-title">Revenue (Last 12 Months)</h3>
                <canvas id="revenueChart"></canvas>
            </div>
            <div class="analytics-chart-box">
                <h3 class="analytics-chart-title">Orders (Last 12 Months)</h3>
                <canvas id="ordersChart"></canvas>
            </div>
        </div>

        <!-- Charts Row 2: Brand Revenue + Category -->
        <div class="analytics-charts-row">
            <div class="analytics-chart-box">
                <h3 class="analytics-chart-title">Revenue by Brand</h3>
                <canvas id="brandChart"></canvas>
            </div>
            <div class="analytics-chart-box">
                <h3 class="analytics-chart-title">Revenue by Category</h3>
                <canvas id="categoryChart"></canvas>
            </div>
        </div>

        <!-- Charts Row 3: Order Status + Payment Methods -->
        <div class="analytics-charts-row">
            <div class="analytics-chart-box">
                <h3 class="analytics-chart-title">Orders by Status</h3>
                <canvas id="statusChart"></canvas>
            </div>
            <div class="analytics-chart-box">
                <h3 class="analytics-chart-title">Payment Methods</h3>
                <canvas id="paymentChart"></canvas>
            </div>
        </div>

        <!-- Charts Row 4: New Members -->
        <div class="analytics-charts-row">
            <div class="analytics-chart-box">
                <h3 class="analytics-chart-title">New Members (Last 12 Months)</h3>
                <canvas id="membersChart"></canvas>
            </div>
            <div class="analytics-chart-box">
                <h3 class="analytics-chart-title">Top 5 Best Selling Products</h3>
                <canvas id="topProductsChart"></canvas>
            </div>
        </div>

        <!-- Tables Row: Recent Orders + Top Reviewers -->
        <div class="analytics-charts-row">
            <div class="analytics-chart-box">
                <h3 class="analytics-chart-title">Recent Orders</h3>
                <table class="data-table" style="font-size:13px;">
                    <thead><tr><th>Order</th><th>Customer</th><th>Total</th><th>Status</th><th>Date</th></tr></thead>
                    <tbody>
                        <?php foreach ($recentOrders as $ro): ?>
                        <tr>
                            <td><a href="<?= $base ?>/admin/order-detail.php?id=<?= $ro->id ?>">#<?= str_pad($ro->id, 5, '0', STR_PAD_LEFT) ?></a></td>
                            <td><?= clean($ro->user_name) ?></td>
                            <td><?= format_price($ro->total) ?></td>
                            <td><span class="status-badge status-<?= $ro->status ?>"><?= ucfirst($ro->status) ?></span></td>
                            <td><?= date('d M', strtotime($ro->created_at)) ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($recentOrders)): ?><tr><td colspan="5" style="text-align:center;color:#999;">No orders yet</td></tr><?php endif; ?>
                    </tbody>
                </table>
            </div>
            <div class="analytics-chart-box">
                <h3 class="analytics-chart-title">Top Reviewers</h3>
                <table class="data-table" style="font-size:13px;">
                    <thead><tr><th>Name</th><th>Reviews</th><th>Avg Rating</th></tr></thead>
                    <tbody>
                        <?php foreach ($topReviewers as $tr): ?>
                        <tr>
                            <td><strong><?= clean($tr->reviewer_name) ?></strong></td>
                            <td><?= $tr->cnt ?></td>
                            <td><i class="fa-solid fa-star" style="color:#f5a623;font-size:11px;"></i> <?= $tr->avg_rating ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($topReviewers)): ?><tr><td colspan="3" style="text-align:center;color:#999;">No reviews yet</td></tr><?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</section>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
<script>
Chart.defaults.font.family = "'Inter', sans-serif";
Chart.defaults.font.size = 12;
Chart.defaults.plugins.legend.labels.usePointStyle = true;

var purple = '#6c5ce7', blue = '#0984e3', teal = '#00cec9', orange = '#e17055', yellow = '#fdcb6e', green = '#27ae60', red = '#e74c3c', pink = '#fd79a8';
var palette = [purple, blue, teal, orange, yellow, green, red, pink, '#8e44ad', '#2d3436'];

// Revenue Chart (Bar + Line)
new Chart(document.getElementById('revenueChart'), {
    type: 'bar',
    data: {
        labels: <?= json_encode(array_column($revenueMonthly, 'label')) ?>,
        datasets: [{
            label: 'Revenue (RM)',
            data: <?= json_encode(array_column($revenueMonthly, 'value')) ?>,
            backgroundColor: 'rgba(108, 92, 231, 0.2)',
            borderColor: purple,
            borderWidth: 2,
            borderRadius: 6,
            hoverBackgroundColor: 'rgba(108, 92, 231, 0.4)',
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: {
            y: { beginAtZero: true, ticks: { callback: v => 'RM ' + v.toLocaleString() }, grid: { color: 'rgba(0,0,0,0.04)' } },
            x: { grid: { display: false } }
        }
    }
});

// Orders Chart (Line)
new Chart(document.getElementById('ordersChart'), {
    type: 'line',
    data: {
        labels: <?= json_encode(array_column($ordersMonthly, 'label')) ?>,
        datasets: [{
            label: 'Orders',
            data: <?= json_encode(array_column($ordersMonthly, 'value')) ?>,
            borderColor: blue,
            backgroundColor: 'rgba(9, 132, 227, 0.1)',
            fill: true,
            tension: 0.4,
            pointBackgroundColor: blue,
            pointRadius: 4,
            pointHoverRadius: 6,
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: {
            y: { beginAtZero: true, ticks: { stepSize: 1 }, grid: { color: 'rgba(0,0,0,0.04)' } },
            x: { grid: { display: false } }
        }
    }
});

// Brand Revenue (Horizontal Bar)
new Chart(document.getElementById('brandChart'), {
    type: 'bar',
    data: {
        labels: <?= json_encode(array_column($brandRevenue, 'brand')) ?>,
        datasets: [{
            label: 'Revenue (RM)',
            data: <?= json_encode(array_map(fn($b) => (float)$b->revenue, $brandRevenue)) ?>,
            backgroundColor: palette.slice(0, count($brandRevenue)),
            borderRadius: 6,
        }]
    },
    options: {
        indexAxis: 'y',
        responsive: true,
        plugins: { legend: { display: false } },
        scales: {
            x: { beginAtZero: true, ticks: { callback: v => 'RM ' + v.toLocaleString() }, grid: { color: 'rgba(0,0,0,0.04)' } },
            y: { grid: { display: false } }
        }
    }
});

// Category Revenue (Doughnut)
new Chart(document.getElementById('categoryChart'), {
    type: 'doughnut',
    data: {
        labels: <?= json_encode(array_map(fn($c) => ucfirst($c->category), $categoryRevenue)) ?>,
        datasets: [{
            data: <?= json_encode(array_map(fn($c) => (float)$c->revenue, $categoryRevenue)) ?>,
            backgroundColor: palette.slice(0, count($categoryRevenue)),
            borderWidth: 2,
            hoverOffset: 8,
        }]
    },
    options: {
        responsive: true,
        cutout: '55%',
        plugins: {
            legend: { position: 'bottom' },
            tooltip: { callbacks: { label: ctx => ctx.label + ': RM ' + ctx.parsed.toLocaleString() } }
        }
    }
});

// Order Status (Pie)
var statusColors = { pending: yellow, processing: blue, shipped: teal, delivered: green, cancelled: red };
new Chart(document.getElementById('statusChart'), {
    type: 'pie',
    data: {
        labels: <?= json_encode(array_map(fn($s) => ucfirst($s->status), $orderStatuses)) ?>,
        datasets: [{
            data: <?= json_encode(array_map(fn($s) => (int)$s->cnt, $orderStatuses)) ?>,
            backgroundColor: <?= json_encode(array_map(fn($s) => ['pending'=>'#fdcb6e','processing'=>'#0984e3','shipped'=>'#00cec9','delivered'=>'#27ae60','cancelled'=>'#e74c3c'][$s->status] ?? '#999', $orderStatuses)) ?>,
            borderWidth: 2,
            hoverOffset: 8,
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { position: 'bottom' } }
    }
});

// Payment Methods (Polar Area)
new Chart(document.getElementById('paymentChart'), {
    type: 'polarArea',
    data: {
        labels: <?= json_encode(array_map(fn($p) => ['cod'=>'Cash on Delivery','card'=>'Card','bank'=>'Online Banking','ewallet'=>'E-Wallet'][$p->payment_method] ?? ucfirst($p->payment_method), $paymentMethods)) ?>,
        datasets: [{
            data: <?= json_encode(array_map(fn($p) => (float)$p->revenue, $paymentMethods)) ?>,
            backgroundColor: ['rgba(108,92,231,0.6)', 'rgba(9,132,227,0.6)', 'rgba(0,206,201,0.6)', 'rgba(253,203,110,0.6)'],
            borderWidth: 1,
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { position: 'bottom' },
            tooltip: { callbacks: { label: ctx => ctx.label + ': RM ' + ctx.parsed.r.toLocaleString() } }
        },
        scales: { r: { ticks: { display: false }, grid: { color: 'rgba(0,0,0,0.04)' } } }
    }
});

// New Members (Bar)
new Chart(document.getElementById('membersChart'), {
    type: 'bar',
    data: {
        labels: <?= json_encode(array_column($membersMonthly, 'label')) ?>,
        datasets: [{
            label: 'New Members',
            data: <?= json_encode(array_column($membersMonthly, 'value')) ?>,
            backgroundColor: 'rgba(225, 112, 85, 0.3)',
            borderColor: orange,
            borderWidth: 2,
            borderRadius: 6,
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: {
            y: { beginAtZero: true, ticks: { stepSize: 1 }, grid: { color: 'rgba(0,0,0,0.04)' } },
            x: { grid: { display: false } }
        }
    }
});

// Top Products (Horizontal Bar)
new Chart(document.getElementById('topProductsChart'), {
    type: 'bar',
    data: {
        labels: <?= json_encode(array_map(fn($p) => $p->name, $topProducts)) ?>,
        datasets: [{
            label: 'Units Sold',
            data: <?= json_encode(array_map(fn($p) => (int)$p->sold, $topProducts)) ?>,
            backgroundColor: [purple, blue, teal, orange, green],
            borderRadius: 6,
        }]
    },
    options: {
        indexAxis: 'y',
        responsive: true,
        plugins: { legend: { display: false } },
        scales: {
            x: { beginAtZero: true, ticks: { stepSize: 1 }, grid: { color: 'rgba(0,0,0,0.04)' } },
            y: { grid: { display: false } }
        }
    }
});
</script>

<?php include '../_foot.php'; ?>
