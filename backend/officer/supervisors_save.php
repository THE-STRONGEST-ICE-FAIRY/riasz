<?php
require '../../utilities/database/database.php';
header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"), true);

$user_id = $data['user_id'] ?? null;
$department_id = $data['department_id'] ?? null;
$first_name = $data['first_name'] ?? null;
$last_name = $data['last_name'] ?? null;
$email = $data['email'] ?? null;
$jobrole = $data['jobrole'] ?? null;
$contact_no = $data['contact_no'] ?? null;

try {

    if ($user_id) {

        // 🔥 UPDATE MODE

        $stmt = $conn->prepare("
            UPDATE users
            SET user_first_name = ?,
                user_last_name = ?,
                user_email = ?,
                user_date_updated = NOW()
            WHERE user_id = ?
        ");
        $stmt->execute([$first_name, $last_name, $email, $user_id]);

        $stmt2 = $conn->prepare("
            UPDATE supervisors
            SET supervisor_jobrole = ?,
				contact_no = ?
            WHERE user_id = ?
        ");
        $stmt2->execute([$jobrole, $contact_no, $user_id]);

    } 
	else {

		if (!$department_id || !$first_name || !$last_name || !$email) {
			echo json_encode(["success" => false, "message" => "Missing fields"]);
			exit;
		}

        // ➕ CREATE MODE

        $stmt = $conn->prepare("
            INSERT INTO users (
                user_first_name,
                user_last_name,
                user_email,
                user_role,
                user_is_archived
            ) VALUES (?, ?, ?, 'supervisor', 0)
        ");

        $stmt->execute([$first_name, $last_name, $email]);

        $new_user_id = $conn->lastInsertId();

        $stmt2 = $conn->prepare("
            INSERT INTO supervisors (
                user_id,
                supervisor_jobrole,
                department_id,
				contact_no
            ) VALUES (?, ?, ?, ?)
        ");

        $stmt2->execute([$new_user_id, $jobrole, $department_id, $contact_no]);
    }

    echo json_encode(["success" => true, "contact_no" => $contact_no]);

} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
}