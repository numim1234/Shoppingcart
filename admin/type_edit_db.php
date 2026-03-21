<?php 
// echo "<pre>";
// print_r($_POST);
// echo "</pre>";
// exit();
if (isset($_POST['type_name'])) {
     include '../condb.php';
     //สร้างตัวแปรวันที่เพื่อเอาไปตั้งชื่อไฟล์ใหม่
      echo '
      <script src="https://code.jquery.com/jquery-2.1.3.min.js"></script>
      <script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert-dev.js"></script>
      <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.css">';
    //ประกาศตัวแปรรับค่าจากฟอร์ม
    $type_id = $_POST['type_id'];
    $type_name = $_POST['type_name'];
  
    //sql insert
    $stmt = $conn->prepare("UPDATE tbl_type SET 
    type_name=:type_name
    WHERE type_id =:type_id");
    $stmt->bindParam(':type_id', $type_id, PDO::PARAM_INT);
    $stmt->bindParam(':type_name', $type_name, PDO::PARAM_STR);
    $result = $stmt->execute();
    //เงื่อนไขตรวจสอบการเพิ่มข้อมูล
            if($result){
                echo '<script>
                     setTimeout(function() {
                      swal({
                        title: "แก้ไขข้อมูลประเภทสำเร็จ",
                        text: "Redirecting in 1 seconds.",
                        type: "success",
                        timer: 1000,
                        showConfirmButton: false
                      }, function() {
                          window.location = "type.php"; //หน้าที่ต้องการให้กระโดดไป
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
                          window.location = "type.php"; //หน้าที่ต้องการให้กระโดดไป
                      });
                    }, 1000);
                </script>';
            } //else ของ if result  
    $conn = null; //close connect db
    } //isset
?>