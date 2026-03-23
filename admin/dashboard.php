<!DOCTYPE html>
<html lang="en">
<?php $menu = "index"; ?>
<?php include 'head.php'; ?>
<?php include '../condb.php'; ?>

<style>
    body {
        font-family: 'Prompt', sans-serif;
        background: #FAF5EE !important;
        color: #4b3b35;
    }

    .title {
        font-weight: 700;
        font-size: 24px;
        color: #3f2f26;
        margin-bottom: 4px;
    }

    .page-sub {
        color: #6b564a;
        margin-top: 0;
        margin-bottom: 18px;
    }

    .card-custom {
        background: #fff;
        border-radius: 12px;
        padding: 18px;
        border: 1px solid rgba(119, 90, 67, 0.06);
        box-shadow: 0 6px 18px rgba(118, 90, 67, 0.04);
    }

    .stat-row {
        display: flex;
        gap: 16px;
        margin-bottom: 16px
    }

    .stat-card {
        flex: 1;
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 14px;
        border-radius: 10px;
        background: #F5E8DA
    }

    .stat-card .stat-icon {
        width: 46px;
        height: 46px;
        border-radius: 10px;
        background: #A67C52;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        font-weight: 700
    }

    .stat-body {
        flex: 1
    }

    .stat-number {
        font-size: 22px;
        font-weight: 800;
        color: #6b3f2a
    }

    .stat-label {
        font-size: 13px;
        color: #7b5c4b;
        margin-top: 4px
    }

    table.table {
        background: transparent
    }

    table.table thead th {
        border-bottom: 0;
        color: #6b564a;
        font-weight: 600
    }

    table.table tbody td {
        border-bottom: 1px solid rgba(119, 90, 67, 0.06);
        color: #4b3b35
    }

    @media (max-width:767px) {
        .stat-row {
            flex-direction: column
        }
    }
</style>

<body class="hold-transition sidebar-mini layout-fixed">
    <div class="wrapper">

        <!-- Navbar -->
        <?php include 'nav.php'; ?>

        <!-- Sidebar -->
        <?php include 'menu.php'; ?>

        <!-- Content -->
        <div class="content-wrapper">
            <div class="content-header">
                <div class="container-fluid">

                    <!-- Title -->
                    <div class="mb-4">
                        <h2 class="title">☕ Bakery Dashboard</h2>
                        <p class="page-sub">ภาพรวมร้านของคุณ</p>
                    </div>

                    <!-- Stats -->
                    <div class="stat-row">
                        <div class="stat-card card-custom">
                            <div class="stat-icon">O</div>
                            <div class="stat-body">
                                <div class="stat-number">120</div>
                                <div class="stat-label">คำสั่งซื้อทั้งหมด</div>
                            </div>
                        </div>

                        <div class="stat-card card-custom">
                            <div class="stat-icon">฿</div>
                            <div class="stat-body">
                                <div class="stat-number">2,450 ฿</div>
                                <div class="stat-label">ยอดขายวันนี้</div>
                            </div>
                        </div>

                        <div class="stat-card card-custom">
                            <div class="stat-icon">P</div>
                            <div class="stat-body">
                                <div class="stat-number">35</div>
                                <div class="stat-label">จำนวนสินค้า</div>
                            </div>
                        </div>
                    </div>

                    <!-- Tables -->
                    <div class="row mt-4 g-3">

                        <!-- Orders -->
                        <div class="col-md-6">
                            <div class="card-custom">
                                <h5 class="mb-3">🧾 คำสั่งซื้อล่าสุด</h5>
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>วันที่</th>
                                            <th>ยอดเงิน</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>001</td>
                                            <td>2026-03-22</td>
                                            <td>150 ฿</td>
                                        </tr>
                                        <tr>
                                            <td>002</td>
                                            <td>2026-03-22</td>
                                            <td>220 ฿</td>
                                        </tr>
                                        <tr>
                                            <td>003</td>
                                            <td>2026-03-22</td>
                                            <td>90 ฿</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Best Seller -->
                        <div class="col-md-6">
                            <div class="card-custom">
                                <h5 class="mb-3">🔥 สินค้าขายดี</h5>
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>สินค้า</th>
                                            <th>จำนวนขาย</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>ครัวซองต์</td>
                                            <td>50</td>
                                        </tr>
                                        <tr>
                                            <td>เค้กช็อกโกแลต</td>
                                            <td>40</td>
                                        </tr>
                                        <tr>
                                            <td>กาแฟลาเต้</td>
                                            <td>30</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                    </div>

                </div>
            </div>
        </div>

        <?php include 'footer.php'; ?>

    </div>

    <?php include 'script.php'; ?>
</body>

</html>