<?php
session_start();
include '../condb.php';

$reserve_id = isset($_GET['reserve_id']) ? (int)$_GET['reserve_id'] : 0;
$slip_id = isset($_GET['slip_id']) ? (int)$_GET['slip_id'] : 0;
$status = isset($_GET['status']) ? trim($_GET['status']) : '';

$allowStatus = ['pending', 'received', 'cancelled'];

if (($reserve_id <= 0 && $slip_id <= 0) || !in_array($status, $allowStatus)) {
    $_SESSION['error'] = 'ข้อมูลไม่ถูกต้อง';
    header('Location: orders.php?tab=all');
    exit;
}

try {
    if ($reserve_id > 0) {
        $stmt = $conn->prepare("UPDATE tbl_reservation SET pickup_status = ? WHERE reserve_id = ?");
        $stmt->execute([$status, $reserve_id]);
        $_SESSION['success'] = 'อัปเดตสถานะการรับสินค้า (การจอง) เรียบร้อยแล้ว';
        header('Location: orders.php?tab=reservation');
        exit;
    }

    if ($slip_id > 0) {
        $stmt = $conn->prepare("UPDATE tbl_payment_slip SET status = ? WHERE slip_id = ?");
        $stmt->execute([$status, $slip_id]);
        $_SESSION['success'] = 'อัปเดตสถานะการรับสินค้า (การขาย) เรียบร้อยแล้ว';
        header('Location: orders.php?tab=sale');
        exit;
    }
} catch (Exception $e) {
    $_SESSION['error'] = 'ไม่สามารถอัปเดตสถานะได้';
}

header('Location: orders.php?tab=all');
exit;
