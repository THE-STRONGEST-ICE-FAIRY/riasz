<?php
header("Content-Type: application/json");

require '../../utilities/database/database.php';

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

    // =========================================================
    // MODE 1: PASSWORD LOGIN (YOUR ORIGINAL LOGIC KEPT)
    // =========================================================
    if ($password !== null) {

        if (!password_verify($password, $user['schooluser_password'])) {
            echo json_encode([
                "status" => "error",
                "message" => "Invalid password"
            ]);
            exit;
        }

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

        $otpStmt->execute([$user['user_id']]);
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