<?php
session_start();
include_once '../condb.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: promotion.php');
    exit;
}

$promo_id     = isset($_POST['promo_id']) ? (int)$_POST['promo_id'] : 0;
$promo_name   = trim($_POST['promo_name'] ?? '');
$promo_type   = trim($_POST['promo_type'] ?? '');
$promo_value  = (float)($_POST['promo_value'] ?? 0);
$min_order    = (float)($_POST['min_order'] ?? 0);
$start_date   = trim($_POST['start_date'] ?? '');
$end_date     = trim($_POST['end_date'] ?? '');
$promo_detail = trim($_POST['promo_detail'] ?? '');
$promo_status = isset($_POST['promo_status']) ? (int)$_POST['promo_status'] : 1;
$apply_type   = trim($_POST['apply_type'] ?? 'product');
$product_ids  = $_POST['product_ids'] ?? [];

if (!is_array($product_ids)) {
    $product_ids = [];
}

$product_ids = array_map('intval', $product_ids);
$product_ids = array_filter($product_ids, function ($id) {
    return $id > 0;
});
$product_ids = array_values(array_unique($product_ids));

if (
    $promo_id <= 0 ||
    $promo_name === '' ||
    !in_array($promo_type, ['percent', 'amount'], true) ||
    $promo_value <= 0 ||
    $start_date === '' ||
    $end_date === '' ||
    !in_array($apply_type, ['all', 'product'], true)
) {
    echo "<script>alert('ข้อมูลไม่ครบหรือไม่ถูกต้อง'); window.history.back();</script>";
    exit;
}

if (!strtotime($start_date) || !strtotime($end_date)) {
    echo "<script>alert('รูปแบบวันที่ไม่ถูกต้อง'); window.history.back();</script>";
    exit;
}

if (strtotime($start_date) > strtotime($end_date)) {
    echo "<script>alert('วันที่เริ่มต้องไม่มากกว่าวันที่สิ้นสุด'); window.history.back();</script>";
    exit;
}

if ($apply_type === 'product' && empty($product_ids)) {
    echo "<script>alert('กรุณาเลือกสินค้าอย่างน้อย 1 รายการ'); window.history.back();</script>";
    exit;
}

try {
    $conn->beginTransaction();

    $stmtCheck = $conn->prepare("
        SELECT promo_id
        FROM tbl_promotion
        WHERE promo_id = ?
        LIMIT 1
    ");
    $stmtCheck->execute([$promo_id]);

    if (!$stmtCheck->fetch(PDO::FETCH_ASSOC)) {
        throw new Exception('ไม่พบข้อมูลโปรโมชั่นที่ต้องการแก้ไข');
    }

    $stmt = $conn->prepare("
        UPDATE tbl_promotion
        SET promo_name = ?,
            promo_type = ?,
            promo_value = ?,
            min_order = ?,
            start_date = ?,
            end_date = ?,
            promo_detail = ?,
            promo_status = ?,
            apply_type = ?
        WHERE promo_id = ?
    ");

    $stmt->execute([
        $promo_name,
        $promo_type,
        $promo_value,
        $min_order,
        $start_date,
        $end_date,
        $promo_detail,
        $promo_status,
        $apply_type,
        $promo_id
    ]);

    $stmtDel = $conn->prepare("
        DELETE FROM tbl_promotion_product
        WHERE promo_id = ?
    ");
    $stmtDel->execute([$promo_id]);

    if ($apply_type === 'product') {
        $stmtLink = $conn->prepare("
            INSERT INTO tbl_promotion_product (promo_id, p_id)
            VALUES (?, ?)
        ");

        foreach ($product_ids as $p_id) {
            $stmtLink->execute([$promo_id, $p_id]);
        }
    }

    $conn->commit();
    echo "<script>alert('แก้ไขโปรโมชั่นเรียบร้อยแล้ว'); window.location='promotion.php';</script>";
    exit;
} catch (Exception $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }

    echo "<script>alert('เกิดข้อผิดพลาด: " . addslashes($e->getMessage()) . "'); window.history.back();</script>";
    exit;
}