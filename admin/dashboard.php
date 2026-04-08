<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$menu = "dashboard";
include 'head.php';
include '../condb.php';

/* =========================
   1) จำนวนการจองทั้งหมด
========================= */
$stmtTotalReserve = $conn->prepare("
    SELECT COUNT(*) AS total_reserve
    FROM tbl_reservation
");
$stmtTotalReserve->execute();
$rowTotalReserve = $stmtTotalReserve->fetch(PDO::FETCH_ASSOC);
$total_reserve = $rowTotalReserve['total_reserve'] ?? 0;

/* =========================
   2) จำนวนการขายทั้งหมด
   ใช้ tbl_payment_slip
========================= */
$stmtTotalSales = $conn->prepare("
    SELECT COUNT(*) AS total_sales
    FROM tbl_payment_slip
");
$stmtTotalSales->execute();
$rowTotalSales = $stmtTotalSales->fetch(PDO::FETCH_ASSOC);
$total_sales = $rowTotalSales['total_sales'] ?? 0;

/* =========================
   3) ยอดขายวันนี้
   ใช้ tbl_payment_slip
========================= */
$stmtTodaySales = $conn->prepare("
    SELECT IFNULL(SUM(pay_amount), 0) AS today_sales
    FROM tbl_payment_slip
    WHERE DATE(created_at) = CURDATE()
");
$stmtTodaySales->execute();
$rowTodaySales = $stmtTodaySales->fetch(PDO::FETCH_ASSOC);
$today_sales = $rowTodaySales['today_sales'] ?? 0;

/* =========================
   4) จำนวนสินค้า
========================= */
$stmtTotalProducts = $conn->prepare("
    SELECT COUNT(*) AS total_products
    FROM tbl_product
");
$stmtTotalProducts->execute();
$rowTotalProducts = $stmtTotalProducts->fetch(PDO::FETCH_ASSOC);
$total_products = $rowTotalProducts['total_products'] ?? 0;

/* =========================
   5) การจองล่าสุด
========================= */
$stmtLatestReserve = $conn->prepare("
    SELECT reserve_id, reserve_name, created_at, pickup_date, pickup_time, total_amount
    FROM tbl_reservation
    ORDER BY reserve_id DESC
    LIMIT 5
");
$stmtLatestReserve->execute();
$latestReserve = $stmtLatestReserve->fetchAll(PDO::FETCH_ASSOC);

/* =========================
   6) การขายล่าสุด
========================= */
$stmtLatestSales = $conn->prepare("
    SELECT 
        slip_id,
        payer_name,
        payer_phone,
        pay_amount,
        pay_datetime,
        created_at,
        status
    FROM tbl_payment_slip
    ORDER BY slip_id DESC
    LIMIT 5
");
$stmtLatestSales->execute();
$latestSales = $stmtLatestSales->fetchAll(PDO::FETCH_ASSOC);

/* =========================
   7) สินค้าขายดี 3 อันดับแรกของเดือนนี้
   นับจาก tbl_reservation + tbl_reservation_detail
   เฉพาะรายการที่จ่ายแล้ว
========================= */
$stmtBest = $conn->prepare("
    SELECT 
        p.p_id,
        p.p_name,
        COALESCE(SUM(rd.qty), 0) AS total_sold
    FROM tbl_reservation r
    INNER JOIN tbl_reservation_detail rd ON r.reserve_id = rd.reserve_id
    INNER JOIN tbl_product p ON rd.p_id = p.p_id
    WHERE MONTH(r.created_at) = MONTH(CURRENT_DATE())
      AND YEAR(r.created_at) = YEAR(CURRENT_DATE())
      AND r.payment_status = 'paid'
    GROUP BY p.p_id, p.p_name
    ORDER BY total_sold DESC, p.p_name ASC
    LIMIT 3
");
$stmtBest->execute();
$bestProducts = $stmtBest->fetchAll(PDO::FETCH_ASSOC);

/* =========================
   8) สินค้าขายไม่ดี 5 อันดับของเดือนนี้
   เอาสินค้าทุกตัวมาคิด แม้เดือนนี้ขาย 0
========================= */
$stmtLow = $conn->prepare("
    SELECT 
        p.p_id,
        p.p_name,
        p.p_stock,
        COALESCE(SUM(
            CASE
                WHEN MONTH(r.created_at) = MONTH(CURRENT_DATE())
                 AND YEAR(r.created_at) = YEAR(CURRENT_DATE())
                 AND r.payment_status = 'paid'
                THEN rd.qty
                ELSE 0
            END
        ), 0) AS total_sold
    FROM tbl_product p
    LEFT JOIN tbl_reservation_detail rd ON p.p_id = rd.p_id
    LEFT JOIN tbl_reservation r ON rd.reserve_id = r.reserve_id
    GROUP BY p.p_id, p.p_name, p.p_stock
    ORDER BY total_sold ASC, p.p_name ASC
    LIMIT 3
");
$stmtLow->execute();
$lowProducts = $stmtLow->fetchAll(PDO::FETCH_ASSOC);

/* =========================
   helper
========================= */
$maxBestSold = 1;
if (!empty($bestProducts)) {
    $maxBestSold = max(array_column($bestProducts, 'total_sold'));
    if ($maxBestSold <= 0) {
        $maxBestSold = 1;
    }
}

$maxLowSold = 1;
if (!empty($lowProducts)) {
    $maxLowSold = max(array_column($lowProducts, 'total_sold'));
    if ($maxLowSold <= 0) {
        $maxLowSold = 1;
    }
}
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>Bakery Dashboard</title>
    <style>
        body {
            font-family: 'Prompt', sans-serif;
            background: #f3f5fb !important;
            color: #2f3542;
        }

        .content-wrapper {
            background: #f3f5fb !important;
            min-height: 100vh;
        }

        .dashboard-wrap {
            padding: 20px;
        }

        .page-head {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
            margin-bottom: 18px;
        }

        .page-title {
            font-size: 28px;
            font-weight: 800;
            margin: 0;
            color: #1f2937;
        }

        .page-subtitle {
            color: #8a94a6;
            font-size: 14px;
            margin-top: 4px;
        }

        .breadcrumb-mini {
            font-size: 13px;
            color: #98a2b3;
            background: #fff;
            border: 1px solid #e7ebf3;
            border-radius: 12px;
            padding: 10px 14px;
        }

        .top-grid {
            display: grid;
            grid-template-columns: 1.35fr 1fr 1fr 1fr 1fr;
            gap: 16px;
            margin-bottom: 18px;
        }

        .welcome-card,
        .stat-card,
        .dash-card {
            background: #fff;
            border: 1px solid #e9edf5;
            border-radius: 18px;
            box-shadow: 0 10px 24px rgba(15, 23, 42, 0.04);
        }

        .welcome-card {
            overflow: hidden;
            display: flex;
            flex-direction: column;
            min-height: 170px;
        }

        .welcome-top {
            background: linear-gradient(135deg, #cfd7ff 0%, #dfe6ff 100%);
            padding: 18px 20px 16px;
            position: relative;
            min-height: 120px;
        }

        .welcome-badge {
            color: #4f5bd5;
            font-size: 14px;
            font-weight: 700;
            margin-bottom: 6px;
        }

        .welcome-title {
            font-size: 15px;
            color: #50607a;
            margin: 0;
        }

        .welcome-illustration {
            position: absolute;
            right: 18px;
            bottom: 8px;
            font-size: 48px;
            opacity: 0.85;
        }

        .welcome-bottom {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 18px;
            padding: 16px 20px;
            flex-wrap: wrap;
        }

        .profile-box {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .profile-avatar {
            width: 52px;
            height: 52px;
            border-radius: 50%;
            background: linear-gradient(135deg, #ef4444, #6366f1);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            font-weight: 700;
            box-shadow: 0 8px 20px rgba(99, 102, 241, 0.25);
        }

        .profile-name {
            font-weight: 700;
            color: #253045;
            margin-bottom: 2px;
        }

        .profile-role {
            font-size: 13px;
            color: #8a94a6;
        }

        .mini-stats {
            display: flex;
            gap: 28px;
            flex-wrap: wrap;
        }

        .mini-stat-label {
            font-size: 12px;
            color: #97a0b1;
            margin-bottom: 4px;
        }

        .mini-stat-value {
            font-weight: 800;
            color: #253045;
        }

        .stat-card {
            padding: 18px 16px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            min-height: 98px;
        }

        .stat-info {
            min-width: 0;
        }

        .stat-label {
            font-size: 13px;
            color: #8b95a7;
            margin-bottom: 8px;
        }

        .stat-number {
            font-size: 31px;
            font-weight: 800;
            line-height: 1.05;
            color: #253045;
        }

        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: linear-gradient(135deg, #6478ff, #5865f2);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            flex-shrink: 0;
            box-shadow: 0 10px 18px rgba(88, 101, 242, 0.22);
        }

        .mid-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 18px;
            margin-bottom: 18px;
        }

        .bottom-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 18px;
        }

        .dash-card {
            padding: 0;
            overflow: hidden;
        }

        .dash-card-head {
            padding: 18px 20px 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        .dash-card-title {
            font-size: 20px;
            font-weight: 800;
            color: #263247;
            margin: 0;
        }

        .dash-card-sub {
            color: #98a2b3;
            font-size: 13px;
            margin-top: 4px;
        }

        .dash-card-body {
            padding: 0 20px 20px;
        }

        .table-modern {
            width: 100%;
            border-collapse: collapse;
            margin: 0;
        }

        .table-modern thead th {
            font-size: 13px;
            color: #8d96a8;
            font-weight: 700;
            background: #f8faff;
            border-bottom: 1px solid #edf1f7;
            padding: 13px 12px;
            text-align: left;
        }

        .table-modern tbody td {
            padding: 13px 12px;
            border-bottom: 1px solid #f0f3f8;
            color: #364152;
            vertical-align: middle;
        }

        .table-modern tbody tr:hover {
            background: #fafcff;
        }

        .amount-text {
            font-weight: 800;
            color: #3a4a63;
        }

        .tag-soft {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: #f5f7ff;
            color: #6270df;
            border-radius: 999px;
            padding: 7px 12px;
            font-size: 12px;
            font-weight: 700;
        }

        .product-list {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .product-row {
            display: grid;
            grid-template-columns: 54px 1fr 90px;
            align-items: center;
            gap: 14px;
        }

        .product-rank {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            background: linear-gradient(135deg, #ffcf70, #f0a93d);
            color: #6a4308;
            font-weight: 800;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 10px 18px rgba(240, 169, 61, 0.18);
        }

        .product-name {
            font-weight: 700;
            color: #253045;
            margin-bottom: 6px;
        }

        .product-meta {
            font-size: 13px;
            color: #8b95a7;
        }

        .product-total {
            text-align: right;
            font-weight: 800;
            color: #253045;
        }

        .progress-soft {
            width: 100%;
            height: 8px;
            background: #edf1f7;
            border-radius: 999px;
            overflow: hidden;
            margin-top: 8px;
        }

        .progress-soft span {
            display: block;
            height: 100%;
            border-radius: 999px;
            background: linear-gradient(90deg, #6478ff, #30c59b);
        }

        .low-list {
            display: flex;
            flex-direction: column;
            gap: 14px;
        }

        .low-item {
            padding: 14px 16px;
            border-radius: 14px;
            background: #fafcff;
            border: 1px solid #eef2f7;
        }

        .low-top {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            align-items: center;
            margin-bottom: 8px;
        }

        .low-name {
            font-weight: 700;
            color: #253045;
        }

        .low-sub {
            font-size: 13px;
            color: #8b95a7;
            margin-top: 4px;
        }

        .low-value {
            font-size: 13px;
            color: #8b95a7;
            font-weight: 700;
        }

        .empty-text {
            text-align: center;
            color: #9ca4b3;
            padding: 18px 0 !important;
        }

        @media (max-width: 1200px) {
            .top-grid {
                grid-template-columns: 1fr 1fr;
            }

            .welcome-card {
                grid-column: span 2;
            }
        }

        @media (max-width: 991px) {

            .top-grid,
            .mid-grid,
            .bottom-grid {
                grid-template-columns: 1fr;
            }

            .welcome-card {
                grid-column: span 1;
            }

            .page-title {
                font-size: 24px;
            }
        }

        @media (max-width: 768px) {
            .dashboard-wrap {
                padding: 14px;
            }

            .welcome-bottom,
            .page-head {
                flex-direction: column;
                align-items: flex-start;
            }

            .product-row {
                grid-template-columns: 44px 1fr;
            }

            .product-total {
                grid-column: 2 / 3;
                text-align: left;
            }
        }
    </style>
</head>

<body class="hold-transition sidebar-mini layout-fixed">
    <div class="wrapper">

        <?php include 'nav.php'; ?>
        <?php include 'menu.php'; ?>

        <div class="content-wrapper">
            <section class="content">
                <div class="container-fluid dashboard-wrap">

                    <div class="page-head">
                        <div>
                            <h1 class="page-title">DASHBOARD</h1>
                            <div class="page-subtitle">ภาพรวมร้านเบเกอรี่ แยกการจองและการขายอย่างชัดเจน</div>
                        </div>
                        <div class="breadcrumb-mini">Dashboards / Dashboard</div>
                    </div>

                    <div class="top-grid">
                        <div class="welcome-card">
                            <div class="welcome-top">
                                <div class="welcome-badge">Welcome Back !</div>
                                <p class="welcome-title">Bakery shop dashboard overview</p>
                                <div class="welcome-illustration">🧁</div>
                            </div>

                            <div class="welcome-bottom">
                                <div class="profile-box">
                                    <div class="profile-avatar">B</div>
                                    <div>
                                        <div class="profile-name">Bakery Store</div>
                                        <div class="profile-role">ร้านเบเกอรี่ / ระบบหลังบ้าน</div>
                                    </div>
                                </div>

                                <div class="mini-stats">
                                    <div>
                                        <div class="mini-stat-label">Top Products</div>
                                        <div class="mini-stat-value"><?= number_format(count($bestProducts)) ?></div>
                                    </div>
                                    <div>
                                        <div class="mini-stat-label">Reservations</div>
                                        <div class="mini-stat-value"><?= number_format($total_reserve) ?></div>
                                    </div>
                                    <div>
                                        <div class="mini-stat-label">Sales</div>
                                        <div class="mini-stat-value"><?= number_format($total_sales) ?></div>
                                    </div>
                                    <div>
                                        <div class="mini-stat-label">Revenue Today</div>
                                        <div class="mini-stat-value">฿<?= number_format($today_sales, 2) ?></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="stat-card">
                            <div class="stat-info">
                                <div class="stat-label">Reservations</div>
                                <div class="stat-number"><?= number_format($total_reserve) ?></div>
                            </div>
                            <div class="stat-icon">📅</div>
                        </div>

                        <div class="stat-card">
                            <div class="stat-info">
                                <div class="stat-label">Sales</div>
                                <div class="stat-number"><?= number_format($total_sales) ?></div>
                            </div>
                            <div class="stat-icon">🛒</div>
                        </div>

                        <div class="stat-card">
                            <div class="stat-info">
                                <div class="stat-label">Revenue</div>
                                <div class="stat-number">฿<?= number_format($today_sales, 2) ?></div>
                            </div>
                            <div class="stat-icon">💰</div>
                        </div>

                        <div class="stat-card">
                            <div class="stat-info">
                                <div class="stat-label">Products</div>
                                <div class="stat-number"><?= number_format($total_products) ?></div>
                            </div>
                            <div class="stat-icon">🧺</div>
                        </div>
                    </div>

                    <div class="mid-grid">
                        <div class="dash-card">
                            <div class="dash-card-head">
                                <div>
                                    <h3 class="dash-card-title">📌 การจองล่าสุด</h3>
                                    <div class="dash-card-sub">รายการจาก tbl_reservation</div>
                                </div>
                                <div class="tag-soft">ล่าสุด 5 รายการ</div>
                            </div>

                            <div class="dash-card-body">
                                <div class="table-responsive">
                                    <table class="table-modern">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>ลูกค้า</th>
                                                <th>วันรับสินค้า</th>
                                                <th>ยอดเงิน</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (!empty($latestReserve)): ?>
                                                <?php foreach ($latestReserve as $row): ?>
                                                    <tr>
                                                        <td>RSV-<?= htmlspecialchars($row['reserve_id']) ?></td>
                                                        <td><?= htmlspecialchars($row['reserve_name']) ?></td>
                                                        <td>
                                                            <?= htmlspecialchars($row['pickup_date']) ?>
                                                            <?= htmlspecialchars(substr($row['pickup_time'], 0, 5)) ?>
                                                        </td>
                                                        <td class="amount-text"><?= number_format($row['total_amount'], 2) ?> ฿
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <tr>
                                                    <td colspan="4" class="empty-text">ไม่มีข้อมูลการจอง</td>
                                                </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="dash-card">
                            <div class="dash-card-head">
                                <div>
                                    <h3 class="dash-card-title">🧾 การขายล่าสุด</h3>
                                    <div class="dash-card-sub">รายการจาก tbl_payment_slip</div>
                                </div>
                                <div class="tag-soft">ล่าสุด 5 รายการ</div>
                            </div>

                            <div class="dash-card-body">
                                <div class="table-responsive">
                                    <table class="table-modern">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>ลูกค้า</th>
                                                <th>วันเวลาโอน</th>
                                                <th>ยอดเงิน</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (!empty($latestSales)): ?>
                                                <?php foreach ($latestSales as $row): ?>
                                                    <tr>
                                                        <td>SALE-<?= htmlspecialchars($row['slip_id']) ?></td>
                                                        <td><?= htmlspecialchars($row['payer_name']) ?></td>
                                                        <td><?= htmlspecialchars($row['pay_datetime']) ?></td>
                                                        <td class="amount-text"><?= number_format($row['pay_amount'], 2) ?> ฿
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <tr>
                                                    <td colspan="4" class="empty-text">ไม่มีข้อมูลการขาย</td>
                                                </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bottom-grid">
                        <div class="dash-card">
                            <div class="dash-card-head">
                                <div>
                                    <h3 class="dash-card-title">🔥 สินค้าขายดี 3 อันดับแรก</h3>
                                    <div class="dash-card-sub">คำนวณจากยอดรวมที่ขายได้ในเดือนนี้</div>
                                </div>
                            </div>

                            <div class="dash-card-body">
                                <?php if (!empty($bestProducts)): ?>
                                    <div class="product-list">
                                        <?php foreach ($bestProducts as $index => $row): ?>
                                            <?php $percent = ((float)$row['total_sold'] / $maxBestSold) * 100; ?>
                                            <div class="product-row">
                                                <div class="product-rank"><?= $index + 1 ?></div>

                                                <div>
                                                    <div class="product-name"><?= htmlspecialchars($row['p_name']) ?></div>
                                                    <div class="product-meta">สินค้าขายดีประจำเดือนนี้</div>
                                                    <div class="progress-soft">
                                                        <span style="width: <?= $percent ?>%;"></span>
                                                    </div>
                                                </div>

                                                <div class="product-total"><?= number_format($row['total_sold']) ?> ชิ้น</div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php else: ?>
                                    <div class="empty-text">ไม่มีข้อมูลสินค้าขายดี</div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="dash-card">
                            <div class="dash-card-head">
                                <div>
                                    <h3 class="dash-card-title">📉 สินค้าขายไม่ดี</h3>
                                    <div class="dash-card-sub">รายการสินค้าที่มียอดขายต่ำในเดือนนี้</div>
                                </div>
                                <div class="tag-soft">ต่ำสุด 3 รายการ</div>
                            </div>

                            <div class="dash-card-body">
                                <?php if (!empty($lowProducts)): ?>
                                    <div class="low-list">
                                        <?php foreach ($lowProducts as $row): ?>
                                            <?php $lowPercent = $maxLowSold > 0 ? (((float)$row['total_sold'] / $maxLowSold) * 100) : 0; ?>
                                            <div class="low-item">
                                                <div class="low-top">
                                                    <div>
                                                        <div class="low-name"><?= htmlspecialchars($row['p_name']) ?></div>
                                                        <div class="low-sub">
                                                            ขายเดือนนี้ <?= number_format($row['total_sold']) ?> ชิ้น
                                                            · คงเหลือ <?= number_format($row['p_stock']) ?> ชิ้น
                                                        </div>
                                                    </div>
                                                    <div class="low-value"><?= number_format($row['total_sold']) ?> ชิ้น</div>
                                                </div>
                                                <div class="progress-soft">
                                                    <span style="width: <?= $lowPercent ?>%;"></span>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php else: ?>
                                    <div class="empty-text">ไม่มีข้อมูลสินค้าขายไม่ดี</div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                </div>
            </section>
        </div>

        <?php include 'footer.php'; ?>
    </div>

    <?php include 'script.php'; ?>
</body>

</html>