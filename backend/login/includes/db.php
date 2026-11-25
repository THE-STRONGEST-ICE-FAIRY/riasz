<?php
/**
 * Loads the project's PDO connection and makes sure it throws on errors.
 * Include AFTER bootstrap.php, from the global scope of an entry script.
 */
require_once DB_FILE;   // defines $conn

if (!isset($conn) || !($conn instanceof PDO)) {
    error_log('[RIAS] database.php did not provide a PDO $conn');
    http_response_code(500);
    exit('Server configuration error.');
}

$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
