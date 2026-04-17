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

/*
|--------------------------------------------------------------------------
| WHERE สำหรับฝั่งจอง (อิง pickup_date)
|--------------------------------------------------------------------------
*/
$whereReserve = "";
$paramsReserve = [];

if (!empty($date_start) && !empty($date_end)) {
    $whereReserve = " WHERE r.pickup_date BETWEEN ? AND ? ";
    $paramsReserve[] = $date_start;
    $paramsReserve[] = $date_end;
} elseif (!empty($date_start)) {
    $whereReserve = " WHERE r.pickup_date >= ? ";
    $paramsReserve[] = $date_start;
} elseif (!empty($date_end)) {
    $whereReserve = " WHERE r.pickup_date <= ? ";
    $paramsReserve[] = $date_end;
}

/*
|--------------------------------------------------------------------------
| WHERE สำหรับฝั่งขาย (อิง pay_datetime / created_at ของ slip)
|--------------------------------------------------------------------------
*/
$whereSale = " WHERE 1=1 ";
$paramsSale = [];

if (!empty($date_start) && !empty($date_end)) {
    $whereSale .= " AND DATE(ps.pay_datetime) BETWEEN ? AND ? ";
    $paramsSale[] = $date_start;
    $paramsSale[] = $date_end;
} elseif (!empty($date_start)) {
    $whereSale .= " AND DATE(ps.pay_datetime) >= ? ";
    $paramsSale[] = $date_start;
} elseif (!empty($date_end)) {
    $whereSale .= " AND DATE(ps.pay_datetime) <= ? ";
    $paramsSale[] = $date_end;
}

/*
|--------------------------------------------------------------------------
| สรุปฝั่งจอง
|--------------------------------------------------------------------------
*/
$sqlReserveTotal = "
    SELECT 
        COUNT(*) AS total_orders,
        COALESCE(SUM(r.total_amount), 0) AS total_sales,
        COALESCE(SUM(r.deposit_amount), 0) AS total_deposit
    FROM tbl_reservation r
    $whereReserve
";
$stmtReserveTotal = $conn->prepare($sqlReserveTotal);
$stmtReserveTotal->execute($paramsReserve);
$reserveTotal = $stmtReserveTotal->fetch(PDO::FETCH_ASSOC);

/*
|--------------------------------------------------------------------------
| สรุปฝั่งขาย
|--------------------------------------------------------------------------
*/
$sqlSaleTotal = "
    SELECT 
        COUNT(DISTINCT ps.slip_id) AS total_sales_orders,
        COALESCE(SUM(ps.pay_amount), 0) AS total_sales_amount
    FROM tbl_payment_slip ps
    $whereSale
      AND ps.note LIKE 'sale%'
";
$stmtSaleTotal = $conn->prepare($sqlSaleTotal);
$stmtSaleTotal->execute($paramsSale);
$saleTotal = $stmtSaleTotal->fetch(PDO::FETCH_ASSOC);

/*
|--------------------------------------------------------------------------
| สรุปรวมทั้งหมด
|--------------------------------------------------------------------------
*/
$allOrders = (int)($reserveTotal['total_orders'] ?? 0) + (int)($saleTotal['total_sales_orders'] ?? 0);
$allIncome = (float)($reserveTotal['total_sales'] ?? 0) + (float)($saleTotal['total_sales_amount'] ?? 0);

/*
|--------------------------------------------------------------------------
| สินค้าขายดีฝั่งจอง
|--------------------------------------------------------------------------
*/
$sqlTopReserve = "
    SELECT 
        p.p_name,
        SUM(rd.qty) AS total_qty,
        SUM(rd.subtotal) AS total_amount
    FROM tbl_reservation_detail rd
    INNER JOIN tbl_product p ON rd.p_id = p.p_id
    INNER JOIN tbl_reservation r ON rd.reserve_id = r.reserve_id
    $whereReserve
    GROUP BY rd.p_id, p.p_name
    ORDER BY total_qty DESC
    LIMIT 5
";
$stmtTopReserve = $conn->prepare($sqlTopReserve);
$stmtTopReserve->execute($paramsReserve);
$topReserveProducts = $stmtTopReserve->fetchAll(PDO::FETCH_ASSOC);

/*
|--------------------------------------------------------------------------
| สินค้าขายดีฝั่งขาย
|--------------------------------------------------------------------------
*/
$sqlTopSale = "
    SELECT 
        p.p_name,
        SUM(sd.qty) AS total_qty,
        SUM(sd.subtotal) AS total_amount
    FROM tbl_sale_detail sd
    INNER JOIN tbl_product p ON sd.p_id = p.p_id
    INNER JOIN tbl_payment_slip ps ON sd.slip_id = ps.slip_id
    $whereSale
      AND ps.note LIKE 'sale%'
    GROUP BY sd.p_id, p.p_name
    ORDER BY total_qty DESC
    LIMIT 5
";
$stmtTopSale = $conn->prepare($sqlTopSale);
$stmtTopSale->execute($paramsSale);
$topSaleProducts = $stmtTopSale->fetchAll(PDO::FETCH_ASSOC);

/*
|--------------------------------------------------------------------------
| รายการจอง
|--------------------------------------------------------------------------
*/
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
        r.order_type,
        r.payment_status,
        r.created_at
    FROM tbl_reservation r
    $whereReserve
    ORDER BY r.reserve_id DESC
";
$stmtReserve = $conn->prepare($sqlReserve);
$stmtReserve->execute($paramsReserve);
$reservations = $stmtReserve->fetchAll(PDO::FETCH_ASSOC);

/*
|--------------------------------------------------------------------------
| รายการขายปกติ
|--------------------------------------------------------------------------
*/
$sqlSales = "
    SELECT 
        ps.slip_id,
        ps.payer_name,
        ps.payer_phone,
        ps.pay_amount,
        ps.pay_datetime,
        ps.status,
        ps.note,
        COUNT(sd.sd_id) AS total_items
    FROM tbl_payment_slip ps
    LEFT JOIN tbl_sale_detail sd ON ps.slip_id = sd.slip_id
    $whereSale
      AND ps.note LIKE 'sale%'
    GROUP BY ps.slip_id, ps.payer_name, ps.payer_phone, ps.pay_amount, ps.pay_datetime, ps.status, ps.note
    ORDER BY ps.slip_id DESC
";
$stmtSales = $conn->prepare($sqlSales);
$stmtSales->execute($paramsSale);
$sales = $stmtSales->fetchAll(PDO::FETCH_ASSOC);

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
        grid-template-columns: repeat(4, 1fr);
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
        font-size: 26px;
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

    .section-chip {
        display: inline-block;
        padding: 6px 12px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 700;
    }

    .chip-reserve {
        background: #fff3cd;
        color: #856404;
    }

    .chip-sale {
        background: #d1fae5;
        color: #065f46;
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
                    <p class="hero-subtitle">สรุปข้อมูลการจองและการขายแบบแยกส่วนอย่างชัดเจน</p>
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
                        <i class="fas fa-layer-group"></i>
                    </div>
                    <div class="summary-label">รายการทั้งหมด</div>
                    <div class="summary-value"><?= number_format($allOrders) ?> รายการ</div>
                </div>

                <div class="summary-card">
                    <div class="summary-icon" style="background: linear-gradient(135deg,#35c98f,#20b97a);">
                        <i class="fas fa-coins"></i>
                    </div>
                    <div class="summary-label">ยอดรวมทั้งหมด</div>
                    <div class="summary-value"><?= number_format($allIncome, 2) ?> บาท</div>
                </div>

                <div class="summary-card">
                    <div class="summary-icon" style="background: linear-gradient(135deg,#f4b267,#e59a44);">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <div class="summary-label">รายการจอง</div>
                    <div class="summary-value"><?= number_format($reserveTotal['total_orders']) ?> รายการ</div>
                </div>

                <div class="summary-card">
                    <div class="summary-icon" style="background: linear-gradient(135deg,#34d399,#10b981);">
                        <i class="fas fa-shopping-bag"></i>
                    </div>
                    <div class="summary-label">รายการขาย</div>
                    <div class="summary-value"><?= number_format($saleTotal['total_sales_orders']) ?> รายการ</div>
                </div>
            </div>

            <div class="card report-card">
                <div class="card-header">
                    <span class="section-chip chip-reserve">ฝั่งจองสินค้า</span>
                    <span class="ms-2">สรุปการจอง</span>
                </div>
                <div class="card-subtitle">ยอดจองรวมและยอดมัดจำจากรายการจอง</div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="summary-card">
                                <div class="summary-label">จำนวนรายการจอง</div>
                                <div class="summary-value"><?= number_format($reserveTotal['total_orders']) ?></div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="summary-card">
                                <div class="summary-label">ยอดจองรวม</div>
                                <div class="summary-value"><?= number_format($reserveTotal['total_sales'], 2) ?></div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="summary-card">
                                <div class="summary-label">ยอดมัดจำรวม</div>
                                <div class="summary-value"><?= number_format($reserveTotal['total_deposit'], 2) ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card report-card">
                <div class="card-header">
                    <span class="section-chip chip-sale">ฝั่งขายปกติ</span>
                    <span class="ms-2">สรุปการขาย</span>
                </div>
                <div class="card-subtitle">ยอดขายที่ชำระผ่านสลิปขายปกติ</div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="summary-card">
                                <div class="summary-label">จำนวนรายการขาย</div>
                                <div class="summary-value"><?= number_format($saleTotal['total_sales_orders']) ?></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="summary-card">
                                <div class="summary-label">ยอดขายรวม</div>
                                <div class="summary-value"><?= number_format($saleTotal['total_sales_amount'], 2) ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-4">
                <div class="col-lg-6">
                    <div class="card report-card h-100">
                        <div class="card-header">
                            <i class="fas fa-star me-2 text-warning"></i>สินค้าขายดีฝั่งจอง
                        </div>
                        <div class="card-subtitle">Top 5 จาก tbl_reservation_detail</div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead>
                                        <tr>
                                            <th width="70">อันดับ</th>
                                            <th>ชื่อสินค้า</th>
                                            <th>จำนวนขาย</th>
                                            <th>ยอดรวม</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($topReserveProducts)) { ?>
                                        <?php $i = 1;
                                            foreach ($topReserveProducts as $row) { ?>
                                        <tr>
                                            <td class="text-center"><span class="rank-badge"><?= $i++ ?></span></td>
                                            <td><?= htmlspecialchars($row['p_name']) ?></td>
                                            <td class="text-center"><?= number_format($row['total_qty']) ?></td>
                                            <td class="text-end amount-text">
                                                <?= number_format($row['total_amount'], 2) ?></td>
                                        </tr>
                                        <?php } ?>
                                        <?php } else { ?>
                                        <tr>
                                            <td colspan="4" class="text-center text-muted">ไม่พบข้อมูล</td>
                                        </tr>
                                        <?php } ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="card report-card h-100">
                        <div class="card-header">
                            <i class="fas fa-fire me-2 text-danger"></i>สินค้าขายดีฝั่งขายปกติ
                        </div>
                        <div class="card-subtitle">Top 5 จาก tbl_sale_detail</div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead>
                                        <tr>
                                            <th width="70">อันดับ</th>
                                            <th>ชื่อสินค้า</th>
                                            <th>จำนวนขาย</th>
                                            <th>ยอดรวม</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($topSaleProducts)) { ?>
                                        <?php $i = 1;
                                            foreach ($topSaleProducts as $row) { ?>
                                        <tr>
                                            <td class="text-center"><span class="rank-badge"><?= $i++ ?></span></td>
                                            <td><?= htmlspecialchars($row['p_name']) ?></td>
                                            <td class="text-center"><?= number_format($row['total_qty']) ?></td>
                                            <td class="text-end amount-text">
                                                <?= number_format($row['total_amount'], 2) ?></td>
                                        </tr>
                                        <?php } ?>
                                        <?php } else { ?>
                                        <tr>
                                            <td colspan="4" class="text-center text-muted">ไม่พบข้อมูล</td>
                                        </tr>
                                        <?php } ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card report-card">
                <div class="card-header">
                    <i class="fas fa-clipboard-list me-2 text-warning"></i>รายการจองสินค้า
                </div>
                <div class="card-subtitle">สรุปรายการลูกค้าที่จองสินค้า พร้อมยอดรวม มัดจำ และยอดคงเหลือ</div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th width="70">ลำดับ</th>
                                    <th>เลขที่จอง</th>
                                    <th>ชื่อลูกค้า</th>
                                    <th>เบอร์โทร</th>
                                    <th>วันที่รับสินค้า</th>
                                    <th>เวลา</th>
                                    <th>ยอดรวม</th>
                                    <th>มัดจำ</th>
                                    <th>คงเหลือ</th>
                                    <th>สถานะชำระ</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($reservations)) { ?>
                                <?php $no = 1; ?>
                                <?php foreach ($reservations as $row) { ?>
                                <tr>
                                    <td class="text-center"><?= $no++ ?></td>
                                    <td class="text-center">#<?= (int)$row['reserve_id'] ?></td>
                                    <td><?= htmlspecialchars($row['reserve_name']) ?></td>
                                    <td><?= htmlspecialchars($row['reserve_phone']) ?></td>
                                    <td class="text-center"><?= date('d/m/Y', strtotime($row['pickup_date'])) ?></td>
                                    <td class="text-center"><?= date('H:i', strtotime($row['pickup_time'])) ?> น.</td>
                                    <td class="text-end amount-text"><?= number_format($row['total_amount'], 2) ?></td>
                                    <td class="text-end"><?= number_format($row['deposit_amount'], 2) ?></td>
                                    <td class="text-end"><?= number_format($row['remain_amount'], 2) ?></td>
                                    <td class="text-center"><?= htmlspecialchars($row['payment_status']) ?></td>
                                </tr>
                                <?php } ?>
                                <?php } else { ?>
                                <tr>
                                    <td colspan="10" class="text-center text-muted">ไม่พบข้อมูลการจอง</td>
                                </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="card report-card">
                <div class="card-header">
                    <i class="fas fa-cash-register me-2 text-success"></i>รายการขายปกติ
                </div>
                <div class="card-subtitle">รายการขายที่ชำระเงินแล้วผ่านระบบสลิปขายปกติ</div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th width="70">ลำดับ</th>
                                    <th>เลขที่ขาย</th>
                                    <th>ผู้ชำระ</th>
                                    <th>เบอร์โทร</th>
                                    <th>จำนวนรายการ</th>
                                    <th>วันเวลาชำระ</th>
                                    <th>ยอดชำระ</th>
                                    <th>สถานะ</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($sales)) { ?>
                                <?php $no = 1; ?>
                                <?php foreach ($sales as $row) { ?>
                                <tr>
                                    <td class="text-center"><?= $no++ ?></td>
                                    <td class="text-center">#<?= (int)$row['slip_id'] ?></td>
                                    <td><?= htmlspecialchars($row['payer_name']) ?></td>
                                    <td><?= htmlspecialchars($row['payer_phone']) ?></td>
                                    <td class="text-center"><?= number_format($row['total_items']) ?></td>
                                    <td class="text-center"><?= date('d/m/Y H:i', strtotime($row['pay_datetime'])) ?>
                                    </td>
                                    <td class="text-end amount-text"><?= number_format($row['pay_amount'], 2) ?></td>
                                    <td class="text-center"><?= htmlspecialchars($row['status']) ?></td>
                                </tr>
                                <?php } ?>
                                <?php } else { ?>
                                <tr>
                                    <td colspan="8" class="text-center text-muted">ไม่พบข้อมูลการขาย</td>
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