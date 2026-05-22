<?php
require '../../utilities/database/database.php';
header("Content-Type: application/json");

try {
    $sql = "
        SELECT 
            c.company_id,
            c.company_name,
            c.company_email,
            c.company_website,
            c.company_address,
            c.intern_allowance,
            c.partnership_status,
            c.max_internslots,
            c.revenue_growth,
            c.profit_margins,
            c.roi,
            c.roa,
            COUNT(d.department_id) AS departments
        FROM companies c
        LEFT JOIN departments d ON c.company_id = d.company_id
        GROUP BY c.company_id
        ORDER BY c.company_name ASC
    ";

    $stmt = $conn->prepare($sql);
    $stmt->execute();

    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "success" => true,
        "data" => $data
    ]);

} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
}