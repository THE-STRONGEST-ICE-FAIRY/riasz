<?php
/**
 * Shared bootstrap: config, helpers, secure session. Does NOT touch the database
 * (so the login page still renders if the DB is down). Use db.php for that.
 */
date_default_timezone_set('Asia/Manila');

require_once __DIR__ . '/config.php';

error_reporting(E_ALL);
ini_set('display_errors', APP_DEBUG ? '1' : '0');
ini_set('log_errors', '1');

require_once __DIR__ . '/helpers.php';

start_secure_session();
