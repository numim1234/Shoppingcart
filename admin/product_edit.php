<?php
include '../condb.php';

// ดึงประเภทสินค้า
$stmtType = $conn->prepare("SELECT * FROM tbl_type ORDER BY type_id ASC");
$stmtType->execute();
$resultType = $stmtType->fetchAll();

// ตรวจสอบ p_id
if (isset($_GET['p_id'])) {
    $stmt_pro = $conn->prepare("
        SELECT * FROM tbl_product p
        INNER JOIN tbl_type t ON p.type_id = t.type_id
        WHERE p.p_id=?
    ");
    $stmt_pro->execute([$_GET['p_id']]);
    $row_prod = $stmt_pro->fetch(PDO::FETCH_ASSOC);

    $stmt_img = $conn->prepare("SELECT * FROM tbl_img_detail WHERE p_id=?");
    $stmt_img->execute([$_GET['p_id']]);
    $result_img = $stmt_img->fetchAll();

    if (!$row_prod) {
        header("Location: product.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>แก้ไขสินค้า</title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        body {
            background: #fdf6ee;
            font-family: 'Segoe UI', sans-serif;
        }

        .card-custom {
            background: #fffaf3;
            border: none;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
        }

        .title {
            color: #6b4f3b;
            font-weight: 600;
        }

        label {
            color: #5a4634;
            font-weight: 500;
        }

        .form-control {
            border-radius: 10px;
            border: 1px solid #e8dcd1;
        }

        .btn-primary {
            background: #c8a27c;
            border: none;
            border-radius: 10px;
        }

        .btn-primary:hover {
            background: #b68f6a;
        }

        /* upload */
        .upload-box {
            border: 2px dashed #e0cbb5;
            border-radius: 15px;
            background: #fffaf3;
            cursor: pointer;
            transition: .3s;
        }

        .upload-box:hover {
            background: #fdf1e6;
        }

        .upload-icon {
            font-size: 30px;
        }

        /* preview */
        .preview-item {
            width: 120px;
            height: 100px;
            margin: 5px;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .preview-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
    </style>
</head>

<body>

    <div class="container mt-5">
        <div class="card card-custom p-4">

            <h4 class="title mb-4">🧁 แก้ไขสินค้า</h4>

            <form id="filepond-form" enctype="multipart/form-data">

                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label>สินค้า</label>
                        <input type="text" name="p_name" value="<?= $row_prod['p_name']; ?>" class="form-control"
                            required>
                    </div>

                    <div class="form-group col-md-6">
                        <label>ประเภทสินค้า</label>
                        <select name="type_id" class="form-control" required>
                            <?php foreach ($resultType as $t) { ?>
                                <option value="<?= $t['type_id']; ?>"
                                    <?= $t['type_id'] == $row_prod['type_id'] ? 'selected' : '' ?>>
                                    <?= $t['type_name']; ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label>รายละเอียดสินค้า</label>
                    <textarea name="p_detail" class="form-control"><?= $row_prod['p_detail']; ?></textarea>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label>ราคา</label>
                        <input type="number" name="p_price" value="<?= $row_prod['p_price']; ?>" class="form-control">
                    </div>

                    <div class="form-group col-md-4">
                        <label>จำนวน</label>
                        <input type="number" name="p_qty" value="<?= $row_prod['p_qty']; ?>" class="form-control">
                    </div>

                    <div class="form-group col-md-4">
                        <label>หน่วย</label>
                        <select name="p_unit" class="form-control">
                            <option <?= $row_prod['p_unit'] == "ชิ้น" ? "selected" : "" ?>>ชิ้น</option>
                            <option <?= $row_prod['p_unit'] == "กล่อง" ? "selected" : "" ?>>กล่อง</option>
                            <option <?= $row_prod['p_unit'] == "ชุด" ? "selected" : "" ?>>ชุด</option>
                        </select>
                    </div>
                </div>

                <!-- upload -->
                <div class="form-group">
                    <label>เพิ่มรูปสินค้า</label>

                    <div id="uploadBox" class="upload-box text-center p-4">
                        <div class="upload-icon">📷</div>
                        <p>ลากรูป หรือคลิกเลือก</p>
                        <input type="file" id="fileInput" name="filepond[]" multiple accept="image/*" hidden>
                    </div>

                    <div id="previewContainer" class="d-flex flex-wrap mt-3"></div>
                </div>

                <!-- รูปเดิม -->
                <?php if (!empty($result_img)) { ?>
                    <div class="form-group">
                        <label>รูปภาพปัจจุบัน</label>
                        <div class="d-flex flex-wrap">
                            <?php foreach ($result_img as $img) { ?>
                                <div class="preview-item">
                                    <img src="p_gallery/<?= $img['img']; ?>">
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                <?php } ?>

                <input type="hidden" name="p_id" value="<?= $row_prod['p_id']; ?>">

                <div class="mt-4">
                    <button type="submit" id="btnSubmit" class="btn btn-primary">💾 บันทึก</button>
                    <a href="product.php" class="btn btn-secondary">ย้อนกลับ</a>
                </div>

            </form>
        </div>
    </div>

    <script>
        // upload click
        const uploadBox = document.getElementById("uploadBox");
        const fileInput = document.getElementById("fileInput");

        uploadBox.addEventListener("click", () => fileInput.click());

        uploadBox.addEventListener("dragover", (e) => {
            e.preventDefault();
            uploadBox.style.background = "#f3e5d7";
        });

        uploadBox.addEventListener("dragleave", () => {
            uploadBox.style.background = "#fffaf3";
        });

        uploadBox.addEventListener("drop", (e) => {
            e.preventDefault();
            fileInput.files = e.dataTransfer.files;
            fileInput.dispatchEvent(new Event("change"));
        });

        // preview
        fileInput.addEventListener("change", function() {
            const container = document.getElementById("previewContainer");
            container.innerHTML = "";

            Array.from(this.files).forEach(file => {
                if (!file.type.startsWith("image/")) return;

                const reader = new FileReader();
                reader.onload = e => {
                    const div = document.createElement("div");
                    div.className = "preview-item";
                    div.innerHTML = `<img src="${e.target.result}">`;
                    container.appendChild(div);
                }
                reader.readAsDataURL(file);
            });
        });

        // submit ajax
        $("#btnSubmit").click(function(e) {
            e.preventDefault();

            var formData = new FormData($("#filepond-form")[0]);

            $.ajax({
                url: 'product_edit_db.php',
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',
                success: function(res) {
                    if (res.status) {
                        Swal.fire({
                            icon: 'success',
                            title: res.message,
                            timer: 1200,
                            showConfirmButton: false
                        }).then(() => {
                            window.location = "product.php";
                        });
                    } else {
                        Swal.fire('ผิดพลาด', res.message, 'error');
                    }
                }
            });
        });
    </script>

</body>

</html>