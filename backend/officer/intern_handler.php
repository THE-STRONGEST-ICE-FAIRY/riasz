<?php
header('Content-Type: application/json');
ini_set('display_errors', 0);
ini_set('log_errors', 1);

require '../../utilities/PHPMailer/PHPMailer.php';
require '../../utilities/PHPMailer/SMTP.php';
require '../../utilities/PHPMailer/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../../utilities/database/database.php';

// Fetch intern data
$sql = "
SELECT 
    CONCAT(u.user_first_name, ' ', u.user_last_name) AS full_name,
    su.schooluser_id,
    p.program_name,
    s.school_name,
    u.user_email AS email,
    i.intern_birthdate AS birthdate,
    i.intern_gender AS gender,
    i.intern_city AS city,
    i.intern_province_or_state AS province_or_state,
    i.intern_postal_code AS postal_code,
    i.intern_country AS country,
    su.schooluser_password
FROM interns i
JOIN schoolusers su ON i.schooluser_id = su.schooluser_id
JOIN users u ON su.user_id = u.user_id
JOIN programs p ON i.program_id = p.program_id
JOIN schools s ON p.school_id = s.school_id
";

$stmt = $conn->query($sql);
$interns = [];

while ($row = $stmt->fetch()) {
    // Determine account status
    if (is_null($row['schooluser_password'])) {
        $status = 'Pending';
    } elseif (
        empty($row['full_name']) ||
        empty($row['schooluser_id']) ||
        empty($row['program_name']) ||
        empty($row['school_name']) ||
        empty($row['email']) ||
        empty($row['birthdate']) ||
        empty($row['gender']) ||
        empty($row['city']) ||
        empty($row['province_or_state']) ||
        empty($row['postal_code']) ||
        empty($row['country'])
    ) {
        $status = 'Incomplete';
    } else {
        $status = 'OK';
    }

    $interns[] = [
        'full_name' => $row['full_name'],
        'schooluser_id' => $row['schooluser_id'],
        'program_name' => $row['program_name'],
        'school_name' => $row['school_name'],
        'email' => $row['email'],
        'birthdate' => $row['birthdate'],
        'gender' => $row['gender'],
        'city' => $row['city'],
        'province_or_state' => $row['province_or_state'],
        'postal_code' => $row['postal_code'],
        'country' => $row['country'],
        'account_status' => $status
    ];
}

echo json_encode($interns);

exit;