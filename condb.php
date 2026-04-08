<?php
$servername = "127.0.0.1";
$username = "root";
$password = "root";

try {
  $conn = new PDO(
    "mysql:host=$servername;port=3306;dbname=shopping_pdo;charset=utf8",
    $username,
    $password
  );
  $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
  echo "Connection failed: " . $e->getMessage();
}

date_default_timezone_set('Asia/Bangkok');
