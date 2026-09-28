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
require_once $root . '/nol-gate/lib/PasswordChangeGate.php';

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
$GLOBALS['NO_ADMIN_PAGES_BASE'] = '/pages';

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

$gate = new PasswordChangeGate();
expect($gate->evaluate(['must_change_password' => 0], false)['force'] === false, 'flag off allow');
expect($gate->evaluate(['must_change_password' => 1], true)['force'] === false, 'flag on allowed surface');
expect($gate->evaluate(['must_change_password' => 1], false)['force'] === true, 'flag on other page force');
expect($gate->mustChange(['must_change_password' => 1]) === true, 'row flag 1');
expect($gate->mustChange(['must_change_password' => 0]) === false, 'row flag 0');

$db = DB::getInstance();
$stamp = date('His');
$uids = [];
$cleanup = static function () use ($db, &$uids): void {
    if ($uids === []) {
        return;
    }
    $in = implode(',', array_map(static function ($u) use ($db) {
        return $db->quote($u);
    }, $uids));
    $db->exec("DELETE FROM nb_admin WHERE uid IN ($in)");
};
$insert = static function (string $uid) use ($db, &$uids): int {
    $uids[] = $uid;
    $ins = $db->prepare(
        'INSERT INTO nb_admin (uid, upwd, uname, email, phone, active_status, role_id, sitekey, created_at, last_login_at)
         VALUES (:uid, :upwd, :uname, :email, :phone, :st, 2, :site, NOW(), NOW())'
    );
    $ins->execute([
        'uid' => $uid,
        'upwd' => password_hash('Abcd1234!', PASSWORD_DEFAULT),
        'uname' => '큐에이',
        'email' => $uid . '@gmail.com',
        'phone' => '010-7222-' . str_pad((string) count($uids), 4, '0', STR_PAD_LEFT),
        'st' => 'Y',
        'site' => 'QATEST',
    ]);
    return (int) $db->lastInsertId();
};

try {
    $actor = $insert('qatest_pw_a' . $stamp);
    $target = $insert('qatest_pw_t' . $stamp);
    expect(AccountModel::mustChangePassword($target) === false, 'new account is not forced');

    $gate->markResetByAdmin($target, $actor);
    expect(AccountModel::mustChangePassword($target) === true, 'admin reset sets flag');
    $tok = $db->prepare('SELECT login_token FROM nb_admin WHERE no = :no');
    $tok->execute(['no' => $target]);
    expect((string) $tok->fetchColumn() === '', 'reset clears login_token');

    $gate->markResetByAdmin($actor, $actor);
    expect(AccountModel::mustChangePassword($actor) === false, 'self password save is not reset');

    $_SESSION['no_adm_login_no'] = $target;
    expect($gate->landingAfterLogin() === '/pages/setting/pwd.php', 'first login lands on pwd page');
    $gate->complete($target);
    expect(AccountModel::mustChangePassword($target) === false, 'complete clears flag');
    expect($gate->landingAfterLogin() === '/pages/board/board.list.php', 'after complete lands on board');

    AccountModel::update($target, [
        'uid' => 'qatest_pw_t' . $stamp,
        'uname' => '큐에이',
        'email' => 'qatest_pw_t' . $stamp . '@gmail.com',
        'phone' => '010-7222-0002',
        'active_status' => 'Y',
        'role_id' => 2,
    ]);
    expect(AccountModel::mustChangePassword($target) === false, 'profile update without password does not set flag');
} catch (Throwable $e) {
    expect(false, 'exception: ' . $e->getMessage());
}

$cleanup();
echo "----\n{$pass} passed, {$fail} failed\n";
exit($fail === 0 ? 0 : 1);
