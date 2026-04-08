<?php
session_start();
require_once("../condb.php");
$menu = "report";

if (!isset($_SESSION['m_id'])) {
    header("Location: ../login.php");
    exit();
}

$date_start = $_GET['date_start'] ?? '';
$date_end   = $_GET['date_end'] ?? '';

$where = "";
$params = [];

if (!empty($date_start) && !empty($date_end)) {
    $where = " WHERE r.pickup_date BETWEEN ? AND ? ";
    $params[] = $date_start;
    $params[] = $date_end;
} elseif (!empty($date_start)) {
    $where = " WHERE r.pickup_date >= ? ";
    $params[] = $date_start;
} elseif (!empty($date_end)) {
    $where = " WHERE r.pickup_date <= ? ";
    $params[] = $date_end;
}

// ====== สรุปยอดรวม ======
$sqlTotal = "
    SELECT 
        COUNT(*) AS total_orders,
        COALESCE(SUM(r.total_amount), 0) AS total_sales,
        COALESCE(SUM(r.deposit_amount), 0) AS total_deposit
    FROM tbl_reservation r
    $where
";
$stmtTotal = $conn->prepare($sqlTotal);
$stmtTotal->execute($params);
$total = $stmtTotal->fetch(PDO::FETCH_ASSOC);

// ====== สินค้าขายดี ======
$sqlTop = "
    SELECT 
        p.p_name,
        SUM(rd.qty) AS total_qty,
        SUM(rd.subtotal) AS total_amount
    FROM tbl_reservation_detail rd
    INNER JOIN tbl_product p ON rd.p_id = p.p_id
    INNER JOIN tbl_reservation r ON rd.reserve_id = r.reserve_id
    $where
    GROUP BY rd.p_id, p.p_name
    ORDER BY total_qty DESC
    LIMIT 5
";
$stmtTop = $conn->prepare($sqlTop);
$stmtTop->execute($params);
$topProducts = $stmtTop->fetchAll(PDO::FETCH_ASSOC);

// ====== รายการจอง ======
$sqlReserve = "
    SELECT 
        r.reserve_id,
        r.reserve_name,
        r.reserve_phone,
        r.pickup_date,
        r.pickup_time,
        r.total_amount,
        r.deposit_amount,
        (r.total_amount - r.deposit_amount) AS remain_amount,
        r.created_at
    FROM tbl_reservation r
    $where
    ORDER BY r.reserve_id ASC
";
$stmtReserve = $conn->prepare($sqlReserve);
$stmtReserve->execute($params);
$reservations = $stmtReserve->fetchAll(PDO::FETCH_ASSOC);

// สำหรับส่งค่าไป export pdf
$queryString = http_build_query([
    'date_start' => $date_start,
    'date_end'   => $date_end
]);
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>รายงานระบบ</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

    <style>
        body {
            background: #f3f5fb;
            font-family: "Prompt", sans-serif;
            color: #2f3542;
        }

        .content-wrapper {
            margin-left: 230px;
            padding: 24px;
            min-height: 100vh;
            background: #f3f5fb;
        }

        .hero-card {
            background: linear-gradient(135deg, #cfd7ff 0%, #dfe6ff 100%);
            border-radius: 20px;
            border: 1px solid #d9e1fb;
            padding: 22px 24px;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 18px;
            flex-wrap: wrap;
        }

        .hero-title {
            font-size: 30px;
            font-weight: 800;
            color: #253045;
            margin: 0 0 6px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .hero-subtitle {
            margin: 0;
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

        .summary-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 16px;
            margin-bottom: 20px;
        }

        .summary-card {
            background: #fff;
            border: 1px solid #e9edf5;
            border-radius: 18px;
            box-shadow: 0 10px 24px rgba(15, 23, 42, 0.04);
            padding: 18px 20px;
        }

        .summary-icon {
            width: 52px;
            height: 52px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            margin-bottom: 12px;
            color: #fff;
            box-shadow: 0 10px 18px rgba(88, 101, 242, 0.16);
        }

        .summary-label {
            color: #8b95a7;
            font-size: 13px;
            margin-bottom: 8px;
        }

        .summary-value {
            font-size: 28px;
            font-weight: 800;
            color: #253045;
            line-height: 1.1;
        }

        .filter-card,
        .report-card {
            background: #fff;
            border: 1px solid #e9edf5;
            border-radius: 20px;
            box-shadow: 0 10px 24px rgba(15, 23, 42, 0.04);
            overflow: hidden;
        }

        .filter-card {
            margin-bottom: 20px;
        }

        .filter-card .card-body,
        .report-card .card-body {
            padding: 20px 22px;
        }

        .report-card {
            margin-bottom: 20px;
        }

        .report-card .card-header {
            border: none;
            background: #fff;
            color: #253045;
            font-size: 20px;
            font-weight: 800;
            padding: 18px 22px 8px;
        }

        .report-card .card-subtitle {
            color: #96a0b2;
            font-size: 13px;
            padding: 0 22px 10px;
        }

        .form-label {
            font-weight: 700;
            color: #58677d;
        }

        .form-control {
            border-radius: 12px;
            border: 1px solid #dbe3ef;
            min-height: 44px;
        }

        .form-control:focus {
            border-color: #6478ff;
            box-shadow: 0 0 0 0.15rem rgba(100, 120, 255, 0.12);
        }

        .btn {
            border-radius: 12px;
            font-weight: 700;
            padding: 10px 16px;
        }

        .btn-primary {
            background: linear-gradient(135deg, #6478ff, #5865f2);
            border: none;
        }

        .btn-secondary {
            background: #eef2ff;
            color: #5865f2;
            border: 1px solid #dbe2ff;
        }

        .btn-secondary:hover {
            background: #e2e8ff;
            color: #4654cf;
            border-color: #cfd8ff;
        }

        .btn-success {
            background: linear-gradient(135deg, #35c98f, #20b97a);
            border: none;
        }

        .btn-danger {
            background: linear-gradient(135deg, #ff6b81, #f5556d);
            border: none;
        }

        .table {
            margin-bottom: 0;
        }

        .table thead th {
            background: #f8faff;
            color: #8d96a8;
            font-weight: 700;
            border-bottom: 1px solid #edf1f7;
            text-align: center;
            vertical-align: middle;
            white-space: nowrap;
            padding: 14px 12px;
        }

        .table tbody td {
            vertical-align: middle;
            padding: 14px 12px;
            border-color: #f0f3f8;
        }

        .table-hover tbody tr:hover {
            background-color: #fafcff;
        }

        .amount-text {
            font-weight: 800;
            color: #253045;
            white-space: nowrap;
        }

        .rank-badge {
            width: 34px;
            height: 34px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            background: #eef2ff;
            color: #5865f2;
            font-weight: 800;
            font-size: 13px;
        }

        @media print {

            .no-print,
            .main-sidebar,
            .navbar,
            .filter-card,
            .hero-card {
                display: none !important;
            }

            .content-wrapper {
                margin-left: 0 !important;
                padding: 0 !important;
            }

            body {
                background: #fff !important;
            }

            .summary-card,
            .report-card {
                box-shadow: none !important;
                border: 1px solid #ddd !important;
            }
        }

        @media (max-width: 991px) {
            .content-wrapper {
                margin-left: 0;
                padding: 16px;
            }

            .summary-grid {
                grid-template-columns: 1fr;
            }

            .hero-title {
                font-size: 24px;
            }
        }
    </style>
</head>

<body>

    <?php include("menu.php"); ?>

    <div class="content-wrapper">
        <div class="container-fluid">

            <div class="hero-card">
                <div>
                    <h1 class="hero-title">
                        <i class="fas fa-chart-bar"></i>
                        รายงานระบบ
                    </h1>
                    <p class="hero-subtitle">สรุปข้อมูลการจอง สินค้าขายดี และรายการจองทั้งหมด</p>
                </div>
                <div class="hero-icon">
                    <i class="fas fa-file-alt"></i>
                </div>
            </div>

            <div class="card filter-card mb-4 no-print">
                <div class="card-body">
                    <form method="GET" class="row g-3 align-items-end">
                        <div class="col-md-3">
                            <label class="form-label">วันที่เริ่มต้น</label>
                            <input type="date" name="date_start" class="form-control"
                                value="<?= htmlspecialchars($date_start) ?>">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">วันที่สิ้นสุด</label>
                            <input type="date" name="date_end" class="form-control"
                                value="<?= htmlspecialchars($date_end) ?>">
                        </div>

                        <div class="col-md-6">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search me-1"></i> ค้นหา
                            </button>

                            <a href="report.php" class="btn btn-secondary">
                                <i class="fas fa-sync-alt me-1"></i> ล้างค่า
                            </a>

                            <button type="button" onclick="window.print();" class="btn btn-success">
                                <i class="fas fa-print me-1"></i> พิมพ์รายงาน
                            </button>

                            <a href="report_pdf.php?<?= $queryString ?>" target="_blank" class="btn btn-danger">
                                <i class="fas fa-file-pdf me-1"></i> Export PDF
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <div class="summary-grid">
                <div class="summary-card">
                    <div class="summary-icon" style="background: linear-gradient(135deg,#6478ff,#5865f2);">
                        <i class="fas fa-receipt"></i>
                    </div>
                    <div class="summary-label">จำนวนรายการจอง</div>
                    <div class="summary-value"><?= number_format($total['total_orders']) ?> รายการ</div>
                </div>

                <div class="summary-card">
                    <div class="summary-icon" style="background: linear-gradient(135deg,#35c98f,#20b97a);">
                        <i class="fas fa-coins"></i>
                    </div>
                    <div class="summary-label">ยอดจองรวม</div>
                    <div class="summary-value"><?= number_format($total['total_sales'], 2) ?> บาท</div>
                </div>

                <div class="summary-card">
                    <div class="summary-icon" style="background: linear-gradient(135deg,#f4b267,#e59a44);">
                        <i class="fas fa-wallet"></i>
                    </div>
                    <div class="summary-label">ยอดมัดจำรวม</div>
                    <div class="summary-value"><?= number_format($total['total_deposit'], 2) ?> บาท</div>
                </div>
            </div>

            <div class="card report-card">
                <div class="card-header">
                    <i class="fas fa-star me-2 text-warning"></i>สินค้าขายดี 5 อันดับ
                </div>
                <div class="card-subtitle">สรุปสินค้าที่มียอดขายสูงสุดตามช่วงวันที่ที่เลือก</div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th width="80">อันดับ</th>
                                    <th>ชื่อสินค้า</th>
                                    <th width="150">จำนวนขาย</th>
                                    <th width="180">ยอดรวม</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($topProducts)) { ?>
                                    <?php $i = 1; ?>
                                    <?php foreach ($topProducts as $row) { ?>
                                        <tr>
                                            <td class="text-center"><span class="rank-badge"><?= $i++ ?></span></td>
                                            <td><?= htmlspecialchars($row['p_name']) ?></td>
                                            <td class="text-center"><?= number_format($row['total_qty']) ?></td>
                                            <td class="text-end amount-text"><?= number_format($row['total_amount'], 2) ?> บาท
                                            </td>
                                        </tr>
                                    <?php } ?>
                                <?php } else { ?>
                                    <tr>
                                        <td colspan="4" class="text-center text-muted">ยังไม่มีข้อมูล</td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="card report-card">
                <div class="card-header">
                    <i class="fas fa-clipboard-list me-2 text-success"></i>รายการจองสินค้า
                </div>
                <div class="card-subtitle">สรุปรายการลูกค้าที่จองสินค้า พร้อมยอดรวม มัดจำ และยอดคงเหลือ</div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th width="70">ลำดับ</th>
                                    <th>ชื่อลูกค้า</th>
                                    <th>เบอร์โทร</th>
                                    <th>วันที่รับสินค้า</th>
                                    <th>เวลา</th>
                                    <th>ยอดรวม</th>
                                    <th>มัดจำ</th>
                                    <th>คงเหลือ</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($reservations)) { ?>
                                    <?php $no = 1; ?>
                                    <?php foreach ($reservations as $row) { ?>
                                        <tr>
                                            <td class="text-center"><?= $no++ ?></td>
                                            <td><?= htmlspecialchars($row['reserve_name']) ?></td>
                                            <td><?= htmlspecialchars($row['reserve_phone']) ?></td>
                                            <td class="text-center"><?= date('d/m/Y', strtotime($row['pickup_date'])) ?></td>
                                            <td class="text-center"><?= date('H:i', strtotime($row['pickup_time'])) ?> น.</td>
                                            <td class="text-end amount-text"><?= number_format($row['total_amount'], 2) ?></td>
                                            <td class="text-end"><?= number_format($row['deposit_amount'], 2) ?></td>
                                            <td class="text-end"><?= number_format($row['remain_amount'], 2) ?></td>
                                        </tr>
                                    <?php } ?>
                                <?php } else { ?>
                                    <tr>
                                        <td colspan="8" class="text-center text-muted">ไม่พบข้อมูลการจอง</td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <script src="../assets/js/bootstrap.bundle.min.js"></script>
</body>

</html>