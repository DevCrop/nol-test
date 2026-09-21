<?php

/**
 * php sql/migrate.php status
 * php sql/migrate.php up
 * php sql/migrate.php down              마지막 1개 롤백
 * php sql/migrate.php down {id}         해당 id까지 역순 롤백
 * php sql/migrate.php make 설명_영문
 */
if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "cli only\n");
    exit(1);
}

$root = dirname(__DIR__);
require_once $root . '/config/env.php';
if (env('APP_ENV', 'production') === 'production') { fwrite(STDERR, "Production: use php sql/release.php check|up\n"); exit(1); }
require_once $root . '/sql/Migrator.php';

$host = (string) env('DB_HOST', 'db');
$port = (int) env('DB_PORT', 3306);
$inDocker = is_file('/.dockerenv');
if (!$inDocker && ($host === 'db' || $host === 'mysql')) {
    $host = '127.0.0.1';
    $port = (int) env('DB_HOST_PORT', 3318);
}

$dsn = sprintf(
    'mysql:host=%s;port=%d;dbname=%s;charset=%s',
    $host,
    $port,
    (string) env('DB_NAME', 'dbusrdaehakro0605'),
    (string) env('DB_CHARSET', 'utf8mb4')
);

try {
    $pdo = new PDO($dsn, (string) env('DB_USER', 'user'), (string) env('DB_PASS', 'password'), [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    fwrite(STDERR, 'DB 연결 실패. docker가 켜져 있는지, 포트 ' . $port . " 을 확인하세요.\n");
    exit(1);
}

$dir = $root . '/sql/migrations';
$cmd = $argv[1] ?? 'status';
$migrator = new Migrator($pdo, $dir);

try {
    if ($cmd === 'status') {
        foreach ($migrator->status() as $row) {
            $mark = $row['applied'] ? 'APPLIED ' . $row['at'] : 'PENDING';
            echo $row['id'] . '  ' . $mark . PHP_EOL;
        }
        exit(0);
    }
    if ($cmd === 'up') {
        $ran = $migrator->up();
        echo $ran === [] ? "이미 최신입니다.\n" : ('UP ' . implode("\nUP ", $ran) . PHP_EOL);
        exit(0);
    }
    if ($cmd === 'down') {
        $ran = $migrator->down($argv[2] ?? null);
        echo $ran === [] ? "롤백할 항목이 없습니다.\n" : ('DOWN ' . implode("\nDOWN ", $ran) . PHP_EOL);
        exit(0);
    }
    if ($cmd === 'make') {
        [$up, $down] = Migrator::make($dir, (string) ($argv[2] ?? ''));
        echo $up . PHP_EOL . $down . PHP_EOL;
        exit(0);
    }
    fwrite(STDERR, "status | up | down [id] | make 이름\n");
    exit(1);
} catch (Throwable $e) {
    fwrite(STDERR, $e->getMessage() . PHP_EOL);
    exit(1);
}
