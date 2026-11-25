<?php
require '../../utilities/database/database.php';
header("Content-Type: application/json");

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

// GET Endpoints
if ($method === 'GET') {
    if ($action === 'get_departments') {
        $company_id = $_GET['company_id'] ?? null;
        if (!$company_id) { echo json_encode(["success" => false, "message" => "Missing company_id"]); exit; }
        
        $stmt = $conn->prepare("SELECT department_id, department_name FROM departments WHERE company_id = ? ORDER BY department_name ASC");
        $stmt->execute([$company_id]);
        echo json_encode(["success" => true, "data" => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
        exit;
    }
    
    if ($action === 'get_supervisors_by_dept') {
        $department_id = $_GET['department_id'] ?? null;
        if (!$department_id) { echo json_encode(["success" => false, "message" => "Missing department_id"]); exit; }

        $stmt = $conn->prepare("SELECT u.user_id, u.user_first_name, u.user_last_name, u.user_email, s.supervisor_jobrole, s.contact_no FROM supervisors s JOIN users u ON s.user_id = u.user_id WHERE s.department_id = ?");
        $stmt->execute([$department_id]);
        echo json_encode(["success" => true, "data" => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
        exit;
    }

    if ($action === 'get_all_supervisors_table') {
        try {
            $sql = "
                SELECT s.supervisor_id, u.user_id, u.user_first_name, u.user_last_name, u.user_email, s.supervisor_jobrole, s.contact_no, d.department_id, d.department_name, c.company_id, c.company_name
                FROM supervisors s
                INNER JOIN users u ON s.user_id = u.user_id
                INNER JOIN departments d ON s.department_id = d.department_id
                INNER JOIN companies c ON d.company_id = c.company_id
                ORDER BY u.user_first_name ASC, u.user_last_name ASC
            ";
            $stmt = $conn->prepare($sql);
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $data = [];
            foreach ($rows as $r) {
                $data[] = [
                    'supervisor_id' => $r['supervisor_id'], 'user_id' => $r['user_id'],
                    'full_name' => trim($r['user_first_name'] . ' ' . $r['user_last_name']),
                    'user_email' => $r['user_email'], 'supervisor_jobrole' => $r['supervisor_jobrole'],
                    'contact_no' => $r['contact_no'], 'department_name' => $r['department_name'],
                    'company_name' => $r['company_name']
                ];
            }
            echo json_encode(['success' => true, 'data' => $data]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }
}

// POST Endpoints
$inputData = json_decode(file_get_contents("php://input"), true);

if ($action === 'save_department') {
    $department_id = $inputData['department_id'] ?? null;
    $company_id = $inputData['company_id'] ?? null;
    $department_name = $inputData['department_name'] ?? null;

    if (!$company_id || !$department_name) {
        echo json_encode(["success" => false, "message" => "Missing fields"]);
        exit;
    }

    try {
        if ($department_id) {
            $stmt = $conn->prepare("UPDATE departments SET department_name = ? WHERE department_id = ?");
            $stmt->execute([$department_name, $department_id]);
        } else {
            $stmt = $conn->prepare("INSERT INTO departments (department_name, company_id) VALUES (?, ?)");
            $stmt->execute([$department_name, $company_id]);
        }
        echo json_encode(["success" => true]);
    } catch (Exception $e) {
        echo json_encode(["success" => false, "message" => $e->getMessage()]);
    }
    exit;
}

if ($action === 'save_supervisor') {
    $user_id = $inputData['user_id'] ?? null;
    $department_id = $inputData['department_id'] ?? null;
    $first_name = $inputData['first_name'] ?? null;
    $last_name = $inputData['last_name'] ?? null;
    $email = $inputData['email'] ?? null;
    $jobrole = $inputData['jobrole'] ?? null;
    $contact_no = $inputData['contact_no'] ?? null;

    try {
        if ($user_id) {
            $stmt = $conn->prepare("UPDATE users SET user_first_name = ?, user_last_name = ?, user_email = ?, user_date_updated = NOW() WHERE user_id = ?");
            $stmt->execute([$first_name, $last_name, $email, $user_id]);

            $stmt2 = $conn->prepare("UPDATE supervisors SET supervisor_jobrole = ?, contact_no = ? WHERE user_id = ?");
            $stmt2->execute([$jobrole, $contact_no, $user_id]);
        } else {
            if (!$department_id || !$first_name || !$last_name || !$email) {
                echo json_encode(["success" => false, "message" => "Missing fields"]);
                exit;
            }
            $stmt = $conn->prepare("INSERT INTO users (user_first_name, user_last_name, user_email, user_role, user_is_archived) VALUES (?, ?, ?, 'supervisor', 0)");
            $stmt->execute([$first_name, $last_name, $email]);
            $new_user_id = $conn->lastInsertId();

            $stmt2 = $conn->prepare("INSERT INTO supervisors (user_id, supervisor_jobrole, department_id, contact_no) VALUES (?, ?, ?, ?)");
            $stmt2->execute([$new_user_id, $jobrole, $department_id, $contact_no]);
        }
        echo json_encode(["success" => true]);
    } catch (Exception $e) {
        echo json_encode(["success" => false, "message" => $e->getMessage()]);
    }
    exit;
}
