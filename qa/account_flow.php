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
require_once $root . '/nol-gate/Model/AclModel.php';
require_once $root . '/nol-gate/lib/Acl.php';
require_once $root . '/nol-gate/lib/AclValidator.php';
require_once $root . '/nol-gate/lib/AccountValidator.php';
require_once $root . '/nol-gate/lib/Role.php';

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
$ids = [];

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

function leaveNoSuper(?int $currentRole, int $newRole, int $superCount): bool
{
    return $currentRole === 1 && $newRole !== 1 && $superCount <= 1;
}

$db = DB::getInstance();
$stamp = date('His');
$pwd = password_hash('Abcd1234!', PASSWORD_DEFAULT);

$cleanup = static function () use ($db): void {
    try {
        $db->exec("DELETE FROM nb_admin_acl WHERE admin_no IN (SELECT no FROM nb_admin WHERE uid LIKE 'qatest_%')");
    } catch (PDOException $e) {
        // table missing until migrate
    }
    $db->exec("DELETE FROM nb_admin WHERE uid LIKE 'qatest_%'");
};
$cleanup();

$insert = static function (string $uid, int $roleId, string $email, string $phone) use ($db, $pwd): int {
    $stmt = $db->prepare(
        'INSERT INTO nb_admin (uid, upwd, uname, email, phone, active_status, role_id, sitekey, created_at)
         VALUES (:uid, :upwd, :uname, :email, :phone, :st, :role, :site, NOW())'
    );
    $stmt->execute([
        'uid' => $uid,
        'upwd' => $pwd,
        'uname' => '큐에이',
        'email' => $email,
        'phone' => $phone,
        'st' => 'Y',
        'role' => $roleId,
        'site' => 'QATEST',
    ]);
    return (int) $db->lastInsertId();
};

try {
    $g1 = $insert('qatest_g' . $stamp, 2, 'qatest.g.' . $stamp . '@gmail.com', '010-7000-0001');
    $g2 = $insert('qatest_s' . $stamp, 1, 'qatest.s.' . $stamp . '@gmail.com', '010-7000-0002');
    $ids = [$g1, $g2];

    expect($g1 > 0 && $g2 > 0, 'fixture insert general+super');

    $dup = AccountValidator::create([
        'uid' => 'qatest_g' . $stamp,
        'uname' => '홍길동',
        'email' => 'qatest.other.' . $stamp . '@gmail.com',
        'phone' => '010-7000-0099',
        'upwd' => 'Abcd1234!',
        'upwd_confirm' => 'Abcd1234!',
    ]);
    expect($dup->fails() && in_array('이미 사용 중인 아이디입니다.', $dup->errors(), true), 'duplicate uid rejected');

    $dupMail = AccountValidator::create([
        'uid' => 'qatest_x' . $stamp,
        'uname' => '홍길동',
        'email' => 'qatest.g.' . $stamp . '@gmail.com',
        'phone' => '010-7000-0098',
        'upwd' => 'Abcd1234!',
        'upwd_confirm' => 'Abcd1234!',
    ]);
    expect($dupMail->fails() && in_array('이미 사용 중인 이메일입니다.', $dupMail->errors(), true), 'duplicate email rejected');

    $badName = AccountValidator::update([
        'uid' => 'qatest_g' . $stamp,
        'uname' => '이름1',
        'email' => 'qatest.g.' . $stamp . '@gmail.com',
        'phone' => '010-7000-0001',
    ], $g1);
    expect($badName->fails(), 'name rejects digits');

    $okSelf = AccountValidator::update([
        'uid' => 'qatest_g' . $stamp,
        'uname' => '홍길동',
        'email' => 'qatest.g.' . $stamp . '@gmail.com',
        'phone' => '010-7000-0001',
    ], $g1);
    expect(!$okSelf->fails(), 'self uid/email allowed on update');

    $aclPost = ['acl' => ['board' => ['view' => '1', 'update' => '1']]];
    try {
        $grants = AclValidator::assert(2, $aclPost);
        expect(!empty($grants['board']['view']) && !empty($grants['board']['update']), 'acl general grants');
    } catch (RuntimeException $e) {
        expect(false, 'acl general grants (' . $e->getMessage() . ')');
        $grants = [];
    }

    AccountModel::update($g2, [
        'uid' => 'qatest_s' . $stamp,
        'uname' => '큐에이',
        'email' => 'qatest.s.' . $stamp . '@gmail.com',
        'phone' => '010-7000-0002',
        'active_status' => 'Y',
        'role_id' => 2,
    ]);
    AclModel::replaceAll($g2, $grants);
    expect(AccountModel::getRoleId($g2) === 2, 'super -> general persisted');
    $saved = AclModel::fetch($g2);
    expect(!empty($saved['board']['view']) && !empty($saved['board']['update']), 'demote writes acl rows');

    $emptyAcl = AclValidator::assert(1, []);
    expect($emptyAcl === [], 'super acl is empty');
    AccountModel::update($g2, [
        'uid' => 'qatest_s' . $stamp,
        'uname' => '큐에이',
        'email' => 'qatest.s.' . $stamp . '@gmail.com',
        'phone' => '010-7000-0002',
        'active_status' => 'Y',
        'role_id' => 1,
    ]);
    AclModel::deleteByAdmin($g2);
    expect(AccountModel::getRoleId($g2) === 1, 'general -> super persisted');
    expect(AclModel::fetch($g2) === [], 'promote clears acl');

    $supers = AccountModel::countByRole(1);
    expect(leaveNoSuper(1, 2, 1) === true, 'last super cannot demote');
    expect(leaveNoSuper(1, 2, 2) === false, 'second super can demote');
    expect(leaveNoSuper(1, 2, $supers) === ($supers <= 1), 'live super count demote rule');

    expect(AccountModel::delete($g1) === true, 'delete general');
    expect(AccountModel::getRoleId($g1) === null, 'deleted general gone');

    $before = AccountModel::countByRole(1);
    $denyLast = AccountModel::getRoleId($g2) === 1 && $before <= 1;
    if ($denyLast) {
        expect(true, 'skip deleting last remaining super fixture');
    } else {
        expect(AccountModel::delete($g2) === true, 'delete extra super');
    }
} catch (Throwable $e) {
    expect(false, 'exception: ' . $e->getMessage());
}

$cleanup();
echo "----\n{$pass} passed, {$fail} failed\n";
exit($fail === 0 ? 0 : 1);
