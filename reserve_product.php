<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once("condb.php");

$deposit_percent = 50;

$stmt = $conn->prepare("
    SELECT 
        p.p_id,
        p.p_name,
        p.p_price,
        p.sale_type,
        (
            SELECT d.img
            FROM tbl_img_detail d
            WHERE d.p_id = p.p_id
            ORDER BY d.id ASC
            LIMIT 1
        ) AS product_image
    FROM tbl_product p
    ORDER BY p.p_name ASC
");
$stmt->execute();
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>จองขนมและเบเกอรี่</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Noto+Sans+Thai:wght@400;500;600;700;800&display=swap">

    <style>
        :root {
            --bg: #f5f7ff;
            --bg-soft: #eef2ff;
            --card: rgba(255, 255, 255, 0.96);
            --card-strong: #ffffff;
            --line: #dbe4ff;
            --line-strong: #c7d2fe;
            --primary: #6366f1;
            --primary-dark: #4f46e5;
            --secondary: #8b5cf6;
            --accent: #f59e0b;
            --danger: #ef4444;
            --text: #1e293b;
            --text-soft: #64748b;
            --success-soft: #eefcf3;
            --shadow: 0 16px 40px rgba(99, 102, 241, 0.10);
            --shadow-soft: 0 10px 24px rgba(99, 102, 241, 0.08);
            --radius-xl: 28px;
            --radius-lg: 22px;
            --radius-md: 16px;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Noto Sans Thai', sans-serif;
            background:
                radial-gradient(circle at top left, rgba(139, 92, 246, 0.12) 0%, transparent 28%),
                radial-gradient(circle at top right, rgba(99, 102, 241, 0.14) 0%, transparent 30%),
                linear-gradient(135deg, #f8faff 0%, #f3f6ff 100%);
            color: var(--text);
            min-height: 100vh;
        }

        .page-wrap {
            padding: 34px 0 50px;
        }

        .main-card {
            border: 0;
            border-radius: 32px;
            overflow: hidden;
            background: var(--card);
            box-shadow: 0 24px 60px rgba(99, 102, 241, 0.12);
            backdrop-filter: blur(8px);
        }

        .header-box {
            position: relative;
            overflow: hidden;
            background: linear-gradient(135deg, #6366f1 0%, #7c3aed 55%, #a78bfa 100%);
            color: #fff;
            padding: 34px 34px 78px;
        }

        .header-box::before {
            content: "";
            position: absolute;
            right: -40px;
            top: -40px;
            width: 180px;
            height: 180px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.10);
        }

        .header-box::after {
            content: "";
            position: absolute;
            left: -10%;
            bottom: -58px;
            width: 120%;
            height: 120px;
            background: #f8fbff;
            border-radius: 50%;
        }

        .header-content {
            position: relative;
            z-index: 2;
        }

        .brand-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(255, 255, 255, 0.16);
            border: 1px solid rgba(255, 255, 255, 0.24);
            color: #fff;
            border-radius: 999px;
            padding: 8px 14px;
            font-size: .92rem;
            font-weight: 700;
            margin-bottom: 14px;
            backdrop-filter: blur(6px);
        }

        .header-box h2 {
            margin: 0 0 8px;
            font-weight: 800;
            font-size: 2rem;
        }

        .header-box p {
            margin: 0;
            opacity: .96;
            max-width: 700px;
            font-size: 0.98rem;
        }

        .content-box {
            padding: 14px 30px 30px;
            margin-top: -28px;
            position: relative;
            z-index: 3;
        }

        .btn-back-home {
            border-radius: 999px;
            padding: 10px 18px;
            font-weight: 700;
            font-size: 0.95rem;
            color: var(--primary-dark);
            background: #ffffff;
            border: 1px solid var(--line);
            box-shadow: 0 8px 18px rgba(99, 102, 241, 0.08);
            text-decoration: none;
            transition: 0.2s ease;
        }

        .btn-back-home:hover {
            background: #eef2ff;
            color: var(--primary-dark);
            transform: translateY(-1px);
        }

        .section-card {
            background: linear-gradient(180deg, #ffffff 0%, #fbfcff 100%);
            border: 1px solid var(--line);
            border-radius: var(--radius-lg);
            padding: 22px;
            box-shadow: var(--shadow-soft);
            margin-bottom: 22px;
        }

        .section-title {
            font-size: 1.15rem;
            font-weight: 800;
            color: var(--text);
            margin-bottom: 8px;
        }

        .section-subtitle {
            color: var(--text-soft);
            font-size: .95rem;
            margin-bottom: 16px;
        }

        .booking-alert {
            border: 1px solid #fde68a;
            background: #fffbeb;
            color: #92400e;
            border-radius: 16px;
            padding: 14px 16px;
            font-size: .95rem;
            font-weight: 600;
            margin-bottom: 18px;
        }

        .form-label {
            font-weight: 700;
            color: #475569;
            margin-bottom: 8px;
        }

        .form-control {
            border-radius: 14px;
            min-height: 48px;
            border: 1px solid var(--line);
            background: #fff;
            color: var(--text);
            padding: 12px 14px;
            box-shadow: none;
            transition: .2s ease;
        }

        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 .22rem rgba(99, 102, 241, 0.14);
        }

        textarea.form-control {
            min-height: 110px;
        }

        .deposit-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 14px;
            border-radius: 999px;
            background: #f5f3ff;
            color: #6d28d9;
            border: 1px solid #ddd6fe;
            font-size: .9rem;
            font-weight: 800;
        }

        .product-card {
            border: 1px solid var(--line);
            border-radius: 22px;
            background: #fff;
            padding: 16px;
            margin-bottom: 18px;
            transition: .25s ease;
            box-shadow: 0 8px 20px rgba(148, 163, 184, 0.08);
        }

        .product-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 16px 30px rgba(99, 102, 241, 0.10);
            border-color: #c7d2fe;
        }

        .product-card.active {
            border-color: var(--primary);
            background: linear-gradient(180deg, #f8f9ff 0%, #f2f5ff 100%);
            box-shadow: 0 18px 34px rgba(99, 102, 241, 0.14);
        }

        .product-image {
            width: 100%;
            height: 150px;
            object-fit: cover;
            border-radius: 18px;
            border: 1px solid #e5e7eb;
            background: #f8fafc;
        }

        .product-name {
            font-size: 1.08rem;
            font-weight: 800;
            color: var(--text);
            margin-bottom: 6px;
        }

        .product-price {
            font-size: 1.08rem;
            font-weight: 800;
            color: var(--primary-dark);
            margin-bottom: 8px;
        }

        .sale-type {
            display: inline-block;
            background: #eef2ff;
            color: var(--primary-dark);
            border: 1px solid #dbe4ff;
            border-radius: 999px;
            padding: 5px 10px;
            font-size: .82rem;
            font-weight: 700;
        }

        .check-wrap {
            background: #f8faff;
            border: 1px dashed #c7d2fe;
            border-radius: 16px;
            padding: 12px;
            text-align: center;
        }

        .form-check-input {
            cursor: pointer;
        }

        .form-check-input:checked {
            background-color: var(--primary);
            border-color: var(--primary);
        }

        .qty-label {
            font-size: .92rem;
            font-weight: 700;
            color: #475569;
            margin-bottom: 8px;
        }

        .qty-input {
            max-width: 130px;
            text-align: center;
            font-weight: 700;
        }

        .summary-box {
            position: sticky;
            top: 18px;
            border: 1px solid var(--line);
            border-radius: 26px;
            background: linear-gradient(180deg, #ffffff 0%, #f7f9ff 100%);
            padding: 22px;
            box-shadow: 0 18px 36px rgba(99, 102, 241, 0.12);
        }

        .summary-title {
            font-size: 1.18rem;
            font-weight: 800;
            color: var(--text);
            margin-bottom: 14px;
        }

        .customer-preview {
            background: #fff;
            border: 1px solid var(--line);
            border-radius: 18px;
            padding: 14px;
            margin-bottom: 16px;
            font-size: .95rem;
            color: #334155;
        }

        .selected-list-box {
            background: #fff;
            border: 1px dashed #cbd5e1;
            border-radius: 18px;
            padding: 14px;
            min-height: 90px;
        }

        .summary-line {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 12px;
            color: var(--text-soft);
            font-weight: 600;
        }

        .summary-total {
            font-size: 1.08rem;
            font-weight: 800;
            color: var(--text);
        }

        .summary-deposit {
            font-size: 1.18rem;
            font-weight: 800;
            color: var(--primary-dark);
        }

        .btn-submit {
            border: none;
            border-radius: 999px;
            padding: 14px 18px;
            font-weight: 800;
            font-size: 1rem;
            background: linear-gradient(135deg, #6366f1 0%, #7c3aed 100%);
            color: #fff;
            box-shadow: 0 14px 28px rgba(99, 102, 241, 0.24);
            transition: .2s ease;
        }

        .btn-submit:hover {
            color: #fff;
            transform: translateY(-1px);
            box-shadow: 0 16px 32px rgba(99, 102, 241, 0.30);
        }

        .note-hint {
            color: var(--text-soft);
            font-size: .9rem;
            margin-top: 8px;
        }

        hr.soft-line {
            border: 0;
            border-top: 1px dashed #d6def8;
            margin: 18px 0;
        }

        .empty-text {
            color: var(--text-soft);
        }

        @media (max-width: 991px) {
            .summary-box {
                position: static;
                margin-top: 6px;
            }
        }

        @media (max-width: 576px) {
            .page-wrap {
                padding: 18px 0 30px;
            }

            .header-box {
                padding: 26px 20px 64px;
            }

            .content-box {
                padding: 10px 16px 20px;
            }

            .section-card {
                padding: 16px;
                border-radius: 18px;
            }

            .product-image {
                height: 128px;
            }

            .header-box h2 {
                font-size: 1.55rem;
            }

            .header-box p {
                font-size: 0.92rem;
            }

            .product-card {
                padding: 14px;
            }
        }
    </style>
</head>

<body>
    <div class="container page-wrap">
        <div class="main-card">
            <div class="header-box">
                <div class="header-content">
                    <div class="brand-badge">🧁 ระบบจองเบเกอรี่ล่วงหน้า</div>
                    <h2>แบบฟอร์มจองขนมล่วงหน้า</h2>
                    <p>เลือกเมนูที่ต้องการ กรอกข้อมูลสำหรับติดต่อ และตรวจสอบยอดรวมพร้อมยอดมัดจำก่อนยืนยันการจอง</p>
                </div>
            </div>

            <div class="content-box">
                <div class="mb-3">
                    <a href="index.php" class="btn btn-back-home">← กลับหน้าหลัก</a>
                </div>

                <form action="reserve_add.php" method="post" id="reserveForm">
                    <div class="row g-4">
                        <div class="col-lg-8">
                            <div class="section-card">
                                <div class="section-title">ข้อมูลลูกค้า</div>
                                <div class="section-subtitle">กรอกข้อมูลสำหรับติดต่อกลับและนัดรับสินค้า</div>

                                <div class="booking-alert">
                                    ⚠️ กรุณาสั่งจองล่วงหน้าอย่างน้อย <strong>3 วัน</strong> ก่อนวันมารับสินค้า
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">ชื่อผู้จอง</label>
                                        <input type="text" name="reserve_name" id="reserve_name" class="form-control"
                                            placeholder="กรอกชื่อผู้จอง" required>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">เบอร์โทรศัพท์ลูกค้า</label>
                                        <input type="text" name="reserve_phone" id="reserve_phone" class="form-control"
                                            placeholder="กรอกเบอร์โทรศัพท์" required>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">วันที่มารับสินค้า</label>
                                        <input type="date" name="pickup_date" id="pickup_date" class="form-control"
                                            required>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">เวลามารับสินค้า</label>
                                        <input type="time" name="pickup_time" id="pickup_time" class="form-control"
                                            required>
                                    </div>
                                </div>
                            </div>

                            <div class="section-card">
                                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                                    <div>
                                        <div class="section-title mb-1">เลือกเมนูเบเกอรี่</div>
                                        <div class="section-subtitle mb-0">เลือกรายการและจำนวนที่ต้องการจอง</div>
                                    </div>
                                    <span class="deposit-badge">💳 มัดจำ <?= $deposit_percent ?>%</span>
                                </div>

                                <?php foreach ($products as $row): ?>
                                    <?php
                                    $img_path = "";
                                    $default_img = "assets/img/no-image.png";

                                    if (!empty($row['product_image']) && file_exists(__DIR__ . '/admin/p_gallery/' . $row['product_image'])) {
                                        $img_path = "admin/p_gallery/" . $row['product_image'];
                                    } else {
                                        $img_path = $default_img;
                                    }
                                    ?>
                                    <div class="product-card product-item" data-price="<?= (float)$row['p_price'] ?>">
                                        <div class="row align-items-center g-3">
                                            <div class="col-md-3">
                                                <img src="<?= htmlspecialchars($img_path) ?>"
                                                    alt="<?= htmlspecialchars($row['p_name']) ?>" class="product-image"
                                                    onerror="this.src='assets/img/no-image.png'">
                                            </div>

                                            <div class="col-md-4">
                                                <div class="product-name"><?= htmlspecialchars($row['p_name']) ?></div>
                                                <div class="product-price"><?= number_format($row['p_price'], 2) ?> บาท
                                                </div>

                                                <?php if (!empty($row['sale_type'])): ?>
                                                    <div class="sale-type">ประเภทขาย: <?= htmlspecialchars($row['sale_type']) ?>
                                                    </div>
                                                <?php endif; ?>
                                            </div>

                                            <div class="col-md-2">
                                                <div class="check-wrap">
                                                    <div
                                                        class="form-check d-flex justify-content-center align-items-center gap-2 m-0">
                                                        <input class="form-check-input product-check" type="checkbox"
                                                            name="selected_products[]" value="<?= $row['p_id'] ?>"
                                                            id="product_<?= $row['p_id'] ?>">
                                                        <label class="form-check-label fw-bold"
                                                            for="product_<?= $row['p_id'] ?>">
                                                            เลือกสินค้า
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-3">
                                                <label class="qty-label">จำนวน</label>
                                                <input type="number" name="qty[<?= $row['p_id'] ?>]"
                                                    class="form-control qty-input product-qty" min="0" value="0">
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>

                                <div class="mt-4">
                                    <label class="form-label">หมายเหตุเพิ่มเติม</label>
                                    <textarea name="reserve_note" class="form-control"
                                        placeholder="เช่น ขอเขียนหน้าเค้ก, มารับช่วงบ่าย, ขอแพ็กแยกกล่อง หรือรายละเอียดเพิ่มเติมอื่น ๆ"></textarea>
                                    <div class="note-hint">
                                        ระบุรายละเอียดเพิ่มเติมเพื่อให้ร้านเตรียมสินค้าได้ตรงตามต้องการ</div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-4">
                            <div class="summary-box">
                                <div class="summary-title">สรุปรายการจอง</div>

                                <div class="customer-preview">
                                    <div><strong>ชื่อลูกค้า:</strong> <span id="preview_name">-</span></div>
                                    <div><strong>เบอร์โทร:</strong> <span id="preview_phone">-</span></div>
                                    <div><strong>วันที่รับ:</strong> <span id="preview_date">-</span></div>
                                    <div><strong>เวลารับ:</strong> <span id="preview_time">-</span></div>
                                </div>

                                <div class="selected-list-box mb-3">
                                    <div id="selected-list" class="empty-text">ยังไม่ได้เลือกสินค้า</div>
                                </div>

                                <hr class="soft-line">

                                <div class="summary-line">
                                    <span>จำนวนสินค้ารวม</span>
                                    <span id="total_qty">0 ชิ้น</span>
                                </div>

                                <div class="summary-line">
                                    <span>ยอดรวมสินค้า</span>
                                    <span class="summary-total" id="total_price">0.00 บาท</span>
                                </div>

                                <div class="summary-line">
                                    <span>ยอดมัดจำที่ต้องชำระ</span>
                                    <span class="summary-deposit" id="deposit_price">0.00 บาท</span>
                                </div>

                                <input type="hidden" name="total_amount" id="total_amount_input" value="0">
                                <input type="hidden" name="deposit_amount" id="deposit_amount_input" value="0">

                                <button type="submit" class="btn btn-submit w-100 mt-3">
                                    ยืนยันการจองสินค้า
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        const depositPercent = <?= (float)$deposit_percent ?>;
        const productItems = document.querySelectorAll('.product-item');
        const totalQtyEl = document.getElementById('total_qty');
        const totalPriceEl = document.getElementById('total_price');
        const depositPriceEl = document.getElementById('deposit_price');
        const selectedListEl = document.getElementById('selected-list');
        const totalAmountInput = document.getElementById('total_amount_input');
        const depositAmountInput = document.getElementById('deposit_amount_input');

        const reserveName = document.getElementById('reserve_name');
        const reservePhone = document.getElementById('reserve_phone');
        const pickupDate = document.getElementById('pickup_date');
        const pickupTime = document.getElementById('pickup_time');

        const previewName = document.getElementById('preview_name');
        const previewPhone = document.getElementById('preview_phone');
        const previewDate = document.getElementById('preview_date');
        const previewTime = document.getElementById('preview_time');

        function formatMoney(num) {
            return parseFloat(num).toLocaleString('th-TH', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        }

        function updateSummary() {
            let totalQty = 0;
            let totalPrice = 0;
            let html = '';
            let selectedCount = 0;

            productItems.forEach(item => {
                const check = item.querySelector('.product-check');
                const qtyInput = item.querySelector('.product-qty');
                const name = item.querySelector('.product-name').innerText;
                const price = parseFloat(item.dataset.price);
                const qty = parseInt(qtyInput.value) || 0;

                if (check.checked && qty > 0) {
                    const subtotal = price * qty;
                    totalQty += qty;
                    totalPrice += subtotal;
                    selectedCount++;
                    item.classList.add('active');

                    html += `
                    <div class="d-flex justify-content-between mb-2 gap-2">
                        <div>
                            <strong>${name}</strong><br>
                            <small class="text-muted">${qty} x ${formatMoney(price)} บาท</small>
                        </div>
                        <div><strong>${formatMoney(subtotal)}</strong></div>
                    </div>
                `;
                } else {
                    item.classList.remove('active');
                }
            });

            const deposit = (totalPrice * depositPercent) / 100;

            totalQtyEl.innerText = totalQty + ' ชิ้น';
            totalPriceEl.innerText = formatMoney(totalPrice) + ' บาท';
            depositPriceEl.innerText = formatMoney(deposit) + ' บาท';

            totalAmountInput.value = totalPrice.toFixed(2);
            depositAmountInput.value = deposit.toFixed(2);

            selectedListEl.innerHTML = selectedCount > 0 ?
                html :
                '<span class="empty-text">ยังไม่ได้เลือกสินค้า</span>';
        }

        function updateCustomerPreview() {
            previewName.innerText = reserveName.value || '-';
            previewPhone.innerText = reservePhone.value || '-';
            previewDate.innerText = pickupDate.value || '-';
            previewTime.innerText = pickupTime.value || '-';
        }

        function getMinPickupDate() {
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            today.setDate(today.getDate() + 3);
            return today;
        }

        function formatDateForInput(dateObj) {
            const year = dateObj.getFullYear();
            const month = String(dateObj.getMonth() + 1).padStart(2, '0');
            const day = String(dateObj.getDate()).padStart(2, '0');
            return `${year}-${month}-${day}`;
        }

        // ตั้งค่าวันรับขั้นต่ำ = วันนี้ + 3 วัน
        const minPickupDate = getMinPickupDate();
        pickupDate.min = formatDateForInput(minPickupDate);

        productItems.forEach(item => {
            const check = item.querySelector('.product-check');
            const qtyInput = item.querySelector('.product-qty');

            check.addEventListener('change', function() {
                if (this.checked && (parseInt(qtyInput.value) || 0) === 0) {
                    qtyInput.value = 1;
                }
                if (!this.checked) {
                    qtyInput.value = 0;
                }
                updateSummary();
            });

            qtyInput.addEventListener('input', function() {
                const qty = parseInt(this.value) || 0;
                check.checked = qty > 0;
                updateSummary();
            });
        });

        reserveName.addEventListener('input', updateCustomerPreview);
        reservePhone.addEventListener('input', updateCustomerPreview);
        pickupDate.addEventListener('input', updateCustomerPreview);
        pickupTime.addEventListener('input', updateCustomerPreview);

        // กันเลือกวันที่น้อยกว่า 3 วัน
        pickupDate.addEventListener('change', function() {
            if (!this.value) return;

            const selectedDate = new Date(this.value);
            const minDate = getMinPickupDate();

            selectedDate.setHours(0, 0, 0, 0);
            minDate.setHours(0, 0, 0, 0);

            if (selectedDate < minDate) {
                alert('กรุณาเลือกวันที่รับสินค้าล่วงหน้าอย่างน้อย 3 วัน');
                this.value = '';
                previewDate.innerText = '-';
            }
        });

        // กัน submit ซ้ำอีกชั้น
        document.getElementById('reserveForm').addEventListener('submit', function(e) {
            const selectedProducts = document.querySelectorAll('.product-check:checked');
            const selectedDateValue = pickupDate.value;

            if (selectedProducts.length === 0) {
                e.preventDefault();
                alert('กรุณาเลือกสินค้าอย่างน้อย 1 รายการ');
                return;
            }

            if (!selectedDateValue) {
                e.preventDefault();
                alert('กรุณาเลือกวันที่มารับสินค้า');
                return;
            }

            const selectedDate = new Date(selectedDateValue);
            const minDate = getMinPickupDate();

            selectedDate.setHours(0, 0, 0, 0);
            minDate.setHours(0, 0, 0, 0);

            if (selectedDate < minDate) {
                e.preventDefault();
                alert('ต้องสั่งจองล่วงหน้าอย่างน้อย 3 วัน');
                return;
            }
        });

        updateSummary();
        updateCustomerPreview();
    </script>
</body>

</html>