<?php
// returns HTML fragment for the reservation list for the logged-in user
session_start();
require_once __DIR__ . '/condb.php';

if (!isset($_SESSION['m_id'])) {
    http_response_code(401);
    echo 'Unauthorized';
    exit;
}

$m_id = (int)$_SESSION['m_id'];

$stmt = $conn->prepare("SELECT * FROM tbl_reservation WHERE m_id = ? ORDER BY created_at DESC");
$stmt->execute([$m_id]);
$reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($reservations)) {
    echo '<div class="alert alert-info rounded-4">ยังไม่มีรายการสั่งซื้อ</div>';
    exit;
}

foreach ($reservations as $r) {
    $reserve_id = (int)$r['reserve_id'];
    $created = $r['created_at'] ?? $r['created'] ?? '-';

    $stmtSum = $conn->prepare("SELECT COALESCE(SUM(rd.qty * p.p_price),0) AS total FROM tbl_reservation_detail rd JOIN tbl_product p ON p.p_id = rd.p_id WHERE rd.reserve_id = ?");
    $stmtSum->execute([$reserve_id]);
    $total = (float)$stmtSum->fetchColumn();

    $status = htmlspecialchars($r['payment_status'] ?? $r['reserve_status'] ?? 'N/A');

    echo '<div class="card mb-3">';
    echo '<div class="card-body d-flex justify-content-between align-items-center">';
    echo '<div>';
    echo '<div class="fw-bold">คำสั่งซื้อ: ' . $reserve_id . '</div>';
    echo '<div class="text-muted small">วันที่: ' . htmlspecialchars($created) . '</div>';
    echo '<div class="text-muted small">สถานะ: ' . $status . '</div>';
    echo '</div>';
    echo '<div class="text-end">';
    echo '<div class="fw-bold text-danger mb-2">' . number_format($total, 2) . ' บาท</div>';
    echo '<a href="order_detail.php?id=' . $reserve_id . '" class="btn btn-outline-primary btn-sm">ดูรายละเอียด</a>';
    echo '</div>';
    echo '</div>';
    echo '</div>';
}

exit;
