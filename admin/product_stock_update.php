<?php
header('Content-Type: application/json; charset=utf-8');
include '../condb.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => false, 'msg' => 'Invalid method']);
    exit;
}

$p_id = isset($_POST['p_id']) ? intval($_POST['p_id']) : 0;
$p_qty = isset($_POST['p_qty']) ? intval($_POST['p_qty']) : 0;

if ($p_id <= 0) {
    echo json_encode(['status' => false, 'msg' => 'Invalid product']);
    exit;
}

try {
    $stmt = $conn->prepare("UPDATE tbl_product SET p_qty = :p_qty WHERE p_id = :p_id");
    $stmt->execute([':p_qty' => $p_qty, ':p_id' => $p_id]);
    echo json_encode(['status' => true]);
} catch (Exception $e) {
    echo json_encode(['status' => false, 'msg' => $e->getMessage()]);
}

$conn = null;
