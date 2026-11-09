<?php
/* ==========================================================================
 * !!! DELETE THIS FILE AFTER DEPLOYMENT !!!
 *
 * setup_accounts.php seeds the initial Administrator + Owner accounts. It
 * must NOT ship to production: after the first successful run, delete it.
 *
 * Access rules (deployment-hardened):
 *   - By default it runs ONLY from localhost / 127.0.0.1 (XAMPP/Laragon).
 *   - To seed accounts on a remote host once, set AC_ADMIN_SETUP_TOKEN to a
 *     random string and visit: setup_accounts.php?token=<that-string>.
 *     A valid token bypasses the localhost guard; any wrong/missing token
 *     returns 403. Unset AC_ADMIN_SETUP_TOKEN and DELETE this file after.
 * ========================================================================== */
declare(strict_types=1);
require_once __DIR__ . '/../AilisCraftUser/api/config.php';
$host = strtolower((string)($_SERVER['HTTP_HOST'] ?? ''));
$setupToken = (string)($_GET['token'] ?? '');
$tokenOk = AC_ADMIN_SETUP_TOKEN !== '' && hash_equals(AC_ADMIN_SETUP_TOKEN, $setupToken);
if (AC_ADMIN_SETUP_TOKEN !== '' && !$tokenOk) {
    http_response_code(403);
    exit('Invalid or missing setup token. Provide ?token=<AC_ADMIN_SETUP_TOKEN>.');
}
if (!$tokenOk && !in_array(explode(':', $host)[0], ['localhost', '127.0.0.1'], true)) {
    http_response_code(403);
    exit('Run this setup only on your local XAMPP computer, or provide AC_ADMIN_SETUP_TOKEN via ?token=.');
}
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  try {
    $pdo = db();
    $count = (int)$pdo->query('SELECT COUNT(*) FROM admin_accounts')->fetchColumn();
    if ($count > 0) { $message = 'Setup skipped: admin_accounts already contains records. No accounts were changed.'; }
    else {
      $q = $pdo->prepare('INSERT INTO admin_accounts (admin_code,full_name,email,username,password_hash,role,status) VALUES (?,?,?,?,?,?,\'Active\')');
      $q->execute(['ADM-001','System Administrator','admin@ailiscraft.com','admin',password_hash('AilisAdmin123', PASSWORD_DEFAULT),'Administrator']);
      $q->execute(['OWN-001','Aili’s Craft Owner','owner@ailiscraft.com','Owner',password_hash('AilisCraft123', PASSWORD_DEFAULT),'Owner']);
      $message = 'Created the initial Administrator and Owner accounts. For safety, delete setup_accounts.php now.';
    }
  } catch (Throwable $e) { $message = 'Setup failed. Confirm the database schema and connection settings in AilisCraftUser/api/config.php. ' . (AC_DEBUG ? $e->getMessage() : ''); }
}
?><!doctype html><html lang="en"><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>AilisCraft account setup</title><body style="font:16px system-ui;max-width:640px;margin:48px auto;padding:24px"><h1>Initial AilisCraft accounts</h1><p>This page only works on localhost. It creates default accounts only if <code>admin_accounts</code> is empty.</p><?php if($message!==''): ?><p style="padding:12px;background:#f2eee7"><?=htmlspecialchars($message,ENT_QUOTES,'UTF-8')?></p><?php endif; ?><form method="post"><button type="submit" style="padding:12px 18px">Create initial accounts if table is empty</button></form><h2>Initial credentials</h2><p>Administrator: <code>admin</code> / <code>AilisAdmin123</code></p><p>Owner: <code>Owner</code> / <code>AilisCraft123</code></p><p><b>Delete this setup file after running it.</b> Change these passwords after first login.</p></body></html>
