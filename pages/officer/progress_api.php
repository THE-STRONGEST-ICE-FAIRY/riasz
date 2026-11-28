<?php
session_start();
require '../../utilities/database/database.php';
require_once __DIR__ . '/internship_requirement_helpers.php';

header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', '0');
ini_set('log_errors', '1');
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

function progressRespond($payload, $status = 200)
{
    http_response_code($status);
    echo json_encode($payload, JSON_INVALID_UTF8_SUBSTITUTE);
    exit;
}

$userId = (int)($_SESSION['user_id'] ?? 0);
$sessionRole = strtolower(trim((string)($_SESSION['user_role'] ?? '')));
if ($userId <= 0 || !in_array($sessionRole, ['officer', 'internship officer'], true)) {
    progressRespond(['success' => false, 'message' => 'Please sign in as an internship officer.'], 403);
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
    progressRespond(['success' => false, 'message' => 'This account is not an active internship officer.'], 403);
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    progressRespond(['success' => false, 'message' => 'Only GET requests are supported.'], 405);
}

try {
    $stmt = $conn->query("
        SELECT
            ip.internship_id,
            ip.internship_date_started,
            ip.internship_date_ended,
            EXISTS (
                SELECT 1
                FROM finalreports fr
                WHERE fr.internship_id = ip.internship_id
                  AND (
                      NULLIF(TRIM(fr.finalreport_file_path), '') IS NOT NULL
                      OR NULLIF(TRIM(fr.finalreport_filename), '') IS NOT NULL
                      OR fr.finalreport_date_uploaded IS NOT NULL
                  )
                  AND EXISTS (
                      SELECT 1
                      FROM finalnarratives fn
                      WHERE fn.finalreport_id = fr.finalreport_id
                        AND NULLIF(TRIM(fn.finalnarrative_answer), '') IS NOT NULL
                  )
            ) AS final_report_submitted
        FROM internships ip
        JOIN interns i ON i.intern_id = ip.intern_id
        JOIN schoolusers su ON su.schooluser_id = i.schooluser_id
        JOIN users u ON u.user_id = su.user_id
        WHERE u.user_role = 'intern'
          AND COALESCE(u.user_is_archived, 0) = 0
    ");
    $internships = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $internshipIds = array_fill_keys(array_map(
        function ($internship) {
            return (int)$internship['internship_id'];
        },
        $internships
    ), true);

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

    $requirementsDue = 0;
    $requirementsSubmitted = 0;
    foreach ($internships as $internship) {
        $internshipId = (int)$internship['internship_id'];
        $progress = internshipRequirementProgress(
            $internship['internship_date_started'],
            $internship['internship_date_ended'],
            $attendanceMonths[$internshipId] ?? [],
            $accomplishmentMonths[$internshipId] ?? [],
            $internship['final_report_submitted'],
            new DateTimeImmutable('today')
        );
        $requirementsDue += $progress['requirements_due'];
        $requirementsSubmitted += $progress['requirements_submitted'];
    }

    $completionPercent = $requirementsDue > 0
        ? (int)round(($requirementsSubmitted / $requirementsDue) * 100)
        : null;

    progressRespond([
        'success' => true,
        'requirements_submitted' => $requirementsSubmitted,
        'requirements_due' => $requirementsDue,
        'completion_percent' => $completionPercent,
    ]);
} catch (PDOException $error) {
    error_log('progress_api database error: ' . $error->getMessage());
    progressRespond(['success' => false, 'message' => 'Internship progress could not be loaded.'], 500);
}
