<?php
include '../condb.php';

// ================== โหมดจัดการรูป ==================
if (isset($_GET['p_id'])) {
    $stmt_img = $conn->prepare("SELECT * FROM tbl_img_detail WHERE p_id = ? ORDER BY id DESC");
    $stmt_img->execute([$_GET['p_id']]);
    $result_img = $stmt_img->fetchAll();
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>จัดการรูปภาพ</title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
    .card {
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    }

    .card img:hover {
        transform: scale(1.05);
    }
    </style>
</head>

<body class="container mt-4">

    <h4>📸 จัดการรูปสินค้า</h4>

    <div class="row">
        <?php foreach ($result_img as $row_img) { ?>
        <div class="col-md-3 mb-3">
            <div class="card">
                <img src="../p_gallery/<?php echo $row_img['img']; ?>" style="height:180px; object-fit:cover;">
                <div class="card-body text-center">
                    <button class="btn btn-danger btn-sm w-100"
                        onclick="delImg(<?php echo $row_img['id']; ?>,'<?php echo $row_img['img']; ?>')">
                        <i class="fas fa-trash"></i> ลบ
                    </button>
                </div>
            </div>
        </div>
        <?php } ?>
    </div>

    <script>
    function delImg(id, img_name) {
        Swal.fire({
                title: 'ลบรูป?',
                icon: 'warning',
                showCancelButton: true
            })
            .then((r) => {
                if (r.isConfirmed) {
                    $.post('product_img_del.php', {
                        id: id,
                        img_name: img_name
                    }, function() {
                        location.reload();
                    });
                }
            });
    }
    </script>

</body>

</html>
<?php exit;
}

// ================== โหมดรายการสินค้า ==================
$stmt = $conn->prepare("
SELECT p.*, t.type_name
FROM tbl_product p
LEFT JOIN tbl_type t ON p.type_id = t.type_id
ORDER BY p.p_id DESC
");
$stmt->execute();
$result = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>จัดการสินค้า</title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>

    <style>
    body {
        background: #f8f9fa;
    }

    .card {
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    }

    .product-img {
        border-radius: 8px;
    }

    .badge-stock {
        background: #28a745;
        color: #fff;
        padding: 5px 10px;
        border-radius: 20px;
    }

    .badge-out {
        background: #dc3545;
        color: #fff;
        padding: 5px 10px;
        border-radius: 20px;
    }

    .form-check-input {
        width: 45px;
        height: 22px;
        cursor: pointer;
    }

    table.dataTable thead {
        background: #343a40;
        color: #fff;
    }
    </style>
</head>

<body class="container mt-4">

    <div class="d-flex justify-content-between mb-3">
        <h4>📦 จัดการสินค้า</h4>
        <a href="product.php?act=add" class="btn btn-primary">
            <i class="fas fa-plus"></i> เพิ่มสินค้า
        </a>
    </div>

    <div class="card">
        <div class="card-body">

            <table id="example1" class="table table-striped">
                <thead>
                    <tr>
                        <th style="width:5%">No</th>
                        <th style="width:10%">รูป</th>
                        <th style="width:20%">ชื่อ</th>
                        <th style="width:15%">ประเภท</th>
                        <th style="width:10%">ราคา</th>
                        <th style="width:10%">จำนวน</th>
                        <th style="width:10%">หน่วย</th>
                        <th style="width:10%">สถานะ</th>
                        <th style="width:10%">จัดการ</th>
                    </tr>
                </thead>

                <tbody>
                    <?php $i = 1;
                    foreach ($result as $row) {

                        $stmt_img = $conn->prepare("SELECT img FROM tbl_img_detail WHERE p_id=? ORDER BY id DESC LIMIT 1");
                        $stmt_img->execute([$row['p_id']]);
                        $img = $stmt_img->fetchColumn();
                    ?>

                    <tr>
                        <td><?php echo $i++; ?></td>

                        <td>
                            <?php if ($img) { ?>
                            <img src="p_gallery/<?php echo $img; ?>" width="60" height="60" style="object-fit:cover;">
                            <?php } else {
                                    echo '-';
                                } ?>
                        </td>

                        <td><?php echo $row['p_name']; ?></td>
                        <td><?php echo $row['type_name']; ?></td>
                        <td><?php echo number_format($row['p_price'], 2); ?></td>

                        <td>
                            <?php if ($row['p_qty'] > 0) { ?>
                            <span class="badge-stock"><?php echo $row['p_qty']; ?></span>
                            <?php } else { ?>
                            <span class="badge-out">หมด</span>
                            <?php } ?>
                        </td>

                        <td><?php echo $row['p_unit']; ?></td>

                        <td class="text-center">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox"
                                    onchange="toggleStatus(<?= $row['p_id'] ?>)"
                                    <?php echo ($row['p_status'] == 1) ? 'checked' : ''; ?>>
                            </div>

                            <?php if ($row['p_status'] == 1) { ?>
                            <small class="text-success"></small>
                            <?php } else { ?>
                            <small class="text-danger"></small>
                            <?php } ?>
                        </td>

                        <td>
                            <a href="product.php?act=edit&p_id=<?php echo $row['p_id']; ?>"
                                class="btn btn-warning btn-sm">
                                <i class="fas fa-edit"></i>
                            </a>

                            <button class="btn btn-danger btn-sm" onclick="delProduct(<?php echo $row['p_id']; ?>)">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>

                    </tr>
                    <?php } ?>
                </tbody>
            </table>

        </div>
    </div>

    <script>
    $(function() {
        $('#example1').DataTable();
    });

    function delProduct(p_id) {
        Swal.fire({
            title: 'ลบสินค้า?',
            icon: 'warning',
            showCancelButton: true
        }).then((result) => {
            if (result.isConfirmed) {
                $.post('product_delete.php', {
                    p_id: p_id
                }, function() {
                    location.reload();
                });
            }
        });
    }

    function toggleStatus(p_id) {
        $.ajax({
            method: 'POST',
            url: 'product_update_status.php',
            data: {
                p_id: p_id
            },
            dataType: 'json',
            success: function(res) {
                if (res.status) {
                    Swal.fire({
                        icon: 'success',
                        title: 'อัปเดตแล้ว',
                        timer: 800,
                        showConfirmButton: false
                    }).then(() => {
                        location.reload();
                    });
                }
            }
        });
    }
    </script>

</body>

</html>

<?php $conn = null; ?>