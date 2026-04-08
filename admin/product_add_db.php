<?php
if (isset($_POST['p_name'])) {
    include '../condb.php';
    header('Content-Type: application/json; charset=utf-8');

    $uploadErrors = [];

    try {
        $conn->beginTransaction();

        // รับค่า
        $p_name    = htmlspecialchars(trim($_POST['p_name']));
        $type_id   = (int)$_POST['type_id'];
        $p_detail  = htmlspecialchars(trim($_POST['p_detail']));
        $p_price   = (float)$_POST['p_price'];
        $p_qty     = (int)$_POST['p_qty'];
        $p_stock   = $p_qty; // ให้ stock เท่ากับจำนวนที่เพิ่ม
        $p_unit    = htmlspecialchars(trim($_POST['p_unit']));
        $p_status  = isset($_POST['p_status']) ? (int)$_POST['p_status'] : 1;
        $sale_type = isset($_POST['sale_type']) ? trim($_POST['sale_type']) : 'sale';

        // Insert product
        $stmt = $conn->prepare("
            INSERT INTO tbl_product
            (p_name, type_id, p_detail, p_price, p_qty, p_stock, p_unit, p_status, sale_type)
            VALUES
            (:p_name, :type_id, :p_detail, :p_price, :p_qty, :p_stock, :p_unit, :p_status, :sale_type)
        ");

        $stmt->execute([
            ':p_name'    => $p_name,
            ':type_id'   => $type_id,
            ':p_detail'  => $p_detail,
            ':p_price'   => $p_price,
            ':p_qty'     => $p_qty,
            ':p_stock'   => $p_stock,
            ':p_unit'    => $p_unit,
            ':p_status'  => $p_status,
            ':sale_type' => $sale_type
        ]);

        $last_id = $conn->lastInsertId();

        // Upload image
        if (!empty($_FILES['filepond']['name'][0])) {

            $path = __DIR__ . '/p_gallery/';
            if (!is_dir($path)) {
                mkdir($path, 0755, true);
            }

            foreach ($_FILES['filepond']['name'] as $i => $name) {

                $tmp  = $_FILES['filepond']['tmp_name'][$i];
                $size = $_FILES['filepond']['size'][$i];
                $err  = $_FILES['filepond']['error'][$i];

                if ($err !== UPLOAD_ERR_OK) {
                    $uploadErrors[] = "Upload error: {$name}";
                    continue;
                }

                if (!file_exists($tmp)) {
                    $uploadErrors[] = "File missing: {$name}";
                    continue;
                }

                $mime = mime_content_type($tmp);

                if (!in_array($mime, ['image/jpeg', 'image/png', 'image/webp'])) {
                    $uploadErrors[] = "Invalid type: {$name}";
                    continue;
                }

                if ($size > 5000000) {
                    $uploadErrors[] = "File too large: {$name}";
                    continue;
                }

                $ext = pathinfo($name, PATHINFO_EXTENSION);
                $newname = uniqid() . "_" . $i . "." . $ext;

                if (move_uploaded_file($tmp, $path . $newname)) {
                    $stmt_photo = $conn->prepare("
                        INSERT INTO tbl_img_detail (p_id, img)
                        VALUES (?, ?)
                    ");
                    $stmt_photo->execute([$last_id, $newname]);
                } else {
                    $uploadErrors[] = "Move failed: {$name}";
                }
            }
        }

        // ถ้ามี error → rollback
        if (!empty($uploadErrors)) {
            $conn->rollBack();
            echo json_encode([
                "status" => false,
                "message" => implode(", ", $uploadErrors)
            ]);
            exit;
        }

        $conn->commit();

        echo json_encode([
            "status" => true,
            "message" => "บันทึกเรียบร้อย"
        ]);
    } catch (Exception $e) {
        if ($conn->inTransaction()) {
            $conn->rollBack();
        }

        echo json_encode([
            "status" => false,
            "message" => $e->getMessage()
        ]);
    }

    $conn = null;
}
