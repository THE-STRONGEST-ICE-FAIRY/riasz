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

if (!isset($data["interns"]) || !is_array($data["interns"])) {
    echo json_encode(["success" => false, "message" => "Invalid payload"]);
    exit;
}

try {
    $conn->beginTransaction();
	
	$errors = [];
	$successCount = 0;

    foreach ($data["interns"] as $i => $row) {

        // -------------------------
        // INPUTS
        // -------------------------
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

        // -------------------------
        // PROGRAM + SCHOOL VALIDATION
        // -------------------------
        $stmt = $conn->prepare("
            SELECT p.program_id, s.school_id, s.school_name
            FROM programs p
            INNER JOIN schools s ON p.school_id = s.school_id
            WHERE p.program_name = :program_name
            LIMIT 1
        ");

        $stmt->execute(["program_name" => $programName]);
        $programData = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$programData) {
            $errors[] = [
				"row" => $i,
				"message" => "Invalid program name",
				"field" => "program_name"
			];
			continue;
        }

        if ($programData["school_name"] !== $schoolName) {
            $errors[] = [
				"row" => $i,
				"message" => "School does not match program",
				"field" => "school_name"
			];
			continue;
        }

        $programId = $programData["program_id"];

        // -------------------------
        // 1. INSERT USER
        // -------------------------
        $stmt = $conn->prepare("
            INSERT INTO users 
            (user_first_name, user_last_name, user_email, user_role, user_is_archived)
            VALUES
            (:first, :last, :email, 'intern', 0)
        ");

        $stmt->execute([
            "first" => $first,
            "last" => $last,
            "email" => $email
        ]);

        $userId = $conn->lastInsertId();

        // -------------------------
        // 2. INSERT SCHOOL USER
        // -------------------------
        $stmt = $conn->prepare("
            INSERT INTO schoolusers
            (user_id, schooluser_given_id, schooluser_password)
            VALUES
            (:user_id, :given_id, NULL)
        ");

        $stmt->execute([
            "user_id" => $userId,
            "given_id" => $schoolUserGivenId
        ]);

        $schoolUserId = $conn->lastInsertId();

		// -------------------------
		// 2.5 PASSWORD RESET TOKEN + EMAIL
		// -------------------------
		try {
			// Generate reset token
			$token = bin2hex(random_bytes(32));
			$expiry = date("Y-m-d H:i:s", strtotime("+24 hours"));

			$stmt = $conn->prepare("
				INSERT INTO passwordresets
				(schooluser_id, passreset_token, passreset_date_created, passreset_date_expiry, passreset_is_used)
				VALUES (?, ?, NOW(), ?, 0)
			");

			$stmt->execute([$schoolUserId, $token, $expiry]);

			// Send email via Mailpit
			$mail = new PHPMailer(true);

			$mail->isSMTP();
			$mail->Host = '127.0.0.1';
			$mail->SMTPAuth = false;
			$mail->Port = 1025;

			$mail->setFrom('no-reply@school.local', 'School System');
			$mail->addAddress($email, $first);

			$link = "http://localhost/pages/login/resetpassword_be.php?token=$token";

			$mail->isHTML(true);
			$mail->Subject = "Create Your Password";
			$mail->Body = "
				<h3>Welcome, $first</h3>
				<p>Please click the link below to set your password:</p>
				<a href='$link'>$link</a>
				<p>This link will expire in 24 hours.</p>
			";

			$mail->send();
		} catch (Exception $e) {
			$errors[] = [
				"row" => $i,
				"message" => "Email/token error: " . $e->getMessage(),
				"field" => "email"
			];
		}

        // -------------------------
        // 3. INSERT INTERN
        // -------------------------
        $stmt = $conn->prepare("
            INSERT INTO interns
            (schooluser_id, program_id, intern_birthdate, intern_gender,
             intern_city, intern_province_or_state, intern_postal_code, intern_country)
            VALUES
            (:schooluser_id, :program_id, :birthdate, :gender,
             :city, :province, :postal, :country)
        ");

        $stmt->execute([
            "schooluser_id" => $schoolUserId,
            "program_id" => $programId,
            "birthdate" => $birthdate ?: null,
            "gender" => $gender ?: null,
            "city" => $city ?: null,
            "province" => $province ?: null,
            "postal" => $postal ?: null,
            "country" => $country ?: null
        ]);
		
		$successCount++;
    }

    $conn->commit();

    echo json_encode([
		"success" => count($errors) === 0,
		"inserted" => $successCount,
		"errors" => $errors
	]);
	
	exit;

} catch (Throwable $inner) {

    if ($conn->inTransaction()) {
        $conn->rollBack();
    }

    // fallback row index
    $failedRow = $i ?? 0;

    // default generic error
    $error = [
        "row" => $failedRow,
        "message" => "Database error occurred",
        "field" => "database"
    ];

    // duplicate email
    if (str_contains($inner->getMessage(), "users.user_email")) {
        $error = [
            "row" => $failedRow,
            "message" => "Email already exists",
            "field" => "email"
        ];
    }

    // duplicate school user ID
    else if (str_contains($inner->getMessage(), "schooluser_given_id")) {
        $error = [
            "row" => $failedRow,
            "message" => "School User ID already exists",
            "field" => "school_user_id"
        ];
    }

    echo json_encode([
        "success" => false,
        "errors" => [$error]
    ]);

    exit;
}

exit;