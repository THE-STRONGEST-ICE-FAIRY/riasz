<?php
date_default_timezone_set('Asia/Manila');

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

file_put_contents("otp_debug.txt", "HIT " . date("H:i:s") . PHP_EOL, FILE_APPEND);

header("Content-Type: application/json");

require '../../utilities/database/database.php';

require '../../utilities/PHPMailer/Exception.php';
require '../../utilities/PHPMailer/PHPMailer.php';
require '../../utilities/PHPMailer/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

try {

    $email = $_POST['email'] ?? '';

    if (!$email) {
        echo json_encode(["status" => "error", "message" => "Email required"]);
        exit;
    }

    // Find user
    $stmt = $conn->prepare("SELECT user_id FROM users WHERE user_email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        echo json_encode(["status" => "error", "message" => "User not found"]);
        exit;
    }

    $otp = rand(100000, 999999);
    $expiry = date("Y-m-d H:i:s", strtotime("+5 minutes"));

    // Save OTP
    $insert = $conn->prepare("
        INSERT INTO onetimepasswords
        (schooluser_id, otp_code, otp_date_created, otp_date_expiry, otp_attempts)
        VALUES (?, ?, NOW(), ?, 0)
    ");
    $insert->execute([$user['user_id'], $otp, $expiry]);

    // MAIL via Mailpit
    $mail = new PHPMailer(true);

    $mail->isSMTP();
    $mail->Host = '127.0.0.1';
    $mail->Port = 1025;
    $mail->SMTPAuth = false;

    $mail->setFrom('no-reply@system.local', 'OTP System');
    $mail->addAddress($email);

    $mail->Subject = "Your OTP Code";
    $mail->Body = "Your OTP is: $otp\nValid for 5 minutes.";

    $mail->send();

    echo json_encode([
        "status" => "success",
        "message" => "OTP sent"
    ]);

} catch (Exception $e) {
    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage()
    ]);
}