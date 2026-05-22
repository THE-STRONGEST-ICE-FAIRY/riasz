<?php
require '../../utilities/database/database.php';
header("Content-Type: application/json");

$department_id = $_GET['department_id'] ?? null;

if (!$department_id) {
    echo json_encode(["success" => false, "message" => "Missing department_id"]);
    exit;
}

$stmt = $conn->prepare("
    SELECT 
        u.user_id,
        u.user_first_name,
        u.user_last_name,
        u.user_email,
        s.supervisor_jobrole,
		s.contact_no
    FROM supervisors s
    JOIN users u ON s.user_id = u.user_id
    WHERE s.department_id = ?
");

$stmt->execute([$department_id]);

$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode([
    "success" => true,
    "data" => $data
]);