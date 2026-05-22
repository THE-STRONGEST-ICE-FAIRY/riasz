<?php
require '../../utilities/database/database.php';

header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['companies']) || !is_array($data['companies'])) {
    echo json_encode([
        "success" => false,
        "message" => "Invalid payload"
    ]);
    exit;
}

$companies = $data['companies'];

$inserted = 0;
$errors = [];

$stmt = $conn->prepare("
    INSERT INTO companies (
        company_name,
        company_email,
        company_website,
        company_address,
        intern_allowance,
        partnership_status,
        max_internslots,
        revenue_growth,
        profit_margins,
        roi,
        roa,
        company_date_added
    ) VALUES (
        :company_name,
        :company_email,
        :company_website,
        :company_address,
        :intern_allowance,
        :partnership_status,
        :max_internslots,
        :revenue_growth,
        :profit_margins,
        :roi,
        :roa,
        NOW()
    )
");

foreach ($companies as $index => $row) {

    $rowErrors = [];

    // ---------------- REQUIRED VALIDATION ----------------
    if (empty($row['company_name'])) {
        $rowErrors[] = [
            "row" => $index,
            "field" => "company_name",
            "message" => "Company name is required"
        ];
    }

    if (empty($row['company_email'])) {
        $rowErrors[] = [
            "row" => $index,
            "field" => "company_email",
            "message" => "Company email is required"
        ];
    }

    // email format check
    if (!empty($row['company_email']) && !filter_var($row['company_email'], FILTER_VALIDATE_EMAIL)) {
        $rowErrors[] = [
            "row" => $index,
            "field" => "company_email",
            "message" => "Invalid email format"
        ];
    }

    // ---------------- SKIP IF ERRORS ----------------
    if (!empty($rowErrors)) {
        $errors = array_merge($errors, $rowErrors);
        continue;
    }

    // ---------------- SAFE INSERT ----------------
    try {
        $stmt->execute([
            ":company_name"       => $row['company_name'],
            ":company_email"      => $row['company_email'],
            ":company_website"    => $row['company_website'] ?? null,
            ":company_address"    => $row['company_address'] ?? null,
            ":intern_allowance"   => clean($row['intern_allowance'] ?? null),
            ":partnership_status" => $row['partnership_status'] ?? null,
            ":max_internslots"    => clean($row['max_internslots'] ?? null),
			":revenue_growth"     => clean($row['revenue_growth'] ?? null),
			":profit_margins"     => clean($row['profit_margins'] ?? null),
			":roi"                => clean($row['roi'] ?? null),
			":roa"                => clean($row['roa'] ?? null)
        ]);

        $inserted++;

    } catch (PDOException $e) {
        $errors[] = [
            "row" => $index,
            "field" => "database",
            "message" => $e->getMessage()
        ];
    }
}

// ---------------- RESPONSE ----------------
if (!empty($errors)) {
    echo json_encode([
        "success" => false,
        "inserted" => $inserted,
        "errors" => $errors
    ]);
    exit;
}

echo json_encode([
    "success" => true,
    "inserted" => $inserted
]);

function clean($value) {
    if (!isset($value)) return null;

    $value = trim($value);

    if ($value === "") return null;

    return $value;
}