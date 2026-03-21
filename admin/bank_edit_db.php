<?php 
// echo "<pre>";
// print_r($_POST);
// echo "</pre>";
// exit();
if (isset($_POST['b_name'])) {
     include '../condb.php';
     //สร้างตัวแปรวันที่เพื่อเอาไปตั้งชื่อไฟล์ใหม่
      echo '
      <script src="https://code.jquery.com/jquery-2.1.3.min.js"></script>
      <script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert-dev.js"></script>
      <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.css">';
    //ประกาศตัวแปรรับค่าจากฟอร์ม
    $b_id = $_POST['b_id'];
    $b_name = $_POST['b_name'];
    $b_owner = $_POST['b_owner'];
    $b_number = $_POST['b_number'];
    $bn_name = $_POST['bn_name'];
    $b_type = $_POST['b_type'];
    $b_logo2 = $_POST['b_logo2'];
    $date1 = date("Ymd_His");
    //สร้างตัวแปรสุ่มตัวเลขเพื่อเอาไปตั้งชื่อไฟล์ที่อัพโหลดไม่ให้ชื่อไฟล์ซ้ำกัน
    $numrand = (mt_rand());
    $b_logo = (isset($_POST['b_logo']) ? $_POST['b_logo'] : '');
    $upload=$_FILES['b_logo']['name'];
    //ตัดขื่อเอาเฉพาะนามสกุล
    $typefile = strrchr($_FILES['b_logo']['name'],".");
    //มีการอัพโหลดไฟล์
    if($upload !='') {
    //โฟลเดอร์ที่เก็บไฟล์
    $path="b_logo/";
    //ตั้งชื่อไฟล์ใหม่เป็น สุ่มตัวเลข+วันที่
    $newname = $numrand.$date1.$typefile;
    $path_copy=$path.$newname;
    //คัดลอกไฟล์ไปยังโฟลเดอร์
    move_uploaded_file($_FILES['b_logo']['tmp_name'],$path_copy);      
    }else {
      $newname=$b_logo2;
    }
    //sql insert
    $stmt = $conn->prepare("UPDATE tbl_bank SET 
    b_name=:b_name,
    b_type=:b_type,
    b_owner=:b_owner,
    b_number=:b_number,
    bn_name=:bn_name,
    b_logo='$newname'
    WHERE b_id =:b_id");
    $stmt->bindParam(':b_id', $b_id, PDO::PARAM_INT);
    $stmt->bindParam(':b_name', $b_name, PDO::PARAM_STR);
    $stmt->bindParam(':b_type', $b_type, PDO::PARAM_STR);
    $stmt->bindParam(':b_owner', $b_owner, PDO::PARAM_STR);
    $stmt->bindParam(':b_number', $b_number, PDO::PARAM_STR);
    $stmt->bindParam(':bn_name', $bn_name, PDO::PARAM_STR);
    $result = $stmt->execute();
    //เงื่อนไขตรวจสอบการเพิ่มข้อมูล
            if($result){
                echo '<script>
                     setTimeout(function() {
                      swal({
                        title: "แก้ไขข้อมูลบัญชีสำเร็จ",
                        text: "Redirecting in 1 seconds.",
                        type: "success",
                        timer: 1000,
                        showConfirmButton: false
                      }, function() {
                          window.location = "bank.php"; //หน้าที่ต้องการให้กระโดดไป
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
                          window.location = "bank.php"; //หน้าที่ต้องการให้กระโดดไป
                      });
                    }, 1000);
                </script>';
            } //else ของ if result  
    $conn = null; //close connect db
    } //isset
?>