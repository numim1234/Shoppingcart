<?php
require_once __DIR__ . '/../require_login.php';
require_once __DIR__ . '/../head.php';
?>

<body>
    <div class="main-wrapper">
        <?php require_once __DIR__ . '/../header.php'; ?>

        <div class="container" style="margin-top:80px;">
            <div class="row">
                <div class="col-md-8 mx-auto">
                    <div class="card p-4">
                        <h3>ยินดีต้อนรับ, <?php echo htmlspecialchars($_SESSION['m_name'] ?? 'สมาชิก'); ?></h3>
                        <p>คุณเข้าสู่ระบบเรียบร้อยแล้ว สามารถเข้าหน้าการสั่งซื้อหรือการจองได้</p>
                        <a class="btn btn-primary" href="/Shoppingcart/">กลับหน้าหลัก</a>
                    </div>
                </div>
            </div>
        </div>

        <?php require_once __DIR__ . '/../footer.php'; ?>
    </div>
</body>

</html>