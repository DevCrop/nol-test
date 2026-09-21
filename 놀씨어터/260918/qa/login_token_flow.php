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

function exclusiveOk(string $mine, string $db): bool
{
    if ($db === '') {
        return true;
    }
    return $mine !== '' && hash_equals($db, $mine);
}

$db = DB::getInstance();
$stamp = date('His');
$uid = 'qatest_tok' . $stamp;
$cleanup = static function () use ($db, $uid): void {
    $db->exec("DELETE FROM nb_admin WHERE uid = " . $db->quote($uid));
};
$cleanup();

$ins = $db->prepare(
    'INSERT INTO nb_admin (uid, upwd, uname, email, phone, active_status, role_id, sitekey, created_at)
     VALUES (:uid, :upwd, :uname, :email, :phone, :st, 2, :site, NOW())'
);
$ins->execute([
    'uid' => $uid,
    'upwd' => password_hash('Abcd1234!', PASSWORD_DEFAULT),
    'uname' => '큐에이',
    'email' => 'qatest.tok.' . $stamp . '@gmail.com',
    'phone' => '010-7000-0010',
    'st' => 'Y',
    'site' => 'QATEST',
]);
$id = (int) $db->lastInsertId();

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

try {
    expect($id > 0, 'fixture account');
    expect(AccountModel::getLoginToken($id) === '', 'empty token before login');
    expect(exclusiveOk('', '') === true, 'no token yet allows old session');

    AuthSession::establish(['no' => $id, 'uid' => $uid, 'uname' => '큐에이', 'role_id' => 2], 'manager');
    $t1 = (string) ($_SESSION['no_adm_login_token'] ?? '');
    expect(strlen($t1) === 64, 'token length 64 hex');
    expect(AccountModel::getLoginToken($id) === $t1, 'db matches first session');
    expect(exclusiveOk($t1, $t1) === true, 'first device valid');

    AuthSession::establish(['no' => $id, 'uid' => $uid, 'uname' => '큐에이', 'role_id' => 2], 'manager');
    $t2 = (string) ($_SESSION['no_adm_login_token'] ?? '');
    expect($t2 !== $t1, 'second login new token');
    expect(AccountModel::getLoginToken($id) === $t2, 'db is later login');
    expect(exclusiveOk($t1, $t2) === false, 'first device kicked');
    expect(exclusiveOk($t2, $t2) === true, 'second device valid');
    expect(exclusiveOk('', $t2) === false, 'missing session token kicked');

    AccountModel::clearLoginTokenIfMatch($id, $t1);
    expect(AccountModel::getLoginToken($id) === $t2, 'logout of old token does not clear winner');
    AccountModel::clearLoginTokenIfMatch($id, $t2);
    expect(AccountModel::getLoginToken($id) === '', 'logout of current token clears');

    $originList = defined('CORS_ORIGINS') ? (string) CORS_ORIGINS : '';
    expect(strpos($originList, 'gate.local') !== false || $originList === '', 'cors origins env present or empty');
    expect(!isset($_SESSION['login_token_header']), 'token not exposed as cors header');
} catch (Throwable $e) {
    expect(false, 'exception: ' . $e->getMessage());
}

$cleanup();
echo "----\n{$pass} passed, {$fail} failed\n";
exit($fail === 0 ? 0 : 1);
