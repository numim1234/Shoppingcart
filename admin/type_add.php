    <form action="type_add_db.php" method="post" enctype="multipart/form-data">
        <div class="card-body">
            <!-- First row: Username and Password -->
            <div class="row">
                <div class="form-group col-4">
                    <label for="exampleInputEmail1">ประเภทสินค้า</label>
                    <input type="text" name="type_name" class="form-control" id="exampleInputEmail1" placeholder="ประเภทสินค้า" required>
                </div>
          
            </div>
           
        <!-- Submit button -->
              <div class="form-group col-6">
            <button type="submit" class="btn btn-success">บันทึกข้อมูล</button>
            <a href="type.php" type="button" class="btn btn-dark">กลับ</a>
        </div>
    </form>
</div>
