<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require '../../utilities/database/database.php';

$action = $_POST['action'] ?? $_GET['action'] ?? '';

if ($action === 'get_schools') {
    header('Content-Type: application/json');

    $stmt = $conn->query("SELECT school_id AS id, school_name FROM schools ORDER BY school_name ASC");
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    exit;
}

if ($action === 'add_school') {
    $name = trim($_POST['school_name']);

    if ($name === '') {
        echo "School name cannot be empty.";
        exit;
    }

    // 🔍 check if exists
    $stmt = $conn->prepare("SELECT COUNT(*) FROM schools WHERE school_name = :name");
    $stmt->execute(['name' => $name]);

    if ($stmt->fetchColumn() > 0) {
        echo "School already exists.";
        exit;
    }

    // ✅ insert if not exists
    $stmt = $conn->prepare("INSERT INTO schools (school_name) VALUES (:name)");
    $stmt->execute(['name' => $name]);

    echo "School added successfully.";
}

if ($action === 'add_program') {
    $name = trim($_POST['program_name']);
    $school_id = intval($_POST['school_id']);

    if ($name === '' || $school_id <= 0) {
        echo "Please fill in all fields.";
        exit;
    }

    $stmt = $conn->prepare("INSERT INTO programs (program_name, school_id) VALUES (:name, :school_id)");
    $stmt->execute([
        'name' => $name,
        'school_id' => $school_id
    ]);

    echo "Program added successfully.";
}

if ($action === 'get_all_data') {
    header('Content-Type: application/json');

    $stmt = $conn->query("
        SELECT 
            s.school_id,
            s.school_name,
            p.program_id,
            p.program_name
        FROM schools s
        LEFT JOIN programs p ON s.school_id = p.school_id
        ORDER BY s.school_name
    ");

    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $data = [];

    foreach ($rows as $row) {
        $sid = $row['school_id'];

        if (!isset($data[$sid])) {
            $data[$sid] = [
                'school_id' => $row['school_id'],
                'school_name' => $row['school_name'],
                'programs' => []
            ];
        }

        if (!empty($row['program_id'])) {
            $data[$sid]['programs'][] = [
                'program_id' => $row['program_id'],
                'program_name' => $row['program_name']
            ];
        }
    }

    echo json_encode(array_values($data));
    exit;
}

if ($action === 'delete_school') {
    $id = intval($_POST['school_id']);

    $stmt = $conn->prepare("DELETE FROM schools WHERE school_id = :id");
    $stmt->execute(['id' => $id]);

    echo "School deleted.";
    exit;
}

if ($action === 'delete_program') {
    $id = intval($_POST['program_id']);

    $stmt = $conn->prepare("DELETE FROM programs WHERE program_id = :id");
    $stmt->execute(['id' => $id]);

    echo "Program deleted.";
    exit;
}

if ($action === 'edit_school') {
    $id = intval($_POST['school_id']);
    $name = trim($_POST['school_name']);

    $stmt = $conn->prepare("
        UPDATE schools 
        SET school_name = :name 
        WHERE school_id = :id
    ");

    $stmt->execute([
        'name' => $name,
        'id' => $id
    ]);

    echo "School updated.";
    exit;
}

if ($action === 'edit_program') {
    $id = intval($_POST['program_id']);
    $name = trim($_POST['program_name']);

    $stmt = $conn->prepare("
        UPDATE programs 
        SET program_name = :name 
        WHERE program_id = :id
    ");

    $stmt->execute([
        'name' => $name,
        'id' => $id
    ]);

    echo "Program updated.";
    exit;
}

?>
