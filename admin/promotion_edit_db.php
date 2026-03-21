<?php 
// echo "<pre>";
// print_r($_POST);
// echo "</pre>";
// exit();
if (isset($_POST['pro_name'])) {
     include '../condb.php';
     //สร้างตัวแปรวันที่เพื่อเอาไปตั้งชื่อไฟล์ใหม่
      echo '
      <script src="https://code.jquery.com/jquery-2.1.3.min.js"></script>
      <script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert-dev.js"></script>
      <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.css">';
    //ประกาศตัวแปรรับค่าจากฟอร์ม
    $pro_id = $_POST['pro_id'];
    $pro_name = $_POST['pro_name'];
    $pro_code = $_POST['pro_code'];
    $pro_start = $_POST['pro_start'];
    $pro_end = $_POST['pro_end'];
    $pro_discount = $_POST['pro_discount'];
    $pro_img2 = $_POST['pro_img2'];
    $date1 = date("Ymd_His");
    //สร้างตัวแปรสุ่มตัวเลขเพื่อเอาไปตั้งชื่อไฟล์ที่อัพโหลดไม่ให้ชื่อไฟล์ซ้ำกัน
    $numrand = (mt_rand());
    $pro_img = (isset($_POST['pro_img']) ? $_POST['pro_img'] : '');
    $upload=$_FILES['pro_img']['name'];
    //ตัดขื่อเอาเฉพาะนามสกุล
    $typefile = strrchr($_FILES['pro_img']['name'],".");
    //มีการอัพโหลดไฟล์
    if($upload !='') {
    //โฟลเดอร์ที่เก็บไฟล์
    $path="pro_img/";
    //ตั้งชื่อไฟล์ใหม่เป็น สุ่มตัวเลข+วันที่
    $newname = $numrand.$date1.$typefile;
    $path_copy=$path.$newname;
    //คัดลอกไฟล์ไปยังโฟลเดอร์
    move_uploaded_file($_FILES['pro_img']['tmp_name'],$path_copy);      
    }else {
      $newname=$pro_img2;
    }
    //sql insert
    $stmt = $conn->prepare("UPDATE tbl_promotion SET 
    pro_name=:pro_name,
    pro_code=:pro_code,
    pro_start=:pro_start,
    pro_end=:pro_end,
    pro_discount=:pro_discount,
    pro_img='$newname'
    WHERE pro_id =:pro_id");
    $stmt->bindParam(':pro_id', $pro_id, PDO::PARAM_INT);
    $stmt->bindParam(':pro_name', $pro_name, PDO::PARAM_STR);
    $stmt->bindParam(':pro_discount', $pro_discount, PDO::PARAM_STR);
    $stmt->bindParam(':pro_code', $pro_code, PDO::PARAM_STR);
    $stmt->bindParam(':pro_start', $pro_start, PDO::PARAM_STR);
    $stmt->bindParam(':pro_end', $pro_end, PDO::PARAM_STR);
    $result = $stmt->execute();
    //เงื่อนไขตรวจสอบการเพิ่มข้อมูล
            if($result){
                echo '<script>
                     setTimeout(function() {
                      swal({
                        title: "แก้ไขข้อมูลโปรโมชั่นสำเร็จ",
                        text: "Redirecting in 1 seconds.",
                        type: "success",
                        timer: 1000,
                        showConfirmButton: false
                      }, function() {
                          window.location = "promotion.php"; //หน้าที่ต้องการให้กระโดดไป
                      });
                    }, 1000);
                </script>';
            }else{
               echo '<script>
                     setTimeout(function() {
                      swal({
                          title: "เกิดข้อผิดพลาด",
                          type: "error"
                      }, function() {
                          window.location = "promotion.php"; //หน้าที่ต้องการให้กระโดดไป
                      });
                    }, 1000);
                </script>';
            } //else ของ if result  
    $conn = null; //close connect db
    } //isset
?>