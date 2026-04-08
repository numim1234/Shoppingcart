<?php
include '../condb.php';

$stmtPromotion = $conn->prepare("
    SELECT * FROM tbl_promotion
    ORDER BY pro_id ASC
");
$stmtPromotion->execute();
$resultPro = $stmtPromotion->fetchAll();
?>

<!DOCTYPE html>
<html lang="th">

<head>
  <meta charset="UTF-8">
  <title>จัดการโปรโมชั่น</title>

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

    .promo-img {
      width: 68px;
      height: 68px;
      object-fit: cover;
      border-radius: 14px;
      border: 3px solid #eef2f7;
      background: #fff;
      box-shadow: 0 6px 14px rgba(0, 0, 0, 0.06);
    }

    .promo-name {
      font-weight: 700;
      color: #253045;
      margin-bottom: 2px;
    }

    .promo-code {
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

    .discount-badge {
      display: inline-flex;
      align-items: center;
      gap: 7px;
      background: #ffe9ec;
      color: #d8485c;
      padding: 8px 14px;
      border-radius: 999px;
      font-size: 12px;
      font-weight: 700;
      border: 1px solid #ffd1d8;
    }

    .status-badge {
      display: inline-flex;
      align-items: center;
      gap: 7px;
      padding: 8px 14px;
      border-radius: 999px;
      font-size: 12px;
      font-weight: 700;
      border: 1px solid transparent;
    }

    .status-active {
      background: #e8f8ee;
      color: #1f8b4d;
      border-color: #cdeedb;
    }

    .status-inactive {
      background: #fff4e5;
      color: #b26b00;
      border-color: #ffe0b3;
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
        <h1 class="page-title">จัดการโปรโมชั่น</h1>
        <div class="page-subtitle">จัดการข้อมูลโปรโมชั่นทั้งหมดในหน้าจอเดียว</div>
      </div>

      <div class="page-action">
        <a href="promotion.php?act=add" class="btn btn-add">
          <i class="fas fa-plus me-2"></i>เพิ่มโปรโมชั่น
        </a>
      </div>
    </div>

    <div class="hero-card">
      <div>
        <h2 class="hero-title">🎉 Promotion Management</h2>
        <p class="hero-desc">ปรับหน้าตาให้ไปในทิศทางเดียวกับ dashboard, admin, member และ type
          โดยยังใช้ข้อมูลเดิมทั้งหมด</p>
      </div>
      <div class="hero-icon">
        <i class="fas fa-tags"></i>
      </div>
    </div>

    <?php
    $totalPromotion = count($resultPro);
    $activePromotion = 0;
    $expiredPromotion = 0;

    foreach ($resultPro as $row) {
      $date_now = new DateTime();
      $date_end = new DateTime(date('m/d/Y H:i:s', strtotime($row['pro_end'])));
      if ($date_now > $date_end) {
        $expiredPromotion++;
      } else {
        $activePromotion++;
      }
    }
    ?>

    <div class="stats-row">
      <div class="stat-card">
        <div class="stat-icon"><i class="fas fa-bullhorn"></i></div>
        <div>
          <div class="stat-label">จำนวนโปรโมชั่นทั้งหมด</div>
          <div class="stat-value"><?= number_format($totalPromotion) ?></div>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
        <div>
          <div class="stat-label">โปรโมชั่นที่ใช้งาน</div>
          <div class="stat-value"><?= number_format($activePromotion) ?></div>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-icon"><i class="fas fa-clock"></i></div>
        <div>
          <div class="stat-label">โปรโมชั่นที่หมดอายุ</div>
          <div class="stat-value"><?= number_format($expiredPromotion) ?></div>
        </div>
      </div>
    </div>

    <div class="main-card">
      <div class="card-head">
        <div>
          <h3 class="card-title">รายการโปรโมชั่น</h3>
          <div class="card-subtitle">แสดงข้อมูลรูปภาพ โค้ด ส่วนลด ช่วงเวลา และสถานะโปรโมชั่น</div>
        </div>
      </div>

      <div class="card-body-custom">
        <div class="table-responsive">
          <table id="example1" class="table align-middle table-modern w-100">
            <thead>
              <tr>
                <th style="width: 6%;">No.</th>
                <th style="width: 9%;">รูป</th>
                <th style="width: 18%;">โปรโมชั่น</th>
                <th style="width: 15%;">CODE</th>
                <th style="width: 10%;">ส่วนลด</th>
                <th style="width: 14%;">เริ่มต้น</th>
                <th style="width: 14%;">สิ้นสุด</th>
                <th style="width: 10%;">สถานะ</th>
                <th style="width: 12%;">จัดการ</th>
              </tr>
            </thead>

            <tbody>
              <?php $runNumber = 1; ?>
              <?php foreach ($resultPro as $row_pro): ?>
                <?php
                $date_now = new DateTime();
                $date_end = new DateTime(date('m/d/Y H:i:s', strtotime($row_pro['pro_end'])));
                $isExpired = ($date_now > $date_end);
                ?>
                <tr>
                  <td class="text-center fw-bold"><?= $runNumber++; ?></td>

                  <td class="text-center">
                    <img src="pro_img/<?= htmlspecialchars($row_pro['pro_img']); ?>" class="promo-img"
                      alt="promotion">
                  </td>

                  <td>
                    <div class="promo-name"><?= htmlspecialchars($row_pro['pro_name']); ?></div>
                  </td>

                  <td>
                    <span class="promo-code">
                      <i class="fas fa-ticket-alt"></i>
                      <?= htmlspecialchars($row_pro['pro_code']); ?>
                    </span>
                  </td>

                  <td>
                    <span class="discount-badge">
                      <i class="fas fa-percent"></i>
                      <?= htmlspecialchars($row_pro['pro_discount']); ?> %
                    </span>
                  </td>

                  <td><?= date('d-m-Y H:i:s', strtotime($row_pro['pro_start'])); ?></td>
                  <td><?= date('d-m-Y H:i:s', strtotime($row_pro['pro_end'])); ?></td>

                  <td>
                    <?php if ($isExpired): ?>
                      <span class="status-badge status-inactive">
                        <i class="fas fa-clock"></i> ปิดใช้งาน
                      </span>
                    <?php else: ?>
                      <span class="status-badge status-active">
                        <i class="fas fa-check-circle"></i> ใช้งาน
                      </span>
                    <?php endif; ?>
                  </td>

                  <td class="text-center">
                    <div class="action-group">
                      <a href="promotion.php?act=edit&pro_id=<?= (int)$row_pro['pro_id']; ?>"
                        class="btn-action btn-edit" title="แก้ไข">
                        <i class="fas fa-pen"></i>
                      </a>

                      <button class="btn-action btn-delete"
                        onclick="confirmDelete(event, '<?= (int)$row_pro['pro_id']; ?>')"
                        title="ลบ">
                        <i class="fas fa-trash"></i>
                      </button>
                    </div>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>

          <?php if (empty($resultPro)): ?>
            <div class="empty-cover">ยังไม่มีข้อมูลโปรโมชั่น</div>
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

    function confirmDelete(event, pro_id) {
      event.preventDefault();

      Swal.fire({
        title: 'ลบโปรโมชั่น?',
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
          window.location.href = "promotion_del.php?pro_id=" + encodeURIComponent(pro_id);
        }
      });
    }
  </script>

</body>

</html>