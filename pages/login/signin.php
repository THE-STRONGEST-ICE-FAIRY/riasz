<?php
session_start();
header("Content-Type: application/json");

require '../../utilities/database/database.php';

function recordSuccessfulLogin(PDO $conn, $userId)
{
    $previousLogin = $conn->prepare("
        SELECT userlog_timestamp
        FROM userlogs
        WHERE user_id = ? AND userlog_type = 'login'
        ORDER BY userlog_timestamp DESC, userlog_id DESC
        LIMIT 1
    ");
    $previousLogin->execute([$userId]);
    $previousLoginAt = $previousLogin->fetchColumn();

    $recordLogin = $conn->prepare("
        INSERT INTO userlogs (user_id, userlog_type, userlog_ip_address, userlog_device_info)
        VALUES (?, 'login', ?, ?)
    ");
    $recordLogin->execute([
        $userId,
        $_SERVER['REMOTE_ADDR'] ?? null,
        $_SERVER['HTTP_USER_AGENT'] ?? null,
    ]);

    $_SESSION['previous_login_at'] = $previousLoginAt ?: '';
}

try {

    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? null;
    $otp = $_POST['otp'] ?? null;

    if (!$email) {
        echo json_encode([
            "status" => "error",
            "message" => "Missing email"
        ]);
        exit;
    }

    // =========================
    // GET USER
    // =========================
    $stmt = $conn->prepare("
        SELECT 
            u.user_id,
            u.user_email,
            u.user_role,
            su.schooluser_id,
            su.schooluser_password
        FROM users u
        JOIN schoolusers su ON u.user_id = su.user_id
        WHERE u.user_email = ?
          AND COALESCE(u.user_is_archived, 0) = 0
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

    // =========================================================
    // MODE 1: PASSWORD LOGIN (YOUR ORIGINAL LOGIC KEPT)
    // =========================================================
    if ($password !== null) {

        if (!is_string($user['schooluser_password']) || $user['schooluser_password'] === '' || !password_verify($password, $user['schooluser_password'])) {
            echo json_encode([
                "status" => "error",
                "message" => "Invalid password"
            ]);
            exit;
        }

        session_regenerate_id(true);
        recordSuccessfulLogin($conn, (int)$user['user_id']);
        $_SESSION['user_id'] = (int)$user['user_id'];
        $_SESSION['user_role'] = strtolower(trim((string)$user['user_role']));

        echo json_encode([
            "status" => "success",
            "role" => $user['user_role']
        ]);
        exit;
    }

    // =========================================================
    // MODE 2: OTP LOGIN (NEW ADDITION)
    // =========================================================
    if ($otp !== null) {

        // Get latest OTP
        $otpStmt = $conn->prepare("
            SELECT *
            FROM onetimepasswords
            WHERE schooluser_id = ?
            ORDER BY otp_date_created DESC
            LIMIT 1
        ");

        $otpStmt->execute([$user['schooluser_id']]);
        $otpRow = $otpStmt->fetch(PDO::FETCH_ASSOC);

        if (!$otpRow) {
            echo json_encode([
                "status" => "error",
                "message" => "No OTP found"
            ]);
            exit;
        }

        // Check expiry
        $now = new DateTime();
        $expiry = new DateTime($otpRow['otp_date_expiry']);

        if ($now > $expiry) {
            echo json_encode([
                "status" => "error",
                "message" => "OTP expired"
            ]);
            exit;
        }

        // Check attempts
        if ($otpRow['otp_attempts'] >= 3) {
            echo json_encode([
                "status" => "error",
                "message" => "Too many attempts"
            ]);
            exit;
        }

        // Validate OTP
        if ($otpRow['otp_code'] !== $otp) {

            $update = $conn->prepare("
                UPDATE onetimepasswords
                SET otp_attempts = otp_attempts + 1
                WHERE otp_id = ?
            ");
            $update->execute([$otpRow['otp_id']]);

            echo json_encode([
                "status" => "error",
                "message" => "Invalid OTP"
            ]);
            exit;
        }

        // SUCCESS → delete OTP
        $delete = $conn->prepare("
            DELETE FROM onetimepasswords
            WHERE otp_id = ?
        ");
        $delete->execute([$otpRow['otp_id']]);

        session_regenerate_id(true);
        recordSuccessfulLogin($conn, (int)$user['user_id']);
        $_SESSION['user_id'] = (int)$user['user_id'];
        $_SESSION['user_role'] = strtolower(trim((string)$user['user_role']));

        echo json_encode([
            "status" => "success",
            "role" => $user['user_role']
        ]);
        exit;
    }

    // If neither password nor OTP provided
    echo json_encode([
        "status" => "error",
        "message" => "No authentication method provided"
    ]);

} catch (Exception $e) {
    echo json_encode([
        "status" => "error",
        "message" => "Server error: " . $e->getMessage()
    ]);
}