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
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_first_name = $_POST['user_first_name'];
    $user_last_name = $_POST['user_last_name'];
    $user_email = $_POST['user_email'];
    $user_role = $_POST['user_role'];
    $schooluser_given_id = $_POST['schooluser_given_id'];  // Collect the new field

    // Start by inserting the user into the 'users' table
    $query = "INSERT INTO users (user_first_name, user_last_name, user_email, user_role) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bindValue(1, $user_first_name, PDO::PARAM_STR);  // Corrected for PDO
    $stmt->bindValue(2, $user_last_name, PDO::PARAM_STR);   // Corrected for PDO
    $stmt->bindValue(3, $user_email, PDO::PARAM_STR);       // Corrected for PDO
    $stmt->bindValue(4, $user_role, PDO::PARAM_STR);        // Corrected for PDO
    // $stmt->execute();
	if (!$stmt->execute()) {
		print_r($stmt->errorInfo());
		exit;
	}
    $user_id = $conn->lastInsertId();  // Get the inserted user ID
	
    // Insert into the schoolusers table with schooluser_given_id and password as NULL
    $query = "INSERT INTO schoolusers (user_id, schooluser_given_id, schooluser_password) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bindValue(1, $user_id, PDO::PARAM_INT);                 // user_id from users table
    $stmt->bindValue(2, $schooluser_given_id, PDO::PARAM_STR);      // schooluser_given_id from the form
    $stmt->bindValue(3, NULL, PDO::PARAM_NULL);                     // Set password as NULL
    // $stmt->execute();
	if (!$stmt->execute()) {
		print_r($stmt->errorInfo());
		exit;
	}

	$schooluser_id = $conn->lastInsertId();

    // Insert into schoolusers table if necessary
    if (isset($_POST['school_id']) && $user_role === 'executive') {
        $school_id = $_POST['school_id'];
        $query = "INSERT INTO executivedirectors (schooluser_id, school_id) VALUES (?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bindValue(1, $schooluser_id, PDO::PARAM_INT);         // Corrected for PDO
        $stmt->bindValue(2, $school_id, PDO::PARAM_INT);       // Corrected for PDO
        // $stmt->execute();
		if (!$stmt->execute()) {
			print_r($stmt->errorInfo());
			exit;
		}
    } elseif (isset($_POST['program_id']) && $user_role === 'program') {
        $program_id = $_POST['program_id'];
        $query = "INSERT INTO programdirectors (schooluser_id, program_id) VALUES (?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bindValue(1, $schooluser_id, PDO::PARAM_INT);         // Corrected for PDO
        $stmt->bindValue(2, $program_id, PDO::PARAM_INT);      // Corrected for PDO
        // $stmt->execute();
		if (!$stmt->execute()) {
			print_r($stmt->errorInfo());
			exit;
		}
    }
	
	$conn->beginTransaction();
	
	// 3. Generate reset token
	$token = bin2hex(random_bytes(32));
	$expiry = date("Y-m-d H:i:s", strtotime("+24 hours"));

	$query = "INSERT INTO passwordresets
			  (schooluser_id, passreset_token, passreset_date_created, passreset_date_expiry, passreset_is_used)
			  VALUES (?, ?, NOW(), ?, 0)";
	$stmt = $conn->prepare($query);
	$stmt->execute([$schooluser_id, $token, $expiry]);

	$conn->commit();

	// 4. Send email via Mailpit (SMTP localhost)
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

	echo "User created and email sent.";
}

?>