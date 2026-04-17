<?php
session_start();
require_once("condb.php");
require_once("head.php");

$keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';
$type_id = isset($_GET['type_id']) ? (int)$_GET['type_id'] : 0;

// นับจำนวนสินค้าในตะกร้า
$cartCount = 0;
if (!empty($_SESSION['cart']) && is_array($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $item) {
        if (($item['order_type'] ?? '') !== 'reserve') {
            $cartCount += (int)($item['qty'] ?? 0);
        }
    }
}

// ดึงชื่อประเภทที่เลือก
$selectedType = null;
if ($type_id > 0) {
    $stmtSelectedType = $conn->prepare("SELECT * FROM tbl_type WHERE type_id = ?");
    $stmtSelectedType->execute([$type_id]);
    $selectedType = $stmtSelectedType->fetch(PDO::FETCH_ASSOC);
}

// =========================
// ดึงโปรโมชั่นที่ยังใช้งาน
// =========================
$promotions = [];
try {
    $stmtPromo = $conn->prepare("
        SELECT *
        FROM tbl_promotion
        WHERE promo_status = 1
          AND start_date <= CURRENT_DATE()
          AND end_date >= CURRENT_DATE()
        ORDER BY promo_id DESC
        LIMIT 6
    ");
    $stmtPromo->execute();
    $promotions = $stmtPromo->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $promotions = [];
}

// =========================
// ดึงสินค้าขายดี 3 อันดับแรก
// =========================
$bestProducts = [];

try {
    $stmtBest = $conn->prepare("
        SELECT 
            p.p_id,
            p.p_stock,
            p.p_name,
            p.p_price,
            p.p_detail,
            p.img,
            p.type_id,
            t.type_name,
            COALESCE(SUM(rd.qty), 0) AS total_sold
        FROM tbl_reservation r
        INNER JOIN tbl_reservation_detail rd ON r.reserve_id = rd.reserve_id
        INNER JOIN tbl_product p ON p.p_id = rd.p_id
        LEFT JOIN tbl_type t ON p.type_id = t.type_id
        WHERE MONTH(r.created_at) = MONTH(CURRENT_DATE())
          AND YEAR(r.created_at) = YEAR(CURRENT_DATE())
          AND r.payment_status = 'paid'
          AND p.p_status = 1
          AND (p.sale_type = 'sale' OR p.sale_type IS NULL OR p.sale_type = '')
        GROUP BY p.p_id, p.p_name, p.p_price, p.p_detail, p.img, p.type_id, t.type_name, p.p_stock
        ORDER BY total_sold DESC, p.p_id DESC
        LIMIT 3
    ");
    $stmtBest->execute();
    $bestProducts = $stmtBest->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $bestProducts = [];
}

// =========================
// ดึงสินค้าพร้อมขาย + สถานะเปิด
// =========================
if ($keyword != '' && $type_id > 0) {
    $stmtProduct = $conn->prepare("
        SELECT p.*, t.type_name
        FROM tbl_product p
        LEFT JOIN tbl_type t ON p.type_id = t.type_id
        WHERE (p.p_name LIKE ? OR p.p_detail LIKE ? OR t.type_name LIKE ?)
          AND p.type_id = ?
          AND p.p_status = 1
          AND (p.sale_type = 'sale' OR p.sale_type IS NULL OR p.sale_type = '')
        ORDER BY p.p_id DESC
    ");
    $search = "%{$keyword}%";
    $stmtProduct->execute([$search, $search, $search, $type_id]);
} elseif ($keyword != '') {
    $stmtProduct = $conn->prepare("
        SELECT p.*, t.type_name
        FROM tbl_product p
        LEFT JOIN tbl_type t ON p.type_id = t.type_id
        WHERE (p.p_name LIKE ? OR p.p_detail LIKE ? OR t.type_name LIKE ?)
          AND p.p_status = 1
          AND (p.sale_type = 'sale' OR p.sale_type IS NULL OR p.sale_type = '')
        ORDER BY p.p_id DESC
    ");
    $search = "%{$keyword}%";
    $stmtProduct->execute([$search, $search, $search]);
} elseif ($type_id > 0) {
    $stmtProduct = $conn->prepare("
        SELECT p.*, t.type_name
        FROM tbl_product p
        LEFT JOIN tbl_type t ON p.type_id = t.type_id
        WHERE p.type_id = ?
          AND p.p_status = 1
          AND (p.sale_type = 'sale' OR p.sale_type IS NULL OR p.sale_type = '')
        ORDER BY p.p_id DESC
    ");
    $stmtProduct->execute([$type_id]);
} else {
    $stmtProduct = $conn->prepare("
        SELECT p.*, t.type_name
        FROM tbl_product p
        LEFT JOIN tbl_type t ON p.type_id = t.type_id
        WHERE p.p_status = 1
          AND (p.sale_type = 'sale' OR p.sale_type IS NULL OR p.sale_type = '')
        ORDER BY p.p_id DESC
    ");
    $stmtProduct->execute();
}

$products = $stmtProduct->fetchAll(PDO::FETCH_ASSOC);
?>

<body>
    <div class="main-wrapper innerpagebg">

        <?php require_once("header.php"); ?>

        <?php if ($keyword == '' && $type_id == 0): ?>
        <?php require_once("banner.php"); ?>
        <?php endif; ?>

        <div class="container py-5">

            <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success rounded-4 shadow-sm border-0">
                <?= $_SESSION['success'];
                    unset($_SESSION['success']); ?>
            </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger rounded-4 shadow-sm border-0">
                <?= $_SESSION['error'];
                    unset($_SESSION['error']); ?>
            </div>
            <?php endif; ?>

            <div class="shop-topbar mb-4">
                <div>
                    <?php if ($type_id > 0 && !empty($selectedType)): ?>
                    <h3 class="mb-1">สินค้าพร้อมขาย: <?= htmlspecialchars($selectedType['type_name']) ?></h3>
                    <p class="text-muted mb-0">แสดงสินค้าเฉพาะประเภทที่เลือก</p>
                    <?php elseif ($keyword != ''): ?>
                    <h3 class="mb-1">ผลการค้นหาสินค้าพร้อมขาย: "<?= htmlspecialchars($keyword) ?>"</h3>
                    <p class="text-muted mb-0">รายการสินค้าที่ค้นพบ</p>
                    <?php else: ?>
                    <h3 class="mb-1">สินค้าพร้อมขาย</h3>
                    <p class="text-muted mb-0">รายการสินค้าที่สามารถสั่งซื้อได้ทันที</p>
                    <?php endif; ?>
                </div>

                <div class="shop-topbar-right">
                    <?php if ($type_id > 0 || $keyword != ''): ?>
                    <a href="index.php" class="btn btn-light action-btn">
                        <i class="fas fa-th-large me-2"></i>ดูสินค้าทั้งหมด
                    </a>
                    <?php endif; ?>

                    <a href="reserve_product.php" class="btn btn-primary action-btn">
                        <i class="fas fa-calendar-check me-2"></i>ไปหน้าการจอง
                    </a>

                    <?php if (isset($_SESSION['m_id'])): ?>
                    <a href="order_history.php" class="btn btn-danger action-btn text-white">
                        <i class="fas fa-history me-2"></i>ประวัติการสั่งซื้อ
                    </a>
                    <?php endif; ?>

                    <a href="cart.php" class="btn btn-danger action-btn position-relative">
                        <i class="fas fa-shopping-cart me-2"></i>ตะกร้าสินค้า
                        <?php if ($cartCount > 0): ?>
                        <span
                            class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-warning text-dark">
                            <?= $cartCount ?>
                        </span>
                        <?php endif; ?>
                    </a>
                </div>
            </div>

            <?php if ($keyword == '' && $type_id == 0 && !empty($promotions)): ?>
            <div class="promo-section-block mb-5">
                <div class="section-head mb-4">
                    <h3 class="mb-1">🎉 โปรโมชั่นพิเศษ</h3>
                    <p class="text-muted mb-0">โปรโมชั่นที่กำลังใช้งานอยู่ในขณะนี้</p>
                </div>

                <div class="row g-4">
                    <?php foreach ($promotions as $promo): ?>
                    <?php
                            $promoValueText = ($promo['promo_type'] === 'percent')
                                ? number_format((float)$promo['promo_value'], 0) . '%'
                                : number_format((float)$promo['promo_value'], 2) . ' บาท';

                            $promoApplyText = ($promo['apply_type'] === 'all') ? 'ใช้ได้ทั้งร้าน' : 'ใช้ได้เฉพาะสินค้าที่ร่วมรายการ';
                            ?>
                    <div class="col-lg-4 col-md-6">
                        <div class="card promo-card h-100 border-0 shadow-sm">
                            <div class="promo-top text-white">
                                <div class="promo-badge">PROMOTION</div>
                                <h4 class="mb-2 promo-title"><?= htmlspecialchars($promo['promo_name']) ?></h4>
                                <div class="promo-value"><?= $promoValueText ?></div>
                            </div>

                            <div class="card-body d-flex flex-column">
                                <div class="promo-minimum mb-2">
                                    ยอดขั้นต่ำ <?= number_format((float)$promo['min_order'], 2) ?> บาท
                                </div>

                                <div class="promo-apply mb-2">
                                    <?= htmlspecialchars($promoApplyText) ?>
                                </div>

                                <p class="text-muted promo-detail mb-3">
                                    <?= !empty($promo['promo_detail']) ? nl2br(htmlspecialchars($promo['promo_detail'])) : 'โปรโมชั่นพิเศษสำหรับลูกค้าของร้าน' ?>
                                </p>

                                <div class="mt-auto">
                                    <div class="promo-date">
                                        <i class="far fa-calendar-alt me-2"></i>
                                        <?= date('d/m/Y', strtotime($promo['start_date'])) ?>
                                        - <?= date('d/m/Y', strtotime($promo['end_date'])) ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <?php if ($keyword == '' && $type_id == 0 && !empty($bestProducts)): ?>
            <div class="best-seller-section mb-5">
                <div class="section-head mb-4">
                    <h3 class="mb-1">🔥 สินค้าขายดี 3 อันดับแรก</h3>
                    <p class="text-muted mb-0">สินค้าที่ลูกค้าสั่งซื้อมากที่สุด</p>
                </div>

                <div class="row g-4">
                    <?php foreach ($bestProducts as $index => $best): ?>
                    <?php
                            $bestImgFile = '';

                            $stmtBestImg = $conn->prepare("SELECT img FROM tbl_img_detail WHERE p_id = ? ORDER BY id DESC LIMIT 1");
                            $stmtBestImg->execute([$best['p_id']]);
                            $bestDetailImg = $stmtBestImg->fetchColumn();

                            if (!empty($bestDetailImg)) {
                                $bestImgFile = trim($bestDetailImg);
                            } elseif (!empty($best['img'])) {
                                $bestImgFile = trim($best['img']);
                            }

                            $bestImgPath = 'admin/p_gallery/no-image.png';
                            if ($bestImgFile != '' && file_exists(__DIR__ . '/admin/p_gallery/' . $bestImgFile)) {
                                $bestImgPath = 'admin/p_gallery/' . $bestImgFile;
                            }

                            $bestShortDetail = mb_substr($best['p_detail'] ?? '', 0, 90);
                            $bestStock = (int)($best['p_stock'] ?? 0);
                            ?>

                    <div class="col-lg-4 col-md-6">
                        <div class="card h-100 border-0 shadow-sm best-card">
                            <div class="product-img-wrap position-relative">
                                <img src="<?= htmlspecialchars($bestImgPath) ?>" class="card-img-top product-img"
                                    alt="<?= htmlspecialchars($best['p_name']) ?>" loading="lazy"
                                    onerror="this.onerror=null;this.src='admin/p_gallery/no-image.png';">

                                <span class="best-rank-badge">
                                    อันดับ <?= $index + 1 ?>
                                </span>
                            </div>

                            <div class="card-body d-flex flex-column p-3">
                                <div class="small text-muted mb-2">
                                    <?= htmlspecialchars($best['type_name'] ?? '-') ?>
                                </div>

                                <h5 class="card-title product-title mb-2">
                                    <?= htmlspecialchars($best['p_name']) ?>
                                </h5>

                                <div class="product-price mb-2">
                                    <?= number_format((float)$best['p_price'], 2) ?> บาท
                                </div>

                                <div class="small text-muted mb-2">
                                    สต็อกคงเหลือ <?= number_format($bestStock) ?> ชิ้น
                                </div>

                                <div class="mb-3">
                                    <span class="badge bg-warning text-dark rounded-pill px-3 py-2">
                                        ขายแล้ว <?= number_format((float)$best['total_sold']) ?> ชิ้น
                                    </span>
                                </div>

                                <p class="card-text text-muted product-desc mb-3">
                                    <?= htmlspecialchars($bestShortDetail) ?><?= mb_strlen($best['p_detail'] ?? '') > 90 ? '...' : '' ?>
                                </p>

                                <div class="mt-auto d-grid gap-2">
                                    <button type="button" class="btn btn-outline-dark rounded-pill"
                                        data-bs-toggle="modal" data-bs-target="#productModal<?= (int)$best['p_id'] ?>">
                                        <i class="fas fa-eye me-2"></i>ดูรายละเอียด
                                    </button>

                                    <?php if (isset($_SESSION['m_id'])): ?>
                                    <form action="add_to_cart.php" method="post" class="m-0">
                                        <input type="hidden" name="p_id" value="<?= (int)$best['p_id'] ?>">
                                        <input type="hidden" name="qty" value="1">
                                        <button type="submit" class="btn btn-success rounded-pill w-100">
                                            <i class="fas fa-shopping-cart me-2"></i>ซื้อเลย
                                        </button>
                                    </form>
                                    <?php else: ?>
                                    <a href="login.php" class="btn btn-danger rounded-pill w-100">
                                        เข้าสู่ระบบเพื่อสั่งซื้อ
                                    </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal fade" id="productModal<?= (int)$best['p_id'] ?>" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered modal-lg">
                            <div class="modal-content rounded-4 border-0 shadow-lg">
                                <div class="modal-header border-0 pb-0">
                                    <button type="button" class="btn-close position-relative" data-bs-dismiss="modal"
                                        aria-label="Close" style="z-index:1060;"></button>
                                </div>

                                <div class="modal-body pt-0 p-4">
                                    <div class="row g-4 align-items-start">
                                        <div class="col-md-6">
                                            <div class="bg-light rounded-4 overflow-hidden border">
                                                <img src="<?= htmlspecialchars($bestImgPath) ?>" class="img-fluid w-100"
                                                    style="height:380px; object-fit:cover;"
                                                    alt="<?= htmlspecialchars($best['p_name']) ?>"
                                                    onerror="this.onerror=null;this.src='admin/p_gallery/no-image.png';">
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <p class="text-muted mb-2">
                                                <?= htmlspecialchars($best['type_name'] ?? '-') ?></p>
                                            <h3 class="mb-2"><?= htmlspecialchars($best['p_name']) ?></h3>

                                            <h4 class="text-danger fw-bold mb-3">
                                                <?= number_format((float)$best['p_price'], 2) ?> บาท
                                            </h4>

                                            <div class="mb-3">
                                                <span class="badge bg-success rounded-pill px-3 py-2">
                                                    พร้อมขาย
                                                </span>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label fw-bold">รายละเอียดสินค้า</label>
                                                <div class="bg-light rounded-4 p-3 text-muted" style="line-height:1.8;">
                                                    <?= nl2br(htmlspecialchars($best['p_detail'] ?? '-')) ?>
                                                </div>
                                            </div>

                                            <?php if (isset($_SESSION['m_id'])): ?>
                                            <form action="add_to_cart.php" method="post">
                                                <input type="hidden" name="p_id" value="<?= (int)$best['p_id'] ?>">

                                                <div class="mb-3">
                                                    <label class="form-label fw-bold">จำนวน</label>
                                                    <div class="d-flex align-items-center gap-2">
                                                        <button type="button"
                                                            class="btn btn-outline-secondary rounded-circle qty-btn"
                                                            onclick="changeQtyGenericSale(<?= (int)$best['p_id'] ?>, -1)">
                                                            -
                                                        </button>

                                                        <input type="number" name="qty"
                                                            id="qtySale<?= (int)$best['p_id'] ?>" value="1" min="1"
                                                            class="form-control text-center qty-input"
                                                            style="width:90px;"
                                                            <?= $bestStock > 0 ? 'max="' . $bestStock . '"' : '' ?>>

                                                        <button type="button"
                                                            class="btn btn-outline-secondary rounded-circle qty-btn"
                                                            onclick="changeQtyGenericSale(<?= (int)$best['p_id'] ?>, 1)">
                                                            +
                                                        </button>
                                                    </div>
                                                </div>

                                                <div class="mb-3 stock-block">
                                                    <small class="text-muted">สต็อกคงเหลือ</small>
                                                    <div class="fw-bold mb-1"><?= number_format($bestStock) ?> ชิ้น
                                                    </div>
                                                </div>

                                                <div class="mb-3">
                                                    <small class="text-muted">ราคารวม</small>
                                                    <div class="fw-bold text-danger fs-5"
                                                        id="totalPrice<?= (int)$best['p_id'] ?>"
                                                        data-price="<?= (float)$best['p_price'] ?>">
                                                        <?= number_format((float)$best['p_price'], 2) ?> บาท
                                                    </div>
                                                </div>

                                                <div class="d-grid">
                                                    <button type="submit"
                                                        class="btn btn-success rounded-pill w-100 py-2">
                                                        <i class="fas fa-shopping-cart me-2"></i>เพิ่มเข้าตะกร้า
                                                    </button>
                                                </div>
                                            </form>
                                            <?php else: ?>
                                            <div class="d-grid gap-2">
                                                <a href="login.php" class="btn btn-danger rounded-pill py-2">
                                                    เข้าสู่ระบบเพื่อสั่งซื้อ
                                                </a>
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
            </div>
            <?php endif; ?>

            <div class="section-head mb-4">
                <h3 class="mb-1">รายการสินค้าทั้งหมด</h3>
                <p class="text-muted mb-0">รายการสินค้าที่สามารถสั่งซื้อได้ทันที</p>
            </div>

            <div class="row g-4">
                <?php if (!empty($products)): ?>
                <?php foreach ($products as $row): ?>
                <?php
                        $imgFile = '';

                        $stmtImg = $conn->prepare("SELECT img FROM tbl_img_detail WHERE p_id = ? ORDER BY id DESC LIMIT 1");
                        $stmtImg->execute([$row['p_id']]);
                        $detailImg = $stmtImg->fetchColumn();

                        if (!empty($detailImg)) {
                            $imgFile = trim($detailImg);
                        } elseif (!empty($row['img'])) {
                            $imgFile = trim($row['img']);
                        }

                        $imgPath = 'admin/p_gallery/no-image.png';
                        if ($imgFile != '' && file_exists(__DIR__ . '/admin/p_gallery/' . $imgFile)) {
                            $imgPath = 'admin/p_gallery/' . $imgFile;
                        }

                        $shortDetail = mb_substr($row['p_detail'] ?? '', 0, 90);
                        $currentStock = (int)($row['p_stock'] ?? 0);
                        $isOutOfStock = $currentStock <= 0;
                        ?>

                <div class="col-lg-3 col-md-4 col-sm-6">
                    <div class="card h-100 border-0 product-card shadow-sm">

                        <div class="product-img-wrap position-relative">
                            <img src="<?= htmlspecialchars($imgPath) ?>" class="card-img-top product-img"
                                alt="<?= htmlspecialchars($row['p_name']) ?>" loading="lazy"
                                onerror="this.onerror=null;this.src='admin/p_gallery/no-image.png';">

                            <span class="badge product-type-badge">
                                <?= htmlspecialchars($row['type_name'] ?? '-') ?>
                            </span>
                        </div>

                        <div class="card-body d-flex flex-column p-3">
                            <h5 class="card-title product-title mb-2">
                                <?= htmlspecialchars($row['p_name']) ?>
                            </h5>

                            <div class="product-price mb-2">
                                <?= number_format((float)$row['p_price'], 2) ?> บาท
                            </div>

                            <div class="small text-muted mb-2">
                                สต็อกคงเหลือ <?= number_format($currentStock) ?> ชิ้น
                            </div>

                            <p class="card-text text-muted product-desc mb-3">
                                <?= htmlspecialchars($shortDetail) ?><?= mb_strlen($row['p_detail'] ?? '') > 90 ? '...' : '' ?>
                            </p>

                            <div class="mt-auto d-grid gap-2">
                                <button type="button" class="btn btn-outline-dark rounded-pill" data-bs-toggle="modal"
                                    data-bs-target="#productModalMain<?= (int)$row['p_id'] ?>">
                                    <i class="fas fa-eye me-2"></i>ดูรายละเอียด
                                </button>

                                <?php if (isset($_SESSION['m_id'])): ?>
                                <form action="add_to_cart.php" method="post" class="m-0">
                                    <input type="hidden" name="p_id" value="<?= (int)$row['p_id'] ?>">
                                    <input type="hidden" name="qty" value="1">
                                    <button type="submit" class="btn btn-success rounded-pill w-100"
                                        <?= $isOutOfStock ? 'disabled' : '' ?>>
                                        <i class="fas fa-shopping-cart me-2"></i>ซื้อเลย
                                    </button>
                                </form>
                                <?php else: ?>
                                <a href="login.php" class="btn btn-danger rounded-pill w-100">
                                    เข้าสู่ระบบเพื่อสั่งซื้อ
                                </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal fade" id="productModalMain<?= (int)$row['p_id'] ?>" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered modal-lg">
                        <div class="modal-content rounded-4 border-0 shadow-lg">
                            <div class="modal-header border-0 pb-0">
                                <button type="button" class="btn-close position-relative" data-bs-dismiss="modal"
                                    aria-label="Close" style="z-index:1060;"></button>
                            </div>

                            <div class="modal-body pt-0 p-4">
                                <div class="row g-4 align-items-start">
                                    <div class="col-md-6">
                                        <div class="bg-light rounded-4 overflow-hidden border">
                                            <img src="<?= htmlspecialchars($imgPath) ?>" class="img-fluid w-100"
                                                style="height:380px; object-fit:cover;"
                                                alt="<?= htmlspecialchars($row['p_name']) ?>"
                                                onerror="this.onerror=null;this.src='admin/p_gallery/no-image.png';">
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <p class="text-muted mb-2"><?= htmlspecialchars($row['type_name'] ?? '-') ?></p>
                                        <h3 class="mb-2"><?= htmlspecialchars($row['p_name']) ?></h3>

                                        <h4 class="text-danger fw-bold mb-3">
                                            <?= number_format((float)$row['p_price'], 2) ?> บาท
                                        </h4>

                                        <div class="mb-3">
                                            <span class="badge bg-success rounded-pill px-3 py-2">
                                                พร้อมขาย
                                            </span>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label fw-bold">รายละเอียดสินค้า</label>
                                            <div class="bg-light rounded-4 p-3 text-muted" style="line-height:1.8;">
                                                <?= nl2br(htmlspecialchars($row['p_detail'] ?? '-')) ?>
                                            </div>
                                        </div>

                                        <?php if (isset($_SESSION['m_id'])): ?>
                                        <form action="add_to_cart.php" method="post">
                                            <input type="hidden" name="p_id" value="<?= (int)$row['p_id'] ?>">

                                            <div class="mb-3">
                                                <label class="form-label fw-bold">จำนวน</label>
                                                <div class="d-flex align-items-center gap-2">
                                                    <button type="button"
                                                        class="btn btn-outline-secondary rounded-circle qty-btn"
                                                        onclick="changeQtyGenericSaleMain(<?= (int)$row['p_id'] ?>, -1)"
                                                        <?= $isOutOfStock ? 'disabled' : '' ?>>
                                                        -
                                                    </button>

                                                    <input type="number" name="qty"
                                                        id="qtySaleMain<?= (int)$row['p_id'] ?>" value="1" min="1"
                                                        class="form-control text-center qty-input" style="width:90px;"
                                                        <?= $isOutOfStock ? 'disabled' : '' ?>
                                                        <?= $currentStock > 0 ? 'max="' . $currentStock . '"' : '' ?>>

                                                    <button type="button"
                                                        class="btn btn-outline-secondary rounded-circle qty-btn"
                                                        onclick="changeQtyGenericSaleMain(<?= (int)$row['p_id'] ?>, 1)"
                                                        <?= $isOutOfStock ? 'disabled' : '' ?>>
                                                        +
                                                    </button>
                                                </div>
                                            </div>

                                            <div class="mb-3 stock-block">
                                                <small class="text-muted">สต็อกคงเหลือ</small>
                                                <div class="fw-bold mb-1"><?= number_format($currentStock) ?> ชิ้น</div>
                                                <?php if ($isOutOfStock): ?>
                                                <small class="text-danger d-block">สินค้าหมด
                                                    ไม่สามารถสั่งซื้อได้</small>
                                                <?php endif; ?>
                                            </div>

                                            <div class="mb-3">
                                                <small class="text-muted">ราคารวม</small>
                                                <div class="fw-bold text-danger fs-5"
                                                    id="totalPriceMain<?= (int)$row['p_id'] ?>"
                                                    data-price="<?= (float)$row['p_price'] ?>">
                                                    <?= number_format((float)$row['p_price'], 2) ?> บาท
                                                </div>
                                            </div>

                                            <?php if ($isOutOfStock): ?>
                                            <div class="mb-3">
                                                <small class="text-danger">สินค้าหมด ไม่สามารถสั่งซื้อได้</small>
                                            </div>
                                            <?php endif; ?>

                                            <div class="d-grid">
                                                <button type="submit" class="btn btn-success rounded-pill w-100 py-2"
                                                    <?= $isOutOfStock ? 'disabled' : '' ?>>
                                                    <i class="fas fa-shopping-cart me-2"></i>เพิ่มเข้าตะกร้า
                                                </button>
                                            </div>
                                        </form>
                                        <?php else: ?>
                                        <div class="d-grid gap-2">
                                            <a href="login.php" class="btn btn-danger rounded-pill py-2">
                                                เข้าสู่ระบบเพื่อสั่งซื้อ
                                            </a>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php else: ?>
                <div class="col-12">
                    <div class="alert alert-warning rounded-4 shadow-sm border-0">
                        ไม่พบสินค้าพร้อมขาย
                    </div>
                </div>
                <?php endif; ?>
            </div>

        </div>

        <?php require_once("footer.php"); ?>
    </div>

    <a href="cart.php" class="btn btn-danger rounded-circle shadow floating-cart-btn">
        <div class="position-relative">
            <i class="fas fa-shopping-cart text-white"></i>
            <?php if ($cartCount > 0): ?>
            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-warning text-dark">
                <?= $cartCount ?>
            </span>
            <?php endif; ?>
        </div>
    </a>

    <style>
    .shop-topbar,
    .section-head {
        background: #fff;
        border-radius: 20px;
        padding: 20px 24px;
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.06);
    }

    .shop-topbar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 16px;
        flex-wrap: wrap;
    }

    .shop-topbar-right {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }

    .action-btn {
        border-radius: 999px;
        padding: 10px 18px;
        font-weight: 600;
        box-shadow: 0 4px 14px rgba(0, 0, 0, 0.08);
    }

    .product-card,
    .best-card,
    .promo-card {
        border-radius: 22px;
        overflow: hidden;
        transition: all 0.25s ease;
        background: #fff;
    }

    .product-card:hover,
    .best-card:hover,
    .promo-card:hover {
        transform: translateY(-6px);
        box-shadow: 0 14px 30px rgba(0, 0, 0, 0.12) !important;
    }

    .product-img-wrap {
        overflow: hidden;
        background: #f8f9fa;
    }

    .product-img {
        height: 230px;
        width: 100%;
        object-fit: cover;
        transition: transform 0.35s ease;
    }

    .product-card:hover .product-img,
    .best-card:hover .product-img {
        transform: scale(1.04);
    }

    .product-type-badge {
        position: absolute;
        top: 12px;
        left: 12px;
        background: rgba(255, 255, 255, 0.92);
        color: #333;
        border-radius: 999px;
        padding: 8px 14px;
        font-weight: 600;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.08);
    }

    .best-rank-badge {
        position: absolute;
        top: 12px;
        left: 12px;
        background: linear-gradient(135deg, #ffcc00, #ff9800);
        color: #222;
        border-radius: 999px;
        padding: 8px 14px;
        font-weight: 700;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.12);
    }

    .promo-top {
        background: linear-gradient(135deg, #ff7a18, #ff3d00);
        padding: 24px 22px;
        position: relative;
    }

    .promo-badge {
        display: inline-block;
        padding: 6px 12px;
        border-radius: 999px;
        background: rgba(255, 255, 255, 0.2);
        font-size: 12px;
        font-weight: 700;
        letter-spacing: 0.5px;
        margin-bottom: 12px;
    }

    .promo-title {
        font-size: 1.15rem;
        font-weight: 800;
        line-height: 1.5;
        min-height: 52px;
    }

    .promo-value {
        font-size: 2rem;
        font-weight: 800;
        line-height: 1.2;
    }

    .promo-minimum {
        font-weight: 700;
        color: #dc3545;
    }

    .promo-apply {
        color: #495057;
        font-size: 0.95rem;
        font-weight: 600;
    }

    .promo-detail {
        min-height: 72px;
        line-height: 1.7;
    }

    .promo-date {
        background: #f8f9fa;
        border-radius: 999px;
        padding: 10px 14px;
        font-size: 0.92rem;
        color: #495057;
        font-weight: 600;
        text-align: center;
    }

    .product-title {
        min-height: 52px;
        font-size: 1.05rem;
        font-weight: 700;
        line-height: 1.5;
    }

    .product-price {
        color: #dc3545;
        font-size: 1.25rem;
        font-weight: 800;
    }

    .product-desc {
        min-height: 68px;
        line-height: 1.7;
    }

    .qty-btn {
        width: 42px;
        height: 42px;
        font-size: 20px;
        line-height: 1;
    }

    .floating-cart-btn {
        position: fixed;
        right: 20px;
        bottom: 20px;
        width: 62px;
        height: 62px;
        z-index: 9999;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    @media (max-width: 768px) {

        .shop-topbar,
        .section-head {
            padding: 16px;
        }

        .shop-topbar-right {
            width: 100%;
        }

        .shop-topbar-right .btn {
            flex: 1 1 calc(50% - 8px);
            text-align: center;
        }

        .product-img {
            height: 210px;
        }

        .promo-title {
            min-height: auto;
        }

        .promo-detail {
            min-height: auto;
        }
    }
    </style>

    <script>
    function changeQtyGenericSale(id, amount) {
        const input = document.getElementById('qtySale' + id);
        const totalText = document.getElementById('totalPrice' + id);
        if (!input || !totalText) return;

        let current = parseInt(input.value) || 1;
        current += amount;
        if (current < 1) current = 1;
        const max = parseInt(input.max) || Infinity;
        if (current > max) current = max;
        input.value = current;

        const price = parseFloat(totalText.dataset.price || 0);
        totalText.innerText = (price * current).toLocaleString('th-TH', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        }) + ' บาท';
    }

    function changeQtyGenericSaleMain(id, amount) {
        const input = document.getElementById('qtySaleMain' + id);
        const totalText = document.getElementById('totalPriceMain' + id);
        if (!input || !totalText) return;

        let current = parseInt(input.value) || 1;
        current += amount;
        if (current < 1) current = 1;
        const max = parseInt(input.max) || Infinity;
        if (current > max) current = max;
        input.value = current;

        const price = parseFloat(totalText.dataset.price || 0);
        totalText.innerText = (price * current).toLocaleString('th-TH', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        }) + ' บาท';
    }

    document.addEventListener('DOMContentLoaded', function() {
        <?php foreach ($products as $row): ?>
            (function() {
                const input = document.getElementById('qtySaleMain<?= (int)$row['p_id'] ?>');
                const total = document.getElementById('totalPriceMain<?= (int)$row['p_id'] ?>');

                if (input && total) {
                    input.addEventListener('input', function() {
                        let val = parseInt(this.value) || 1;
                        const max = parseInt(this.max) || Infinity;
                        if (val < 1) val = 1;
                        if (val > max) val = max;
                        this.value = val;

                        const price = parseFloat(total.dataset.price || 0);
                        total.innerText = (price * val).toLocaleString('th-TH', {
                            minimumFractionDigits: 2,
                            maximumFractionDigits: 2
                        }) + ' บาท';
                    });
                }
            })();
        <?php endforeach; ?>

        <?php foreach ($bestProducts as $best): ?>
            (function() {
                const input = document.getElementById('qtySale<?= (int)$best['p_id'] ?>');
                const total = document.getElementById('totalPrice<?= (int)$best['p_id'] ?>');

                if (input && total) {
                    input.addEventListener('input', function() {
                        let val = parseInt(this.value) || 1;
                        const max = parseInt(this.max) || Infinity;
                        if (val < 1) val = 1;
                        if (val > max) val = max;
                        this.value = val;

                        const price = parseFloat(total.dataset.price || 0);
                        total.innerText = (price * val).toLocaleString('th-TH', {
                            minimumFractionDigits: 2,
                            maximumFractionDigits: 2
                        }) + ' บาท';
                    });
                }
            })();
        <?php endforeach; ?>
    });
    </script>

</body>

</html>