<?php
include '../condb.php';

// Query products with type
$stmt = $conn->prepare("
    SELECT p.*, t.type_name 
    FROM tbl_product p 
    LEFT JOIN tbl_type t ON p.type_id = t.type_id 
    ORDER BY p.p_id DESC
");
$stmt->execute();
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

$totalProducts = count($products);
$inStockCount = count(array_filter($products, function ($item) {
    return (int)$item['p_stock'] > 0;
}));
$outStockCount = count(array_filter($products, function ($item) {
    return (int)$item['p_stock'] <= 0;
}));
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>จัดการสต็อกสินค้า</title>

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

    .stock-input {
        border-radius: 12px;
        border: 1px solid #dbe3ef;
        min-width: 90px;
        text-align: center;
        font-weight: 700;
    }

    .stock-input:focus {
        border-color: #6478ff;
        box-shadow: 0 0 0 0.15rem rgba(100, 120, 255, 0.12);
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

    .btn-save-stock {
        border: none;
        border-radius: 12px;
        padding: 9px 14px;
        font-weight: 700;
        background: linear-gradient(135deg, #35c98f, #20b97a);
        color: #fff;
        width: 100%;
        box-shadow: 0 10px 18px rgba(32, 185, 122, 0.18);
    }

    .btn-save-stock:hover {
        opacity: 0.95;
        color: #fff;
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
                <h1 class="page-title">จัดการสต็อกสินค้า</h1>
                <div class="page-subtitle">อัปเดตจำนวนสินค้าคงเหลือของแต่ละรายการได้ทันที</div>
            </div>
        </div>

        <div class="hero-card">
            <div>
                <h2 class="hero-title">📦 Stock Management</h2>
                <!-- <p class="hero-desc">ปรับหน้าตาให้ไปในทิศทางเดียวกับ dashboard และหน้าจัดการอื่น
                    โดยยังใช้ข้อมูลเดิมทั้งหมด</p> -->
            </div>
            <div class="hero-icon">
                <i class="fas fa-warehouse"></i>
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
                <div class="stat-icon"><i class="fas fa-circle-check"></i></div>
                <div>
                    <div class="stat-label">สินค้าที่มีสต็อก</div>
                    <div class="stat-value"><?= number_format($inStockCount) ?></div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-triangle-exclamation"></i></div>
                <div>
                    <div class="stat-label">สินค้าหมดสต็อก</div>
                    <div class="stat-value"><?= number_format($outStockCount) ?></div>
                </div>
            </div>
        </div>

        <div class="main-card">
            <div class="card-head">
                <div>
                    <h3 class="card-title">รายการสต็อกสินค้า</h3>

                </div>
            </div>

            <div class="card-body-custom">
                <div class="table-responsive">
                    <table id="stockTable" class="table align-middle table-modern w-100">
                        <thead>
                            <tr>
                                <th style="width: 5%;">No</th>
                                <th style="width: 10%;">รูป</th>
                                <th>ชื่อสินค้า</th>
                                <th style="width: 15%;">ประเภท</th>
                                <th style="width: 16%;">สต็อกคงเหลือ</th>
                                <th style="width: 12%;">หน่วย</th>
                                <th style="width: 12%;">จัดการ</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php $i = 1; ?>
                            <?php foreach ($products as $row): ?>
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
                                    <?php if ((int)$row['p_stock'] > 0) { ?>
                                    <span class="stock-badge stock-ok">
                                        <i class="fas fa-check-circle"></i> พร้อมขาย
                                    </span>
                                    <?php } else { ?>
                                    <span class="stock-badge stock-out">
                                        <i class="fas fa-xmark-circle"></i> หมดสต็อก
                                    </span>
                                    <?php } ?>
                                </td>

                                <td>
                                    <span class="type-badge">
                                        <i class="fas fa-tag"></i>
                                        <?php echo htmlspecialchars($row['type_name']); ?>
                                    </span>
                                </td>

                                <td>
                                    <input type="number" class="form-control stock-input qty-input" min="0"
                                        value="<?php echo intval($row['p_stock']); ?>"
                                        data-pid="<?php echo $row['p_id']; ?>">
                                </td>

                                <td><?php echo htmlspecialchars($row['p_unit']); ?></td>

                                <td>
                                    <button class="btn-save-stock save-qty" data-pid="<?php echo $row['p_id']; ?>">
                                        <i class="fas fa-floppy-disk me-2"></i>บันทึก
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                    <?php if (empty($products)): ?>
                    <div class="empty-cover">ยังไม่มีข้อมูลสินค้า</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

    </div>

    <script>
    $(function() {
        $('#stockTable').DataTable({
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

        $('.save-qty').on('click', function() {
            var p_id = $(this).data('pid');
            var qty = $('input.qty-input[data-pid="' + p_id + '"]').val();

            $.post('product_stock_update.php', {
                p_id: p_id,
                p_stock: qty
            }, function(res) {
                if (res && res.status) {
                    Swal.fire({
                        icon: 'success',
                        title: 'อัปเดตแล้ว',
                        timer: 800,
                        showConfirmButton: false
                    }).then(() => location.reload());
                } else {
                    Swal.fire('ผิดพลาด', res.msg || 'ไม่สามารถอัปเดตได้', 'error');
                }
            }, 'json').fail(function() {
                Swal.fire('ผิดพลาด', 'ไม่สามารถติดต่อเซิร์ฟเวอร์ได้', 'error');
            });
        });
    });
    </script>

</body>

</html>

<?php $conn = null; ?>