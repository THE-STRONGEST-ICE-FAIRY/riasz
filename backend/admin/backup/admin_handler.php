<?php
// Set to false in production so errors aren't shown to users.
$debug = true;
ini_set('display_errors', $debug ? 1 : 0);
error_reporting(E_ALL);

require '../../utilities/database/database.php';

$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$action = $_POST['action'] ?? $_GET['action'] ?? '';

// Everything except reading data must be a POST request.
$readOnlyActions = ['get_schools', 'get_all_data', 'get_schools_table'];
if (!in_array($action, $readOnlyActions, true) && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo "Method not allowed.";
    exit;
}

function fail($message, $code = 400) {
    http_response_code($code);
    echo $message;
    exit;
}

try {
    switch ($action) {

        case 'get_schools':
            header('Content-Type: application/json');
            $stmt = $conn->query("SELECT school_id AS id, school_name FROM schools ORDER BY school_name ASC");
            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
            break;

        case 'get_all_data':
            header('Content-Type: application/json');
            $stmt = $conn->query("
                SELECT s.school_id, s.school_name, p.program_id, p.program_name
                FROM schools s
                LEFT JOIN programs p ON s.school_id = p.school_id
                ORDER BY s.school_name, p.program_name
            ");

            $data = [];
            foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
                $sid = $row['school_id'];
                if (!isset($data[$sid])) {
                    $data[$sid] = [
                        'school_id'   => $row['school_id'],
                        'school_name' => $row['school_name'],
                        'programs'    => []
                    ];
                }
                if (!empty($row['program_id'])) {
                    $data[$sid]['programs'][] = [
                        'program_id'   => $row['program_id'],
                        'program_name' => $row['program_name']
                    ];
                }
            }
            echo json_encode(array_values($data));
            break;

        case 'get_schools_table':
            header('Content-Type: application/json');
            $schools  = $conn->query("SELECT school_id, school_name FROM schools ORDER BY school_name ASC")->fetchAll(PDO::FETCH_ASSOC);
            $programs = $conn->query("SELECT program_id, program_name, school_id FROM programs ORDER BY program_name ASC")->fetchAll(PDO::FETCH_ASSOC);
            $execRows = $conn->query("
                SELECT ed.school_id, u.user_first_name, u.user_last_name
                FROM executivedirectors ed
                JOIN schoolusers su ON su.schooluser_id = ed.schooluser_id
                JOIN users u ON u.user_id = su.user_id
            ")->fetchAll(PDO::FETCH_ASSOC);
            $pdRows = $conn->query("
                SELECT pd.program_id, u.user_first_name, u.user_last_name
                FROM programdirectors pd
                JOIN schoolusers su ON su.schooluser_id = pd.schooluser_id
                JOIN users u ON u.user_id = su.user_id
            ")->fetchAll(PDO::FETCH_ASSOC);

            $pdByProgram = [];
            foreach ($pdRows as $pd) {
                $pdByProgram[$pd['program_id']][] = trim($pd['user_first_name'] . ' ' . $pd['user_last_name']);
            }

            $byId = [];
            foreach ($schools as $s) {
                $byId[$s['school_id']] = [
                    'school_id'   => $s['school_id'],
                    'school_name' => $s['school_name'],
                    'programs'    => [],
                    'executives'  => []
                ];
            }
            foreach ($programs as $p) {
                if (isset($byId[$p['school_id']])) {
                    $pId = $p['program_id'];
                    $byId[$p['school_id']]['programs'][] = [
                        'program_id'   => $pId,
                        'program_name' => $p['program_name'],
                        'directors'    => $pdByProgram[$pId] ?? []
                    ];
                }
            }
            foreach ($execRows as $e) {
                if (isset($byId[$e['school_id']])) {
                    $byId[$e['school_id']]['executives'][] = trim($e['user_first_name'] . ' ' . $e['user_last_name']);
                }
            }
            echo json_encode(array_values($byId));
            break;

        case 'delete_schools':
            $raw = $_POST['ids'] ?? [];
            if (!is_array($raw)) $raw = [$raw];
            $ids = array_values(array_unique(array_filter(array_map('intval', $raw), function ($i) { return $i > 0; })));
            if (!$ids) fail("No schools selected.");
            if (count($ids) > 500) fail("Too many schools selected at once.");

            $conn->beginTransaction();
            try {
                foreach ($ids as $sid) {
                    $conn->prepare("DELETE FROM programs WHERE school_id = :id")->execute(['id' => $sid]);
                    $conn->prepare("DELETE FROM schools WHERE school_id = :id")->execute(['id' => $sid]);
                }
                $conn->commit();
            } catch (PDOException $e) {
                $conn->rollBack();
                throw $e;
            }
            echo count($ids) . " school(s) deleted.";
            break;

        case 'add_school':
            $name = trim($_POST['school_name'] ?? '');
            if ($name === '') fail("School name cannot be empty.");

            $stmt = $conn->prepare("SELECT COUNT(*) FROM schools WHERE school_name = :name");
            $stmt->execute(['name' => $name]);
            if ($stmt->fetchColumn() > 0) fail("School already exists.", 409);

            $conn->beginTransaction();
            try {
                $stmt = $conn->prepare("INSERT INTO schools (school_name) VALUES (:name)");
                $stmt->execute(['name' => $name]);
                $newSchoolId = $conn->lastInsertId();

                $optPrograms = $_POST['optional_programs'] ?? [];
                if (is_array($optPrograms)) {
                    $progStmt = $conn->prepare("INSERT INTO programs (program_name, school_id) VALUES (:pname, :sid)");
                    $seen = [];
                    foreach ($optPrograms as $p) {
                        $pname = trim((string)$p);
                        if ($pname !== '' && !in_array(strtolower($pname), $seen, true)) {
                            $seen[] = strtolower($pname);
                            $progStmt->execute(['pname' => $pname, 'sid' => $newSchoolId]);
                        }
                    }
                }
                $conn->commit();
            } catch (PDOException $e) {
                $conn->rollBack();
                throw $e;
            }
            echo "School added successfully.";
            break;

        case 'add_program':
            $name = trim($_POST['program_name'] ?? '');
            $school_id = intval($_POST['school_id'] ?? 0);
            if ($name === '' || $school_id <= 0) fail("Please fill in all fields.");

            $stmt = $conn->prepare("SELECT COUNT(*) FROM programs WHERE program_name = :name AND school_id = :sid");
            $stmt->execute(['name' => $name, 'sid' => $school_id]);
            if ($stmt->fetchColumn() > 0) fail("That program already exists for this school.", 409);

            $stmt = $conn->prepare("INSERT INTO programs (program_name, school_id) VALUES (:name, :school_id)");
            $stmt->execute(['name' => $name, 'school_id' => $school_id]);
            echo "Program added successfully.";
            break;

        case 'edit_school':
            $id = intval($_POST['school_id'] ?? 0);
            $name = trim($_POST['school_name'] ?? '');
            if ($id <= 0 || $name === '') fail("School name cannot be empty.");

            $stmt = $conn->prepare("SELECT COUNT(*) FROM schools WHERE school_name = :name AND school_id <> :id");
            $stmt->execute(['name' => $name, 'id' => $id]);
            if ($stmt->fetchColumn() > 0) fail("Another school already has that name.", 409);

            $stmt = $conn->prepare("UPDATE schools SET school_name = :name WHERE school_id = :id");
            $stmt->execute(['name' => $name, 'id' => $id]);
            echo "School updated.";
            break;

        case 'edit_program':
            $id = intval($_POST['program_id'] ?? 0);
            $name = trim($_POST['program_name'] ?? '');
            if ($id <= 0 || $name === '') fail("Program name cannot be empty.");

            $stmt = $conn->prepare("UPDATE programs SET program_name = :name WHERE program_id = :id");
            $stmt->execute(['name' => $name, 'id' => $id]);
            echo "Program updated.";
            break;

        case 'delete_school':
            $id = intval($_POST['school_id'] ?? 0);
            if ($id <= 0) fail("Invalid school.");

            $conn->beginTransaction();
            try {
                $stmt = $conn->prepare("DELETE FROM programs WHERE school_id = :id");
                $stmt->execute(['id' => $id]);
                $stmt = $conn->prepare("DELETE FROM schools WHERE school_id = :id");
                $stmt->execute(['id' => $id]);
                $conn->commit();
            } catch (PDOException $e) {
                $conn->rollBack();
                throw $e;
            }
            echo "School deleted.";
            break;

        case 'delete_program':
            $id = intval($_POST['program_id'] ?? 0);
            if ($id <= 0) fail("Invalid program.");

            $stmt = $conn->prepare("DELETE FROM programs WHERE program_id = :id");
            $stmt->execute(['id' => $id]);
            echo "Program deleted.";
            break;

        default:
            fail("Unknown action.");
    }
} catch (PDOException $e) {
    if ($e->getCode() === '23000') {
        fail("Can't complete that: other records (such as users) still depend on it.", 409);
    }
    error_log($e->getMessage());
    fail($debug ? "Database error: " . $e->getMessage() : "A database error occurred.", 500);
}
