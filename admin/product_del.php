<?php
// ตรวจสอบว่ามีการส่งค่า id มาหรือไม่
if (isset($_POST['id'])) {
    include '../condb.php';

    // 1. ลบไฟล์จริงออกจาก Folder (p_gallery)
    $img_name = $_POST['img_name'];
    $path = "../p_gallery/";
    $file_path = $path . $img_name;

    if (file_exists($file_path)) {
        unlink($file_path); // คำสั่งลบไฟล์จริง
    }

    // 2. ลบข้อมูลออกจากฐานข้อมูล tbl_img_detail
    $id = (int)$_POST['id'];
    $stmt_del = $conn->prepare("DELETE FROM tbl_img_detail WHERE id = :id");
    $stmt_del->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt_del->execute();

    // 3. ส่งผลลัพธ์กลับในรูปแบบ JSON
    if ($stmt_del) {
        echo json_encode(array("status" => true, "message" => "ลบรูปภาพสำเร็จ"));
    } else {
        echo json_encode(array("status" => false, "message" => "เกิดข้อผิดพลาด"));
    }

    $conn = null;
}
