<?php
include '../condb.php';

// ================== โหมดจัดการรูป ==================
if (isset($_GET['p_id'])) {
    $stmt_img = $conn->prepare("SELECT * FROM tbl_img_detail WHERE p_id = ? ORDER BY id DESC");
    $stmt_img->execute([$_GET['p_id']]);
    $result_img = $stmt_img->fetchAll();
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>จัดการรูปภาพสินค้า</title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
    body {
        background: #f3f5fb;
        font-family: 'Prompt', sans-serif;
        color: #2f3542;
    }

    .page-wrap {
        padding: 24px;
    }

    .page-head {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 14px;
        flex-wrap: wrap;
        margin-bottom: 20px;
    }

    .page-title {
        margin: 0;
        font-size: 28px;
        font-weight: 800;
        color: #253045;
    }

    .page-subtitle {
        margin-top: 6px;
        font-size: 14px;
        color: #8a94a6;
    }

    .btn-back {
        border: none;
        border-radius: 14px;
        padding: 11px 18px;
        font-weight: 700;
        background: linear-gradient(135deg, #6478ff, #5865f2);
        color: #fff;
        text-decoration: none;
        box-shadow: 0 10px 18px rgba(88, 101, 242, 0.22);
    }

    .btn-back:hover {
        color: #fff;
        opacity: 0.95;
    }

    .hero-card {
        background: linear-gradient(135deg, #cfd7ff 0%, #dfe6ff 100%);
        border-radius: 20px;
        border: 1px solid #d9e1fb;
        padding: 22px 24px;
        margin-bottom: 18px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 18px;
        flex-wrap: wrap;
    }

    .hero-title {
        margin: 0;
        font-size: 30px;
        font-weight: 800;
        color: #253045;
    }

    .hero-desc {
        margin: 8px 0 0;
        color: #58677d;
        font-size: 14px;
    }

    .hero-icon {
        width: 72px;
        height: 72px;
        border-radius: 20px;
        background: rgba(255, 255, 255, 0.6);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 30px;
        color: #5c6ad8;
        box-shadow: 0 8px 20px rgba(99, 102, 241, 0.12);
    }

    .gallery-card {
        background: #fff;
        border: 1px solid #e9edf5;
        border-radius: 20px;
        box-shadow: 0 10px 24px rgba(15, 23, 42, 0.04);
        padding: 22px;
    }

    .image-card {
        background: #fff;
        border: 1px solid #e9edf5;
        border-radius: 18px;
        box-shadow: 0 10px 24px rgba(15, 23, 42, 0.04);
        overflow: hidden;
        transition: 0.25s ease;
    }

    .image-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 14px 30px rgba(0, 0, 0, 0.10);
    }

    .image-card img {
        width: 100%;
        height: 220px;
        object-fit: cover;
        background: #f8faff;
    }

    .image-card-body {
        padding: 14px;
    }

    .btn-delete-img {
        width: 100%;
        border: none;
        border-radius: 12px;
        padding: 10px 14px;
        background: #ffe2e5;
        color: #d8485c;
        font-weight: 700;
    }

    .btn-delete-img:hover {
        background: #ffc7ce;
        color: #bd3045;
    }

    .empty-cover {
        padding: 36px;
        text-align: center;
        color: #96a0b2;
        background: #fafcff;
        border: 1px dashed #dbe3ef;
        border-radius: 16px;
    }
    </style>
</head>

<body>
    <div class="container-fluid page-wrap">

        <div class="page-head">
            <div>
                <h1 class="page-title">จัดการรูปภาพสินค้า</h1>
                <div class="page-subtitle">จัดการรูปทั้งหมดของสินค้าที่เลือก</div>
            </div>

            <a href="product.php" class="btn-back">
                <i class="fas fa-arrow-left me-2"></i>กลับหน้าสินค้า
            </a>
        </div>

        <div class="hero-card">
            <div>
                <h2 class="hero-title">📸 Product Image Gallery</h2>
                <p class="hero-desc">แสดงรูปภาพสินค้าทั้งหมด พร้อมลบรูปได้ทันที</p>
            </div>
            <div class="hero-icon">
                <i class="fas fa-images"></i>
            </div>
        </div>

        <div class="gallery-card">
            <?php if (!empty($result_img)): ?>
            <div class="row g-4">
                <?php foreach ($result_img as $row_img): ?>
                <div class="col-lg-3 col-md-4 col-sm-6">
                    <div class="image-card">
                        <img src="../p_gallery/<?php echo htmlspecialchars($row_img['img']); ?>" alt="product image">
                        <div class="image-card-body">
                            <button class="btn-delete-img"
                                onclick="delImg(<?php echo (int)$row_img['id']; ?>, '<?php echo htmlspecialchars($row_img['img'], ENT_QUOTES); ?>')">
                                <i class="fas fa-trash me-2"></i>ลบรูป
                            </button>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <div class="empty-cover">ยังไม่มีรูปภาพสำหรับสินค้านี้</div>
            <?php endif; ?>
        </div>

    </div>

    <script>
    function delImg(id, img_name) {
        Swal.fire({
            title: 'ลบรูป?',
            text: 'รูปภาพนี้จะถูกลบถาวร',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d8485c',
            cancelButtonColor: '#6478ff',
            confirmButtonText: 'ลบ',
            cancelButtonText: 'ยกเลิก',
            borderRadius: 16
        }).then((r) => {
            if (r.isConfirmed) {
                $.post('product_img_del.php', {
                    id: id,
                    img_name: img_name
                }, function() {
                    location.reload();
                });
            }
        });
    }
    </script>

</body>

</html>
<?php
    exit;
}

// ================== โหมดรายการสินค้า ==================
$stmt = $conn->prepare("
    SELECT p.*, t.type_name
    FROM tbl_product p
    LEFT JOIN tbl_type t ON p.type_id = t.type_id
    ORDER BY p.p_id DESC
");
$stmt->execute();
$result = $stmt->fetchAll();

$totalProducts = count($result);
$activeProducts = count(array_filter($result, function ($item) {
    return (int)$item['p_status'] === 1;
}));
$outStockProducts = count(array_filter($result, function ($item) {
    return (int)$item['p_stock'] <= 0;
}));
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>จัดการสินค้า</title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>

    <style>
    body {
        background: #f3f5fb;
        font-family: 'Prompt', sans-serif;
        color: #2f3542;
    }

    .page-wrap {
        padding: 24px;
    }

    .page-head {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 14px;
        flex-wrap: wrap;
        margin-bottom: 20px;
    }

    .page-title {
        margin: 0;
        font-size: 28px;
        font-weight: 800;
        color: #253045;
    }

    .page-subtitle {
        margin-top: 6px;
        font-size: 14px;
        color: #8a94a6;
    }

    .page-action .btn-add {
        border: none;
        border-radius: 14px;
        padding: 11px 18px;
        font-weight: 700;
        background: linear-gradient(135deg, #6478ff, #5865f2);
        color: #fff;
        box-shadow: 0 10px 18px rgba(88, 101, 242, 0.22);
    }

    .page-action .btn-add:hover {
        opacity: 0.95;
        color: #fff;
    }

    .hero-card {
        background: linear-gradient(135deg, #cfd7ff 0%, #dfe6ff 100%);
        border-radius: 20px;
        border: 1px solid #d9e1fb;
        padding: 22px 24px;
        margin-bottom: 18px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 18px;
        flex-wrap: wrap;
    }

    .hero-title {
        margin: 0;
        font-size: 30px;
        font-weight: 800;
        color: #253045;
    }

    .hero-desc {
        margin: 8px 0 0;
        color: #58677d;
        font-size: 14px;
    }

    .hero-icon {
        width: 72px;
        height: 72px;
        border-radius: 20px;
        background: rgba(255, 255, 255, 0.6);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 30px;
        color: #5c6ad8;
        box-shadow: 0 8px 20px rgba(99, 102, 241, 0.12);
    }

    .stats-row {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 16px;
        margin-bottom: 20px;
    }

    .stat-card {
        background: #fff;
        border: 1px solid #e9edf5;
        border-radius: 18px;
        box-shadow: 0 10px 24px rgba(15, 23, 42, 0.04);
        padding: 18px 20px;
        display: flex;
        align-items: center;
        gap: 14px;
    }

    .stat-icon {
        width: 54px;
        height: 54px;
        border-radius: 16px;
        background: linear-gradient(135deg, #6478ff, #5865f2);
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 22px;
        flex-shrink: 0;
        box-shadow: 0 10px 18px rgba(88, 101, 242, 0.20);
    }

    .stat-label {
        color: #8b95a7;
        font-size: 13px;
        margin-bottom: 5px;
    }

    .stat-value {
        font-size: 28px;
        font-weight: 800;
        color: #253045;
        line-height: 1.1;
    }

    .main-card {
        background: #fff;
        border: 1px solid #e9edf5;
        border-radius: 20px;
        box-shadow: 0 10px 24px rgba(15, 23, 42, 0.04);
        overflow: hidden;
    }

    .card-head {
        padding: 18px 22px;
        border-bottom: 1px solid #eef2f7;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
    }

    .card-title {
        margin: 0;
        font-size: 20px;
        font-weight: 800;
        color: #253045;
    }

    .card-subtitle {
        margin-top: 4px;
        font-size: 13px;
        color: #96a0b2;
    }

    .card-body-custom {
        padding: 20px 22px;
    }

    table.dataTable {
        margin-top: 0 !important;
    }

    .table-modern thead th,
    table.dataTable thead th {
        background: #f8faff !important;
        color: #8d96a8 !important;
        font-size: 13px;
        font-weight: 700;
        border-bottom: 1px solid #edf1f7 !important;
        padding: 14px 12px !important;
    }

    .table-modern tbody td,
    table.dataTable tbody td {
        padding: 14px 12px !important;
        vertical-align: middle;
        border-bottom: 1px solid #f0f3f8;
        color: #364152;
        background: #fff;
    }

    table.dataTable tbody tr:hover td {
        background: #fafcff !important;
    }

    .table>:not(caption)>*>* {
        box-shadow: none !important;
    }

    .product-img {
        width: 64px;
        height: 64px;
        object-fit: cover;
        border-radius: 14px;
        border: 3px solid #eef2f7;
        background: #fff;
        box-shadow: 0 6px 14px rgba(0, 0, 0, 0.06);
    }

    .product-name {
        font-weight: 700;
        color: #253045;
        margin-bottom: 3px;
    }

    .type-badge {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: #eef2ff;
        color: #5865f2;
        padding: 8px 12px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 700;
        border: 1px solid #dbe2ff;
    }

    .stock-badge {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        padding: 8px 14px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 700;
        border: 1px solid transparent;
    }

    .stock-ok {
        background: #e8f8ee;
        color: #1f8b4d;
        border-color: #cdeedb;
    }

    .stock-out {
        background: #ffe2e5;
        color: #d8485c;
        border-color: #ffd1d8;
    }

    .status-text {
        font-size: 12px;
        font-weight: 700;
        margin-top: 4px;
    }

    .status-on {
        color: #1f8b4d;
    }

    .status-off {
        color: #d8485c;
    }

    .form-check.form-switch {
        display: flex;
        justify-content: center;
        margin: 0;
    }

    .form-check-input {
        width: 3rem;
        height: 1.5rem;
        cursor: pointer;
        border: none;
        background-color: #d8deea;
        box-shadow: none !important;
    }

    .form-check-input:checked {
        background-color: #5f6fe0;
    }

    .action-group {
        display: flex;
        justify-content: center;
        gap: 8px;
        flex-wrap: wrap;
    }

    .btn-action {
        width: 38px;
        height: 38px;
        border: none;
        border-radius: 12px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 14px;
        transition: 0.2s ease;
        text-decoration: none;
    }

    .btn-edit {
        background: #fff4d8;
        color: #9a6a2f;
    }

    .btn-edit:hover {
        background: #ffe7a3;
        color: #7f551f;
    }

    .btn-delete {
        background: #ffe2e5;
        color: #d8485c;
    }

    .btn-delete:hover {
        background: #ffc7ce;
        color: #bd3045;
    }

    .btn-gallery {
        background: #eef2ff;
        color: #5865f2;
    }

    .btn-gallery:hover {
        background: #dbe2ff;
        color: #4654cf;
    }

    .price-text {
        font-weight: 800;
        color: #253045;
    }

    .dataTables_wrapper .dataTables_length select,
    .dataTables_wrapper .dataTables_filter input {
        border-radius: 12px;
        border: 1px solid #dbe3ef;
        padding: 6px 10px;
        background: #fff;
    }

    .dataTables_wrapper .dataTables_filter input:focus,
    .dataTables_wrapper .dataTables_length select:focus {
        outline: none;
        border-color: #6478ff;
        box-shadow: 0 0 0 0.15rem rgba(100, 120, 255, 0.12);
    }

    .dataTables_wrapper .dataTables_info,
    .dataTables_wrapper .dataTables_paginate {
        margin-top: 14px;
        color: #8a94a6 !important;
        font-size: 13px;
    }

    .page-item .page-link {
        border: none;
        margin: 0 3px;
        border-radius: 10px !important;
        color: #5865f2;
        background: #eef2ff;
    }

    .page-item.active .page-link {
        background: #5865f2;
        color: #fff;
    }

    .empty-cover {
        padding: 30px;
        text-align: center;
        color: #96a0b2;
    }

    @media (max-width: 991px) {
        .stats-row {
            grid-template-columns: 1fr;
        }

        .page-wrap {
            padding: 16px;
        }

        .hero-title {
            font-size: 24px;
        }
    }

    @media (max-width: 768px) {

        .card-head,
        .page-head,
        .hero-card {
            flex-direction: column;
            align-items: flex-start;
        }
    }
    </style>
</head>

<body>
    <div class="container-fluid page-wrap">

        <div class="page-head">
            <div>
                <h1 class="page-title">จัดการสินค้า</h1>
                <div class="page-subtitle">จัดการข้อมูลสินค้า สต๊อก รูปภาพ และสถานะการขายในหน้าจอเดียว</div>
            </div>

            <div class="page-action">
                <a href="product.php?act=add" class="btn btn-add">
                    <i class="fas fa-plus me-2"></i>เพิ่มสินค้า
                </a>
            </div>
        </div>

        <div class="hero-card">
            <div>
                <h2 class="hero-title">📦 Product Management</h2>
                <!-- <p class="hero-desc">ปรับหน้าตาให้ไปในทิศทางเดียวกับ dashboard และหน้าจัดการอื่น
                    โดยยังใช้ข้อมูลเดิมทั้งหมด</p> -->
            </div>
            <div class="hero-icon">
                <i class="fas fa-box-open"></i>
            </div>
        </div>

        <div class="stats-row">
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-boxes-stacked"></i></div>
                <div>
                    <div class="stat-label">จำนวนสินค้าทั้งหมด</div>
                    <div class="stat-value"><?= number_format($totalProducts) ?></div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-toggle-on"></i></div>
                <div>
                    <div class="stat-label">สินค้าที่เปิดขาย</div>
                    <div class="stat-value"><?= number_format($activeProducts) ?></div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-triangle-exclamation"></i></div>
                <div>
                    <div class="stat-label">สินค้าหมดสต๊อก</div>
                    <div class="stat-value"><?= number_format($outStockProducts) ?></div>
                </div>
            </div>
        </div>

        <div class="main-card">
            <div class="card-head">
                <div>
                    <h3 class="card-title">รายการสินค้า</h3>
                    <!-- <div class="card-subtitle">แสดงรูปสินค้า ชื่อ ประเภท ราคา สต๊อก หน่วย สถานะ และปุ่มจัดการ</div> -->
                </div>
            </div>

            <div class="card-body-custom">
                <div class="table-responsive">
                    <table id="example1" class="table align-middle table-modern w-100">
                        <thead>
                            <tr>
                                <th style="width: 5%;">No</th>
                                <th style="width: 9%;">รูป</th>
                                <th style="width: 18%;">ชื่อสินค้า</th>
                                <th style="width: 13%;">ประเภท</th>
                                <th style="width: 10%;">ราคา</th>
                                <th style="width: 10%;">จำนวน</th>
                                <th style="width: 10%;">หน่วย</th>
                                <th style="width: 10%;">สถานะ</th>
                                <th style="width: 15%;">จัดการ</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php $i = 1; ?>
                            <?php foreach ($result as $row): ?>
                            <?php
                                $stmt_img = $conn->prepare("SELECT img FROM tbl_img_detail WHERE p_id=? ORDER BY id DESC LIMIT 1");
                                $stmt_img->execute([$row['p_id']]);
                                $img = $stmt_img->fetchColumn();
                                ?>
                            <tr>
                                <td class="fw-bold text-center"><?php echo $i++; ?></td>

                                <td class="text-center">
                                    <?php if ($img) { ?>
                                    <img src="p_gallery/<?php echo htmlspecialchars($img); ?>" class="product-img"
                                        alt="product">
                                    <?php } else { ?>
                                    <span class="text-muted">-</span>
                                    <?php } ?>
                                </td>

                                <td>
                                    <div class="product-name"><?php echo htmlspecialchars($row['p_name']); ?></div>
                                </td>

                                <td>
                                    <span class="type-badge">
                                        <i class="fas fa-tag"></i>
                                        <?php echo htmlspecialchars($row['type_name']); ?>
                                    </span>
                                </td>

                                <td class="price-text">
                                    <?php echo number_format($row['p_price'], 2); ?>
                                </td>

                                <td>
                                    <?php if ($row['p_stock'] > 0) { ?>
                                    <span class="stock-badge stock-ok">
                                        <i class="fas fa-check-circle"></i>
                                        <?php echo number_format($row['p_stock']); ?>
                                    </span>
                                    <?php } else { ?>
                                    <span class="stock-badge stock-out">
                                        <i class="fas fa-xmark-circle"></i>
                                        หมด
                                    </span>
                                    <?php } ?>
                                </td>

                                <td><?php echo htmlspecialchars($row['p_unit']); ?></td>

                                <td class="text-center">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox"
                                            onchange="toggleStatus(<?= (int)$row['p_id'] ?>)"
                                            <?php echo ($row['p_status'] == 1) ? 'checked' : ''; ?>>
                                    </div>

                                    <?php if ($row['p_status'] == 1) { ?>
                                    <div class="status-text status-on">เปิดขาย</div>
                                    <?php } else { ?>
                                    <div class="status-text status-off">ปิดขาย</div>
                                    <?php } ?>
                                </td>

                                <td class="text-center">
                                    <div class="action-group">
                                        <!-- <a href="product.php?p_id=<?php echo (int)$row['p_id']; ?>"
                                                class="btn-action btn-gallery" title="จัดการรูป">
                                                <i class="fas fa-images"></i>
                                            </a> -->

                                        <a href="product.php?act=edit&p_id=<?php echo (int)$row['p_id']; ?>"
                                            class="btn-action btn-edit" title="แก้ไข">
                                            <i class="fas fa-pen"></i>
                                        </a>

                                        <button class="btn-action btn-delete"
                                            onclick="delProduct(<?php echo (int)$row['p_id']; ?>)" title="ลบ">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                    <?php if (empty($result)): ?>
                    <div class="empty-cover">ยังไม่มีข้อมูลสินค้า</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

    </div>

    <script>
    $(function() {
        $('#example1').DataTable({
            pageLength: 10,
            language: {
                search: "ค้นหา:",
                lengthMenu: "แสดง _MENU_ รายการ",
                info: "แสดง _START_ ถึง _END_ จาก _TOTAL_ รายการ",
                infoEmpty: "ไม่มีข้อมูล",
                zeroRecords: "ไม่พบข้อมูลที่ค้นหา",
                paginate: {
                    first: "แรก",
                    last: "สุดท้าย",
                    next: "ถัดไป",
                    previous: "ก่อนหน้า"
                }
            }
        });
    });

    function delProduct(p_id) {
        Swal.fire({
            title: 'ลบสินค้า?',
            text: 'ข้อมูลสินค้านี้จะถูกลบถาวร',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d8485c',
            cancelButtonColor: '#6478ff',
            confirmButtonText: 'ลบ',
            cancelButtonText: 'ยกเลิก',
            borderRadius: 16
        }).then((result) => {
            if (result.isConfirmed) {
                $.post('product_delete.php', {
                    p_id: p_id
                }, function() {
                    location.reload();
                });
            }
        });
    }

    function toggleStatus(p_id) {
        $.ajax({
            method: 'POST',
            url: 'product_update_status.php',
            data: {
                p_id: p_id
            },
            dataType: 'json',
            success: function(res) {
                if (res.status) {
                    Swal.fire({
                        icon: 'success',
                        title: 'อัปเดตแล้ว',
                        timer: 800,
                        showConfirmButton: false
                    }).then(() => {
                        location.reload();
                    });
                }
            }
        });
    }
    </script>

</body>

</html>

<?php $conn = null; ?>