<?php
require_once("condb.php");
require_once("head.php");

$stmt = $conn->prepare("SELECT * FROM tbl_contact ORDER BY contact_id DESC LIMIT 1");
$stmt->execute();
$contact = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<body>
    <div class="main-wrapper innerpagebg">
        <?php require_once("header.php"); ?>

        <div class="container py-5" style="margin-top:100px;">
            <div class="row">
                <div class="col-lg-8 mx-auto">
                    <div class="card shadow border-0 rounded-4">
                        <div class="card-body p-4">
                            <h2 class="mb-4">ติดต่อเรา</h2>

                            <?php if ($contact): ?>
                                <p><strong>ชื่อร้าน:</strong> <?= htmlspecialchars($contact['contact_name']) ?></p>
                                <p><strong>ที่อยู่:</strong> <?= nl2br(htmlspecialchars($contact['contact_address'])) ?></p>
                                <p><strong>เบอร์โทร:</strong> <?= htmlspecialchars($contact['contact_phone']) ?></p>
                                <p><strong>อีเมล:</strong> <?= htmlspecialchars($contact['contact_email']) ?></p>

                                <?php if (!empty($contact['contact_facebook'])): ?>
                                    <p>
                                        <strong>Facebook:</strong>
                                        <a href="<?= htmlspecialchars($contact['contact_facebook']) ?>" target="_blank">
                                            <?= htmlspecialchars($contact['contact_facebook']) ?>
                                        </a>
                                    </p>
                                <?php endif; ?>

                                <?php if (!empty($contact['contact_line'])): ?>
                                    <p><strong>Line:</strong> <?= htmlspecialchars($contact['contact_line']) ?></p>
                                <?php endif; ?>

                                <?php if (!empty($contact['contact_map'])): ?>
                                    <div class="mt-4">
                                        <strong>แผนที่:</strong><br>
                                        <a href="<?= htmlspecialchars($contact['contact_map']) ?>" target="_blank"
                                            class="btn btn-outline-primary mt-2">
                                            เปิดแผนที่
                                        </a>
                                    </div>
                                <?php endif; ?>
                            <?php else: ?>
                                <p class="text-muted">ยังไม่มีข้อมูลติดต่อ</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php require_once("footer.php"); ?>
    </div>
</body>

</html>