<?php
require_once("../condb.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $contact_id       = $_POST['contact_id'] ?? '';
    $contact_name     = trim($_POST['contact_name'] ?? '');
    $contact_address  = trim($_POST['contact_address'] ?? '');
    $contact_phone    = trim($_POST['contact_phone'] ?? '');
    $contact_email    = trim($_POST['contact_email'] ?? '');
    $contact_facebook = trim($_POST['contact_facebook'] ?? '');
    $contact_line     = trim($_POST['contact_line'] ?? '');
    $contact_map      = trim($_POST['contact_map'] ?? '');

    try {
        if (!empty($contact_id)) {
            $sql = "UPDATE tbl_contact SET
                        contact_name = :contact_name,
                        contact_address = :contact_address,
                        contact_phone = :contact_phone,
                        contact_email = :contact_email,
                        contact_facebook = :contact_facebook,
                        contact_line = :contact_line,
                        contact_map = :contact_map
                    WHERE contact_id = :contact_id";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':contact_id', $contact_id, PDO::PARAM_INT);
        } else {
            $sql = "INSERT INTO tbl_contact
                        (contact_name, contact_address, contact_phone, contact_email, contact_facebook, contact_line, contact_map)
                    VALUES
                        (:contact_name, :contact_address, :contact_phone, :contact_email, :contact_facebook, :contact_line, :contact_map)";
            $stmt = $conn->prepare($sql);
        }

        $stmt->bindParam(':contact_name', $contact_name, PDO::PARAM_STR);
        $stmt->bindParam(':contact_address', $contact_address, PDO::PARAM_STR);
        $stmt->bindParam(':contact_phone', $contact_phone, PDO::PARAM_STR);
        $stmt->bindParam(':contact_email', $contact_email, PDO::PARAM_STR);
        $stmt->bindParam(':contact_facebook', $contact_facebook, PDO::PARAM_STR);
        $stmt->bindParam(':contact_line', $contact_line, PDO::PARAM_STR);
        $stmt->bindParam(':contact_map', $contact_map, PDO::PARAM_STR);

        $stmt->execute();

        header("Location: contact_form.php?success=1");
        exit();
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
        exit();
    }
} else {
    header("Location: contact_form.php");
    exit();
}
