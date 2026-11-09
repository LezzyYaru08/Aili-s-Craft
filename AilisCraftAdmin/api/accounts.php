<?php
declare(strict_types=1);
require_once __DIR__ . '/config.php';
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_set_cookie_params(['httponly'=>true,'samesite'=>'Lax','secure'=>!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS']!=='off']);
    session_start();
}
function account_out(array $data, int $status=200): void {
    http_response_code($status); header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store'); echo json_encode($data, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES); exit;
}
try {
    $staff = $_SESSION['ac_staff'] ?? null;
    if (!$staff || !in_array(strtolower((string)($staff['role'] ?? '')), ['administrator','manager'], true)) {
        account_out(['ok'=>false,'error'=>'Administrator login required. Please sign in again.'],401);
    }
    $pdo=db(); $method=strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
    if ($method==='GET') {
        $admins=$pdo->query("SELECT id,admin_code,full_name,email,username,role,status,created_at FROM admin_accounts ORDER BY created_at DESC,id DESC")->fetchAll();
        $users=$pdo->query("SELECT id,full_name,email,phone,status,created_at,updated_at FROM users ORDER BY created_at DESC,id DESC")->fetchAll();
        account_out(['ok'=>true,'admins'=>$admins,'users'=>$users]);
    }
    $in=json_decode(file_get_contents('php://input') ?: '{}',true);
    if (!is_array($in)) throw new ApiError('Invalid request body.');
    if ($method==='POST') {
        $name=clean_str($in['name']??'',150); $email=strtolower(clean_str($in['email']??'',190));
        $username=clean_str($in['username']??'',60); $password=(string)($in['password']??'');
        $role=clean_str($in['role']??'Administrator',30);
        if ($name==='' || !filter_var($email,FILTER_VALIDATE_EMAIL) || $username==='' || strlen($password)<6) throw new ApiError('Enter a valid name, email, username, and password (at least 6 characters).');
        if (!in_array($role,['Owner','Administrator','Manager'],true)) throw new ApiError('Invalid account role.');
        $code=strtoupper(substr($role,0,3)).'-'.strtoupper(bin2hex(random_bytes(3)));
        $q=$pdo->prepare('INSERT INTO admin_accounts(admin_code,full_name,email,username,password_hash,role,status) VALUES(?,?,?,?,?,?,?)');
        $q->execute([$code,$name,$email,$username,password_hash($password,PASSWORD_DEFAULT),$role,'Active']);
        account_out(['ok'=>true,'id'=>(int)$pdo->lastInsertId(),'message'=>'Account created in the database.']);
    }
    if ($method==='PATCH') {
        $id=(int)($in['id']??0); $name=clean_str($in['name']??'',150); $email=strtolower(clean_str($in['email']??'',190));
        $username=clean_str($in['username']??'',60); $role=clean_str($in['role']??'',30); $status=clean_str($in['status']??'',20); $password=(string)($in['password']??'');
        if($id<1 || $name==='' || !filter_var($email,FILTER_VALIDATE_EMAIL) || $username==='' || !in_array($role,['Owner','Administrator','Manager'],true) || !in_array($status,['Active','Disabled'],true)) throw new ApiError('Invalid account details.');
        if($id===(int)$staff['id'] && $status!=='Active') throw new ApiError('You cannot disable the account currently in use.');
        if($password!=='' && strlen($password)<6) throw new ApiError('Password must be at least 6 characters.');
        $sql='UPDATE admin_accounts SET full_name=?,email=?,username=?,role=?,status=?'.($password!==''?',password_hash=?':'').' WHERE id=?';
        $args=[$name,$email,$username,$role,$status]; if($password!=='')$args[]=password_hash($password,PASSWORD_DEFAULT); $args[]=$id;
        $q=$pdo->prepare($sql);$q->execute($args);
        account_out(['ok'=>true,'message'=>'Account updated in the database.']);
    }
    if ($method==='DELETE') {
        $id=(int)($in['id']??0);
        if($id<1 || $id===(int)$staff['id']) throw new ApiError('Invalid account or cannot delete the account currently in use.');
        $q=$pdo->prepare('DELETE FROM admin_accounts WHERE id=?');$q->execute([$id]);
        account_out(['ok'=>true,'message'=>'Account deleted from the database.']);
    }
    throw new ApiError('Method not allowed.',405);
} catch(ApiError $e){account_out(['ok'=>false,'error'=>$e->getMessage()],$e->status);}
catch(PDOException $e){error_log('[AilisCraft accounts] '.$e->getMessage());$msg=AC_DEBUG?$e->getMessage():'Email or username may already be in use, or the database rejected the change.';account_out(['ok'=>false,'error'=>$msg],409);}
catch(Throwable $e){error_log('[AilisCraft accounts] '.$e);account_out(['ok'=>false,'error'=>AC_DEBUG?$e->getMessage():'Account service error.'],500);}
