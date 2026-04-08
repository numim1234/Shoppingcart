<?php
header('Content-Type: application/json; charset=utf-8');
include '../condb.php';

try {
    if (
        !isset($_POST['p_id']) ||
        !isset($_POST['p_name']) ||
        !isset($_POST['type_id'])
    ) {
        echo json_encode([
            'status' => false,
            'message' => 'ข้อมูลไม่ครบ'
        ]);
        exit;
    }

    $p_id      = (int)$_POST['p_id'];
    $p_name    = trim($_POST['p_name']);
    $type_id   = (int)$_POST['type_id'];
    $p_detail  = trim($_POST['p_detail'] ?? '');
    $p_price   = (float)($_POST['p_price'] ?? 0);
    $p_qty     = (int)($_POST['p_qty'] ?? 0);
    $p_stock   = $p_qty; // ให้สต็อกตรงกับจำนวนที่แก้
    $p_unit    = trim($_POST['p_unit'] ?? '');
    $p_status  = isset($_POST['p_status']) ? (int)$_POST['p_status'] : 1;
    $sale_type = isset($_POST['sale_type']) ? trim($_POST['sale_type']) : 'sale';

    // path เก็บรูป
    $uploadDir = __DIR__ . '/p_gallery/';

    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    // 1) อัปเดตข้อมูลสินค้า
    $sql = "UPDATE tbl_product SET
                p_name = :p_name,
                type_id = :type_id,
                p_detail = :p_detail,
                p_price = :p_price,
                p_qty = :p_qty,
                p_stock = :p_stock,
                p_unit = :p_unit,
                p_status = :p_status,
                sale_type = :sale_type
            WHERE p_id = :p_id";

    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':p_name', $p_name);
    $stmt->bindParam(':type_id', $type_id, PDO::PARAM_INT);
    $stmt->bindParam(':p_detail', $p_detail);
    $stmt->bindParam(':p_price', $p_price);
    $stmt->bindParam(':p_qty', $p_qty, PDO::PARAM_INT);
    $stmt->bindParam(':p_stock', $p_stock, PDO::PARAM_INT);
    $stmt->bindParam(':p_unit', $p_unit);
    $stmt->bindParam(':p_status', $p_status, PDO::PARAM_INT);
    $stmt->bindParam(':sale_type', $sale_type);
    $stmt->bindParam(':p_id', $p_id, PDO::PARAM_INT);
    $stmt->execute();

    // 2) ถ้ามีการอัปโหลดรูปใหม่
    if (!empty($_FILES['filepond']['name'][0])) {

        // ดึงรูปเก่าจาก tbl_img_detail
        $stmtOld = $conn->prepare("SELECT img FROM tbl_img_detail WHERE p_id = ?");
        $stmtOld->execute([$p_id]);
        $oldImages = $stmtOld->fetchAll(PDO::FETCH_ASSOC);

        // ลบไฟล์รูปเก่าในโฟลเดอร์
        foreach ($oldImages as $old) {
            if (!empty($old['img'])) {
                $oldPath = $uploadDir . $old['img'];
                if (file_exists($oldPath)) {
                    unlink($oldPath);
                }
            }
        }

        // ลบข้อมูลรูปเก่าในฐานข้อมูล
        $stmtDelete = $conn->prepare("DELETE FROM tbl_img_detail WHERE p_id = ?");
        $stmtDelete->execute([$p_id]);

        // อัปโหลดรูปใหม่
        $totalFiles = count($_FILES['filepond']['name']);

        for ($i = 0; $i < $totalFiles; $i++) {
            $fileName = $_FILES['filepond']['name'][$i];
            $tmpName  = $_FILES['filepond']['tmp_name'][$i];
            $error    = $_FILES['filepond']['error'][$i];

            if ($error !== 0 || empty($fileName)) {
                continue;
            }

            $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            $allow = ['jpg', 'jpeg', 'png', 'webp'];

            if (!in_array($ext, $allow)) {
                continue;
            }

            $newName = 'product_' . $p_id . '_' . time() . '_' . $i . '.' . $ext;
            $targetPath = $uploadDir . $newName;

            if (move_uploaded_file($tmpName, $targetPath)) {
                $stmtImg = $conn->prepare("INSERT INTO tbl_img_detail (p_id, img) VALUES (?, ?)");
                $stmtImg->execute([$p_id, $newName]);
            }
        }
    }

    echo json_encode([
        'status' => true,
        'message' => 'บันทึกข้อมูลสำเร็จ'
    ]);
} catch (Exception $e) {
    echo json_encode([
        'status' => false,
        'message' => $e->getMessage()
    ]);
}
