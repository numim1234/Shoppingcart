<?php
session_start();
include_once '../condb.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

$promo_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($promo_id <= 0) {
    echo "<script>alert('ไม่พบรหัสโปรโมชั่น'); window.location='promotion.php';</script>";
    exit;
}

try {
    $conn->beginTransaction();

    $stmtCheck = $conn->prepare("
        SELECT promo_id, promo_name
        FROM tbl_promotion
        WHERE promo_id = ?
        LIMIT 1
    ");
    $stmtCheck->execute([$promo_id]);
    $promo = $stmtCheck->fetch(PDO::FETCH_ASSOC);

    if (!$promo) {
        throw new Exception('ไม่พบข้อมูลโปรโมชั่น');
    }

    $stmtDelLink = $conn->prepare("
        DELETE FROM tbl_promotion_product
        WHERE promo_id = ?
    ");
    $stmtDelLink->execute([$promo_id]);

    $stmtDelPromo = $conn->prepare("
        DELETE FROM tbl_promotion
        WHERE promo_id = ?
    ");
    $stmtDelPromo->execute([$promo_id]);

    $conn->commit();

    echo "<script>alert('ลบโปรโมชั่นเรียบร้อยแล้ว'); window.location='promotion.php';</script>";
    exit;
} catch (Exception $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }

    echo "<script>alert('เกิดข้อผิดพลาด: " . addslashes($e->getMessage()) . "'); window.location='promotion.php';</script>";
    exit;
}