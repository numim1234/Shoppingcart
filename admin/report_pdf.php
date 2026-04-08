<?php
session_start();
require_once("../condb.php");

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

$sql = "
    SELECT 
        r.reserve_id,
        r.reserve_name,
        r.reserve_phone,
        r.pickup_date,
        r.pickup_time,
        r.total_amount,
        r.deposit_amount,
        (r.total_amount - r.deposit_amount) AS remain_amount
    FROM tbl_reservation r
    $where
    ORDER BY r.reserve_id ASC
";
$stmt = $conn->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>รายงานการจองสินค้า</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 14px;
        }

        h2 {
            text-align: center;
            margin-bottom: 10px;
        }

        .date-range {
            text-align: center;
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        table,
        th,
        td {
            border: 1px solid #333;
        }

        th,
        td {
            padding: 8px;
            text-align: center;
        }

        th {
            background: #eee;
        }

        .text-right {
            text-align: right;
        }
    </style>
</head>

<body onload="window.print()">

    <h2>รายงานการจองสินค้า</h2>

    <div class="date-range">
        <?php if (!empty($date_start) || !empty($date_end)) { ?>
            ช่วงวันที่:
            <?= !empty($date_start) ? date('d/m/Y', strtotime($date_start)) : '-' ?>
            ถึง
            <?= !empty($date_end) ? date('d/m/Y', strtotime($date_end)) : '-' ?>
        <?php } else { ?>
            ข้อมูลทั้งหมด
        <?php } ?>
    </div>

    <table>
        <thead>
            <tr>
                <th>ลำดับ</th>
                <th>รหัสจอง</th>
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
            <?php if (!empty($rows)) { ?>
                <?php $i = 1; ?>
                <?php foreach ($rows as $row) { ?>
                    <tr>
                        <td><?= $i++ ?></td>
                        <td><?= $row['reserve_id'] ?></td>
                        <td><?= htmlspecialchars($row['reserve_name']) ?></td>
                        <td><?= htmlspecialchars($row['reserve_phone']) ?></td>
                        <td><?= date('d/m/Y', strtotime($row['pickup_date'])) ?></td>
                        <td><?= date('H:i', strtotime($row['pickup_time'])) ?> น.</td>
                        <td class="text-right"><?= number_format($row['total_amount'], 2) ?></td>
                        <td class="text-right"><?= number_format($row['deposit_amount'], 2) ?></td>
                        <td class="text-right"><?= number_format($row['remain_amount'], 2) ?></td>
                    </tr>
                <?php } ?>
            <?php } else { ?>
                <tr>
                    <td colspan="9">ไม่พบข้อมูล</td>
                </tr>
            <?php } ?>
        </tbody>
    </table>

</body>

</html>