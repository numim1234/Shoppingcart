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
      if(isset($_GET['m_id'])){
      include '../condb.php';
      $stmt_m = $conn->prepare("
        SELECT * FROM tbl_member WHERE m_id=?
        ORDER BY m_id ASC #เรียงลำดับข้อมูลจากน้อยไปมาก
        ");
      $stmt_m->execute([$_GET['m_id']]);
      $row_em = $stmt_m->fetch(PDO::FETCH_ASSOC);
      //ถ้าคิวรี่ผิดพลาดให้กลับไปหน้า index
        if($stmt_m->rowCount() < 1){
            header('Location: index.php');
            exit();
         }
      }//isset
      ?>
    <form action="admin_edit_db.php" method="post" enctype="multipart/form-data">
        <div class="card-body">
            <!-- First row: Username and Password -->
            <div class="row">
                <div class="form-group col-4">
                    <label for="exampleInputEmail1">Username</label>
                    <input type="text" name="m_username" value="<?= $row_em['m_username'];?>" class="form-control" id="exampleInputEmail1" placeholder="Enter Username">
                </div>
                <div class="form-group col-4">
                    <label for="exampleInputPassword1">Password</label>
                    <input type="text" name="m_password" value="<?= $row_em['m_password'];?>" class="form-control" id="exampleInputPassword1" placeholder="Enter Password">
                </div>
            </div>

            <!-- Second row: Name, Email -->
            <div class="row">
                <div class="form-group col-8">
                    <label for="exampleInputEmail1">ชื่อ-นามสกุล</label>
                    <input type="text" name="m_name" value="<?= $row_em['m_name'];?>" class="form-control" id="exampleInputEmail1" placeholder="ชื่อ-นามสกุล">
                </div>
            </div>

            <!-- Third row: Phone number -->
            <div class="row">
               <div class="form-group col-4">
                    <label for="exampleInputPassword1">อีเมล์</label>
                    <input type="email" name="m_email" value="<?= $row_em['m_email'];?>" class="form-control" id="exampleInputPassword1" placeholder="Email">
                </div>
                <div class="form-group col-4">
                    <label for="exampleInputEmail1">เบอร์โทร</label>
                    <input type="text" name="m_tel" value="<?= $row_em['m_tel'];?>"  class="form-control"  id="exampleInputEmail1" placeholder="เบอร์โทร">
                </div>
                
            </div>
            <div class="row">
             <div class="form-group col-4">
                    <label for="address">ที่อยู่</label>
                    <textarea class="form-control" name="m_address" id="address" placeholder="Address"><?= $row_em['m_address'];?></textarea><br>
                       <img src='m_img/<?= $row_em['m_img'];?>' width="300px">
                </div> 
            </div>  
            <!-- File input -->
            <div class="row">
            <div class="form-group col-4">
                 <img id="preview" alt="Preview Image">
               </div>
             </div>
             <div class="row">
            <div class="form-group col-4">
                <label for="exampleInputFile">File รูปภาพ *jpg, png</label>
                <div class="input-group">
                  
                    <div class="custom-file">
                        <input type="file" name="m_img" class="custom-file-input" id="exampleInputFile" onchange="previewImage(event)" >
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
            <input type="hidden" name="m_level" value="admin">
            <input type="hidden" name="m_img2" value="<?php echo $row_em['m_img'];?>">
            <input type="hidden" name="m_id" value="<?= $row_em['m_id'];?>">
            <button type="submit" class="btn btn-success">บันทึกข้อมูล</button>
            <a href="admin.php" type="button" class="btn btn-dark">กลับ</a>
        </div>
    </form>
</div>
