<?php
date_default_timezone_set('Asia/Manila');

require '../../utilities/database/database.php';

$token = $_GET['token'] ?? '';

if (!$token) {
    die("Invalid reset link.");
}

$hashedToken = hash('sha256', $token);

// =========================
// FIND RESET REQUEST
// =========================
$stmt = $conn->prepare("
    SELECT *
    FROM passwordresets
    WHERE passreset_token = ?
    AND passreset_is_used = 0
    AND passreset_date_expiry > NOW()
    LIMIT 1
");

$stmt->execute([$hashedToken]);
$reset = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$reset) {
    die("This reset link is invalid or expired.");
}

// =========================
// HANDLE PASSWORD SUBMIT
// =========================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if (!$password || !$confirm) {
        $error = "All fields are required.";
    } elseif ($password !== $confirm) {
        $error = "Passwords do not match.";
    } else {

        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Update password
        $update = $conn->prepare("
            UPDATE schoolusers
            SET schooluser_password = ?
            WHERE user_id = ?
        ");

        $update->execute([
            $hashedPassword,
            $reset['schooluser_id']
        ]);

        // Mark token as used
        $used = $conn->prepare("
            UPDATE passwordresets
            SET passreset_is_used = 1
            WHERE passreset_id = ?
        ");

        $used->execute([$reset['passreset_id']]);

        die("Password successfully reset. You may now close this page.");
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Reset Password</title>
    <style>
        body {
            font-family: Arial;
            background: #f4f4f4;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .box {
            background: white;
            padding: 30px;
            border-radius: 10px;
            width: 350px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }

        input {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
        }

        button {
            width: 100%;
            padding: 10px;
            background: #213B9A;
            color: white;
            border: none;
            cursor: pointer;
        }

        button:hover {
            background: #1a2f7a;
        }

        .error {
            color: red;
        }
    </style>
</head>

<body>

<div class="box">
    <h2>Reset Password</h2>

    <?php if (!empty($error)): ?>
        <p class="error"><?= $error ?></p>
    <?php endif; ?>

    <form method="POST">
        <input type="password" name="password" placeholder="New Password" required>
        <input type="password" name="confirm_password" placeholder="Confirm Password" required>
        <button type="submit">Update Password</button>
    </form>
</div>

</body>
</html>