<?php
require '../../utilities/database/database.php';
header("Content-Type: application/json");

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

function clean($value) {
    if (!isset($value)) return null;
    $value = trim($value);
    return $value === "" ? null : $value;
}

// GET: Fetch Companies list with department counts
if ($method === 'GET') {
    try {
        $sql = "
            SELECT 
                c.company_id, c.company_name, c.company_email, c.company_website,
                c.company_address, c.intern_allowance, c.partnership_status,
                c.max_internslots, c.revenue_growth, c.profit_margins, c.roi, c.roa,
                COUNT(d.department_id) AS departments
            FROM companies c
            LEFT JOIN departments d ON c.company_id = d.company_id
            GROUP BY c.company_id
            ORDER BY c.company_name ASC
        ";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode(["success" => true, "data" => $data]);
    } catch (Exception $e) {
        echo json_encode(["success" => false, "message" => $e->getMessage()]);
    }
    exit;
}

$inputData = json_decode(file_get_contents("php://input"), true);

// POST: Add Multiple Companies
if ($action === 'add') {
    if (!isset($inputData['companies']) || !is_array($inputData['companies'])) {
        echo json_encode(["success" => false, "message" => "Invalid payload"]);
        exit;
    }

    $companies = $inputData['companies'];
    $inserted = 0;
    $errors = [];

    $stmt = $conn->prepare("
        INSERT INTO companies (company_name, company_email, company_website, company_address, intern_allowance, partnership_status, max_internslots, revenue_growth, profit_margins, roi, roa, company_date_added) 
        VALUES (:company_name, :company_email, :company_website, :company_address, :intern_allowance, :partnership_status, :max_internslots, :revenue_growth, :profit_margins, :roi, :roa, NOW())
    ");

    foreach ($companies as $index => $row) {
        $rowErrors = [];
        if (empty($row['company_name'])) $rowErrors[] = ["row" => $index, "field" => "company_name", "message" => "Company name is required"];
        if (empty($row['company_email'])) $rowErrors[] = ["row" => $index, "field" => "company_email", "message" => "Company email is required"];
        if (!empty($row['company_email']) && !filter_var($row['company_email'], FILTER_VALIDATE_EMAIL)) {
            $rowErrors[] = ["row" => $index, "field" => "company_email", "message" => "Invalid email format"];
        }

        if (!empty($rowErrors)) {
            $errors = array_merge($errors, $rowErrors);
            continue;
        }

        try {
            $stmt->execute([
                ":company_name" => $row['company_name'], ":company_email" => $row['company_email'],
                ":company_website" => $row['company_website'] ?? null, ":company_address" => $row['company_address'] ?? null,
                ":intern_allowance" => clean($row['intern_allowance'] ?? null), ":partnership_status" => $row['partnership_status'] ?? null,
                ":max_internslots" => clean($row['max_internslots'] ?? null), ":revenue_growth" => clean($row['revenue_growth'] ?? null),
                ":profit_margins" => clean($row['profit_margins'] ?? null), ":roi" => clean($row['roi'] ?? null), ":roa" => clean($row['roa'] ?? null)
            ]);
            $inserted++;
        } catch (PDOException $e) {
            $errors[] = ["row" => $index, "field" => "database", "message" => $e->getMessage()];
        }
    }

    if (!empty($errors)) {
        echo json_encode(["success" => false, "inserted" => $inserted, "errors" => $errors]);
        exit;
    }
    echo json_encode(["success" => true, "inserted" => $inserted]);
    exit;
}

// POST: Update Company
if ($action === 'update') {
    if (!$inputData) {
        echo json_encode(["success" => false, "message" => "Invalid JSON"]);
        exit;
    }
    try {
        $stmt = $conn->prepare("
            UPDATE companies SET company_name = ?, company_email = ?, company_website = ?, company_address = ?, intern_allowance = ?, partnership_status = ?, max_internslots = ?, revenue_growth = ?, profit_margins = ?, roi = ?, roa = ? WHERE company_id = ?
        ");
        $stmt->execute([
            $inputData['company_name'], $inputData['company_email'], $inputData['company_website'],
            $inputData['company_address'], $inputData['intern_allowance'], $inputData['partnership_status'],
            $inputData['max_internslots'], $inputData['revenue_growth'], $inputData['profit_margins'],
            $inputData['roi'], $inputData['roa'], $inputData['company_id']
        ]);
        echo json_encode(["success" => true]);
    } catch (Exception $e) {
        echo json_encode(["success" => false, "message" => $e->getMessage()]);
    }
    exit;
}
