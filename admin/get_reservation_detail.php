<?php
session_start();
include '../condb.php';

header('Content-Type: application/json; charset=utf-8');

$reserve_id = isset($_GET['reserve_id']) ? (int)$_GET['reserve_id'] : 0;

if ($reserve_id <= 0) {
    echo json_encode([
        'status' => false,
        'items' => [],
        'reserve' => null,
        'slip' => null,
        'message' => 'ไม่พบรหัสการจอง'
    ]);
    exit;
}

try {
    // ข้อมูลการจอง
    $stmtReserve = $conn->prepare("
        SELECT 
            reserve_id,
            reserve_name,
            reserve_phone,
            pickup_date,
            pickup_time,
            total_amount,
            deposit_amount,
            (total_amount - deposit_amount) AS remaining_amount,
            payment_status,
            reserve_status,
            created_at
        FROM tbl_reservation
        WHERE reserve_id = ?
        LIMIT 1
    ");
    $stmtReserve->execute([$reserve_id]);
    $reserve = $stmtReserve->fetch(PDO::FETCH_ASSOC);

    if (!$reserve) {
        echo json_encode([
            'status' => false,
            'items' => [],
            'reserve' => null,
            'slip' => null,
            'message' => 'ไม่พบข้อมูลการจอง'
        ]);
        exit;
    }

    // รายการสินค้าในการจอง
    $stmtItems = $conn->prepare("
        SELECT 
            rd.rd_id,
            rd.reserve_id,
            rd.p_id,
            rd.qty,
            rd.price,
            rd.subtotal,
            p.p_name
        FROM tbl_reservation_detail rd
        LEFT JOIN tbl_product p ON rd.p_id = p.p_id
        WHERE rd.reserve_id = ?
        ORDER BY rd.rd_id ASC
    ");
    $stmtItems->execute([$reserve_id]);
    $items = $stmtItems->fetchAll(PDO::FETCH_ASSOC);

    // ดึงสลิปของการจองจาก tbl_payment_slip
    // note ของคุณเก็บรูปแบบ reserve_id:15
    $stmtSlip = $conn->prepare("
        SELECT 
            slip_id,
            member_id,
            payer_name,
            payer_phone,
            pay_amount,
            pay_datetime,
            slip_image,
            note,
            status,
            created_at
        FROM tbl_payment_slip
        WHERE note = ?
           OR note LIKE ?
        ORDER BY slip_id DESC
        LIMIT 1
    ");
    $stmtSlip->execute([
        'reserve_id:' . $reserve_id,
        'reserve_id:' . $reserve_id . '%'
    ]);
    $slip = $stmtSlip->fetch(PDO::FETCH_ASSOC);

    echo json_encode([
        'status' => true,
        'reserve' => $reserve,
        'items' => $items,
        'slip' => $slip ?: null
    ]);
} catch (Exception $e) {
    echo json_encode([
        'status' => false,
        'items' => [],
        'reserve' => null,
        'slip' => null,
        'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()
    ]);
}
