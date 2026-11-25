<?php
require '../../utilities/database/database.php';
header("Content-Type: application/json");

$type = $_GET['type'] ?? '';

if ($type === 'schools') {
    $stmt = $conn->query("SELECT school_id, school_name FROM schools");
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    exit;
}

if ($type === 'programs') {
    $stmt = $conn->query("SELECT program_id, program_name, school_id FROM programs");
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    exit;
}

echo json_encode(["success" => false, "message" => "Invalid lookup type"]);
