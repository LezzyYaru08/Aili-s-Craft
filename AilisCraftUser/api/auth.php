<?php
declare(strict_types=1);
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/email_config.php';
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax', 'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off']);
    session_start();
}

/* ------------------------------------------------------------------
 * Password strength helper (shared by register + reset)
 * Rules: min 8 chars, must include uppercase, lowercase, and a digit.
 * ------------------------------------------------------------------ */
function ac_password_meets_minimum(string $password): bool
{
    return strlen($password) >= 8
        && preg_match('/[A-Z]/', $password) === 1
        && preg_match('/[a-z]/', $password) === 1
        && preg_match('/[0-9]/', $password) === 1;
}
define('AC_PASSWORD_RULE_MESSAGE', 'Password must be at least 8 characters and include uppercase, lowercase, and a number.');

/* ------------------------------------------------------------------
 * Password-reset helpers (forgot password flow)
 * ------------------------------------------------------------------ */

/** Absolute URL to the reset-password page for the given raw token. */
function ac_reset_link(string $token): string
{
    // APP_URL (config.php) = AC_APP_URL or auto-detected scheme+HTTP_HOST — no
    // trailing slash, no 'localhost' fallback (auto-detect covers CLI).
    // Script lives in .../AilisCraftUser/api/auth.php -> reset page is .../AilisCraftUser/reset-password.php
    $base = rtrim(str_replace('\\', '/', dirname(dirname($_SERVER['SCRIPT_NAME'] ?? '/AilisCraftUser/api/auth.php'))), '/');
    return APP_URL . $base . '/reset-password.php?token=' . urlencode($token);
}

/** Build the HTML body of the reset email (shared by all providers). */
function ac_reset_email_body(string $email, string $link): string
{
    return
        '<html><body style="font-family:Arial,sans-serif;color:#2b2230;">' .
        '<h2 style="color:#d85a86;">Reset your password</h2>' .
        '<p>Hi,</p>' .
        '<p>We received a request to reset the password for your Aili\'s Craft account (<strong>' . htmlspecialchars($email, ENT_QUOTES) . '</strong>).</p>' .
        '<p>Click the link below to choose a new password. It expires in <strong>60 minutes</strong>:</p>' .
        '<p><a href="' . htmlspecialchars($link, ENT_QUOTES) . '" style="display:inline-block;background:#eb6e9b;color:#fff;padding:12px 20px;border-radius:8px;text-decoration:none;">Reset my password</a></p>' .
        '<p style="word-break:break-all;color:#6b5c68;font-size:12px;">Or copy this address: ' . htmlspecialchars($link, ENT_QUOTES) . '</p>' .
        '<p>If you did not request this, you can safely ignore this email — your password will not change.</p>' .
        '<p>— Aili\'s Craft</p>' .
        '</body></html>';
}

/**
 * Send the reset email. Tries, in order:
 *   1. Brevo HTTP API (free 300/day) — if EMAIL_PROVIDER='brevo' and key set
 *   2. Resend HTTP API (free 100/day) — if EMAIL_PROVIDER='resend' and key set
 *   3. PHP mail() — last fallback (usually only on real hosting)
 * The reset link is ALWAYS written to uploads/reset_links.log as a record,
 * regardless of whether real sending succeeded. Never throws.
 */
function ac_send_reset_email(string $email, string $token): array
{
    $link = ac_reset_link($token);
    $subject = 'Reset your Aili\'s Craft password';
    $body = ac_reset_email_body($email, $link);
    $fromName = EMAIL_FROM_NAME;
    $fromAddr = EMAIL_FROM_ADDR;
    $sent = false;
    $provider = EMAIL_ENABLED ? EMAIL_PROVIDER : 'logonly';
    $error = null;

    try {
        if (EMAIL_ENABLED && EMAIL_PROVIDER === 'brevo') {
            $payload = json_encode([
                'sender'      => ['name' => $fromName, 'email' => $fromAddr],
                'to'          => [['email' => $email]],
                'subject'     => $subject,
                'htmlContent' => $body,
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            $ctx = stream_context_create([
                'http' => [
                    'method'        => 'POST',
                    'header'        => "api-key: " . EMAIL_API_KEY . "\r\nContent-Type: application/json\r\nAccept: application/json\r\n",
                    'content'       => $payload,
                    'timeout'       => EMAIL_TIMEOUT,
                    'ignore_errors' => true,
                ],
            ]);
            $resp = @file_get_contents('https://api.brevo.com/v3/smtp/email', false, $ctx);
            if ($resp === false) {
                $error = 'Network/HTTP error reaching Brevo.';
            } else {
                $data = json_decode((string)$resp, true);
                if (is_array($data) && !empty($data['messageId'])) {
                    $sent = true;
                } else {
                    $error = 'Brevo rejected: ' . substr((string)$resp, 0, 200);
                }
            }
        } elseif (EMAIL_ENABLED && EMAIL_PROVIDER === 'resend') {
            $payload = json_encode([
                'from'    => $fromName . ' <' . $fromAddr . '>',
                'to'      => [$email],
                'subject' => $subject,
                'html'    => $body,
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            $ctx = stream_context_create([
                'http' => [
                    'method'        => 'POST',
                    'header'        => "Authorization: Bearer " . EMAIL_API_KEY . "\r\nContent-Type: application/json\r\nAccept: application/json\r\n",
                    'content'       => $payload,
                    'timeout'       => EMAIL_TIMEOUT,
                    'ignore_errors' => true,
                ],
            ]);
            $resp = @file_get_contents('https://api.resend.com/emails', false, $ctx);
            if ($resp === false) {
                $error = 'Network/HTTP error reaching Resend.';
            } else {
                $data = json_decode((string)$resp, true);
                if (is_array($data) && !empty($data['id'])) {
                    $sent = true;
                } else {
                    $error = 'Resend rejected: ' . substr((string)$resp, 0, 200);
                }
            }
        } else {
            $headers = [
                'MIME-Version: 1.0',
                'Content-type: text/html; charset=utf-8',
                'From: ' . $fromName . ' <' . $fromAddr . '>',
                'Reply-To: ' . $fromAddr,
                'X-Mailer: PHP/' . PHP_VERSION,
            ];
            $sent = EMAIL_ENABLED ? @mail($email, $subject, $body, implode("\r\n", $headers)) : false;
            if (!$sent && EMAIL_ENABLED) {
                $error = 'PHP mail() returned false (no local MTA?).';
            }
        }
    } catch (Throwable $e) {
        $error = $e->getMessage();
    }

    // ALWAYS keep a record — even (especially) when real sending is off or fails.
    // logs/ is web-denied (.htaccess Require all denied) so reset tokens are never exposed.
    $logDir = dirname(__DIR__) . '/logs';
    if (is_dir($logDir) && is_writable($logDir)) {
        @file_put_contents(
            $logDir . '/reset_links.log',
            '[' . date('Y-m-d H:i:s') . '] ' . $email . ' -> ' . $link
            . ' | provider: ' . $provider
            . ' | sent: ' . ($sent ? 'yes' : 'no')
            . ($error ? ' | error: ' . $error : '')
            . PHP_EOL,
            FILE_APPEND
        );
    }
    error_log('[AilisCraft reset] link for ' . $email . ' => ' . $link . ' (' . $provider . ' sent: ' . ($sent ? 'yes' : 'no') . ($error ? ' | ' . $error : '') . ')');

    return ['sent' => $sent, 'link' => $link, 'provider' => $provider, 'error' => $error];
}
try {
    $action = (string)($_GET['action'] ?? '');
    $method = (string)($_SERVER['REQUEST_METHOD'] ?? 'GET');
    if ($action === 'register') {
        require_method($method, 'POST');
        $in = read_json_body();
        $name = clean_str($in['fullName'] ?? '', 150);
        $email = strtolower(clean_str($in['email'] ?? '', 190));
        $password = (string)($in['password'] ?? '');
        $accepted = !empty($in['termsAccepted']);
        if ($name === '') throw new ApiError('Please enter your full name.');
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) throw new ApiError('Please enter a valid email address.');
        if (!ac_password_meets_minimum($password)) throw new ApiError(AC_PASSWORD_RULE_MESSAGE);
        if (!$accepted) throw new ApiError('Please accept the Terms & Conditions.');
        $pdo = db();
        $check = $pdo->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
        $check->execute([$email]);
        if ($check->fetch()) throw new ApiError('An account with this email already exists. Please log in.');
        $stmt = $pdo->prepare('INSERT INTO users (full_name,email,password_hash,status,terms_accepted_at) VALUES (?,?,?,\'Active\',NOW())');
        $stmt->execute([$name, $email, password_hash($password, PASSWORD_DEFAULT)]);
        $_SESSION['ac_user'] = ['id' => (int)$pdo->lastInsertId(), 'fullName' => $name, 'email' => $email];
        session_regenerate_id(true);
        json_out(['ok' => true, 'user' => $_SESSION['ac_user']]);
    }
    if ($action === 'login') {
        require_method($method, 'POST');
        $in = read_json_body();
        $email = strtolower(clean_str($in['email'] ?? '', 190));
        $password = (string)($in['password'] ?? '');
        $stmt = db()->prepare('SELECT id,full_name,email,password_hash,status FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([$email]); $row = $stmt->fetch();
        if (!$row || $row['status'] !== 'Active' || !password_verify($password, (string)$row['password_hash'])) throw new ApiError('Incorrect email or password.', 401);
        session_regenerate_id(true);
        $_SESSION['ac_user'] = ['id' => (int)$row['id'], 'fullName' => $row['full_name'], 'email' => $row['email']];
        json_out(['ok' => true, 'user' => $_SESSION['ac_user']]);
    }
    if ($action === 'staff-login') {
        require_method($method, 'POST');
        $in = read_json_body();
        $identity = strtolower(clean_str($in['username'] ?? '', 190));
        $password = (string)($in['password'] ?? '');
        $wantedRole = strtolower(clean_str($in['panel'] ?? '', 30));
        if (!in_array($wantedRole, ['owner','admin'], true)) throw new ApiError('Invalid login panel.', 400);
        $stmt = db()->prepare('SELECT id,full_name,email,username,password_hash,role,status FROM admin_accounts WHERE (LOWER(username)=? OR LOWER(email)=?) LIMIT 1');
        $stmt->execute([$identity, $identity]); $row = $stmt->fetch();
        $role = strtolower((string)($row['role'] ?? ''));
        $roleOk = $wantedRole === 'owner' ? $role === 'owner' : in_array($role, ['administrator','manager'], true);
        if (!$row || $row['status'] !== 'Active' || !$roleOk || !password_verify($password, (string)$row['password_hash'])) throw new ApiError('Incorrect credentials or no active account exists for this panel.', 401);
        session_regenerate_id(true);
        $_SESSION['ac_staff'] = ['id' => (int)$row['id'], 'fullName' => $row['full_name'], 'email' => $row['email'], 'username' => $row['username'], 'role' => $row['role']];
        json_out(['ok' => true, 'staff' => $_SESSION['ac_staff']]);
    }
    if ($action === 'logout') {
        require_method($method, 'POST');
        $_SESSION = [];
        if (ini_get('session.use_cookies')) { $p = session_get_cookie_params(); setcookie(session_name(), '', time()-42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']); }
        session_destroy(); json_out(['ok' => true]);
    }

    /* ---------------- Forgot password: request a reset link ---------------- */
    if ($action === 'forgot') {
        require_method($method, 'POST');
        $in = read_json_body();
        $email = strtolower(clean_str($in['email'] ?? '', 190));
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            // Always respond identically to avoid email enumeration.
            json_out(['ok' => true, 'message' => 'If an account exists for that email, a reset link has been sent.']);
        }
        $pdo = db();

        // Rate limit: max 3 reset requests per email per hour.
        $countStmt = $pdo->prepare('SELECT COUNT(*) AS c FROM password_resets WHERE email = ? AND created_at > (NOW() - INTERVAL 1 HOUR)');
        $countStmt->execute([$email]);
        if ((int)$countStmt->fetchColumn() >= 3) {
            json_out(['ok' => true, 'message' => 'If an account exists for that email, a reset link has been sent.']);
        }

        $userStmt = $pdo->prepare('SELECT id FROM users WHERE email = ? AND status = \'Active\' LIMIT 1');
        $userStmt->execute([$email]);
        if ($userStmt->fetch()) {
            $token = bin2hex(random_bytes(32));
            $tokenHash = hash('sha256', $token);
            // Invalidate any earlier (still-valid) tokens for this email.
            $del = $pdo->prepare('DELETE FROM password_resets WHERE email = ?');
            $del->execute([$email]);
            $ins = $pdo->prepare('INSERT INTO password_resets (email, token_hash, expires_at) VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 1 HOUR))');
            $ins->execute([$email, $tokenHash]);
            $mailResult = ac_send_reset_email($email, $token);
        }

        $out = ['ok' => true, 'message' => 'If an account exists for that email, a reset link has been sent.'];
        if (AC_DEBUG && isset($mailResult)) {
            $out['debugResetLink'] = $mailResult['link'];
            $out['debugMailSent'] = $mailResult['sent'];
        }
        json_out($out);
    }

    /* ---------------- Verify a reset token is still valid (GET) ---------------- */
    if ($action === 'verify-reset-token') {
        require_method($method, 'GET');
        $token = (string)($_GET['token'] ?? '');
        if ($token === '' || !preg_match('/^[0-9a-f]{64}$/', $token)) {
            throw new ApiError('This reset link is invalid or has expired.', 400);
        }
        $stmt = db()->prepare('SELECT id FROM password_resets WHERE token_hash = ? AND expires_at > NOW() LIMIT 1');
        $stmt->execute([hash('sha256', $token)]);
        if (!$stmt->fetch()) {
            throw new ApiError('This reset link is invalid or has expired.', 400);
        }
        json_out(['ok' => true]);
    }

    /* ---------------- Reset password using a valid token ---------------- */
    if ($action === 'reset') {
        require_method($method, 'POST');
        $in = read_json_body();
        $token = (string)($in['token'] ?? '');
        $password = (string)($in['password'] ?? '');
        if ($token === '' || !preg_match('/^[0-9a-f]{64}$/', $token)) {
            throw new ApiError('This reset link is invalid or has expired.', 400);
        }
        if (!ac_password_meets_minimum($password)) {
            throw new ApiError(AC_PASSWORD_RULE_MESSAGE);
        }
        $pdo = db();
        $tokenHash = hash('sha256', $token);
        $find = $pdo->prepare('SELECT email FROM password_resets WHERE token_hash = ? AND expires_at > NOW() LIMIT 1');
        $find->execute([$tokenHash]);
        $row = $find->fetch();
        if (!$row) {
            throw new ApiError('This reset link is invalid or has expired.', 400);
        }
        $email = (string)$row['email'];

        $upd = $pdo->prepare('UPDATE users SET password_hash = ? WHERE email = ? AND status = \'Active\' LIMIT 1');
        $upd->execute([password_hash($password, PASSWORD_DEFAULT), $email]);

        // Single-use: consume all tokens for this email.
        $del = $pdo->prepare('DELETE FROM password_resets WHERE email = ?');
        $del->execute([$email]);

        json_out(['ok' => true, 'message' => 'Your password has been reset. You can now log in.']);
    }

    throw new ApiError('Unknown authentication action.', 404);
} catch (ApiError $e) { json_out(['ok'=>false,'error'=>$e->getMessage()], $e->status); }
catch (Throwable $e) { error_log('[AilisCraft auth] '.$e); json_out(['ok'=>false,'error'=>AC_DEBUG ? $e->getMessage() : 'Authentication service error. Check the database connection and schema.'], 500); }
