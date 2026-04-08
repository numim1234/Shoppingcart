<?php
session_start();
require_once("condb.php");

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: reserve_product.php");
    exit();
}

$reserve_name  = trim($_POST['reserve_name'] ?? '');
$reserve_phone = trim($_POST['reserve_phone'] ?? '');
$pickup_date   = $_POST['pickup_date'] ?? '';
$pickup_time   = $_POST['pickup_time'] ?? '';
$reserve_note  = trim($_POST['reserve_note'] ?? '');
$selected_products = $_POST['selected_products'] ?? [];
$qtys = $_POST['qty'] ?? [];

if ($reserve_name === '' || $reserve_phone === '' || $pickup_date === '' || $pickup_time === '') {
    $_SESSION['error'] = 'กรุณากรอกข้อมูลให้ครบ';
    header("Location: reserve_product.php");
    exit();
}

if (empty($selected_products)) {
    $_SESSION['error'] = 'กรุณาเลือกสินค้าอย่างน้อย 1 รายการ';
    header("Location: reserve_product.php");
    exit();
}

$total_amount = 0;
$orderItems = [];

foreach ($selected_products as $p_id) {
    $p_id = (int)$p_id;
    $qty = isset($qtys[$p_id]) ? (int)$qtys[$p_id] : 0;

    if ($qty <= 0) {
        continue;
    }

    $stmt = $conn->prepare("SELECT p_id, p_name, p_price FROM tbl_product WHERE p_id = ?");
    $stmt->execute([$p_id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($product) {
        $price = (float)$product['p_price'];
        $subtotal = $price * $qty;
        $total_amount += $subtotal;

        $orderItems[] = [
            'p_id' => $p_id,
            'qty' => $qty,
            'price' => $price,
            'subtotal' => $subtotal
        ];
    }
}

if (empty($orderItems)) {
    $_SESSION['error'] = 'กรุณาระบุจำนวนสินค้าให้ถูกต้อง';
    header("Location: reserve_product.php");
    exit();
}

// มัดจำ 50%
$deposit_amount = $total_amount * 0.5;

try {
    $conn->beginTransaction();

    $m_id = $_SESSION['m_id'] ?? null;

    $stmt = $conn->prepare("
        INSERT INTO tbl_reservation
        (m_id, reserve_name, reserve_phone, pickup_date, pickup_time, reserve_note, total_amount, deposit_amount, payment_status, reserve_status)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending', 'pending')
    ");
    $stmt->execute([
        $m_id,
        $reserve_name,
        $reserve_phone,
        $pickup_date,
        $pickup_time,
        $reserve_note,
        $total_amount,
        $deposit_amount
    ]);

    $reserve_id = $conn->lastInsertId();

    $stmtDetail = $conn->prepare("
        INSERT INTO tbl_reservation_detail
        (reserve_id, p_id, qty, price, subtotal)
        VALUES (?, ?, ?, ?, ?)
    ");

    foreach ($orderItems as $item) {
        $stmtDetail->execute([
            $reserve_id,
            $item['p_id'],
            $item['qty'],
            $item['price'],
            $item['subtotal']
        ]);
    }

    $conn->commit();

    $_SESSION['reserve_id'] = $reserve_id;
    $_SESSION['reserve_payment_amount'] = $deposit_amount;
    $_SESSION['success'] = 'บันทึกการจองเรียบร้อย กรุณาชำระมัดจำ';

    // เด้งกลับไป cart แล้วเปิด modal QR ตัวเดิม
    header("Location: payment_form.php?reserve_id=" . $reserve_id . "&show_qr=1");
    exit();
} catch (Exception $e) {
    $conn->rollBack();
    $_SESSION['error'] = 'เกิดข้อผิดพลาดในการบันทึกการจอง';
    header("Location: reserve_product.php");
    exit();
}
