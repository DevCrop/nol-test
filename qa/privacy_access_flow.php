<?php

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "cli only\n");
    exit(1);
}

$root = dirname(__DIR__);
require_once $root . '/config/env.php';
require_once $root . '/src/Database/DB.php';
require_once $root . '/inc/lib/db.php';
require_once $root . '/nol-gate/lib/PrivacyAccessLogger.php';
require_once $root . '/nol-gate/Model/PrivacyAccessModel.php';
require_once $root . '/nol-gate/lib/PiiMask.php';

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
$GLOBALS['NO_SITE_UNIQUE_KEY'] = 'QAPRIVUI';

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
$has = $db->query("SHOW TABLES LIKE 'nb_admin_privacy_access'")->fetchColumn();
expect((string) $has === 'nb_admin_privacy_access', 'nb_admin_privacy_access exists');
if ($has !== 'nb_admin_privacy_access') {
    echo "php sql/migrate.php up 먼저 실행\n";
    exit(1);
}

$_SESSION = [
    'no_adm_login_no' => 9,
    'no_adm_login_uid' => 'qa_actor',
];
$_SERVER['REMOTE_ADDR'] = '203.0.113.10';

$db->beginTransaction();
$before = PrivacyAccessModel::count('QAPRIVUI');
PrivacyAccessLogger::record('view', 'inquiry', 12, '홍길동', '대관 신청 열람');
PrivacyAccessLogger::record('download', 'inquiry', 12, '홍길동', '대관 신청 첨부 다운로드', '검토');
PrivacyAccessLogger::record('hack', 'inquiry', 1, 'x', 'nope');
$after = PrivacyAccessModel::count('QAPRIVUI');
expect($after === $before + 2, 'view+download stored, invalid skipped');

$rows = PrivacyAccessModel::list('QAPRIVUI', 0, 5);
$hit = false;
foreach ($rows as $row) {
    if ((string) ($row['subject_label'] ?? '') === '홍*동' && (string) ($row['action'] ?? '') === 'download') {
        $hit = ((string) ($row['reason'] ?? '') === '검토');
        break;
    }
}
expect($hit, 'subject masked and reason kept');
expect(PrivacyAccessModel::count('QAPRIVUI', ['action' => 'view']) === 1, 'view filter');
expect(PrivacyAccessModel::count('QAPRIVUI', ['action' => 'download']) === 1, 'download filter');
expect(PrivacyAccessModel::count('QAPRIVUI', ['entity' => 'admin_account']) === 0, 'entity filter');
expect(PrivacyAccessModel::count('QAPRIVUI', ['action' => 'download', 'entity' => 'inquiry', 'keyword' => '검토']) === 1, 'combined reason search');
expect(PrivacyAccessModel::count('QAPRIVUI', ['keyword' => 'qa_actor']) === 2, 'actor search');
expect(PrivacyAccessModel::count('QAPRIVUI', ['keyword' => '203.0.113.10']) === 2, 'IP search');
expect(PrivacyAccessModel::count('QAPRIVUI', ['keyword' => '홍*동']) === 2, 'masked subject search');
expect(PrivacyAccessModel::count('QAPRIVUI', ['keyword' => '홍길동']) === 0, 'unmasked subject not searchable');
expect(PrivacyAccessModel::count('QAPRIVUI', ['keyword' => "' OR 1=1 --"]) === 0, 'SQL payload literal');
expect(PrivacyAccessModel::count('QAPRIVUI', ['keyword' => '%']) === 0, 'wildcard literal');
expect(PrivacyAccessModel::count('MISSING_SITE', ['keyword' => '검토']) === 0, 'search preserves site scope');
expect(count(PrivacyAccessModel::list('QAPRIVUI', 0, 10, ['action' => 'download'])) === 1, 'filtered list');
expect(count(PrivacyAccessModel::list('QAPRIVUI', 1, 1)) === 1, 'page offset');
$db->rollBack();
expect(PrivacyAccessModel::count('QAPRIVUI') === $before, 'QA rows rolled back');

$api = file_get_contents($root . '/nol-gate/resource/js/core/apiRoutes.js');
expect(strpos($api, 'PRIVACY_ACCESS:') !== false, 'API.PRIVACY_ACCESS route');

echo $fail === 0 ? "OK {$pass}\n" : "FAIL {$fail}\n";
exit($fail === 0 ? 0 : 1);
