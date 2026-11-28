<?php
session_start();
require '../../utilities/database/database.php';

header('Content-Type: application/json; charset=utf-8');
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$userId = (int)($_SESSION['user_id'] ?? 0);
$sessionRole = strtolower(trim((string)($_SESSION['user_role'] ?? '')));
if ($userId <= 0 || !in_array($sessionRole, ['officer', 'internship officer'], true)) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Please sign in as an internship officer.']);
    exit;
}

function respond($payload, $status = 200)
{
    http_response_code($status);
    echo json_encode($payload, JSON_INVALID_UTF8_SUBSTITUTE);
    exit;
}

function validDate($value)
{
    if (!is_string($value) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
        return false;
    }

    $date = DateTime::createFromFormat('!Y-m-d', $value);
    return $date !== false && $date->format('Y-m-d') === $value;
}

function validTime($value)
{
    if ($value === '') {
        return true;
    }

    if (!is_string($value) || !preg_match('/^\d{2}:\d{2}$/', $value)) {
        return false;
    }

    $time = DateTime::createFromFormat('!H:i', $value);
    return $time !== false && $time->format('H:i') === $value;
}

function evaluationReport(PDO $db)
{
    $stmt = $db->query("
        SELECT
            u.user_id,
            u.user_first_name,
            u.user_last_name,
            u.user_email,
            COALESCE(ud.company_name, c.company_name, '') AS company_name,
            COUNT(DISTINCT ip.internship_id) AS assigned_count,
            COUNT(DISTINCT CASE
                WHEN graded.internship_id IS NOT NULL THEN ip.internship_id
            END) AS submitted_count
        FROM supervisors s
        JOIN users u ON u.user_id = s.user_id
        LEFT JOIN io_user_details ud ON ud.user_id = u.user_id
        LEFT JOIN departments d ON d.department_id = s.department_id
        LEFT JOIN companies c ON c.company_id = d.company_id
        JOIN internships ip ON ip.supervisor_id = s.supervisor_id
        JOIN interns i ON i.intern_id = ip.intern_id
        JOIN schoolusers su ON su.schooluser_id = i.schooluser_id
        JOIN users intern_user ON intern_user.user_id = su.user_id
        LEFT JOIN (
            SELECT DISTINCT internship_id
            FROM assessmentgrades
        ) graded ON graded.internship_id = ip.internship_id
        WHERE u.user_role = 'supervisor'
          AND COALESCE(u.user_is_archived, 0) = 0
          AND intern_user.user_role = 'intern'
          AND COALESCE(intern_user.user_is_archived, 0) = 0
        GROUP BY u.user_id, u.user_first_name, u.user_last_name, u.user_email, ud.company_name, c.company_name
        ORDER BY u.user_last_name, u.user_first_name
    ");

    $rows = [];
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $assigned = (int)$row['assigned_count'];
        $submitted = (int)$row['submitted_count'];
        if ($submitted === $assigned) {
            $status = 'Submitted';
        } elseif ($submitted === 0) {
            $status = 'Not Yet';
        } else {
            $status = 'Partially Submitted';
        }

        $rows[] = [
            'user_id' => (int)$row['user_id'],
            'name' => trim($row['user_first_name'] . ' ' . $row['user_last_name']),
            'email' => $row['user_email'],
            'company_name' => $row['company_name'],
            'assigned_count' => $assigned,
            'submitted_count' => $submitted,
            'status' => $status,
        ];
    }

    return $rows;
}

function notificationData(PDO $db, $userId)
{
    $stmt = $db->prepare("
        SELECT
            nr.notifrecip_id AS id,
            n.notif_type AS type,
            n.notif_message AS message,
            n.notif_date_created AS created_at,
            nr.notifrecip_is_read AS is_read
        FROM notificationrecipients nr
        JOIN notifications n ON n.notification_id = nr.notification_id
        WHERE nr.user_id = ?
        ORDER BY n.notif_date_created DESC, nr.notifrecip_id DESC
        LIMIT 50
    ");
    $stmt->execute([$userId]);
    $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $unreadCount = 0;
    foreach ($notifications as &$notification) {
        $notification['id'] = (int)$notification['id'];
        $notification['is_read'] = (int)$notification['is_read'];
        if ($notification['is_read'] === 0) {
            $unreadCount++;
        }
    }
    unset($notification);

    return [
        'notifications' => $notifications,
        'unread_count' => $unreadCount,
    ];
}

function dashboardData(PDO $db, $userId)
{
    $stmt = $db->query("
        SELECT
            COUNT(*) AS total_users,
            COALESCE(SUM(user_role = 'intern'), 0) AS student_interns,
            COALESCE(SUM(user_role = 'officer'), 0) AS internship_officers,
            COALESCE(SUM(user_role = 'program'), 0) AS program_directors,
            COALESCE(SUM(user_role = 'executive'), 0) AS executive_directors,
            COALESCE(SUM(user_role = 'supervisor'), 0) AS industry_partners
        FROM users
        WHERE COALESCE(user_is_archived, 0) = 0
    ");
    $roleCounts = $stmt->fetch(PDO::FETCH_ASSOC);
    foreach ($roleCounts as $key => $value) {
        $roleCounts[$key] = (int)$value;
    }

    $statusCounts = [
        'deployed' => 0,
        'waiting' => 0,
        'undeployed' => 0,
        'unlisted' => 0,
    ];
    $stmt = $db->query("
        SELECT
            u.user_id,
            ud.directory_status,
            (
                SELECT st.status_text
                FROM schoolusers su
                JOIN interns i ON i.schooluser_id = su.schooluser_id
                JOIN internships ip ON ip.intern_id = i.intern_id
                JOIN statuses st ON st.internship_id = ip.internship_id
                WHERE su.user_id = u.user_id
                ORDER BY st.status_date_created DESC, st.status_id DESC
                LIMIT 1
            ) AS latest_status,
            EXISTS (
                SELECT 1
                FROM schoolusers su
                JOIN interns i ON i.schooluser_id = su.schooluser_id
                JOIN internships ip ON ip.intern_id = i.intern_id
                WHERE su.user_id = u.user_id
            ) AS has_internship
        FROM users u
        LEFT JOIN io_user_details ud ON ud.user_id = u.user_id
        WHERE u.user_role = 'intern'
          AND COALESCE(u.user_is_archived, 0) = 0
    ");

    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $student) {
        $status = strtolower(trim((string)($student['latest_status'] ?: $student['directory_status'])));
        if (in_array($status, ['active', 'deployed'], true) || ($status === '' && (int)$student['has_internship'] === 1)) {
            $statusCounts['deployed']++;
        } elseif ($status === 'waiting') {
            $statusCounts['waiting']++;
        } elseif ($status === 'undeployed') {
            $statusCounts['undeployed']++;
        } else {
            $statusCounts['unlisted']++;
        }
    }

    $stmt = $db->prepare('
        SELECT academic_year_start, academic_year_end
        FROM io_dashboard_settings
        WHERE user_id = ?
        LIMIT 1
    ');
    $stmt->execute([$userId]);
    $academicYear = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    $academicYearLabel = $academicYear
        ? date('Y', strtotime($academicYear['academic_year_start'])) . '-' . date('Y', strtotime($academicYear['academic_year_end']))
        : 'All Academic Years';

    $eventsQuery = "
        SELECT
            e.event_id,
            e.event_title AS title,
            e.event_date AS date,
            e.event_description AS description,
            d.event_type,
            TIME_FORMAT(d.start_time, '%H:%i') AS start_time,
            TIME_FORMAT(d.end_time, '%H:%i') AS end_time,
            d.target_role,
            d.target_school_id,
            d.target_program_id,
            d.notify_all
        FROM io_dashboard_event_details d
        JOIN events e ON e.event_id = d.event_id
        WHERE d.owner_user_id = ?
    ";
    $eventParams = [$userId];
    if ($academicYear) {
        $eventsQuery .= ' AND e.event_date BETWEEN ? AND ?';
        $eventParams[] = $academicYear['academic_year_start'];
        $eventParams[] = $academicYear['academic_year_end'];
    }
    $eventsQuery .= ' ORDER BY e.event_date, e.event_id DESC LIMIT 100';
    $stmt = $db->prepare($eventsQuery);
    $stmt->execute($eventParams);
    $events = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $evaluationRows = evaluationReport($db);
    $evaluationProgress = ['total' => 0, 'submitted' => 0, 'pending' => 0];
    foreach ($evaluationRows as $row) {
        $evaluationProgress['total'] += $row['assigned_count'];
        $evaluationProgress['submitted'] += $row['submitted_count'];
    }
    $evaluationProgress['pending'] = $evaluationProgress['total'] - $evaluationProgress['submitted'];

    $notificationData = notificationData($db, $userId);

    $schools = $db->query('SELECT school_id AS id, school_name AS name FROM schools ORDER BY school_name')->fetchAll(PDO::FETCH_ASSOC);
    $programs = $db->query('SELECT program_id AS id, program_name AS name, school_id FROM programs ORDER BY program_name')->fetchAll(PDO::FETCH_ASSOC);

    return [
        'role_counts' => $roleCounts,
        'status_counts' => $statusCounts,
        'evaluation_progress' => $evaluationProgress,
        'evaluation_report' => $evaluationRows,
        'academic_year' => [
            'start' => $academicYear['academic_year_start'] ?? '',
            'end' => $academicYear['academic_year_end'] ?? '',
            'label' => $academicYearLabel,
        ],
        'events' => $events,
        'notifications' => $notificationData['notifications'],
        'unread_count' => $notificationData['unread_count'],
        'schools' => $schools,
        'programs' => $programs,
    ];
}

function ensureOfficerId(PDO $db, $userId)
{
    $stmt = $db->prepare('SELECT io_id FROM io_user_details WHERE user_id = ? LIMIT 1');
    $stmt->execute([$userId]);
    $ioId = $stmt->fetchColumn();
    if ($ioId !== false && $ioId !== null) {
        return (int)$ioId;
    }

    $db->exec('INSERT INTO internshipofficers () VALUES ()');
    $ioId = (int)$db->lastInsertId();
    $stmt = $db->prepare('
        INSERT INTO io_user_details (user_id, io_id)
        VALUES (?, ?)
        ON DUPLICATE KEY UPDATE io_id = VALUES(io_id)
    ');
    $stmt->execute([$userId, $ioId]);
    return $ioId;
}

function targetUsers(PDO $db, $role, $schoolId, $programId)
{
    if ($role === 'supervisor' && ($schoolId !== null || $programId !== null)) {
        respond(['status' => 'error', 'message' => 'School and program filters are not available for industry partners.'], 400);
    }
    if ($role === 'executive' && $programId !== null) {
        respond(['status' => 'error', 'message' => 'Program filters are not available for executive directors.'], 400);
    }

    if ($role === 'intern') {
        $query = "
            SELECT DISTINCT u.user_id
            FROM users u
            JOIN schoolusers su ON su.user_id = u.user_id
            JOIN interns i ON i.schooluser_id = su.schooluser_id
            LEFT JOIN programs p ON p.program_id = i.program_id
            WHERE u.user_role = 'intern' AND COALESCE(u.user_is_archived, 0) = 0
        ";
    } elseif ($role === 'program') {
        $query = "
            SELECT DISTINCT u.user_id
            FROM users u
            JOIN schoolusers su ON su.user_id = u.user_id
            JOIN programdirectors pd ON pd.schooluser_id = su.schooluser_id
            JOIN programs p ON p.program_id = pd.program_id
            WHERE u.user_role = 'program' AND COALESCE(u.user_is_archived, 0) = 0
        ";
    } elseif ($role === 'executive') {
        $query = "
            SELECT DISTINCT u.user_id
            FROM users u
            JOIN schoolusers su ON su.user_id = u.user_id
            JOIN executivedirectors ed ON ed.schooluser_id = su.schooluser_id
            WHERE u.user_role = 'executive' AND COALESCE(u.user_is_archived, 0) = 0
        ";
    } elseif ($role === 'supervisor') {
        $query = "
            SELECT DISTINCT u.user_id
            FROM users u
            JOIN supervisors s ON s.user_id = u.user_id
            WHERE u.user_role = 'supervisor' AND COALESCE(u.user_is_archived, 0) = 0
        ";
    } else {
        respond(['status' => 'error', 'message' => 'Select a valid recipient role.'], 400);
    }

    $params = [];
    if ($schoolId !== null) {
        if ($role === 'executive') {
            $query .= ' AND ed.school_id = ?';
        } elseif ($role === 'intern' || $role === 'program') {
            $query .= ' AND p.school_id = ?';
        }
        $params[] = $schoolId;
    }
    if ($programId !== null) {
        $query .= ' AND p.program_id = ?';
        $params[] = $programId;
    }

    $stmt = $db->prepare($query);
    $stmt->execute($params);
    return array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN));
}

function createNotification(PDO $db, $senderId, $type, $message, array $recipientIds)
{
    $recipientIds = array_values(array_unique(array_filter(array_map('intval', $recipientIds))));
    if (!$recipientIds) {
        return null;
    }

    $stmt = $db->prepare('
        INSERT INTO notifications (user_id, notif_type, notif_message)
        VALUES (?, ?, ?)
    ');
    $stmt->execute([$senderId, $type, $message]);
    $notificationId = (int)$db->lastInsertId();
    $stmt = $db->prepare('
        INSERT INTO notificationrecipients (notification_id, user_id)
        VALUES (?, ?)
    ');
    foreach ($recipientIds as $recipientId) {
        $stmt->execute([$notificationId, $recipientId]);
    }
    return $notificationId;
}

function saveDashboardEvent(PDO $db, $userId)
{
    $title = trim((string)($_POST['title'] ?? ''));
    $date = trim((string)($_POST['date'] ?? ''));
    $startTime = trim((string)($_POST['start_time'] ?? ''));
    $endTime = trim((string)($_POST['end_time'] ?? ''));
    $description = trim((string)($_POST['description'] ?? ''));
    $eventType = trim((string)($_POST['event_type'] ?? 'task'));
    $targetRole = trim((string)($_POST['target_role'] ?? ''));
    $schoolId = (int)($_POST['target_school_id'] ?? 0);
    $programId = (int)($_POST['target_program_id'] ?? 0);
    $notifyAll = ($_POST['notify_all'] ?? '') === '1';
    $notificationMethod = strtolower(trim((string)($_POST['notification_method'] ?? 'system')));

    if ($title === '' || strlen($title) > 255 || !validDate($date)) {
        respond(['status' => 'error', 'message' => 'Enter a title of 255 characters or fewer and a valid date.'], 400);
    }
    if (!validTime($startTime) || !validTime($endTime)) {
        respond(['status' => 'error', 'message' => 'Enter valid start and end times.'], 400);
    }
    if ($startTime !== '' && $endTime !== '' && $endTime < $startTime) {
        respond(['status' => 'error', 'message' => 'The end time must be after the start time.'], 400);
    }
    if (!in_array($eventType, ['event', 'task'], true)) {
        respond(['status' => 'error', 'message' => 'Select Event or Task.'], 400);
    }
    if (!in_array($notificationMethod, ['system', 'email'], true)) {
        respond(['status' => 'error', 'message' => 'Select a valid notification method.'], 400);
    }
    if ($notifyAll && $notificationMethod === 'email') {
        respond(['status' => 'error', 'message' => 'Email delivery is not configured for dashboard tasks. Choose System notifications.'], 400);
    }
    if ($notifyAll && $targetRole === '') {
        respond(['status' => 'error', 'message' => 'Select a recipient role before choosing Notify All.'], 400);
    }

    $schoolId = $schoolId > 0 ? $schoolId : null;
    $programId = $programId > 0 ? $programId : null;
    $recipients = $notifyAll
        ? targetUsers($db, $targetRole, $schoolId, $programId)
        : [];

    $db->beginTransaction();
    $ioId = ensureOfficerId($db, $userId);
    $stmt = $db->prepare('
        INSERT INTO events (io_id, event_date, event_year, event_title, event_description)
        VALUES (?, ?, ?, ?, ?)
    ');
    $stmt->execute([$ioId, $date, (int)substr($date, 0, 4), $title, $description]);
    $eventId = (int)$db->lastInsertId();

    $stmt = $db->prepare('
        INSERT INTO io_dashboard_event_details
            (event_id, owner_user_id, event_type, start_time, end_time, target_role,
             target_school_id, target_program_id, notify_all, notification_method)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ');
    $stmt->execute([
        $eventId,
        $userId,
        $eventType,
        $startTime !== '' ? $startTime : null,
        $endTime !== '' ? $endTime : null,
        $targetRole !== '' ? $targetRole : null,
        $schoolId,
        $programId,
        $notifyAll ? 1 : 0,
        $notificationMethod,
    ]);

    if ($notifyAll) {
        $eventRecipient = $db->prepare('INSERT INTO eventrecipients (event_id, user_id) VALUES (?, ?)');
        foreach ($recipients as $recipientId) {
            $eventRecipient->execute([$eventId, $recipientId]);
        }

        $message = 'New ' . $eventType . ': ' . $title . ' on ' . $date . '.';
        createNotification($db, $userId, $eventType, $message, $recipients);
    }

    $db->commit();
    respond([
        'status' => 'success',
        'message' => $notifyAll
            ? 'Calendar item saved and ' . count($recipients) . ' system notification(s) created.'
            : 'Calendar item saved.',
        'data' => ['event_id' => $eventId, 'recipient_count' => count($recipients)],
    ]);
}

$action = $_SERVER['REQUEST_METHOD'] === 'GET'
    ? trim((string)($_GET['action'] ?? ''))
    : trim((string)($_POST['action'] ?? ''));

try {
    if ($_SERVER['REQUEST_METHOD'] === 'GET' && $action === 'notifications') {
        respond(['status' => 'success', 'data' => notificationData($conn, $userId)]);
    }

    if ($_SERVER['REQUEST_METHOD'] === 'GET' && $action === 'dashboard') {
        respond(['status' => 'success', 'data' => dashboardData($conn, $userId)]);
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        respond(['status' => 'error', 'message' => 'Method not allowed.'], 405);
    }

    if ($action === 'save_academic_year') {
        $start = trim((string)($_POST['start_date'] ?? ''));
        $end = trim((string)($_POST['end_date'] ?? ''));
        if (!validDate($start) || !validDate($end) || $start > $end) {
            respond(['status' => 'error', 'message' => 'Choose a valid academic year date range.'], 400);
        }

        $stmt = $conn->prepare('
            INSERT INTO io_dashboard_settings (user_id, academic_year_start, academic_year_end)
            VALUES (?, ?, ?)
            ON DUPLICATE KEY UPDATE
                academic_year_start = VALUES(academic_year_start),
                academic_year_end = VALUES(academic_year_end)
        ');
        $stmt->execute([$userId, $start, $end]);
        respond(['status' => 'success', 'message' => 'Academic year saved.']);
    }

    if ($action === 'create_event' || $action === 'create_notification') {
        if ($action === 'create_notification') {
            $_POST['event_type'] = 'task';
        }
        saveDashboardEvent($conn, $userId);
    }

    if ($action === 'mark_read') {
        $stmt = $conn->prepare('
            UPDATE notificationrecipients
            SET notifrecip_is_read = 1
            WHERE user_id = ? AND COALESCE(notifrecip_is_read, 0) = 0
        ');
        $stmt->execute([$userId]);
        respond(['status' => 'success', 'message' => 'Notifications marked as read.']);
    }

    if ($action === 'notify_pending') {
        $pendingIds = [];
        foreach (evaluationReport($conn) as $row) {
            if ($row['submitted_count'] < $row['assigned_count']) {
                $pendingIds[] = $row['user_id'];
            }
        }
        if (!$pendingIds) {
            respond(['status' => 'success', 'message' => 'There are no pending evaluation forms to notify.']);
        }

        $conn->beginTransaction();
        $count = count(array_unique($pendingIds));
        createNotification(
            $conn,
            $userId,
            'evaluation_reminder',
            'Please complete your outstanding internship evaluation form(s).',
            $pendingIds
        );
        $conn->commit();
        respond(['status' => 'success', 'message' => 'Reminder sent to ' . $count . ' industry partner(s).']);
    }

    respond(['status' => 'error', 'message' => 'Unknown dashboard action.'], 400);
} catch (PDOException $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    error_log('[IO dashboard] ' . $e->getMessage());
    respond(['status' => 'error', 'message' => 'The dashboard request could not be completed.'], 500);
}
