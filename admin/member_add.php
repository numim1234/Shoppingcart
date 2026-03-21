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
    <form action="member_add_db.php" method="post" enctype="multipart/form-data">
        <div class="card-body">
            <!-- First row: Username and Password -->
            <div class="row">
                <div class="form-group col-4">
                    <label for="exampleInputEmail1">Username</label>
                    <input type="text" name="m_username" class="form-control" id="exampleInputEmail1" placeholder="Enter Username" required>
                </div>
                <div class="form-group col-4">
                    <label for="exampleInputPassword1">Password</label>
                    <input type="password" name="m_password" class="form-control" id="exampleInputPassword1" placeholder="Enter Password" required>
                </div>
            </div>

            <!-- Second row: Name, Email -->
            <div class="row">
                <div class="form-group col-8">
                    <label for="exampleInputEmail1">ชื่อ-นามสกุล</label>
                    <input type="text" name="m_name" class="form-control" id="exampleInputEmail1" placeholder="ชื่อ-นามสกุล" required>
                </div>
            </div>

            <!-- Third row: Phone number -->
            <div class="row">
               <div class="form-group col-4">
                    <label for="exampleInputPassword1">อีเมล์</label>
                    <input type="email" name="m_email" class="form-control" id="exampleInputPassword1" placeholder="Email" required>
                </div>
                <div class="form-group col-4">
                    <label for="exampleInputEmail1">เบอร์โทร</label>
                    <input type="text" name="m_tel" class="form-control" id="exampleInputEmail1" placeholder="เบอร์โทร" required>
                </div>
                
            </div>
            <div class="row">
             <div class="form-group col-4">
                    <label for="address">ที่อยู่</label>
                    <textarea class="form-control" name="m_address" id="address" placeholder="Address"></textarea>
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
                        <input type="file" name="m_img" class="custom-file-input" id="exampleInputFile" onchange="previewImage(event)" required>
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
            <input type="hidden" name="m_level" value="member">
            <button type="submit" class="btn btn-success">บันทึกข้อมูล</button>
            <a href="member.php" type="button" class="btn btn-dark">กลับ</a>
        </div>
    </form>
</div>
