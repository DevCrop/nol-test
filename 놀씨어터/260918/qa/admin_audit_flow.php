<?php

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "cli only\n");
    exit(1);
}

$root = dirname(__DIR__);
require_once $root . '/config/env.php';
require_once $root . '/src/Database/DB.php';
require_once $root . '/inc/lib/db.php';
require_once $root . '/nol-gate/lib/AuditLogger.php';
require_once $root . '/nol-gate/Model/AuditModel.php';

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
$has = $db->query("SHOW TABLES LIKE 'nb_admin_audit'")->fetchColumn();
expect((string) $has === 'nb_admin_audit', 'nb_admin_audit exists');
if ($has !== 'nb_admin_audit') {
    echo "php sql/migrate.php up 먼저 실행\n";
    exit(1);
}

$db->prepare('DELETE FROM nb_admin_audit WHERE sitekey = :s')->execute(['s' => 'QATEST']);
$_SESSION['no_adm_login_no'] = 9;
$_SESSION['no_adm_login_uid'] = 'qa_actor';
$_SERVER['REMOTE_ADDR'] = '203.0.113.10';

$before = AuditModel::count('QATEST');
AuditLogger::record('create', 'account', 77, 'qa_user', [
    'uname' => 'QA',
    'upwd' => 'secret',
    'password' => 'secret',
    'grants' => ['board' => ['view' => true]],
]);
$after = AuditModel::count('QATEST');
expect($after === $before + 1, 'record increments count');

$rows = AuditModel::list('QATEST', 0, 1);
$row = $rows[0] ?? [];
expect(($row['actor_uid'] ?? '') === 'qa_actor', 'actor from session');
expect(($row['actor_ip'] ?? '') === '203.0.113.10', 'ip stored');
expect(($row['action'] ?? '') === 'create', 'action create');
expect(($row['entity'] ?? '') === 'account', 'entity account');
expect(($row['target_no'] ?? 0) == 77, 'target_no');

$detailStmt = $db->query("SELECT detail_json FROM nb_admin_audit WHERE sitekey = 'QATEST' ORDER BY no DESC LIMIT 1");
$detail = (string) $detailStmt->fetchColumn();
expect(strpos($detail, 'secret') === false, 'password keys stripped');
expect(strpos($detail, 'QA') !== false, 'uname kept');

AuditLogger::record('explode', 'account', 1, 'x');
AuditLogger::record('create', 'nope', 1, 'x');
expect(AuditModel::count('QATEST') === $after, 'invalid action/entity ignored');

$threw = false;
try {
    AuditLogger::ifOk(false, 'delete', 'banner', 1, 'skip');
} catch (Throwable $e) {
    $threw = true;
}
expect(!$threw && AuditModel::count('QATEST') === $after, 'ifOk false is no-op');

AuditLogger::ifOk(true, 'update', 'board', 3, '글제목');
expect(AuditModel::count('QATEST') === $after + 1, 'ifOk true records');
expect(AuditModel::count('QATEST', 'board') === 1, 'entity filter count');

$acl = file_get_contents($root . '/nol-gate/lib/Acl.php');
expect(strpos($acl, "'Audit' => 'account'") !== false, 'Acl maps AuditController to account');

$api = file_get_contents($root . '/nol-gate/resource/js/core/apiRoutes.js');
expect(strpos($api, 'AUDIT:') !== false, 'API.AUDIT route');
$fetcher = file_get_contents($root . '/nol-gate/resource/js/core/fetcher.js');
expect(strpos($fetcher, 'credentials: "include"') !== false, 'fetcher credentials include');

$db->prepare('DELETE FROM nb_admin_audit WHERE sitekey = :s')->execute(['s' => 'QATEST']);

echo "pass={$pass} fail={$fail}\n";
exit($fail > 0 ? 1 : 0);
