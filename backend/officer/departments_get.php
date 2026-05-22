<?php
require '../../utilities/database/database.php';
header("Content-Type: application/json");

$company_id = $_GET['company_id'] ?? null;

if (!$company_id) {
    echo json_encode(["success" => false, "message" => "Missing company_id"]);
    exit;
}

$stmt = $conn->prepare("
    SELECT department_id, department_name
    FROM departments
    WHERE company_id = ?
    ORDER BY department_name ASC
");

$stmt->execute([$company_id]);

$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode([
    "success" => true,
    "data" => $data
]);