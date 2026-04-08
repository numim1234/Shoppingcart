<?php
include '../condb.php';

$stmtType = $conn->prepare("
    SELECT * FROM tbl_type 
    ORDER BY type_id ASC
");
$stmtType->execute();
$resultType = $stmtType->fetchAll();
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>จัดการประเภทสินค้า</title>

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

    .type-badge {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: #eef2ff;
        color: #5865f2;
        padding: 9px 14px;
        border-radius: 999px;
        font-size: 13px;
        font-weight: 700;
        border: 1px solid #dbe2ff;
    }

    .action-group {
        display: flex;
        justify-content: center;
        gap: 8px;
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

        .action-group {
            flex-wrap: wrap;
        }
    }
    </style>
</head>

<body>
    <div class="container-fluid page-wrap">

        <div class="page-head">
            <div>
                <h1 class="page-title">จัดการประเภทสินค้า</h1>
                <div class="page-subtitle">จัดการประเภทสินค้าทั้งหมดในหน้าจอเดียว</div>
            </div>

            <div class="page-action">
                <a href="type.php?act=add" class="btn btn-add">
                    <i class="fas fa-plus me-2"></i>เพิ่มประเภท
                </a>
            </div>
        </div>

        <div class="hero-card">
            <div>
                <h2 class="hero-title">📂 Product Type Management</h2>
                <!-- <p class="hero-desc">ปรับหน้าตาให้ไปในทิศทางเดียวกับ dashboard, admin และ member
          โดยยังใช้ข้อมูลเดิมทั้งหมด</p> -->
            </div>
            <div class="hero-icon">
                <i class="fas fa-folder-tree"></i>
            </div>
        </div>

        <div class="stats-row">
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-layer-group"></i></div>
                <div>
                    <div class="stat-label">จำนวนประเภททั้งหมด</div>
                    <div class="stat-value"><?= number_format(count($resultType)) ?></div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-tag"></i></div>
                <div>
                    <div class="stat-label">ประเภทล่าสุด</div>
                    <div class="stat-value" style="font-size:18px;">
                        <?= !empty($resultType) ? htmlspecialchars($resultType[count($resultType) - 1]['type_name']) : '-' ?>
                    </div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-list-check"></i></div>
                <div>
                    <div class="stat-label">พร้อมจัดการ</div>
                    <div class="stat-value">100%</div>
                </div>
            </div>
        </div>

        <div class="main-card">
            <div class="card-head">
                <div>
                    <h3 class="card-title">รายการประเภทสินค้า</h3>
                    <!-- <div class="card-subtitle">แสดงข้อมูลชื่อประเภท พร้อมปุ่มแก้ไขและลบ</div> -->
                </div>
            </div>

            <div class="card-body-custom">
                <div class="table-responsive">
                    <table id="example1" class="table align-middle table-modern w-100">
                        <thead>
                            <tr>
                                <th style="width: 8%;">No.</th>
                                <th>ประเภทสินค้า</th>
                                <th style="width: 12%;">แก้ไข</th>
                                <th style="width: 12%;">ลบ</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php $runNumber = 1; ?>
                            <?php foreach ($resultType as $row_type): ?>
                            <tr>
                                <td class="text-center fw-bold"><?= $runNumber++; ?></td>

                                <td>
                                    <span class="type-badge">
                                        <i class="fas fa-tag"></i>
                                        <?= htmlspecialchars($row_type['type_name']); ?>
                                    </span>
                                </td>

                                <td class="text-center">
                                    <a href="type.php?act=edit&type_id=<?= (int)$row_type['type_id']; ?>"
                                        class="btn-action btn-edit" title="แก้ไข">
                                        <i class="fas fa-pen"></i>
                                    </a>
                                </td>

                                <td class="text-center">
                                    <button class="btn-action btn-delete"
                                        onclick="confirmDelete('<?= (int)$row_type['type_id']; ?>')" title="ลบ">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                    <?php if (empty($resultType)): ?>
                    <div class="empty-cover">ยังไม่มีข้อมูลประเภทสินค้า</div>
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

    function confirmDelete(type_id) {
        Swal.fire({
            title: 'ลบประเภท?',
            text: 'ข้อมูลจะหายถาวร',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d8485c',
            cancelButtonColor: '#6478ff',
            confirmButtonText: 'ลบ',
            cancelButtonText: 'ยกเลิก',
            borderRadius: 16
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = "type_del.php?type_id=" + encodeURIComponent(type_id);
            }
        });
    }
    </script>

</body>

</html>