<?php
include '../condb.php';

if (isset($_GET['m_id'])) {
    $stmt_m = $conn->prepare("SELECT * FROM tbl_member WHERE m_id=?");
    $stmt_m->execute([$_GET['m_id']]);
    $row_em = $stmt_m->fetch(PDO::FETCH_ASSOC);

    if ($stmt_m->rowCount() < 1) {
        header('Location: admin.php');
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>แก้ไข Admin</title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">

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
        .preview-img {
            width: 200px;
            height: 200px;
            object-fit: cover;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin-top: 10px;
        }
    </style>
</head>

<body>

    <div class="container mt-5">
        <div class="card card-custom p-4">

            <h4 class="title mb-4">👤 แก้ไขผู้ดูแลระบบ</h4>

            <form action="admin_edit_db.php" method="post" enctype="multipart/form-data">

                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label>Username</label>
                        <input type="text" name="m_username" value="<?= $row_em['m_username']; ?>" class="form-control">
                    </div>

                    <div class="form-group col-md-6">
                        <label>Password</label>
                        <input type="text" name="m_password" value="<?= $row_em['m_password']; ?>" class="form-control">
                    </div>
                </div>

                <div class="form-group">
                    <label>ชื่อ-นามสกุล</label>
                    <input type="text" name="m_name" value="<?= $row_em['m_name']; ?>" class="form-control">
                </div>

                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label>อีเมล</label>
                        <input type="email" name="m_email" value="<?= $row_em['m_email']; ?>" class="form-control">
                    </div>

                    <div class="form-group col-md-6">
                        <label>เบอร์โทร</label>
                        <input type="text" name="m_tel" value="<?= $row_em['m_tel']; ?>" class="form-control">
                    </div>
                </div>

                <div class="form-group">
                    <label>ที่อยู่</label>
                    <textarea name="m_address" class="form-control"><?= $row_em['m_address']; ?></textarea>
                </div>

                <!-- รูปเดิม -->
                <div class="form-group">
                    <label>รูปปัจจุบัน</label><br>
                    <img src="m_img/<?= $row_em['m_img']; ?>" class="preview-img">
                </div>

                <!-- upload -->
                <div class="form-group">
                    <label>เปลี่ยนรูปใหม่</label>

                    <div id="uploadBox" class="upload-box text-center p-4">
                        <div class="upload-icon">📷</div>
                        <p>คลิกหรือวางรูป</p>
                        <small>ไม่เลือกก็ได้ (ใช้รูปเดิม)</small>
                        <input type="file" name="m_img" id="fileInput" accept="image/*" hidden>
                    </div>

                    <img id="preview" class="preview-img" style="display:none;">
                </div>

                <input type="hidden" name="m_level" value="admin">
                <input type="hidden" name="m_img2" value="<?= $row_em['m_img']; ?>">
                <input type="hidden" name="m_id" value="<?= $row_em['m_id']; ?>">

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">💾 บันทึก</button>
                    <a href="admin.php" class="btn btn-secondary">ย้อนกลับ</a>
                </div>

            </form>
        </div>
    </div>

    <script>
        const uploadBox = document.getElementById("uploadBox");
        const fileInput = document.getElementById("fileInput");
        const preview = document.getElementById("preview");

        uploadBox.addEventListener("click", () => fileInput.click());

        // drag
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
            showPreview(fileInput.files[0]);
        });

        // preview
        fileInput.addEventListener("change", function() {
            showPreview(this.files[0]);
        });

        function showPreview(file) {
            if (!file || !file.type.startsWith("image/")) return;

            const reader = new FileReader();
            reader.onload = function(e) {
                preview.src = e.target.result;
                preview.style.display = "block";
            }
            reader.readAsDataURL(file);
        }
    </script>

</body>

</html>