<?php
require '../../utilities/database/database.php';

header('Content-Type: application/json; charset=utf-8');
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
session_start();

$sessionRole = strtolower(trim((string)($_SESSION['user_role'] ?? '')));
if (!in_array($sessionRole, ['officer', 'internship officer'], true)) {
    http_response_code(403);
    echo json_encode(['error' => 'You must be signed in as an internship officer.']);
    exit;
}

function respond($payload, $status = 200)
{
    http_response_code($status);
    echo json_encode($payload);
    exit;
}

function roleMap($role)
{
    $roles = [
        'student' => 'intern',
        'industry' => 'supervisor',
        'exec' => 'executive',
        'prog' => 'program',
        'officer' => 'officer',
    ];
    return isset($roles[$role]) ? $roles[$role] : null;
}

function ensureSchool(PDO $db, $name)
{
    $name = trim((string)$name);
    if ($name === '') {
        return null;
    }

    $stmt = $db->prepare('SELECT school_id FROM schools WHERE LOWER(school_name) = LOWER(?) LIMIT 1');
    $stmt->execute([$name]);
    $id = $stmt->fetchColumn();
    if ($id !== false) {
        return (int)$id;
    }

    $stmt = $db->prepare('INSERT INTO schools (school_name) VALUES (?)');
    $stmt->execute([$name]);
    return (int)$db->lastInsertId();
}

function ensureProgram(PDO $db, $name, $schoolId)
{
    $name = trim((string)$name);
    if ($name === '') {
        return null;
    }

    if ($schoolId === null) {
        $stmt = $db->prepare('SELECT program_id FROM programs WHERE LOWER(program_name) = LOWER(?) AND school_id IS NULL LIMIT 1');
        $stmt->execute([$name]);
    } else {
        $stmt = $db->prepare('SELECT program_id FROM programs WHERE LOWER(program_name) = LOWER(?) AND school_id = ? LIMIT 1');
        $stmt->execute([$name, $schoolId]);
    }

    $id = $stmt->fetchColumn();
    if ($id !== false) {
        return (int)$id;
    }

    $stmt = $db->prepare('INSERT INTO programs (program_name, school_id) VALUES (?, ?)');
    $stmt->execute([$name, $schoolId]);
    return (int)$db->lastInsertId();
}

function ensureCompany(PDO $db, $name, $address, $status)
{
    $name = trim((string)$name);
    if ($name === '') {
        return null;
    }

    $stmt = $db->prepare('SELECT company_id FROM companies WHERE LOWER(company_name) = LOWER(?) LIMIT 1');
    $stmt->execute([$name]);
    $id = $stmt->fetchColumn();

    if ($id !== false) {
        $stmt = $db->prepare('UPDATE companies SET company_address = COALESCE(?, company_address), partnership_status = COALESCE(?, partnership_status) WHERE company_id = ?');
        $stmt->execute([$address !== '' ? $address : null, $status !== '' ? $status : null, $id]);
        return (int)$id;
    }

    $stmt = $db->prepare('INSERT INTO companies (company_name, company_address, partnership_status) VALUES (?, ?, ?)');
    $stmt->execute([$name, $address !== '' ? $address : null, $status !== '' ? $status : null]);
    return (int)$db->lastInsertId();
}

function ensureDepartment(PDO $db, $name, $companyId)
{
    $name = trim((string)$name);
    if ($name === '') {
        return null;
    }

    if ($companyId === null) {
        $stmt = $db->prepare('SELECT department_id FROM departments WHERE LOWER(department_name) = LOWER(?) AND company_id IS NULL LIMIT 1');
        $stmt->execute([$name]);
    } else {
        $stmt = $db->prepare('SELECT department_id FROM departments WHERE LOWER(department_name) = LOWER(?) AND company_id = ? LIMIT 1');
        $stmt->execute([$name, $companyId]);
    }

    $id = $stmt->fetchColumn();
    if ($id !== false) {
        return (int)$id;
    }

    $stmt = $db->prepare('INSERT INTO departments (department_name, company_id) VALUES (?, ?)');
    $stmt->execute([$name, $companyId]);
    return (int)$db->lastInsertId();
}

function uploadedLogoPath()
{
    if (!isset($_FILES['logo']) || $_FILES['logo']['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }
    if ($_FILES['logo']['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException('The company logo upload failed.');
    }
    if ($_FILES['logo']['size'] > 5 * 1024 * 1024) {
        throw new RuntimeException('Company logos must be 5 MB or smaller.');
    }

    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($_FILES['logo']['tmp_name']);
    $extensions = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/gif' => 'gif',
        'image/webp' => 'webp',
    ];
    if (!isset($extensions[$mime])) {
        throw new RuntimeException('Company logos must be JPEG, PNG, GIF, or WebP images.');
    }

    $directory = __DIR__ . '/../../uploads/companylogos';
    if (!is_dir($directory) && !mkdir($directory, 0755, true) && !is_dir($directory)) {
        throw new RuntimeException('Could not create the company logo directory.');
    }

    $filename = bin2hex(random_bytes(16)) . '.' . $extensions[$mime];
    if (!move_uploaded_file($_FILES['logo']['tmp_name'], $directory . DIRECTORY_SEPARATOR . $filename)) {
        throw new RuntimeException('Could not save the company logo.');
    }
    return 'uploads/companylogos/' . $filename;
}

function saveRoleAssociation(PDO $db, $role, $userId, $schooluserId, array $input, $existingIoId = null)
{
    $companyName = trim($input['company_name']);
    $schoolName = trim($input['school_name']);
    $detail = trim($input['role_detail']);
    $companyStatus = trim($input['company_status']);

    if ($role === 'intern') {
        $stmt = $db->prepare('SELECT intern_id FROM interns WHERE schooluser_id = ? LIMIT 1');
        $stmt->execute([$schooluserId]);
        if ($stmt->fetchColumn() === false) {
            $stmt = $db->prepare('INSERT INTO interns (schooluser_id) VALUES (?)');
            $stmt->execute([$schooluserId]);
        }
    } elseif ($role === 'supervisor') {
        $companyId = ensureCompany($db, $companyName, trim($input['business_address']), $companyStatus);
        $departmentId = ensureDepartment($db, $detail, $companyId);
        $stmt = $db->prepare('SELECT supervisor_id FROM supervisors WHERE user_id = ? LIMIT 1');
        $stmt->execute([$userId]);
        $supervisorId = $stmt->fetchColumn();
        if ($supervisorId === false) {
            $stmt = $db->prepare('INSERT INTO supervisors (user_id, supervisor_jobrole, department_id) VALUES (?, ?, ?)');
            $stmt->execute([$userId, 'Industry Partner', $departmentId]);
        } else {
            $stmt = $db->prepare('UPDATE supervisors SET department_id = ? WHERE supervisor_id = ?');
            $stmt->execute([$departmentId, $supervisorId]);
        }
    } elseif ($role === 'executive') {
        $schoolId = ensureSchool($db, $schoolName);
        $stmt = $db->prepare('SELECT exd_id FROM executivedirectors WHERE schooluser_id = ? LIMIT 1');
        $stmt->execute([$schooluserId]);
        $directorId = $stmt->fetchColumn();
        if ($directorId === false) {
            $stmt = $db->prepare('INSERT INTO executivedirectors (schooluser_id, school_id) VALUES (?, ?)');
            $stmt->execute([$schooluserId, $schoolId]);
        } else {
            $stmt = $db->prepare('UPDATE executivedirectors SET school_id = ? WHERE exd_id = ?');
            $stmt->execute([$schoolId, $directorId]);
        }
    } elseif ($role === 'program') {
        $schoolId = ensureSchool($db, $schoolName);
        $programId = ensureProgram($db, $detail, $schoolId);
        $stmt = $db->prepare('SELECT pd_id FROM programdirectors WHERE schooluser_id = ? LIMIT 1');
        $stmt->execute([$schooluserId]);
        $directorId = $stmt->fetchColumn();
        if ($directorId === false) {
            $stmt = $db->prepare('INSERT INTO programdirectors (schooluser_id, program_id) VALUES (?, ?)');
            $stmt->execute([$schooluserId, $programId]);
        } else {
            $stmt = $db->prepare('UPDATE programdirectors SET program_id = ? WHERE pd_id = ?');
            $stmt->execute([$programId, $directorId]);
        }
    } elseif ($role === 'officer') {
        if ($existingIoId === null) {
            $stmt = $db->prepare('INSERT INTO internshipofficers () VALUES ()');
            $stmt->execute();
            return (int)$db->lastInsertId();
        }
    }

    return $existingIoId;
}

function saveDetails(PDO $db, $userId, $roleDetail, $companyName, $schoolName, $directoryStatus, $companyStatus, $address, $hours, $logoPath, $ioId)
{
    $stmt = $db->prepare("
        INSERT INTO io_user_details
            (user_id, role_detail, company_name, school_name, directory_status, company_status, business_address, business_hours, logo_path, io_id)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE
            role_detail = VALUES(role_detail),
            company_name = VALUES(company_name),
            school_name = VALUES(school_name),
            directory_status = VALUES(directory_status),
            company_status = VALUES(company_status),
            business_address = VALUES(business_address),
            business_hours = VALUES(business_hours),
            logo_path = VALUES(logo_path),
            io_id = VALUES(io_id)
    ");
    $stmt->execute([
        $userId,
        $roleDetail !== '' ? $roleDetail : null,
        $companyName !== '' ? $companyName : null,
        $schoolName !== '' ? $schoolName : null,
        $directoryStatus !== '' ? $directoryStatus : null,
        $companyStatus !== '' ? $companyStatus : null,
        $address !== '' ? $address : null,
        $hours !== '' ? $hours : null,
        $logoPath,
        $ioId,
    ]);
}

function readInput()
{
    $name = trim($_POST['name'] ?? '');
    $nameParts = preg_split('/\s+/', $name, 2);
    $roleKey = $_POST['role'] ?? '';
    $role = roleMap($roleKey);
    $email = trim($_POST['email'] ?? '');
    $companyName = trim($_POST['company_name'] ?? '');
    $schoolName = trim($_POST['school_name'] ?? '');
    $roleDetail = trim($_POST['role_detail'] ?? '');
    $status = trim($_POST['status'] ?? '');
    $partnerStatus = trim($_POST['company_status'] ?? '');
    if ($role === 'supervisor' && $partnerStatus === '') {
        $partnerStatus = $status;
    }

    if ($role === null) {
        respond(['error' => 'Please select a valid user role.'], 400);
    }
    if ($name === '' || $email === '') {
        respond(['error' => 'Name and email are required.'], 400);
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        respond(['error' => 'Enter a valid email address.'], 400);
    }
    if ($role === 'intern' && $status !== '' && !in_array($status, ['Active', 'Waiting', 'Undeployed'], true)) {
        respond(['error' => 'Select a valid student status.'], 400);
    }
    if ($role === 'supervisor' && $partnerStatus !== '' && !in_array($partnerStatus, ['New Partner', 'Active Partner', 'Inactive Partner'], true)) {
        respond(['error' => 'Select a valid partner status.'], 400);
    }

    return [
        'role_key' => $roleKey,
        'role' => $role,
        'first_name' => $nameParts[0],
        'last_name' => isset($nameParts[1]) ? $nameParts[1] : '',
        'email' => $email,
        'company_name' => $companyName,
        'school_name' => $schoolName,
        'role_detail' => $roleDetail,
        'status' => $status,
        'company_status' => $partnerStatus,
        'business_address' => trim($_POST['business_address'] ?? ''),
        'business_hours' => trim($_POST['business_hours'] ?? ''),
    ];
}

if (($_GET['action'] ?? '') === 'list' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        $stmt = $conn->query("
            SELECT
                u.user_id,
                u.user_first_name,
                u.user_last_name,
                u.user_email,
                u.user_role,
                su.schooluser_id,
                d.role_detail,
                d.company_name,
                d.school_name,
                d.directory_status,
                d.company_status,
                d.business_address,
                d.business_hours,
                d.logo_path,
                d.io_id,
                COALESCE(d.company_name, (
                    SELECT c.company_name
                    FROM supervisors s
                    JOIN departments dept ON dept.department_id = s.department_id
                    JOIN companies c ON c.company_id = dept.company_id
                    WHERE s.user_id = u.user_id
                    LIMIT 1
                )) AS linked_company,
                COALESCE(d.role_detail, (
                    SELECT p.program_name
                    FROM interns i
                    JOIN schoolusers si ON si.schooluser_id = i.schooluser_id
                    LEFT JOIN programs p ON p.program_id = i.program_id
                    WHERE si.user_id = u.user_id
                    LIMIT 1
                ), (
                    SELECT dept.department_name
                    FROM supervisors s
                    JOIN departments dept ON dept.department_id = s.department_id
                    WHERE s.user_id = u.user_id
                    LIMIT 1
                )) AS linked_detail,
                COALESCE(d.school_name, (
                    SELECT sc.school_name
                    FROM executivedirectors ex
                    JOIN schoolusers se ON se.schooluser_id = ex.schooluser_id
                    JOIN schools sc ON sc.school_id = ex.school_id
                    WHERE se.user_id = u.user_id
                    LIMIT 1
                ), (
                    SELECT sc.school_name
                    FROM programdirectors pd
                    JOIN schoolusers sp ON sp.schooluser_id = pd.schooluser_id
                    JOIN programs p ON p.program_id = pd.program_id
                    JOIN schools sc ON sc.school_id = p.school_id
                    WHERE sp.user_id = u.user_id
                    LIMIT 1
                )) AS linked_school,
                COALESCE(d.directory_status, (
                    SELECT st.status_text
                    FROM interns i
                    JOIN schoolusers si ON si.schooluser_id = i.schooluser_id
                    JOIN internships ip ON ip.intern_id = i.intern_id
                    JOIN statuses st ON st.internship_id = ip.internship_id
                    WHERE si.user_id = u.user_id
                    ORDER BY st.status_date_created DESC
                    LIMIT 1
                )) AS linked_status,
                COALESCE(d.company_status, (
                    SELECT c.partnership_status
                    FROM supervisors s
                    JOIN departments dept ON dept.department_id = s.department_id
                    JOIN companies c ON c.company_id = dept.company_id
                    WHERE s.user_id = u.user_id
                    LIMIT 1
                )) AS linked_company_status
            FROM users u
            LEFT JOIN schoolusers su ON su.user_id = u.user_id
            LEFT JOIN io_user_details d ON d.user_id = u.user_id
            WHERE COALESCE(u.user_is_archived, 0) = 0
              AND u.user_role IN ('intern', 'supervisor', 'executive', 'program', 'officer')
            ORDER BY u.user_last_name, u.user_first_name
        ");

        $users = [];
        $seenUsers = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            if (isset($seenUsers[$row['user_id']])) {
                continue;
            }
            $seenUsers[$row['user_id']] = true;
            $roleKeys = [
                'intern' => 'student',
                'supervisor' => 'industry',
                'executive' => 'exec',
                'program' => 'prog',
                'officer' => 'officer',
            ];
            $users[] = [
                'id' => (int)$row['user_id'],
                'name' => trim($row['user_first_name'] . ' ' . $row['user_last_name']),
                'email' => $row['user_email'],
                'role' => $row['user_role'],
                'role_key' => $roleKeys[$row['user_role']],
                'company_name' => $row['linked_company'] ?? '',
                'school_name' => $row['linked_school'] ?? '',
                'role_detail' => $row['linked_detail'] ?? '',
                'status' => $row['linked_status'] ?? '',
                'company_status' => $row['linked_company_status'] ?? '',
                'business_address' => $row['business_address'] ?? '',
                'business_hours' => $row['business_hours'] ?? '',
                'logo_path' => $row['logo_path'] ?? '',
                'intern_count' => $row['role_detail'] !== null
                    ? (string)$row['role_detail']
                    : ($row['io_id'] === null ? '' : (string)$conn->query(
                        'SELECT COUNT(DISTINCT intern_id) FROM internships WHERE io_id = ' . (int)$row['io_id']
                    )->fetchColumn()),
            ];
        }
        respond(['users' => $users]);
    } catch (Throwable $e) {
        error_log('[IO users] ' . $e->getMessage());
        respond(['error' => 'Could not load users from the database.'], 500);
    }
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(['error' => 'Method not allowed.'], 405);
}

$action = $_POST['action'] ?? '';
if ($action === 'archive') {
    $userId = (int)($_POST['user_id'] ?? 0);
    if ($userId <= 0) {
        respond(['error' => 'Invalid user.'], 400);
    }

    try {
        $stmt = $conn->prepare("
            UPDATE users SET user_is_archived = 1
            WHERE user_id = ?
              AND user_role IN ('intern', 'supervisor', 'executive', 'program', 'officer')
              AND COALESCE(user_is_archived, 0) = 0
        ");
        $stmt->execute([$userId]);
        if ($stmt->rowCount() === 0) {
            respond(['error' => 'User not found or already archived.'], 404);
        }
        respond(['success' => true, 'message' => 'User archived.']);
    } catch (Throwable $e) {
        error_log('[IO users] ' . $e->getMessage());
        respond(['error' => 'Could not archive this user.'], 500);
    }
}

if (!in_array($action, ['create', 'update'], true)) {
    respond(['error' => 'Unknown action.'], 400);
}

$input = readInput();
$userId = (int)($_POST['user_id'] ?? 0);
if ($action === 'update' && $userId <= 0) {
    respond(['error' => 'Invalid user.'], 400);
}

$logoPath = null;
$newLogoAbsolutePath = null;
try {
    $logoPath = uploadedLogoPath();
    if ($logoPath !== null) {
        $newLogoAbsolutePath = __DIR__ . '/../../' . str_replace('/', DIRECTORY_SEPARATOR, $logoPath);
    }

    $conn->beginTransaction();
    $schooluserId = null;
    $ioId = null;

    if ($action === 'create') {
        $givenId = 'IO-' . strtoupper(bin2hex(random_bytes(8)));
        $stmt = $conn->prepare('INSERT INTO users (user_first_name, user_last_name, user_email, user_role) VALUES (?, ?, ?, ?)');
        $stmt->execute([$input['first_name'], $input['last_name'], $input['email'], $input['role']]);
        $userId = (int)$conn->lastInsertId();

        $stmt = $conn->prepare('INSERT INTO schoolusers (user_id, schooluser_given_id, schooluser_password) VALUES (?, ?, NULL)');
        $stmt->execute([$userId, $givenId]);
        $schooluserId = (int)$conn->lastInsertId();
    } else {
        $stmt = $conn->prepare('SELECT user_role FROM users WHERE user_id = ? AND COALESCE(user_is_archived, 0) = 0 FOR UPDATE');
        $stmt->execute([$userId]);
        $currentRole = $stmt->fetchColumn();
        if ($currentRole === false) {
            $conn->rollBack();
            if ($newLogoAbsolutePath !== null && is_file($newLogoAbsolutePath)) {
                unlink($newLogoAbsolutePath);
            }
            respond(['error' => 'User not found.'], 404);
        }
        if ($currentRole !== $input['role']) {
            $conn->rollBack();
            if ($newLogoAbsolutePath !== null && is_file($newLogoAbsolutePath)) {
                unlink($newLogoAbsolutePath);
            }
            respond(['error' => 'A user role cannot be changed while editing.'], 400);
        }

        $stmt = $conn->prepare('UPDATE users SET user_first_name = ?, user_last_name = ?, user_email = ? WHERE user_id = ?');
        $stmt->execute([$input['first_name'], $input['last_name'], $input['email'], $userId]);
        $stmt = $conn->prepare('SELECT schooluser_id FROM schoolusers WHERE user_id = ? LIMIT 1');
        $stmt->execute([$userId]);
        $schooluserId = $stmt->fetchColumn();
        if ($schooluserId === false) {
            $givenId = 'IO-' . strtoupper(bin2hex(random_bytes(8)));
            $stmt = $conn->prepare('INSERT INTO schoolusers (user_id, schooluser_given_id, schooluser_password) VALUES (?, ?, NULL)');
            $stmt->execute([$userId, $givenId]);
            $schooluserId = $conn->lastInsertId();
        }
        $schooluserId = (int)$schooluserId;
        $stmt = $conn->prepare('SELECT io_id, logo_path FROM io_user_details WHERE user_id = ?');
        $stmt->execute([$userId]);
        $details = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
        $ioId = isset($details['io_id']) && $details['io_id'] !== null ? (int)$details['io_id'] : null;
        if ($logoPath === null) {
            $logoPath = $details['logo_path'] ?? null;
        }
    }

    $ioId = saveRoleAssociation($conn, $input['role'], $userId, $schooluserId, $input, $ioId);
    saveDetails(
        $conn,
        $userId,
        $input['role_detail'],
        $input['company_name'],
        $input['school_name'],
        $input['role'] === 'intern' ? $input['status'] : '',
        $input['role'] === 'supervisor' ? $input['company_status'] : '',
        $input['business_address'],
        $input['business_hours'],
        $logoPath,
        $ioId
    );
    $conn->commit();
    respond(['success' => true, 'message' => $action === 'create' ? 'User added.' : 'User updated.']);
} catch (Throwable $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    if ($newLogoAbsolutePath !== null && is_file($newLogoAbsolutePath)) {
        unlink($newLogoAbsolutePath);
    }
    if ($e instanceof PDOException && $e->getCode() === '23000') {
        respond(['error' => 'That email or account identifier is already in use.'], 409);
    }
    error_log('[IO users] ' . $e->getMessage());
    respond(['error' => $e instanceof RuntimeException ? $e->getMessage() : 'Could not save this user.'], 500);
}
