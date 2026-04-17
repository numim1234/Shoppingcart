<?php
session_start();
require_once("condb.php");
require_once("head.php");

if (!isset($_SESSION['m_id'])) {
    header('Location: login.php');
    exit;
}

$m_id = (int)$_SESSION['m_id'];

$stmt = $conn->prepare("SELECT * FROM tbl_reservation WHERE m_id = ? ORDER BY created_at DESC");
$stmt->execute([$m_id]);
$reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<body>
    <div class="main-wrapper innerpagebg">
        <?php require_once('header.php'); ?>

        <div class="container py-5">
            <style>
            .page-header-card {
                background: #fff;
                border: 1px solid #eef2f6;
                border-radius: 12px;
                padding: 20px;
                margin-top: 18px;
                box-shadow: 0 6px 18px rgba(15, 23, 42, 0.04);
            }

            .page-header-card .section-head {
                margin: 0;
            }
            </style>
            <div class="page-header-card mb-4">
                <div class="section-head">
                    <h3 class="mb-1">ประวัติการสั่งซื้อ</h3>
                    <p class="text-muted mb-0">รายการสั่งซื้อที่ผ่านมา</p>
                </div>
            </div>

            <div id="order-list">
                <!-- reservations will be loaded here via AJAX for real-time updates -->
                <div class="text-center text-muted py-4">กำลังโหลดรายการ...</div>
            </div>

            <script>
            (function() {
                const load = () => {
                    fetch('order_history_list.php', {
                            credentials: 'same-origin'
                        })
                        .then(r => {
                            if (!r.ok) throw new Error(r.statusText);
                            return r.text();
                        })
                        .then(html => {
                            document.getElementById('order-list').innerHTML = html;
                        })
                        .catch(err => {
                            document.getElementById('order-list').innerHTML =
                                '<div class="alert alert-warning">เกิดข้อผิดพลาดในการโหลด</div>';
                            console.error('load orders error', err);
                        });
                };

                // initial load
                load();
                // poll every 5 seconds
                setInterval(load, 5000);
            })();
            </script>

        </div>

        <?php require_once('footer.php'); ?>
    </div>
</body>

</html>