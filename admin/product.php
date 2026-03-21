<?php
$menu = "product";
?>
<?php include('head.php'); ?>

<body class="hold-transition sidebar-mini layout-fixed">
    <div class="wrapper">

        <?php include('nav.php'); ?>
        <?php include('menu.php'); ?>

        <div class="content-wrapper">
            <div class="content-header">
                <div class="container-fluid">
                    <a href="product.php?act=add"> </a>
                    <?php
                    $act = (isset($_GET['act'])) ? $_GET['act'] : '';
                    if ($act == 'add') {
                        include('product_add.php');
                    } elseif ($act == 'edit') {
                        include('product_edit.php');
                    } elseif ($act == 'img') {
                        include('product_img.php');
                    } else {
                        include('product_list.php');
                    }
                    ?>
                </div>
            </div>
        </div>
        <?php include('footer.php'); ?>

        <aside class=" control-sidebar control-sidebar-dark">
        </aside>
    </div>
    <?php include('script.php'); ?>
</body>

</html>