<?php
include_once '../condb.php';

$stmt = $conn->prepare("
    SELECT p.*,
           COUNT(pp.p_id) AS total_products
    FROM tbl_promotion p
    LEFT JOIN tbl_promotion_product pp ON p.promo_id = pp.promo_id
    GROUP BY p.promo_id
    ORDER BY p.promo_id DESC
");
$stmt->execute();
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

$today = date('Y-m-d');

$totalPromotions = count($rows);
$totalActive = count(array_filter($rows, function ($r) use ($today) {
  return (int)$r['promo_status'] === 1 && $r['end_date'] >= $today;
}));
$totalExpired = count(array_filter($rows, function ($r) use ($today) {
  return $r['end_date'] < $today;
}));
$totalDisabled = count(array_filter($rows, function ($r) {
  return (int)$r['promo_status'] === 0;
}));
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

.promo-stats-row {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 16px;
    margin-bottom: 20px;
}

.promo-stat-card {
    background: #fff;
    border: 1px solid #e9edf5;
    border-radius: 18px;
    box-shadow: 0 10px 24px rgba(15, 23, 42, 0.04);
    padding: 18px 20px;
    display: flex;
    align-items: center;
    gap: 14px;
}

.promo-stat-icon {
    width: 54px;
    height: 54px;
    border-radius: 16px;
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 22px;
    flex-shrink: 0;
    box-shadow: 0 10px 18px rgba(88, 101, 242, 0.20);
}

.promo-stat-label {
    color: #8b95a7;
    font-size: 13px;
    margin-bottom: 5px;
}

.promo-stat-value {
    font-size: 28px;
    font-weight: 800;
    color: #253045;
    line-height: 1.1;
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

.promo-table {
    margin-bottom: 0;
}

.promo-table thead th {
    background: #f8faff !important;
    color: #8d96a8 !important;
    font-size: 13px;
    font-weight: 700;
    border-bottom: 1px solid #edf1f7 !important;
    text-align: center;
    vertical-align: middle;
    white-space: nowrap;
    padding: 14px 12px !important;
}

.promo-table tbody td {
    padding: 14px 12px !important;
    vertical-align: middle;
    border-bottom: 1px solid #f0f3f8;
    color: #364152;
    background: #fff;
}

.promo-table tbody tr:hover td {
    background: #fafcff !important;
}

.promo-name {
    font-weight: 800;
    color: #253045;
    margin-bottom: 4px;
}

.promo-detail {
    font-size: 13px;
    color: #8a94a6;
    line-height: 1.6;
}

.promo-type-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 96px;
    padding: 8px 14px;
    border-radius: 999px;
    font-size: 12px;
    font-weight: 700;
    background: #eef2ff;
    color: #5865f2;
    border: 1px solid #dbe2ff;
}

.promo-apply-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 92px;
    padding: 8px 14px;
    border-radius: 999px;
    font-size: 12px;
    font-weight: 700;
    background: #f8fafc;
    color: #58677d;
    border: 1px solid #e5eaf1;
}

.promo-amount {
    font-weight: 800;
    color: #253045;
    white-space: nowrap;
}

.promo-date {
    font-size: 13px;
    line-height: 1.6;
    color: #58677d;
}

.promo-status-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    padding: 8px 14px;
    border-radius: 999px;
    font-size: 12px;
    font-weight: 700;
    border: 1px solid transparent;
    min-width: 100px;
}

.promo-status-success {
    background: #e8fff3;
    color: #1f9d63;
    border-color: #c8f2dd;
}

.promo-status-secondary {
    background: #f1f3f7;
    color: #7c8798;
    border-color: #e1e6ee;
}

.promo-status-danger {
    background: #ffe9ec;
    color: #d8485c;
    border-color: #ffd2d9;
}

.promo-action-group {
    display: flex;
    justify-content: center;
    gap: 8px;
}

.promo-btn-action {
    width: 38px;
    height: 38px;
    border: none;
    border-radius: 12px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
    transition: 0.2s ease;
    text-decoration: none;
}

.promo-btn-edit {
    background: #fff4d8;
    color: #9a6a2f;
}

.promo-btn-edit:hover {
    background: #ffe7a3;
    color: #7f551f;
}

.promo-btn-delete {
    background: #ffe2e5;
    color: #d8485c;
}

.promo-btn-delete:hover {
    background: #ffc7ce;
    color: #bd3045;
}

.promo-empty-cover {
    padding: 32px 24px;
    text-align: center;
    color: #96a0b2;
}

@media (max-width: 991px) {
    .promo-stats-row {
        grid-template-columns: 1fr;
    }

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
            <h2 class="promo-hero-title">🎁 Promotion Management</h2>
            <p class="promo-hero-desc">จัดการโปรโมชั่นทั้งหมดของร้านในรูปแบบที่อ่านง่ายและดูเป็นระบบ</p>
        </div>
        <div class="promo-hero-icon">
            <i class="fas fa-tags"></i>
        </div>
    </div>

    <div class="promo-stats-row">
        <div class="promo-stat-card">
            <div class="promo-stat-icon" style="background: linear-gradient(135deg,#6478ff,#5865f2);">
                <i class="fas fa-ticket-alt"></i>
            </div>
            <div>
                <div class="promo-stat-label">จำนวนโปรโมชั่นทั้งหมด</div>
                <div class="promo-stat-value"><?= number_format($totalPromotions) ?></div>
            </div>
        </div>

        <div class="promo-stat-card">
            <div class="promo-stat-icon" style="background: linear-gradient(135deg,#35c98f,#20b97a);">
                <i class="fas fa-check-circle"></i>
            </div>
            <div>
                <div class="promo-stat-label">โปรโมชั่นที่เปิดใช้งาน</div>
                <div class="promo-stat-value"><?= number_format($totalActive) ?></div>
            </div>
        </div>

        <div class="promo-stat-card">
            <div class="promo-stat-icon" style="background: linear-gradient(135deg,#f4b267,#e59a44);">
                <i class="fas fa-clock"></i>
            </div>
            <div>
                <div class="promo-stat-label">โปรโมชั่นที่หมดอายุ</div>
                <div class="promo-stat-value"><?= number_format($totalExpired) ?></div>
            </div>
        </div>

        <div class="promo-stat-card">
            <div class="promo-stat-icon" style="background: linear-gradient(135deg,#ff6b81,#f5556d);">
                <i class="fas fa-ban"></i>
            </div>
            <div>
                <div class="promo-stat-label">โปรโมชั่นที่ปิดใช้งาน</div>
                <div class="promo-stat-value"><?= number_format($totalDisabled) ?></div>
            </div>
        </div>
    </div>

    <div class="promo-main-card">
        <div class="promo-card-head">
            <div>
                <h3 class="promo-card-title">รายการโปรโมชั่น</h3>
                <div class="promo-card-subtitle">แสดงรายละเอียดส่วนลด เงื่อนไข วันที่ใช้งาน และการจัดการแต่ละโปรโมชั่น
                </div>
            </div>
        </div>

        <div class="promo-card-body">
            <?php if (empty($rows)): ?>
            <div class="promo-empty-cover">
                ยังไม่มีโปรโมชั่นในระบบ
            </div>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table promo-table align-middle w-100">
                    <thead>
                        <tr>
                            <th width="70">No.</th>
                            <th>ชื่อโปรโมชั่น</th>
                            <th width="120">ประเภท</th>
                            <th width="140">ส่วนลด</th>
                            <th width="130">ขั้นต่ำ</th>
                            <th width="140">ใช้กับ</th>
                            <th width="170">วันที่โปรโมชั่น</th>
                            <th width="130">สถานะ</th>
                            <th width="120">จัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rows as $i => $row): ?>
                        <?php
                $statusText = 'เปิดใช้งาน';
                $statusClass = 'success';
                $statusIcon = 'fa-check-circle';

                if ((int)$row['promo_status'] === 0) {
                  $statusText = 'ปิดใช้งาน';
                  $statusClass = 'secondary';
                  $statusIcon = 'fa-ban';
                } elseif ($row['end_date'] < $today) {
                  $statusText = 'หมดอายุ';
                  $statusClass = 'danger';
                  $statusIcon = 'fa-clock';
                }

                $promoType = ($row['promo_type'] === 'percent') ? 'เปอร์เซ็นต์' : 'จำนวนเงิน';

                $promoValue = ($row['promo_type'] === 'percent')
                  ? number_format($row['promo_value'], 0) . '%'
                  : number_format($row['promo_value'], 2) . ' บาท';

                $applyText = ($row['apply_type'] === 'all')
                  ? 'ทั้งร้าน'
                  : ((int)$row['total_products']) . ' สินค้า';
                ?>
                        <tr>
                            <td class="text-center fw-bold"><?= $i + 1 ?></td>

                            <td>
                                <div class="promo-name"><?= htmlspecialchars($row['promo_name']) ?></div>
                                <div class="promo-detail">
                                    <?= !empty($row['promo_detail']) ? nl2br(htmlspecialchars($row['promo_detail'])) : '-' ?>
                                </div>
                            </td>

                            <td class="text-center">
                                <span class="promo-type-badge"><?= $promoType ?></span>
                            </td>

                            <td class="text-center">
                                <div class="promo-amount"><?= $promoValue ?></div>
                            </td>

                            <td class="text-center">
                                <div class="promo-amount"><?= number_format($row['min_order'], 2) ?> บาท</div>
                            </td>

                            <td class="text-center">
                                <span class="promo-apply-badge"><?= $applyText ?></span>
                            </td>

                            <td class="text-center">
                                <div class="promo-date">
                                    <?= date('d/m/Y', strtotime($row['start_date'])) ?><br>
                                    <span class="text-muted">ถึง
                                        <?= date('d/m/Y', strtotime($row['end_date'])) ?></span>
                                </div>
                            </td>

                            <td class="text-center">
                                <span class="promo-status-badge promo-status-<?= $statusClass ?>">
                                    <i class="fas <?= $statusIcon ?>"></i>
                                    <?= $statusText ?>
                                </span>
                            </td>

                            <td class="text-center">
                                <div class="promo-action-group">
                                    <a href="promotion.php?act=edit&id=<?= (int)$row['promo_id'] ?>"
                                        class="promo-btn-action promo-btn-edit" title="แก้ไข">
                                        <i class="fas fa-pen"></i>
                                    </a>

                                    <a href="promotion_del.php?id=<?= (int)$row['promo_id'] ?>"
                                        class="promo-btn-action promo-btn-delete" title="ลบ"
                                        onclick="return confirm('ยืนยันการลบโปรโมชั่นนี้หรือไม่?');">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </div>

</div>