<?php 
if(isset($_GET['b_id'])){
    include '../condb.php';
//ประกาศตัวแปรรับค่าจาก param method get
$b_id = $_GET['b_id'];
$stmt = $conn->prepare('DELETE FROM tbl_bank WHERE b_id=:b_id');
$stmt->bindParam(':b_id', $b_id , PDO::PARAM_INT);
$stmt->execute();

  if($stmt->rowCount() > 0){
        echo '<script>       
              window.location = "bank.php"; //หน้าที่ต้องการให้กระโดดไป
              </script>';
    }else{
       echo '<script>         
              window.location = "bank.php"; //หน้าที่ต้องการให้กระโดดไป
             </script>';
    }
$conn = null;
} //isset
?>