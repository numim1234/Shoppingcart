<?php
session_start();

if (isset($_GET['p_id'])) {
    $p_id = $_GET['p_id'];

    if (isset($_SESSION['cart'][$p_id])) {
        unset($_SESSION['cart'][$p_id]);
    }
}

header("Location: cart.php");
exit();
