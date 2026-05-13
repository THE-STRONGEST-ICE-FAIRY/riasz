<?php

require '../../utilities/database/database.php';

$users = [];
try {
    $stmt = $conn->query("SELECT user_id, user_first_name, user_last_name, user_email, user_role, user_is_archived, user_date_created FROM users");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $db_error = "Could not fetch users: " . $e->getMessage();
}

// Fetch Schools and Programs for the new visualization
$schoolsData = [];
try {
    // Fetch all schools
    $schoolStmt = $conn->query("SELECT * FROM schools ORDER BY school_name ASC");
    $tempSchools = $schoolStmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch all programs
    $programStmt = $conn->query("SELECT * FROM programs ORDER BY program_name ASC");
    $tempPrograms = $programStmt->fetchAll(PDO::FETCH_ASSOC);

    // Organize programs under schools
    foreach ($tempSchools as $school) {
        $schoolId = $school['school_id'];
        $school['programs'] = array_filter($tempPrograms, function($p) use ($schoolId) {
            return $p['school_id'] == $schoolId;
        });
        $schoolsData[] = $school;
    }
} catch (Exception $e) {
    $db_error = "Could not fetch schools/programs: " . $e->getMessage();
}

// Output the table content
echo "<thead>
    <tr>
        <th>User ID</th>
        <th>Full Name</th>
        <th>Email Address</th>
        <th>Role</th>
        <th>Status</th>
        <th>Date Created</th>
    </tr>
</thead>
<tbody>";

// If there are no users
if (empty($users)) {
    echo "<tr>
            <td colspan='6' style='text-align: center; padding: 48px; color: var(--text-muted);'>
                No user records found in the database.
            </td>
        </tr>";
} else {
    // Loop through each user and generate a table row
    foreach ($users as $user) {
        // Replacing shorthand null coalescing and ternary with more explicit conditional logic
        if (isset($user['user_is_archived'])) {
            $isArchived = (bool)$user['user_is_archived'];
        } else {
            $isArchived = false;
        }

        if ($isArchived) {
            $statusText = 'Archived';
            $statusClass = 'badge-archived';
        } else {
            $statusText = 'Active';
            $statusClass = 'badge-active';
        }

        if (isset($user['user_role']) && $user['user_role']) {
            $userRole = $user['user_role'];
        } else {
            $userRole = 'User';
        }

        echo "<tr>
                <td style='font-family: monospace; font-weight: 600; color: var(--primary);'>#{$user['user_id']}</td>
                <td>
                    <div style='font-weight: 600;'>{$user['user_first_name']} {$user['user_last_name']}</div>
                </td>
                <td style='color: var(--text-muted);'>{$user['user_email']}</td>
                <td><span style='font-weight: 500;'>{$userRole}</span></td>
                <td><span class='badge {$statusClass}'>{$statusText}</span></td>
                <td style='font-size: 13px; color: var(--text-muted);'>" . date('M d, Y', strtotime($user['user_date_created'])) . "</td>
            </tr>";
    }
}

echo "</tbody>";
?>