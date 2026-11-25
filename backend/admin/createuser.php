<?php

require '../../utilities/PHPMailer/PHPMailer.php';
require '../../utilities/PHPMailer/SMTP.php';
require '../../utilities/PHPMailer/Exception.php';

// Aliased so it doesn't shadow PHP's own Exception / PDOException handling.
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as MailException;

require '../../utilities/database/database.php';

// Set to false in production.
$debug = true;
ini_set('display_errors', $debug ? 1 : 0);
error_reporting(E_ALL);

header('Content-Type: application/json');

$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

function respond($payload, $code = 200) {
    http_response_code($code);
    echo json_encode($payload);
    exit();
}

// ---------------------------------------------------------------------
// Fetch schools
// ---------------------------------------------------------------------
if (isset($_GET['action']) && $_GET['action'] === 'load_schools') {
    try {
        $stmt = $conn->query("SELECT school_id, school_name FROM schools ORDER BY school_name ASC");
        respond($stmt->fetchAll(PDO::FETCH_ASSOC));
    } catch (PDOException $e) {
        respond(['error' => 'Database query failed'], 500);
    }
}

// ---------------------------------------------------------------------
// Fetch programs for a school
// ---------------------------------------------------------------------
if (isset($_GET['action']) && $_GET['action'] === 'load_programs' && isset($_GET['school_id'])) {
    try {
        $stmt = $conn->prepare("SELECT program_id, program_name FROM programs WHERE school_id = ? ORDER BY program_name ASC");
        $stmt->bindValue(1, (int)$_GET['school_id'], PDO::PARAM_INT);
        $stmt->execute();
        respond($stmt->fetchAll(PDO::FETCH_ASSOC));
    } catch (PDOException $e) {
        respond(['error' => 'Database query failed'], 500);
    }
}

// ---------------------------------------------------------------------
// Create user
// ---------------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(['error' => 'Invalid request.'], 405);
}

$user_first_name     = trim($_POST['user_first_name'] ?? '');
$user_last_name      = trim($_POST['user_last_name'] ?? '');
$user_email          = trim($_POST['user_email'] ?? '');
$user_role           = $_POST['user_role'] ?? '';
$schooluser_given_id = trim($_POST['schooluser_given_id'] ?? '');
$school_id           = (int)($_POST['school_id'] ?? 0);
$program_id          = (int)($_POST['program_id'] ?? 0);

// Server-side validation (the browser checks can be bypassed)
if ($user_first_name === '' || $user_last_name === '' || $schooluser_given_id === '') {
    respond(['error' => 'Please fill in all required fields.'], 400);
}
if (!filter_var($user_email, FILTER_VALIDATE_EMAIL)) {
    respond(['error' => 'Please enter a valid email address.'], 400);
}
if (!in_array($user_role, ['officer', 'program', 'executive'], true)) {
    respond(['error' => 'Invalid role.'], 400);
}
if ($user_role === 'executive' && $school_id <= 0) {
    respond(['error' => 'Please select a school for the executive.'], 400);
}
if ($user_role === 'program' && $program_id <= 0) {
    respond(['error' => 'Please select a program for the program director.'], 400);
}

try {
    $conn->beginTransaction();

    // 1. users
    $stmt = $conn->prepare("INSERT INTO users (user_first_name, user_last_name, user_email, user_role) VALUES (?, ?, ?, ?)");
    $stmt->execute([$user_first_name, $user_last_name, $user_email, $user_role]);
    $user_id = $conn->lastInsertId();

    // 2. schoolusers
    $stmt = $conn->prepare("INSERT INTO schoolusers (user_id, schooluser_given_id, schooluser_password) VALUES (?, ?, ?)");
    $stmt->execute([$user_id, $schooluser_given_id, null]);
    $schooluser_id = $conn->lastInsertId();

    // 3. role-specific table
    if ($user_role === 'executive') {
        $stmt = $conn->prepare("INSERT INTO executivedirectors (schooluser_id, school_id) VALUES (?, ?)");
        $stmt->execute([$schooluser_id, $school_id]);
    } elseif ($user_role === 'program') {
        $stmt = $conn->prepare("INSERT INTO programdirectors (schooluser_id, program_id) VALUES (?, ?)");
        $stmt->execute([$schooluser_id, $program_id]);
    }

    // 4. password reset token
    $token  = bin2hex(random_bytes(32));
    $expiry = date("Y-m-d H:i:s", strtotime("+24 hours"));
    $stmt = $conn->prepare("INSERT INTO passwordresets (schooluser_id, passreset_token, passreset_date_created, passreset_date_expiry, passreset_is_used) VALUES (?, ?, NOW(), ?, 0)");
    $stmt->execute([$schooluser_id, $token, $expiry]);

    $conn->commit();
} catch (Throwable $e) {
    // Catches PDOException too (the old catch only caught PHPMailer's Exception).
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }

    if ($e instanceof PDOException && $e->getCode() === '23000') {
        respond(['error' => 'A user with that email or given ID already exists.'], 409);
    }

    error_log($e->getMessage());
    respond(['error' => $debug ? 'Failed to create user: ' . $e->getMessage() : 'Failed to create user.'], 500);
}

// ---------------------------------------------------------------------
// 5. Send email (after commit, so an email failure doesn't undo the user)
// ---------------------------------------------------------------------
try {
    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host     = '127.0.0.1';
    $mail->SMTPAuth = false;
    $mail->Port     = 1025;
    $mail->setFrom('no-reply@school.local', 'School System');
    $mail->addAddress($user_email, $user_first_name);

    $link     = "http://localhost/pages/login/resetpassword_be.php?token=" . urlencode($token);
    $safeName = htmlspecialchars($user_first_name, ENT_QUOTES, 'UTF-8');

    $mail->isHTML(true);
    $mail->Subject = "Create Your Password";
    $mail->Body = "
        <h3>Welcome, {$safeName}</h3>
        <p>Click below to set your password:</p>
        <a href='{$link}'>{$link}</a>
        <p>This link expires in 24 hours.</p>
    ";
    $mail->send();

    respond(['success' => true, 'message' => 'User created and email sent.']);
} catch (MailException $e) {
    error_log('Mail error: ' . $e->getMessage());
    respond([
        'success' => true,
        'message' => 'User created, but the email could not be sent. Please resend the password link manually.'
    ]);
}
