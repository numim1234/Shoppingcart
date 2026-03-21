<?php 
//คิวรี่ข้อมูลมาแสดงในตาราง
    include '../condb.php';
    $stmtBank = $conn->prepare("
    SELECT * FROM tbl_bank 
    ORDER BY b_id  ASC #เรียงลำดับข้อมูลจากน้อยไปมาก
    ");
      $stmtBank->execute();
      $resultBank = $stmtBank->fetchAll();                                         
?>
  <table id="example1" class="table table-striped dataTable">
    <thead>
      <tr role="row" class="info">
        <th  tabindex="0" rowspan="1" colspan="1" style="width: 1%;">No.</th>
        <th  tabindex="0" rowspan="1" colspan="1" style="width: 5%;">รูป</th>
        <th  tabindex="0" rowspan="1" colspan="1" style="width: 10%;">ธนาคาร</th>
        <th  tabindex="0" rowspan="1" colspan="1" style="width: 10%;">ประเภท</th>
        <th  tabindex="0" rowspan="1" colspan="1" style="width: 20%;">ชื่อเจ้าของบัญชี</th>
        <th  tabindex="0" rowspan="1" colspan="1" style="width: 15%;">เลขบัญชี</th>
        <th  tabindex="0" rowspan="1" colspan="1" style="width: 10%;">สาขา</th>
        <th  tabindex="0" rowspan="1" colspan="1" style="width: 3%;">-</th>  
        <th  tabindex="0" rowspan="1" colspan="1" style="width: 3%;">-</th> 
      </tr>
    </thead>
    <tbody>
       <?php $runNumber = 1; foreach ($resultBank as $row_bank) { ?>  
      <tr>
        <td  align="center">
         <?php echo $runNumber++; ?>
        </td>
         <td  align="center">
        <img src="b_logo/<?php echo $row_bank['b_logo']; ?>" id="uploaded_image" class="img-responsive img-circle " width="40">
        </td>
         <td>
         <?php echo $row_bank['b_name']; ?>
        </td>
         <td>
         <?php echo $row_bank['b_type']; ?>
        </td>
         <td>
         <?php echo $row_bank['b_owner']; ?>
        </td>
         <td>
         <?php echo $row_bank['b_number']; ?>
        </td>
        <td>
         <?php echo $row_bank['bn_name']; ?>
        </td>
        <td><a href="bank.php?act=edit&b_id=<?php echo $row_bank['b_id']; ?>"><i class="fas fa-edit" style="color: #2E86C1;"></i></a></td>
        <td><a href=""  onclick="confirmDelete(event, '<?php echo $row_bank['b_id']; ?>')"><i class="fas fa-trash" style="color: #D35400;"></i></a></td>   
         <?php } ?>  
      </tr>
    </tbody>
  </table>
   <script>
       function confirmDelete(event, b_id) {
          event.preventDefault(); // หยุดการเปลี่ยนเส้นทางไปยังหน้าลบก่อน
          Swal.fire({
              text: "คุณแน่ใจที่จะลบข้อมูลหรือไม่?",
              icon: "warning",
              showCancelButton: true,
              confirmButtonColor: "#3085d6",
              cancelButtonColor: "#d33",
              confirmButtonText: "ตกลง",
              cancelButtonText: "ยกเลิก"
          }).then((result) => {
              if (result.isConfirmed) {
                  // Redirect to logout.php if user confirms
                   window.location.href = "bank_del.php?b_id=" + encodeURIComponent(b_id)
              }
          });
      }

    </script>
