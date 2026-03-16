<?php
// Config
$host = "localhost";
$db   = "riasz";
$user = "root";
$pass = "";

// Connect
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Include PHPMailer
require 'PHPMailer/PHPMailer.php';
require 'PHPMailer/SMTP.php';
require 'PHPMailer/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    $sql = "SELECT * FROM users WHERE username='$username' AND password=MD5('$password')";
    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        $message = "Login successful! Welcome, $username! Nyah~";
    } else {
        $message = "Login failed! Your pathetic credentials are worthless! (╯°□°）╯︵ ┻━┻";
    }

    // Test Mailpit functionality with PHPMailer
    if (isset($_POST['email'])) {
        $email = $_POST['email'];
        $subject = "Test Email from Login Page";
        $body = "This is a test email sent via Mailpit. If you received this, it worked!";

        // Create a PHPMailer instance
        $mail = new PHPMailer(true);
        
        try {
            //Server settings
            $mail->isSMTP();                                           // Set mailer to use SMTP
            $mail->Host       = 'localhost';                            // SMTP server (Mailpit is running locally)
            $mail->SMTPAuth   = false;                                  // No authentication required for Mailpit
            $mail->Port       = 1025;                                   // Mailpit's default SMTP port

            // Recipients
            $mail->setFrom('no-reply@yourdomain.com', 'Test Sender');
            $mail->addAddress($email);                                  // Add a recipient

            // Content
            $mail->isHTML(true);                                        // Set email format to HTML
            $mail->Subject = $subject;
            $mail->Body    = $body;

            // Send email
            $mail->send();
            $message .= "<br>Email sent successfully to $email!";
        } catch (Exception $e) {
            $message .= "<br>Email sending failed. Mailer Error: {$mail->ErrorInfo}";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login Test</title>
</head>
<body>
    <h2>Login Page</h2>
    <form method="post">
        Username: <input type="text" name="username" required><br><br>
        Password: <input type="password" name="password" required><br><br>
        <input type="submit" value="Login">
    </form>

    <h3>Test PHPMailer Integration</h3>
    <form method="post">
        <textarea name="email" placeholder="Enter email to send test" required></textarea><br><br>
        <input type="submit" value="Send Test Email">
    </form>

    <p><?php echo $message; ?></p>
</body>
</html>