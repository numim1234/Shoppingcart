<?php
session_start();

if (!isset($_SESSION['success'])) {
    header('Location: index.php');
    exit;
}

$msg = $_SESSION['success'];
$last_slip_id = $_SESSION['last_slip_id'] ?? null;
unset($_SESSION['success']);
// keep last_slip_id for one display then clear
if (isset($_SESSION['last_slip_id'])) {
    unset($_SESSION['last_slip_id']);
}
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ชำระเงินสำเร็จ</title>

    <link rel="stylesheet" href="/assets/css/bootstrap.min.css">
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Noto+Sans+Thai:wght@400;500;600;700&display=swap">

    <style>
        body {
            font-family: 'Noto Sans Thai', sans-serif;
            background:
                radial-gradient(circle at top left, #dcfce7 0%, transparent 30%),
                radial-gradient(circle at top right, #dbeafe 0%, transparent 28%),
                linear-gradient(135deg, #f0fdf4 0%, #f8fafc 100%);
            min-height: 100vh;
        }

        .success-wrapper {
            min-height: 100vh;
            display: flex;
            align-items: center;
            padding: 40px 15px;
        }

        .success-card {
            max-width: 600px;
            width: 100%;
            margin: 0 auto;
            background: rgba(255, 255, 255, 0.96);
            border-radius: 28px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.12);
            padding: 45px 30px;
            text-align: center;
            backdrop-filter: blur(6px);
            animation: fadeUp 0.6s ease;
        }

        @keyframes fadeUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .icon-success {
            width: 95px;
            height: 95px;
            border-radius: 50%;
            margin: 0 auto 20px;
            background: linear-gradient(135deg, #22c55e, #16a34a);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 44px;
            font-weight: bold;
            box-shadow: 0 12px 30px rgba(34, 197, 94, 0.35);
        }

        h2 {
            font-weight: 700;
        }

        .success-text {
            color: #475569;
            margin-bottom: 20px;
        }

        .info-box {
            background: #ecfdf5;
            border-left: 4px solid #22c55e;
            padding: 15px 18px;
            border-radius: 16px;
            font-size: 14px;
            color: #334155;
        }

        .btn-main {
            border-radius: 999px;
            padding: 12px 24px;
            font-weight: 600;
            border: none;
            background: linear-gradient(135deg, #0f172a, #334155);
            color: #fff;
            transition: 0.2s;
        }

        .btn-main:hover {
            transform: translateY(-1px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
        }

        .btn-soft {
            border-radius: 999px;
            padding: 12px 24px;
            font-weight: 600;
        }
    </style>
</head>

<body>

    <div class="success-wrapper">
        <div class="success-card">

            <div class="icon-success">✓</div>

            <h2 class="text-success mb-3">ชำระเงินสำเร็จ</h2>

            <p class="success-text">
                <?= htmlspecialchars($msg) ?>
            </p>

            <div class="info-box mb-4">
                ระบบได้รับข้อมูลการโอนเงินและสลิปของคุณเรียบร้อยแล้ว <br>
                กรุณารอการตรวจสอบจากทางร้าน
            </div>

            <div class="d-flex justify-content-center gap-2 flex-wrap">
                <a href="index.php" class="btn btn-main">
                    กลับหน้าหลัก
                </a>

                <a href="cart.php" class="btn btn-outline-secondary btn-soft">
                    ดูตะกร้า
                </a>
                <?php if (!empty($last_slip_id)): ?>
                    <a href="/slip_pdf.php?slip_id=<?= (int)$last_slip_id ?>" class="btn btn-outline-primary btn-soft" target="_blank" rel="noopener" download>ดาวน์โหลดสลิป (PDF)</a>
                <?php endif; ?>
            </div>

        </div>
    </div>

    <!-- popup แจ้งเตือน -->
    <script>
        setTimeout(() => {
            alert("ชำระเงินสำเร็จ 🎉");
        }, 300);
    </script>

</body>

</html>