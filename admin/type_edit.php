           <?php
      if(isset($_GET['type_id'])){
      include '../condb.php';
      $stmt_type = $conn->prepare("
        SELECT * FROM tbl_type WHERE type_id=?
        ORDER BY type_id ASC #เรียงลำดับข้อมูลจากน้อยไปมาก
        ");
      $stmt_type->execute([$_GET['type_id']]);
      $row_type = $stmt_type->fetch(PDO::FETCH_ASSOC);
      //ถ้าคิวรี่ผิดพลาดให้กลับไปหน้า index
        if($stmt_type->rowCount() < 1){
            header('Location: index.php');
            exit();
         }
      }//isset
      ?>
    <form action="type_edit_db.php" method="post" enctype="multipart/form-data">
        <div class="card-body">
            <!-- First row: Username and Password -->
            <div class="row">
                <div class="form-group col-4">
                    <label for="exampleInputEmail1">ประเภทสินค้า</label>
                    <input type="text" name="type_name"  value="<?= $row_type['type_name'];?>" class="form-control" id="exampleInputEmail1" placeholder="ประเภทสินค้า" required>
                </div>
          
            </div>
           
        <!-- Submit button -->
              <div class="form-group col-6">
            <input type="hidden" name="type_id" value="<?= $row_type['type_id'];?>">
            <button type="submit" class="btn btn-success">บันทึกข้อมูล</button>
            <a href="type.php" type="button" class="btn btn-dark">กลับ</a>
        </div>
    </form>
</div>
