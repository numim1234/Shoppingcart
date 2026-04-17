<?php
session_start();
require_once 'condb.php';

date_default_timezone_set('Asia/Bangkok');
error_reporting(E_ALL);
ini_set('display_errors', 1);

/*
|--------------------------------------------------------------------------
| ฟังก์ชันหาโปรโมชั่นที่ดีที่สุดสำหรับสินค้าแต่ละรายการ
|--------------------------------------------------------------------------
*/
if (!function_exists('getPromotion')) {
    function getPromotion(PDO $conn, int $p_id, float $lineTotal): array
    {
        $today = date('Y-m-d');

        try {
            $stmt = $conn->prepare("
                SELECT p.*
                FROM tbl_promotion p
                LEFT JOIN tbl_promotion_product pp ON p.promo_id = pp.promo_id
                WHERE p.promo_status = 1
                  AND p.start_date <= ?
                  AND p.end_date >= ?
                  AND p.min_order <= ?
                  AND (
                        p.apply_type = 'all'
                        OR (p.apply_type = 'product' AND pp.p_id = ?)
                      )
                GROUP BY p.promo_id
                ORDER BY p.promo_id DESC
            ");
            $stmt->execute([$today, $today, $lineTotal, $p_id]);
            $promotions = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $bestPromo = null;
            $bestDiscount = 0;

            foreach ($promotions as $promo) {
                $discount = 0;

                if ($promo['promo_type'] === 'percent') {
                    $discount = ($lineTotal * (float)$promo['promo_value']) / 100;
                } elseif ($promo['promo_type'] === 'amount') {
                    $discount = (float)$promo['promo_value'];
                }

                if ($discount > $lineTotal) {
                    $discount = $lineTotal;
                }

                if ($discount > $bestDiscount) {
                    $bestDiscount = $discount;
                    $bestPromo = $promo;
                }
            }

            return [
                'promo' => $bestPromo,
                'discount' => $bestDiscount,
                'final_total' => $lineTotal - $bestDiscount
            ];
        } catch (Exception $e) {
            return [
                'promo' => null,
                'discount' => 0,
                'final_total' => $lineTotal
            ];
        }
    }
}

$payer_name    = trim($_POST['payer_name'] ?? '');
$payer_phone   = trim($_POST['payer_phone'] ?? '');
$pay_amount    = (float)($_POST['pay_amount'] ?? 0);
$pay_datetime  = $_POST['pay_datetime'] ?? '';
$payment_type  = trim($_POST['payment_type'] ?? 'normal');
$reserve_id    = isset($_POST['reserve_id']) ? (int)$_POST['reserve_id'] : 0;
$pickup_date   = trim($_POST['pickup_date'] ?? '');
$pickup_time   = trim($_POST['pickup_time'] ?? '');

if ($payer_name === '' || $pay_amount <= 0 || $pay_datetime === '') {
    $_SESSION['error'] = 'กรอกข้อมูลไม่ครบ';
    header('Location: payment_form.php');
    exit;
}

if (!isset($_FILES['slip_image']) || $_FILES['slip_image']['error'] != 0) {
    $_SESSION['error'] = 'กรุณาแนบสลิป';
    header('Location: payment_form.php');
    exit;
}

$upload_dir = 'uploads/slips/';
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

$file = $_FILES['slip_image'];
$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
$allow = ['jpg', 'jpeg', 'png', 'webp'];

if (!in_array($ext, $allow, true)) {
    $_SESSION['error'] = 'ไฟล์ไม่ถูกต้อง';
    header('Location: payment_form.php');
    exit;
}

if ($file['size'] > 5 * 1024 * 1024) {
    $_SESSION['error'] = 'ไฟล์สลิปต้องมีขนาดไม่เกิน 5MB';
    header('Location: payment_form.php');
    exit;
}

$new_name = 'slip_' . time() . '_' . mt_rand(1000, 9999) . '.' . $ext;
$target_path = $upload_dir . $new_name;

try {
    $conn->beginTransaction();

    $member_id = $_SESSION['m_id'] ?? null;
    $note = null;
    $saleDiscountSummary = [];

    /*
    |--------------------------------------------------------------------------
    | กรณีชำระเงินค่าสินค้าปกติ -> คำนวณโปร + เช็กสต็อก + ตัดสต็อก
    |--------------------------------------------------------------------------
    */
    if ($payment_type === 'normal') {
        if (!isset($_SESSION['cart']) || empty($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
            throw new Exception('ไม่พบสินค้าในตะกร้า');
        }

        $cart = $_SESSION['cart'];
        $normalItems = [];
        $calculatedPayAmount = 0;

        foreach ($cart as $item) {
            $order_type = $item['order_type'] ?? 'sale';
            if ($order_type !== 'sale') {
                continue;
            }

            $p_id  = (int)($item['p_id'] ?? 0);
            $qty   = (int)($item['qty'] ?? 0);
            $price = (float)($item['p_price'] ?? 0);
            $name  = trim($item['p_name'] ?? '-');

            if ($p_id <= 0 || $qty <= 0 || $price < 0) {
                continue;
            }

            $lineTotal = $price * $qty;
            $promoResult = getPromotion($conn, $p_id, $lineTotal);
            $discount = (float)$promoResult['discount'];
            $finalTotal = (float)$promoResult['final_total'];
            $promo = $promoResult['promo'];

            $normalItems[] = [
                'p_id'         => $p_id,
                'p_name'       => $name,
                'qty'          => $qty,
                'price'        => $price,
                'line_total'   => $lineTotal,
                'discount'     => $discount,
                'final_total'  => $finalTotal,
                'promo_id'     => $promo['promo_id'] ?? null,
                'promo_name'   => $promo['promo_name'] ?? null
            ];

            $calculatedPayAmount += $finalTotal;
        }

        if (empty($normalItems)) {
            throw new Exception('ไม่พบรายการสินค้าขายปกติในตะกร้า');
        }

        // กันยอดเพี้ยนจากการแก้ hidden input ฝั่งหน้าเว็บ
        if (abs($calculatedPayAmount - $pay_amount) > 0.01) {
            throw new Exception('ยอดชำระไม่ตรงกับยอดสุทธิในระบบ กรุณาลองใหม่อีกครั้ง');
        }

        $stmtCheck = $conn->prepare("
            SELECT p_id, p_name, p_stock, sale_type
            FROM tbl_product
            WHERE p_id = ?
            LIMIT 1
        ");

        $stmtCutStock = $conn->prepare("
            UPDATE tbl_product
            SET p_stock = p_stock - ?
            WHERE p_id = ? AND p_stock >= ?
        ");

        foreach ($normalItems as $item) {
            $stmtCheck->execute([$item['p_id']]);
            $product = $stmtCheck->fetch(PDO::FETCH_ASSOC);

            if (!$product) {
                throw new Exception('ไม่พบสินค้าในระบบ');
            }

            if (($product['sale_type'] ?? 'sale') !== 'sale') {
                throw new Exception('มีสินค้าที่ไม่ใช่สินค้าขายปกติอยู่ในรายการ');
            }

            $currentStock = (int)($product['p_stock'] ?? 0);
            $qty = (int)$item['qty'];

            if ($currentStock < $qty) {
                throw new Exception('สินค้า "' . $product['p_name'] . '" มีสต็อกไม่พอ');
            }

            $stmtCutStock->execute([$qty, $item['p_id'], $qty]);

            if ($stmtCutStock->rowCount() == 0) {
                throw new Exception('ตัดสต็อกสินค้า "' . $product['p_name'] . '" ไม่สำเร็จ');
            }

            if (!empty($item['promo_name']) && $item['discount'] > 0) {
                $saleDiscountSummary[] = $item['p_name'] . ' ลด ' . number_format($item['discount'], 2) . ' บาท (' . $item['promo_name'] . ')';
            }
        }

        $note = 'sale';
        if ($pickup_date !== '' || $pickup_time !== '') {
            $note .= ' | pickup:' . trim($pickup_date . ' ' . $pickup_time);
        }
        if (!empty($saleDiscountSummary)) {
            $note .= ' | promo:' . implode(', ', $saleDiscountSummary);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | กรณีชำระเงินการจอง
    |--------------------------------------------------------------------------
    */
    if ($payment_type === 'reservation') {
        if ($reserve_id <= 0) {
            throw new Exception('ไม่พบเลขที่การจอง');
        }

        $stmtReserveCheck = $conn->prepare("
            SELECT reserve_id, deposit_amount
            FROM tbl_reservation
            WHERE reserve_id = ?
            LIMIT 1
        ");
        $stmtReserveCheck->execute([$reserve_id]);
        $reserve = $stmtReserveCheck->fetch(PDO::FETCH_ASSOC);

        if (!$reserve) {
            throw new Exception('ไม่พบข้อมูลการจอง');
        }

        $expectedDeposit = (float)$reserve['deposit_amount'];
        if (abs($expectedDeposit - $pay_amount) > 0.01) {
            throw new Exception('ยอดมัดจำไม่ถูกต้อง กรุณาลองใหม่อีกครั้ง');
        }

        $note = 'reserve_id:' . $reserve_id;
    }

    /*
    |--------------------------------------------------------------------------
    | อัปโหลดไฟล์สลิป
    |--------------------------------------------------------------------------
    */
    if (!move_uploaded_file($file['tmp_name'], $target_path)) {
        throw new Exception('อัปโหลดสลิปไม่สำเร็จ');
    }

    /*
    |--------------------------------------------------------------------------
    | บันทึกสลิป
    |--------------------------------------------------------------------------
    */
    $stmt = $conn->prepare("
        INSERT INTO tbl_payment_slip
        (member_id, payer_name, payer_phone, pay_amount, pay_datetime, slip_image, note, status)
        VALUES (?, ?, ?, ?, ?, ?, ?, 'รอตรวจสอบ')
    ");

    $stmt->execute([
        $member_id,
        $payer_name,
        $payer_phone,
        $pay_amount,
        date('Y-m-d H:i:s', strtotime($pay_datetime)),
        $new_name,
        $note
    ]);

    $slip_id = $conn->lastInsertId();

    /*
    |--------------------------------------------------------------------------
    | บันทึกรายการสินค้าในใบขาย (tbl_sale_detail)
    | subtotal จะเก็บยอดสุทธิหลังหักโปรของรายการนั้น
    |--------------------------------------------------------------------------
    */
    if ($payment_type === 'normal') {
        $stmtDetail = $conn->prepare("
            INSERT INTO tbl_sale_detail (slip_id, p_id, qty, price, subtotal)
            VALUES (?, ?, ?, ?, ?)
        ");

        foreach ($normalItems as $item) {
            $stmtDetail->execute([
                $slip_id,
                (int)$item['p_id'],
                (int)$item['qty'],
                (float)$item['price'],
                (float)$item['final_total']
            ]);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | ถ้าเป็นการจอง อัปเดตสถานะการชำระของการจอง
    |--------------------------------------------------------------------------
    */
    if ($payment_type === 'reservation' && $reserve_id > 0) {
        $stmtReserve = $conn->prepare("
            UPDATE tbl_reservation
            SET payment_status = 'paid'
            WHERE reserve_id = ?
        ");
        $stmtReserve->execute([$reserve_id]);
    }

    $conn->commit();

    if ($payment_type === 'normal') {
        unset($_SESSION['cart']);
    }

    $_SESSION['last_slip_id'] = $slip_id;
    $_SESSION['success'] = 'ชำระเงินสำเร็จ และแนบสลิปเรียบร้อยแล้ว';
    header('Location: payment_success.php');
    exit;
} catch (Exception $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }

    if (isset($target_path) && file_exists($target_path)) {
        @unlink($target_path);
    }

    $_SESSION['error'] = $e->getMessage();
    header('Location: payment_form.php');
    exit;
}