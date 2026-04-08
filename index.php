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
        // ไม่นับสินค้าจองในตะกร้าซื้อปกติ
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
// ดึงสินค้าขายดี 3 อันดับแรก
// =========================
// หมายเหตุ:
// โค้ดนี้อิงจากการขายใน tbl_reservation_detail
// ถ้าระบบของคุณใช้ตารางอื่น ให้เปลี่ยนชื่อ table/field ให้ตรงจริง
$bestProducts = [];

try {
    $stmtBest = $conn->prepare("
    SELECT 
        p.p_id,
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
    GROUP BY p.p_id, p.p_name, p.p_price, p.p_detail, p.img, p.type_id, t.type_name
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

                            <p class="card-text text-muted product-desc mb-3">
                                <?= htmlspecialchars($shortDetail) ?><?= mb_strlen($row['p_detail'] ?? '') > 90 ? '...' : '' ?>
                            </p>

                            <div class="mt-auto d-grid gap-2">
                                <button type="button" class="btn btn-outline-dark rounded-pill" data-bs-toggle="modal"
                                    data-bs-target="#productModal<?= (int)$row['p_id'] ?>">
                                    <i class="fas fa-eye me-2"></i>ดูรายละเอียด
                                </button>

                                <?php if (isset($_SESSION['m_id'])): ?>
                                <form action="add_to_cart.php" method="post" class="m-0">
                                    <input type="hidden" name="p_id" value="<?= (int)$row['p_id'] ?>">
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

                <div class="modal fade" id="productModal<?= (int)$row['p_id'] ?>" tabindex="-1" aria-hidden="true">
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
                                                        onclick="changeQtyGenericSale(<?= (int)$row['p_id'] ?>, -1)">
                                                        -
                                                    </button>

                                                    <input type="number" name="qty" id="qtySale<?= (int)$row['p_id'] ?>"
                                                        value="1" min="1" class="form-control text-center"
                                                        style="width:90px;">

                                                    <button type="button"
                                                        class="btn btn-outline-secondary rounded-circle qty-btn"
                                                        onclick="changeQtyGenericSale(<?= (int)$row['p_id'] ?>, 1)">
                                                        +
                                                    </button>
                                                </div>
                                            </div>

                                            <div class="mb-3">
                                                <small class="text-muted">ราคารวม</small>
                                                <div class="fw-bold text-danger fs-5"
                                                    id="totalPrice<?= (int)$row['p_id'] ?>"
                                                    data-price="<?= (float)$row['p_price'] ?>">
                                                    <?= number_format((float)$row['p_price'], 2) ?> บาท
                                                </div>
                                            </div>

                                            <div class="d-grid">
                                                <button type="submit" class="btn btn-success rounded-pill w-100 py-2">
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
    .best-card {
        border-radius: 22px;
        overflow: hidden;
        transition: all 0.25s ease;
        background: #fff;
    }

    .product-card:hover,
    .best-card:hover {
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
                const input = document.getElementById('qtySale<?= (int)$row['p_id'] ?>');
                const total = document.getElementById('totalPrice<?= (int)$row['p_id'] ?>');

                if (input && total) {
                    input.addEventListener('input', function() {
                        let val = parseInt(this.value) || 1;
                        if (val < 1) val = 1;
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