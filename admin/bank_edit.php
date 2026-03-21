    <script type="text/javascript">
      function previewImage(event) {
         var input = event.target;
         var image = document.getElementById('preview');
         if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
               image.src = e.target.result;
            }
            reader.readAsDataURL(input.files[0]);
         }
      }
   </script>
   <style>
      #preview {
         width: 300px;
         height: 300px;
      }
   </style>
   <?php
      if(isset($_GET['b_id'])){
      include '../condb.php';
      $stmt_b = $conn->prepare("
        SELECT * FROM tbl_bank WHERE b_id=?
        ORDER BY b_id ASC #เรียงลำดับข้อมูลจากน้อยไปมาก
        ");
      $stmt_b->execute([$_GET['b_id']]);
      $row_b = $stmt_b->fetch(PDO::FETCH_ASSOC);
      //ถ้าคิวรี่ผิดพลาดให้กลับไปหน้า index
        if($stmt_b->rowCount() < 1){
            header('Location: index.php');
            exit();
         }
      }//isset
      ?>
    <form action="bank_edit_db.php" method="post" enctype="multipart/form-data">
        <div class="card-body">
            <!-- First row: Username and Password -->
            <div class="row">
                <div class="form-group col-4">
                    <label for="exampleInputEmail1">ธนาคาร</label>
                    <input type="text" name="b_name" value="<?= $row_b['b_name'];?>" class="form-control" id="exampleInputEmail1" placeholder="ชื่อธนาคาร" required>
                </div>
                <div class="form-group col-4">
                    <label for="exampleInputPassword1">ประเภท</label>
                    <input type="text" name="b_type" value="<?= $row_b['b_type'];?>" class="form-control" readonly>
                </div>
            </div>

            <!-- Second row: Name, Email -->
            <div class="row">
                <div class="form-group col-8">
                    <label for="exampleInputEmail1">ชื่อเจ้าของบัญชี</label>
                    <input type="text" name="b_owner"  value="<?= $row_b['b_owner'];?>" class="form-control" id="exampleInputEmail1" placeholder="ชื่อ-นามสกุล" required>
                </div>
            </div>

            <!-- Third row: Phone number -->
            <div class="row">
               <div class="form-group col-4">
                    <label for="exampleInputPassword1">เลขบัญชี</label>
                    <input type="text" name="b_number"  value="<?= $row_b['b_number'];?>" class="form-control" id="exampleInputPassword1" placeholder="เลขบัญชี" required>
                </div>
                <div class="form-group col-4">
                    <label for="exampleInputEmail1">สาขา</label>
                    <input type="text" name="bn_name"  value="<?= $row_b['bn_name'];?>" class="form-control" id="exampleInputEmail1" placeholder="สาขา" required>

                </div>
              
            </div> 
            <!-- File input -->
            <div class="row">
            <div class="form-group col-4">
                <img src='b_logo/<?= $row_b['b_logo'];?>' width="300px">
                 <img id="preview" alt="Preview Image">
               </div>
             </div>
             <div class="row">
            <div class="form-group col-4">
                <label for="exampleInputFile">File รูปภาพ Logo ธนาคาร *jpg, png</label>
                <div class="input-group">
                    <div class="custom-file">
                        <input type="file" name="b_logo" class="custom-file-input" id="exampleInputFile" onchange="previewImage(event)" >
                        <label class="custom-file-label" for="exampleInputFile">Choose file</label>
                    </div>
                    <div class="input-group-append">
                        <span class="input-group-text">Upload</span>
                    </div>
                </div>
            </div>
          </div>
        <!-- Submit button -->
              <div class="form-group col-4">
            <input type="hidden" name="b_logo2" value="<?php echo $row_b['b_logo'];?>">
            <input type="hidden" name="b_id" value="<?= $row_b['b_id'];?>">
            <button type="submit" class="btn btn-success">บันทึกข้อมูล</button>
            <a href="bank.php" type="button" class="btn btn-dark">กลับ</a>
        </div>
    </form>
</div>
