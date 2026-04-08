<?php
require_once("../condb.php");
$menu = "contact";

// ดึงข้อมูล
$stmt = $conn->prepare("SELECT * FROM tbl_contact ORDER BY contact_id DESC LIMIT 1");
$stmt->execute();
$contact = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="th">
<?php include("head.php"); ?>

<body class="hold-transition sidebar-mini layout-fixed">
    <div class="wrapper">

        <?php
        include("navbar.php");
        include("menu.php");
        ?>

        <div class="content-wrapper" style="background:#f3f5fb;">
            <section class="content pt-3 pb-4">
                <div class="container-fluid">

                    <!-- header -->
                    <div class="page-head mb-3">
                        <h1 class="fw-bold" style="color:#253045;">จัดการข้อมูลติดต่อ</h1>
                        <div class="text-muted">แก้ไขข้อมูลร้านและช่องทางติดต่อ</div>
                    </div>

                    <!-- hero -->
                    <div class="hero-card mb-3">
                        <div>
                            <h2 class="hero-title">🏪 Contact Management</h2>
                            <p class="hero-desc">จัดการข้อมูลร้าน เบอร์โทร แผนที่ และโซเชียลมีเดีย</p>
                        </div>
                        <div class="hero-icon">
                            <i class="fas fa-address-book"></i>
                        </div>
                    </div>

                    <!-- alert -->
                    <?php if (isset($_GET['success'])): ?>
                        <div class="alert alert-success">บันทึกข้อมูลเรียบร้อย</div>
                    <?php endif; ?>

                    <!-- form -->
                    <div class="main-card">
                        <div class="card-head">
                            <h3 class="card-title">ข้อมูลร้าน</h3>
                        </div>

                        <div class="card-body-custom">
                            <form action="contact_save.php" method="post">
                                <input type="hidden" name="contact_id"
                                    value="<?= htmlspecialchars($contact['contact_id'] ?? '') ?>">

                                <div class="row g-3">

                                    <div class="col-md-12">
                                        <label>ชื่อร้าน</label>
                                        <input type="text" name="contact_name" class="form-control modern-input"
                                            value="<?= htmlspecialchars($contact['contact_name'] ?? '') ?>">
                                    </div>

                                    <div class="col-md-6">
                                        <label>เบอร์โทร</label>
                                        <input type="text" name="contact_phone" class="form-control modern-input"
                                            value="<?= htmlspecialchars($contact['contact_phone'] ?? '') ?>">
                                    </div>

                                    <div class="col-md-6">
                                        <label>อีเมล</label>
                                        <input type="email" name="contact_email" class="form-control modern-input"
                                            value="<?= htmlspecialchars($contact['contact_email'] ?? '') ?>">
                                    </div>

                                    <div class="col-md-6">
                                        <label>Facebook</label>
                                        <input type="text" name="contact_facebook" class="form-control modern-input"
                                            value="<?= htmlspecialchars($contact['contact_facebook'] ?? '') ?>">
                                    </div>

                                    <div class="col-md-6">
                                        <label>Line</label>
                                        <input type="text" name="contact_line" class="form-control modern-input"
                                            value="<?= htmlspecialchars($contact['contact_line'] ?? '') ?>">
                                    </div>

                                    <div class="col-md-12">
                                        <label>ที่อยู่</label>
                                        <textarea name="contact_address" class="form-control modern-input" rows="3">
<?= htmlspecialchars($contact['contact_address'] ?? '') ?>
                    </textarea>
                                    </div>

                                    <div class="col-md-12">
                                        <label>Google Map</label>
                                        <textarea name="contact_map" class="form-control modern-input" rows="2">
<?= htmlspecialchars($contact['contact_map'] ?? '') ?>
                    </textarea>
                                    </div>

                                </div>

                                <div class="mt-4">
                                    <button type="submit" class="btn btn-save">
                                        <i class="fas fa-save me-2"></i>บันทึกข้อมูล
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                </div>
            </section>
        </div>

    </div>

    <!-- STYLE -->
    <style>
        .page-head {
            margin-bottom: 15px;
        }

        .hero-card {
            background: linear-gradient(135deg, #cfd7ff, #dfe6ff);
            border-radius: 18px;
            padding: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .hero-title {
            font-size: 26px;
            font-weight: 800;
        }

        .hero-icon {
            font-size: 28px;
            background: #fff;
            padding: 15px;
            border-radius: 15px;
        }

        .main-card {
            background: #fff;
            border-radius: 18px;
            box-shadow: 0 10px 24px rgba(0, 0, 0, 0.05);
        }

        .card-head {
            padding: 15px;
            border-bottom: 1px solid #eee;
        }

        .card-body-custom {
            padding: 20px;
        }

        .modern-input {
            border-radius: 12px;
            border: 1px solid #ddd;
        }

        .modern-input:focus {
            border-color: #6478ff;
            box-shadow: 0 0 0 0.15rem rgba(100, 120, 255, .2);
        }

        .btn-save {
            background: linear-gradient(135deg, #6478ff, #5865f2);
            color: #fff;
            border: none;
            border-radius: 12px;
            padding: 10px 20px;
        }
    </style>

</body>

</html>