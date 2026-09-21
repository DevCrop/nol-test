<?php

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "cli only\n");
    exit(1);
}

$root = dirname(__DIR__);
require_once $root . '/config/env.php';
require_once $root . '/src/Database/DB.php';
require_once $root . '/inc/lib/db.php';
require_once $root . '/nol-gate/Model/AccountModel.php';
require_once $root . '/nol-gate/lib/AuthSession.php';

$host = (string) env('DB_HOST', 'db');
$port = (int) env('DB_PORT', 3306);
if (!is_file('/.dockerenv') && ($host === 'db' || $host === 'mysql')) {
    $host = '127.0.0.1';
    $port = (int) env('DB_HOST_PORT', 3318);
}
define('DB_HOST', $host);
define('DB_PORT', $port);
define('DB_NAME', (string) env('DB_NAME', 'dbusrdaehakro0605'));
define('DB_USER', (string) env('DB_USER', 'user'));
define('DB_PASS', (string) env('DB_PASS', 'password'));
define('DB_CHARSET', (string) env('DB_CHARSET', 'utf8mb4'));
$GLOBALS['NO_SITE_UNIQUE_KEY'] = 'QATEST';

$fail = 0;
$pass = 0;
function expect($ok, string $name): void
{
    global $fail, $pass;
    if ($ok) {
        $pass++;
        echo "PASS  {$name}\n";
        return;
    }
    $fail++;
    echo "FAIL  {$name}\n";
}

$db = DB::getInstance();
$uid = 'qatest_exp' . date('His');
$db->exec('DELETE FROM nb_admin WHERE uid = ' . $db->quote($uid));
$ins = $db->prepare(
    'INSERT INTO nb_admin (uid, upwd, uname, email, phone, active_status, role_id, sitekey, created_at)
     VALUES (:uid, :upwd, :uname, :email, :phone, :st, 2, :site, NOW())'
);
$ins->execute([
    'uid' => $uid,
    'upwd' => password_hash('Abcd1234!', PASSWORD_DEFAULT),
    'uname' => '큐에이',
    'email' => 'qatest.exp@gmail.com',
    'phone' => '010-7000-0011',
    'st' => 'Y',
    'site' => 'QATEST',
]);
$id = (int) $db->lastInsertId();

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

AuthSession::establish(['no' => $id, 'uid' => $uid, 'uname' => '큐에이', 'role_id' => 2], 'manager');
$_SESSION['mfa_pending'] = ['uid' => $uid];
$token = (string) $_SESSION['no_adm_login_token'];
expect($token !== '' && AccountModel::getLoginToken($id) === $token, 'token set');

AuthSession::expire('로그인이 필요합니다.');
expect(empty($_SESSION['no_adm_login_uid']), 'admin uid cleared');
expect(empty($_SESSION['no_adm_login_token']), 'session token cleared');
expect(empty($_SESSION['mfa_pending']), 'mfa leftover cleared');
expect(($_SESSION['admin_flash_error'] ?? '') === '로그인이 필요합니다.', 'flash kept');
expect(AccountModel::getLoginToken($id) === '', 'db token cleared for this session');

AuthSession::establish(['no' => $id, 'uid' => $uid, 'uname' => '큐에이', 'role_id' => 2], 'manager');
$winner = (string) $_SESSION['no_adm_login_token'];
$_SESSION['no_adm_login_token'] = 'deadbeef';
AuthSession::expire('다른 곳에서 로그인되어 종료되었습니다.');
expect(AccountModel::getLoginToken($id) === $winner, 'expire does not clear winner token');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$_SESSION['no_adm_login_no'] = $id;
$_SESSION['no_adm_login_uid'] = $uid;
$_SESSION['no_adm_login_token'] = $winner;
AuthSession::destroy();
expect(empty($_SESSION['no_adm_login_uid']), 'destroy clears uid');
expect(AccountModel::getLoginToken($id) === '', 'destroy clears matching db token');

$_SERVER['SCRIPT_NAME'] = '/nol-gate/Controller/AuditController.php';
expect(AuthSession::wantsJson() === true, 'controller wants json');
$_SERVER['SCRIPT_NAME'] = '/nol-gate/pages/works/process.php';
expect(AuthSession::wantsJson() === true, 'process.php wants json');
$_SERVER['SCRIPT_NAME'] = '/nol-gate/pages/account/audit.php';
expect(AuthSession::wantsJson() === false, 'html page not json');

$src = file_get_contents($root . '/nol-gate/index.php');
expect(strpos($src, 'AuthSession::cancel()') === false, 'login page no longer cancel()');
$fetch = file_get_contents($root . '/nol-gate/resource/js/core/fetcher.js');
expect(strpos($fetch, 'res.status === 401') !== false, 'fetcher sends 401 to login');
expect(strpos($fetch, 'credentials: "include"') !== false, 'credentials include');

$db->exec('DELETE FROM nb_admin WHERE uid = ' . $db->quote($uid));
echo "pass={$pass} fail={$fail}\n";
exit($fail > 0 ? 1 : 0);
