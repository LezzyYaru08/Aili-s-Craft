<?php
declare(strict_types=1);
require_once __DIR__ . '/config.php';
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax', 'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off']);
    session_start();
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
        if (strlen($password) < 6) throw new ApiError('Password must be at least 6 characters.');
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
    throw new ApiError('Unknown authentication action.', 404);
} catch (ApiError $e) { json_out(['ok'=>false,'error'=>$e->getMessage()], $e->status); }
catch (Throwable $e) { error_log('[AilisCraft auth] '.$e); json_out(['ok'=>false,'error'=>AC_DEBUG ? $e->getMessage() : 'Authentication service error. Check the database connection and schema.'], 500); }
