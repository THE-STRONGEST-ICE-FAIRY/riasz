<?php
// Set to false in production so errors aren't shown to users.
$debug = true;
ini_set('display_errors', $debug ? 1 : 0);
error_reporting(E_ALL);

require '../../utilities/database/database.php';

$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

header('Content-Type: text/plain; charset=utf-8');

function fail($message, $code = 400) {
    http_response_code($code);
    echo $message;
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    fail('Method not allowed.', 405);
}

// Reads ids[] from the request as a clean list of positive integers.
function idsFromPost() {
    $raw = $_POST['ids'] ?? [];
    if (!is_array($raw)) $raw = [$raw];
    $ids = array_values(array_unique(array_filter(array_map('intval', $raw), function ($i) { return $i > 0; })));
    if (!$ids) fail('No users selected.');
    if (count($ids) > 500) fail('Too many users selected at once.');
    return $ids;
}

$action = $_POST['action'] ?? '';

try {
    switch ($action) {

        case 'archive_users':
        case 'restore_users':
            $ids = idsFromPost();
            $flag = $action === 'archive_users' ? 1 : 0;
            $ph = implode(',', array_fill(0, count($ids), '?'));
            $stmt = $conn->prepare("UPDATE users SET user_is_archived = ? WHERE user_id IN ($ph)");
            $stmt->execute(array_merge([$flag], $ids));
            echo count($ids) . ($flag ? ' user(s) archived.' : ' user(s) restored.');
            break;

        case 'delete_users':
            $ids = idsFromPost();
            $conn->beginTransaction();
            try {
                foreach ($ids as $uid) {
                    $s = $conn->prepare("SELECT schooluser_id FROM schoolusers WHERE user_id = ?");
                    $s->execute([$uid]);
                    foreach ($s->fetchAll(PDO::FETCH_COLUMN) as $suid) {
                        foreach (['passwordresets', 'executivedirectors', 'programdirectors'] as $table) {
                            $d = $conn->prepare("DELETE FROM $table WHERE schooluser_id = ?");
                            $d->execute([$suid]);
                        }
                    }
                    $conn->prepare("DELETE FROM schoolusers WHERE user_id = ?")->execute([$uid]);
                    $conn->prepare("DELETE FROM users WHERE user_id = ?")->execute([$uid]);
                }
                $conn->commit();
            } catch (PDOException $e) {
                $conn->rollBack();
                throw $e;
            }
            echo count($ids) . ' user(s) deleted.';
            break;

        case 'edit_user':
            $id    = (int)($_POST['user_id'] ?? 0);
            $first = trim($_POST['first_name'] ?? '');
            $last  = trim($_POST['last_name'] ?? '');
            $email = trim($_POST['email'] ?? '');

            if ($id <= 0 || $first === '' || $last === '') fail('Please fill in all fields.');
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) fail('Please enter a valid email address.');

            $stmt = $conn->prepare("UPDATE users SET user_first_name = ?, user_last_name = ?, user_email = ? WHERE user_id = ?");
            $stmt->execute([$first, $last, $email, $id]);
            echo 'User updated.';
            break;

        default:
            fail('Unknown action.');
    }
} catch (PDOException $e) {
    if ($e->getCode() === '23000') {
        fail('That email is already used, or other records still depend on this user.', 409);
    }
    error_log($e->getMessage());
    fail($debug ? 'Database error: ' . $e->getMessage() : 'A database error occurred.', 500);
}
