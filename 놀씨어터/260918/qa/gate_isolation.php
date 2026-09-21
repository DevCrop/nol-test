<?php

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "cli only\n");
    exit(1);
}

$root = dirname(__DIR__);
require_once $root . '/inc/lib/Gate.php';
require_once $root . '/inc/lib/GateAllowlist.php';
require_once $root . '/inc/lib/Cors.php';
require_once $root . '/inc/lib/ClientFault.php';
require_once $root . '/config/env.php';

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

expect(Gate::normalizeHost('Gate.Local:8618') === 'gate.local', 'strip port and case');
expect(Gate::hostMatches('gate.local', 'GATE.LOCAL:8618'), 'host match ignores port');
expect(!Gate::hostMatches('gate.local', 'localhost:8618'), 'public host is not GATE');
expect(!Gate::hostMatches('', 'gate.local'), 'empty expected never matches');

expect(Gate::adminPathHit('nol-gate', '/nol-gate/index.php', '/nol-gate/'), 'admin dir exact');
expect(Gate::adminPathHit('nol-gate', '/index.php', '/nol-gate?x=1'), 'admin dir query');
expect(!Gate::adminPathHit('nol-gate', '/index.php', '/nol-gateway'), 'no prefix false positive');
expect(!Gate::adminPathHit('nol-gate', '/index.php', '/uploads/banners/a.jpg'), 'uploads not admin');
expect(!Gate::adminPathHit('', '/nol-gate/index.php', '/nol-gate/'), 'empty dir never hits');

expect(!Gate::shouldHide(false, 'gate.local', 'localhost', 'nol-gate', '/nol-gate/index.php', '/nol-gate/'), 'enforce off');
expect(!Gate::shouldHide(true, '', 'localhost', 'nol-gate', '/nol-gate/index.php', '/nol-gate/'), 'empty GATE_HOST');
expect(!Gate::shouldHide(true, 'gate.local', 'gate.local:8618', 'nol-gate', '/index.php', '/'), 'GATE host itself');
expect(Gate::shouldHide(true, 'gate.local', 'localhost:8618', 'nol-gate', '/nol-gate/index.php', '/nol-gate/'), 'public hides /nol-gate');
expect(!Gate::shouldHide(true, 'gate.local', 'localhost:8618', 'nol-gate', '/index.php', '/'), 'public home stays');

expect(SESSION_DOMAIN === '', 'SESSION_DOMAIN empty host-only');
expect(SESSION_NAME === 'NOLGATESESSID', 'session name');
expect(SESSION_SAMESITE === 'Lax', 'samesite Lax');
expect(strpos((string) CORS_ORIGINS, '*') === false, 'CORS has no star');
expect(!Cors::allows('*'), 'Cors rejects star origin');
expect(!Cors::allows('https://evil.example'), 'Cors rejects unknown origin');
expect(!Cors::allows(''), 'Cors empty origin denied');

$_SERVER['HTTP_X_FORWARDED_FOR'] = '203.0.113.9';
$_SERVER['REMOTE_ADDR'] = '10.0.0.8';
expect(GateAllowlist::clientIp() === '10.0.0.8', 'IP uses REMOTE_ADDR not XFF');
expect(!GateAllowlist::allows('10.0.0.8', ['203.0.113.9']), 'spoofed XFF cannot satisfy allowlist');
expect(GateAllowlist::allows('10.0.0.8', []), 'empty rules still allow');

$apache = file_get_contents($root . '/apache.conf');
expect(strpos($apache, 'DocumentRoot /var/www/html/nol-gate') !== false, 'GATE vhost root is nol-gate');
expect(strpos($apache, 'Alias /uploads') !== false, 'GATE aliases uploads only');
expect(strpos($apache, 'Alias /inc') === false, 'GATE does not alias /inc');

$ht = file_get_contents($root . '/.htaccess');
expect(strpos($ht, 'RewriteRule ^nol-gate') !== false, 'public htaccess 404 /nol-gate');
expect(strpos($ht, 'sql|qa|storage') !== false, 'public blocks sql/qa/storage');

$setup = file_get_contents($root . '/config/setup.php');
expect(strpos($setup, 'gabia.io') === false, 'setup.php has no hosted DB fallback');
expect(strpos($setup, 'DB_PASS') !== false && !preg_match('/define\(\'DB_PASS\',\s*\'[^\']+\'\)/', $setup), 'setup DB_PASS from env only');

$bomHits = [];
$it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root . '/nol-gate/pages'));
foreach ($it as $file) {
    if (!$file->isFile() || strtolower($file->getExtension()) !== 'php') {
        continue;
    }
    $fh = fopen($file->getPathname(), 'rb');
    $sig = $fh ? fread($fh, 3) : '';
    if ($fh) {
        fclose($fh);
    }
    if ($sig === "\xEF\xBB\xBF") {
        $bomHits[] = $file->getFilename();
    }
}
expect($bomHits === [], 'admin pages have no UTF-8 BOM');

$login = file_get_contents($root . '/nol-gate/lib/login/login.process.php');
expect(strpos($login, 'password_verify') !== false, 'login uses password_verify');
expect(!preg_match('/define\(\s*[\'\"]DB_/', $login), 'login.process has no DB constants');

$fetcher = file_get_contents($root . '/nol-gate/resource/js/core/fetcher.js');
expect(strpos($fetcher, 'credentials: "include"') !== false || strpos($fetcher, "credentials: 'include'") !== false, 'fetcher credentials include');

$pdo = new PDOException('SQLSTATE[HY000] [1045] Access denied');
expect(ClientFault::message($pdo) === '처리할 수 없습니다.', 'PDO leak stripped');
expect(ClientFault::message(new RuntimeException('권한이 없습니다.')) === '권한이 없습니다.', 'user Runtime kept');

$inDocker = is_file('/.dockerenv');
if ($inDocker) {
    $http = static function (string $host, string $path, array $extra = []): array {
        $headers = "Host: {$host}\r\n";
        foreach ($extra as $h) {
            $headers .= $h . "\r\n";
        }
        $ctx = stream_context_create([
            'http' => [
                'method' => 'GET',
                'header' => $headers,
                'ignore_errors' => true,
                'timeout' => 5,
                'follow_location' => 0,
            ],
        ]);
        $body = @file_get_contents('http://127.0.0.1' . $path, false, $ctx);
        $code = 0;
        $joined = '';
        if (isset($http_response_header) && is_array($http_response_header)) {
            $joined = implode("\n", $http_response_header);
            if (preg_match('/\s(\d{3})\s/', $http_response_header[0], $m)) {
                $code = (int) $m[1];
            }
        }
        return [$code, (string) $body, $joined];
    };

    [$pubGate] = $http('localhost', '/nol-gate/');
    expect(in_array($pubGate, [404, 403], true), 'HTTP public /nol-gate hidden (' . $pubGate . ')');

    [$gateHome, $gateBody] = $http('gate.local', '/');
    expect($gateHome > 0 && $gateHome < 500, 'HTTP GATE / reachable (' . $gateHome . ')');
    expect(strpos($gateBody, 'Not Found') === false || $gateHome !== 404, 'HTTP GATE / is not hide-404');

    [$pubHome] = $http('localhost', '/');
    expect($pubHome > 0 && $pubHome !== 404, 'HTTP public / lives');

    foreach (['/qa/account_flow.php', '/sql/migrate.php', '/config/env.php', '/.env', '/260918.sql'] as $blocked) {
        [$code] = $http('localhost', $blocked);
        expect(in_array($code, [404, 403], true), 'HTTP public blocks ' . $blocked . ' (' . $code . ')');
    }

    [$optCode, , $optHead] = $http('gate.local', '/', [
        'Origin: https://evil.example',
        'Access-Control-Request-Method: POST',
    ]);
    expect(strpos($optHead, 'Access-Control-Allow-Origin: https://evil.example') === false, 'HTTP CORS no evil origin');
} else {
    echo "SKIP  HTTP checks (not in docker)\n";
}

echo $fail === 0 ? "OK {$pass}\n" : "FAIL {$fail} / pass {$pass}\n";
exit($fail === 0 ? 0 : 1);
