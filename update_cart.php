<?php
session_start();

if (isset($_POST['p_id']) && isset($_POST['qty'])) {
    $p_id = $_POST['p_id'];
    $qty = (int)$_POST['qty'];

    if (isset($_SESSION['cart'][$p_id])) {
        if ($qty < 1) {
            unset($_SESSION['cart'][$p_id]);
        } else {
            $_SESSION['cart'][$p_id]['qty'] = $qty;
        }
    }
}

header("Location: cart.php");
exit();
