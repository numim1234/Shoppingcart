<!DOCTYPE html>
<?php
header("Location: dashboard.php");
exit();
?>
<html lang="en">
<?php $menu = "index"; ?>
<!-- เรียกใช้ไฟล์ haed -->
<?php include 'head.php';
require_once("../condb.php"); ?>

<body class="hold-transition sidebar-mini layout-fixed">
    <div class="wrapper">

        <!-- Navbar -->
        <?php include 'nav.php'; ?>
        <!-- /.navbar -->

        <!-- Main Sidebar Container -->
        <?php include 'menu.php'; ?>
        <!-- Content Wrapper. Contains page content -->
        <div class="content-wrapper">
            <!-- Content Header (Page header) -->
            <div class="content-header">
                <div class="container-fluid">


                </div>

            </div>
        </div>
        <!-- /.content-wrapper -->
        <?php include 'footer.php'; ?>
        <!-- Control Sidebar -->
        <aside class="control-sidebar control-sidebar-dark">
            <!-- Control sidebar content goes here -->
        </aside>
        <!-- /.control-sidebar -->
    </div>
    <!-- ./wrapper -->
    <?php include 'script.php'; ?>
</body>

</html>