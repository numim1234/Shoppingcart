<?php
session_start();
require_once("condb.php");

error_reporting(E_ALL);
ini_set('display_errors', 1);

$reserve_id = isset($_GET['reserve_id']) ? (int)$_GET['reserve_id'] : 0;
$get_pay_amount = isset($_GET['pay_amount']) ? (float)$_GET['pay_amount'] : 0;
$show_qr = isset($_GET['show_qr']) ? (int)$_GET['show_qr'] : 0;

$pickup_date = trim($_GET['pickup_date'] ?? '');
$pickup_time = trim($_GET['pickup_time'] ?? '');

$is_reservation = false;
$reservation = null;

$pay_amount = 0;
$total_amount = 0;
$remaining_amount = 0;
$payer_name = '';
$payer_phone = '';

if ($reserve_id > 0) {
    $stmt = $conn->prepare("
        SELECT reserve_id, reserve_name, reserve_phone, total_amount, deposit_amount
        FROM tbl_reservation
        WHERE reserve_id = ?
        LIMIT 1
    ");
    $stmt->execute([$reserve_id]);
    $reservation = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$reservation) {
        die("ไม่พบข้อมูลการจอง");
    }

    $is_reservation = true;
    $pay_amount = (float)$reservation['deposit_amount'];
    $total_amount = (float)$reservation['total_amount'];
    $remaining_amount = $total_amount - $pay_amount;
    if ($remaining_amount < 0) {
        $remaining_amount = 0;
    }

    $payer_name = $reservation['reserve_name'];
    $payer_phone = $reservation['reserve_phone'];
} elseif ($get_pay_amount > 0) {
    $pay_amount = $get_pay_amount;
    $total_amount = $get_pay_amount;
    $remaining_amount = 0;

    if (isset($_SESSION['customer_name'])) {
        $payer_name = $_SESSION['customer_name'];
    }
    if (isset($_SESSION['customer_phone'])) {
        $payer_phone = $_SESSION['customer_phone'];
    }
} else {
    die("ไม่พบข้อมูลการชำระเงิน");
}
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>แจ้งชำระเงิน</title>
    <link rel="stylesheet" href="/assets/css/bootstrap.min.css">
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Noto+Sans+Thai:wght@400;500;600;700&display=swap">

    <style>
        body {
            font-family: 'Noto Sans Thai', sans-serif;
            background:
                radial-gradient(circle at top left, #ffe7d6 0%, transparent 30%),
                radial-gradient(circle at top right, #dbeafe 0%, transparent 28%),
                linear-gradient(135deg, #f8fafc 0%, #eef2ff 100%);
            min-height: 100vh;
        }

        .payment-wrapper {
            min-height: 100vh;
            display: flex;
            align-items: center;
            padding: 40px 15px;
        }

        .payment-card {
            max-width: 720px;
            width: 100%;
            margin: 0 auto;
            border: 0;
            border-radius: 28px;
            overflow: hidden;
            background: rgba(255, 255, 255, 0.96);
            box-shadow: 0 20px 60px rgba(15, 23, 42, 0.12);
            backdrop-filter: blur(6px);
        }

        .payment-header {
            background: linear-gradient(135deg, #f59e0b 0%, #f97316 100%);
            color: #fff;
            padding: 34px 30px 28px;
            text-align: center;
            position: relative;
        }

        .payment-header::after {
            content: "";
            position: absolute;
            left: -20%;
            bottom: -55px;
            width: 140%;
            height: 90px;
            background: #fff;
            border-radius: 50%;
        }

        .payment-badge {
            width: 82px;
            height: 82px;
            border-radius: 50%;
            margin: 0 auto 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(255, 255, 255, 0.20);
            border: 2px solid rgba(255, 255, 255, 0.32);
            font-size: 34px;
            font-weight: 700;
            position: relative;
            z-index: 2;
        }

        .payment-header h2,
        .payment-header p {
            position: relative;
            z-index: 2;
        }

        .payment-body {
            position: relative;
            z-index: 3;
            padding: 36px 30px 30px;
        }

        .amount-box {
            background: linear-gradient(135deg, #fff7ed 0%, #fef2f2 100%);
            border: 1px solid #fed7aa;
            border-radius: 24px;
            padding: 18px 20px;
            text-align: center;
            margin-bottom: 24px;
        }

        .amount-box small {
            display: block;
            color: #64748b;
            margin-bottom: 6px;
        }

        .amount-box .amount {
            font-size: 32px;
            font-weight: 700;
            color: #dc2626;
            line-height: 1.1;
        }

        .info-box {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 20px;
            padding: 18px;
            margin-bottom: 24px;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            gap: 15px;
            padding: 8px 0;
            border-bottom: 1px dashed #dbe2ea;
        }

        .info-row:last-child {
            border-bottom: none;
        }

        .info-label {
            color: #64748b;
            font-weight: 500;
        }

        .info-value {
            color: #0f172a;
            font-weight: 600;
            text-align: right;
        }

        .form-label {
            font-weight: 600;
            color: #334155;
            margin-bottom: 8px;
        }

        .form-control {
            border-radius: 16px;
            border: 1px solid #dbe2ea;
            padding: 13px 15px;
            box-shadow: none;
        }

        .form-control:focus {
            border-color: #fb923c;
            box-shadow: 0 0 0 0.22rem rgba(249, 115, 22, 0.13);
        }

        .upload-box {
            border: 2px dashed #cbd5e1;
            border-radius: 18px;
            padding: 18px;
            background: #f8fafc;
        }

        .preview-box {
            display: none;
            margin-top: 14px;
            text-align: center;
        }

        .preview-box img {
            max-width: 100%;
            max-height: 320px;
            object-fit: contain;
            border-radius: 18px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 8px 24px rgba(15, 23, 42, 0.08);
            background: #fff;
            padding: 8px;
        }

        .helper-box {
            background: #eff6ff;
            border-left: 4px solid #3b82f6;
            color: #475569;
            border-radius: 14px;
            padding: 14px 16px;
            font-size: 14px;
            margin-top: 8px;
        }

        .btn-submit {
            background: linear-gradient(135deg, #0f172a 0%, #334155 100%);
            border: none;
            color: #fff;
            border-radius: 999px;
            padding: 14px 24px;
            font-weight: 600;
            font-size: 16px;
        }

        .btn-submit:hover {
            color: #fff;
        }

        .btn-back {
            border-radius: 999px;
            padding: 12px 22px;
            font-weight: 600;
        }

        .alert {
            border-radius: 16px;
        }

        @media (max-width: 576px) {
            .payment-header {
                padding: 28px 20px 24px;
            }

            .payment-body {
                padding: 28px 18px 22px;
            }

            .amount-box .amount {
                font-size: 28px;
            }

            .info-row {
                flex-direction: column;
                gap: 4px;
            }

            .info-value {
                text-align: left;
            }
        }
    </style>
</head>

<body>
    <div class="payment-wrapper">
        <div class="payment-card">
            <div class="payment-header">
                <div class="payment-badge">฿</div>
                <h2>แจ้งชำระเงิน</h2>
                <p>กรอกข้อมูลและแนบสลิปการโอนเงินให้ครบถ้วน</p>
            </div>

            <div class="payment-body">
                <div class="amount-box">
                    <small><?= $is_reservation ? 'ยอดมัดจำที่ต้องชำระ' : 'ยอดที่ต้องชำระ' ?></small>
                    <div class="amount"><?= number_format($pay_amount, 2) ?> บาท</div>
                </div>

                <div class="info-box">
                    <?php if ($is_reservation): ?>
                        <div class="info-row">
                            <div class="info-label">เลขที่การจอง</div>
                            <div class="info-value">#<?= $reservation['reserve_id'] ?></div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">ชื่อลูกค้า</div>
                            <div class="info-value"><?= htmlspecialchars($reservation['reserve_name']) ?></div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">เบอร์โทร</div>
                            <div class="info-value"><?= htmlspecialchars($reservation['reserve_phone']) ?></div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">ยอดรวมทั้งหมด</div>
                            <div class="info-value"><?= number_format($total_amount, 2) ?> บาท</div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">ยอดคงเหลือหลังมัดจำ</div>
                            <div class="info-value"><?= number_format($remaining_amount, 2) ?> บาท</div>
                        </div>
                    <?php else: ?>
                        <div class="info-row">
                            <div class="info-label">ประเภทการชำระ</div>
                            <div class="info-value">ชำระค่าสินค้าปกติ</div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">ยอดรวมที่ต้องชำระ</div>
                            <div class="info-value"><?= number_format($pay_amount, 2) ?> บาท</div>
                        </div>

                        <?php if ($pickup_date !== '' || $pickup_time !== ''): ?>
                            <div class="info-row">
                                <div class="info-label">วันรับสินค้า</div>
                                <div class="info-value">
                                    <?= htmlspecialchars($pickup_date ?: '-') ?>
                                    <?= htmlspecialchars($pickup_time ?: '') ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>

                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger mb-4">
                        <?= htmlspecialchars($_SESSION['error']);
                        unset($_SESSION['error']); ?>
                    </div>
                <?php endif; ?>

                <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert alert-success mb-4">
                        <?= htmlspecialchars($_SESSION['success']);
                        unset($_SESSION['success']); ?>
                    </div>
                <?php endif; ?>

                <form action="payment_save.php" method="post" enctype="multipart/form-data">
                    <?php if ($is_reservation): ?>
                        <input type="hidden" name="reserve_id" value="<?= (int)$reservation['reserve_id'] ?>">
                    <?php endif; ?>

                    <input type="hidden" name="payment_type" value="<?= $is_reservation ? 'reservation' : 'normal' ?>">
                    <input type="hidden" name="pickup_date" value="<?= htmlspecialchars($pickup_date) ?>">
                    <input type="hidden" name="pickup_time" value="<?= htmlspecialchars($pickup_time) ?>">

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">ชื่อผู้โอน</label>
                            <input type="text" name="payer_name" class="form-control" placeholder="กรอกชื่อผู้โอน"
                                value="<?= htmlspecialchars($payer_name) ?>" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">เบอร์โทร</label>
                            <input type="text" name="payer_phone" class="form-control" placeholder="กรอกเบอร์โทร"
                                value="<?= htmlspecialchars($payer_phone) ?>">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">ยอดที่ชำระ</label>
                            <input type="number" step="0.01" name="pay_amount" class="form-control"
                                value="<?= number_format($pay_amount, 2, '.', '') ?>" required readonly>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">วันเวลาโอน</label>
                            <input type="datetime-local" name="pay_datetime" class="form-control" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">แนบสลิปการโอนเงิน</label>
                        <div class="upload-box">
                            <input type="file" name="slip_image" id="slip_image" class="form-control"
                                accept=".jpg,.jpeg,.png,.webp" required>
                            <div class="form-text mt-2">
                                รองรับไฟล์ .jpg, .jpeg, .png, .webp ขนาดไม่เกิน 5MB
                            </div>

                            <div class="preview-box" id="previewBox">
                                <div class="text-muted mb-2 mt-2">ตัวอย่างสลิป</div>
                                <img id="previewImage" src="" alt="Preview Slip">
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <div class="helper-box">
                            กรุณาตรวจสอบยอดเงิน วันเวลาโอน และรูปสลิปให้ถูกต้องก่อนกดส่งข้อมูล
                        </div>
                    </div>

                    <div class="d-grid gap-2 d-md-flex justify-content-md-between">
                        <a href="cart.php" class="btn btn-outline-secondary btn-back">
                            กลับไปตะกร้า
                        </a>
                        <button type="submit" class="btn btn-submit">
                            ส่งสลิปการโอนเงิน
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="paymentModal" tabindex="-1" aria-labelledby="paymentModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4 border-0 shadow">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold" id="paymentModalLabel">
                        <?= $is_reservation ? 'ชำระมัดจำผ่าน QR พร้อมเพย์' : 'ชำระเงินผ่าน QR พร้อมเพย์' ?>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body text-center px-4 pb-4">
                    <p class="text-muted mb-2">
                        <?= $is_reservation ? 'ยอดมัดจำที่ต้องชำระ' : 'ยอดที่ต้องชำระ' ?>
                    </p>
                    <h2 class="text-danger fw-bold mb-3"><?= number_format($pay_amount, 2) ?> บาท</h2>

                    <div class="bg-light rounded-4 p-3 mb-3">
                        <img src="https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=PromptPayDemo"
                            class="img-fluid mb-2" alt="QR Payment">
                        <p class="text-muted mb-0">
                            <?= $is_reservation ? 'สแกน QR นี้เพื่อชำระมัดจำ' : 'สแกน QR นี้เพื่อชำระเงิน' ?>
                        </p>
                    </div>

                    <button type="button" class="btn btn-success rounded-pill px-4" data-bs-dismiss="modal">
                        สแกนแล้ว → ไปแนบสลิป
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="/assets/js/bootstrap.bundle.min.js"></script>
    <script>
        const slipInput = document.getElementById('slip_image');
        const previewBox = document.getElementById('previewBox');
        const previewImage = document.getElementById('previewImage');

        if (slipInput) {
            slipInput.addEventListener('change', function() {
                const file = this.files[0];

                if (!file) {
                    previewBox.style.display = 'none';
                    previewImage.src = '';
                    return;
                }

                const reader = new FileReader();
                reader.onload = function(e) {
                    previewImage.src = e.target.result;
                    previewBox.style.display = 'block';
                };
                reader.readAsDataURL(file);
            });
        }

        <?php if ($show_qr == 1): ?>
            document.addEventListener("DOMContentLoaded", function() {
                var paymentModalEl = document.getElementById('paymentModal');
                if (paymentModalEl) {
                    var paymentModal = new bootstrap.Modal(paymentModalEl);
                    paymentModal.show();
                }
            });
        <?php endif; ?>
    </script>
</body>

</html>