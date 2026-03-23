<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>แก้ไขประเภทสินค้า</title>

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
    </style>
</head>

<body>

    <?php
    if (isset($_GET['type_id'])) {
        include '../condb.php';
        $stmt_type = $conn->prepare("SELECT * FROM tbl_type WHERE type_id=?");
        $stmt_type->execute([$_GET['type_id']]);
        $row_type = $stmt_type->fetch(PDO::FETCH_ASSOC);

        if (!$row_type) {
            header('Location: type.php');
            exit();
        }
    }
    ?>

    <div class="container mt-5">
        <div class="card card-custom p-4">

            <h4 class="title mb-4">✏️ แก้ไขประเภทสินค้า</h4>

            <form action="type_edit_db.php" method="post">

                <div class="form-group">
                    <label>ชื่อประเภทสินค้า</label>
                    <input type="text" name="type_name" value="<?= $row_type['type_name']; ?>" class="form-control"
                        placeholder="เช่น เค้ก, ขนมปัง, เครื่องดื่ม" required>
                </div>

                <input type="hidden" name="type_id" value="<?= $row_type['type_id']; ?>">

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">💾 บันทึก</button>
                    <a href="type.php" class="btn btn-secondary">ย้อนกลับ</a>
                </div>

            </form>
        </div>
    </div>

</body>

</html>