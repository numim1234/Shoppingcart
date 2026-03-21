<?php
echo '<meta charset="utf-8">';
include('../condb.php');

$p_id = $_POST["p_id"]; // No need to escape since using prepared statements

// Prepare the SQL statement to fetch m_status
$query_status = "SELECT p_status FROM tbl_product WHERE p_id = :p_id";
$stmt = $conn->prepare($query_status);
$stmt->bindParam(':p_id', $p_id);
$stmt->execute();

// Fetch the result
$row = $stmt->fetch(PDO::FETCH_ASSOC);

// Prepare the SQL statement to update p_status (toggle between 0 and 1)
$sql_update = "UPDATE tbl_product SET p_status = CASE WHEN p_status = 1 THEN 0 ELSE 1 END WHERE p_id = :p_id";
$stmt = $conn->prepare($sql_update);
$stmt->bindParam(':p_id', $p_id);
$stmt->execute();

echo "Status updated successfully.";
