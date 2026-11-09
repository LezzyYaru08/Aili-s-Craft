<?php
/**
 * Aili's Craft — Owner panel: direct messages from customers.
 *
 * Endpoints (same DB credentials as api/config.php):
 *   GET  ?action=list       -> {ok:true, messages:[{id,user_id,name,email,subject,message,is_read,created_at}]}
 *                              ordered by created_at DESC.
 *   POST ?action=mark-read  body {id:int} -> sets is_read=1 for that message.
 */
declare(strict_types=1);
require_once __DIR__ . '/config.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax', 'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off']);
    session_start();
}

try {
    $action = (string) ($_GET['action'] ?? '');
    $method = (string) ($_SERVER['REQUEST_METHOD'] ?? 'GET');
    $pdo = db();

    if ($action === 'list') {
        require_method($method, 'GET');
        $stmt = $pdo->prepare(
            'SELECT m.id, m.user_id, u.full_name AS name, u.email,
                    m.subject, m.message, m.is_read, m.created_at
             FROM user_messages m
             INNER JOIN users u ON u.id = m.user_id
             ORDER BY m.created_at DESC'
        );
        $stmt->execute();
        $messages = $stmt->fetchAll();
        foreach ($messages as &$row) {
            $row['id'] = (int) $row['id'];
            $row['user_id'] = (int) $row['user_id'];
            $row['is_read'] = (int) $row['is_read'];
        }
        unset($row);
        json_out(['ok' => true, 'messages' => $messages]);
    }

    if ($action === 'mark-read') {
        require_method($method, 'POST');
        $in = read_json_body();
        $id = (int) ($in['id'] ?? 0);
        if ($id <= 0) {
            throw new ApiError('A valid message id is required.', 400);
        }
        $stmt = $pdo->prepare('UPDATE user_messages SET is_read = 1 WHERE id = ?');
        $stmt->execute([$id]);
        json_out(['ok' => true, 'updated' => $stmt->rowCount()]);
    }

    throw new ApiError('Unknown messages action.', 404);
} catch (ApiError $e) {
    json_out(['ok' => false, 'error' => $e->getMessage()], $e->status);
} catch (Throwable $e) {
    error_log('[AilisCraft messages] ' . $e);
    json_out(
        ['ok' => false, 'error' => AC_DEBUG ? $e->getMessage() : 'Messages service error. Check the database connection and schema.'],
        500
    );
}
