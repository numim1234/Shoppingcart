<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include 'condb.php';

$sql = "SELECT t.type_id, t.type_name, COUNT(p.type_id) AS cnt
    FROM tbl_type t
    LEFT JOIN tbl_product p ON p.type_id = t.type_id
    GROUP BY t.type_id
    ORDER BY t.type_name ASC";
$stmt = $conn->query($sql);
?>

<!-- Header -->
<header class="header">
    <div class="container">
        <nav class="navbar navbar-expand-lg header-nav">
            <div class="navbar-header">
                <a id="mobile_btn" href="javascript:void(0);">
                    <span class="bar-icon">
                        <span></span>
                        <span></span>
                        <span></span>
                    </span>
                </a>
                <a href="../index.php" class="navbar-brand logo">
                    <img src="../banner/su.png" width="80" class="img-fluid" alt="Logo">
                </a>
            </div>

            <div class="main-menu-wrapper">
                <div class="menu-header">
                    <a href="index.php" class="menu-logo"></a>
                    <a id="menu_close" class="menu-close" href="javascript:void(0);">
                        <i class="fas fa-times"></i>
                    </a>
                </div>

                <ul class="main-nav">
                    <li class="has-submenu megamenu active"><a href="index.php">หน้าหลัก</a></li>
                    <li class="has-submenu">
                        <a href="index.php">ประเภทสินค้า <i class="fas fa-chevron-down"></i></a>
                        <ul class="submenu">
                            <?php foreach ($stmt as $type): ?>
                                <?php
                                $tid = htmlspecialchars($type['type_id']);
                                $tname = htmlspecialchars($type['type_name']);
                                $cnt = (int)$type['cnt'];
                                ?>
                                <li>
                                    <a href="index.php?type_id=<?= $tid ?>">
                                        <?= $tname ?> <span class="badge badge-secondary"><?= $cnt ?></span>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </li>
                    <li><a href="contact.php">ติดต่อเรา</a></li>
                </ul>
            </div>

            <ul class="nav header-navbar-rht">
                <?php if (isset($_SESSION['m_id'])): ?>
                    <li class="nav-item d-flex align-items-center gap-2">
                        <span class="px-3 py-2 rounded-pill bg-light fw-semibold">
                            👋 สวัสดี, <?= htmlspecialchars($_SESSION['m_name'] ?? 'สมาชิก'); ?>
                        </span>
                    </li>

                    <li class="nav-item">
                        <a class="btn btn-outline-danger rounded-pill px-3" href="logout.php"
                            onclick="return confirm('ต้องการออกจากระบบใช่หรือไม่?');">
                            ออกจากระบบ
                        </a>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link header-login" href="login.php">เข้าสู่ระบบ</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link header-login add-listing" href="register.php">
                            <i class="fa-solid fa-plus"></i>สมัครสมาชิก
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>
    </div>
</header>
<!-- /Header -->