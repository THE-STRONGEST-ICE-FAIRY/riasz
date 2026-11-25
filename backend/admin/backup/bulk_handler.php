<?php
$debug = true;
ini_set('display_errors', $debug ? 1 : 0);
error_reporting(E_ALL);

require '../../utilities/database/database.php';
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

header('Content-Type: application/json; charset=utf-8');

function fail($message, $code = 400) {
    http_response_code($code);
    echo json_encode(['error' => $message]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    fail('Method not allowed.', 405);
}

$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    $input = $_POST;
}

$mode = $input['mode'] ?? '';
$rows = $input['rows'] ?? [];

if (!is_array($rows) || empty($rows)) {
    fail('No valid rows provided for bulk insertion.');
}

try {
    $conn->beginTransaction();

    if ($mode === 'schools') {
        $count = 0;
        foreach ($rows as $idx => $r) {
            $schoolName = trim($r['school_name'] ?? '');
            $progName = trim($r['program_name'] ?? '');
            if ($schoolName === '') {
                throw new Exception("Row #" . ($idx + 1) . ": School name is required.");
            }

            $stmt = $conn->prepare("SELECT school_id FROM schools WHERE school_name = ?");
            $stmt->execute([$schoolName]);
            $schoolId = $stmt->fetchColumn();

            if (!$schoolId) {
                $stmt = $conn->prepare("INSERT INTO schools (school_name) VALUES (?)");
                $stmt->execute([$schoolName]);
                $schoolId = $conn->lastInsertId();
            }

            if ($progName !== '') {
                $stmt = $conn->prepare("SELECT COUNT(*) FROM programs WHERE program_name = ? AND school_id = ?");
                $stmt->execute([$progName, $schoolId]);
                if ($stmt->fetchColumn() == 0) {
                    $stmt = $conn->prepare("INSERT INTO programs (program_name, school_id) VALUES (?, ?)");
                    $stmt->execute([$progName, $schoolId]);
                }
            }
            $count++;
        }
        $conn->commit();
        echo json_encode(['success' => true, 'message' => "Successfully added $count school/program row(s)."]);

    } elseif ($mode === 'users') {
        $count = 0;
        foreach ($rows as $idx => $r) {
            $rowNum = $idx + 1;
            $first = trim($r['first_name'] ?? '');
            $last = trim($r['last_name'] ?? '');
            $email = trim($r['email'] ?? '');
            $givenId = trim($r['given_id'] ?? '');
            $role = trim(strtolower($r['role'] ?? ''));
            $schoolName = trim($r['school_name'] ?? '');
            $progName = trim($r['program_name'] ?? '');

            if ($first === '' || $last === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || $givenId === '' || !in_array($role, ['officer', 'program', 'executive'], true)) {
                throw new Exception("Row #$rowNum has invalid or incomplete user fields.");
            }

            // Check duplicate email or given_id before inserting
            $dupStmt = $conn->prepare("SELECT COUNT(*) FROM users WHERE user_email = ?");
            $dupStmt->execute([$email]);
            if ($dupStmt->fetchColumn() > 0) {
                throw new Exception("Row #$rowNum: Email '$email' already exists.");
            }

            $dupIdStmt = $conn->prepare("SELECT COUNT(*) FROM schoolusers WHERE schooluser_given_id = ?");
            $dupIdStmt->execute([$givenId]);
            if ($dupIdStmt->fetchColumn() > 0) {
                throw new Exception("Row #$rowNum: Given ID '$givenId' already exists.");
            }

            // 1. users
            $stmt = $conn->prepare("INSERT INTO users (user_first_name, user_last_name, user_email, user_role) VALUES (?, ?, ?, ?)");
            $stmt->execute([$first, $last, $email, $role]);
            $userId = $conn->lastInsertId();

            // 2. schoolusers
            $stmt = $conn->prepare("INSERT INTO schoolusers (user_id, schooluser_given_id, schooluser_password) VALUES (?, ?, ?)");
            $stmt->execute([$userId, $givenId, null]);
            $schooluserId = $conn->lastInsertId();

            // 3. role-specific links
            if ($role === 'executive') {
                if ($schoolName === '' || $schoolName === 'n/a') {
                    throw new Exception("Row #$rowNum: School is required for Executive Director.");
                }
                $stmt = $conn->prepare("SELECT school_id FROM schools WHERE school_name = ?");
                $stmt->execute([$schoolName]);
                $schoolId = $stmt->fetchColumn();
                if (!$schoolId) {
                    $stmt = $conn->prepare("INSERT INTO schools (school_name) VALUES (?)");
                    $stmt->execute([$schoolName]);
                    $schoolId = $conn->lastInsertId();
                }
                $stmt = $conn->prepare("INSERT INTO executivedirectors (schooluser_id, school_id) VALUES (?, ?)");
                $stmt->execute([$schooluserId, $schoolId]);

            } elseif ($role === 'program') {
                if ($schoolName === '' || $schoolName === 'n/a' || $progName === '' || $progName === 'n/a') {
                    throw new Exception("Row #$rowNum: School and Program are required for Program Director.");
                }
                $stmt = $conn->prepare("SELECT school_id FROM schools WHERE school_name = ?");
                $stmt->execute([$schoolName]);
                $schoolId = $stmt->fetchColumn();
                if (!$schoolId) {
                    $stmt = $conn->prepare("INSERT INTO schools (school_name) VALUES (?)");
                    $stmt->execute([$schoolName]);
                    $schoolId = $conn->lastInsertId();
                }
                $stmt = $conn->prepare("SELECT program_id FROM programs WHERE program_name = ? AND school_id = ?");
                $stmt->execute([$progName, $schoolId]);
                $progId = $stmt->fetchColumn();
                if (!$progId) {
                    $stmt = $conn->prepare("INSERT INTO programs (program_name, school_id) VALUES (?, ?)");
                    $stmt->execute([$progName, $schoolId]);
                    $progId = $conn->lastInsertId();
                }
                $stmt = $conn->prepare("INSERT INTO programdirectors (schooluser_id, program_id) VALUES (?, ?)");
                $stmt->execute([$schooluserId, $progId]);
            }

            // 4. password reset token
            $token  = bin2hex(random_bytes(32));
            $expiry = date("Y-m-d H:i:s", strtotime("+24 hours"));
            $stmt = $conn->prepare("INSERT INTO passwordresets (schooluser_id, passreset_token, passreset_date_created, passreset_date_expiry, passreset_is_used) VALUES (?, ?, NOW(), ?, 0)");
            $stmt->execute([$schooluserId, $token, $expiry]);

            $count++;
        }
        $conn->commit();
        echo json_encode(['success' => true, 'message' => "Successfully added $count user(s)."]);
    } else {
        fail('Unknown bulk mode.');
    }
} catch (Throwable $e) {
    if ($conn->inTransaction()) $conn->rollBack();
    error_log($e->getMessage());
    fail($debug ? $e->getMessage() : 'An error occurred during bulk processing.');
}
