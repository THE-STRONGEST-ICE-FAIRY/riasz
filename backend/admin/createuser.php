<?php

require '../../utilities/PHPMailer/PHPMailer.php';
require '../../utilities/PHPMailer/SMTP.php';
require '../../utilities/PHPMailer/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../../utilities/database/database.php';

// Enable error reporting (remove this in production)
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Fetch Schools
if (isset($_GET['action']) && $_GET['action'] == 'load_schools') {
    $query = "SELECT school_id, school_name FROM schools";
    $result = $conn->query($query);

    // Check for query failure
    if (!$result) {
        echo json_encode(['error' => 'Database query failed']);
        exit();
    }

    $schools = array();
    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {  // Corrected for PDO
        $schools[] = $row;
    }

    // Return JSON response
    echo json_encode($schools);
    exit(); // Ensure no further output
}

// Fetch Programs for a Specific School
if (isset($_GET['action']) && $_GET['action'] == 'load_programs' && isset($_GET['school_id'])) {
    $school_id = $_GET['school_id'];

    $query = "SELECT program_id, program_name FROM programs WHERE school_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bindValue(1, $school_id, PDO::PARAM_INT);  // Corrected for PDO
    $stmt->execute();

    $programs = array();
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {  // Corrected for PDO
        $programs[] = $row;
    }

    // Return JSON response
    echo json_encode($programs);
    exit(); // Ensure no further output
}

// User Creation Logic
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_first_name = $_POST['user_first_name'];
    $user_last_name = $_POST['user_last_name'];
    $user_email = $_POST['user_email'];
    $user_role = $_POST['user_role'];
    $schooluser_given_id = $_POST['schooluser_given_id'];

    try {
        // Start transaction BEFORE any inserts
        $conn->beginTransaction();

        // 1. Insert user
        $stmt = $conn->prepare("INSERT INTO users (user_first_name, user_last_name, user_email, user_role) VALUES (?, ?, ?, ?)");
        $stmt->execute([$user_first_name, $user_last_name, $user_email, $user_role]);
        $user_id = $conn->lastInsertId();

        // 2. Insert into schoolusers
        $stmt = $conn->prepare("INSERT INTO schoolusers (user_id, schooluser_given_id, schooluser_password) VALUES (?, ?, ?)");
        $stmt->execute([$user_id, $schooluser_given_id, null]);
        $schooluser_id = $conn->lastInsertId();

        // 3. Role-based insert
        if (isset($_POST['school_id']) && $user_role === 'executive') {
            $school_id = $_POST['school_id'];
            $stmt = $conn->prepare("INSERT INTO executivedirectors (schooluser_id, school_id) VALUES (?, ?)");
            $stmt->execute([$schooluser_id, $school_id]);
        } elseif (isset($_POST['program_id']) && $user_role === 'program') {
            $program_id = $_POST['program_id'];
            $stmt = $conn->prepare("INSERT INTO programdirectors (schooluser_id, program_id) VALUES (?, ?)");
            $stmt->execute([$schooluser_id, $program_id]);
        }

        // 4. Password reset token
        $token = bin2hex(random_bytes(32));
        $expiry = date("Y-m-d H:i:s", strtotime("+24 hours"));
        $stmt = $conn->prepare("INSERT INTO passwordresets (schooluser_id, passreset_token, passreset_date_created, passreset_date_expiry, passreset_is_used) VALUES (?, ?, NOW(), ?, 0)");
        $stmt->execute([$schooluser_id, $token, $expiry]);

        // Commit everything if no errors
        $conn->commit();

        // 5. Send email
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = '127.0.0.1';
        $mail->SMTPAuth = false;
        $mail->Port = 1025;
        $mail->setFrom('no-reply@school.local', 'School System');
        $mail->addAddress($user_email, $user_first_name);

        $link = "http://localhost/pages/login/resetpassword_be.php?token=$token";
        $mail->isHTML(true);
        $mail->Subject = "Create Your Password";
        $mail->Body = "
            <h3>Welcome, $user_first_name</h3>
            <p>Click below to set your password:</p>
            <a href='$link'>$link</a>
            <p>This link expires in 24 hours.</p>
        ";
        $mail->send();

        echo json_encode(['success' => true, 'message' => 'User created and email sent.']);
	}
	catch (Exception $e) {
		$conn->rollBack();
		http_response_code(500); // <-- tell the browser it's an error
		echo json_encode(['error' => "Failed to create user: " . $e->getMessage()]);
		exit();
	}
}

?>