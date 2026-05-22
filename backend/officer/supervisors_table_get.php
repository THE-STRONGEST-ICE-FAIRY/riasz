<?php
require '../../utilities/database/database.php';
header("Content-Type: application/json");

try {

    $sql = "
        SELECT
            s.supervisor_id,

            u.user_id,
            u.user_first_name,
            u.user_last_name,
            u.user_email,

            s.supervisor_jobrole,
			s.contact_no,

            d.department_id,
            d.department_name,

            c.company_id,
            c.company_name

        FROM supervisors s

        INNER JOIN users u
            ON s.user_id = u.user_id

        INNER JOIN departments d
            ON s.department_id = d.department_id

        INNER JOIN companies c
            ON d.company_id = c.company_id

        ORDER BY
            u.user_first_name ASC,
            u.user_last_name ASC
    ";

    $stmt = $conn->prepare($sql);

    $stmt->execute();

    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $data = [];

    foreach ($rows as $r) {

        $data[] = [

            'supervisor_id' => $r['supervisor_id'],

            'user_id' => $r['user_id'],

            'full_name' =>
                trim(
                    $r['user_first_name'] . ' ' .
                    $r['user_last_name']
                ),

            'user_email' => $r['user_email'],

            'supervisor_jobrole' =>
                $r['supervisor_jobrole'],
				
			'contact_no' => $r['contact_no'],

            'department_name' =>
                $r['department_name'],

            'company_name' =>
                $r['company_name']

        ];
    }

    echo json_encode([
        'success' => true,
        'data' => $data
    ]);

} catch (Exception $e) {

    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);

}
?>