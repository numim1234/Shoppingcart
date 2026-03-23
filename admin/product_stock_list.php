<?php
include '../condb.php';

// Query products with type
$stmt = $conn->prepare("SELECT p.*, t.type_name FROM tbl_product p LEFT JOIN tbl_type t ON p.type_id = t.type_id ORDER BY p.p_id DESC");
$stmt->execute();
$products = $stmt->fetchAll();
?>

<div class="d-flex justify-content-between mb-3">
    <h4>📦 จัดการสต็อกสินค้า</h4>
</div>

<div class="card">
    <div class="card-body">
        <table id="stockTable" class="table table-striped">
            <thead>
                <tr>
                    <th style="width:5%">No</th>
                    <th style="width:10%">รูป</th>
                    <th>ชื่อสินค้า</th>
                    <th style="width:15%">ประเภท</th>
                    <th style="width:12%">จำนวน</th>
                    <th style="width:12%">หน่วย</th>
                    <th style="width:12%">จัดการ</th>
                </tr>
            </thead>
            <tbody>
                <?php $i = 1;
                foreach ($products as $row) {
                    $stmt_img = $conn->prepare("SELECT img FROM tbl_img_detail WHERE p_id=? ORDER BY id DESC LIMIT 1");
                    $stmt_img->execute([$row['p_id']]);
                    $img = $stmt_img->fetchColumn();
                ?>
                    <tr>
                        <td><?php echo $i++; ?></td>
                        <td>
                            <?php if ($img) { ?>
                                <img src="p_gallery/<?php echo $img; ?>" width="60" height="60" style="object-fit:cover;border-radius:6px;">
                            <?php } else {
                                echo '-';
                            } ?>
                        </td>
                        <td><?php echo htmlspecialchars($row['p_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['type_name']); ?></td>
                        <td>
                            <div class="input-group">
                                <input type="number" class="form-control qty-input" min="0" value="<?php echo intval($row['p_qty']); ?>" data-pid="<?php echo $row['p_id']; ?>">
                            </div>
                        </td>
                        <td><?php echo htmlspecialchars($row['p_unit']); ?></td>
                        <td>
                            <button class="btn btn-success btn-sm save-qty" data-pid="<?php echo $row['p_id']; ?>">บันทึก</button>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>

<script>
    $(function() {
        $('#stockTable').DataTable();

        $('.save-qty').on('click', function() {
            var p_id = $(this).data('pid');
            var qty = $('input.qty-input[data-pid="' + p_id + '"]').val();
            $.post('product_stock_update.php', {
                p_id: p_id,
                p_qty: qty
            }, function(res) {
                if (res && res.status) {
                    Swal.fire({
                        icon: 'success',
                        title: 'อัปเดตแล้ว',
                        timer: 800,
                        showConfirmButton: false
                    }).then(() => location.reload());
                } else {
                    Swal.fire('ผิดพลาด', 'ไม่สามารถอัปเดตได้', 'error');
                }
            }, 'json').fail(function() {
                Swal.fire('ผิดพลาด', 'ไม่สามารถติดต่อเซิร์ฟเวอร์ได้', 'error');
            });
        });
    });
</script>

<?php $conn = null; ?>