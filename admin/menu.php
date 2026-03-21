<aside class="main-sidebar sidebar-dark-light elevation-4">
    <!-- Brand Logo -->
    <a href="index.php" class="brand-link">
        <img src="m_img/<?php echo $_SESSION['m_img']; ?>" alt="AdminLTE Logo"
            class="brand-image img-circle elevation-3" style="opacity: .8">
        <span class="brand-text font-weight-light"><?php echo $_SESSION['m_name']; ?>

    </a>
    <!-- Sidebar -->
    <div class="sidebar">
        <!-- Sidebar Menu -->
        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                <li class="nav-item">
                    <a href="dashboard.php" class="nav-link <?php if ($menu == "dashboard") {
                                                                echo "active";
                                                            } ?>">
                        <i class="nav-icon fas fa-home"></i>
                        <p>หน้าหลัก</p>
                    </a>
                </li>
                <li class="nav-header">จัดการระบบ</li>
                <li class="nav-item">
                    <a href="dashboard.php" class="nav-link <?php if ($menu == "order") {
                                                                echo "active";
                                                            } ?>">
                        <i class="nav-icon fas fa-box-open"></i>
                        <p>รายการ</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="admin.php" class="nav-link <?php if ($menu == "admin") {
                                                            echo "active";
                                                        } ?>">
                        <i class="nav-icon fas fa-user-cog"></i>
                        <p>แอดมิน</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="member.php" class="nav-link <?php if ($menu == "member") {
                                                                echo "active";
                                                            } ?>">
                        <i class="nav-icon fas fa-users"></i>
                        <p>สมาชิก</p>
                    </a>
                </li>
                <!-- <li class="nav-item">
                    <a href="bank.php" class="nav-link <?php if ($menu == "bank") {
                                                            echo "active";
                                                        } ?>">
                        <i class="nav-icon fas fa-donate"></i>
                        <p>บัญชี</p>
                    </a>
                </li> -->
                <!-- <li class="nav-item">
                    <a href="promotion.php" class="nav-link <?php if ($menu == "promotion") {
                                                                echo "active";
                                                            } ?>">
                        <i class="nav-icon fas fa-layer-group"></i>
                        <p>โปรโมชั่น</p>
                    </a>
                </li> -->
                <li class="nav-item">
                    <a href="type.php" class="nav-link <?php if ($menu == "type") {
                                                            echo "active";
                                                        } ?>">
                        <i class="nav-icon fas fa-tasks"></i>
                        <p>ประเภท</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="product.php" class="nav-link <?php if ($menu == "product") {
                                                                echo "active";
                                                            } ?>">
                        <i class="nav-icon fas fa-cart-plus"></i>
                        <p>สินค้า</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="stock_product.php" class="nav-link <?php if ($menu == "stock_product") {
                                                                    echo "active";
                                                                } ?>">
                        <i class="nav-icon fas fa-coins"></i>
                        <p>สต๊อกสินค้าสินค้า</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="index.php" class="nav-link  <?php if ($menu == "report") {
                                                                echo "active";
                                                            } ?>">
                        <i class="nav-icon fas fa-chart-bar"></i>
                        <p>รายงานยอดขาย</p>
                    </a>
                </li>
                <li class="nav-header">ออกจากระบบ</li>
                <li class="nav-item">
                    <a href="" class="nav-link" onclick="confirmLogout(event)">
                        <i class="nav-icon fas fa-sign-out-alt"></i>
                        <p>ออกจากระบบ</p>
                    </a>
                </li>
            </ul>
        </nav>
        <!-- /.sidebar-menu -->
    </div>
    <!-- /.sidebar -->
</aside>