<?php
echo '<meta charset="utf-8">';
include('../condb.php');

$m_id = $_POST["m_id"]; // No need to escape since using prepared statements

// Prepare the SQL statement to fetch m_status
$query_status = "SELECT m_status FROM tbl_member WHERE m_id = :m_id";
$stmt = $conn->prepare($query_status);
$stmt->bindParam(':m_id', $m_id);
$stmt->execute();

// Fetch the result
$row = $stmt->fetch(PDO::FETCH_ASSOC);
$st = $row['m_status'];

// Set $st_full based on $st
$st_full = ($st != 1) ? 1 : 0;

// Prepare the SQL statement to update m_status
$sql_update = "UPDATE tbl_member SET m_status = :st_full WHERE m_id = :m_id";
$stmt = $conn->prepare($sql_update);
$stmt->bindParam(':st_full', $st_full);
$stmt->bindParam(':m_id', $m_id);
$stmt->execute();

echo "Status updated successfully.";

?>
