<?php
session_start();
require_once 'condb.php';

$stmt = $conn->prepare("SELECT * FROM tbl_payment_slip ORDER BY slip_id DESC");
$stmt->execute();
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>รายการแจ้งชำระเงิน</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: #f5f6fa;
            font-family: 'Sarabun', sans-serif;
        }

        .table img {
            border-radius: 10px;
            border: 1px solid #ddd;
        }
    </style>
</head>

<body>
    <div class="container py-4">
        <h2 class="mb-4">รายการแจ้งชำระเงิน</h2>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <?= $_SESSION['success'];
                unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger">
                <?= $_SESSION['error'];
                unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <div class="mb-3">
            <a href="payment_form.php" class="btn btn-primary">+ แจ้งชำระเงิน</a>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle bg-white">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>ชื่อผู้โอน</th>
                        <th>เบอร์โทร</th>
                        <th>จำนวนเงิน</th>
                        <th>วันเวลาโอน</th>
                        <th>สลิป</th>
                        <th>หมายเหตุ</th>
                        <th>สถานะ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($rows): ?>
                        <?php foreach ($rows as $row): ?>
                            <tr>
                                <td><?= $row['slip_id']; ?></td>
                                <td><?= htmlspecialchars($row['payer_name']); ?></td>
                                <td><?= htmlspecialchars($row['payer_phone']); ?></td>
                                <td><?= number_format($row['pay_amount'], 2); ?></td>
                                <td><?= htmlspecialchars($row['pay_datetime']); ?></td>
                                <td>
                                    <a href="uploads/slips/<?= htmlspecialchars($row['slip_image']); ?>" target="_blank">
                                        <img src="uploads/slips/<?= htmlspecialchars($row['slip_image']); ?>" width="90">
                                    </a>
                                </td>
                                <td><?= nl2br(htmlspecialchars($row['note'])); ?></td>
                                <td><?= htmlspecialchars($row['status']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center">ยังไม่มีข้อมูลแจ้งชำระเงิน</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>

</html>