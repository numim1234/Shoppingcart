<?php
include '../condb.php';

$stmtMem = $conn->prepare("
    SELECT * FROM tbl_member 
    WHERE m_level = 'member'
    ORDER BY m_id ASC
");
$stmtMem->execute();
$resultMem = $stmtMem->fetchAll();
?>

<!DOCTYPE html>
<html lang="th">

<head>
  <meta charset="UTF-8">
  <title>จัดการสมาชิก</title>

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
      border: none;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    }

    table.dataTable thead {
      background: #343a40;
      color: #fff;
    }

    table.dataTable tbody tr:hover {
      background: #f1f3f5;
    }

    .profile-img {
      border-radius: 50%;
      object-fit: cover;
      border: 2px solid #dee2e6;
    }

    .badge-member {
      background: #28a745;
      color: #fff;
      padding: 6px 12px;
      border-radius: 20px;
    }

    .btn {
      border-radius: 8px;
    }
  </style>
</head>

<body class="container mt-4">

  <!-- header -->
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h4>👥 จัดการสมาชิก</h4>

    <a href="member.php?act=add" class="btn btn-primary">
      <i class="fas fa-plus"></i> เพิ่มสมาชิก
    </a>
  </div>

  <div class="card">
    <div class="card-body">

      <table id="example1" class="table table-striped align-middle">
        <thead>
          <tr>
            <th style="width:5%">No.</th>
            <th style="width:8%">รูป</th>
            <th>Username</th>
            <th>ชื่อ</th>
            <th>อีเมล</th>
            <th style="width:10%">สถานะ</th>
            <th style="width:12%">สิทธิ์</th>
            <th style="width:12%">จัดการ</th>
          </tr>
        </thead>

        <tbody>
          <?php $runNumber = 1;
          foreach ($resultMem as $row_member) { ?>
            <tr>

              <td class="text-center">
                <?php echo $runNumber++; ?>
              </td>

              <td class="text-center">
                <img src="m_img/<?php echo $row_member['m_img']; ?>" width="45" height="45"
                  class="profile-img">
              </td>

              <td><?php echo $row_member['m_username']; ?></td>

              <td><?php echo $row_member['m_name']; ?></td>

              <td><?php echo $row_member['m_email']; ?></td>

              <td class="text-center">
                <div class="form-check form-switch">
                  <input class="form-check-input" type="checkbox"
                    onchange="toggle_check(<?= $row_member['m_id'] ?>)"
                    <?php echo ($row_member['m_status'] == 1) ? 'checked' : ''; ?>>
                </div>
              </td>

              <td class="text-center">
                <span class="badge-member">
                  <i class="fas fa-user"></i> Member
                </span>
              </td>

              <td class="text-center">

                <a href="member.php?act=edit&m_id=<?php echo $row_member['m_id']; ?>"
                  class="btn btn-warning btn-sm">
                  <i class="fas fa-edit"></i>
                </a>

                <button class="btn btn-danger btn-sm"
                  onclick="confirmDelete('<?php echo $row_member['m_id']; ?>')">
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

    // toggle status
    function toggle_check(m_id) {
      $.ajax({
        method: 'POST',
        url: 'admin_update_status.php',
        data: {
          m_id: m_id
        }
      });
    }

    // delete
    function confirmDelete(m_id) {
      Swal.fire({
        title: 'ลบสมาชิก?',
        text: 'ข้อมูลจะถูกลบถาวร',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'ลบ',
        cancelButtonText: 'ยกเลิก'
      }).then((result) => {
        if (result.isConfirmed) {
          window.location.href = "member_del.php?m_id=" + encodeURIComponent(m_id);
        }
      });
    }
  </script>

</body>

</html>