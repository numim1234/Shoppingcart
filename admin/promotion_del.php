<?php 
if(isset($_GET['pro_id'])){
    include '../condb.php';
//ประกาศตัวแปรรับค่าจาก param method get
$pro_id = $_GET['pro_id'];
$stmt = $conn->prepare('DELETE FROM tbl_promotion WHERE pro_id=:pro_id');
$stmt->bindParam(':pro_id', $pro_id , PDO::PARAM_INT);
$stmt->execute();

  if($stmt->rowCount() > 0){
        echo '<script>       
              window.location = "promotion.php"; //หน้าที่ต้องการให้กระโดดไป
              </script>';
    }else{
       echo '<script>         
              window.location = "promotion.php"; //หน้าที่ต้องการให้กระโดดไป
             </script>';
    }
$conn = null;
} //isset
?>