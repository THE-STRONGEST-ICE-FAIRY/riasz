<?php
date_default_timezone_set('Asia/Manila');

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

file_put_contents("changepass_debug.txt", "HIT " . date("H:i:s") . PHP_EOL, FILE_APPEND);

header("Content-Type: application/json");

require '../../utilities/database/database.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../../utilities/PHPMailer/Exception.php';
require '../../utilities/PHPMailer/PHPMailer.php';
require '../../utilities/PHPMailer/SMTP.php';

try {

    $email = $_POST['email'] ?? '';

    if (!$email) {
        echo json_encode(["status" => "error", "message" => "Email required"]);
        exit;
    }

    // =========================
    // GET USER
    // =========================
    $stmt = $conn->prepare("
        SELECT user_id, user_email
        FROM users
        WHERE user_email = ?
        LIMIT 1
    ");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        echo json_encode(["status" => "error", "message" => "User not found"]);
        exit;
    }

    // =========================
    // GENERATE TOKEN
    // =========================
    $plainToken = bin2hex(random_bytes(32));

    // 🔐 HASH TOKEN BEFORE STORAGE
    $hashedToken = hash('sha256', $plainToken);

    $expiry = date("Y-m-d H:i:s", strtotime("+15 minutes"));

    // =========================
    // STORE HASH ONLY
    // =========================
    $insert = $conn->prepare("
        INSERT INTO passwordresets
        (schooluser_id, passreset_token, passreset_date_created, passreset_date_expiry, passreset_is_used)
        VALUES (?, ?, NOW(), ?, 0)
    ");

    $insert->execute([
        $user['user_id'],
        $hashedToken,
        $expiry
    ]);

    // =========================
    // RESET LINK (SEND PLAIN TOKEN)
    // =========================
    $resetLink = "http://localhost/pages/login/resetpassword.php?token=" . $plainToken;

    // =========================
    // MAILPIT
    // =========================
    $mail = new PHPMailer(true);

    $mail->isSMTP();
    $mail->Host = '127.0.0.1';
    $mail->Port = 1025;
    $mail->SMTPAuth = false;

    $mail->setFrom('no-reply@system.local', 'Password Reset');
    $mail->addAddress($email);

    $mail->Subject = "Password Reset Request";
    $mail->Body =
        "Click the link below to reset your password:\n\n" .
        $resetLink . "\n\n" .
        "This link expires in 15 minutes.";

    $mail->send();

    echo json_encode([
        "status" => "success",
        "message" => "Reset email sent"
    ]);

} catch (Exception $e) {
    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage()
    ]);
}