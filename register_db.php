<?php 
if (isset($_POST['m_name'])) {
    include 'condb.php';
    // Include SweetAlert and jQuery
    echo '
    <script src="https://code.jquery.com/jquery-2.1.3.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert-dev.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.css">';
    
    // Retrieve form data
    $m_username = $_POST['m_username'];
    $m_password = $_POST['m_password'];
    $m_level = $_POST['m_level'];
    $m_name = $_POST['m_name'];
    $m_email = $_POST['m_email'];
    $m_tel = $_POST['m_tel'];
    $m_address = $_POST['m_address'];
    $m_status = $_POST['m_status'];
    // Check if username already exists
    $stmt_check = $conn->prepare("SELECT * FROM tbl_member WHERE m_username = :m_username");
    $stmt_check->bindParam(':m_username', $m_username, PDO::PARAM_STR);
    $stmt_check->execute();
    $row_count = $stmt_check->rowCount();

    if ($row_count > 0) {
        // Username already exists
        echo '<script>
             setTimeout(function() {
              swal({
                  title: "ข้อมูลซ้ำ",
                  text: "มีชื่อผู้ใช้นี้อยู่ในระบบแล้ว",
                  type: "error"
              }, function() {
                  window.location = "register.php"; 
              });
            }, 1000);
        </script>';
    } else {
        // Username is unique, proceed with insertion
        // Upload file handling...
        $date1 = date("Ymd_His");
        $numrand = (mt_rand());
        $upload=$_FILES['m_img']['name'];

        if($upload !='') {
            $typefile = strrchr($_FILES['m_img']['name'],".");
            if($typefile =='.jpg' || $typefile  =='.jpg' || $typefile  =='.png'){
                $path="admin/m_img/";
                $newname = $numrand.$date1.$typefile;
                $path_copy=$path.$newname;
                move_uploaded_file($_FILES['m_img']['tmp_name'],$path_copy);

                // SQL insertion...
                $stmt_insert = $conn->prepare("INSERT INTO tbl_member (m_username, m_password, m_level, m_name, m_email, m_tel, m_address, m_status, m_img)
                VALUES (:m_username, :m_password, :m_level, :m_name, :m_email, :m_tel, :m_address, :m_status, '$newname')");
                $stmt_insert->bindParam(':m_username', $m_username, PDO::PARAM_STR);
                $stmt_insert->bindParam(':m_password', $m_password, PDO::PARAM_STR);
                $stmt_insert->bindParam(':m_level', $m_level, PDO::PARAM_STR);
                $stmt_insert->bindParam(':m_name', $m_name, PDO::PARAM_STR);
                $stmt_insert->bindParam(':m_email', $m_email, PDO::PARAM_STR);
                $stmt_insert->bindParam(':m_tel', $m_tel, PDO::PARAM_STR);
                $stmt_insert->bindParam(':m_address', $m_address, PDO::PARAM_STR);
                $stmt_insert->bindParam(':m_status', $m_status, PDO::PARAM_INT);
                $result = $stmt_insert->execute();

                // Response handling...
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
                              window.location = "login.php"; // Redirect to admin.php
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
                              window.location = "login.php"; // Redirect to admin.php
                          });
                        }, 1000);
                    </script>';
                }
            }else{ // Invalid file type
                echo '<script>
                         setTimeout(function() {
                          swal({
                              title: "คุณอัพโหลดไฟล์ไม่ถูกต้อง",
                              type: "error"
                          }, function() {
                              window.location = "register.php"; // Redirect to admin.php
                          });
                        }, 1000);
                    </script>';
            }
        } else { // No file uploaded
            echo '<script>
                         setTimeout(function() {
                          swal({
                              title: "กรุณาเลือกไฟล์",
                              type: "error"
                          }, function() {
                              window.location = "register.php"; // Redirect to admin.php
                          });
                        }, 1000);
                    </script>';
        }
    }

    $conn = null; // Close database connection
}
?>
