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
	$tabId = $_POST['tabId'] ?? $input['tabId'] ?? 'default';

    switch ($action) {
		case 'me': {
			$user = getUserFromTab($conn, $tabId);

			if (!$user) {
				echo json_encode([
					"status" => "error",
					"message" => "Not logged in"
				]);
				exit;
			}

			echo json_encode([
				"status" => "success",
				"user" => $user
			]);

			break;
		}
		
		case 'logout': {
			$tabId = $_POST['tabId'] ?? $input['tabId'] ?? null;

			if ($tabId && isset($_SESSION['accounts'][$tabId])) {
				unset($_SESSION['accounts'][$tabId]);
			}

			if (isset($_SESSION['active_tab']) && $_SESSION['active_tab'] === $tabId) {
				unset($_SESSION['active_tab']);
			}

			echo json_encode([
				"status" => "success",
				"message" => "Logged out"
			]);
			break;
		}
		
		case 'profile': {

			$user = getUserFromTab($conn, $tabId);

			if (!$user) {
				echo json_encode([
					"status" => "error",
					"message" => "Not logged in"
				]);
				exit;
			}

			$stmt = $conn->prepare("
				SELECT 
					u.user_id,
					u.user_first_name,
					u.user_last_name,
					u.user_email,

					su.schooluser_id,
					su.schooluser_given_id,
					su.profile_photo,	

					i.intern_id,
					i.intern_birthdate,
					i.intern_gender,
					i.intern_city,
					i.intern_province_or_state,
					i.intern_postal_code,
					i.intern_country,

					p.program_name,
					s.school_name

				FROM users u

				INNER JOIN schoolusers su 
					ON su.user_id = u.user_id

				LEFT JOIN interns i 
					ON i.schooluser_id = su.schooluser_id

				LEFT JOIN programs p 
					ON p.program_id = i.program_id

				LEFT JOIN schools s 
					ON s.school_id = p.school_id

				WHERE u.user_id = ?
				LIMIT 1
			");

			$stmt->execute([$user['user_id']]);
			$profile = $stmt->fetch(PDO::FETCH_ASSOC);

			if (!$profile) {
				echo json_encode([
					"status" => "error",
					"message" => "Profile not found"
				]);
				exit;
			}

			echo json_encode([
				"status" => "success",
				"user" => $profile
			]);

			break;
		}
		
		
		case 'update_profile': {
			$user = getUserFromTab($conn, $tabId);

			if (!$user) {
				echo json_encode([
					"status" => "error",
					"message" => "Not logged in"
				]);
				exit;
			}

			$first_name = $_POST['first_name'] ?? '';
			$last_name = $_POST['last_name'] ?? '';
			$email = $_POST['email'] ?? '';

			$birthdate = $_POST['birthdate'] ?? null;
			$school_id = $_POST['school_id'] ?? '';

			$gender = $_POST['gender'] ?? '';
			$city = $_POST['city'] ?? '';
			$province = $_POST['province'] ?? '';
			$postal_code = $_POST['postal_code'] ?? '';
			$country = $_POST['country'] ?? '';

			$profile_photo_path = null;

			/*
				UPLOAD PROFILE PHOTO
			*/
			if (
				isset($_FILES['profile_photo']) &&
				$_FILES['profile_photo']['error'] === 0
			) {

				$uploadDir = "../../uploads/profilephotos/";

				if (!is_dir($uploadDir)) {
					mkdir($uploadDir, 0777, true);
				}

				$filename =
					time() . "_" . basename($_FILES['profile_photo']['name']);

				$targetFile = $uploadDir . $filename;

				move_uploaded_file(
					$_FILES['profile_photo']['tmp_name'],
					$targetFile
				);

				$profile_photo_path =
					"uploads/profilephotos/" . $filename;

				$stmt = $conn->prepare("
					UPDATE schoolusers
					SET profile_photo = ?
					WHERE user_id = ?
				");

				$stmt->execute([
					$profile_photo_path,
					$user['user_id']
				]);
			}

			/*
				UPDATE USERS TABLE
			*/
			$stmt = $conn->prepare("
				UPDATE users
				SET
					user_first_name = ?,
					user_last_name = ?,
					user_email = ?
				WHERE user_id = ?
			");

			$stmt->execute([
				$first_name,
				$last_name,
				$email,
				$user['user_id']
			]);

			/*
				UPDATE SCHOOLUSERS TABLE
			*/
			$stmt = $conn->prepare("
				UPDATE schoolusers
				SET schooluser_given_id = ?
				WHERE user_id = ?
			");

			$stmt->execute([
				$school_id,
				$user['user_id']
			]);

			/*
				UPDATE INTERNS TABLE
			*/
			$stmt = $conn->prepare("
				UPDATE interns i
				INNER JOIN schoolusers su
					ON su.schooluser_id = i.schooluser_id
				SET
					i.intern_birthdate = ?,
					i.intern_gender = ?,
					i.intern_city = ?,
					i.intern_province_or_state = ?,
					i.intern_postal_code = ?,
					i.intern_country = ?
				WHERE su.user_id = ?
			");

			$stmt->execute([
				$birthdate,
				$gender,
				$city,
				$province,
				$postal_code,
				$country,
				$user['user_id']
			]);

			echo json_encode([
				"status" => "success",
				"message" => "Profile updated"
			]);

			break;
		}

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

function getUserFromTab($conn, $tabId) {

    if (!isset($_SESSION['accounts'][$tabId]['user_id'])) {
        return null;
    }

    $user_id = $_SESSION['accounts'][$tabId]['user_id'];

    $stmt = $conn->prepare("
        SELECT 
            u.user_id,
            u.user_email,
            u.user_role
        FROM users u
        WHERE u.user_id = ?
        LIMIT 1
    ");

    $stmt->execute([$user_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}