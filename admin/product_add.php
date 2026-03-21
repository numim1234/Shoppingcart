<?php
// คิวรี่ข้อมูลมาแสดงในตาราง
include '../condb.php';
$stmtType = $conn->prepare("
    SELECT * FROM tbl_type
    ORDER BY type_id ASC #เรียงลำดับข้อมูลจากน้อยไปมาก
");
$stmtType->execute();
$resultType = $stmtType->fetchAll();
?>

<form id="filepond-form" action="product_add_db.php" method="post" enctype="multipart/form-data">
    <div class="card-body">
        <div class="row">
            <div class="form-group col-4">
                <label for="exampleInputEmail1">สินค้า</label>
                <input type="text" name="p_name" class="form-control" id="exampleInputEmail1" placeholder="ชื่อสินค้า"
                    required>
            </div>
            <div class="form-group col-4">
                <label for="exampleInputPassword1">ประเภทสินค้า</label>
                <select id="productType" name="type_id" class="form-control" required>
                    <option disabled selected>เลือกประเภทสินค้า</option>
                    <?php foreach ($resultType as $row_type) { ?>
                    <option value="<?php echo $row_type['type_id']; ?>"><?php echo $row_type['type_name']; ?></option>
                    <?php } ?>
                </select>
            </div>
        </div>

        <div class="row">
            <div class="form-group col-8">
                <label for="exampleInputEmail1">รายละเอียดสินค้า</label>
                <textarea name="p_detail" id="p_detail" class="form-control" placeholder="รายละเอียดสินค้า"
                    required></textarea>
            </div>
        </div>

        <div class="row">
            <div class="form-group col-3">
                <label for="exampleInputPassword1">ราคา</label>
                <input type="number" name="p_price" class="form-control" id="exampleInputPassword1">
            </div>
            <div class="form-group col-2">
                <label for="exampleInputEmail1">จำนวน</label>
                <input type="text" name="p_qty" class="form-control" id="exampleInputEmail1">
            </div>
            <div class="form-group col-3">
                <label for="exampleInputEmail1">หน่วย</label>
                <select name="p_unit" class="form-control" required>
                    <option disabled selected>เลือกหน่วยสินค้า</option>
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
                <label>File รูปภาพ สินค้า *jpg, png</label>

                <div class="input-group">
                    <div class="custom-file">
                        <input type="file" name="filepond[]" id="fileInput" class="custom-file-input" multiple
                            accept="image/jpeg,image/png" onchange="previewImages(event)">
                        <label class="custom-file-label" for="fileInput">เลือกไฟล์</label>
                    </div>

                    <div class="input-group-append">
                        <span class="input-group-text">Upload</span>
                    </div>
                </div>

                <!-- preview -->
                <div id="previewContainer" class="d-flex flex-wrap mt-3"></div>
            </div>
        </div>
        <div class="row">
            <div class="form-group col-4">
                <button type="submit" id="btnSubmit" class="btn btn-success">บันทึกข้อมูล</button>
                <a href="product.php" type="button" class="btn btn-dark">กลับ</a>
            </div>
        </div>
    </div>
</form>

<script>
$(document).ready(function() {
    // เมื่อคลิกปุ่มบันทึก (ใช้ input file ปกติ)
    $("#btnSubmit").click(function(e) {
        e.preventDefault();

        var formData = new FormData($("#filepond-form")[0]);

        $.ajax({
            url: 'product_add_db.php',
            method: 'POST',
            data: formData,
            dataType: 'json',
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.status) {
                    Swal.fire({
                        title: response.message,
                        text: 'Your action was successful!',
                        icon: 'success',
                        timer: 1000,
                        showConfirmButton: false
                    }).then(function() {
                        window.location = "product.php";
                    });
                } else {
                    Swal.fire({
                        title: response.message,
                        text: 'error!',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                }
            }
        });
    });
});

// Preview logic for native file input
function updatePreviews(input) {
    const container = document.getElementById('previewContainer');
    container.innerHTML = '';
    const files = input.files;
    if (!files || files.length === 0) return;

    Array.from(files).forEach((file, idx) => {
        if (!file.type.startsWith('image/')) return;

        const url = URL.createObjectURL(file);
        const wrap = document.createElement('div');
        wrap.className = 'm-1 position-relative preview-item';
        wrap.style.width = '220px';
        wrap.style.height = '120px';
        wrap.style.overflow = 'hidden';
        wrap.style.borderRadius = '6px';
        wrap.style.boxShadow = '0 1px 6px rgba(0,0,0,0.08)';

        const img = document.createElement('img');
        img.src = url;
        img.style.width = '100%';
        img.style.height = '100%';
        img.style.objectFit = 'cover';
        img.onload = () => URL.revokeObjectURL(url);

        const btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'btn btn-sm btn-light';
        btn.style.position = 'absolute';
        btn.style.left = '6px';
        btn.style.top = '6px';
        btn.innerText = 'ลบ';
        btn.onclick = function() {
            // remove file from input using DataTransfer
            const dt = new DataTransfer();
            Array.from(input.files).forEach((f, i) => {
                if (i !== idx) dt.items.add(f);
            });
            input.files = dt.files;
            updatePreviews(input);
        };

        wrap.appendChild(img);
        wrap.appendChild(btn);
        container.appendChild(wrap);
    });
}

document.getElementById('fileInput').addEventListener('change', function() {
    updatePreviews(this);
});
</script>