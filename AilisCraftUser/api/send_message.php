<?php
/**
 * Aili's Craft — Customer → shop direct message.
 *
 *   POST JSON { subject, message }  (requires logged-in customer session)
 *   -> { ok: true }
 *   -> { ok: false, error: "Please log in" } when not authenticated.
 *
 * Inserts a row into user_messages (admin_id = NULL = unassigned). The owner /
 * admin panel is expected to read and reply from its own side.
 */
declare(strict_types=1);
require_once __DIR__ . '/config.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax', 'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off']);
    session_start();
}

try {
    require_method((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET'), 'POST');

    $user = $_SESSION['ac_user'] ?? null;
    $userId = is_array($user) ? (int) ($user['id'] ?? 0) : 0;
    if ($userId <= 0) {
        json_out(['ok' => false, 'error' => 'Please log in'], 401);
    }

    $in = read_json_body();
    $subject = clean_str($in['subject'] ?? '', 150);
    $message = clean_str($in['message'] ?? '', 5000);
    if ($subject === '') {
        throw new ApiError('Please enter a subject.');
    }
    if ($message === '') {
        throw new ApiError('Please enter a message.');
    }

    $stmt = db()->prepare(
        'INSERT INTO user_messages (user_id, admin_id, subject, message, is_read, created_at)
         VALUES (?, NULL, ?, ?, 0, NOW())'
    );
    $stmt->execute([$userId, $subject, $message]);

    json_out(['ok' => true]);
} catch (ApiError $e) {
    json_out(['ok' => false, 'error' => $e->getMessage()], $e->status);
} catch (Throwable $e) {
    error_log('[AilisCraft send_message] ' . $e);
    json_out(['ok' => false, 'error' => AC_DEBUG ? $e->getMessage() : 'Message could not be sent. Please try again.'], 500);
}
