<?php
header('Content-Type: application/json');

$response = ["status" => false, "message" => "error"];

try {

    if (!isset($_POST['p_id'])) {
        throw new Exception("ไม่มี p_id");
    }

    include '../condb.php';

    $conn->beginTransaction(); // ⭐ สำคัญ

    $p_id    = (int)$_POST['p_id'];
    $p_name  = trim($_POST['p_name']);
    $type_id = (int)$_POST['type_id'];
    $p_detail = trim($_POST['p_detail']);
    $p_price = (int)$_POST['p_price'];
    $p_qty   = (int)$_POST['p_qty'];
    $p_unit  = trim($_POST['p_unit']);

    // =====================
    // UPDATE PRODUCT
    // =====================
    $stmt = $conn->prepare("
        UPDATE tbl_product SET 
            p_name = ?, 
            type_id = ?, 
            p_detail = ?, 
            p_price = ?, 
            p_qty = ?, 
            p_unit = ?
        WHERE p_id = ?
    ");

    $stmt->execute([
        $p_name,
        $type_id,
        $p_detail,
        $p_price,
        $p_qty,
        $p_unit,
        $p_id
    ]);

    // =====================
    // UPLOAD IMAGE
    // =====================
    if (!empty($_FILES['filepond']['name'][0])) {

        $path = __DIR__ . '/../p_gallery/';

        if (!is_dir($path)) {
            mkdir($path, 0755, true);
        }

        foreach ($_FILES['filepond']['name'] as $i => $name) {

            $tmp  = $_FILES['filepond']['tmp_name'][$i];
            $size = $_FILES['filepond']['size'][$i];
            $err  = $_FILES['filepond']['error'][$i];

            // ⭐ เช็ค error
            if ($err !== UPLOAD_ERR_OK) continue;

            if (!file_exists($tmp)) continue;

            $mime = mime_content_type($tmp);

            if (!in_array($mime, ['image/jpeg', 'image/png'])) continue;

            if ($size > 5000000) continue;

            // ⭐ กันชื่อชน + ปลอดภัย
            $ext = pathinfo($name, PATHINFO_EXTENSION);
            $newname = uniqid() . "_" . $i . "." . $ext;

            if (move_uploaded_file($tmp, $path . $newname)) {

                $stmt2 = $conn->prepare("
                    INSERT INTO tbl_img_detail (p_id, img)
                    VALUES (?, ?)
                ");
                $stmt2->execute([$p_id, $newname]);
            }
        }
    }

    $conn->commit(); // ⭐

    $response["status"] = true;
    $response["message"] = "แก้ไขข้อมูลเรียบร้อย";
} catch (Exception $e) {

    if (isset($conn)) {
        $conn->rollBack(); // ⭐ rollback ถ้าพัง
    }

    $response["message"] = $e->getMessage();
}

echo json_encode($response);
exit;
