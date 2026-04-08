<?php
session_start();
require_once("condb.php");

$p_id = isset($_POST['p_id']) ? (int)$_POST['p_id'] : (isset($_GET['p_id']) ? (int)$_GET['p_id'] : 0);
$qty  = isset($_POST['qty']) ? (int)$_POST['qty'] : 1;

if ($p_id <= 0) {
    $_SESSION['error'] = 'ข้อมูลสินค้าไม่ถูกต้อง';
    header("Location: index.php");
    exit();
}

if ($qty < 1) {
    $qty = 1;
}

// ดึงข้อมูลสินค้า
$stmt = $conn->prepare("
    SELECT p.*, t.type_name
    FROM tbl_product p
    LEFT JOIN tbl_type t ON p.type_id = t.type_id
    WHERE p.p_id = ?
    LIMIT 1
");
$stmt->execute([$p_id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    $_SESSION['error'] = 'ไม่พบสินค้า';
    header("Location: index.php");
    exit();
}

/*
|------------------------------------------------------------
| กันสินค้าพรีออเดอร์ไม่ให้เข้าตะกร้าปกติ
|------------------------------------------------------------
*/
if (($product['sale_type'] ?? 'sale') === 'preorder') {
    $_SESSION['error'] = 'สินค้านี้เป็นพรีออเดอร์ กรุณาจองล่วงหน้า';
    header("Location: index.php");
    exit();
}

/*
|------------------------------------------------------------
| เช็กสต็อกสินค้าพร้อมขาย
|------------------------------------------------------------
*/
$current_stock = (int)($product['p_stock'] ?? 0);

if ($current_stock <= 0) {
    $_SESSION['error'] = 'สินค้าหมด';
    header("Location: index.php");
    exit();
}

// ดึงรูปแรกจาก tbl_img_detail ก่อน
$imgFile = '';
$stmtImg = $conn->prepare("
    SELECT img
    FROM tbl_img_detail
    WHERE p_id = ?
    ORDER BY id DESC
    LIMIT 1
");
$stmtImg->execute([$p_id]);
$detailImg = $stmtImg->fetchColumn();

if (!empty($detailImg)) {
    $imgFile = trim($detailImg);
} elseif (!empty($product['img'])) {
    $imgFile = trim($product['img']);
}

$imgPath = 'admin/p_gallery/no-image.png';
if ($imgFile !== '' && file_exists(__DIR__ . '/admin/p_gallery/' . $imgFile)) {
    $imgPath = 'admin/p_gallery/' . $imgFile;
}

if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// ถ้ามีสินค้าเดิมอยู่แล้ว ให้รวมจำนวนเดิมในตะกร้า
$oldQty = isset($_SESSION['cart'][$p_id]) ? (int)$_SESSION['cart'][$p_id]['qty'] : 0;
$newQty = $oldQty + $qty;

// กันจำนวนเกินสต็อก
if ($newQty > $current_stock) {
    $_SESSION['error'] = 'เพิ่มสินค้าเกินสต็อกไม่ได้ (คงเหลือ ' . $current_stock . ' ชิ้น)';
    header("Location: index.php");
    exit();
}

// เพิ่ม/อัปเดตสินค้าในตะกร้า
$_SESSION['cart'][$p_id] = [
    'p_id'       => (int)$product['p_id'],
    'p_name'     => $product['p_name'],
    'p_price'    => (float)$product['p_price'],
    'qty'        => $newQty,
    'type_name'  => $product['type_name'] ?? '',
    'img'        => $imgPath,
    'order_type' => 'sale',
    'deposit'    => 0,
    'remain'     => 0
];

$_SESSION['success'] = 'เพิ่มสินค้าลงตะกร้าเรียบร้อยแล้ว';
header("Location: cart.php");
exit();
