<?php
require '../../_base.php';
require '../../_functions.php';

header('Content-Type: application/json');

$ref = trim($_GET['ref'] ?? '');
if (!$ref) {
    echo json_encode(['success' => false]);
    exit;
}

$stmt = $pdo->prepare("
    SELECT b.*, f.flight_number, f.departure_time, f.arrival_time, f.date,
           fa.code AS from_code, fa.city AS from_city,
           ta.code AS to_code,   ta.city AS to_city
    FROM bookings b
    JOIN flights f   ON f.flight_id = b.flight_id
    JOIN airports fa ON fa.airport_id = f.from_airport_id
    JOIN airports ta ON ta.airport_id = f.to_airport_id
    WHERE b.booking_reference = ?
");
$stmt->execute([$ref]);
$booking = $stmt->fetch();

if (!$booking) {
    echo json_encode(['success' => false]);
    exit;
}

$pStmt = $pdo->prepare("
    SELECT p.*, s.seat_number
    FROM passengers p
    LEFT JOIN seats s ON s.seat_id = p.seat_id
    WHERE p.booking_id = ?
");
$pStmt->execute([$booking['booking_id']]);
$passengers = $pStmt->fetchAll();

ob_start(); ?>
<div class="card border-primary mb-3">
    <div class="card-header bg-primary text-white d-flex justify-content-between">
        <strong><i class="bi bi-ticket me-2"></i><?= h($booking['booking_reference']) ?></strong>
        <span class="badge bg-<?= $booking['status'] === 'confirmed' ? 'success' : 'danger' ?>"><?= ucfirst($booking['status']) ?></span>
    </div>
    <div class="card-body">
        <div class="row g-3 mb-3">
            <div class="col-md-3">
                <div class="an-label">Route</div>
                <div class="fw-bold"><?= h($booking['from_code']) ?> → <?= h($booking['to_code']) ?></div>
                <div class="small text-muted"><?= h($booking['from_city']) ?> → <?= h($booking['to_city']) ?></div>
            </div>
            <div class="col-md-3">
                <div class="an-label">Flight</div>
                <div class="fw-bold"><?= h($booking['flight_number']) ?></div>
                <div class="small text-muted"><?= fmtDate($booking['date']) ?></div>
            </div>
            <div class="col-md-3">
                <div class="an-label">Time</div>
                <div class="fw-bold"><?= fmtTime($booking['departure_time']) ?> – <?= fmtTime($booking['arrival_time']) ?></div>
                <div class="small text-muted"><?= ucfirst($booking['class']) ?> Class</div>
            </div>
            <div class="col-md-3">
                <div class="an-label">Total Paid</div>
                <div class="fw-bold fs-5 text-primary">RM <?= number_format($booking['total_price'], 2) ?></div>
                <div class="small text-muted"><?= $booking['passenger_count'] ?> passenger(s)</div>
            </div>
        </div>
        <table class="table table-sm table-bordered mb-0">
            <thead class="table-light">
                <tr><th>#</th><th>Name</th><th>IC/Passport</th><th>Email</th><th>Phone</th><th>Seat</th><th>Baggage</th><th>Meal</th></tr>
            </thead>
            <tbody>
                <?php foreach ($passengers as $i => $p): ?>
                <tr>
                    <td><?= $i+1 ?></td>
                    <td><?= h($p['name']) ?></td>
                    <td><?= h($p['ic_passport']) ?></td>
                    <td><?= h($p['email']) ?></td>
                    <td><?= h($p['phone']) ?></td>
                    <td><?= h($p['seat_number'] ?? '—') ?></td>
                    <td><?= $p['baggage'] ? '✓' : '✗' ?></td>
                    <td><?= $p['meal']    ? '✓' : '✗' ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php
$html = ob_get_clean();
echo json_encode(['success' => true, 'html' => $html]);
