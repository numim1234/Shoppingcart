<?php
include '../condb.php';

// ดึงประเภทสินค้า
$stmtType = $conn->prepare("SELECT * FROM tbl_type ORDER BY type_id ASC");
$stmtType->execute();
$resultType = $stmtType->fetchAll();

// ตรวจสอบ p_id
if (isset($_GET['p_id'])) {
    $stmt_pro = $conn->prepare("
        SELECT * FROM tbl_product p
        INNER JOIN tbl_type t ON p.type_id = t.type_id
        WHERE p.p_id=?
    ");
    $stmt_pro->execute([$_GET['p_id']]);
    $row_prod = $stmt_pro->fetch(PDO::FETCH_ASSOC);

    // รูปสินค้า
    $stmt_img = $conn->prepare("SELECT * FROM tbl_img_detail WHERE p_id=?");
    $stmt_img->execute([$_GET['p_id']]);
    $result_img = $stmt_img->fetchAll();

    if (!$row_prod) {
        header("Location: product.php");
        exit();
    }
}
?>

<form id="filepond-form" action="product_edit_db.php" method="post" enctype="multipart/form-data">
    <div class="card-body">
        <div class="row">
            <div class="form-group col-4">
                <label>สินค้า</label>
                <input type="text" name="p_name" value="<?= $row_prod['p_name']; ?>" class="form-control" required>
            </div>

            <div class="form-group col-4">
                <label>ประเภทสินค้า</label>
                <select name="type_id" class="form-control" required>
                    <option value="<?= $row_prod['type_id']; ?>"><?= $row_prod['type_name']; ?></option>
                    <option disabled>เลือกประเภทสินค้า</option>
                    <?php foreach ($resultType as $t) { ?>
                    <option value="<?= $t['type_id']; ?>"><?= $t['type_name']; ?></option>
                    <?php } ?>
                </select>
            </div>
        </div>

        <div class="row">
            <div class="form-group col-8">
                <label>รายละเอียดสินค้า</label>
                <textarea name="p_detail" class="form-control"><?= $row_prod['p_detail']; ?></textarea>
            </div>
        </div>

        <div class="row">
            <div class="form-group col-3">
                <label>ราคา</label>
                <input type="number" name="p_price" value="<?= $row_prod['p_price']; ?>" class="form-control">
            </div>

            <div class="form-group col-2">
                <label>จำนวน</label>
                <input type="text" name="p_qty" value="<?= $row_prod['p_qty']; ?>" class="form-control">
            </div>

            <div class="form-group col-3">
                <label>หน่วย</label>
                <select name="p_unit" class="form-control" required>
                    <option value="<?= $row_prod['p_unit']; ?>"><?= $row_prod['p_unit']; ?></option>
                    <option disabled>เลือกหน่วยสินค้า</option>
                    <option value="กล่อง">กล่อง</option>
                    <option value="ชิ้น">ชิ้น</option>
                    <option value="อัน">อัน</option>
                    <option value="ชุด">ชุด</option>
                    <option value="เล่ม">เล่ม</option>
                </select>
            </div>
        </div>

        <div class="row">
            <div class="form-group col-8">
                <label>File รูปภาพ สินค้า *jpg, png (เลือกเมื่อต้องการเพิ่มรูปใหม่)</label>
                <input type="file" id="fileInputEdit" name="filepond[]" multiple accept="image/jpeg,image/png"
                    class="form-control-file">
                <div id="previewContainerEdit" class="d-flex flex-wrap mt-2"></div>
            </div>
        </div>

        <?php if (!empty($result_img)) { ?>
        <div class="row">
            <div class="col-md-12">
                <h4>รูปภาพปัจจุบัน</h4>
                <div class="d-flex flex-wrap">
                    <?php foreach ($result_img as $img) { ?>
                    <div class="p-2">
                        <img src="p_gallery/<?= $img['img']; ?>" width="120" style="object-fit:cover;">
                    </div>
                    <?php } ?>
                </div>
            </div>
        </div>
        <?php } ?>

        <div class="row">
            <div class="form-group col-4">
                <input type="hidden" name="p_id" value="<?= $row_prod['p_id']; ?>">
                <button type="submit" id="btnSubmit" class="btn btn-success">บันทึกข้อมูล</button>
                <a href="product.php" type="button" class="btn btn-dark">กลับ</a>
            </div>
        </div>
    </div>
</form>