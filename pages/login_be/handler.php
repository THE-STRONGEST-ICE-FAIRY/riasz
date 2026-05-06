<?php
session_start();
date_default_timezone_set('Asia/Manila');

header("Content-Type: application/json");

require '../../utilities/database/database.php';

require '../../utilities/PHPMailer/Exception.php';
require '../../utilities/PHPMailer/PHPMailer.php';
require '../../utilities/PHPMailer/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

try {	
	$input = json_decode(file_get_contents("php://input"), true);

	$email = $_POST['email'] ?? $input['email'] ?? '';
	$password = $_POST['password'] ?? $input['password'] ?? null;
	$otp = $_POST['otp'] ?? $input['otp'] ?? null;
	$action = $_POST['action'] ?? $input['action'] ?? '';
	$user = '';

    switch ($action) {
		case 'login': {
			if (!$email) {
				echo json_encode([
					"status" => "error",
					"message" => "Missing email"
				]);
				exit;
			}
			
			$stmt = $conn->prepare("
				SELECT 
					u.user_id,
					u.user_email,
					u.user_role,
					su.schooluser_password
				FROM users u
				JOIN schoolusers su ON u.user_id = su.user_id
				WHERE u.user_email = ?
				LIMIT 1
			");

			$stmt->execute([$email]);
			$user = $stmt->fetch(PDO::FETCH_ASSOC);

			if (!$user) {
				echo json_encode([
					"status" => "error",
					"message" => "User not found"
				]);
				exit;
			}

			if ($password !== null) {
				if (!password_verify($password, $user['schooluser_password']) || $password == null) {
					echo json_encode([
						"status" => "error",
						"message" => "Invalid password"
					]);
					exit;
				}
			}

			$redirect = "";
			switch ($user['user_role']) {
				case 'admin':
					$redirect = "../admin/admin.php";
					break;

				case 'officer':
					$redirect = "../officer/_index.php";
					break;

				case 'executive':
					$redirect = "../executive/executive.php";
					break;

				case 'program':
					$redirect = "../Programdi/_index.php";
					break;

				case 'intern':
					$redirect = "../student/student.php";
					break;

				default:
					$redirect = "/login.php";
					break;
			}

			echo json_encode([
				"status" => "success",
				"role" => $user['user_role'],
				"redirect" => $redirect
			]);
		}
			break;
		
		case 'sendotp': {
			// 1. Get user
			$stmt = $conn->prepare("SELECT user_id FROM users WHERE user_email = ? AND user_is_archived = 0");
			$stmt->execute([$email]);
			$user = $stmt->fetch(PDO::FETCH_ASSOC);

			if (!$user) {
				echo json_encode(["message" => "User not found"]);
				exit;
			}

			$user_id = $user['user_id'];

			// 2. Get schooluser
			$stmt = $conn->prepare("SELECT schooluser_id FROM schoolusers WHERE user_id = ?");
			$stmt->execute([$user_id]);
			$schooluser = $stmt->fetch(PDO::FETCH_ASSOC);

			if (!$schooluser) {
				echo json_encode(["message" => "School user not found"]);
				exit;
			}

			$schooluser_id = $schooluser['schooluser_id'];

			// 3. Check existing OTP lock
			$stmt = $conn->prepare("
				SELECT otp_date_expiry, otp_attempts
				FROM onetimepasswords
				WHERE schooluser_id = ?
				ORDER BY otp_date_created DESC
				LIMIT 1
			");
			$stmt->execute([$schooluser_id]);
			$existing = $stmt->fetch(PDO::FETCH_ASSOC);

			if ($existing) {
				$now = time();
				$expiry = strtotime($existing['otp_date_expiry']);

				// 🔒 locked due to too many attempts AND still valid
				if ($existing['otp_attempts'] >= 3 && $expiry > $now) {
					$mins = ceil(($expiry - $now) / 60);

					echo json_encode([
						"status" => "locked",
						"message" => "OTP locked. Try again in {$mins} minute(s)."
					]);
					exit;
				}

				// 🧹 expired → delete old OTP
				if ($expiry <= $now) {
					$del = $conn->prepare("DELETE FROM onetimepasswords WHERE schooluser_id = ?");
					$del->execute([$schooluser_id]);
				}
			}

			// 3. Generate OTP
			$otp = rand(100000, 999999);
			$created = date("Y-m-d H:i:s");
			$expiry = date("Y-m-d H:i:s", strtotime("+10 minutes"));
			
			// 1. Delete old OTP
			$stmt = $conn->prepare("
				DELETE FROM onetimepasswords 
				WHERE schooluser_id = ?
			");
			$stmt->execute([$schooluser_id]);

			// 4. Store OTP
			$stmt = $conn->prepare("
				INSERT INTO onetimepasswords (schooluser_id, otp_code, otp_date_created, otp_date_expiry, otp_attempts)
				VALUES (?, ?, ?, ?, 0)
			");
			$stmt->execute([$schooluser_id, $otp, $created, $expiry]);

			// 5. Send email via Mailpit
			$mail = new PHPMailer(true);

			try {
				$mail->isSMTP();
				$mail->Host = '127.0.0.1';
				$mail->SMTPAuth = false;
				$mail->Port = 1025;

				$mail->setFrom('no-reply@local.test', 'OTP System');
				$mail->addAddress($email);

				$mail->isHTML(true);
				$mail->Subject = "Your OTP Code";
				$mail->Body = "
					<h2>Your OTP Code</h2>
					<p><b>{$otp}</b></p>
					<p>This code will expire in 10 minutes.</p>
				";

				$mail->send();
				
				$_SESSION['user_email'] = $email;

				echo json_encode([
					"status" => "success",
					"message" => "OTP sent successfully"
				]);

			} catch (Exception $e) {
				echo json_encode([
					"message" => "Email failed: " . $mail->ErrorInfo
				]);
			}
		}
			break;
		
		case 'useotp': {
			if (!isset($_SESSION['user_email'])) {
				echo json_encode(["message" => "Invalid OTP"]);
				exit;
			}

			if (!$otp) {
				echo json_encode(["message" => "Invalid OTP"]);
				exit;
			}

			$email = $_SESSION['user_email'];

			$stmt = $conn->prepare("
				SELECT otp.otp_id, otp.otp_code, otp.otp_attempts, otp.otp_date_expiry, u.user_role
				FROM users u
				INNER JOIN schoolusers su ON u.user_id = su.user_id
				INNER JOIN onetimepasswords otp ON su.schooluser_id = otp.schooluser_id
				WHERE u.user_email = ?
				ORDER BY otp.otp_date_created DESC
				LIMIT 1
			");

			$stmt->execute([$email]);
			$data = $stmt->fetch(PDO::FETCH_ASSOC);

			// ❌ no OTP found
			if (!$data) {
				echo json_encode(["message" => "Invalid OTP"]);
				exit;
			}

			$now = time();
			$expiry = strtotime($data['otp_date_expiry']);

			// ❌ expired
			if ($expiry <= $now) {
				$del = $conn->prepare("DELETE FROM onetimepasswords WHERE otp_id = ?");
				$del->execute([$data['otp_id']]);

				echo json_encode(["message" => "Invalid OTP"]);
				exit;
			}

			// ❌ too many attempts
			if ($data['otp_attempts'] >= 3) {
				echo json_encode(["message" => "Invalid OTP"]);
				exit;
			}

			// ❌ wrong OTP
			if ($data['otp_code'] !== $otp) {

				$upd = $conn->prepare("
					UPDATE onetimepasswords
					SET otp_attempts = otp_attempts + 1
					WHERE otp_id = ?
				");
				$upd->execute([$data['otp_id']]);

				echo json_encode(["message" => "Invalid OTP"]);
				exit;
			}

			// ✅ success			
			$del = $conn->prepare("DELETE FROM onetimepasswords WHERE otp_id = ?");
			$del->execute([$data['otp_id']]);

			$redirect = "";
			switch ($data['user_role']) {
				case 'admin':
					$redirect = "../admin/admin.php";
					break;

				case 'officer':
					$redirect = "../officer/_index.php";
					break;

				case 'executive':
					$redirect = "../executive/executive.php";
					break;

				case 'program':
					$redirect = "../Programdi/_index.php";
					break;

				case 'intern':
					$redirect = "../student/student.php";
					break;

				default:
					$redirect = "/login.php";
					break;
			}

			echo json_encode([
				"message" => "OTP verified successfully",
				"status" => "success",
				"role" => $data['user_role'],
				"redirect" => $redirect
			]);
		}
			break;
		
		case 'resetp': {
			$input = json_decode(file_get_contents("php://input"), true);
			$email = $input['email'] ?? '';

			if (!$email) {
				echo json_encode(["message" => "Email required"]);
				exit;
			}

			// 1. get user + schooluser
			$stmt = $conn->prepare("
				SELECT u.user_id, su.schooluser_id
				FROM users u
				INNER JOIN schoolusers su ON u.user_id = su.user_id
				WHERE u.user_email = ?
				LIMIT 1
			");
			$stmt->execute([$email]);
			$user = $stmt->fetch(PDO::FETCH_ASSOC);

			if (!$user) {
				echo json_encode(["message" => "If email exists, reset link will be sent"]);
				exit;
			}

			$schooluser_id = $user['schooluser_id'];

			// 2. generate token
			$token = bin2hex(random_bytes(32));
			$created = date("Y-m-d H:i:s");
			$expiry = date("Y-m-d H:i:s", strtotime("+15 minutes"));

			// 3. store token (invalidate old ones)
			$conn->prepare("
				DELETE FROM passwordresets WHERE schooluser_id = ?
			")->execute([$schooluser_id]);

			$stmt = $conn->prepare("
				INSERT INTO passwordresets 
				(schooluser_id, passreset_token, passreset_date_created, passreset_date_expiry, passreset_is_used)
				VALUES (?, ?, ?, ?, 0)
			");
			$stmt->execute([$schooluser_id, $token, $created, $expiry]);

			// 4. build reset link
			$resetLink = "http://localhost/pages/login_be/reset.php?token=" . $token;

			// 5. send email
			$mail = new PHPMailer(true);

			try {
				$mail->isSMTP();
				$mail->Host = '127.0.0.1';
				$mail->SMTPAuth = false;
				$mail->Port = 1025;

				$mail->setFrom('no-reply@local.test', 'Password Reset');
				$mail->addAddress($email);

				$mail->isHTML(true);
				$mail->Subject = "Password Reset Link";
				$mail->Body = "
					<h3>Password Reset Request</h3>
					<p>Click the link below to reset your password:</p>
					<a href='{$resetLink}'>Reset Password</a>
					<p>This link expires in 15 minutes.</p>
				";

				$mail->send();

				echo json_encode([
					"status" => "success",
					"message" => "If email exists, reset link will be sent"
				]);

			} catch (Exception $e) {
				echo json_encode([
					"message" => "Email failed"
				]);
			}
		}
			break;
		
		default:
			echo json_encode(['error' => 'Invalid action']);
			break;
    }
	
	exit;

}
catch (Exception $e) {
    echo json_encode([
        "status" => "error",
        "message" => "Server error: " . $e->getMessage()
    ]);
}