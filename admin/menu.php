<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>Sidebar</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

    <style>
        body {
            margin: 0;
            font-family: 'Sarabun', sans-serif;
        }

        /* ===== SIDEBAR GLOBAL LAYOUT ===== */
        .main-sidebar {
            width: 250px !important;
            height: 100vh !important;
            position: fixed !important;
            top: 0 !important;
            left: 0 !important;
            overflow-y: auto;
            z-index: 9999 !important;
            pointer-events: auto !important;
            background: linear-gradient(180deg, #eef2ff 0%, #f8faff 100%);
            border-right: 1px solid #e0e7ff;
            box-shadow: 8px 0 24px rgba(99, 102, 241, 0.08);
            transition: all 0.3s ease;
        }

        .main-sidebar * {
            pointer-events: auto !important;
        }

        .main-header {
            margin-left: 250px !important;
            position: relative !important;
            z-index: 1000 !important;
            transition: all 0.3s ease;
        }

        .content-wrapper {
            margin-left: 250px !important;
            position: relative !important;
            z-index: 1 !important;
            min-height: 100vh;
            transition: all 0.3s ease;
        }

        .wrapper,
        .content,
        .container-fluid,
        .card,
        .hero-card,
        .filter-card,
        .table-card,
        .report-card,
        .main-card {
            position: relative;
            z-index: 1;
        }

        /* ===== BRAND ===== */
        .brand-link {
            display: block;
            text-align: center;
            padding: 24px 16px 20px;
            text-decoration: none;
            border-bottom: 1px solid #e5e7eb;
            background: linear-gradient(135deg, #6366f1, #7c3aed);
            position: relative;
            overflow: hidden;
        }

        .brand-link::before {
            content: "";
            position: absolute;
            top: -30px;
            right: -30px;
            width: 100px;
            height: 100px;
            background: rgba(255, 255, 255, 0.12);
            border-radius: 50%;
        }

        .brand-image {
            width: 68px;
            height: 68px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid rgba(255, 255, 255, 0.7);
            box-shadow: 0 6px 14px rgba(0, 0, 0, 0.10);
            background: #fff;
            position: relative;
            z-index: 2;
        }

        .brand-text {
            display: block;
            margin-top: 10px;
            color: #fff;
            font-weight: 800;
            font-size: 15px;
            line-height: 1.4;
            position: relative;
            z-index: 2;
        }

        .brand-subtext {
            display: block;
            margin-top: 4px;
            color: rgba(255, 255, 255, 0.85);
            font-size: 12px;
            font-weight: 600;
            position: relative;
            z-index: 2;
        }

        /* ===== NAV WRAP ===== */
        .main-sidebar ul,
        .main-sidebar .nav {
            list-style: none !important;
            padding: 12px 12px 22px !important;
            margin: 0 !important;
            display: block !important;
            width: 100% !important;
        }

        .nav-header {
            display: block !important;
            width: 100% !important;
            font-size: 11px;
            color: #9ca3af;
            margin: 16px 10px 8px;
            font-weight: 800;
            clear: both;
            text-transform: uppercase;
            letter-spacing: 0.8px;
        }

        .main-sidebar .nav-item,
        .main-sidebar li {
            display: block !important;
            width: 100% !important;
            float: none !important;
            clear: both !important;
            margin: 7px 0 !important;
            padding: 0 !important;
        }

        /* ===== MENU LINK ===== */
        .main-sidebar .nav-link,
        .main-sidebar li a {
            display: flex !important;
            align-items: center !important;
            gap: 12px;
            width: 100% !important;
            padding: 12px 14px !important;
            border-radius: 16px;
            text-decoration: none !important;
            color: #374151 !important;
            background: rgba(255, 255, 255, 0.72) !important;
            backdrop-filter: blur(6px);
            font-weight: 700;
            font-size: 15px;
            box-sizing: border-box !important;
            transition: all .22s ease;
            position: relative;
            z-index: 10000;
            border: 1px solid transparent;
            box-shadow: 0 2px 8px rgba(99, 102, 241, 0.05);
        }

        .main-sidebar .nav-link i,
        .main-sidebar li a i {
            width: 22px !important;
            min-width: 22px !important;
            height: 22px;
            text-align: center;
            color: #6366f1 !important;
            font-size: 15px;
            margin: 0 !important;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .main-sidebar .nav-link span,
        .main-sidebar .nav-link p {
            margin: 0 !important;
            color: #374151 !important;
            line-height: 1.3;
        }

        .main-sidebar .nav-link:hover {
            background: #ffffff !important;
            color: #374151 !important;
            transform: translateX(4px);
            border-color: #dbe4ff;
            box-shadow: 0 8px 18px rgba(99, 102, 241, 0.10);
        }

        .main-sidebar .nav-link.active {
            background: linear-gradient(135deg, #6366f1 0%, #7c3aed 100%) !important;
            color: #fff !important;
            border-color: #6d5efc !important;
            box-shadow: 0 10px 20px rgba(124, 58, 237, 0.22);
        }

        .main-sidebar .nav-link.active i,
        .main-sidebar .nav-link.active span,
        .main-sidebar .nav-link.active p {
            color: #fff !important;
        }

        /* ===== LOGOUT ===== */
        .main-sidebar .nav-link.logout {
            background: #fff1f2 !important;
            border: 1px solid #fecdd3 !important;
            color: #e11d48 !important;
        }

        .main-sidebar .nav-link.logout i,
        .main-sidebar .nav-link.logout span,
        .main-sidebar .nav-link.logout p {
            color: #e11d48 !important;
        }

        .main-sidebar .nav-link.logout:hover {
            background: #ffe4e6 !important;
            border-color: #fda4af !important;
            transform: translateX(3px);
        }

        /* ===== OVERLAY ===== */
        .sidebar-overlay {
            display: none;
        }

        /* ===== SCROLLBAR ===== */
        .main-sidebar::-webkit-scrollbar {
            width: 8px;
        }

        .main-sidebar::-webkit-scrollbar-track {
            background: transparent;
        }

        .main-sidebar::-webkit-scrollbar-thumb {
            background: #c7d2fe;
            border-radius: 999px;
        }

        .main-sidebar::-webkit-scrollbar-thumb:hover {
            background: #a5b4fc;
        }

        /* ===== MOBILE / TABLET ===== */
        @media (max-width: 991px) {
            .main-sidebar {
                left: -260px !important;
                width: 250px !important;
                height: 100vh !important;
            }

            .main-sidebar.show {
                left: 0 !important;
            }

            .main-header,
            .content-wrapper {
                margin-left: 0 !important;
            }

            .sidebar-overlay {
                display: none;
                position: fixed;
                inset: 0;
                background: rgba(15, 23, 42, 0.35);
                z-index: 9998;
            }

            .sidebar-overlay.show {
                display: block;
            }
        }
    </style>
</head>

<body>

    <aside class="main-sidebar" id="mainSidebar">
        <a href="dashboard.php" class="brand-link">
            <img src="m_img/<?php echo htmlspecialchars($_SESSION['m_img'] ?? 'default.png'); ?>" class="brand-image"
                alt="profile">
            <span class="brand-text"><?php echo htmlspecialchars($_SESSION['m_name'] ?? 'Admin'); ?></span>
            <span class="brand-subtext">Bakery Admin Panel</span>
        </a>

        <ul class="nav">
            <li class="nav-item">
                <a href="dashboard.php" class="nav-link <?php if (($menu ?? '') == 'dashboard') echo 'active'; ?>">
                    <i class="fas fa-home"></i>
                    <span>หน้าหลัก</span>
                </a>
            </li>

            <div class="nav-header">จัดการระบบ</div>

            <li class="nav-item">
                <a href="admin.php" class="nav-link <?php if (($menu ?? '') == 'admin') echo 'active'; ?>">
                    <i class="fas fa-user-cog"></i>
                    <span>แอดมิน</span>
                </a>
            </li>

            <li class="nav-item">
                <a href="member.php" class="nav-link <?php if (($menu ?? '') == 'member') echo 'active'; ?>">
                    <i class="fas fa-users"></i>
                    <span>สมาชิก</span>
                </a>
            </li>

            <li class="nav-item">
                <a href="type.php" class="nav-link <?php if (($menu ?? '') == 'type') echo 'active'; ?>">
                    <i class="fas fa-tasks"></i>
                    <span>ประเภท</span>
                </a>
            </li>

            <li class="nav-item">
                <a href="product.php" class="nav-link <?php if (($menu ?? '') == 'product') echo 'active'; ?>">
                    <i class="fas fa-bread-slice"></i>
                    <span>สินค้า</span>
                </a>
            </li>

            <li class="nav-item">
                <a href="stock_product.php"
                    class="nav-link <?php if (($menu ?? '') == 'stock_product') echo 'active'; ?>">
                    <i class="fas fa-warehouse"></i>
                    <span>สต๊อกสินค้า</span>
                </a>
            </li>

            <li class="nav-item">
                <a href="contact_form.php" class="nav-link <?php if (($menu ?? '') == 'contact') echo 'active'; ?>">
                    <i class="fas fa-address-book"></i>
                    <span>ติดต่อเรา</span>
                </a>
            </li>

            <li class="nav-item">
                <a href="orders.php" class="nav-link <?php if (($menu ?? '') == 'orders') echo 'active'; ?>">
                    <i class="fas fa-shopping-bag"></i>
                    <span>ออเดอร์</span>
                </a>
            </li>

            <li class="nav-item">
                <a href="report.php" class="nav-link <?php if (($menu ?? '') == 'report') echo 'active'; ?>">
                    <i class="fas fa-chart-bar"></i>
                    <span>รายงาน</span>
                </a>
            </li>

            <div class="nav-header">ระบบ</div>

            <li class="nav-item">
                <a href="../logout.php" class="nav-link logout">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>ออกจากระบบ</span>
                </a>
            </li>
        </ul>
    </aside>

    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const sidebar = document.getElementById('mainSidebar');
            const overlay = document.getElementById('sidebarOverlay');

            const toggleBtn =
                document.querySelector('[data-widget="pushmenu"]') ||
                document.querySelector('.menu-toggle') ||
                document.querySelector('.navbar-toggler') ||
                document.querySelector('#sidebarToggle');

            function openSidebar() {
                sidebar.classList.add('show');
                overlay.classList.add('show');
            }

            function closeSidebar() {
                sidebar.classList.remove('show');
                overlay.classList.remove('show');
            }

            if (toggleBtn) {
                toggleBtn.addEventListener('click', function(e) {
                    if (window.innerWidth <= 991) {
                        e.preventDefault();
                        if (sidebar.classList.contains('show')) {
                            closeSidebar();
                        } else {
                            openSidebar();
                        }
                    }
                });
            }

            if (overlay) {
                overlay.addEventListener('click', function() {
                    closeSidebar();
                });
            }

            window.addEventListener('resize', function() {
                if (window.innerWidth > 991) {
                    sidebar.classList.remove('show');
                    overlay.classList.remove('show');
                }
            });
        });
    </script>

</body>

</html>