<?php
header("Content-Type: text/plain");

require '../../utilities/database/database.php';

try {

    $email = $_POST['email'] ?? '';
    $otp = $_POST['otp'] ?? '';

    if (!$email || !$otp) {
        echo "invalid";
        exit;
    }

    $stmt = $conn->prepare("
        SELECT o.*
        FROM onetimepasswords o
        JOIN users u ON u.user_id = o.schooluser_id
        WHERE u.user_email = ?
        ORDER BY o.otp_date_created DESC
        LIMIT 1
    ");

    $stmt->execute([$email]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
        echo "invalid";
        exit;
    }

    if ($row['otp_code'] === $otp) {
        echo "verified";
    } else {
        echo "invalid";
    }

} catch (Exception $e) {
    echo "invalid";
}