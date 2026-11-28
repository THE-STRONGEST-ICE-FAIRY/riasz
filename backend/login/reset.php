<?php
/**
 * Set / reset a password using the emailed link.
 * The link carries a random token; only its SHA-256 hash is stored in passwordresets.
 */
require __DIR__ . '/includes/bootstrap.php';
require __DIR__ . '/includes/db.php';

security_headers();
header("Content-Security-Policy: default-src 'self'; style-src 'self' https://fonts.googleapis.com; font-src https://fonts.gstatic.com; img-src 'self' data:; frame-ancestors 'none'; base-uri 'self'; form-action 'self'");

$state  = 'invalid';   // invalid | form | done
$errors = [];

$token = $_GET['token'] ?? '';
$reset = null;

try {
    if (is_string($token) && preg_match('/^[a-f0-9]{64}$/', $token) === 1) {
        $st = $conn->prepare(
            'SELECT pr.passreset_id, pr.schooluser_id, u.user_email
               FROM passwordresets pr
               JOIN schoolusers su ON su.schooluser_id = pr.schooluser_id
               JOIN users u ON u.user_id = su.user_id
              WHERE pr.passreset_token = ?
                AND pr.passreset_is_used = 0
                AND pr.passreset_date_expiry > ?
                AND u.user_is_archived = 0
              LIMIT 1'
        );
        $st->execute([hash('sha256', $token), date('Y-m-d H:i:s')]);
        $reset = $st->fetch() ?: null;
    }

    if ($reset) {
        $state = 'form';

        if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
            $password = $_POST['password'] ?? '';
            $confirm  = $_POST['confirm_password'] ?? '';

            if (!csrf_valid($_POST['csrf'] ?? null)) {
                $errors[] = 'Your session expired. Please reload this page and try again.';
            } elseif (!is_string($password) || !is_string($confirm) || $password === '') {
                $errors[] = 'Please fill in both password fields.';
            } elseif ($password !== $confirm) {
                $errors[] = 'Passwords do not match.';
            } else {
                $errors = password_problems($password, (string) $reset['user_email']);
            }

            if (!$errors) {
                $conn->beginTransaction();

                $conn->prepare('UPDATE schoolusers SET schooluser_password = ? WHERE schooluser_id = ?')
                     ->execute([password_hash($password, PASSWORD_DEFAULT), $reset['schooluser_id']]);

                $conn->prepare('UPDATE passwordresets SET passreset_is_used = 1 WHERE passreset_id = ?')
                     ->execute([$reset['passreset_id']]);

                // Any other outstanding links or codes for this account are now void.
                $conn->prepare('DELETE FROM passwordresets WHERE schooluser_id = ? AND passreset_id <> ?')
                     ->execute([$reset['schooluser_id'], $reset['passreset_id']]);
                $conn->prepare('DELETE FROM onetimepasswords WHERE schooluser_id = ?')
                     ->execute([$reset['schooluser_id']]);

                $conn->commit();

                // Let the person sign in straight away even if they had failed attempts before.
                try {
                    rl_clear($conn, rl_key('login:e', strtolower((string) $reset['user_email'])));
                } catch (\Throwable $ignored) {
                    // throttle table missing: the password is already saved, so don't report a failure
                }

                $state = 'done';
            }
        }
    }
} catch (\Throwable $ex) {
    error_log('[RIAS reset] ' . $ex->getMessage());
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    $state  = $reset ? 'form' : 'invalid';
    $errors = ['Something went wrong. Please try again.'];
}

$minLen = PASSWORD_MIN_LENGTH;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="robots" content="noindex, nofollow">
  <title>Set Password | Rams Internship Assessment System</title>
  <link href="https://fonts.googleapis.com/css2?family=Lora:wght@400;600&family=Inria+Serif:wght@400;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="style.css">
  <link rel="icon" type="image/png" href="../../assets/rias_icon.png">
</head>
<body>
  <header class="header">
    <div class="container header-content">
      <img src="../../assets/header_apclogo_orange.png" alt="RIAS Logo" class="rias-logo">
      <img src="../../assets/header_apclogo.png" alt="APC Logo" class="header-logo">
    </div>
  </header>

  <main class="hero reset-hero">
    <div class="auth-card">
<?php if ($state === 'done'): ?>
      <h1 class="modal-title">Password updated</h1>
      <p class="notice notice-ok" role="status">Your password was set successfully. You can now sign in.</p>
      <a class="btn btn-primary btn-link" href="../../pages/login/login.php">Go to Sign In</a>

<?php elseif ($state === 'form'): ?>
      <h1 class="modal-title">Set your password</h1>
      <p class="subtitle">Choose a new password for <strong><?= e($reset['user_email']) ?></strong>.</p>

<?php if ($errors): ?>
      <div class="notice notice-error" role="alert">
<?php foreach ($errors as $msg): ?>
        <p><?= e($msg) ?></p>
<?php endforeach; ?>
      </div>
<?php endif; ?>

      <form method="post" action="" id="reset-form" novalidate>
        <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">

        <div class="input-group">
          <label for="password">New password</label>
          <div class="input-wrapper">
            <input type="password" id="password" name="password" minlength="<?= $minLen ?>" autocomplete="new-password" required>
            <button type="button" class="input-icon toggle-password" aria-label="Show new password" data-target="password"
                    data-label-show="Show new password" data-label-hide="Hide new password"
                    data-src-show="../../assets/login_password_show.png" data-src-hide="../../assets/login_password.png">
              <img src="../../assets/login_password.png" alt="">
            </button>
          </div>
          <span class="hint" id="length-hint">At least <?= $minLen ?> characters.</span>
        </div>

        <div class="input-group">
          <label for="confirm_password">Confirm password</label>
          <div class="input-wrapper">
            <input type="password" id="confirm_password" name="confirm_password" autocomplete="new-password" required>
            <button type="button" class="input-icon toggle-password" aria-label="Show confirmation password" data-target="confirm_password"
                    data-label-show="Show confirmation password" data-label-hide="Hide confirmation password"
                    data-src-show="../../assets/login_password_show.png" data-src-hide="../../assets/login_password.png">
              <img src="../../assets/login_password.png" alt="">
            </button>
          </div>
          <span class="error" id="match-error" aria-live="polite"></span>
        </div>

        <button type="submit" class="btn btn-primary">Set Password</button>
      </form>

<?php else: ?>
      <h1 class="modal-title">Link not valid</h1>
      <p class="notice notice-error" role="alert">This link is invalid, expired, or has already been used.</p>
      <p class="subtitle">Request a new one with <em>Forgot Password?</em> on the sign-in page.</p>
      <a class="btn btn-primary btn-link" href="../../pages/login/login.php">Back to Sign In</a>
<?php endif; ?>
    </div>
    <div class="reset-branding" aria-hidden="true">
      <span class="reset-brand-title">RIAS</span>
      <span class="reset-brand-subtitle">RAMS<br>INTERNSHIP<br>ASSESSMENT<br>SYSTEM</span>
    </div>
  </main>

  <script src="reset.js" defer></script>
</body>
</html>
