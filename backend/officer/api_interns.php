<?php
header('Content-Type: application/json');
ini_set('display_errors', 0);
ini_set('log_errors', 1);

require '../../utilities/PHPMailer/PHPMailer.php';
require '../../utilities/PHPMailer/SMTP.php';
require '../../utilities/PHPMailer/Exception.php';
use PHPMailer\PHPMailer\PHPMailer;
// Aliased so it doesn't shadow PHP's own Exception / PDOException handling.
use PHPMailer\PHPMailer\Exception as MailException;

require '../../utilities/database/database.php';

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

// GET: Fetch Intern Masterlist
if ($method === 'GET') {
    $sql = "
    SELECT 
        i.intern_id,
        u.user_first_name AS first_name,
        u.user_last_name AS last_name,
        CONCAT(u.user_first_name, ' ', u.user_last_name) AS full_name,
        su.schooluser_given_id,
        p.program_id,
        p.program_name,
        s.school_id,
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
        if (is_null($row['schooluser_password'])) {
            $status = 'Pending';
        } elseif (empty($row['full_name']) || empty($row['schooluser_given_id']) || empty($row['program_name']) || empty($row['school_name']) || empty($row['email']) || empty($row['birthdate']) || empty($row['gender']) || empty($row['city']) || empty($row['province_or_state']) || empty($row['postal_code']) || empty($row['country'])) {
            $status = 'Incomplete';
        } else {
            $status = 'OK';
        }

        $interns[] = [
            'intern_id' => $row['intern_id'],
            'first_name' => $row['first_name'],
            'last_name' => $row['last_name'],
            'full_name' => $row['full_name'],
            'schooluser_given_id' => $row['schooluser_given_id'],
            'program_name' => $row['program_name'],
            'school_name' => $row['school_name'],
            'email' => $row['email'],
            'birthdate' => $row['birthdate'],
            'gender' => $row['gender'],
            'city' => $row['city'],
            'province_or_state' => $row['province_or_state'],
            'postal_code' => $row['postal_code'],
            'country' => $row['country'],
            'account_status' => $status,
            'school_id' => $row['school_id'],
            'program_id' => $row['program_id'],
        ];
    }
    echo json_encode($interns);
    exit;
}

// POST Handling
$inputData = json_decode(file_get_contents("php://input"), true);

if ($action === 'add') {
    if (!isset($inputData["interns"]) || !is_array($inputData["interns"])) {
        echo json_encode(["success" => false, "message" => "Invalid payload"]);
        exit;
    }

    try {
        $conn->beginTransaction();
        $errors = [];
        $successCount = 0;

        foreach ($inputData["interns"] as $i => $row) {
            $first = $row["firstName"];
            $last = $row["lastName"];
            $email = $row["email"];
            $schoolUserGivenId = $row["schoolUserId"];
            $schoolName = $row["schoolName"];
            $programName = $row["programName"];
            $birthdate = $row["birthdate"];
            $gender = $row["gender"];
            $city = $row["city"];
            $province = $row["province"];
            $postal = $row["postal"];
            $country = $row["country"];

            $stmt = $conn->prepare("
                SELECT p.program_id, s.school_id, s.school_name
                FROM programs p
                INNER JOIN schools s ON p.school_id = s.school_id
                WHERE p.program_name = :program_name LIMIT 1
            ");
            $stmt->execute(["program_name" => $programName]);
            $programData = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$programData) {
                $errors[] = ["row" => $i, "message" => "Invalid program name", "field" => "program_name"];
                continue;
            }
            if ($programData["school_name"] !== $schoolName) {
                $errors[] = ["row" => $i, "message" => "School does not match program", "field" => "school_name"];
                continue;
            }
            $programId = $programData["program_id"];

            $stmt = $conn->prepare("INSERT INTO users (user_first_name, user_last_name, user_email, user_role, user_is_archived) VALUES (:first, :last, :email, 'intern', 0)");
            $stmt->execute(["first" => $first, "last" => $last, "email" => $email]);
            $userId = $conn->lastInsertId();

            $stmt = $conn->prepare("INSERT INTO schoolusers (user_id, schooluser_given_id, schooluser_password) VALUES (:user_id, :given_id, NULL)");
            $stmt->execute(["user_id" => $userId, "given_id" => $schoolUserGivenId]);
            $schoolUserId = $conn->lastInsertId();

            try {
                $token = bin2hex(random_bytes(32));
                $expiry = date("Y-m-d H:i:s", strtotime("+24 hours"));
                $stmt = $conn->prepare("INSERT INTO passwordresets (schooluser_id, passreset_token, passreset_date_created, passreset_date_expiry, passreset_is_used) VALUES (?, ?, NOW(), ?, 0)");
                $stmt->execute([$schoolUserId, $token, $expiry]);

                $mail = new PHPMailer(true);
                $mail->isSMTP();
                $mail->Host = '127.0.0.1';
                $mail->SMTPAuth = false;
                $mail->Port = 1025;
                $mail->setFrom('no-reply@school.local', 'School System');
                $mail->addAddress($email, $first);
                $link = "http://localhost/backend/login/resetpassword_be.php?token=$token";
                $mail->isHTML(true);
                $mail->Subject = "Create Your Password";
                $mail->Body = "<h3>Welcome, $first</h3><p>Please click the link below to set your password:</p><a href='$link'>$link</a><p>This link will expire in 24 hours.</p>";
                $mail->send();
            } catch (MailException $e) {
                $errors[] = ["row" => $i, "message" => "Email/token error: " . $e->getMessage(), "field" => "email"];
            }

            $stmt = $conn->prepare("INSERT INTO interns (schooluser_id, program_id, intern_birthdate, intern_gender, intern_city, intern_province_or_state, intern_postal_code, intern_country) VALUES (:schooluser_id, :program_id, :birthdate, :gender, :city, :province, :postal, :country)");
            $stmt->execute([
                "schooluser_id" => $schoolUserId, "program_id" => $programId,
                "birthdate" => $birthdate ?: null, "gender" => $gender ?: null,
                "city" => $city ?: null, "province" => $province ?: null,
                "postal" => $postal ?: null, "country" => $country ?: null
            ]);
            $successCount++;
        }

        $conn->commit();
        echo json_encode(["success" => count($errors) === 0, "inserted" => $successCount, "errors" => $errors]);
    } catch (Throwable $inner) {
        if ($conn->inTransaction()) $conn->rollBack();
        $failedRow = $i ?? 0;
        $error = ["row" => $failedRow, "message" => "Database error occurred", "field" => "database"];
        if (str_contains($inner->getMessage(), "users.user_email")) {
            $error = ["row" => $failedRow, "message" => "Email already exists", "field" => "email"];
        } else if (str_contains($inner->getMessage(), "schooluser_given_id")) {
            $error = ["row" => $failedRow, "message" => "School User ID already exists", "field" => "school_user_id"];
        }
        echo json_encode(["success" => false, "errors" => [$error]]);
    }
    exit;
}

if ($action === 'update') {
    if (!$inputData) {
        echo json_encode(["success" => false, "message" => "Invalid input"]);
        exit;
    }
    try {
        $conn->beginTransaction();
        $stmt = $conn->prepare("SELECT i.schooluser_id, u.user_id, i.program_id FROM interns i JOIN schoolusers su ON su.schooluser_id = i.schooluser_id JOIN users u ON u.user_id = su.user_id WHERE i.intern_id = ?");
        $stmt->execute([$inputData['intern_id']]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) throw new Exception("Intern not found");

        $user_id = $row['user_id'];
        $schooluser_id = $row['schooluser_id'];

        $stmt = $conn->prepare("UPDATE users SET user_first_name = ?, user_last_name = ?, user_email = ?, user_date_updated = NOW() WHERE user_id = ?");
        $stmt->execute([$inputData['first_name'], $inputData['last_name'], $inputData['email'], $user_id]);

        $stmt = $conn->prepare("UPDATE schoolusers SET schooluser_given_id = ? WHERE schooluser_id = ?");
        $stmt->execute([$inputData['schooluser_given_id'], $schooluser_id]);

        $stmt = $conn->prepare("SELECT program_id FROM programs WHERE program_id = ?");
        $stmt->execute([$inputData['program_id']]);
        $program = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$program) throw new Exception("Program not found");

        $stmt = $conn->prepare("UPDATE interns SET program_id = ?, intern_birthdate = ?, intern_gender = ?, intern_city = ?, intern_province_or_state = ?, intern_postal_code = ?, intern_country = ? WHERE intern_id = ?");
        $stmt->execute([
            $program['program_id'], $inputData['birthdate'], $inputData['gender'],
            $inputData['city'], $inputData['province_or_state'], $inputData['postal_code'],
            $inputData['country'], $inputData['intern_id']
        ]);

        $conn->commit();
        echo json_encode(["success" => true]);
    } catch (Throwable $e) {
        if ($conn->inTransaction()) $conn->rollBack();
        echo json_encode(["success" => false, "message" => $e->getMessage()]);
    }
    exit;
}
