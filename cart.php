<?php
session_start();
require_once 'require_login.php';
require_once 'condb.php';
require_once 'header.php';

$cart = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];
?>

<div class="container" style="margin-top:80px;">
    <h3>ตะกร้าสินค้า</h3>

    <?php if (empty($cart)): ?>
        <p>ตะกร้าว่าง</p>
    <?php else: ?>
        <table class="table table-bordered">
            <tr>
                <th>สินค้า</th>
                <th>ราคา</th>
                <th>จำนวน</th>
                <th>รวม</th>
            </tr>
            <?php
            $total = 0;
            foreach ($cart as $item) {
                $sum = $item['p_price'] * $item['qty'];
                $total += $sum;
                echo "<tr>
        <td>{$item['p_name']}</td>
        <td>{$item['p_price']}</td>
        <td>{$item['qty']}</td>
        <td>{$sum}</td>
    </tr>";
            }
            echo "<tr><td colspan='3'>รวมทั้งหมด</td><td>{$total}</td></tr>";
            ?>
        </table>
    <?php endif; ?>
</div>

<?php require_once 'footer.php'; ?>