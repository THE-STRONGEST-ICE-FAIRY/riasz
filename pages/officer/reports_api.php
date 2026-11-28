<?php
session_start();
require '../../utilities/database/database.php';

header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', '0');
ini_set('log_errors', '1');
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

function reportsRespond($payload, $status = 200)
{
    http_response_code($status);
    echo json_encode($payload, JSON_INVALID_UTF8_SUBSTITUTE);
    exit;
}

$userId = (int)($_SESSION['user_id'] ?? 0);
$sessionRole = strtolower(trim((string)($_SESSION['user_role'] ?? '')));
if ($userId <= 0 || !in_array($sessionRole, ['officer', 'internship officer'], true)) {
    reportsRespond(['success' => false, 'message' => 'Please sign in as an internship officer.'], 403);
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
    reportsRespond(['success' => false, 'message' => 'This account is not an active internship officer.'], 403);
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    reportsRespond(['success' => false, 'message' => 'Only GET requests are supported.'], 405);
}

$action = $_GET['action'] ?? '';

try {
    if ($action === 'deployment') {
        $stmt = $conn->query("
            SELECT
                su.schooluser_given_id AS student_id,
                u.user_last_name AS last_name,
                u.user_first_name AS first_name,
                u.user_email AS email,
                p.program_name,
                c.company_name,
                CONCAT_WS(' ', supervisor_user.user_first_name, supervisor_user.user_last_name) AS supervisor_name,
                COALESCE(
                    NULLIF(TRIM(latest_status.status_text), ''),
                    NULLIF(TRIM(intern_details.directory_status), ''),
                    CASE WHEN ip.internship_id IS NOT NULL THEN 'Deployed' ELSE 'Unlisted' END
                ) AS deployment_status
            FROM users u
            JOIN schoolusers su ON su.user_id = u.user_id
            JOIN interns i ON i.schooluser_id = su.schooluser_id
            LEFT JOIN programs p ON p.program_id = i.program_id
            LEFT JOIN io_user_details intern_details ON intern_details.user_id = u.user_id
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
            LEFT JOIN supervisors supervisor ON supervisor.supervisor_id = ip.supervisor_id
            LEFT JOIN users supervisor_user ON supervisor_user.user_id = supervisor.user_id
            LEFT JOIN departments d ON d.department_id = supervisor.department_id
            LEFT JOIN companies c ON c.company_id = d.company_id
            WHERE u.user_role = 'intern'
              AND COALESCE(u.user_is_archived, 0) = 0
            ORDER BY u.user_last_name, u.user_first_name, su.schooluser_given_id
        ");

        $rows = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $rawStatus = trim((string)($row['deployment_status'] ?? ''));
            $normalizedStatus = strtolower($rawStatus);
            if ($normalizedStatus === 'active' || $normalizedStatus === 'deployed') {
                $rawStatus = 'Deployed';
            } elseif ($normalizedStatus === 'waiting') {
                $rawStatus = 'Waiting';
            } elseif ($normalizedStatus === 'undeployed') {
                $rawStatus = 'Undeployed';
            } elseif ($rawStatus === '') {
                $rawStatus = 'Unlisted';
            }

            $row['deployment_status'] = $rawStatus;
            $rows[] = $row;
        }

        reportsRespond(['success' => true, 'rows' => $rows]);
    }

    if ($action === 'evaluation') {
        $stmt = $conn->query("
            SELECT
                ip.internship_id,
                su.schooluser_given_id AS student_id,
                u.user_first_name AS first_name,
                u.user_last_name AS last_name,
                p.program_name,
                c.company_name,
                ig.supervisor_grade,
                ig.io_grade,
                ig.total_grade
            FROM internships ip
            JOIN interns i ON i.intern_id = ip.intern_id
            JOIN schoolusers su ON su.schooluser_id = i.schooluser_id
            JOIN users u ON u.user_id = su.user_id
            LEFT JOIN programs p ON p.program_id = i.program_id
            LEFT JOIN supervisors supervisor ON supervisor.supervisor_id = ip.supervisor_id
            LEFT JOIN departments d ON d.department_id = supervisor.department_id
            LEFT JOIN companies c ON c.company_id = d.company_id
            LEFT JOIN internshipgrades ig ON ig.internshipgrade_id = (
                SELECT MAX(ig2.internshipgrade_id)
                FROM internshipgrades ig2
                WHERE ig2.internship_id = ip.internship_id
            )
            WHERE u.user_role = 'intern'
              AND COALESCE(u.user_is_archived, 0) = 0
            ORDER BY ip.internship_year DESC, u.user_last_name, u.user_first_name
        ");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($rows as &$row) {
            $row['internship_id'] = (int)$row['internship_id'];
            foreach (['supervisor_grade', 'io_grade', 'total_grade'] as $gradeField) {
                $row[$gradeField] = $row[$gradeField] === null ? null : (float)$row[$gradeField];
            }
        }
        unset($row);

        $criteriaStmt = $conn->query("
            SELECT
                ip.internship_id,
                TRIM(ac.ass_content_question) AS criterion,
                ac.ass_content_order,
                ag.assessment_grade
            FROM assessmentgrades ag
            JOIN assessmentcontents ac ON ac.assessment_content_id = ag.assessment_content_id
            JOIN internships ip ON ip.internship_id = ag.internship_id
            JOIN interns i ON i.intern_id = ip.intern_id
            JOIN schoolusers su ON su.schooluser_id = i.schooluser_id
            JOIN users u ON u.user_id = su.user_id
            WHERE u.user_role = 'intern'
              AND COALESCE(u.user_is_archived, 0) = 0
              AND ag.assessment_grade IS NOT NULL
              AND TRIM(COALESCE(ac.ass_content_question, '')) <> ''
            ORDER BY ac.ass_content_order, criterion, ip.internship_id
        ");

        $criteriaSums = [];
        $scoresByInternship = [];
        foreach ($criteriaStmt->fetchAll(PDO::FETCH_ASSOC) as $score) {
            $criterion = $score['criterion'];
            $internshipId = (int)$score['internship_id'];
            $grade = (float)$score['assessment_grade'];

            if (!isset($criteriaSums[$criterion])) {
                $criteriaSums[$criterion] = ['sum' => 0.0, 'count' => 0];
            }
            $criteriaSums[$criterion]['sum'] += $grade;
            $criteriaSums[$criterion]['count']++;

            if (!isset($scoresByInternship[$internshipId][$criterion])) {
                $scoresByInternship[$internshipId][$criterion] = ['sum' => 0.0, 'count' => 0];
            }
            $scoresByInternship[$internshipId][$criterion]['sum'] += $grade;
            $scoresByInternship[$internshipId][$criterion]['count']++;
        }

        $criteria = [];
        foreach ($criteriaSums as $label => $aggregate) {
            $criteria[] = [
                'label' => $label,
                'average_score' => round($aggregate['sum'] / $aggregate['count'], 2),
                'response_count' => $aggregate['count'],
            ];
        }

        $criterionScores = [];
        foreach ($scoresByInternship as $internshipId => $criterionAggregates) {
            foreach ($criterionAggregates as $label => $aggregate) {
                $criterionScores[] = [
                    'internship_id' => (int)$internshipId,
                    'criterion' => $label,
                    'score' => round($aggregate['sum'] / $aggregate['count'], 2),
                ];
            }
        }

        reportsRespond([
            'success' => true,
            'rows' => $rows,
            'criteria' => $criteria,
            'criterion_scores' => $criterionScores,
        ]);
    }

    reportsRespond(['success' => false, 'message' => 'Unknown report type.'], 400);
} catch (PDOException $error) {
    error_log('reports_api database error: ' . $error->getMessage());
    reportsRespond(['success' => false, 'message' => 'The report could not be loaded from the database.'], 500);
}
