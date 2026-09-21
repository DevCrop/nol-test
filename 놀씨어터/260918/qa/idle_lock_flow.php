<?php
ob_start();

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "cli only\n");
    exit(1);
}

$root = dirname(__DIR__);
require_once $root . '/config/env.php';
require_once $root . '/src/Database/DB.php';
require_once $root . '/inc/lib/db.php';
require_once $root . '/nol-gate/Model/AccountModel.php';
require_once $root . '/nol-gate/lib/AccountIdleLock.php';
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

$now = new DateTimeImmutable('2026-09-18 12:00:00');
$clock = static function () use ($now): DateTimeImmutable {
    return $now;
};
$lock = new AccountIdleLock(90, $clock);

$row = static function (array $over = []): array {
    return array_merge([
        'no' => 10,
        'role_id' => 2,
        'last_login_at' => '2026-09-18 12:00:00',
        'created_at' => '2025-01-01 00:00:00',
        'idle_locked_at' => null,
        'active_status' => 'Y',
    ], $over);
};

expect($lock->decide($row(), 1) === AccountIdleLock::ALLOW, 'recent login allow');
expect(
    $lock->decide($row(['last_login_at' => '2026-06-20 12:00:01']), 1) === AccountIdleLock::ALLOW,
    '89d 23h59 allow'
);
expect(
    $lock->decide($row(['last_login_at' => '2026-06-20 12:00:00']), 1) === AccountIdleLock::LOCK,
    'exactly 90d lock'
);
expect(
    $lock->decide($row(['last_login_at' => '2026-01-01 00:00:00']), 1) === AccountIdleLock::LOCK,
    'old login lock'
);
expect(
    $lock->decide($row(['last_login_at' => '', 'created_at' => '2026-01-01 00:00:00']), 1) === AccountIdleLock::LOCK,
    'no last_login uses created_at'
);
expect(
    $lock->decide($row(['last_login_at' => '', 'created_at' => '']), 1) === AccountIdleLock::ALLOW,
    'missing dates do not lock'
);
expect(
    $lock->decide($row(['idle_locked_at' => '2026-09-01 00:00:00', 'last_login_at' => $now->format('Y-m-d H:i:s')]), 1) === AccountIdleLock::DENY,
    'already locked stays deny even if last_login fresh'
);
expect(
    $lock->decide($row(['role_id' => 1, 'last_login_at' => '2020-01-01 00:00:00']), 0) === AccountIdleLock::ALLOW,
    'last live super never auto-locks'
);
expect(
    $lock->decide($row([
        'role_id' => 1,
        'last_login_at' => '2020-01-01 00:00:00',
        'idle_locked_at' => '2026-01-01 00:00:00',
    ]), 0) === AccountIdleLock::CLEAR,
    'last live super previously locked is cleared'
);
expect(
    $lock->decide($row(['role_id' => 1, 'last_login_at' => '2020-01-01 00:00:00']), 1) === AccountIdleLock::LOCK,
    'super with another live super does lock'
);
$off = new AccountIdleLock(0, $clock);
expect($off->decide($row(['last_login_at' => '2020-01-01 00:00:00']), 1) === AccountIdleLock::ALLOW, 'days=0 disables');
$offNeg = new AccountIdleLock(-3, $clock);
expect($offNeg->decide($row(['last_login_at' => '2020-01-01 00:00:00']), 1) === AccountIdleLock::ALLOW, 'negative days disables');

$before = $lock->decide($row(['last_login_at' => '2020-01-01 00:00:00']), 1);
$lock->decide($row(['last_login_at' => '2020-01-01 00:00:00']), 1);
expect($before === AccountIdleLock::LOCK, 'decide has no hidden mutation');

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

$insert = static function (string $uid, int $roleId, array $times) use ($db, &$uids): int {
    $uids[] = $uid;
    $ins = $db->prepare(
        'INSERT INTO nb_admin (uid, upwd, uname, email, phone, active_status, role_id, sitekey, created_at, last_login_at, idle_locked_at)
         VALUES (:uid, :upwd, :uname, :email, :phone, :st, :role, :site, :created, :last, :idle)'
    );
    $ins->execute([
        'uid' => $uid,
        'upwd' => password_hash('Abcd1234!', PASSWORD_DEFAULT),
        'uname' => '큐에이',
        'email' => $uid . '@gmail.com',
        'phone' => '010-7111-' . str_pad((string) (count($uids) + 1), 4, '0', STR_PAD_LEFT),
        'st' => 'Y',
        'role' => $roleId,
        'site' => 'QATEST',
        'created' => $times['created'] ?? '2025-01-01 00:00:00',
        'last' => $times['last'] ?? null,
        'idle' => $times['idle'] ?? null,
    ]);
    return (int) $db->lastInsertId();
};

$read = static function (int $id) use ($db): array {
    $stmt = $db->prepare('SELECT * FROM nb_admin WHERE no = :no');
    $stmt->execute(['no' => $id]);
    return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
};

try {
    $cleanup();
    $mgr = $insert('qatest_idle_m' . $stamp, 2, [
        'last' => '2026-01-01 00:00:00',
        'created' => '2025-01-01 00:00:00',
    ]);
    $svc = new AccountIdleLock(90, $clock);
    $threw = false;
    $msg = '';
    try {
        $svc->guard($read($mgr));
    } catch (RuntimeException $e) {
        $threw = true;
        $msg = $e->getMessage();
    }
    expect($threw, 'guard locks stale manager');
    expect($msg === AccountIdleLock::MSG_LOCKED, 'guard message');
    $after = $read($mgr);
    expect(trim((string) $after['idle_locked_at']) !== '', 'idle_locked_at written');
    expect(($after['active_status'] ?? '') === 'Y', 'active_status untouched');
    expect((string) ($after['login_token'] ?? '') === '', 'login_token cleared on lock');

    $threw2 = false;
    try {
        $svc->guard($read($mgr));
    } catch (RuntimeException $e) {
        $threw2 = true;
    }
    expect($threw2, 'already locked still denied');

    $selfErr = false;
    try {
        $svc->unlock($mgr, $mgr);
    } catch (RuntimeException $e) {
        $selfErr = $e->getMessage() === AccountIdleLock::MSG_SELF;
    }
    expect($selfErr, 'cannot unlock self');
    expect(trim((string) $read($mgr)['idle_locked_at']) !== '', 'failed self unlock leaves lock');

    $actor = $insert('qatest_idle_a' . $stamp, 1, [
        'last' => '2026-09-18 12:00:00',
        'created' => '2026-09-01 00:00:00',
    ]);
    $svc->unlock($mgr, $actor);
    $unlocked = $read($mgr);
    expect(trim((string) ($unlocked['idle_locked_at'] ?? '')) === '', 'unlock clears idle_locked_at');
    expect((string) $unlocked['last_login_at'] === '2026-09-18 12:00:00', 'unlock resets last_login to now');
    $threw3 = false;
    try {
        $svc->guard($read($mgr));
    } catch (RuntimeException $e) {
        $threw3 = true;
    }
    expect(!$threw3, 'just unlocked is not immediately due');

    AccountModel::lockIdle($mgr, '2026-09-01 00:00:00');
    AccountModel::update($mgr, [
        'uid' => 'qatest_idle_m' . $stamp,
        'uname' => '큐에이',
        'email' => 'qatest_idle_m' . $stamp . '@gmail.com',
        'phone' => '010-7111-0001',
        'active_status' => 'Y',
        'role_id' => 2,
    ]);
    expect(trim((string) $read($mgr)['idle_locked_at']) !== '', 'account update does not clear idle lock');

    $s1 = $insert('qatest_idle_s1' . $stamp, 1, [
        'last' => '2020-01-01 00:00:00',
        'created' => '2020-01-01 00:00:00',
    ]);
    $s2 = $insert('qatest_idle_s2' . $stamp, 1, [
        'last' => '2026-09-18 12:00:00',
        'created' => '2026-01-01 00:00:00',
    ]);
    $others = AccountModel::countLiveSupers($s1);
    expect($others >= 1, 'another live super exists');
    $threwS1 = false;
    try {
        $svc->guard($read($s1));
    } catch (RuntimeException $e) {
        $threwS1 = true;
    }
    expect($threwS1, 'stale super locks when another live super exists');
    expect(trim((string) $read($s1)['idle_locked_at']) !== '', 's1 locked');

    $threwS2 = false;
    try {
        $svc->guard($read($s2));
    } catch (RuntimeException $e) {
        $threwS2 = true;
    }
    expect(!$threwS2, 'remaining super not locked');
    expect(trim((string) ($read($s2)['idle_locked_at'] ?? '')) === '', 's2 stays unlocked');

    AccountModel::lockIdle($s1, '2026-01-01 00:00:00');
    AccountModel::lockIdle($actor, '2026-01-01 00:00:00');
    AccountModel::lockIdle($s2, '2026-01-01 00:00:00');
    $threwS2b = false;
    try {
        $svc->guard($read($s2));
    } catch (RuntimeException $e) {
        $threwS2b = true;
    }
    expect(!$threwS2b, 'last remaining super auto-unlocks on login');
    expect(trim((string) ($read($s2)['idle_locked_at'] ?? '')) === '', 'clear wrote null idle_locked_at');

    $n = $insert('qatest_idle_n' . $stamp, 2, [
        'last' => '2020-01-01 00:00:00',
        'created' => '2020-01-01 00:00:00',
    ]);
    $db->prepare("UPDATE nb_admin SET active_status = 'N' WHERE no = :no")->execute(['no' => $n]);
    expect(($read($n)['active_status'] ?? '') === 'N', 'manual inactive fixture');
    expect($svc->isLocked($read($n)) === false, 'manual inactive is not idle lock');

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $fresh = $insert('qatest_idle_f' . $stamp, 2, [
        'last' => '2026-01-01 00:00:00',
        'created' => '2026-01-01 00:00:00',
    ]);
    AuthSession::establish(['no' => $fresh, 'uid' => 'qatest_idle_f' . $stamp, 'uname' => '큐에이', 'role_id' => 2], 'manager');
    $touched = $read($fresh);
    expect(strtotime((string) $touched['last_login_at']) >= time() - 30, 'establish stamps last_login_at');
    expect(trim((string) ($touched['idle_locked_at'] ?? '')) === '', 'establish does not idle-lock');
} catch (Throwable $e) {
    expect(false, 'exception: ' . $e->getMessage());
}

$cleanup();
echo "----\n{$pass} passed, {$fail} failed\n";
exit($fail === 0 ? 0 : 1);
