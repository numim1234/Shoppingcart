<!DOCTYPE html>
<html lang="en">
<?php $menu = "bank";?>
<!-- เรียกใช้ไฟล์ haed --> 
<?php include'head.php'; ?>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">

  <!-- Navbar -->
  <?php include'nav.php'; ?>
  <!-- /.navbar -->

  <!-- Main Sidebar Container -->
 <?php include'menu.php'; ?>

  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="content-header">
      <div class="container-fluid">
        <a href="bank.php?act=add" type="button" class="btn btn-info btn-rounded btn-sm"><i class="fas fa-plus-circle"></i> บัญชีธนาคาร</a><p>
         <?php 
            $act = (isset($_GET['act']) ? $_GET['act'] : '');
            if ($act == 'add') {
            include('bank_add.php');
            }elseif ($act == 'edit') {
            include('bank_edit.php');
            }else{
            include('bank_list.php'); 
            }
          ?>
      </div><!-- /.container-fluid -->
    </div>
  </div>
  <!-- /.content-wrapper -->
  <?php include'footer.php'; ?>
  <!-- Control Sidebar -->
  <aside class="control-sidebar control-sidebar-dark">
    <!-- Control sidebar content goes here -->
  </aside>
  <!-- /.control-sidebar -->
</div>
<!-- ./wrapper -->
<?php include'script.php'; ?>
</body>
</html>
