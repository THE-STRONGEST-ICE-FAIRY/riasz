<?php
/**
 * Single JSON endpoint for the login page.
 *
 * POST  action = login | sendotp | useotp | resetp | whoami | logout
 *       tabId  = per-browser-tab id (generated client-side)
 * Header X-CSRF-Token is required for every action except logout.
 */
require __DIR__ . '/includes/bootstrap.php';
require __DIR__ . '/includes/db.php';
require __DIR__ . '/includes/auth.php';
require __DIR__ . '/includes/actions.php';

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    header('Allow: POST');
    json_out(['status' => 'error', 'message' => 'Method not allowed.'], 405);
}

try {
    $in     = read_input();
    $action = is_string($in['action'] ?? null) ? $in['action'] : '';
    $tabId  = $in['tabId'] ?? '';

    if ($action === 'logout') {
        // Logging out is harmless to forge, and dashboards call it without our page's token.
        action_logout(valid_tab_id($tabId) ? $tabId : '');
    }

    if (!csrf_valid($_SERVER['HTTP_X_CSRF_TOKEN'] ?? null)) {
        json_out(['status' => 'error', 'code' => 'csrf', 'message' => 'Your session expired. Please refresh the page.'], 403);
    }

    if (!valid_tab_id($tabId)) {
        json_out(['status' => 'error', 'message' => 'Invalid request.'], 400);
    }

    switch ($action) {
        case 'login':   action_login($conn, $in, $tabId);   break;
        case 'sendotp': action_sendotp($conn, $in, $tabId); break;
        case 'useotp':  action_useotp($conn, $in, $tabId);  break;
        case 'resetp':  action_resetp($conn, $in);          break;
        case 'whoami':  action_whoami($conn, $tabId);       break;
        default:
            json_out(['status' => 'error', 'message' => 'Unknown action.'], 400);
    }
} catch (\Throwable $ex) {
    error_log('[RIAS handler] ' . get_class($ex) . ': ' . $ex->getMessage() . ' @ ' . $ex->getFile() . ':' . $ex->getLine());

    if ($conn instanceof PDO && $conn->inTransaction()) {
        $conn->rollBack();
    }
    if (!headers_sent()) {
        json_out([
            'status'  => 'error',
            'message' => APP_DEBUG ? $ex->getMessage() : 'Something went wrong. Please try again.',
        ], 500);
    }
}
