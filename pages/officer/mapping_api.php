<?php
session_start();
require '../../utilities/database/database.php';

header('Content-Type: application/json; charset=utf-8');
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

function mappingResponse($payload, $status = 200)
{
    http_response_code($status);
    echo json_encode($payload, JSON_INVALID_UTF8_SUBSTITUTE);
    exit;
}

function mappingError($message, $status)
{
    mappingResponse(['status' => 'error', 'message' => $message], $status);
}

$userId = (int)($_SESSION['user_id'] ?? 0);
$sessionRole = strtolower(trim((string)($_SESSION['user_role'] ?? '')));
if ($userId <= 0 || !in_array($sessionRole, ['officer', 'internship officer'], true)) {
    mappingError('Please sign in as an internship officer.', 403);
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
    mappingError('This account is not an active internship officer.', 403);
}

function ensureMappingOfficerId(PDO $db, $userId)
{
    $stmt = $db->prepare('SELECT io_id FROM io_user_details WHERE user_id = ? LIMIT 1');
    $stmt->execute([$userId]);
    $ioId = $stmt->fetchColumn();
    if ($ioId !== false && $ioId !== null) {
        return (int)$ioId;
    }

    $db->exec('INSERT INTO internshipofficers () VALUES ()');
    $ioId = (int)$db->lastInsertId();
    $stmt = $db->prepare("
        INSERT INTO io_user_details (user_id, io_id)
        VALUES (?, ?)
        ON DUPLICATE KEY UPDATE io_id = VALUES(io_id)
    ");
    $stmt->execute([$userId, $ioId]);
    return $ioId;
}

function mappingBody()
{
    $body = json_decode(file_get_contents('php://input'), true);
    if (!is_array($body)) {
        mappingError('A valid JSON request body is required.', 400);
    }
    return $body;
}

function mappingPositiveInt($value, $label)
{
    $parsed = filter_var($value, FILTER_VALIDATE_INT);
    if ($parsed === false || $parsed <= 0) {
        mappingError('A valid ' . $label . ' is required.', 400);
    }
    return (int)$parsed;
}

function mappingVersionRows(PDO $db, $ioId, $type)
{
    if ($type === 'outcomes') {
        $stmt = $db->prepare("
            SELECT co.co_id AS id, co.co_version_number AS version,
                   co.co_date_created AS created_at, co.co_is_archived AS is_archived,
                   co.program_id, p.program_name,
                   COUNT(coc.co_content_id) AS item_count
            FROM courseoutcomes co
            LEFT JOIN programs p ON p.program_id = co.program_id
            LEFT JOIN courseoutcomescontents coc ON coc.co_id = co.co_id
            WHERE co.io_id = ?
            GROUP BY co.co_id, co.co_version_number, co.co_date_created,
                     co.co_is_archived, co.program_id, p.program_name
            ORDER BY co.co_version_number DESC, co.co_id DESC
        ");
    } else {
        $stmt = $db->prepare("
            SELECT a.assessment_id AS id, a.ass_version_number AS version,
                   a.ass_date_created AS created_at, a.ass_is_archived AS is_archived,
                   COUNT(ac.assessment_content_id) AS item_count
            FROM assessments a
            LEFT JOIN assessmentcontents ac ON ac.assessment_id = a.assessment_id
            WHERE a.io_id = ?
            GROUP BY a.assessment_id, a.ass_version_number, a.ass_date_created, a.ass_is_archived
            ORDER BY a.ass_version_number DESC, a.assessment_id DESC
        ");
    }

    $stmt->execute([$ioId]);
    return array_map(function ($row) {
        return [
            'id' => (int)$row['id'],
            'version' => (int)$row['version'],
            'created_at' => $row['created_at'],
            'is_archived' => (bool)$row['is_archived'],
            'item_count' => (int)$row['item_count'],
            'program_id' => array_key_exists('program_id', $row) && $row['program_id'] !== null
                ? (int)$row['program_id']
                : null,
            'program_name' => $row['program_name'] ?? null,
        ];
    }, $stmt->fetchAll(PDO::FETCH_ASSOC));
}

function mappingInitialization(PDO $db, $ioId)
{
    $programStmt = $db->query("
        SELECT
            p.program_id,
            p.program_name,
            s.school_id,
            s.school_name,
            so.so_id,
            so.so_version_number,
            soc.so_content_id,
            soc.so_content_text
        FROM programs p
        JOIN schools s ON s.school_id = p.school_id
        LEFT JOIN studentoutcomes so ON so.so_id = (
            SELECT so2.so_id
            FROM studentoutcomes so2
            WHERE so2.program_id = p.program_id
              AND COALESCE(so2.so_is_archived, 0) = 0
            ORDER BY so2.so_version_number DESC, so2.so_id DESC
            LIMIT 1
        )
        LEFT JOIN studentoutcomescontents soc ON soc.so_id = so.so_id
        WHERE s.school_name <> 'Wizard'
        ORDER BY s.school_name, p.program_name, soc.so_content_id
    ");

    $schools = [];
    foreach ($programStmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $schoolId = (int)$row['school_id'];
        $programId = (int)$row['program_id'];
        if (!isset($schools[$schoolId])) {
            $schools[$schoolId] = [
                'id' => $schoolId,
                'name' => $row['school_name'],
                'programs' => [],
            ];
        }
        if (!isset($schools[$schoolId]['programs'][$programId])) {
            $schools[$schoolId]['programs'][$programId] = [
                'id' => $programId,
                'name' => $row['program_name'],
                'student_outcomes' => [],
            ];
        }
        if ($row['so_content_id'] !== null) {
            $schools[$schoolId]['programs'][$programId]['student_outcomes'][] = [
                'id' => (int)$row['so_content_id'],
                'text' => $row['so_content_text'],
                'version' => (int)$row['so_version_number'],
            ];
        }
    }

    foreach ($schools as &$school) {
        $school['programs'] = array_values($school['programs']);
    }
    unset($school);

    $outcomeVersions = mappingVersionRows($db, $ioId, 'outcomes');
    $assessmentVersions = mappingVersionRows($db, $ioId, 'assessment');
    $activeOutcomeIds = [];
    foreach ($outcomeVersions as $version) {
        if (!$version['is_archived'] && $version['program_id'] !== null) {
            $activeOutcomeIds[$version['program_id']] = $version['id'];
        }
    }
    $activeAssessmentId = null;
    foreach ($assessmentVersions as $version) {
        if (!$version['is_archived']) {
            $activeAssessmentId = $version['id'];
            break;
        }
    }

    return [
        'schools' => array_values($schools),
        'outcome_versions' => $outcomeVersions,
        'assessment_versions' => $assessmentVersions,
        'active_outcome_ids' => $activeOutcomeIds,
        'active_assessment_id' => $activeAssessmentId,
    ];
}

function mappingOutcomeFile(PDO $db, $ioId, $coId)
{
    $stmt = $db->prepare("
        SELECT co_id, co_version_number, co_date_created, co_is_archived, program_id
        FROM courseoutcomes
        WHERE co_id = ? AND io_id = ?
        LIMIT 1
    ");
    $stmt->execute([$coId, $ioId]);
    $file = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$file) {
        mappingError('The requested outcomes version was not found.', 404);
    }

    $stmt = $db->prepare("
        SELECT co_content_id, co_content_text
        FROM courseoutcomescontents
        WHERE co_id = ?
        ORDER BY co_content_id
    ");
    $stmt->execute([$coId]);
    $outcomes = [];
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $outcomes[(int)$row['co_content_id']] = [
            'id' => (int)$row['co_content_id'],
            'text' => $row['co_content_text'],
            'program_so_ids' => [],
        ];
    }

    $programSoDetails = [];
    if ($outcomes) {
        $ids = array_keys($outcomes);
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $stmt = $db->prepare("
            SELECT coso.co_content_id, so.program_id, so.so_version_number,
                   so.so_is_archived, soc.so_content_id, soc.so_content_text
            FROM courseoutcomesstudentoutcomes coso
            JOIN studentoutcomescontents soc ON soc.so_content_id = coso.so_content_id
            JOIN studentoutcomes so ON so.so_id = soc.so_id
            WHERE coso.co_content_id IN ($placeholders)
            ORDER BY so.program_id, so.so_version_number, soc.so_content_id
        ");
        $stmt->execute($ids);
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $contentId = (int)$row['co_content_id'];
            $programId = (int)$row['program_id'];
            $soContentId = (int)$row['so_content_id'];
            $outcomes[$contentId]['program_so_ids'][$programId][] = $soContentId;
            if (!isset($programSoDetails[$programId][$soContentId])) {
                $programSoDetails[$programId][$soContentId] = [
                    'id' => $soContentId,
                    'text' => $row['so_content_text'],
                    'version' => (int)$row['so_version_number'],
                    'is_archived' => (bool)$row['so_is_archived'],
                ];
            }
        }
    }

    return [
        'id' => (int)$file['co_id'],
        'version' => (int)$file['co_version_number'],
        'created_at' => $file['co_date_created'],
        'is_archived' => (bool)$file['co_is_archived'],
        'program_id' => $file['program_id'] === null ? null : (int)$file['program_id'],
        'outcomes' => array_values($outcomes),
        'program_so_details' => array_map('array_values', $programSoDetails),
    ];
}

function mappingAssessmentFile(PDO $db, $ioId, $assessmentId)
{
    $stmt = $db->prepare("
        SELECT assessment_id, ass_version_number, ass_date_created, ass_is_archived
        FROM assessments
        WHERE assessment_id = ? AND io_id = ?
        LIMIT 1
    ");
    $stmt->execute([$assessmentId, $ioId]);
    $file = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$file) {
        mappingError('The requested evaluation form was not found.', 404);
    }

    $stmt = $db->prepare("
        SELECT assessment_content_id, ass_content_question, ass_content_help_text,
               ass_content_response_type, ass_content_weight, ass_content_order
        FROM assessmentcontents
        WHERE assessment_id = ?
        ORDER BY ass_content_order, assessment_content_id
    ");
    $stmt->execute([$assessmentId]);
    $criteria = [];
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $criteria[] = [
            'id' => (int)$row['assessment_content_id'],
            'question' => $row['ass_content_question'],
            'help_text' => $row['ass_content_help_text'],
            'response_type' => $row['ass_content_response_type'] ?: 'yes_no',
            'weight' => $row['ass_content_weight'] === null ? null : (float)$row['ass_content_weight'],
            'order' => (int)$row['ass_content_order'],
        ];
    }

    return [
        'id' => (int)$file['assessment_id'],
        'version' => (int)$file['ass_version_number'],
        'created_at' => $file['ass_date_created'],
        'is_archived' => (bool)$file['ass_is_archived'],
        'criteria' => $criteria,
    ];
}

function createMappingFile(PDO $db, $ioId, $type, $programId = null)
{
    if (!in_array($type, ['outcomes', 'assessment'], true)) {
        mappingError('Choose an outcomes mapping or evaluation form file.', 400);
    }

    $previous = null;
    if ($type === 'outcomes') {
        $programId = mappingPositiveInt($programId, 'program');
        $programStmt = $db->prepare('SELECT COUNT(*) FROM programs WHERE program_id = ?');
        $programStmt->execute([$programId]);
        if ((int)$programStmt->fetchColumn() === 0) {
            mappingError('The selected program was not found.', 404);
        }

        $stmt = $db->prepare("
            SELECT COUNT(*)
            FROM courseoutcomes
            WHERE io_id = ? AND program_id = ? AND COALESCE(co_is_archived, 0) = 0
        ");
        $stmt->execute([$ioId, $programId]);
        if ((int)$stmt->fetchColumn() > 0) {
            mappingError('This program already has an ongoing outcomes file. Finish or submit it before creating another.', 409);
        }

        $stmt = $db->prepare("
            SELECT co_id, co_version_number
            FROM courseoutcomes
            WHERE io_id = ? AND program_id = ?
            ORDER BY co_version_number DESC, co_id DESC
            LIMIT 1
        ");
        $stmt->execute([$ioId, $programId]);
        $previous = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        $version = $previous ? (int)$previous['co_version_number'] + 1 : 1;
    } else {
        $stmt = $db->prepare("
            SELECT COUNT(*)
            FROM assessments
            WHERE io_id = ? AND COALESCE(ass_is_archived, 0) = 0
        ");
        $stmt->execute([$ioId]);
        if ((int)$stmt->fetchColumn() > 0) {
            mappingError('An ongoing file already exists. Finish or submit it before creating another.', 409);
        }

        $stmt = $db->prepare("
            SELECT assessment_id, ass_version_number
            FROM assessments
            WHERE io_id = ?
            ORDER BY ass_version_number DESC, assessment_id DESC
            LIMIT 1
        ");
        $stmt->execute([$ioId]);
        $previous = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        $version = $previous ? (int)$previous['ass_version_number'] + 1 : 1;
    }

    $db->beginTransaction();
    if ($type === 'outcomes') {
        $insert = $db->prepare("
            INSERT INTO courseoutcomes (io_id, program_id, co_version_number, co_version_comments, co_date_created, co_is_archived)
            VALUES (?, ?, ?, ?, NOW(), 0)
        ");
        $insert->execute([$ioId, $programId, $version, 'Draft']);
        $newId = (int)$db->lastInsertId();

        if ($previous) {
            $stmt = $db->prepare('SELECT co_content_id, co_content_text FROM courseoutcomescontents WHERE co_id = ? ORDER BY co_content_id');
            $stmt->execute([(int)$previous['co_id']]);
            $copyContent = $db->prepare('INSERT INTO courseoutcomescontents (co_id, co_content_text) VALUES (?, ?)');
            $copyMapping = $db->prepare('INSERT INTO courseoutcomesstudentoutcomes (so_content_id, co_content_id) VALUES (?, ?)');
            $oldMappings = $db->prepare("
                SELECT coso.so_content_id
                FROM courseoutcomesstudentoutcomes coso
                JOIN studentoutcomescontents soc ON soc.so_content_id = coso.so_content_id
                JOIN studentoutcomes so ON so.so_id = soc.so_id
                WHERE coso.co_content_id = ? AND so.program_id = ?
            ");
            foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $oldContent) {
                $copyContent->execute([$newId, $oldContent['co_content_text']]);
                $newContentId = (int)$db->lastInsertId();
                $oldMappings->execute([(int)$oldContent['co_content_id'], $programId]);
                foreach ($oldMappings->fetchAll(PDO::FETCH_COLUMN) as $soContentId) {
                    $copyMapping->execute([(int)$soContentId, $newContentId]);
                }
            }
        }
    } else {
        $insert = $db->prepare("
            INSERT INTO assessments (io_id, ass_version_number, ass_version_comments, ass_date_created, ass_is_archived)
            VALUES (?, ?, ?, NOW(), 0)
        ");
        $insert->execute([$ioId, $version, 'Draft']);
        $newId = (int)$db->lastInsertId();

        if ($previous) {
            $stmt = $db->prepare("
                SELECT ass_content_question, ass_content_help_text, ass_content_response_type,
                       ass_content_weight, ass_content_order
                FROM assessmentcontents
                WHERE assessment_id = ?
                ORDER BY ass_content_order, assessment_content_id
            ");
            $stmt->execute([(int)$previous['assessment_id']]);
            $copyContent = $db->prepare("
                INSERT INTO assessmentcontents
                    (assessment_id, ass_content_question, ass_content_help_text,
                     ass_content_response_type, ass_content_weight, ass_content_order)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $item) {
                $copyContent->execute([
                    $newId,
                    $item['ass_content_question'],
                    $item['ass_content_help_text'],
                    $item['ass_content_response_type'],
                    $item['ass_content_weight'],
                    $item['ass_content_order'],
                ]);
            }
        }
    }
    $db->commit();
    return ['id' => $newId, 'version' => $version];
}

function saveOutcomeFile(PDO $db, $ioId, array $body)
{
    $coId = mappingPositiveInt($body['id'] ?? null, 'outcomes file');
    $programId = mappingPositiveInt($body['program_id'] ?? null, 'program');
    $outcomes = $body['outcomes'] ?? null;
    if (!is_array($outcomes) || count($outcomes) > 150) {
        mappingError('Outcome rows must be provided (maximum 150).', 400);
    }

    $stmt = $db->prepare('SELECT co_is_archived, program_id FROM courseoutcomes WHERE co_id = ? AND io_id = ? LIMIT 1');
    $stmt->execute([$coId, $ioId]);
    $file = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$file) {
        mappingError('The outcomes version was not found.', 404);
    }
    if ($file['program_id'] === null || (int)$file['program_id'] !== $programId) {
        mappingError('This outcomes file belongs to a different program.', 409);
    }
    if ((int)$file['co_is_archived'] === 1) {
        mappingError('Submitted outcomes are read-only. Create a new file to make changes.', 409);
    }

    $stmt = $db->prepare('SELECT COUNT(*) FROM programs WHERE program_id = ?');
    $stmt->execute([$programId]);
    if ((int)$stmt->fetchColumn() === 0) {
        mappingError('The selected program was not found.', 404);
    }

    $stmt = $db->prepare("
        SELECT soc.so_content_id
        FROM studentoutcomes so
        JOIN studentoutcomescontents soc ON soc.so_id = so.so_id
        WHERE so.program_id = ?
          AND COALESCE(so.so_is_archived, 0) = 0
          AND so.so_id = (
              SELECT so2.so_id
              FROM studentoutcomes so2
              WHERE so2.program_id = ?
                AND COALESCE(so2.so_is_archived, 0) = 0
              ORDER BY so2.so_version_number DESC, so2.so_id DESC
              LIMIT 1
          )
    ");
    $stmt->execute([$programId, $programId]);
    $availableSoIds = array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN));
    $availableSet = array_fill_keys($availableSoIds, true);

    $normalized = [];
    $seenIds = [];
    foreach ($outcomes as $outcome) {
        if (!is_array($outcome)) {
            mappingError('Each outcome must be an object.', 400);
        }
        $id = $outcome['id'] ?? null;
        if ($id !== null) {
            $id = mappingPositiveInt($id, 'outcome row');
            if (isset($seenIds[$id])) {
                mappingError('Duplicate outcome rows are not allowed.', 400);
            }
            $seenIds[$id] = true;
        }
        $text = trim((string)($outcome['text'] ?? ''));
        if ($text === '' || strlen($text) > 10000) {
            mappingError('Each course outcome must have text (maximum 10,000 characters).', 400);
        }
        $soIds = $outcome['so_ids'] ?? [];
        if (!is_array($soIds)) {
            mappingError('Student outcome selections must be a list.', 400);
        }
        $selectedSoIds = [];
        foreach ($soIds as $soId) {
            $soId = mappingPositiveInt($soId, 'student outcome');
            if (!isset($availableSet[$soId])) {
                mappingError('A selected student outcome is not available for this program.', 400);
            }
            $selectedSoIds[$soId] = $soId;
        }
        $normalized[] = ['id' => $id, 'text' => $text, 'so_ids' => array_values($selectedSoIds)];
    }

    $existingStmt = $db->prepare('SELECT co_content_id FROM courseoutcomescontents WHERE co_id = ?');
    $existingStmt->execute([$coId]);
    $existingIds = array_map('intval', $existingStmt->fetchAll(PDO::FETCH_COLUMN));
    $keptIds = array_filter(array_column($normalized, 'id'));
    if (array_diff($keptIds, $existingIds)) {
        mappingError('An outcome row does not belong to this version.', 400);
    }

    $db->beginTransaction();
    $insert = $db->prepare('INSERT INTO courseoutcomescontents (co_id, co_content_text) VALUES (?, ?)');
    $update = $db->prepare('UPDATE courseoutcomescontents SET co_content_text = ? WHERE co_content_id = ? AND co_id = ?');
    $deleteMappings = $db->prepare("
        DELETE m FROM mapping m
        JOIN courseoutcomesstudentoutcomes coso ON coso.coso_id = m.coso_id
        WHERE coso.co_content_id = ?
    ");
    $deleteAssociations = $db->prepare('DELETE FROM courseoutcomesstudentoutcomes WHERE co_content_id = ?');
    $deleteAssessmentMappings = $db->prepare("
        DELETE m FROM mapping m
        JOIN courseoutcomesassessments coa ON coa.coassessment_id = m.coassessment_id
        WHERE coa.co_content_id = ?
    ");
    $deleteAssessmentLinks = $db->prepare('DELETE FROM courseoutcomesassessments WHERE co_content_id = ?');
    $deleteContent = $db->prepare('DELETE FROM courseoutcomescontents WHERE co_content_id = ? AND co_id = ?');
    $removeProgramMappings = $db->prepare("
        DELETE coso
        FROM courseoutcomesstudentoutcomes coso
        JOIN studentoutcomescontents soc ON soc.so_content_id = coso.so_content_id
        JOIN studentoutcomes so ON so.so_id = soc.so_id
        WHERE coso.co_content_id = ? AND so.program_id = ?
    ");
    $addMapping = $db->prepare('INSERT INTO courseoutcomesstudentoutcomes (so_content_id, co_content_id) VALUES (?, ?)');

    $savedIds = [];
    foreach ($normalized as $outcome) {
        if ($outcome['id'] === null) {
            $insert->execute([$coId, $outcome['text']]);
            $contentId = (int)$db->lastInsertId();
        } else {
            $contentId = $outcome['id'];
            $update->execute([$outcome['text'], $contentId, $coId]);
        }
        $savedIds[] = $contentId;

        $removeProgramMappings->execute([$contentId, $programId]);
        foreach ($outcome['so_ids'] as $soId) {
            $addMapping->execute([$soId, $contentId]);
        }
    }

    $removedIds = array_diff($existingIds, $savedIds);
    foreach ($removedIds as $removedId) {
        $deleteMappings->execute([$removedId]);
        $deleteAssessmentMappings->execute([$removedId]);
        $deleteAssociations->execute([$removedId]);
        $deleteAssessmentLinks->execute([$removedId]);
        $deleteContent->execute([$removedId, $coId]);
    }

    $db->commit();
    return ['saved_count' => count($savedIds)];
}

function saveAssessmentFile(PDO $db, $ioId, array $body)
{
    $assessmentId = mappingPositiveInt($body['id'] ?? null, 'evaluation form');
    $criteria = $body['criteria'] ?? null;
    if (!is_array($criteria) || count($criteria) > 150) {
        mappingError('Evaluation criteria must be provided (maximum 150).', 400);
    }

    $stmt = $db->prepare('SELECT ass_is_archived FROM assessments WHERE assessment_id = ? AND io_id = ? LIMIT 1');
    $stmt->execute([$assessmentId, $ioId]);
    $archived = $stmt->fetchColumn();
    if ($archived === false) {
        mappingError('The evaluation form was not found.', 404);
    }
    if ((int)$archived === 1) {
        mappingError('Submitted evaluation forms are read-only. Create a new file to make changes.', 409);
    }

    $normalized = [];
    $seenIds = [];
    $allowedResponses = ['yes_no', 'rating', 'text'];
    foreach ($criteria as $index => $item) {
        if (!is_array($item)) {
            mappingError('Each evaluation criterion must be an object.', 400);
        }
        $id = $item['id'] ?? null;
        if ($id !== null) {
            $id = mappingPositiveInt($id, 'criterion row');
            if (isset($seenIds[$id])) {
                mappingError('Duplicate evaluation criteria are not allowed.', 400);
            }
            $seenIds[$id] = true;
        }
        $question = trim((string)($item['question'] ?? ''));
        if ($question === '' || strlen($question) > 10000) {
            mappingError('Each evaluation criterion must have a question (maximum 10,000 characters).', 400);
        }
        $helpText = trim((string)($item['help_text'] ?? ''));
        if (strlen($helpText) > 10000) {
            mappingError('Criterion help text is too long.', 400);
        }
        $responseType = (string)($item['response_type'] ?? 'yes_no');
        if (!in_array($responseType, $allowedResponses, true)) {
            mappingError('Choose a supported evaluation response type.', 400);
        }
        $weight = $item['weight'] ?? null;
        if ($weight === '' || $weight === null) {
            $weight = null;
        } elseif (!is_numeric($weight) || (float)$weight < 0 || (float)$weight > 100) {
            mappingError('Weights must be between 0 and 100.', 400);
        } else {
            $weight = (float)$weight;
        }
        $normalized[] = [
            'id' => $id,
            'question' => $question,
            'help_text' => $helpText,
            'response_type' => $responseType,
            'weight' => $weight,
            'order' => (int)$index,
        ];
    }

    $existingStmt = $db->prepare('SELECT assessment_content_id FROM assessmentcontents WHERE assessment_id = ?');
    $existingStmt->execute([$assessmentId]);
    $existingIds = array_map('intval', $existingStmt->fetchAll(PDO::FETCH_COLUMN));
    $keptIds = array_filter(array_column($normalized, 'id'));
    if (array_diff($keptIds, $existingIds)) {
        mappingError('A criterion does not belong to this evaluation form.', 400);
    }

    $removedIds = array_values(array_diff($existingIds, $keptIds));
    foreach ($removedIds as $removedId) {
        $usage = $db->prepare("
            SELECT
                EXISTS(SELECT 1 FROM assessmentgrades WHERE assessment_content_id = ?) AS has_grades,
                EXISTS(SELECT 1 FROM courseoutcomesassessments WHERE assessment_content_id = ?) AS has_mappings
        ");
        $usage->execute([$removedId, $removedId]);
        $usageRow = $usage->fetch(PDO::FETCH_ASSOC);
        if ((int)$usageRow['has_grades'] === 1 || (int)$usageRow['has_mappings'] === 1) {
            mappingError('A criterion used by an evaluation or outcome mapping cannot be removed. Create a new version instead.', 409);
        }
    }

    $db->beginTransaction();
    $insert = $db->prepare("
        INSERT INTO assessmentcontents
            (assessment_id, ass_content_question, ass_content_help_text,
             ass_content_response_type, ass_content_weight, ass_content_order)
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    $update = $db->prepare("
        UPDATE assessmentcontents
        SET ass_content_question = ?, ass_content_help_text = ?,
            ass_content_response_type = ?, ass_content_weight = ?, ass_content_order = ?
        WHERE assessment_content_id = ? AND assessment_id = ?
    ");
    $delete = $db->prepare('DELETE FROM assessmentcontents WHERE assessment_content_id = ? AND assessment_id = ?');
    foreach ($normalized as $item) {
        if ($item['id'] === null) {
            $insert->execute([
                $assessmentId, $item['question'], $item['help_text'],
                $item['response_type'], $item['weight'], $item['order'],
            ]);
        } else {
            $update->execute([
                $item['question'], $item['help_text'], $item['response_type'],
                $item['weight'], $item['order'], $item['id'], $assessmentId,
            ]);
        }
    }
    foreach ($removedIds as $removedId) {
        $delete->execute([$removedId, $assessmentId]);
    }

    $db->commit();
    return ['saved_count' => count($normalized)];
}

try {
    $ioId = ensureMappingOfficerId($conn, $userId);
    $method = strtoupper((string)($_SERVER['REQUEST_METHOD'] ?? 'GET'));
    if ($method === 'GET') {
        $action = (string)($_GET['action'] ?? 'initialize');
        if ($action === 'initialize') {
            mappingResponse(['status' => 'success', 'data' => mappingInitialization($conn, $ioId)]);
        }
        if ($action === 'outcome_file') {
            $fileId = mappingPositiveInt($_GET['id'] ?? null, 'outcomes file');
            mappingResponse(['status' => 'success', 'data' => mappingOutcomeFile($conn, $ioId, $fileId)]);
        }
        if ($action === 'assessment_file') {
            $fileId = mappingPositiveInt($_GET['id'] ?? null, 'evaluation form');
            mappingResponse(['status' => 'success', 'data' => mappingAssessmentFile($conn, $ioId, $fileId)]);
        }
        mappingError('Unknown mapping request.', 400);
    }

    if ($method !== 'POST') {
        mappingError('This mapping request method is not supported.', 405);
    }

    $body = mappingBody();
    $action = (string)($body['action'] ?? '');
    if ($action === 'create_file') {
        mappingResponse([
            'status' => 'success',
            'data' => createMappingFile(
                $conn,
                $ioId,
                (string)($body['type'] ?? ''),
                $body['program_id'] ?? null
            ),
        ], 201);
    }
    if ($action === 'save_outcomes') {
        mappingResponse(['status' => 'success', 'data' => saveOutcomeFile($conn, $ioId, $body)]);
    }
    if ($action === 'save_assessment') {
        mappingResponse(['status' => 'success', 'data' => saveAssessmentFile($conn, $ioId, $body)]);
    }
    if ($action === 'submit_outcomes' || $action === 'submit_assessment') {
        $id = mappingPositiveInt($body['id'] ?? null, 'file');
        if ($action === 'submit_outcomes') {
            $programId = mappingPositiveInt($body['program_id'] ?? null, 'program');
            $stmt = $conn->prepare('UPDATE courseoutcomes SET co_is_archived = 1 WHERE co_id = ? AND io_id = ? AND program_id = ? AND COALESCE(co_is_archived, 0) = 0');
            $stmt->execute([$id, $ioId, $programId]);
        } else {
            $stmt = $conn->prepare('UPDATE assessments SET ass_is_archived = 1 WHERE assessment_id = ? AND io_id = ? AND COALESCE(ass_is_archived, 0) = 0');
            $stmt->execute([$id, $ioId]);
        }
        if ($stmt->rowCount() !== 1) {
            mappingError('The active file was not found or has already been submitted.', 409);
        }
        mappingResponse(['status' => 'success', 'message' => 'File submitted.']);
    }

    mappingError('Unknown mapping action.', 400);
} catch (PDOException $error) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    error_log('IO mapping database error: ' . $error->getMessage());
    mappingError('The mapping request could not be completed. Please try again.', 500);
}
