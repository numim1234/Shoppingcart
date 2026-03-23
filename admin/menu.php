<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>Sidebar</title>

    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', sans-serif;
        }

        /* ===== SIDEBAR ===== */
        .main-sidebar {
            width: 250px;
            height: 100vh;
            position: fixed;
            background: #fffaf3;
            border-right: 1px solid #f0e2d4;
        }

        /* brand */
        .brand-link {
            display: block;
            text-align: center;
            padding: 20px 10px;
            border-bottom: 1px solid #f0e2d4;
        }

        .brand-image {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            object-fit: cover;
        }

        .brand-text {
            display: block;
            margin-top: 8px;
            color: #6b4f3b;
            font-weight: 600;
        }

        /* menu */
        .nav {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .nav-header {
            font-size: 12px;
            color: #bfa38a;
            margin: 15px 15px 5px;
        }

        .nav-item {
            margin: 5px 10px;
        }

        .nav-link {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px;
            border-radius: 10px;
            text-decoration: none;
            color: #5a4634;
            transition: .3s;
        }

        .nav-link:hover {
            background: #fdf1e6;
        }

        /* active */
        .nav-link.active {
            background: #c8a27c;
            color: #fff;
        }

        .nav-link i {
            width: 20px;
            text-align: center;
        }

        /* logout */
        .nav-link.logout {
            color: #c0392b;
        }
    </style>
</head>

<body>

    <aside class="main-sidebar">

        <!-- Profile -->
        <a href="index.php" class="brand-link">
            <img src="m_img/<?php echo $_SESSION['m_img']; ?>" class="brand-image">
            <span class="brand-text"><?php echo $_SESSION['m_name']; ?></span>
        </a>

        <!-- Menu -->
        <ul class="nav">

            <li class="nav-item">
                <a href="dashboard.php" class="nav-link <?php if ($menu == "dashboard") echo "active"; ?>">
                    <i class="fas fa-home"></i> หน้าหลัก
                </a>
            </li>

            <div class="nav-header">จัดการระบบ</div>

            <li class="nav-item">
                <a href="dashboard.php" class="nav-link <?php if ($menu == "order") echo "active"; ?>">
                    <i class="fas fa-box-open"></i> รายการ
                </a>
            </li>

            <li class="nav-item">
                <a href="admin.php" class="nav-link <?php if ($menu == "admin") echo "active"; ?>">
                    <i class="fas fa-user-cog"></i> แอดมิน
                </a>
            </li>

            <li class="nav-item">
                <a href="member.php" class="nav-link <?php if ($menu == "member") echo "active"; ?>">
                    <i class="fas fa-users"></i> สมาชิก
                </a>
            </li>

            <li class="nav-item">
                <a href="type.php" class="nav-link <?php if ($menu == "type") echo "active"; ?>">
                    <i class="fas fa-tasks"></i> ประเภท
                </a>
            </li>

            <li class="nav-item">
                <a href="product.php" class="nav-link <?php if ($menu == "product") echo "active"; ?>">
                    <i class="fas fa-bread-slice"></i> สินค้า
                </a>
            </li>

            <li class="nav-item">
                <a href="stock_product.php" class="nav-link <?php if ($menu == "stock_product") echo "active"; ?>">
                    <i class="fas fa-warehouse"></i> สต๊อกสินค้า
                </a>
            </li>

            <li class="nav-item">
                <a href="index.php" class="nav-link <?php if ($menu == "report") echo "active"; ?>">
                    <i class="fas fa-chart-bar"></i> รายงาน
                </a>
            </li>

            <div class="nav-header">ระบบ</div>

            <li class="nav-item">
                <a href="#" class="nav-link logout" onclick="confirmLogout(event)">
                    <i class="fas fa-sign-out-alt"></i> ออกจากระบบ
                </a>
            </li>

        </ul>

    </aside>

    <script>
        function confirmLogout(e) {
            e.preventDefault();
            if (confirm("คุณต้องการออกจากระบบใช่หรือไม่?")) {
                window.location.href = "logout.php";
            }
        }
    </script>

</body>

</html>