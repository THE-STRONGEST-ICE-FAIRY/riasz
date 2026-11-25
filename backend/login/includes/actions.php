<?php
/**
 * Action implementations for handler.php.
 * Every response uses the shape {status: "success"|"error", message, ...extras}.
 */

const GENERIC_OTP_MESSAGE   = 'If this email is registered, a 6-digit code has been sent.';
const GENERIC_RESET_MESSAGE = 'If this email is registered, a password reset link has been sent.';
const BAD_CREDENTIALS       = 'Incorrect email or password.';

/** Look up an active (non-archived) account with its school-user row. */
function find_active_user(PDO $db, string $email): ?array
{
    $st = $db->prepare(
        'SELECT u.user_id, u.user_email, u.user_role,
                su.schooluser_id, su.schooluser_password
           FROM users u
           JOIN schoolusers su ON su.user_id = u.user_id
          WHERE u.user_email = ? AND u.user_is_archived = 0
          LIMIT 1'
    );
    $st->execute([$email]);
    $row = $st->fetch();
    return $row ?: null;
}

/** Validate an email field; ends the request with a field error when invalid. */
function require_valid_email(array $in): string
{
    $email = normalize_email($in['email'] ?? null);

    if ($email === null) {
        json_out(['status' => 'error', 'field' => 'email', 'message' => 'Please enter a valid email address.'], 422);
    }
    if (!email_domain_allowed($email)) {
        json_out([
            'status'  => 'error',
            'field'   => 'email',
            'message' => 'Invalid email address. Please use ' . allowed_domains_text() . '.',
        ], 422);
    }
    return $email;
}

// ---------------------------------------------------------------------------
// login (email + password)
// ---------------------------------------------------------------------------
function action_login(PDO $db, array $in, string $tabId): void
{
    $email    = require_valid_email($in);
    $password = $in['password'] ?? null;

    if (!is_string($password) || $password === '' || strlen($password) > 1024) {
        json_out(['status' => 'error', 'field' => 'password', 'message' => 'Please enter your password.'], 422);
    }

    $kMail = rl_key('login:e', $email);
    $kIp   = rl_key('login:i', client_ip());

    $wait = max(
        rl_retry_after($db, $kMail, LOGIN_MAX_FAIL_MAIL, LOGIN_WINDOW),
        rl_retry_after($db, $kIp, LOGIN_MAX_FAIL_IP, LOGIN_WINDOW)
    );
    if ($wait > 0) {
        throttled_out($wait, 'sign-in attempts');
    }

    $user = find_active_user($db, $email);

    // Always run one password_verify so "no such account" costs the same as "wrong password".
    $dummy = '$2y$10$abcdefghijklmnopqrstuuwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0';
    $ok = password_verify($password, $user ? (string) $user['schooluser_password'] : $dummy);

    if (!$user || !$ok) {
        rl_hit($db, $kMail);
        rl_hit($db, $kIp);
        json_out(['status' => 'error', 'field' => 'password', 'message' => BAD_CREDENTIALS], 401);
    }

    $redirect = role_redirect((string) $user['user_role']);
    if ($redirect === null) {
        error_log('[RIAS] unknown role "' . $user['user_role'] . '" for user ' . $user['user_id']);
        json_out(['status' => 'error', 'message' => 'Your account role is not set up yet. Please contact support.'], 403);
    }

    if (password_needs_rehash((string) $user['schooluser_password'], PASSWORD_DEFAULT)) {
        $db->prepare('UPDATE schoolusers SET schooluser_password = ? WHERE schooluser_id = ?')
           ->execute([password_hash($password, PASSWORD_DEFAULT), $user['schooluser_id']]);
    }

    rl_clear($db, $kMail);
    establish_session($tabId, (int) $user['user_id']);

    json_out([
        'status'   => 'success',
        'message'  => 'Signed in.',
        'role'     => normalize_role((string) $user['user_role']),
        'redirect' => $redirect,
    ]);
}

// ---------------------------------------------------------------------------
// sendotp
// ---------------------------------------------------------------------------
function action_sendotp(PDO $db, array $in, string $tabId): void
{
    $email = require_valid_email($in);
    $ip    = client_ip();

    $kLock = rl_key('otp:fail', $email);   // shared with useotp failures
    $kMin  = rl_key('otp:send', $email);
    $kHour = rl_key('otp:sendh', $email);
    $kIp   = rl_key('otp:sendi', $ip);

    $lock = rl_retry_after($db, $kLock, OTP_FAIL_MAX, OTP_FAIL_WINDOW);
    if ($lock > 0) {
        throttled_out($lock, 'failed attempts');
    }

    $wait = max(
        rl_retry_after($db, $kMin, 1, OTP_RESEND_COOLDOWN),
        rl_retry_after($db, $kHour, OTP_SEND_MAX_PER_15M, 900),
        rl_retry_after($db, $kIp, OTP_SEND_MAX_PER_IP, 900)
    );
    if ($wait > 0) {
        throttled_out($wait, 'requests');
    }

    // Count the request for every email, known or not, so responses look identical.
    rl_hit($db, $kMin);
    rl_hit($db, $kHour);
    rl_hit($db, $kIp);

    // Bind the pending email to THIS tab (not the whole session).
    if (!isset($_SESSION['otp_pending']) || !is_array($_SESSION['otp_pending'])) {
        $_SESSION['otp_pending'] = [];
    }
    $_SESSION['otp_pending'][$tabId] = ['email' => $email, 'at' => time()];

    $user = find_active_user($db, $email);

    json_out_early([
        'status'       => 'success',
        'message'      => GENERIC_OTP_MESSAGE,
        'expires_in'   => OTP_TTL,
        'resend_after' => OTP_RESEND_COOLDOWN,
    ]);

    if (!$user) {
        return;
    }

    $otp     = (string) random_int(100000, 999999);
    $created = date('Y-m-d H:i:s');
    $expiry  = date('Y-m-d H:i:s', time() + OTP_TTL);

    $db->beginTransaction();
    $db->prepare('DELETE FROM onetimepasswords WHERE schooluser_id = ?')->execute([$user['schooluser_id']]);
    $db->prepare(
        'INSERT INTO onetimepasswords (schooluser_id, otp_code, otp_date_created, otp_date_expiry, otp_attempts)
         VALUES (?, ?, ?, ?, 0)'
    )->execute([$user['schooluser_id'], $otp, $created, $expiry]);
    $db->commit();

    $mins = (int) (OTP_TTL / 60);
    $sent = send_mail(
        $email,
        'Your RIAS sign-in code',
        '<h2>Your sign-in code</h2><p style="font-size:24px;letter-spacing:4px"><b>' . e($otp) . '</b></p>'
        . "<p>This code expires in {$mins} minutes. If you did not request it, you can ignore this email.</p>",
        "Your RIAS sign-in code is {$otp}. It expires in {$mins} minutes."
    );
    if (!$sent) {
        error_log('[RIAS] OTP email failed for user ' . $user['user_id']);
    }
}

// ---------------------------------------------------------------------------
// useotp
// ---------------------------------------------------------------------------
function action_useotp(PDO $db, array $in, string $tabId): void
{
    $pending = $_SESSION['otp_pending'][$tabId] ?? null;
    if (!is_array($pending) || empty($pending['email']) || (time() - (int) $pending['at']) > 900) {
        unset($_SESSION['otp_pending'][$tabId]);
        json_out(['status' => 'error', 'code' => 'no_pending', 'message' => 'Your session expired. Please request a new code.'], 400);
    }
    $email = (string) $pending['email'];

    $otp = $in['otp'] ?? '';
    $otp = is_string($otp) || is_int($otp) ? trim((string) $otp) : '';

    $kFail = rl_key('otp:fail', $email);
    $lock  = rl_retry_after($db, $kFail, OTP_FAIL_MAX, OTP_FAIL_WINDOW);
    if ($lock > 0) {
        throttled_out($lock, 'failed attempts');
    }

    $invalid = function (string $message = 'Invalid or expired code.') use ($db, $kFail): void {
        rl_hit($db, $kFail);
        json_out(['status' => 'error', 'message' => $message], 401);
    };

    if (preg_match('/^\d{6}$/', $otp) !== 1) {
        $invalid();
    }

    $st = $db->prepare(
        'SELECT otp.otp_id, otp.otp_code, otp.otp_attempts, otp.otp_date_expiry,
                u.user_id, u.user_role
           FROM users u
           JOIN schoolusers su ON su.user_id = u.user_id
           JOIN onetimepasswords otp ON otp.schooluser_id = su.schooluser_id
          WHERE u.user_email = ? AND u.user_is_archived = 0
          ORDER BY otp.otp_date_created DESC
          LIMIT 1'
    );
    $st->execute([$email]);
    $row = $st->fetch();

    if (!$row) {
        $invalid();
    }

    if (strtotime((string) $row['otp_date_expiry']) <= time()) {
        $db->prepare('DELETE FROM onetimepasswords WHERE otp_id = ?')->execute([$row['otp_id']]);
        $invalid();
    }

    if ((int) $row['otp_attempts'] >= OTP_MAX_ATTEMPTS) {
        $invalid('Too many wrong attempts for this code. Please request a new one.');
    }

    if (!hash_equals((string) $row['otp_code'], $otp)) {
        $db->prepare('UPDATE onetimepasswords SET otp_attempts = otp_attempts + 1 WHERE otp_id = ?')
           ->execute([$row['otp_id']]);
        $invalid();
    }

    $redirect = role_redirect((string) $row['user_role']);
    if ($redirect === null) {
        error_log('[RIAS] unknown role "' . $row['user_role'] . '" for user ' . $row['user_id']);
        json_out(['status' => 'error', 'message' => 'Your account role is not set up yet. Please contact support.'], 403);
    }

    $db->prepare('DELETE FROM onetimepasswords WHERE otp_id = ?')->execute([$row['otp_id']]);
    rl_clear($db, $kFail);
    establish_session($tabId, (int) $row['user_id']);

    json_out([
        'status'   => 'success',
        'message'  => 'Code verified.',
        'role'     => normalize_role((string) $row['user_role']),
        'redirect' => $redirect,
    ]);
}

// ---------------------------------------------------------------------------
// resetp (forgot password)
// ---------------------------------------------------------------------------
function action_resetp(PDO $db, array $in): void
{
    $email = require_valid_email($in);

    $kMail = rl_key('reset:e', $email);
    $kIp   = rl_key('reset:i', client_ip());

    $wait = max(
        rl_retry_after($db, $kMail, 1, RESET_COOLDOWN),
        rl_retry_after($db, $kIp, RESET_MAX_PER_IP, 3600)
    );
    if ($wait > 0) {
        throttled_out($wait, 'requests');
    }

    rl_hit($db, $kMail);
    rl_hit($db, $kIp);

    $user = find_active_user($db, $email);

    json_out_early([
        'status'      => 'success',
        'message'     => GENERIC_RESET_MESSAGE,
        'retry_after' => RESET_COOLDOWN,
    ]);

    if (!$user) {
        return;
    }

    $token = bin2hex(random_bytes(32));
    $hash  = hash('sha256', $token);   // only the hash is stored; the plain token goes in the email

    $db->beginTransaction();
    $db->prepare('DELETE FROM passwordresets WHERE schooluser_id = ?')->execute([$user['schooluser_id']]);
    $db->prepare(
        'INSERT INTO passwordresets
            (schooluser_id, passreset_token, passreset_date_created, passreset_date_expiry, passreset_is_used)
         VALUES (?, ?, ?, ?, 0)'
    )->execute([
        $user['schooluser_id'],
        $hash,
        date('Y-m-d H:i:s'),
        date('Y-m-d H:i:s', time() + RESET_TTL),
    ]);
    $db->commit();

    $link = app_url('reset.php', ['token' => $token]);
    $mins = (int) (RESET_TTL / 60);

    $sent = send_mail(
        $email,
        'Reset your RIAS password',
        '<h3>Password reset request</h3><p>Click the link below to choose a new password:</p>'
        . '<p><a href="' . e($link) . '">Reset password</a></p>'
        . "<p>This link expires in {$mins} minutes and can be used once. If you did not request it, you can ignore this email.</p>",
        "Reset your RIAS password: {$link}\nThis link expires in {$mins} minutes and can be used once."
    );
    if (!$sent) {
        error_log('[RIAS] reset email failed for user ' . $user['user_id']);
    }
}

// ---------------------------------------------------------------------------
// whoami (is this tab already signed in?)
// ---------------------------------------------------------------------------
function action_whoami(PDO $db, string $tabId): void
{
    $user = getUserFromTab($db, $tabId);
    $redirect = $user ? role_redirect((string) $user['user_role']) : null;

    if ($user && $redirect !== null) {
        json_out(['status' => 'success', 'authenticated' => true, 'redirect' => $redirect]);
    }
    json_out(['status' => 'success', 'authenticated' => false]);
}

// ---------------------------------------------------------------------------
// logout
// ---------------------------------------------------------------------------
function action_logout(string $tabId): void
{
    unset($_SESSION['accounts'][$tabId], $_SESSION['otp_pending'][$tabId]);

    if (($_SESSION['active_tab'] ?? null) === $tabId) {
        unset($_SESSION['active_tab']);
    }

    // Last signed-in tab gone -> drop the whole session and cookie.
    if (empty($_SESSION['accounts'])) {
        destroy_session_completely();
    }

    json_out(['status' => 'success', 'message' => 'Signed out.']);
}
