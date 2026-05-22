<?php
require '../../utilities/database/database.php';
header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"), true);

$department_id = $data['department_id'] ?? null;
$company_id = $data['company_id'] ?? null;
$department_name = $data['department_name'] ?? null;

if (!$company_id || !$department_name) {
    echo json_encode(["success" => false, "message" => "Missing fields"]);
    exit;
}

try {

    if ($department_id) {
        // UPDATE
        $stmt = $conn->prepare("
            UPDATE departments
            SET department_name = ?
            WHERE department_id = ?
        ");
        $stmt->execute([$department_name, $department_id]);

    } else {
        // CREATE
        $stmt = $conn->prepare("
            INSERT INTO departments (department_name, company_id)
            VALUES (?, ?)
        ");
        $stmt->execute([$department_name, $company_id]);
    }

    echo json_encode(["success" => true]);

} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
}