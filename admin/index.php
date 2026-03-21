<!DOCTYPE html>
<html lang="en">
<?php $menu = "index";?>
<!-- เรียกใช้ไฟล์ haed --> 
<?php include'head.php';   
require_once("../condb.php"); ?>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">

  <!-- Navbar -->
  <?php include'nav.php'; ?>
  <!-- /.navbar -->

  <!-- Main Sidebar Container -->
 <?php include'menu.php';?>
  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="content-header">
      <div class="container-fluid">
       <iframe width="100%" height="700" src="https://www.youtube.com/embed/yncpMqgTu24?si=P89RjVD-nGdoaPm_" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>
     
      </div>
      
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
