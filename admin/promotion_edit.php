<?php
include_once '../condb.php';

$promo_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($promo_id <= 0) {
    echo '<div class="alert alert-danger">ไม่พบรหัสโปรโมชั่น</div>';
    exit;
}

$stmt = $conn->prepare("
    SELECT *
    FROM tbl_promotion
    WHERE promo_id = ?
    LIMIT 1
");
$stmt->execute([$promo_id]);
$promo = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$promo) {
    echo '<div class="alert alert-danger">ไม่พบข้อมูลโปรโมชั่น</div>';
    exit;
}

$stmtProduct = $conn->prepare("
    SELECT p_id, p_name, p_price, p_qty
    FROM tbl_product
    WHERE p_status = 1
    ORDER BY p_name ASC
");
$stmtProduct->execute();
$products = $stmtProduct->fetchAll(PDO::FETCH_ASSOC);

$stmtSelected = $conn->prepare("
    SELECT p_id
    FROM tbl_promotion_product
    WHERE promo_id = ?
");
$stmtSelected->execute([$promo_id]);
$selectedProducts = $stmtSelected->fetchAll(PDO::FETCH_COLUMN);
$selectedProducts = array_map('intval', $selectedProducts);
?>

<style>
.promo-page-wrap {
    padding: 0;
}

.promo-hero-card {
    background: linear-gradient(135deg, #cfd7ff 0%, #dfe6ff 100%);
    border-radius: 20px;
    border: 1px solid #d9e1fb;
    padding: 22px 24px;
    margin-bottom: 18px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 18px;
    flex-wrap: wrap;
}

.promo-hero-title {
    margin: 0;
    font-size: 30px;
    font-weight: 800;
    color: #253045;
}

.promo-hero-desc {
    margin: 8px 0 0;
    color: #58677d;
    font-size: 14px;
}

.promo-hero-icon {
    width: 72px;
    height: 72px;
    border-radius: 20px;
    background: rgba(255, 255, 255, 0.6);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 30px;
    color: #5c6ad8;
    box-shadow: 0 8px 20px rgba(99, 102, 241, 0.12);
}

.promo-main-card {
    background: #fff;
    border: 1px solid #e9edf5;
    border-radius: 20px;
    box-shadow: 0 10px 24px rgba(15, 23, 42, 0.04);
    overflow: hidden;
}

.promo-card-head {
    padding: 18px 22px;
    border-bottom: 1px solid #eef2f7;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 12px;
    flex-wrap: wrap;
}

.promo-card-title {
    margin: 0;
    font-size: 20px;
    font-weight: 800;
    color: #253045;
}

.promo-card-subtitle {
    margin-top: 4px;
    font-size: 13px;
    color: #96a0b2;
}

.promo-card-body {
    padding: 20px 22px;
}

.promo-label {
    font-weight: 700;
    color: #58677d;
    margin-bottom: 8px;
    display: block;
}

.promo-input,
.promo-textarea,
.promo-select {
    border-radius: 12px !important;
    border: 1px solid #dbe3ef !important;
    min-height: 44px;
    box-shadow: none !important;
}

.promo-input:focus,
.promo-textarea:focus,
.promo-select:focus {
    border-color: #6478ff !important;
    box-shadow: 0 0 0 0.15rem rgba(100, 120, 255, 0.12) !important;
}

.promo-textarea {
    min-height: 120px;
    resize: vertical;
}

.promo-product-wrap {
    background: #f8fbff;
    border: 1px solid #e6edf7;
    border-radius: 18px;
    padding: 18px;
    margin-top: 6px;
}

.promo-section-title {
    font-size: 18px;
    font-weight: 800;
    color: #253045;
    margin: 0;
}

.promo-section-subtitle {
    color: #8b95a7;
    font-size: 13px;
    margin-top: 4px;
}

.product-box {
    background: #fff;
    border: 1px solid #e9edf5;
    border-radius: 16px;
    padding: 14px;
    height: 100%;
    transition: 0.2s ease;
}

.product-box:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 20px rgba(15, 23, 42, 0.05);
}

.product-name {
    font-weight: 700;
    color: #253045;
    margin-bottom: 6px;
}

.product-meta {
    color: #8a94a6;
    font-size: 13px;
    line-height: 1.6;
}

.promo-info-box {
    background: #f8faff;
    border: 1px solid #e5ebf5;
    border-radius: 16px;
    padding: 14px 16px;
    margin-bottom: 18px;
}

.promo-info-label {
    color: #8b95a7;
    font-size: 13px;
    margin-bottom: 4px;
}

.promo-info-value {
    font-size: 22px;
    font-weight: 800;
    color: #253045;
    line-height: 1.1;
}

.promo-btn {
    border-radius: 12px;
    font-weight: 700;
    padding: 10px 16px;
}

.promo-btn-primary {
    background: linear-gradient(135deg, #6478ff, #5865f2);
    border: none;
    color: #fff;
    box-shadow: 0 10px 18px rgba(88, 101, 242, 0.22);
}

.promo-btn-primary:hover {
    color: #fff;
    opacity: 0.96;
}

.promo-btn-success {
    background: linear-gradient(135deg, #35c98f, #20b97a);
    border: none;
    color: #fff;
}

.promo-btn-success:hover {
    color: #fff;
    opacity: 0.96;
}

.promo-btn-light {
    background: #eef2ff;
    color: #5865f2;
    border: 1px solid #dbe2ff;
}

.promo-btn-light:hover {
    background: #e2e8ff;
    color: #4654cf;
}

.promo-btn-secondary {
    background: #f8fafc;
    color: #58677d;
    border: 1px solid #e5eaf1;
}

.promo-btn-secondary:hover {
    background: #eef2f7;
    color: #49566a;
}

.promo-footer {
    padding: 18px 22px;
    border-top: 1px solid #eef2f7;
    background: #fff;
}

@media (max-width: 991px) {
    .promo-hero-title {
        font-size: 24px;
    }
}

@media (max-width: 768px) {

    .promo-hero-card,
    .promo-card-head {
        flex-direction: column;
        align-items: flex-start;
    }
}
</style>

<div class="promo-page-wrap">

    <div class="promo-hero-card">
        <div>
            <h2 class="promo-hero-title">✏️ Edit Promotion</h2>
            <p class="promo-hero-desc">แก้ไขรายละเอียดโปรโมชั่นและปรับสินค้าที่เข้าร่วมรายการ</p>
        </div>
        <div class="promo-hero-icon">
            <i class="fas fa-edit"></i>
        </div>
    </div>

    <div class="promo-main-card">
        <div class="promo-card-head">
            <div>
                <h3 class="promo-card-title">ฟอร์มแก้ไขโปรโมชั่น</h3>
                <div class="promo-card-subtitle">ตรวจสอบข้อมูลให้ถูกต้องก่อนบันทึกการแก้ไข</div>
            </div>
        </div>

        <form action="promotion_edit_db.php" method="post" id="promoEditForm">
            <input type="hidden" name="promo_id" value="<?= (int)$promo['promo_id'] ?>">

            <div class="promo-card-body">
                <div class="promo-info-box">
                    <div class="promo-info-label">รหัสโปรโมชั่น</div>
                    <div class="promo-info-value">#<?= (int)$promo['promo_id'] ?></div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="promo-label">ชื่อโปรโมชั่น</label>
                        <input type="text" name="promo_name" class="form-control promo-input"
                            value="<?= htmlspecialchars($promo['promo_name']) ?>" required>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="promo-label">ประเภทโปรโมชั่น</label>
                        <select name="promo_type" class="form-control promo-select" required>
                            <option value="percent" <?= ($promo['promo_type'] === 'percent') ? 'selected' : '' ?>>
                                เปอร์เซ็นต์ (%)</option>
                            <option value="amount" <?= ($promo['promo_type'] === 'amount') ? 'selected' : '' ?>>
                                จำนวนเงิน (บาท)</option>
                        </select>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="promo-label">มูลค่าส่วนลด</label>
                        <input type="number" name="promo_value" step="0.01" min="0.01" class="form-control promo-input"
                            value="<?= htmlspecialchars($promo['promo_value']) ?>" required>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="promo-label">ยอดขั้นต่ำในการใช้โปร</label>
                        <input type="number" name="min_order" step="0.01" min="0" class="form-control promo-input"
                            value="<?= htmlspecialchars($promo['min_order']) ?>">
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="promo-label">วันที่เริ่มโปรโมชั่น</label>
                        <input type="date" name="start_date" class="form-control promo-input"
                            value="<?= htmlspecialchars($promo['start_date']) ?>" required>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="promo-label">วันที่สิ้นสุดโปรโมชั่น</label>
                        <input type="date" name="end_date" class="form-control promo-input"
                            value="<?= htmlspecialchars($promo['end_date']) ?>" required>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="promo-label">การใช้งานโปรโมชั่น</label>
                        <select name="apply_type" id="apply_type" class="form-control promo-select" required>
                            <option value="product" <?= ($promo['apply_type'] === 'product') ? 'selected' : '' ?>>
                                เฉพาะสินค้าที่เลือก</option>
                            <option value="all" <?= ($promo['apply_type'] === 'all') ? 'selected' : '' ?>>ทั้งร้าน
                            </option>
                        </select>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="promo-label">สถานะ</label>
                        <select name="promo_status" class="form-control promo-select" required>
                            <option value="1" <?= ((int)$promo['promo_status'] === 1) ? 'selected' : '' ?>>เปิดใช้งาน
                            </option>
                            <option value="0" <?= ((int)$promo['promo_status'] === 0) ? 'selected' : '' ?>>ปิดใช้งาน
                            </option>
                        </select>
                    </div>

                    <div class="col-md-12 mb-3">
                        <label class="promo-label">รายละเอียดโปรโมชั่น</label>
                        <textarea name="promo_detail" rows="4"
                            class="form-control promo-textarea"><?= htmlspecialchars($promo['promo_detail']) ?></textarea>
                    </div>
                </div>

                <div id="product-section" class="promo-product-wrap">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                        <div>
                            <h5 class="promo-section-title">เลือกสินค้าที่ใช้โปรโมชั่น</h5>
                            <div class="promo-section-subtitle">เลือกหรือแก้ไขรายการสินค้าที่ร่วมโปรโมชั่นนี้</div>
                        </div>
                        <div class="d-flex gap-2 flex-wrap">
                            <button type="button" class="btn promo-btn promo-btn-primary btn-sm"
                                onclick="toggleAllProducts(true)">
                                <i class="fas fa-check-square me-1"></i> เลือกทั้งหมด
                            </button>
                            <button type="button" class="btn promo-btn promo-btn-secondary btn-sm"
                                onclick="toggleAllProducts(false)">
                                <i class="fas fa-times-circle me-1"></i> ล้างทั้งหมด
                            </button>
                        </div>
                    </div>

                    <div class="row">
                        <?php if (!empty($products)): ?>
                        <?php foreach ($products as $row): ?>
                        <?php $checked = in_array((int)$row['p_id'], $selectedProducts, true); ?>
                        <div class="col-md-4 mb-3">
                            <div class="product-box">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input product-check"
                                        id="product_<?= (int)$row['p_id'] ?>" name="product_ids[]"
                                        value="<?= (int)$row['p_id'] ?>" <?= $checked ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="product_<?= (int)$row['p_id'] ?>">
                                        <div class="product-name"><?= htmlspecialchars($row['p_name']) ?></div>
                                    </label>
                                </div>

                                <div class="product-meta">
                                    ราคา <?= number_format($row['p_price'], 2) ?> บาท<br>
                                    จำนวน <?= (int)$row['p_qty'] ?>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        <?php else: ?>
                        <div class="col-12">
                            <div class="text-center text-muted py-3">ไม่พบข้อมูลสินค้า</div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="promo-footer">
                <button type="submit" class="btn promo-btn promo-btn-success">
                    <i class="fas fa-save me-1"></i> บันทึกการแก้ไข
                </button>
                <a href="promotion.php" class="btn promo-btn promo-btn-light">
                    <i class="fas fa-arrow-left me-1"></i> กลับ
                </a>
            </div>
        </form>
    </div>

</div>

<script>
function toggleApplyType() {
    const applyType = document.getElementById('apply_type').value;
    const productSection = document.getElementById('product-section');
    productSection.style.display = (applyType === 'all') ? 'none' : 'block';
}

function toggleAllProducts(checked) {
    const items = document.querySelectorAll('.product-check');
    items.forEach(function(item) {
        item.checked = checked;
    });
}

document.addEventListener('DOMContentLoaded', function() {
    const applyTypeEl = document.getElementById('apply_type');
    const form = document.getElementById('promoEditForm');

    applyTypeEl.addEventListener('change', toggleApplyType);
    toggleApplyType();

    form.addEventListener('submit', function(e) {
        const applyType = applyTypeEl.value;
        const checkedCount = document.querySelectorAll('.product-check:checked').length;

        if (applyType === 'product' && checkedCount === 0) {
            alert('กรุณาเลือกสินค้าอย่างน้อย 1 รายการ');
            e.preventDefault();
        }
    });
});
</script>