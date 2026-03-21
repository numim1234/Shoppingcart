<?php
include '../condb.php';

$stmtType = $conn->prepare("
    SELECT * FROM tbl_type 
    ORDER BY type_id ASC
");
$stmtType->execute();
$resultType = $stmtType->fetchAll();
?>

<!DOCTYPE html>
<html lang="th">

<head>
  <meta charset="UTF-8">
  <title>จัดการประเภทสินค้า</title>

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

    /* card */
    .card {
      border-radius: 12px;
      border: none;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    }

    /* table */
    table.dataTable thead {
      background: #343a40;
      color: #fff;
    }

    table.dataTable tbody tr:hover {
      background: #f1f3f5;
    }

    /* badge */
    .badge-type {
      background: #17a2b8;
      color: white;
      padding: 6px 12px;
      border-radius: 20px;
    }

    /* button */
    .btn {
      border-radius: 8px;
    }
  </style>
</head>

<body class="container mt-4">

  <!-- header -->
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h4>📂 จัดการประเภทสินค้า</h4>

    <a href="type.php?act=add" class="btn btn-primary">
      <i class="fas fa-plus"></i> เพิ่มประเภท
    </a>
  </div>

  <!-- table -->
  <div class="card">
    <div class="card-body">

      <table id="example1" class="table table-striped">
        <thead>
          <tr>
            <th style="width:5%">No.</th>
            <th>ประเภทสินค้า</th>
            <th style="width:10%">แก้ไข</th>
            <th style="width:10%">ลบ</th>
          </tr>
        </thead>

        <tbody>
          <?php $runNumber = 1;
          foreach ($resultType as $row_type) { ?>
            <tr>

              <td class="text-center">
                <?php echo $runNumber++; ?>
              </td>

              <td>
                <span class="badge-type">
                  <?php echo $row_type['type_name']; ?>
                </span>
              </td>

              <td class="text-center">
                <a href="type.php?act=edit&type_id=<?php echo $row_type['type_id']; ?>"
                  class="btn btn-warning btn-sm">
                  <i class="fas fa-edit"></i>
                </a>
              </td>

              <td class="text-center">
                <button class="btn btn-danger btn-sm"
                  onclick="confirmDelete('<?php echo $row_type['type_id']; ?>')">
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

    function confirmDelete(type_id) {
      Swal.fire({
        title: 'ลบประเภท?',
        text: "ข้อมูลจะหายถาวร!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'ลบ',
        cancelButtonText: 'ยกเลิก'
      }).then((result) => {
        if (result.isConfirmed) {
          window.location.href = "type_del.php?type_id=" + encodeURIComponent(type_id);
        }
      });
    }
  </script>

</body>

</html>