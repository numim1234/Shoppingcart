<?php 
if(isset($_GET['type_id'])){
    include '../condb.php';
//ประกาศตัวแปรรับค่าจาก param method get
$type_id = $_GET['type_id'];
$stmt = $conn->prepare('DELETE FROM tbl_type WHERE type_id=:type_id');
$stmt->bindParam(':type_id', $type_id , PDO::PARAM_INT);
$stmt->execute();

  if($stmt->rowCount() > 0){
        echo '<script>       
              window.location = "type.php"; //หน้าที่ต้องการให้กระโดดไป
              </script>';
    }else{
       echo '<script>         
              window.location = "type.php"; //หน้าที่ต้องการให้กระโดดไป
             </script>';
    }
$conn = null;
} //isset
?>