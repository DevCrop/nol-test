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

$before = PrivacyAccessModel::count('QATEST');
PrivacyAccessLogger::record('view', 'inquiry', 12, '홍길동', '대관 신청 열람');
PrivacyAccessLogger::record('download', 'inquiry', 12, '홍길동', '대관 신청 첨부 다운로드', '검토');
PrivacyAccessLogger::record('hack', 'inquiry', 1, 'x', 'nope');
$after = PrivacyAccessModel::count('QATEST');
expect($after === $before + 2, 'view+download stored, invalid skipped');

$rows = PrivacyAccessModel::list('QATEST', 0, 5);
$hit = false;
foreach ($rows as $row) {
    if ((string) ($row['subject_label'] ?? '') === '홍*동' && (string) ($row['action'] ?? '') === 'download') {
        $hit = ((string) ($row['reason'] ?? '') === '검토');
        break;
    }
}
expect($hit, 'subject masked and reason kept');

$api = file_get_contents($root . '/nol-gate/resource/js/core/apiRoutes.js');
expect(strpos($api, 'PRIVACY_ACCESS:') !== false, 'API.PRIVACY_ACCESS route');

echo $fail === 0 ? "OK {$pass}\n" : "FAIL {$fail}\n";
exit($fail === 0 ? 0 : 1);
