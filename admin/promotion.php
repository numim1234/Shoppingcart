<!DOCTYPE html>
<html lang="th">
<?php $menu = "promotion"; ?>
<?php include 'head.php'; ?>
<style>
.page-head {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 12px;
}

.page-title {
    margin: 0;
    font-size: 28px;
    font-weight: 800;
    color: #253045;
}

.page-subtitle {
    font-size: 14px;
    color: #8a94a6;
    margin-top: 5px;
}

/* Button styling consistent with admin theme */
.page-action .btn-add {
    border: none;
    border-radius: 14px;
    padding: 10px 18px;
    font-weight: 700;
    background: linear-gradient(135deg, #6478ff, #5865f2);
    color: #fff;
    box-shadow: 0 10px 18px rgba(88, 101, 242, 0.22);
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.page-action .btn-add:hover {
    opacity: 0.95;
    color: #fff;
}

.page-action .btn-add .me-2 {
    margin-right: 8px;
}
</style>

<body class="hold-transition sidebar-mini layout-fixed">
    <div class="wrapper">

        <?php include 'nav.php'; ?>
        <?php include 'menu.php'; ?>

        <div class="content-wrapper">
            <section class="content-header">
                <div class="container-fluid">
                    <div class="row mb-2">
                        <div class="col-sm-6">
                            <h1>จัดการโปรโมชั่น</h1>
                        </div>
                        <div class="col-sm-6 text-right page-action">
                            <a href="promotion.php?act=add" class="btn-add">
                                <i class="fas fa-plus"></i>
                                เพิ่มโปรโมชั่น
                            </a>
                        </div>
                    </div>
                </div>
            </section>

            <section class="content">
                <div class="container-fluid">
                    <?php
          $act = $_GET['act'] ?? '';

          if ($act === 'add') {
            include 'promotion_add.php';
          } elseif ($act === 'edit') {
            include 'promotion_edit.php';
          } else {
            include 'promotion_list.php';
          }
          ?>
                </div>
            </section>
        </div>

        <?php include 'footer.php'; ?>
    </div>
</body>

</html>