<?php
session_start();
include '../condb.php';

header('Content-Type: application/json; charset=utf-8');

$slip_id = isset($_GET['slip_id']) ? (int)$_GET['slip_id'] : 0;

if ($slip_id <= 0) {
    echo json_encode([
        'status' => false,
        'sale' => null,
        'items' => [],
        'message' => 'ไม่พบรหัสการขาย'
    ]);
    exit;
}

try {
    // ข้อมูลหัวบิลการขาย
    $stmtSale = $conn->prepare("
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
        WHERE slip_id = ?
        LIMIT 1
    ");
    $stmtSale->execute([$slip_id]);
    $sale = $stmtSale->fetch(PDO::FETCH_ASSOC);

    if (!$sale) {
        echo json_encode([
            'status' => false,
            'sale' => null,
            'items' => [],
            'message' => 'ไม่พบข้อมูลการขาย'
        ]);
        exit;
    }

    // รายการสินค้าในใบขาย
    $stmtItems = $conn->prepare("
        SELECT 
            sd.sd_id,
            sd.slip_id,
            sd.p_id,
            sd.qty,
            sd.price,
            sd.subtotal,
            p.p_name,
            p.p_unit
        FROM tbl_sale_detail sd
        LEFT JOIN tbl_product p ON sd.p_id = p.p_id
        WHERE sd.slip_id = ?
        ORDER BY sd.sd_id ASC
    ");
    $stmtItems->execute([$slip_id]);
    $items = $stmtItems->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'status' => true,
        'sale' => $sale,
        'items' => $items,
        'items_count' => count($items),
        'message' => count($items) > 0 ? 'พบรายการสินค้า' : 'ยังไม่มีข้อมูลสินค้าใน tbl_sale_detail ของ slip นี้'
    ]);
} catch (Exception $e) {
    echo json_encode([
        'status' => false,
        'sale' => null,
        'items' => [],
        'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()
    ]);
}
