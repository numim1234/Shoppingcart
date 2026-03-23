<?php
include '../condb.php';
$stmtType = $conn->prepare("SELECT * FROM tbl_type ORDER BY type_id ASC");
$stmtType->execute();
$resultType = $stmtType->fetchAll();
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>เพิ่มสินค้า</title>

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
        font-weight: 600;
        color: #6b4f3b;
    }

    label {
        font-weight: 500;
        color: #5a4634;
    }

    .form-control {
        border-radius: 10px;
        border: 1px solid #e8dcd1;
        background: #fff;
    }

    .form-control:focus {
        border-color: #c8a27c;
        box-shadow: none;
    }

    .btn-primary {
        background: #c8a27c;
        border: none;
        border-radius: 10px;
    }

    .btn-primary:hover {
        background: #b68f6a;
    }

    .btn-secondary {
        border-radius: 10px;
    }

    /* preview */
    .preview-item {
        width: 150px;
        height: 100px;
        border-radius: 10px;
        overflow: hidden;
        position: relative;
        margin: 5px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }

    .preview-item img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .remove-btn {
        position: absolute;
        top: 5px;
        right: 5px;
        background: #fff;
        border: none;
        border-radius: 50%;
        padding: 3px 7px;
        font-size: 12px;
        cursor: pointer;
    }

    .upload-box {
        border: 2px dashed #e0cbb5;
        border-radius: 15px;
        background: #fffaf3;
        cursor: pointer;
        transition: 0.3s;
    }

    .upload-box:hover {
        background: #fdf1e6;
        border-color: #c8a27c;
    }

    .upload-icon {
        font-size: 32px;
        margin-bottom: 5px;
    }

    .upload-box p {
        color: #6b4f3b;
        font-weight: 500;
        margin: 0;
    }

    .upload-box small {
        color: #a1887f;
    }

    /* preview animation */
    .preview-item {
        transition: 0.3s;
    }

    .preview-item:hover {
        transform: scale(1.05);
    }
    </style>
</head>

<body>

    <div class="container mt-5">
        <div class="card card-custom p-4">
            <h4 class="title mb-4">🍞 เพิ่มสินค้า (Bakery)</h4>

            <form id="filepond-form" enctype="multipart/form-data">

                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label>ชื่อสินค้า</label>
                        <input type="text" name="p_name" class="form-control" required>
                    </div>

                    <div class="form-group col-md-6">
                        <label>ประเภทสินค้า</label>
                        <select name="type_id" class="form-control" required>
                            <option disabled selected>เลือกประเภท</option>
                            <?php foreach ($resultType as $row_type) { ?>
                            <option value="<?= $row_type['type_id']; ?>">
                                <?= $row_type['type_name']; ?>
                            </option>
                            <?php } ?>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label>รายละเอียดสินค้า</label>
                    <textarea name="p_detail" class="form-control" rows="3" required></textarea>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label>ราคา</label>
                        <input type="number" name="p_price" class="form-control">
                    </div>

                    <div class="form-group col-md-4">
                        <label>จำนวน</label>
                        <input type="number" name="p_qty" class="form-control">
                    </div>

                    <div class="form-group col-md-4">
                        <label>หน่วย</label>
                        <select name="p_unit" class="form-control" required>
                            <option disabled selected>เลือกหน่วย</option>
                            <option>ชิ้น</option>
                            <option>กล่อง</option>
                            <option>ชุด</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label>รูปสินค้า</label>

                    <div id="uploadBox" class="upload-box text-center p-4">
                        <div class="upload-icon">📷</div>
                        <p class="mb-1">ลากรูปมาวาง หรือคลิกเพื่อเลือก</p>
                        <small>รองรับ JPG / PNG</small>
                        <input type="file" name="filepond[]" id="fileInput" multiple accept="image/*" hidden>
                    </div>

                    <div id="previewContainer" class="d-flex flex-wrap mt-3"></div>
                </div>

                <div class="mt-4">
                    <button type="submit" id="btnSubmit" class="btn btn-primary">💾 บันทึก</button>
                    <a href="product.php" class="btn btn-secondary">ย้อนกลับ</a>
                </div>

            </form>
        </div>
    </div>

    <script>
    $("#btnSubmit").click(function(e) {
        e.preventDefault();

        var formData = new FormData($("#filepond-form")[0]);

        $.ajax({
            url: 'product_add_db.php',
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

    // preview
    document.getElementById("fileInput").addEventListener("change", function() {
        const container = document.getElementById("previewContainer");
        container.innerHTML = "";

        Array.from(this.files).forEach((file, index) => {
            if (!file.type.startsWith("image/")) return;

            const reader = new FileReader();
            reader.onload = function(e) {
                const div = document.createElement("div");
                div.className = "preview-item";

                div.innerHTML = `
                <img src="${e.target.result}">
                <button class="remove-btn" onclick="removeImage(${index})">x</button>
            `;

                container.appendChild(div);
            }
            reader.readAsDataURL(file);
        });
    });

    function removeImage(index) {
        const input = document.getElementById("fileInput");
        const dt = new DataTransfer();

        Array.from(input.files).forEach((file, i) => {
            if (i !== index) dt.items.add(file);
        });

        input.files = dt.files;
        input.dispatchEvent(new Event("change"));
    }
    const uploadBox = document.getElementById("uploadBox");
    const fileInput = document.getElementById("fileInput");

    // คลิกเพื่อเลือกไฟล์
    uploadBox.addEventListener("click", () => fileInput.click());

    // Drag & Drop
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
    </script>

</body>

</html>