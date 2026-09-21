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
require_once $root . '/nol-gate/lib/PasswordExpiry.php';
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

$now = new DateTimeImmutable('2026-09-18 12:00:00');
$clock = static function () use ($now): DateTimeImmutable {
    return $now;
};
$expiry = new PasswordExpiry(90, $clock);
$gate = new PasswordChangeGate($expiry);

$row = static function (array $over = []): array {
    return array_merge([
        'must_change_password' => 0,
        'password_changed_at' => '2026-09-18 12:00:00',
        'created_at' => '2025-01-01 00:00:00',
    ], $over);
};

expect($expiry->decide($row()) === PasswordExpiry::ALLOW, 'fresh password allow');
expect(
    $expiry->decide($row(['password_changed_at' => '2026-06-20 12:00:01'])) === PasswordExpiry::ALLOW,
    '89d 23h59 allow'
);
expect(
    $expiry->decide($row(['password_changed_at' => '2026-06-20 12:00:00'])) === PasswordExpiry::DUE,
    'exactly 90d due'
);
expect(
    $expiry->decide($row(['password_changed_at' => '', 'created_at' => '2026-01-01 00:00:00'])) === PasswordExpiry::DUE,
    'empty changed_at uses created_at'
);
expect((new PasswordExpiry(0, $clock))->decide($row(['password_changed_at' => '2020-01-01 00:00:00'])) === PasswordExpiry::ALLOW, 'days=0 disables');

$ev = $gate->evaluate($row(['password_changed_at' => '2026-01-01 00:00:00']), false);
expect($ev['force'] === true && $ev['reason'] === PasswordChangeGate::REASON_EXPIRED, 'expired forces routing');
$evOk = $gate->evaluate($row(['password_changed_at' => '2026-01-01 00:00:00']), true);
expect($evOk['force'] === false && $evOk['reason'] === PasswordChangeGate::REASON_EXPIRED, 'pwd page allowed while expired');
$evReset = $gate->evaluate($row([
    'must_change_password' => 1,
    'password_changed_at' => '2026-01-01 00:00:00',
]), false);
expect($evReset['reason'] === PasswordChangeGate::REASON_RESET, 'reset reason wins over expiry');
expect($gate->message(PasswordChangeGate::REASON_EXPIRED) === PasswordChangeGate::MSG_EXPIRED, 'expired message');

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
$insert = static function (string $uid, string $changed) use ($db, &$uids): int {
    $uids[] = $uid;
    $ins = $db->prepare(
        'INSERT INTO nb_admin (uid, upwd, uname, email, phone, active_status, role_id, sitekey, created_at, last_login_at, password_changed_at)
         VALUES (:uid, :upwd, :uname, :email, :phone, :st, 2, :site, :created, :last, :changed)'
    );
    $ins->execute([
        'uid' => $uid,
        'upwd' => password_hash('Abcd1234!', PASSWORD_DEFAULT),
        'uname' => '큐에이',
        'email' => $uid . '@gmail.com',
        'phone' => '010-7333-' . str_pad((string) count($uids), 4, '0', STR_PAD_LEFT),
        'st' => 'Y',
        'site' => 'QATEST',
        'created' => '2025-01-01 00:00:00',
        'last' => '2026-09-18 12:00:00',
        'changed' => $changed,
    ]);
    return (int) $db->lastInsertId();
};

try {
    $fresh = $insert('qatest_exp_f' . $stamp, '2026-09-18 12:00:00');
    $old = $insert('qatest_exp_o' . $stamp, '2026-01-01 00:00:00');
    $_SESSION = ['no_adm_login_no' => $fresh];
    expect($gate->landingAfterLogin() === '/pages/board/board.list.php', 'fresh lands board');
    expect(empty($_SESSION[PasswordChangeGate::SESSION_REASON]), 'fresh session has no force reason');

    $_SESSION['no_adm_login_no'] = $old;
    expect($gate->landingAfterLogin() === '/pages/setting/pwd.php', 'expired lands pwd');
    expect(($_SESSION[PasswordChangeGate::SESSION_REASON] ?? '') === PasswordChangeGate::REASON_EXPIRED, 'session reason expired');
    expect($gate->evaluate(AccountModel::findByNo($old), false)['force'] === true, 'forced session true');

    $before = AccountModel::passwordGateRow($old);
    $gate->complete($old);
    $after = AccountModel::passwordGateRow($old);
    expect((int) ($after['must_change_password'] ?? 1) === 0, 'complete clears reset flag');
    expect((string) $after['password_changed_at'] === '2026-09-18 12:00:00', 'complete stamps changed_at to now');
    expect((string) ($before['password_changed_at'] ?? '') !== (string) $after['password_changed_at'], 'stamp actually moved');
    expect(empty($_SESSION[PasswordChangeGate::SESSION_REASON]), 'complete clears session reason');
    expect($gate->landingAfterLogin() === '/pages/board/board.list.php', 'after complete board');

    $actor = $insert('qatest_exp_a' . $stamp, '2026-09-18 12:00:00');
    $target = $insert('qatest_exp_t' . $stamp, '2026-09-18 12:00:00');
    $gate->markResetByAdmin($target, $actor);
    $resetRow = AccountModel::passwordGateRow($target);
    expect((int) $resetRow['must_change_password'] === 1, 'admin reset still sets 29 flag');
    expect((string) $resetRow['password_changed_at'] === '2026-09-18 12:00:00', 'admin reset does not stamp 90-day clock');
} catch (Throwable $e) {
    expect(false, 'exception: ' . $e->getMessage());
}

$cleanup();
echo "----\n{$pass} passed, {$fail} failed\n";
exit($fail === 0 ? 0 : 1);
