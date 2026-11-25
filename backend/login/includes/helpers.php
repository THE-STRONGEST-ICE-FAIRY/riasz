<?php
/**
 * Small, dependency-free helpers used by login.php, handler.php and reset.php.
 * Targets PHP 7.4+.
 */

// ---------------------------------------------------------------------------
// Output / escaping
// ---------------------------------------------------------------------------

function e($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** Send a JSON response and stop. */
function json_out(array $payload, int $status = 200): void
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

/**
 * Send a JSON response, release the browser, and let the script keep running
 * (used so slow work such as SMTP does not make "unknown email" answer faster
 * than "known email", which would leak which accounts exist).
 * After calling this, do not touch $_SESSION or send headers.
 */
function json_out_early(array $payload, int $status = 200): void
{
    $body = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

    ignore_user_abort(true);
    session_write_close();

    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');
    header('Content-Length: ' . strlen($body));
    header('Connection: close');
    echo $body;

    if (function_exists('fastcgi_finish_request')) {
        fastcgi_finish_request();
    } else {
        while (ob_get_level() > 0) {
            ob_end_flush();
        }
        flush();
    }
}

function throttled_out(int $retryAfter, string $what = 'attempts'): void
{
    $mins = (int) ceil($retryAfter / 60);
    $wait = $retryAfter < 60 ? $retryAfter . ' second(s)' : $mins . ' minute(s)';
    json_out([
        'status'      => 'error',
        'code'        => 'throttled',
        'message'     => "Too many {$what}. Try again in {$wait}.",
        'retry_after' => $retryAfter,
    ], 429);
}

function security_headers(): void
{
    header('X-Frame-Options: DENY');
    header('X-Content-Type-Options: nosniff');
    header('Referrer-Policy: no-referrer');
    header('Cache-Control: no-store');
}

// ---------------------------------------------------------------------------
// Session / CSRF
// ---------------------------------------------------------------------------

function start_secure_session(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['SERVER_PORT'] ?? null) == 443);

    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'secure'   => $https,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    ini_set('session.use_strict_mode', '1');
    session_start();
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf']) || !is_string($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_valid(?string $sent): bool
{
    return is_string($sent)
        && $sent !== ''
        && !empty($_SESSION['csrf'])
        && hash_equals($_SESSION['csrf'], $sent);
}

/** Each browser tab gets a random id from the client; it is only ever used as an array key. */
function valid_tab_id($id): bool
{
    return is_string($id) && preg_match('/^[a-f0-9-]{16,64}$/i', $id) === 1;
}

/** Create the signed-in state for one tab. Keeps the accounts[tab][user_id] shape dashboards expect. */
function establish_session(string $tabId, int $userId): void
{
    session_regenerate_id(true);

    if (!isset($_SESSION['accounts']) || !is_array($_SESSION['accounts'])) {
        $_SESSION['accounts'] = [];
    }

    unset($_SESSION['accounts'][$tabId]);
    $_SESSION['accounts'][$tabId] = ['user_id' => $userId, 'login_at' => time()];

    while (count($_SESSION['accounts']) > MAX_TAB_ACCOUNTS) {
        reset($_SESSION['accounts']);
        unset($_SESSION['accounts'][key($_SESSION['accounts'])]);
    }

    $_SESSION['active_tab'] = $tabId;
    unset($_SESSION['otp_pending'][$tabId]);
}

function destroy_session_completely(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', [
            'expires'  => time() - 42000,
            'path'     => $p['path'],
            'domain'   => $p['domain'],
            'secure'   => $p['secure'],
            'httponly' => $p['httponly'],
            'samesite' => $p['samesite'] ?? 'Lax',
        ]);
    }
    session_destroy();
}

// ---------------------------------------------------------------------------
// Input validation
// ---------------------------------------------------------------------------

/** Accepts JSON bodies and normal form posts. */
function read_input(): array
{
    $data = $_POST;
    $raw = file_get_contents('php://input');
    if (is_string($raw) && $raw !== '') {
        $json = json_decode($raw, true);
        if (is_array($json)) {
            $data = array_merge($data, $json);
        }
    }
    return $data;
}

function normalize_email($value): ?string
{
    if (!is_string($value)) {
        return null;
    }
    $email = strtolower(trim($value));
    if ($email === '' || strlen($email) > 254 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return null;
    }
    return $email;
}

function email_domain_allowed(string $email): bool
{
    if (ALLOWED_EMAIL_DOMAINS === []) {
        return true;
    }
    foreach (ALLOWED_EMAIL_DOMAINS as $domain) {
        $len = strlen($domain);
        if ($len > 0 && substr($email, -$len) === strtolower($domain)) {
            return true;
        }
    }
    return false;
}

function allowed_domains_text(): string
{
    return implode(' or ', ALLOWED_EMAIL_DOMAINS);
}

/** @return string[] list of problems, empty when the password is acceptable */
function password_problems(string $password, string $email = ''): array
{
    $problems = [];
    if (mb_strlen($password) < PASSWORD_MIN_LENGTH) {
        $problems[] = 'Password must be at least ' . PASSWORD_MIN_LENGTH . ' characters.';
    }
    if (strlen($password) > PASSWORD_MAX_BYTES) {
        $problems[] = 'Password is too long (maximum ' . PASSWORD_MAX_BYTES . ' bytes).';
    }
    if ($email !== '' && strcasecmp($password, $email) === 0) {
        $problems[] = 'Password must not be the same as your email.';
    }
    return $problems;
}

function normalize_role(string $role): string
{
    $role = strtolower(trim($role));
    return ROLE_ALIASES[$role] ?? $role;
}

function role_redirect(string $role): ?string
{
    return ROLE_REDIRECTS[normalize_role($role)] ?? null;
}

function client_ip(): string
{
    // REMOTE_ADDR only. X-Forwarded-For is client-controlled; trust it only if you run behind a known proxy.
    return substr((string) ($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0'), 0, 45);
}

// ---------------------------------------------------------------------------
// Rate limiting (table: ratelimits - see sql/migration.sql)
// ---------------------------------------------------------------------------

/** Seconds until another hit on $key is allowed, or 0 when under the limit. */
function rl_retry_after(PDO $db, string $key, int $max, int $window): int
{
    $since = date('Y-m-d H:i:s', time() - $window);
    $st = $db->prepare('SELECT COUNT(*) AS c, MIN(rl_created) AS oldest FROM ratelimits WHERE rl_key = ? AND rl_created > ?');
    $st->execute([$key, $since]);
    $row = $st->fetch();

    if ((int) $row['c'] < $max) {
        return 0;
    }
    return max(1, strtotime((string) $row['oldest']) + $window - time());
}

function rl_hit(PDO $db, string $key): void
{
    $st = $db->prepare('INSERT INTO ratelimits (rl_key, rl_created) VALUES (?, ?)');
    $st->execute([$key, date('Y-m-d H:i:s')]);

    if (random_int(1, 100) === 1) { // opportunistic cleanup
        $db->prepare('DELETE FROM ratelimits WHERE rl_created < ?')
           ->execute([date('Y-m-d H:i:s', time() - 86400)]);
    }
}

function rl_clear(PDO $db, string $key): void
{
    $db->prepare('DELETE FROM ratelimits WHERE rl_key = ?')->execute([$key]);
}

function rl_key(string $bucket, string $id): string
{
    return $bucket . ':' . sha1($id);
}

// ---------------------------------------------------------------------------
// Mail
// ---------------------------------------------------------------------------

function send_mail(string $to, string $subject, string $html, string $text): bool
{
    try {
        require_once MAILER_DIR . '/Exception.php';
        require_once MAILER_DIR . '/PHPMailer.php';
        require_once MAILER_DIR . '/SMTP.php';

        $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
        $mail->isSMTP();
        $mail->Host     = MAIL_HOST;
        $mail->Port     = MAIL_PORT;
        $mail->CharSet  = 'UTF-8';
        $mail->SMTPAuth = MAIL_USER !== '';
        if ($mail->SMTPAuth) {
            $mail->Username = MAIL_USER;
            $mail->Password = MAIL_PASS;
        }
        if (MAIL_SECURE !== '') {
            $mail->SMTPSecure = MAIL_SECURE;
        } else {
            $mail->SMTPAutoTLS = false;
        }

        $mail->setFrom(MAIL_FROM, MAIL_FROM_NAME);
        $mail->addAddress($to);
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $html;
        $mail->AltBody = $text;
        $mail->send();
        return true;
    } catch (\Throwable $ex) {
        error_log('[RIAS mail] ' . $ex->getMessage());
        return false;
    }
}

function app_url(string $file, array $query = []): string
{
    $url = rtrim(BASE_URL, '/') . rtrim(LOGIN_PATH, '/') . '/' . ltrim($file, '/');
    return $query ? $url . '?' . http_build_query($query) : $url;
}
