<?php
session_start();
require_once 'condb.php';

date_default_timezone_set('Asia/Bangkok');

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

if (!in_array($ext, $allow)) {
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

    /*
    |--------------------------------------------------------------------------
    | กรณีชำระเงินค่าสินค้าปกติ -> ตัดสต็อก
    |--------------------------------------------------------------------------
    */
    if ($payment_type === 'normal') {
        if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
            throw new Exception('ไม่พบสินค้าในตะกร้า');
        }

        $cart = $_SESSION['cart'];
        $normalItems = [];

        foreach ($cart as $item) {
            $order_type = $item['order_type'] ?? 'sale';

            // ตัดเฉพาะสินค้าขายปกติ
            if ($order_type === 'sale') {
                $p_id = (int)($item['p_id'] ?? 0);
                $qty  = (int)($item['qty'] ?? 0);

                if ($p_id <= 0 || $qty <= 0) {
                    continue;
                }

                $normalItems[] = [
                    'p_id' => $p_id,
                    'qty'  => $qty
                ];
            }
        }

        if (empty($normalItems)) {
            throw new Exception('ไม่พบรายการสินค้าขายปกติในตะกร้า');
        }

        // เช็กสต็อกซ้ำก่อนตัดจริง
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
        }
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
    $member_id = $_SESSION['m_id'] ?? null;

    $stmt = $conn->prepare("
        INSERT INTO tbl_payment_slip
        (member_id, payer_name, payer_phone, pay_amount, pay_datetime, slip_image, note, status)
        VALUES (?, ?, ?, ?, ?, ?, ?, 'รอตรวจสอบ')
    ");

    $note = null;
    if ($payment_type === 'reservation' && $reserve_id > 0) {
        $note = 'reserve_id:' . $reserve_id;
    } elseif ($payment_type === 'normal') {
        $note = 'sale';
        if ($pickup_date !== '' || $pickup_time !== '') {
            $note .= ' | pickup:' . $pickup_date . ' ' . $pickup_time;
        }
    }

    $stmt->execute([
        $member_id,
        $payer_name,
        $payer_phone,
        $pay_amount,
        date('Y-m-d H:i:s', strtotime($pay_datetime)),
        $new_name,
        $note
    ]);

    /*
    |--------------------------------------------------------------------------
    | บันทึกรายการสินค้าในใบขาย (tbl_sale_detail)
    |--------------------------------------------------------------------------
    */
    $slip_id = $conn->lastInsertId();

    if ($payment_type === 'normal' && isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
        $stmtDetail = $conn->prepare("
            INSERT INTO tbl_sale_detail (slip_id, p_id, qty, price, subtotal)
            VALUES (?, ?, ?, ?, ?)
        ");

        foreach ($_SESSION['cart'] as $item) {
            // เอาเฉพาะสินค้าขายปกติ
            if (($item['order_type'] ?? '') !== 'sale') {
                continue;
            }

            $p_id = (int)($item['p_id'] ?? 0);
            $qty = (int)($item['qty'] ?? 0);
            $price = (float)($item['p_price'] ?? 0);
            $subtotal = $qty * $price;

            if ($p_id > 0 && $qty > 0) {
                $stmtDetail->execute([
                    $slip_id,
                    $p_id,
                    $qty,
                    $price,
                    $subtotal
                ]);
            }
        }
    }

    /*
    |--------------------------------------------------------------------------
    | ถ้าเป็นการจอง อัปเดตสถานะการชำระของการจองได้
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

    // ล้างตะกร้าเฉพาะการขาย
    if ($payment_type === 'normal') {
        unset($_SESSION['cart']);
    }

    $_SESSION['success'] = 'ชำระเงินสำเร็จ และแนบสลิปเรียบร้อยแล้ว';
    header('Location: payment_success.php');
    exit;
} catch (Exception $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }

    // ถ้าอัปโหลดไฟล์ไปแล้วแต่ transaction ล้ม ให้ลบไฟล์ทิ้ง
    if (isset($target_path) && file_exists($target_path)) {
        @unlink($target_path);
    }

    $_SESSION['error'] = $e->getMessage();
    header('Location: payment_form.php');
    exit;
}
