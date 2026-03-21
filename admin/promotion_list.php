<?php 
//คิวรี่ข้อมูลมาแสดงในตาราง
    include '../condb.php';
    $stmtPromotion = $conn->prepare("
    SELECT * FROM tbl_promotion 
    ORDER BY pro_id  ASC #เรียงลำดับข้อมูลจากน้อยไปมาก
    ");
      $stmtPromotion->execute();
      $resultPro = $stmtPromotion->fetchAll();                                         
?>
  <table id="example1" class="table table-striped dataTable">
    <thead>
      <tr role="row" class="info">
        <th  tabindex="0" rowspan="1" colspan="1" style="width: 1%;">No.</th>
        <th  tabindex="0" rowspan="1" colspan="1" style="width: 5%;">รูป</th>
        <th  tabindex="0" rowspan="1" colspan="1" style="width: 15%;">โปรโมชั่น</th>
        <th  tabindex="0" rowspan="1" colspan="1" style="width: 15%;">CODE</th>
        <th  tabindex="0" rowspan="1" colspan="1" style="width: 10%;">ส่วนลด</th>
        <th  tabindex="0" rowspan="1" colspan="1" style="width: 15%;">เริ่มต้น</th>
        <th  tabindex="0" rowspan="1" colspan="1" style="width: 15%;">สิ้นสุด</th>
        <th  tabindex="0" rowspan="1" colspan="1" style="width: 10%;">สถานะ</th>
        <th  tabindex="0" rowspan="1" colspan="1" style="width: 3%;">-</th>  
        <th  tabindex="0" rowspan="1" colspan="1" style="width: 3%;">-</th> 
      </tr>
    </thead>
    <tbody>
       <?php $runNumber = 1; foreach ($resultPro as $row_pro) { ?>  
      <tr>
        <td  align="center">
         <?php echo $runNumber++; ?>
        </td>
         <td  align="center">
        <img src="pro_img/<?php echo $row_pro['pro_img']; ?>"  width="60">
        </td>
         <td>
         <?php echo $row_pro['pro_name']; ?>
        </td>
         <td>
         <?php echo $row_pro['pro_code']; ?>
        </td>
         <td>
         <?php echo $row_pro['pro_discount']; ?> %
        </td>
         <td>
         <?php echo date('d-m-Y H:i:s',strtotime($row_pro['pro_start'])); ?>
        </td>
        <td>
         <?php echo date('d-m-Y H:i:s',strtotime($row_pro['pro_end'])); ?>
        </td>
         <?php  $date_now = new DateTime();
            $date2    = new DateTime(date('m/d/Y H:i:s',strtotime($row_pro['pro_end'])));?>
        <td class="align">
        <?php echo $date_now > $date2  ? 'ปิดใช้งาน' :  'ใช้งาน' ?>
        </td>
        <td><a href="promotion.php?act=edit&pro_id=<?php echo $row_pro['pro_id']; ?>"><i class="fas fa-edit" style="color: #2E86C1;"></i></a></td>
        <td><a href=""  onclick="confirmDelete(event, '<?php echo $row_pro['pro_id']; ?>')"><i class="fas fa-trash" style="color: #D35400;"></i></a></td>   
         <?php } ?>  
      </tr>
    </tbody>
  </table>
   <script>
       function confirmDelete(event, pro_id) {
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
                   window.location.href = "promotion_del.php?pro_id=" + encodeURIComponent(pro_id)
              }
          });
      }

    </script>
