<?php
/**
 * RIAS login module - central configuration.
 * Everything environment-specific lives here. No other file should hardcode these values.
 */

// ---------------------------------------------------------------------------
// Environment
// ---------------------------------------------------------------------------
const APP_DEBUG = false;                 // true = show PHP errors (development only)
$configuredBaseUrl = getenv('RIAS_BASE_URL');
define(
    'BASE_URL',
    rtrim($configuredBaseUrl !== false && $configuredBaseUrl !== '' ? $configuredBaseUrl : 'http://localhost:8000', '/')
); // Override RIAS_BASE_URL when deploying outside the local PHP server.
const LOGIN_PATH = '/backend/login';     // URL path of this folder, NO trailing slash.

define('DB_FILE',    __DIR__ . '/../../../utilities/database/database.php'); // must define $conn (PDO)
define('MAILER_DIR', __DIR__ . '/../../../utilities/PHPMailer');

// ---------------------------------------------------------------------------
// Mail (defaults target Mailpit on localhost)
// ---------------------------------------------------------------------------
const MAIL_HOST      = '127.0.0.1';
const MAIL_PORT      = 1025;
const MAIL_USER      = '';               // leave empty for no SMTP auth
const MAIL_PASS      = '';
const MAIL_SECURE    = '';               // '' | 'tls' | 'ssl'
const MAIL_FROM      = 'no-reply@local.test';
const MAIL_FROM_NAME = 'RIAS';

// ---------------------------------------------------------------------------
// Accounts / roles
// ---------------------------------------------------------------------------
// Only these email domains may sign in. Use an empty array to allow any domain.
const ALLOWED_EMAIL_DOMAINS = ['@student.apc.edu.ph', '@apc.edu.ph'];

// users.user_role value  ->  page to open after sign-in (relative to this folder)
const ROLE_REDIRECTS = [
    'admin'     => '../admin/admin.php',
    'officer'   => '../officer/officer.php',
    'executive' => '../executive/executive.php',
    'program'   => '../Programdi/_index.php',
    'intern'    => '../intern/intern.php',
];

// Long role names that older data may contain -> canonical key above
const ROLE_ALIASES = [
    'executive director' => 'executive',
    'program director'   => 'program',
    'internship officer' => 'officer',
    'student intern'     => 'intern',
];

const MAX_TAB_ACCOUNTS = 10;             // max simultaneously signed-in browser tabs per session

// ---------------------------------------------------------------------------
// Passwords
// ---------------------------------------------------------------------------
const PASSWORD_MIN_LENGTH = 12;
const PASSWORD_MAX_BYTES  = 72;         // bcrypt ignores everything after 72 bytes

// ---------------------------------------------------------------------------
// One-time passwords (seconds unless noted)
// ---------------------------------------------------------------------------
const OTP_TTL              = 600;        // how long a code is valid
const OTP_MAX_ATTEMPTS     = 3;          // wrong guesses allowed per code
const OTP_RESEND_COOLDOWN  = 60;         // minimum gap between sends to one email
const OTP_SEND_MAX_PER_15M = 5;          // sends per email per 15 min
const OTP_SEND_MAX_PER_IP  = 10;         // sends per IP per 15 min
const OTP_FAIL_MAX         = 3;          // wrong codes per email within OTP_FAIL_WINDOW ...
const OTP_FAIL_WINDOW      = 300;        // ... before that email is locked for the rest of the window

// ---------------------------------------------------------------------------
// Password reset links
// ---------------------------------------------------------------------------
const RESET_TTL        = 900;            // link validity
const RESET_COOLDOWN   = 300;            // minimum gap between reset emails to one address
const RESET_MAX_PER_IP = 10;             // reset requests per IP per hour

// ---------------------------------------------------------------------------
// Password sign-in throttling
// ---------------------------------------------------------------------------
const LOGIN_WINDOW        = 900;         // 15 minutes
const LOGIN_MAX_FAIL_MAIL = 5;           // failures per email in the window
const LOGIN_MAX_FAIL_IP   = 20;          // failures per IP in the window
