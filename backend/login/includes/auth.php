<?php
/**
 * Helpers for dashboard pages. Include this file (NOT handler.php) to check who is signed in:
 *
 *   require '../login/includes/bootstrap.php';
 *   require '../login/includes/db.php';
 *   require '../login/includes/auth.php';
 *   $user = getUserFromTab($conn, $tabId);   // null when not signed in
 */

function currentUser($tabId)
{
    return $_SESSION['accounts'][$tabId] ?? null;
}

function getUserFromTab(PDO $conn, $tabId)
{
    if (!is_string($tabId) || !isset($_SESSION['accounts'][$tabId]['user_id'])) {
        return null;
    }

    $stmt = $conn->prepare(
        'SELECT user_id, user_email, user_role
           FROM users
          WHERE user_id = ? AND user_is_archived = 0
          LIMIT 1'
    );
    $stmt->execute([$_SESSION['accounts'][$tabId]['user_id']]);
    $row = $stmt->fetch();

    return $row ?: null;
}

/** Backwards-compatible name used by the old handler. */
function roleswitch($role)
{
    return role_redirect((string) $role) ?? '/login.php';
}
