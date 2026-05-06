<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require '../../utilities/database/database.php';

$school_id = $_GET["school_id"] ?? null;

if (!$school_id) {
    echo json_encode([]);
    exit;
}

$stmt = $conn->prepare("
    SELECT program_id, program_name 
    FROM programs 
    WHERE school_id = ?
");

$stmt->execute([$school_id]);

echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));

?>
