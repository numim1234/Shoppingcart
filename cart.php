<?php
session_start();
require_once("head.php");
require_once("condb.php");

date_default_timezone_set('Asia/Bangkok');

$cart = $_SESSION['cart'] ?? [];
$today = date('Y-m-d');

/*
|--------------------------------------------------------------------------
| อัปเดตจำนวนสินค้าในตะกร้า
|--------------------------------------------------------------------------
*/
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_qty'])) {
    $key = $_POST['key'] ?? '';
    $qty = isset($_POST['qty']) ? (int)$_POST['qty'] : 1;

    if (isset($_SESSION['cart'][$key])) {
        if ($qty < 1) {
            $qty = 1;
        }

        $item = $_SESSION['cart'][$key];
        $order_type = $item['order_type'] ?? 'sale';

        if ($order_type === 'sale') {
            $p_id = (int)($item['p_id'] ?? 0);

            if ($p_id > 0) {
                $stmtStock = $conn->prepare("SELECT p_stock, sale_type FROM tbl_product WHERE p_id = ? LIMIT 1");
                $stmtStock->execute([$p_id]);
                $productStock = $stmtStock->fetch(PDO::FETCH_ASSOC);

                if ($productStock) {
                    $currentStock = (int)($productStock['p_stock'] ?? 0);

                    if ($currentStock <= 0) {
                        $_SESSION['error'] = 'สินค้านี้หมดสต็อกแล้ว';
                        unset($_SESSION['cart'][$key]);
                        header("Location: cart.php");
                        exit();
                    }

                    if ($qty > $currentStock) {
                        $qty = $currentStock;
                        $_SESSION['error'] = 'จำนวนสินค้าเกินสต็อก ระบบปรับเหลือ ' . $currentStock . ' ชิ้น';
                    }
                }
            }
        }

        $_SESSION['cart'][$key]['qty'] = $qty;
        $_SESSION['success'] = $_SESSION['success'] ?? 'อัปเดตจำนวนสินค้าเรียบร้อย';
    }

    header("Location: cart.php");
    exit();
}

/*
|--------------------------------------------------------------------------
| ลบสินค้าออกจากตะกร้า
|--------------------------------------------------------------------------
*/
if (isset($_GET['remove']) && $_GET['remove'] !== '') {
    $removeKey = $_GET['remove'];

    if (isset($_SESSION['cart'][$removeKey])) {
        unset($_SESSION['cart'][$removeKey]);
        $_SESSION['success'] = 'ลบสินค้าออกจากตะกร้าแล้ว';
    }

    header("Location: cart.php");
    exit();
}

/*
|--------------------------------------------------------------------------
| ล้างตะกร้าทั้งหมด
|--------------------------------------------------------------------------
*/
if (isset($_GET['clear']) && $_GET['clear'] === '1') {
    unset($_SESSION['cart']);
    $_SESSION['success'] = 'ล้างตะกร้าสำเร็จ';
    header("Location: cart.php");
    exit();
}

/*
|--------------------------------------------------------------------------
| โหลด stock ล่าสุดของสินค้าประเภท sale
|--------------------------------------------------------------------------
*/
$stockMap = [];

if (!empty($cart)) {
    $saleIds = [];

    foreach ($cart as $item) {
        if (($item['order_type'] ?? 'sale') === 'sale' && !empty($item['p_id'])) {
            $saleIds[] = (int)$item['p_id'];
        }
    }

    $saleIds = array_unique($saleIds);

    if (!empty($saleIds)) {
        $placeholders = implode(',', array_fill(0, count($saleIds), '?'));
        $stmtStockAll = $conn->prepare("
            SELECT p_id, p_stock, sale_type
            FROM tbl_product
            WHERE p_id IN ($placeholders)
        ");
        $stmtStockAll->execute($saleIds);

        while ($row = $stmtStockAll->fetch(PDO::FETCH_ASSOC)) {
            $stockMap[(int)$row['p_id']] = [
                'p_stock'   => (int)$row['p_stock'],
                'sale_type' => $row['sale_type']
            ];
        }
    }
}

/*
|--------------------------------------------------------------------------
| คำนวณยอดรวม
|--------------------------------------------------------------------------
*/
$grandTotal = 0;
$grandDeposit = 0;
$grandRemain = 0;
$totalQty = 0;
$payNow = 0;
$hasReserve = false;
$reserve_id = null;
$canCheckout = true;

if (!empty($cart)) {
    foreach ($cart as $cartItem) {
        if (($cartItem['order_type'] ?? '') === 'reserve') {
            $hasReserve = true;
            $reserve_id = $cartItem['reserve_id'] ?? '';
            break;
        }
    }

    foreach ($cart as $item) {
        $p_price = (float)($item['p_price'] ?? 0);
        $qty = (int)($item['qty'] ?? 1);
        $sum = $p_price * $qty;
        $order_type = $item['order_type'] ?? 'sale';

        if ($order_type === 'reserve') {
            $deposit = isset($item['deposit']) ? (float)$item['deposit'] : ($sum * 0.50);
            $remain  = isset($item['remain']) ? (float)$item['remain'] : ($sum * 0.50);
            $payNow += $deposit;
        } else {
            $deposit = 0;
            $remain  = 0;
            $payNow += $sum;

            $p_id = (int)($item['p_id'] ?? 0);
            $currentStock = $stockMap[$p_id]['p_stock'] ?? 0;

            if ($currentStock <= 0 || $qty > $currentStock) {
                $canCheckout = false;
            }
        }

        $grandTotal += $sum;
        $grandDeposit += $deposit;
        $grandRemain += $remain;
        $totalQty += $qty;
    }
}
?>

<div class="container py-4 py-lg-5">
    <div class="row justify-content-center">
        <div class="col-12 col-xl-11">

            <div class="cart-hero mb-4">
                <div class="cart-hero-left">
                    <div class="hero-icon-box">🛒</div>
                    <div>
                        <h2 class="fw-bold mb-1 cart-title">ตะกร้าสินค้า</h2>
                        <p class="cart-subtitle mb-0">ตรวจสอบรายการสินค้า จำนวน และยอดชำระก่อนยืนยันคำสั่งซื้อ</p>
                    </div>
                </div>

                <div class="d-flex gap-2 flex-wrap">
                    <a href="index.php" class="btn btn-outline-secondary rounded-pill px-4 btn-soft">
                        ← เลือกสินค้าเพิ่ม
                    </a>
                    <?php if (!empty($cart)): ?>
                        <a href="cart.php?clear=1" class="btn btn-outline-danger rounded-pill px-4 btn-soft"
                            onclick="return confirm('ต้องการล้างตะกร้าทั้งหมดหรือไม่?')">
                            ล้างตะกร้า
                        </a>
                    <?php endif; ?>
                </div>
            </div>

            <?php if (!empty($_SESSION['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show rounded-4 shadow-sm border-0" role="alert">
                    <?= htmlspecialchars($_SESSION['success']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION['success']); ?>
            <?php endif; ?>

            <?php if (!empty($_SESSION['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show rounded-4 shadow-sm border-0" role="alert">
                    <?= htmlspecialchars($_SESSION['error']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>

            <?php if (!empty($cart)): ?>

                <div class="cart-summary-mini mb-4">
                    <div class="mini-box">
                        <div class="mini-label">จำนวนสินค้า</div>
                        <div class="mini-value"><?= number_format($totalQty) ?> ชิ้น</div>
                    </div>
                    <div class="mini-box">
                        <div class="mini-label">ยอดรวมทั้งหมด</div>
                        <div class="mini-value"><?= number_format($grandTotal, 2) ?> บาท</div>
                    </div>
                    <div class="mini-box">
                        <div class="mini-label"><?= $hasReserve ? 'ยอดที่ชำระตอนนี้' : 'ยอดชำระตอนนี้' ?></div>
                        <div class="mini-value text-danger"><?= number_format($payNow, 2) ?> บาท</div>
                    </div>
                </div>

                <div class="row g-4">
                    <div class="col-lg-8">
                        <?php foreach ($cart as $key => $item): ?>
                            <?php
                            $p_id = (int)($item['p_id'] ?? 0);
                            $p_name = $item['p_name'] ?? '-';
                            $p_price = (float)($item['p_price'] ?? 0);
                            $qty = (int)($item['qty'] ?? 1);
                            $sum = $p_price * $qty;
                            $img = $item['img'] ?? 'admin/p_gallery/no-image.png';
                            $type_name = $item['type_name'] ?? '-';
                            $order_type = $item['order_type'] ?? 'sale';

                            $currentStock = $stockMap[$p_id]['p_stock'] ?? 0;
                            $isSaleItem = ($order_type === 'sale');
                            $isOutOfStock = $isSaleItem && $currentStock <= 0;
                            $isOverStock = $isSaleItem && $qty > $currentStock;

                            if ($order_type === 'reserve') {
                                $deposit = isset($item['deposit']) ? (float)$item['deposit'] : ($sum * 0.50);
                                $remain  = isset($item['remain']) ? (float)$item['remain'] : ($sum * 0.50);
                            } else {
                                $deposit = 0;
                                $remain  = 0;
                            }
                            ?>

                            <div class="card border-0 shadow-sm rounded-4 mb-4 overflow-hidden cart-card cart-card-strong">
                                <div class="card-body p-0">
                                    <div class="row g-0">
                                        <div class="col-md-4 col-lg-3">
                                            <div class="cart-image-wrap h-100">
                                                <img src="<?= htmlspecialchars($img) ?>" alt="<?= htmlspecialchars($p_name) ?>"
                                                    class="w-100 h-100 cart-product-image"
                                                    onerror="this.onerror=null;this.src='admin/p_gallery/no-image.png';">
                                            </div>
                                        </div>

                                        <div class="col-md-8 col-lg-9">
                                            <div class="p-4 h-100 d-flex flex-column justify-content-between">
                                                <div>
                                                    <div
                                                        class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-3">
                                                        <div>
                                                            <span
                                                                class="badge rounded-pill <?= $order_type === 'reserve' ? 'bg-warning text-dark' : 'bg-success' ?> px-3 py-2 mb-2">
                                                                <?= $order_type === 'reserve' ? 'จองสินค้า' : 'ซื้อปกติ' ?>
                                                            </span>

                                                            <h4 class="mb-1 fw-bold"><?= htmlspecialchars($p_name) ?></h4>
                                                            <div class="text-muted small">
                                                                ประเภท: <?= htmlspecialchars($type_name) ?>
                                                            </div>
                                                        </div>

                                                        <a href="cart.php?remove=<?= urlencode($key) ?>"
                                                            class="btn btn-outline-danger btn-remove-item"
                                                            onclick="return confirm('ต้องการลบสินค้านี้ออกจากตะกร้าหรือไม่?')"
                                                            title="ลบสินค้า">
                                                            ×
                                                        </a>
                                                    </div>

                                                    <div class="row g-3 mt-1">
                                                        <div class="col-md-4">
                                                            <div class="info-box info-box-strong">
                                                                <div class="text-muted small mb-1">ราคาต่อชิ้น</div>
                                                                <div class="fw-bold text-dark">
                                                                    <?= number_format($p_price, 2) ?> บาท
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="col-md-4">
                                                            <div class="info-box info-box-strong">
                                                                <div class="text-muted small mb-2">จำนวน</div>

                                                                <form action="cart.php" method="post"
                                                                    class="d-flex align-items-center gap-2">
                                                                    <input type="hidden" name="update_qty" value="1">
                                                                    <input type="hidden" name="key"
                                                                        value="<?= htmlspecialchars($key) ?>">

                                                                    <button type="button"
                                                                        class="btn btn-outline-secondary btn-qty"
                                                                        onclick="changeQty('<?= htmlspecialchars($key) ?>', -1)"
                                                                        <?= $isOutOfStock ? 'disabled' : '' ?>>
                                                                        -
                                                                    </button>

                                                                    <input type="number" name="qty"
                                                                        id="qty<?= htmlspecialchars($key) ?>"
                                                                        value="<?= $qty ?>" min="1"
                                                                        <?= $isSaleItem ? 'max="' . $currentStock . '"' : '' ?>
                                                                        class="form-control text-center qty-input"
                                                                        onchange="this.form.submit()"
                                                                        <?= $isOutOfStock ? 'disabled' : '' ?>>

                                                                    <button type="button"
                                                                        class="btn btn-outline-secondary btn-qty"
                                                                        onclick="changeQty('<?= htmlspecialchars($key) ?>', 1)"
                                                                        <?= ($isOutOfStock || ($isSaleItem && $qty >= $currentStock)) ? 'disabled' : '' ?>>
                                                                        +
                                                                    </button>
                                                                </form>

                                                                <?php if ($isSaleItem): ?>
                                                                    <small class="text-muted d-block mt-2">
                                                                        สต็อกคงเหลือ <?= number_format($currentStock) ?> ชิ้น
                                                                    </small>

                                                                    <?php if ($isOutOfStock): ?>
                                                                        <small class="text-danger d-block mt-1">
                                                                            สินค้าหมด ไม่สามารถสั่งซื้อได้
                                                                        </small>
                                                                    <?php elseif ($isOverStock): ?>
                                                                        <small class="text-danger d-block mt-1">
                                                                            จำนวนในตะกร้าเกินสต็อก กรุณาปรับเหลือไม่เกิน
                                                                            <?= number_format($currentStock) ?> ชิ้น
                                                                        </small>
                                                                    <?php endif; ?>
                                                                <?php endif; ?>
                                                            </div>
                                                        </div>

                                                        <div class="col-md-4">
                                                            <div class="info-box info-box-strong">
                                                                <div class="text-muted small mb-1">รวมรายการ</div>
                                                                <div class="fw-bold text-danger">
                                                                    <?= number_format($sum, 2) ?> บาท
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <?php if ($order_type === 'reserve'): ?>
                                                        <div
                                                            class="alert alert-warning rounded-4 small mt-3 mb-0 border-0 reserve-alert">
                                                            ชำระมัดจำตอนนี้ <?= number_format($deposit, 2) ?> บาท
                                                            และชำระส่วนที่เหลือวันรับสินค้า <?= number_format($remain, 2) ?> บาท
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="col-lg-4">
                        <div class="card border-0 shadow-sm rounded-4 sticky-lg-top cart-side-card" style="top: 100px;">
                            <div class="card-body p-4">
                                <div class="side-title-wrap mb-4">
                                    <h4 class="fw-bold mb-1">สรุปรายการ</h4>
                                    <p class="text-muted small mb-0">ตรวจสอบยอดก่อนดำเนินการชำระเงิน</p>
                                </div>

                                <div class="summary-row">
                                    <span class="text-muted">จำนวนสินค้าทั้งหมด</span>
                                    <strong><?= number_format($totalQty) ?> ชิ้น</strong>
                                </div>

                                <div class="summary-row">
                                    <span class="text-muted">ราคารวมทั้งหมด</span>
                                    <strong><?= number_format($grandTotal, 2) ?> บาท</strong>
                                </div>

                                <?php if ($grandDeposit > 0): ?>
                                    <div class="summary-row">
                                        <span class="text-muted">ยอดมัดจำรวม</span>
                                        <strong class="text-warning"><?= number_format($grandDeposit, 2) ?> บาท</strong>
                                    </div>

                                    <div class="summary-row">
                                        <span class="text-muted">ยอดคงเหลือวันรับสินค้า</span>
                                        <strong><?= number_format($grandRemain, 2) ?> บาท</strong>
                                    </div>
                                <?php endif; ?>

                                <div class="pay-now-box text-center mt-4 mb-4">
                                    <?= number_format($payNow, 2) ?> บาท
                                    <div class="small fw-normal text-dark mt-2">
                                        <?= $hasReserve ? 'ยอดที่ต้องชำระตอนนี้ (มัดจำ)' : 'ยอดที่ต้องชำระตอนนี้' ?>
                                    </div>
                                </div>

                                <div class="pickup-card mb-4">
                                    <h6 class="fw-bold mb-3">วันและเวลารับสินค้า</h6>

                                    <div class="mb-3">
                                        <label class="form-label">วันที่มารับสินค้า</label>
                                        <input type="date" id="pickup_date" class="form-control" min="<?= $today ?>"
                                            value="<?= $today ?>">
                                    </div>

                                    <div class="mb-2">
                                        <label class="form-label">เวลามารับสินค้า</label>
                                        <input type="time" id="pickup_time" class="form-control" min="00:00" max="17:30">
                                    </div>

                                    <small class="text-danger d-block">
                                        * ลูกค้าต้องมารับสินค้าภายในวันที่สั่งสินค้าเท่านั้น
                                    </small>
                                    <small class="text-danger d-block mt-1">
                                        * เวลารับสินค้าต้องไม่เกิน <strong>17:30 น.</strong>
                                    </small>
                                </div>

                                <div class="alert alert-info rounded-4 small mb-4 border-0 summary-note">
                                    <strong>หมายเหตุ:</strong>
                                    รายการที่เป็น “จองสินค้า” จะชำระเฉพาะมัดจำ 50% ก่อน
                                    และชำระส่วนที่เหลือเมื่อมารับสินค้าที่ร้าน
                                    <br>
                                    <strong>เวลารับสินค้า:</strong> กรุณามารับไม่เกิน <strong>17:30 น.</strong>
                                </div>

                                <div class="d-grid gap-2">
                                    <?php if ($canCheckout): ?>
                                        <button type="button" class="btn btn-success btn-lg rounded-pill checkout-btn"
                                            onclick="openPaymentModal()">
                                            ชำระเงินตอนนี้
                                        </button>
                                    <?php else: ?>
                                        <button type="button" class="btn btn-secondary btn-lg rounded-pill" disabled>
                                            กรุณาตรวจสอบจำนวนสินค้าในตะกร้า
                                        </button>
                                    <?php endif; ?>

                                    <a href="index.php" class="btn btn-outline-secondary rounded-pill">
                                        เลือกสินค้าเพิ่ม
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            <?php else: ?>
                <div class="empty-cart-wrap">
                    <div class="empty-cart-card">
                        <div class="empty-cart-icon">🛒</div>
                        <h3 class="fw-bold mb-2">ยังไม่มีสินค้าในตะกร้า</h3>
                        <p class="text-muted mb-4">เริ่มเลือกสินค้าแล้วเพิ่มลงตะกร้าได้เลย</p>
                        <div class="d-flex justify-content-center gap-2 flex-wrap">
                            <a href="index.php" class="btn btn-danger rounded-pill px-4 py-2">
                                ไปเลือกสินค้า
                            </a>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <?php require_once("footer.php"); ?>
    </div>

    <?php if (!empty($cart)): ?>
        <div class="modal fade" id="paymentModal" tabindex="-1" aria-labelledby="paymentModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content rounded-4 border-0 shadow">
                    <div class="modal-header border-0 pb-0">
                        <h5 class="modal-title fw-bold" id="paymentModalLabel">
                            <?= $hasReserve ? 'ชำระมัดจำผ่าน QR พร้อมเพย์' : 'ชำระเงินผ่าน QR พร้อมเพย์' ?>
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body text-center px-4 pb-4">
                        <p class="text-muted mb-2">
                            <?= $hasReserve ? 'ยอดมัดจำที่ต้องชำระ' : 'ยอดที่ต้องชำระ' ?>
                        </p>
                        <h2 class="text-danger fw-bold mb-3"><?= number_format($payNow, 2) ?> บาท</h2>

                        <div class="bg-light rounded-4 p-3 mb-3">
                            <img src="https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=PromptPayDemo"
                                class="img-fluid mb-2" alt="QR Payment">
                            <p class="text-muted mb-0">
                                <?= $hasReserve ? 'สแกน QR นี้เพื่อชำระมัดจำ' : 'สแกน QR นี้เพื่อชำระเงิน' ?>
                            </p>
                        </div>

                        <form action="payment_form.php" method="get" id="paymentForm">
                            <input type="hidden" name="pay_amount" value="<?= $payNow ?>">

                            <?php if ($hasReserve && !empty($reserve_id)): ?>
                                <input type="hidden" name="reserve_id" value="<?= htmlspecialchars($reserve_id) ?>">
                            <?php endif; ?>

                            <input type="hidden" name="pickup_date" id="hidden_pickup_date">
                            <input type="hidden" name="pickup_time" id="hidden_pickup_time">

                            <button type="submit" class="btn btn-success rounded-pill px-4">
                                สแกนแล้ว → แนบสลิป
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <style>
        body {
            background: #f7f8fb;
        }

        .cart-hero {
            background: linear-gradient(135deg, #ffffff 0%, #f5f7ff 100%);
            border: 1px solid #e5eaf3;
            border-radius: 28px;
            padding: 22px 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 16px;
            box-shadow: 0 12px 32px rgba(31, 41, 55, 0.06);
        }

        .cart-hero-left {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .hero-icon-box {
            width: 68px;
            height: 68px;
            min-width: 68px;
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 34px;
            background: linear-gradient(135deg, #eef2ff 0%, #ffeef1 100%);
            border: 1px solid #e6e9f4;
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.8);
        }

        .cart-title {
            font-size: 2rem;
            color: #0f2f57;
        }

        .cart-subtitle {
            color: #6b7280;
            font-size: 1rem;
        }

        .btn-soft {
            background: #fff;
            border-width: 1px;
            box-shadow: 0 4px 14px rgba(15, 23, 42, 0.05);
        }

        .cart-summary-mini {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 16px;
        }

        .mini-box {
            background: #fff;
            border: 1px solid #e9edf5;
            border-radius: 20px;
            padding: 18px 20px;
            box-shadow: 0 10px 24px rgba(15, 23, 42, 0.04);
        }

        .mini-label {
            color: #8b95a7;
            font-size: 13px;
            margin-bottom: 8px;
        }

        .mini-value {
            font-size: 1.55rem;
            font-weight: 800;
            color: #253045;
            line-height: 1.2;
        }

        .cart-card {
            transition: 0.25s ease;
        }

        .cart-card:hover {
            transform: translateY(-3px);
        }

        .cart-card-strong {
            border: 1px solid #e8ecf3 !important;
            box-shadow: 0 14px 28px rgba(15, 23, 42, 0.06) !important;
            background: #fff;
        }

        .cart-image-wrap {
            background: linear-gradient(180deg, #fafbff 0%, #f4f6fb 100%);
            border-right: 1px solid #edf1f7;
        }

        .cart-product-image {
            min-height: 240px;
            object-fit: cover;
        }

        .info-box {
            background: #f8f9fb;
            border: 1px solid #eef0f4;
            border-radius: 16px;
            padding: 14px 16px;
            height: 100%;
        }

        .info-box-strong {
            background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
            border: 1px solid #e8edf5;
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.7);
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 0;
            border-bottom: 1px dashed #e5e7eb;
            gap: 12px;
        }

        .summary-row:last-of-type {
            border-bottom: none;
        }

        .pay-now-box {
            background: linear-gradient(135deg, #fff1f2 0%, #fff7ed 100%);
            color: #dc2626;
            font-size: 30px;
            font-weight: 700;
            border-radius: 20px;
            padding: 18px 20px;
            border: 1px solid #ffe2e2;
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.6);
        }

        .pickup-card {
            background: linear-gradient(180deg, #fffaf3 0%, #fffefb 100%);
            border: 1px solid #f6d7b8;
            border-radius: 20px;
            padding: 16px;
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.5);
        }

        .pickup-card .form-control {
            border: 1px solid #ead8c9;
            min-height: 46px;
        }

        .pickup-card .form-control:focus {
            border-color: #f59e0b;
            box-shadow: 0 0 0 .2rem rgba(245, 158, 11, 0.12);
        }

        .cart-side-card {
            border: 1px solid #e8edf5 !important;
            box-shadow: 0 14px 30px rgba(15, 23, 42, 0.06) !important;
        }

        .side-title-wrap {
            padding-bottom: 12px;
            border-bottom: 1px solid #edf1f7;
        }

        .summary-note {
            background: linear-gradient(180deg, #eff6ff 0%, #f7fbff 100%);
            border: 1px solid #d8e8ff !important;
            color: #305c8a;
        }

        .reserve-alert {
            background: linear-gradient(180deg, #fff8e7 0%, #fffdf7 100%);
            border: 1px solid #ffe1a8 !important;
            color: #8a5a14;
        }

        .checkout-btn {
            box-shadow: 0 10px 20px rgba(34, 197, 94, 0.2);
        }

        .btn-remove-item {
            width: 40px;
            height: 40px;
            min-width: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            font-weight: bold;
            padding: 0;
            text-decoration: none;
        }

        .btn-qty {
            width: 34px;
            height: 34px;
            min-width: 34px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            font-weight: bold;
            padding: 0;
        }

        .qty-input {
            width: 70px;
            min-width: 70px;
        }

        .empty-cart-wrap {
            background: linear-gradient(180deg, #ffffff 0%, #f9fbff 100%);
            border: 1px solid #e7ecf3;
            border-radius: 28px;
            box-shadow: 0 14px 34px rgba(15, 23, 42, 0.05);
            padding: 24px;
        }

        .empty-cart-card {
            min-height: 420px;
            border: 2px dashed #e2e8f0;
            border-radius: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            text-align: center;
            padding: 36px 20px;
            background:
                radial-gradient(circle at top, rgba(99, 102, 241, 0.04), transparent 35%),
                linear-gradient(180deg, #ffffff 0%, #fbfcff 100%);
        }

        .empty-cart-icon {
            width: 110px;
            height: 110px;
            border-radius: 28px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 54px;
            background: linear-gradient(135deg, #eef2ff 0%, #fff1f2 100%);
            border: 1px solid #e6eaf5;
            box-shadow: 0 12px 30px rgba(15, 23, 42, 0.05);
            margin-bottom: 18px;
        }

        @media (max-width: 991.98px) {
            .cart-summary-mini {
                grid-template-columns: 1fr;
            }

            .cart-hero {
                padding: 18px;
            }

            .cart-hero-left {
                align-items: flex-start;
            }
        }

        @media (max-width: 767.98px) {
            .cart-title {
                font-size: 1.6rem;
            }

            .cart-hero-left {
                flex-direction: column;
                gap: 12px;
            }

            .hero-icon-box {
                width: 56px;
                height: 56px;
                min-width: 56px;
                font-size: 28px;
                border-radius: 16px;
            }

            .empty-cart-card {
                min-height: 340px;
            }
        }
    </style>

    <script>
        function changeQty(id, amount) {
            const input = document.getElementById('qty' + id);
            if (!input) return;

            let val = parseInt(input.value) || 1;
            const max = parseInt(input.getAttribute('max')) || 999999;

            val += amount;

            if (val < 1) {
                val = 1;
            }

            if (val > max) {
                val = max;
            }

            input.value = val;
            input.form.submit();
        }

        function isValidPickupTime(timeValue) {
            if (!timeValue) return false;
            return timeValue <= '17:30';
        }

        function openPaymentModal() {
            const pickupDate = document.getElementById('pickup_date');
            const pickupTime = document.getElementById('pickup_time');
            const hiddenPickupDate = document.getElementById('hidden_pickup_date');
            const hiddenPickupTime = document.getElementById('hidden_pickup_time');

            if (!pickupDate || !pickupTime) return;

            if (pickupDate.value === '') {
                alert('กรุณาเลือกวันที่มารับสินค้า');
                pickupDate.focus();
                return;
            }

            if (pickupTime.value === '') {
                alert('กรุณาเลือกเวลามารับสินค้า');
                pickupTime.focus();
                return;
            }

            if (!isValidPickupTime(pickupTime.value)) {
                alert('เวลามารับสินค้าต้องไม่เกิน 17:30 น.');
                pickupTime.focus();
                return;
            }

            if (hiddenPickupDate) hiddenPickupDate.value = pickupDate.value;
            if (hiddenPickupTime) hiddenPickupTime.value = pickupTime.value;

            const paymentModalEl = document.getElementById('paymentModal');
            if (paymentModalEl) {
                const paymentModal = new bootstrap.Modal(paymentModalEl);
                paymentModal.show();
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            const pickupTime = document.getElementById('pickup_time');
            const paymentForm = document.getElementById('paymentForm');

            if (pickupTime) {
                pickupTime.addEventListener('change', function() {
                    if (this.value && !isValidPickupTime(this.value)) {
                        alert('กรุณาเลือกเวลามารับสินค้าไม่เกิน 17:30 น.');
                        this.value = '';
                        this.focus();
                    }
                });
            }

            if (paymentForm) {
                paymentForm.addEventListener('submit', function(e) {
                    const pickupTimeValue = document.getElementById('pickup_time')?.value || '';

                    if (!pickupTimeValue) {
                        e.preventDefault();
                        alert('กรุณาเลือกเวลามารับสินค้า');
                        return;
                    }

                    if (!isValidPickupTime(pickupTimeValue)) {
                        e.preventDefault();
                        alert('เวลามารับสินค้าต้องไม่เกิน 17:30 น.');
                    }
                });
            }
        });
    </script>
    </body>

    </html>