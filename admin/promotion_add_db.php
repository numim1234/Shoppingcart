<?php
// echo "<pre>";
// print_r($_POST);
// print_r($_FILES);
// echo "</pre>";
// exit();
if (isset($_POST['pro_name'])) {
     include '../condb.php';
     //สร้างตัวแปรวันที่เพื่อเอาไปตั้งชื่อไฟล์ใหม่
      echo '
      <script src="https://code.jquery.com/jquery-2.1.3.min.js"></script>
      <script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert-dev.js"></script>
      <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.css">';
      
    $date1 = date("Ymd_His");
    //สร้างตัวแปรสุ่มตัวเลขเพื่อเอาไปตั้งชื่อไฟล์ที่อัพโหลดไม่ให้ชื่อไฟล์ซ้ำกัน
    $numrand = (mt_rand());
    $pro_img = (isset($_POST['pro_img']) ? $_POST['pro_img'] : '');
    $upload=$_FILES['pro_img']['name'];
    //มีการอัพโหลดไฟล์
    if($upload !='') {
    //ตัดขื่อเอาเฉพาะนามสกุล
    $typefile = strrchr($_FILES['pro_img']['name'],".");
    //สร้างเงื่อนไขตรวจสอบนามสกุลของไฟล์ที่อัพโหลดเข้ามา
    if($typefile =='.jpg' || $typefile  =='.jpg' || $typefile  =='.png'){
    //โฟลเดอร์ที่เก็บไฟล์
    $path="pro_img/";
    //ตั้งชื่อไฟล์ใหม่เป็น สุ่มตัวเลข+วันที่
    $newname = $numrand.$date1.$typefile;
    $path_copy=$path.$newname;
    //คัดลอกไฟล์ไปยังโฟลเดอร์
    move_uploaded_file($_FILES['pro_img']['tmp_name'],$path_copy);
     //ประกาศตัวแปรรับค่าจากฟอร์ม
    $pro_name = $_POST['pro_name'];
    $pro_code = $_POST['pro_code'];
    $pro_start = $_POST['pro_start'];
    $pro_end = $_POST['pro_end'];
    $pro_discount = $_POST['pro_discount'];
  
    //sql insert
    $stmt = $conn->prepare("INSERT INTO tbl_promotion (pro_name, pro_discount, pro_code, pro_start, pro_end, pro_img)
    VALUES (:pro_name, :pro_discount, :pro_code, :pro_start, :pro_end, '$newname')");
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
                          title: "บันทึกข้อมูลสำเร็จ",
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
        }else{ //ถ้าไฟล์ที่อัพโหลดไม่ตรงตามที่กำหนด
            echo '<script>
                         setTimeout(function() {
                          swal({
                              title: "คุณอัพโหลดไฟล์ไม่ถูกต้อง",
                              type: "error"
                          }, function() {
                              window.location = "promotion.php"; //หน้าที่ต้องการให้กระโดดไป
                          });
                        }, 1000);
                    </script>';
        } //else ของเช็คนามสกุลไฟล์  
    } // if($upload !='') {
    $conn = null; //close connect db
    } //isset
?>