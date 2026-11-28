<?php
session_start();
require '../../utilities/database/database.php';
require_once __DIR__ . '/internship_requirement_helpers.php';

header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', '0');
ini_set('log_errors', '1');
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

function requirementsRespond($payload, $status = 200)
{
    http_response_code($status);
    echo json_encode($payload, JSON_INVALID_UTF8_SUBSTITUTE);
    exit;
}

function requirementsNormalizeStatus($status, $hasInternship, $endDate, DateTimeImmutable $today)
{
    $status = trim((string)$status);
    if ($status === '') {
        if (!$hasInternship) {
            return 'Not started';
        }

        $endedAt = internshipRequirementParseDate($endDate);
        return $endedAt !== null && $endedAt <= $today ? 'Completed' : 'Ongoing';
    }

    $normalized = strtolower($status);
    if (in_array($normalized, ['active', 'deployed', 'in progress', 'ongoing'], true)) {
        return 'Ongoing';
    }
    if (in_array($normalized, ['complete', 'completed'], true)) {
        return 'Completed';
    }
    if ($normalized === 'failed') {
        return 'Failed';
    }
    if ($normalized === 'withdrawn') {
        return 'Withdrawn';
    }
    return $status;
}

$userId = (int)($_SESSION['user_id'] ?? 0);
$sessionRole = strtolower(trim((string)($_SESSION['user_role'] ?? '')));
if ($userId <= 0 || !in_array($sessionRole, ['officer', 'internship officer'], true)) {
    requirementsRespond(['success' => false, 'message' => 'Please sign in as an internship officer.'], 403);
}

$roleStmt = $conn->prepare("
    SELECT user_role
    FROM users
    WHERE user_id = ? AND COALESCE(user_is_archived, 0) = 0
    LIMIT 1
");
$roleStmt->execute([$userId]);
$databaseRole = strtolower(trim((string)$roleStmt->fetchColumn()));
if (!in_array($databaseRole, ['officer', 'internship officer'], true)) {
    requirementsRespond(['success' => false, 'message' => 'This account is not an active internship officer.'], 403);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $request = json_decode(file_get_contents('php://input'), true);
    if (!is_array($request)) {
        requirementsRespond(['success' => false, 'message' => 'A valid student is required.'], 400);
    }
    $recipientId = filter_var($request['user_id'] ?? null, FILTER_VALIDATE_INT);
    if ($recipientId === false || $recipientId <= 0) {
        requirementsRespond(['success' => false, 'message' => 'A valid student is required.'], 400);
    }

    try {
        $recipientStmt = $conn->prepare("
            SELECT u.user_id
            FROM users u
            JOIN schoolusers su ON su.user_id = u.user_id
            JOIN interns i ON i.schooluser_id = su.schooluser_id
            WHERE u.user_id = ?
              AND u.user_role = 'intern'
              AND COALESCE(u.user_is_archived, 0) = 0
            LIMIT 1
        ");
        $recipientStmt->execute([$recipientId]);
        if (!$recipientStmt->fetchColumn()) {
            requirementsRespond(['success' => false, 'message' => 'The active student could not be found.'], 404);
        }

        $conn->beginTransaction();
        $notificationStmt = $conn->prepare("
            INSERT INTO notifications (user_id, notif_type, notif_message)
            VALUES (?, ?, ?)
        ");
        $notificationStmt->execute([
            $userId,
            'Requirement Reminder',
            'Please review your Internship Requirements Checklist and submit any outstanding monthly attendance, monthly accomplishment, or final report/narrative items that are due.',
        ]);
        $notificationId = (int)$conn->lastInsertId();

        $recipientInsert = $conn->prepare("
            INSERT INTO notificationrecipients (notification_id, user_id)
            VALUES (?, ?)
        ");
        $recipientInsert->execute([$notificationId, $recipientId]);
        $conn->commit();

        requirementsRespond(['success' => true, 'message' => 'Reminder sent.']);
    } catch (PDOException $error) {
        if ($conn->inTransaction()) {
            $conn->rollBack();
        }
        error_log('requirements_api notification error: ' . $error->getMessage());
        requirementsRespond(['success' => false, 'message' => 'The reminder could not be sent.'], 500);
    }
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    requirementsRespond(['success' => false, 'message' => 'Only GET and POST requests are supported.'], 405);
}

try {
    $studentsStmt = $conn->query("
        SELECT
            u.user_id,
            u.user_first_name AS first_name,
            u.user_last_name AS last_name,
            su.schooluser_given_id AS student_id,
            p.program_name,
            ip.internship_id,
            ip.internship_year,
            ip.internship_date_started,
            ip.internship_date_ended,
            latest_status.status_text
        FROM users u
        JOIN schoolusers su ON su.user_id = u.user_id
        JOIN interns i ON i.schooluser_id = su.schooluser_id
        LEFT JOIN programs p ON p.program_id = i.program_id
        LEFT JOIN internships ip ON ip.internship_id = (
            SELECT ip2.internship_id
            FROM internships ip2
            WHERE ip2.intern_id = i.intern_id
            ORDER BY COALESCE(ip2.internship_year, 0) DESC,
                     ip2.internship_date_started DESC,
                     ip2.internship_id DESC
            LIMIT 1
        )
        LEFT JOIN statuses latest_status ON latest_status.status_id = (
            SELECT st.status_id
            FROM statuses st
            WHERE st.internship_id = ip.internship_id
            ORDER BY st.status_date_created DESC, st.status_id DESC
            LIMIT 1
        )
        WHERE u.user_role = 'intern'
          AND COALESCE(u.user_is_archived, 0) = 0
        ORDER BY u.user_last_name, u.user_first_name, su.schooluser_given_id
    ");
    $rows = $studentsStmt->fetchAll(PDO::FETCH_ASSOC);
    $internshipIds = [];
    foreach ($rows as $row) {
        if ($row['internship_id'] !== null) {
            $internshipIds[(int)$row['internship_id']] = true;
        }
    }

    $attendanceMonths = [];
    $attendanceStmt = $conn->query("
        SELECT ar.internship_id, ar.attrep_month
        FROM attendancereports ar
        JOIN internships ip ON ip.internship_id = ar.internship_id
        JOIN interns i ON i.intern_id = ip.intern_id
        JOIN schoolusers su ON su.schooluser_id = i.schooluser_id
        JOIN users u ON u.user_id = su.user_id
        WHERE u.user_role = 'intern'
          AND COALESCE(u.user_is_archived, 0) = 0
    ");
    foreach ($attendanceStmt->fetchAll(PDO::FETCH_ASSOC) as $report) {
        $internshipId = (int)$report['internship_id'];
        if (isset($internshipIds[$internshipId])) {
            $attendanceMonths[$internshipId][] = $report['attrep_month'];
        }
    }

    $accomplishmentMonths = [];
    $accomplishmentStmt = $conn->query("
        SELECT acr.internship_id, acr.accrep_month
        FROM accomplishmentreports acr
        JOIN internships ip ON ip.internship_id = acr.internship_id
        JOIN interns i ON i.intern_id = ip.intern_id
        JOIN schoolusers su ON su.schooluser_id = i.schooluser_id
        JOIN users u ON u.user_id = su.user_id
        WHERE u.user_role = 'intern'
          AND COALESCE(u.user_is_archived, 0) = 0
    ");
    foreach ($accomplishmentStmt->fetchAll(PDO::FETCH_ASSOC) as $report) {
        $internshipId = (int)$report['internship_id'];
        if (isset($internshipIds[$internshipId])) {
            $accomplishmentMonths[$internshipId][] = $report['accrep_month'];
        }
    }

    $finalSubmissions = [];
    $finalStmt = $conn->query("
        SELECT
            fr.internship_id,
            MAX(CASE WHEN
                NULLIF(TRIM(fr.finalreport_file_path), '') IS NOT NULL
                OR NULLIF(TRIM(fr.finalreport_filename), '') IS NOT NULL
                OR fr.finalreport_date_uploaded IS NOT NULL
                THEN 1 ELSE 0 END
            ) AS report_submitted,
            MAX(CASE WHEN NULLIF(TRIM(fn.finalnarrative_answer), '') IS NOT NULL
                THEN 1 ELSE 0 END
            ) AS narrative_submitted
        FROM finalreports fr
        LEFT JOIN finalnarratives fn ON fn.finalreport_id = fr.finalreport_id
        JOIN internships ip ON ip.internship_id = fr.internship_id
        JOIN interns i ON i.intern_id = ip.intern_id
        JOIN schoolusers su ON su.schooluser_id = i.schooluser_id
        JOIN users u ON u.user_id = su.user_id
        WHERE u.user_role = 'intern'
          AND COALESCE(u.user_is_archived, 0) = 0
        GROUP BY fr.internship_id
    ");
    foreach ($finalStmt->fetchAll(PDO::FETCH_ASSOC) as $submission) {
        $internshipId = (int)$submission['internship_id'];
        if (isset($internshipIds[$internshipId])) {
            $finalSubmissions[$internshipId] = [
                'report_submitted' => (bool)$submission['report_submitted'],
                'narrative_submitted' => (bool)$submission['narrative_submitted'],
            ];
        }
    }

    $today = new DateTimeImmutable('today');
    $students = [];
    $summary = [
        'student_count' => count($rows),
        'attendance_submitted' => 0,
        'attendance_due' => 0,
        'accomplishment_submitted' => 0,
        'accomplishment_due' => 0,
        'final_submitted' => 0,
        'final_due' => 0,
        'status_counts' => [
            'ongoing' => 0,
            'completed' => 0,
            'failed' => 0,
            'withdrawn' => 0,
            'not_started' => 0,
            'other' => 0,
        ],
    ];

    foreach ($rows as $row) {
        $internshipId = $row['internship_id'] === null ? 0 : (int)$row['internship_id'];
        $final = $finalSubmissions[$internshipId] ?? [
            'report_submitted' => false,
            'narrative_submitted' => false,
        ];
        $finalComplete = $final['report_submitted'] && $final['narrative_submitted'];
        $progress = internshipRequirementProgress(
            $row['internship_date_started'],
            $row['internship_date_ended'],
            $attendanceMonths[$internshipId] ?? [],
            $accomplishmentMonths[$internshipId] ?? [],
            $finalComplete,
            $today
        );
        $status = requirementsNormalizeStatus(
            $row['status_text'],
            $internshipId > 0,
            $row['internship_date_ended'],
            $today
        );
        $statusKey = strtolower($status);
        if ($statusKey === 'ongoing') {
            $summary['status_counts']['ongoing']++;
        } elseif ($statusKey === 'completed') {
            $summary['status_counts']['completed']++;
        } elseif ($statusKey === 'failed') {
            $summary['status_counts']['failed']++;
        } elseif ($statusKey === 'withdrawn') {
            $summary['status_counts']['withdrawn']++;
        } elseif ($statusKey === 'not started') {
            $summary['status_counts']['not_started']++;
        } else {
            $summary['status_counts']['other']++;
        }

        $summary['attendance_submitted'] += $progress['attendance_submitted'];
        $summary['attendance_due'] += $progress['attendance_due'];
        $summary['accomplishment_submitted'] += $progress['accomplishment_submitted'];
        $summary['accomplishment_due'] += $progress['accomplishment_due'];
        $summary['final_submitted'] += $progress['final_submitted'];
        $summary['final_due'] += $progress['final_due'];

        $students[] = [
            'user_id' => (int)$row['user_id'],
            'student_id' => (string)($row['student_id'] ?? ''),
            'first_name' => (string)($row['first_name'] ?? ''),
            'last_name' => (string)($row['last_name'] ?? ''),
            'program_name' => (string)($row['program_name'] ?? ''),
            'internship_year' => $row['internship_year'] === null ? null : (int)$row['internship_year'],
            'internship_id' => $internshipId > 0 ? $internshipId : null,
            'internship_status' => $status,
            'attendance_submitted' => $progress['attendance_submitted'],
            'attendance_due' => $progress['attendance_due'],
            'accomplishment_submitted' => $progress['accomplishment_submitted'],
            'accomplishment_due' => $progress['accomplishment_due'],
            'final_report_submitted' => $final['report_submitted'],
            'final_narrative_submitted' => $final['narrative_submitted'],
            'final_complete' => $finalComplete,
            'final_due' => $progress['final_due'] > 0,
            'requirements_submitted' => $progress['requirements_submitted'],
            'requirements_due' => $progress['requirements_due'],
        ];
    }

    requirementsRespond([
        'success' => true,
        'summary' => $summary,
        'students' => $students,
    ]);
} catch (PDOException $error) {
    error_log('requirements_api database error: ' . $error->getMessage());
    requirementsRespond(['success' => false, 'message' => 'Student requirements could not be loaded.'], 500);
}
