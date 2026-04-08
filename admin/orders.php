<?php
session_start();
include '../condb.php';
$menu = "orders";

$tab = isset($_GET['tab']) ? $_GET['tab'] : 'all';

/* =========================
   ดึงข้อมูลการจอง
========================= */
$stmtReserve = $conn->prepare("
    SELECT 
        reserve_id,
        reserve_name,
        reserve_phone,
        total_amount,
        payment_status,
        reserve_status,
        created_at
    FROM tbl_reservation
    ORDER BY reserve_id DESC
");
$stmtReserve->execute();
$reservations = $stmtReserve->fetchAll(PDO::FETCH_ASSOC);

/* =========================
   ดึงข้อมูลการขาย
========================= */
$sales = [];
try {
    $stmtSale = $conn->prepare("
        SELECT 
            slip_id,
            payer_name,
            payer_phone,
            pay_amount,
            pay_datetime,
            slip_image,
            status,
            created_at
        FROM tbl_payment_slip
        ORDER BY slip_id DESC
    ");
    $stmtSale->execute();
    $sales = $stmtSale->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $sales = [];
}

/* =========================
   รวมข้อมูลทั้งหมด
========================= */
$allOrders = [];

foreach ($reservations as $row) {
    $allOrders[] = [
        'type' => 'reservation',
        'reserve_id' => $row['reserve_id'],
        'order_no' => 'RSV-' . $row['reserve_id'],
        'customer_name' => $row['reserve_name'],
        'customer_phone' => $row['reserve_phone'],
        'amount' => $row['total_amount'],
        'created_at' => $row['created_at'],
        'pay_datetime' => '',
        'slip_image' => '',
        'slip_id' => 0
    ];
}

foreach ($sales as $row) {
    $allOrders[] = [
        'type' => 'sale',
        'reserve_id' => 0,
        'slip_id' => $row['slip_id'],
        'order_no' => 'SALE-' . $row['slip_id'],
        'customer_name' => $row['payer_name'],
        'customer_phone' => $row['payer_phone'],
        'amount' => $row['pay_amount'],
        'created_at' => $row['created_at'],
        'pay_datetime' => $row['pay_datetime'],
        'slip_image' => $row['slip_image']
    ];
}

usort($allOrders, function ($a, $b) {
    return strtotime($b['created_at']) - strtotime($a['created_at']);
});

/* =========================
   badge ประเภท
========================= */
function badgeType($type)
{
    if ($type == 'reservation') {
        return '<span class="order-pill reserve"><i class="fas fa-calendar-check"></i> การจอง</span>';
    }

    if ($type == 'sale') {
        return '<span class="order-pill sale"><i class="fas fa-shopping-basket"></i> การขาย</span>';
    }

    return '<span class="order-pill other"><i class="fas fa-circle"></i> ไม่ระบุ</span>';
}
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>ออเดอร์</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>

    <style>
        body {
            margin: 0;
            font-family: 'Prompt', sans-serif;
            background: #f3f5fb;
            color: #2f3542;
        }

        .content-wrapper {
            padding: 24px;
            min-height: 100vh;
            background: #f3f5fb;
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
            padding: 18px 20px;
            box-shadow: 0 10px 24px rgba(15, 23, 42, 0.04);
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
        .table-card {
            background: #fff;
            border: 1px solid #e9edf5;
            border-radius: 20px;
            box-shadow: 0 10px 24px rgba(15, 23, 42, 0.04);
            overflow: hidden;
        }

        .filter-card {
            margin-bottom: 18px;
        }

        .filter-card .card-body,
        .table-card .card-body {
            padding: 20px 22px;
        }

        .filter-btns {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
        }

        .filter-btns .btn {
            border-radius: 999px;
            padding: 10px 18px;
            font-weight: 700;
            transition: all .2s ease;
        }

        .filter-btns .btn:hover {
            transform: translateY(-1px);
        }

        .btn-filter-all {
            background: linear-gradient(135deg, #6478ff, #5865f2);
            color: #fff;
            border: 1px solid #5865f2;
        }

        .btn-filter-all:hover {
            color: #fff;
            opacity: .95;
        }

        .btn-filter-outline {
            background: #fff;
            color: #5865f2;
            border: 1px solid #dbe2ff;
        }

        .btn-filter-outline:hover {
            background: #eef2ff;
            color: #4654cf;
        }

        .btn-filter-reserve {
            background: linear-gradient(135deg, #f4b267, #e59a44);
            color: #fff;
            border: 1px solid #e59a44;
        }

        .btn-filter-reserve:hover {
            color: #fff;
            opacity: .95;
        }

        .btn-filter-sale {
            background: linear-gradient(135deg, #35c98f, #20b97a);
            color: #fff;
            border: 1px solid #20b97a;
        }

        .btn-filter-sale:hover {
            color: #fff;
            opacity: .95;
        }

        .table {
            margin-bottom: 0;
        }

        .table thead th {
            background: #f8faff;
            color: #8d96a8;
            font-weight: 700;
            border-bottom: 1px solid #edf1f7;
            padding: 14px 12px;
            vertical-align: middle;
            white-space: nowrap;
        }

        .table tbody td {
            padding: 14px 12px;
            vertical-align: middle;
            border-color: #f0f3f8;
        }

        .table tbody tr:hover {
            background: #fafcff;
        }

        .order-no {
            font-weight: 800;
            color: #253045;
        }

        .customer-name {
            font-weight: 700;
            color: #253045;
        }

        .customer-phone {
            color: #8a94a6;
        }

        .amount-text {
            font-weight: 800;
            color: #253045;
            white-space: nowrap;
        }

        .date-text {
            color: #8d96a8;
            font-size: 14px;
            white-space: nowrap;
        }

        .order-pill {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 8px 14px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 700;
            line-height: 1;
            white-space: nowrap;
            border: 1px solid transparent;
        }

        .order-pill.reserve {
            background: #fff3e6;
            color: #c17632;
            border-color: #efd0af;
        }

        .order-pill.sale {
            background: #e8f8ee;
            color: #1f8b4d;
            border-color: #cdeedb;
        }

        .order-pill.other {
            background: #f3f4f6;
            color: #6b7280;
            border-color: #d8dde3;
        }

        .btn-detail {
            border: none;
            border-radius: 12px;
            background: linear-gradient(135deg, #6478ff, #5865f2);
            color: #fff;
            font-weight: 700;
            padding: 8px 14px;
            font-size: 13px;
            box-shadow: 0 10px 18px rgba(88, 101, 242, 0.18);
        }

        .btn-detail:hover {
            background: linear-gradient(135deg, #5865f2, #4d59dc);
            color: #fff;
            transform: translateY(-1px);
        }

        div.dataTables_wrapper div.dataTables_filter input,
        div.dataTables_wrapper div.dataTables_length select {
            border-radius: 12px;
            border: 1px solid #dbe3ef;
            background: #fff;
            padding: 6px 10px;
            color: #5f4431;
        }

        div.dataTables_wrapper div.dataTables_info,
        div.dataTables_wrapper div.dataTables_length,
        div.dataTables_wrapper div.dataTables_filter {
            color: #7f6857;
        }

        .page-item.active .page-link {
            background: #5865f2;
            border-color: #5865f2;
        }

        .page-link {
            color: #5865f2;
        }

        .reserve-items-title {
            font-size: 15px;
            font-weight: 800;
            color: #5865f2;
            margin: 16px 0 10px;
        }

        .reserve-items-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
            overflow: hidden;
            border-radius: 12px;
        }

        .reserve-items-table th {
            background: #f8faff;
            color: #58677d;
            padding: 10px 8px;
            border: 1px solid #e7ecf4;
            text-align: left;
        }

        .reserve-items-table td {
            padding: 10px 8px;
            border: 1px solid #e7ecf4;
            color: #364152;
        }

        .reserve-items-table td.text-center,
        .reserve-items-table th.text-center {
            text-align: center;
        }

        .reserve-items-table td.text-end,
        .reserve-items-table th.text-end {
            text-align: right;
        }

        .order-detail-popup {
            text-align: left;
            color: #364152;
        }

        .order-detail-info {
            line-height: 2;
            font-size: 15px;
        }

        .slip-preview-card {
            margin-top: 18px;
            padding: 14px;
            background: #f8faff;
            border: 1px solid #e7ecf4;
            border-radius: 16px;
        }

        .slip-preview-title {
            font-size: 14px;
            font-weight: 800;
            color: #5865f2;
            margin-bottom: 10px;
        }

        .slip-preview-img {
            width: 100%;
            max-height: 420px;
            object-fit: contain;
            border-radius: 14px;
            border: 1px solid #dfe6f1;
            background: #fff;
            box-shadow: 0 8px 20px rgba(15, 23, 42, 0.06);
        }

        .slip-preview-fallback {
            padding: 30px;
            text-align: center;
            color: #8a94a6;
            background: #fff;
            border: 1px dashed #d6deea;
            border-radius: 14px;
        }

        .swal2-popup .swal2-html-container {
            overflow: visible !important;
        }

        @media (max-width: 991px) {
            .content-wrapper {
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

    <?php include 'menu.php'; ?>
    <?php include 'nav.php'; ?>

    <div class="content-wrapper">
        <div class="container-fluid">

            <div class="hero-card">
                <div>
                    <h1 class="hero-title">
                        <i class="fas fa-box"></i>
                        จัดการออเดอร์
                    </h1>
                    <p class="hero-subtitle">ตรวจสอบรายการจองและรายการขายทั้งหมดของร้านในหน้าเดียว</p>
                </div>
                <div class="hero-icon">
                    <i class="fas fa-receipt"></i>
                </div>
            </div>

            <div class="summary-grid">
                <div class="summary-card">
                    <div class="summary-label">ออเดอร์ทั้งหมด</div>
                    <div class="summary-value"><?php echo count($allOrders); ?></div>
                </div>
                <div class="summary-card">
                    <div class="summary-label">รายการจอง</div>
                    <div class="summary-value"><?php echo count($reservations); ?></div>
                </div>
                <div class="summary-card">
                    <div class="summary-label">รายการขาย</div>
                    <div class="summary-value"><?php echo count($sales); ?></div>
                </div>
            </div>

            <div class="card filter-card">
                <div class="card-body">
                    <div class="filter-btns">
                        <a href="orders.php?tab=all"
                            class="btn <?php echo ($tab == 'all') ? 'btn-filter-all' : 'btn-filter-outline'; ?>">
                            <i class="fas fa-layer-group me-1"></i> ทั้งหมด
                        </a>

                        <a href="orders.php?tab=reservation"
                            class="btn <?php echo ($tab == 'reservation') ? 'btn-filter-reserve' : 'btn-filter-outline'; ?>">
                            <i class="fas fa-calendar-check me-1"></i> การจอง
                        </a>

                        <a href="orders.php?tab=sale"
                            class="btn <?php echo ($tab == 'sale') ? 'btn-filter-sale' : 'btn-filter-outline'; ?>">
                            <i class="fas fa-shopping-basket me-1"></i> การขาย
                        </a>
                    </div>
                </div>
            </div>

            <div class="card table-card">
                <div class="card-body">
                    <table id="orderTable" class="table align-middle">
                        <thead>
                            <tr>
                                <th>เลขที่</th>
                                <th>ประเภท</th>
                                <th>ลูกค้า</th>
                                <th>เบอร์โทร</th>
                                <th>ยอดรวม</th>
                                <th>วันที่ทำรายการ</th>
                                <th>รายละเอียด</th>
                            </tr>
                        </thead>
                        <tbody>

                            <?php if ($tab == 'all'): ?>
                                <?php foreach ($allOrders as $row): ?>
                                    <tr>
                                        <td class="order-no"><?php echo htmlspecialchars($row['order_no']); ?></td>
                                        <td><?php echo badgeType($row['type']); ?></td>
                                        <td class="customer-name"><?php echo htmlspecialchars($row['customer_name']); ?></td>
                                        <td class="customer-phone"><?php echo htmlspecialchars($row['customer_phone']); ?></td>
                                        <td class="amount-text"><?php echo number_format($row['amount'], 2); ?> บาท</td>
                                        <td class="date-text"><?php echo htmlspecialchars($row['created_at']); ?></td>
                                        <td>
                                            <button type="button" class="btn btn-detail" onclick="viewOrderDetail(
                                                '<?php echo htmlspecialchars($row['order_no'], ENT_QUOTES); ?>',
                                                '<?php echo htmlspecialchars($row['type'], ENT_QUOTES); ?>',
                                                '<?php echo htmlspecialchars($row['customer_name'], ENT_QUOTES); ?>',
                                                '<?php echo htmlspecialchars($row['customer_phone'], ENT_QUOTES); ?>',
                                                '<?php echo htmlspecialchars(number_format($row['amount'], 2), ENT_QUOTES); ?>',
                                                '<?php echo htmlspecialchars($row['created_at'], ENT_QUOTES); ?>',
                                                '<?php echo ($row['type'] === 'reservation') ? (int)$row['reserve_id'] : 0; ?>',
                                                '<?php echo htmlspecialchars($row['slip_image'] ?? '', ENT_QUOTES); ?>',
                                                '<?php echo htmlspecialchars($row['pay_datetime'] ?? '', ENT_QUOTES); ?>',
                                                '<?php echo ($row['type'] === 'sale') ? (int)$row['slip_id'] : 0; ?>'
                                            )">
                                                <i class="fas fa-eye me-1"></i> ดูรายละเอียด
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>

                            <?php if ($tab == 'reservation'): ?>
                                <?php foreach ($reservations as $row): ?>
                                    <tr>
                                        <td class="order-no">RSV-<?php echo $row['reserve_id']; ?></td>
                                        <td><?php echo badgeType('reservation'); ?></td>
                                        <td class="customer-name"><?php echo htmlspecialchars($row['reserve_name']); ?></td>
                                        <td class="customer-phone"><?php echo htmlspecialchars($row['reserve_phone']); ?></td>
                                        <td class="amount-text"><?php echo number_format($row['total_amount'], 2); ?> บาท</td>
                                        <td class="date-text"><?php echo htmlspecialchars($row['created_at']); ?></td>
                                        <td>
                                            <button type="button" class="btn btn-detail" onclick="viewOrderDetail(
                                                'RSV-<?php echo htmlspecialchars($row['reserve_id'], ENT_QUOTES); ?>',
                                                'reservation',
                                                '<?php echo htmlspecialchars($row['reserve_name'], ENT_QUOTES); ?>',
                                                '<?php echo htmlspecialchars($row['reserve_phone'], ENT_QUOTES); ?>',
                                                '<?php echo htmlspecialchars(number_format($row['total_amount'], 2), ENT_QUOTES); ?>',
                                                '<?php echo htmlspecialchars($row['created_at'], ENT_QUOTES); ?>',
                                                '<?php echo (int)$row['reserve_id']; ?>',
                                                '',
                                                '',
                                                0
                                            )">
                                                <i class="fas fa-eye me-1"></i> ดูรายละเอียด
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>

                            <?php if ($tab == 'sale'): ?>
                                <?php foreach ($sales as $row): ?>
                                    <tr>
                                        <td class="order-no">SALE-<?php echo $row['slip_id']; ?></td>
                                        <td><?php echo badgeType('sale'); ?></td>
                                        <td class="customer-name"><?php echo htmlspecialchars($row['payer_name']); ?></td>
                                        <td class="customer-phone"><?php echo htmlspecialchars($row['payer_phone']); ?></td>
                                        <td class="amount-text"><?php echo number_format($row['pay_amount'], 2); ?> บาท</td>
                                        <td class="date-text"><?php echo htmlspecialchars($row['created_at']); ?></td>
                                        <td>
                                            <button type="button" class="btn btn-detail" onclick="viewOrderDetail(
                                                'SALE-<?php echo htmlspecialchars($row['slip_id'], ENT_QUOTES); ?>',
                                                'sale',
                                                '<?php echo htmlspecialchars($row['payer_name'], ENT_QUOTES); ?>',
                                                '<?php echo htmlspecialchars($row['payer_phone'], ENT_QUOTES); ?>',
                                                '<?php echo htmlspecialchars(number_format($row['pay_amount'], 2), ENT_QUOTES); ?>',
                                                '<?php echo htmlspecialchars($row['created_at'], ENT_QUOTES); ?>',
                                                0,
                                                '<?php echo htmlspecialchars($row['slip_image'] ?? '', ENT_QUOTES); ?>',
                                                '<?php echo htmlspecialchars($row['pay_datetime'] ?? '', ENT_QUOTES); ?>',
                                                '<?php echo (int)$row['slip_id']; ?>'
                                            )">
                                                <i class="fas fa-eye me-1"></i> ดูรายละเอียด
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>

                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>

    <script>
        function viewOrderDetail(orderNo, type, customerName, customerPhone, amount, createdAt, reserveId = 0, slipImage =
            '', payDatetime = '', slipId = 0) {
            let typeText = '-';

            if (type === 'reservation') {
                typeText = 'การจอง';
            } else if (type === 'sale') {
                typeText = 'การขาย';
            }

            if (type === 'reservation' && reserveId > 0) {
                $.ajax({
                    url: 'get_reservation_detail.php',
                    type: 'GET',
                    data: {
                        reserve_id: reserveId
                    },
                    dataType: 'json',
                    success: function(response) {
                        let itemsHtml = '<div class="text-muted mt-3">ไม่มีรายการสินค้า</div>';
                        let reserveInfoHtml = `
                            <div><strong>ยอดรวม:</strong> ${amount} บาท</div>
                            <div><strong>วันที่ทำรายการ:</strong> ${createdAt}</div>
                        `;
                        let slipHtml = '';

                        if (response.status && response.reserve) {
                            reserveInfoHtml = `
                                <div><strong>ยอดรวม:</strong> ${Number(response.reserve.total_amount).toLocaleString(undefined, {minimumFractionDigits: 2})} บาท</div>
                                <div><strong>มัดจำที่จ่ายแล้ว:</strong> ${Number(response.reserve.deposit_amount).toLocaleString(undefined, {minimumFractionDigits: 2})} บาท</div>
                                <div><strong>ยอดคงเหลือชำระที่ร้าน:</strong> ${Number(response.reserve.remaining_amount).toLocaleString(undefined, {minimumFractionDigits: 2})} บาท</div>
                                <div><strong>สถานะการชำระ:</strong> ${response.reserve.payment_status ?? '-'}</div>
                                <div><strong>สถานะการจอง:</strong> ${response.reserve.reserve_status ?? '-'}</div>
                                <div><strong>วันที่มารับ:</strong> ${response.reserve.pickup_date ?? '-'}</div>
                                <div><strong>เวลามารับ:</strong> ${response.reserve.pickup_time ?? '-'}</div>
                                <div><strong>วันที่ทำรายการ:</strong> ${response.reserve.created_at ?? createdAt}</div>
                            `;
                        }

                        if (response.status && response.items.length > 0) {
                            itemsHtml = `
                                <div class="reserve-items-title">📦 สินค้าที่สั่งจอง</div>
                                <table class="reserve-items-table">
                                    <thead>
                                        <tr>
                                            <th>สินค้า</th>
                                            <th class="text-center">จำนวน</th>
                                            <th class="text-end">ราคา</th>
                                            <th class="text-end">รวม</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                            `;

                            response.items.forEach(function(item) {
                                itemsHtml += `
                                    <tr>
                                        <td>${item.p_name ?? '-'}</td>
                                        <td class="text-center">${item.qty}</td>
                                        <td class="text-end">${Number(item.price).toLocaleString(undefined, {minimumFractionDigits: 2})}</td>
                                        <td class="text-end">${Number(item.subtotal).toLocaleString(undefined, {minimumFractionDigits: 2})}</td>
                                    </tr>
                                `;
                            });

                            itemsHtml += `
                                    </tbody>
                                </table>
                            `;
                        }

                        if (response.status && response.slip && response.slip.slip_image) {
                            const slipPath = '../uploads/slips/' + response.slip.slip_image;

                            slipHtml = `
                                <div class="slip-preview-card mt-3">
                                    <div class="slip-preview-title">สลิปมัดจำ / สลิปการจอง</div>
                                    <div class="order-detail-info mb-2">
                                        <div><strong>ชื่อผู้โอน:</strong> ${response.slip.payer_name ?? '-'}</div>
                                        <div><strong>เบอร์โทร:</strong> ${response.slip.payer_phone ?? '-'}</div>
                                        <div><strong>ยอดโอน:</strong> ${Number(response.slip.pay_amount || 0).toLocaleString(undefined, {minimumFractionDigits: 2})} บาท</div>
                                        <div><strong>วันเวลาที่โอน:</strong> ${response.slip.pay_datetime ?? '-'}</div>
                                    </div>
                                    <a href="${slipPath}" target="_blank" style="text-decoration:none;">
                                        <img src="${slipPath}" alt="slip" class="slip-preview-img"
                                             onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                                        <div class="slip-preview-fallback" style="display:none;">
                                            ไม่พบรูปสลิป
                                        </div>
                                    </a>
                                </div>
                            `;
                        }

                        Swal.fire({
                            title: 'รายละเอียดออเดอร์',
                            width: 900,
                            confirmButtonText: 'ปิด',
                            confirmButtonColor: '#5865f2',
                            background: '#ffffff',
                            html: `
                                <div class="order-detail-popup">
                                    <div class="order-detail-info">
                                        <div><strong>เลขที่รายการ:</strong> ${orderNo}</div>
                                        <div><strong>ประเภท:</strong> ${typeText}</div>
                                        <div><strong>ชื่อลูกค้า:</strong> ${customerName}</div>
                                        <div><strong>เบอร์โทร:</strong> ${customerPhone}</div>
                                        ${reserveInfoHtml}
                                    </div>
                                    ${itemsHtml}
                                    ${slipHtml}
                                </div>
                            `
                        });
                    },
                    error: function() {
                        Swal.fire({
                            icon: 'error',
                            title: 'เกิดข้อผิดพลาด',
                            text: 'ไม่สามารถดึงรายละเอียดการจองได้',
                            confirmButtonColor: '#5865f2',
                            background: '#ffffff'
                        });
                    }
                });
            } else if (type === 'sale' && slipId > 0) {
                $.ajax({
                    url: 'get_sale_detail.php',
                    type: 'GET',
                    data: {
                        slip_id: slipId
                    },
                    dataType: 'json',
                    success: function(response) {
                        let itemsHtml = '<div class="text-muted mt-3">ไม่มีรายการสินค้า</div>';
                        let slipHtml = '';

                        if (response.status && response.items.length > 0) {
                            itemsHtml = `
                                <div class="reserve-items-title">🛒 สินค้าที่ลูกค้าสั่งซื้อ</div>
                                <table class="reserve-items-table">
                                    <thead>
                                        <tr>
                                            <th>สินค้า</th>
                                            <th class="text-center">จำนวน</th>
                                            <th class="text-end">ราคา</th>
                                            <th class="text-end">รวม</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                            `;

                            response.items.forEach(function(item) {
                                itemsHtml += `
                                    <tr>
                                        <td>${item.p_name ?? '-'}</td>
                                        <td class="text-center">${item.qty}</td>
                                        <td class="text-end">${Number(item.price).toLocaleString(undefined, {minimumFractionDigits: 2})}</td>
                                        <td class="text-end">${Number(item.subtotal).toLocaleString(undefined, {minimumFractionDigits: 2})}</td>
                                    </tr>
                                `;
                            });

                            itemsHtml += `
                                    </tbody>
                                </table>
                            `;
                        }

                        if (response.status && response.sale && response.sale.slip_image) {
                            const slipPath = '../uploads/slips/' + response.sale.slip_image;

                            slipHtml = `
                                <div class="slip-preview-card mt-3">
                                    <div class="slip-preview-title">สลิปการโอนเงิน</div>
                                    <a href="${slipPath}" target="_blank" style="text-decoration:none;">
                                        <img src="${slipPath}" alt="slip" class="slip-preview-img"
                                             onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                                        <div class="slip-preview-fallback" style="display:none;">
                                            ไม่พบรูปสลิป
                                        </div>
                                    </a>
                                </div>
                            `;
                        }

                        Swal.fire({
                            title: 'รายละเอียดออเดอร์',
                            width: 900,
                            confirmButtonText: 'ปิด',
                            confirmButtonColor: '#5865f2',
                            background: '#ffffff',
                            html: `
                                <div class="order-detail-popup">
                                    <div class="order-detail-info">
                                        <div><strong>เลขที่รายการ:</strong> ${orderNo}</div>
                                        <div><strong>ประเภท:</strong> การขาย</div>
                                        <div><strong>ชื่อลูกค้า:</strong> ${customerName}</div>
                                        <div><strong>เบอร์โทร:</strong> ${customerPhone}</div>
                                        <div><strong>ยอดรวม:</strong> ${amount} บาท</div>
                                        <div><strong>วันเวลาที่โอน:</strong> ${payDatetime || '-'}</div>
                                        <div><strong>วันที่ทำรายการ:</strong> ${createdAt}</div>
                                    </div>
                                    ${itemsHtml}
                                    ${slipHtml}
                                </div>
                            `
                        });
                    },
                    error: function() {
                        Swal.fire({
                            icon: 'error',
                            title: 'เกิดข้อผิดพลาด',
                            text: 'ไม่สามารถดึงรายละเอียดการขายได้',
                            confirmButtonColor: '#5865f2',
                            background: '#ffffff'
                        });
                    }
                });
            } else {
                Swal.fire({
                    icon: 'warning',
                    title: 'ไม่พบข้อมูล',
                    text: 'ไม่พบรายละเอียดของรายการนี้',
                    confirmButtonColor: '#5865f2',
                    background: '#ffffff'
                });
            }
        }

        $(document).ready(function() {
            $('#orderTable').DataTable({
                pageLength: 10,
                order: [
                    [5, 'desc']
                ],
                language: {
                    search: "ค้นหา:",
                    lengthMenu: "แสดง _MENU_ รายการ",
                    info: "แสดง _START_ ถึง _END_ จาก _TOTAL_ รายการ",
                    infoEmpty: "ไม่มีข้อมูล",
                    zeroRecords: "ไม่พบข้อมูลที่ค้นหา",
                    paginate: {
                        previous: "ก่อนหน้า",
                        next: "ถัดไป"
                    }
                }
            });
        });
    </script>

</body>

</html>

<?php $conn = null; ?>