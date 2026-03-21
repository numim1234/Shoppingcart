<!DOCTYPE html>
<html lang="en">
<?php $menu = "stock_product";?>
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
       
         <?php       
            include('product_stock_list.php');             
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