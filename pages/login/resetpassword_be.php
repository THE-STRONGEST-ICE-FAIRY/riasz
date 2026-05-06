<?php
date_default_timezone_set('Asia/Manila');

require '../../utilities/database/database.php';

$token = $_GET['token'] ?? null;

if (!$token) {
    exit("No token provided.");
}

// 1. Fetch token data
$query = "SELECT * FROM passwordresets WHERE passreset_token = ?";
$stmt = $conn->prepare($query);
$stmt->execute([$token]);
$reset = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$reset) {
    exit("Invalid token.");
}

if ($reset['passreset_is_used']) {
    exit("This link has already been used.");
}

if (strtotime($reset['passreset_date_expiry']) < time()) {
    exit("This link has expired.");
}

// 2. If form submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $password = $_POST['password'];
    $confirm = $_POST['confirm_password'];

    if ($password !== $confirm) {
        exit("Passwords do not match.");
    }

    $hashed = password_hash($password, PASSWORD_BCRYPT);

    // update schoolusers password
    $query = "UPDATE schoolusers SET schooluser_password = ?
              WHERE schooluser_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->execute([$hashed, $reset['schooluser_id']]);

    // mark token as used
    $query = "UPDATE passwordresets SET passreset_is_used = 1
              WHERE passreset_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->execute([$reset['passreset_id']]);

	echo "<script>
		alert('Password set successfully. You may now log in.');
		window.location.replace('login.php');
	</script>";
    exit;
}
?>

<form method="POST">
    <h2>Create Password</h2>

    <input type="password" name="password" placeholder="New Password" required>
    <br><br>

    <input type="password" name="confirm_password" placeholder="Confirm Password" required>
    <br><br>

    <button type="submit">Set Password</button>
</form>