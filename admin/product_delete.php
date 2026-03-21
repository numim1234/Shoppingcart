<?php
header('Content-Type: application/json; charset=utf-8');
if (isset($_POST['p_id'])) {
    include '../condb.php';

    $p_id = (int)$_POST['p_id'];

    try {
        // ดึงชื่อรูปทั้งหมดของสินค้านี้
        $stmt = $conn->prepare("SELECT img FROM tbl_img_detail WHERE p_id = :p_id");
        $stmt->bindParam(':p_id', $p_id, PDO::PARAM_INT);
        $stmt->execute();
        $imgs = $stmt->fetchAll(PDO::FETCH_COLUMN);

        // ลบไฟล์รูปจากโฟลเดอร์ p_gallery
        $path = __DIR__ . DIRECTORY_SEPARATOR . 'p_gallery' . DIRECTORY_SEPARATOR;
        foreach ($imgs as $img_name) {
            if ($img_name) {
                $file_path = $path . $img_name;
                if (file_exists($file_path)) {
                    @unlink($file_path);
                }
            }
        }

        // เริ่ม Transaction เพื่อความปลอดภัย
        $conn->beginTransaction();

        // ลบข้อมูลรูปภาพในตาราง
        $stmt_del_imgs = $conn->prepare("DELETE FROM tbl_img_detail WHERE p_id = :p_id");
        $stmt_del_imgs->bindParam(':p_id', $p_id, PDO::PARAM_INT);
        $stmt_del_imgs->execute();

        // ลบข้อมูลสินค้าจาก tbl_product
        $stmt_del = $conn->prepare("DELETE FROM tbl_product WHERE p_id = :p_id");
        $stmt_del->bindParam(':p_id', $p_id, PDO::PARAM_INT);
        $stmt_del->execute();

        $conn->commit();

        echo json_encode(array('status' => true, 'message' => 'ลบสินค้าพร้อมรูปภาพเรียบร้อย'));
    } catch (Exception $e) {
        if ($conn->inTransaction()) {
            $conn->rollBack();
        }
        echo json_encode(array('status' => false, 'message' => $e->getMessage()));
    }

    $conn = null;
    exit;
}

echo json_encode(array('status' => false, 'message' => 'ไม่มีข้อมูลที่ส่งมา'));
