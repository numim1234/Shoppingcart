<?php
session_start();
require_once("condb.php");
require_once("head.php");

if (!isset($_SESSION['m_id'])) {
    header('Location: login.php');
    exit;
}

$m_id = (int)$_SESSION['m_id'];
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// validate ownership
$stmtCheck = $conn->prepare("SELECT * FROM tbl_reservation WHERE reserve_id = ? AND m_id = ?");
$stmtCheck->execute([$id, $m_id]);
$reserve = $stmtCheck->fetch(PDO::FETCH_ASSOC);

if (!$reserve) {
    http_response_code(404);
    echo "<p class=\"p-4\">ไม่พบคำสั่งซื้อหรือคุณไม่มีสิทธิ์ดูรายการนี้</p>";
    exit;
}

$stmtItems = $conn->prepare("SELECT rd.*, p.p_name, p.img, p.p_price FROM tbl_reservation_detail rd JOIN tbl_product p ON p.p_id = rd.p_id WHERE rd.reserve_id = ?");
$stmtItems->execute([$id]);
$items = $stmtItems->fetchAll(PDO::FETCH_ASSOC);

$total = 0;
foreach ($items as $it) {
    $price = isset($it['p_price']) ? (float)$it['p_price'] : (float)($it['price'] ?? 0);
    $qty = (int)($it['qty'] ?? 0);
    $total += $price * $qty;
}
?>

<body>
    <div class="main-wrapper innerpagebg">
        <?php require_once('header.php'); ?>

        <div class="container py-5">
            <div class="section-head mb-4">
                <h3 class="mb-1">รายละเอียดคำสั่งซื้อ #<?= htmlspecialchars($reserve['reserve_id']) ?></h3>
                <p class="text-muted mb-0">วันที่
                    <?= htmlspecialchars($reserve['created_at'] ?? $reserve['created'] ?? '-') ?></p>
            </div>

            <div class="card mb-4">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-2"><strong>สถานะการชำระ:</strong>
                                <?= htmlspecialchars($reserve['payment_status'] ?? '-') ?></div>
                            <div class="mb-2"><strong>สถานะการจอง:</strong>
                                <?= htmlspecialchars($reserve['reserve_status'] ?? '-') ?></div>
                        </div>

                        <div class="col-md-6 text-end">
                            <div class="mb-2"><strong>ยอดรวม:</strong> <span
                                    class="text-danger"><?= number_format($total, 2) ?> บาท</span></div>
                        </div>
                    </div>

                    <hr>

                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>สินค้า</th>
                                    <th class="text-center">ราคา/ชิ้น</th>
                                    <th class="text-center">จำนวน</th>
                                    <th class="text-end">รวม</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($items as $it): ?>
                                    <?php
                                    $name = htmlspecialchars($it['p_name'] ?? '-');
                                    $price = isset($it['p_price']) ? (float)$it['p_price'] : (float)($it['price'] ?? 0);
                                    $qty = (int)($it['qty'] ?? 0);
                                    $line = $price * $qty;
                                    ?>
                                    <tr>
                                        <td>
                                            <?php
                                            $rawImg = $it['img'] ?? '';
                                            $imgPath = 'admin/p_gallery/no-image.png';

                                            // Helper to test candidate path (relative to project root)
                                            $tryFile = function ($candidate) {
                                                $full = __DIR__ . '/' . $candidate;
                                                return is_file($full) ? $candidate : false;
                                            };

                                            // 1) Direct path stored in img field
                                            if ($rawImg !== '') {
                                                if ($tryFile($rawImg)) {
                                                    $imgPath = $rawImg;
                                                }
                                            }

                                            // 2) admin/p_gallery/<file>
                                            if ($imgPath === 'admin/p_gallery/no-image.png' && $rawImg !== '') {
                                                $cand = $tryFile('admin/p_gallery/' . $rawImg);
                                                if ($cand) $imgPath = $cand;
                                            }

                                            // 3) uploads/<file>
                                            if ($imgPath === 'admin/p_gallery/no-image.png' && $rawImg !== '') {
                                                $cand = $tryFile('uploads/' . $rawImg);
                                                if ($cand) $imgPath = $cand;
                                            }

                                            // 4) If still not found, try tbl_img_detail for this product id
                                            if ($imgPath === 'admin/p_gallery/no-image.png' && !empty($it['p_id'])) {
                                                $stmtImg = $conn->prepare("SELECT img FROM tbl_img_detail WHERE p_id = ? ORDER BY id DESC LIMIT 1");
                                                $stmtImg->execute([(int)$it['p_id']]);
                                                $rowImg = $stmtImg->fetchColumn();
                                                if ($rowImg) {
                                                    $rowImg = trim($rowImg);
                                                    if ($tryFile('admin/p_gallery/' . $rowImg)) {
                                                        $imgPath = 'admin/p_gallery/' . $rowImg;
                                                    } elseif ($tryFile('uploads/' . $rowImg)) {
                                                        $imgPath = 'uploads/' . $rowImg;
                                                    } elseif ($tryFile($rowImg)) {
                                                        $imgPath = $rowImg;
                                                    }
                                                }
                                            }
                                            ?>
                                            <div class="d-flex align-items-center gap-2">
                                                <img src="<?= htmlspecialchars($imgPath) ?>" alt=""
                                                    style="width:56px;height:56px;object-fit:cover;border-radius:8px;"
                                                    onerror="this.onerror=null;this.src='admin/p_gallery/no-image.png';">
                                                <div>
                                                    <div class="fw-bold"><?= $name ?></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-center"><?= number_format($price, 2) ?> บาท</td>
                                        <td class="text-center"><?= $qty ?></td>
                                        <td class="text-end text-danger"><?= number_format($line, 2) ?> บาท</td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="text-end mt-3">
                        <a href="order_history.php" class="btn btn-outline-secondary">กลับไปยังประวัติการสั่งซื้อ</a>
                    </div>
                </div>
            </div>

        </div>

        <?php require_once('footer.php'); ?>
    </div>
</body>

</html>