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

$data = json_decode(file_get_contents("php://input"), true);

if (!$data) {
    echo json_encode(["success" => false, "message" => "Invalid input"]);
    exit;
}

try {
    $conn->beginTransaction();

    // 1. Get intern + related IDs
    $stmt = $conn->prepare("
        SELECT i.schooluser_id, u.user_id, i.program_id
        FROM interns i
        JOIN schoolusers su ON su.schooluser_id = i.schooluser_id
        JOIN users u ON u.user_id = su.user_id
        WHERE i.intern_id = ?
    ");
    $stmt->execute([$data['intern_id']]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
        throw new Exception("Intern not found");
    }

    $user_id = $row['user_id'];
    $schooluser_id = $row['schooluser_id'];

    // 2. Update users
    $stmt = $conn->prepare("
        UPDATE users 
        SET user_first_name = ?, user_last_name = ?, user_email = ?, user_date_updated = NOW()
        WHERE user_id = ?
    ");
    $stmt->execute([
        $data['first_name'],
        $data['last_name'],
        $data['email'],
        $user_id
    ]);

    // 3. Get program_id
    $stmt = $conn->prepare("SELECT program_id FROM programs WHERE program_name = ?");
    $stmt->execute([$data['program_name']]);
    $program = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$program) {
        throw new Exception("Program not found");
    }

    // 4. Update interns
    $stmt = $conn->prepare("
        UPDATE interns 
        SET program_id = ?, intern_birthdate = ?, intern_gender = ?, 
            intern_city = ?, intern_province_or_state = ?, 
            intern_postal_code = ?, intern_country = ?
        WHERE intern_id = ?
    ");

    $stmt->execute([
        $program['program_id'],
        $data['birthdate'],
        $data['gender'],
        $data['city'],
        $data['province_or_state'],
        $data['postal_code'],
        $data['country'],
        $data['intern_id']
    ]);

    $conn->commit();

    echo json_encode(["success" => true]);

} catch (Exception $e) {
    $conn->rollBack();
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}

exit;