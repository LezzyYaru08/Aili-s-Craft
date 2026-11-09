<?php
/**
 * Aili's Craft — API configuration and shared helpers.
 *
 * Setup:
 *   1. Import ../../ailiscraft_database_schema.sql into MySQL/MariaDB.
 *   2. Adjust the DB_* values below (the defaults match a fresh XAMPP/Laragon install),
 *      or set the AC_DB_HOST / AC_DB_PORT / AC_DB_NAME / AC_DB_USER / AC_DB_PASS
 *      environment variables.
 *   3. Serve the AilisCraftUser folder through Apache/PHP (not file://).
 */
declare(strict_types=1);

function ac_env(string $key, string $default): string
{
    $value = getenv($key);
    return ($value === false || $value === '') ? $default : $value;
}

define('DB_HOST', ac_env('AC_DB_HOST', '127.0.0.1'));
define('DB_PORT', (int) ac_env('AC_DB_PORT', '3306'));
define('DB_NAME', ac_env('AC_DB_NAME', 'ailiscraft_db'));
define('DB_USER', ac_env('AC_DB_USER', 'root'));
define('DB_PASS', ac_env('AC_DB_PASS', ''));

// Philippines has no DST, so a fixed +08:00 offset is safe for the MySQL session.
define('APP_TIMEZONE', 'Asia/Manila');
define('DB_SESSION_TZ', '+08:00');

// Must match the delivery fee shown on order-summary.html (main.js).
define('DELIVERY_FEE', 80.00);

// Set AC_DEBUG=1 to include raw database error text in API responses (development only).
define('AC_DEBUG', ac_env('AC_DEBUG', '0') === '1');

/* ------------------------------------------------------------------
 * Deployment-hardened runtime config (all env-driven; see .env.example).
 * ------------------------------------------------------------------ */
// Public base URL of the application, no trailing slash. Set AC_APP_URL in
// production; falls back to auto-detected scheme + HTTP_HOST (correct for
// most hosting). Used for reset links, the cookie-secure flag, absolute URLs.
if (!defined('APP_URL')) {
    $acAppUrl = ac_env('AC_APP_URL', '');
    if ($acAppUrl !== '') {
        define('APP_URL', rtrim($acAppUrl, '/'));
    } else {
        $acScheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $acHost = (string)($_SERVER['HTTP_HOST'] ?? 'localhost');
        define('APP_URL', $acScheme . '://' . $acHost);
    }
}
// Admin source-code browser (AilisCraftAdmin/source-code.php) — OFF by default.
define('AC_ENABLE_SOURCE_BROWSER', ac_env('AC_ENABLE_SOURCE_BROWSER', '0') === '1');
// Optional shared secret permitting setup_accounts.php to seed accounts from a
// non-localhost IP. Leave empty to keep the localhost-only guard.
define('AC_ADMIN_SETUP_TOKEN', ac_env('AC_ADMIN_SETUP_TOKEN', ''));
// Session storage: 'files' (default, works on XAMPP/shared hosting) or 'db'
// (persists to the ac_sessions table — needs migration 007).
define('AC_SESSION_DRIVER', ac_env('AC_SESSION_DRIVER', 'files'));
// Session-cookie hardening (defaults; auth.php may refine per-request).
ini_set('session.use_strict_mode', '1');
ini_set('session.cookie_httponly', '1');
ini_set('session.cookie_samesite', 'Lax');
ini_set('session.cookie_secure', strncasecmp(APP_URL, 'https://', 8) === 0 ? '1' : '0');
if (AC_SESSION_DRIVER === 'db') {
    try {
        $acSessionHandlerFile = __DIR__ . '/session_db.php';
        if (is_file($acSessionHandlerFile) && @include_once $acSessionHandlerFile && class_exists('AC_DBSessionHandler')) {
            // Registered BEFORE any session_start(); a DB failure inside the
            // handler never throws (it degrades to empty session data).
            session_set_save_handler(new AC_DBSessionHandler(), true);
        } else {
            error_log('[AilisCraft] DB session handler unavailable, falling back to files.');
        }
    } catch (Throwable $e) {
        error_log('[AilisCraft] DB session handler failed to register, falling back to files: ' . $e->getMessage());
    }
}

date_default_timezone_set(APP_TIMEZONE);

/** An error that is safe to show to the customer. */
class ApiError extends RuntimeException
{
    public int $status;

    public function __construct(string $message, int $status = 400)
    {
        parent::__construct($message);
        $this->status = $status;
    }
}

function json_out(array $payload, int $status = 200): void
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PARTIAL_OUTPUT_ON_ERROR);
    exit;
}

function db(): PDO
{
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $dsn = sprintf('mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4', DB_HOST, DB_PORT, DB_NAME);
    try {
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
        $pdo->exec("SET time_zone = '" . DB_SESSION_TZ . "'");
    } catch (PDOException $e) {
        error_log('[AilisCraft] DB connection failed: ' . $e->getMessage());
        $hint = AC_DEBUG ? ' (' . $e->getMessage() . ')' : '';
        throw new ApiError(
            'Cannot connect to the database. Check api/config.php, make sure MySQL is running '
            . 'and that ailiscraft_database_schema.sql has been imported.' . $hint,
            500
        );
    }
    return $pdo;
}

/** Decode the JSON request body (max 256 KB). */
function read_json_body(): array
{
    $raw = file_get_contents('php://input');
    if ($raw === false || $raw === '') {
        throw new ApiError('Empty request body.');
    }
    if (strlen($raw) > 4194304) {
        throw new ApiError('Request is too large.', 413);
    }
    $data = json_decode($raw, true);
    if (!is_array($data)) {
        throw new ApiError('Request body must be valid JSON.');
    }
    return $data;
}

function require_method(string $actual, string $expected): void
{
    if (strtoupper($actual) !== $expected) {
        header('Allow: ' . $expected);
        throw new ApiError('Method not allowed.', 405);
    }
}

/** Trim, collapse control characters and cap the length (multibyte safe). */
function clean_str($value, int $max): string
{
    if (!is_scalar($value)) {
        return '';
    }
    $value = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', (string) $value) ?? '';
    $value = trim($value);
    return function_exists('mb_substr') ? mb_substr($value, 0, $max, 'UTF-8') : substr($value, 0, $max);
}

function normalize_email($value): string
{
    $email = strtolower(clean_str($value, 190));
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new ApiError('A valid account email is required. Please log in again.', 401);
    }
    return $email;
}
