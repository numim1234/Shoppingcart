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
      function generateRandomString($length = 10) {
          $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
          $charactersLength = strlen($characters);
          $randomString = '';
          for ($i = 0; $i < $length; $i++) {
              $randomString .= $characters[rand(0, $charactersLength - 1)];
          }
          return $randomString;
      } 
    ?>
    <form action="promotion_add_db.php" method="post" enctype="multipart/form-data">
        <div class="card-body">
            <!-- First row: Username and Password -->
            <div class="row">
                <div class="form-group col-4">
                    <label for="exampleInputEmail1">โปรโมชั่น</label>
                    <input type="text" name="pro_name" class="form-control" id="exampleInputEmail1" placeholder="โปรโมชั่น" required>
                </div>
                <div class="form-group col-4">
                    <label for="exampleInputPassword1">code ส่วนลด</label>
                    <input type="text" name="pro_code" class="form-control" value="<?php echo strtoupper(generateRandomString())?>" readonly>
                </div>
            </div>
            <div class="row">
               <div class="form-group col-4">
                    <label for="exampleInputPassword1">วันที่เริ่มต้น</label>
                    <input type="datetime-local" name="pro_start" class="form-control" id="exampleInputPassword1" placeholder="เลขบัญชี" required>
                </div>
                <div class="form-group col-4">
                    <label for="exampleInputEmail1">วันที่สิ้นสุด</label>
                    <input type="datetime-local" name="pro_end" class="form-control" id="exampleInputEmail1" placeholder="สาขา" required>
                </div>
                
            </div> 
                <div class="row">
               <div class="form-group col-4">
                    <label for="exampleInputPassword1">ส่วนลด %</label>
                    <input type="text" name="pro_discount" class="form-control" id="exampleInputPassword1" placeholder="ส่วนลด %" required>
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
                <label for="exampleInputFile">File รูปภาพ Logo ธนาคาร *jpg, png</label>
                <div class="input-group">
                    <div class="custom-file">
                        <input type="file" name="pro_img" class="custom-file-input" id="exampleInputFile" onchange="previewImage(event)" required>
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
            <button type="submit" class="btn btn-success">บันทึกข้อมูล</button>
            <a href="promotion.php" type="button" class="btn btn-dark">กลับ</a>
        </div>
    </form>
</div>
