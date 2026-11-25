<?php

require '../../utilities/database/database.php';

header('Content-Type: application/json; charset=utf-8');

// Returns every user as JSON. Searching, filtering, sorting and paging
// happen in the browser (see datatable.js).
try {
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $conn->query("
        SELECT u.user_id, u.user_first_name, u.user_last_name, u.user_email, u.user_role,
               u.user_is_archived, u.user_date_created,
               su.schooluser_given_id,
               COALESCE(s1.school_name, s2.school_name) AS school_name,
               p.program_name
        FROM users u
        LEFT JOIN schoolusers su ON su.user_id = u.user_id
        LEFT JOIN executivedirectors ed ON ed.schooluser_id = su.schooluser_id
        LEFT JOIN schools s1 ON s1.school_id = ed.school_id
        LEFT JOIN programdirectors pd ON pd.schooluser_id = su.schooluser_id
        LEFT JOIN programs p ON p.program_id = pd.program_id
        LEFT JOIN schools s2 ON s2.school_id = p.school_id
        ORDER BY u.user_id DESC
    ");

    $users = [];
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $id = $row['user_id'];
        if (isset($users[$id])) continue; // joins can repeat a user; keep the first row

        $users[$id] = [
            'id'         => (int)$id,
            'first_name' => (string)$row['user_first_name'],
            'last_name'  => (string)$row['user_last_name'],
            'email'      => (string)$row['user_email'],
            'role'       => (string)($row['user_role'] ?? ''),
            'archived'   => !empty($row['user_is_archived']),
            'created'    => $row['user_date_created'],
            'given_id'   => $row['schooluser_given_id'],
            'school'     => $row['school_name'],
            'program'    => $row['program_name'],
        ];
    }

    echo json_encode(['users' => array_values($users)]);
} catch (Throwable $e) {
    error_log($e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Could not fetch users.']);
}
