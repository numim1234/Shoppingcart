<?php
header('Content-Type: application/json; charset=utf-8');
include '../condb.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => false, 'msg' => 'Invalid method']);
    exit;
}

$p_id = isset($_POST['p_id']) ? intval($_POST['p_id']) : 0;
$p_stock = isset($_POST['p_stock']) ? intval($_POST['p_stock']) : -1;

if ($p_id <= 0) {
    echo json_encode(['status' => false, 'msg' => 'Invalid product']);
    exit;
}

if ($p_stock < 0) {
    echo json_encode(['status' => false, 'msg' => 'จำนวนสต็อกต้องมากกว่าหรือเท่ากับ 0']);
    exit;
}

try {
    $stmt = $conn->prepare("UPDATE tbl_product SET p_stock = :p_stock WHERE p_id = :p_id");
    $stmt->execute([
        ':p_stock' => $p_stock,
        ':p_id' => $p_id
    ]);

    echo json_encode(['status' => true, 'msg' => 'อัปเดตสต็อกเรียบร้อย']);
} catch (Exception $e) {
    echo json_encode(['status' => false, 'msg' => $e->getMessage()]);
}

$conn = null;
