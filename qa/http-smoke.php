<?php
require __DIR__ . '/bootstrap.php';
function request_status(string $host, string $path): int {
    $fp = fsockopen('127.0.0.1', 80, $errno, $errstr, 5); if (!$fp) return 0;
    fwrite($fp, "GET {$path} HTTP/1.1\r\nHost: {$host}\r\nConnection: close\r\n\r\n"); $line = fgets($fp); fclose($fp);
    return preg_match('/HTTP\/\d\.\d\s+(\d+)/', (string) $line, $m) ? (int) $m[1] : 0;
}
function get_response(string $host, string $path): array {
    $fp = fsockopen('127.0.0.1', 80, $errno, $errstr, 5); if (!$fp) return [0, ''];
    fwrite($fp, "GET {$path} HTTP/1.1\r\nHost: {$host}\r\nConnection: close\r\n\r\n"); $raw = stream_get_contents($fp); fclose($fp);
    preg_match('/HTTP\/\d\.\d\s+(\d+)/', $raw, $m); return [(int) ($m[1] ?? 0), preg_replace('/^.*?\r\n\r\n/s', '', $raw)];
}
function post_status(string $host, string $path, string $body = ''): int {
    $fp = fsockopen('127.0.0.1', 80, $errno, $errstr, 5); if (!$fp) return 0;
    fwrite($fp, "POST {$path} HTTP/1.1\r\nHost: {$host}\r\nContent-Type: application/x-www-form-urlencoded\r\nContent-Length: " . strlen($body) . "\r\nConnection: close\r\n\r\n{$body}");
    $line = fgets($fp); fclose($fp); return preg_match('/HTTP\/\d\.\d\s+(\d+)/', (string) $line, $m) ? (int) $m[1] : 0;
}
qa_expect(request_status('localhost', '/admin/index.php') === 404, 'public host hides admin');
qa_expect(request_status('gate.local', '/') === 302, 'gate root redirects to admin');
qa_expect(request_status('gate.local', '/pages/about.php') === 404, 'gate host hides public pages');
qa_expect(request_status('gate.local', '/admin/index.php') === 200, 'gate login reachable');
qa_expect(request_status('localhost', '/260918.sql') === 403, 'SQL dump blocked');
qa_expect(request_status('localhost', '/index.test.php') === 404, 'test page absent');
qa_expect(request_status('localhost', '/qa/run-all.php') === 404 && request_status('localhost', '/sql/migrate.php') === 404, 'QA and migration controllers are not web reachable');
qa_expect(post_status('gate.local', '/admin/pages/works/ajax/works.process.php') === 403, 'admin POST without CSRF is forbidden');
qa_expect(post_status('localhost', '/module/ajax/request.process.php') === 403, 'public POST without CSRF is forbidden');
qa_expect(request_status('gate.local', '/admin/lib/login/logout.php') === 405, 'logout rejects GET');
[$validApi, $validBody] = get_response('localhost', '/app/api.php?board_no=1');
qa_expect($validApi === 200 && strpos($validBody, '"rows"') !== false, 'public API accepts a valid bound integer');
[$sqliBoard, $sqliBoardBody] = get_response('localhost', '/app/api.php?board_no=1%20OR%201=1');
qa_expect($sqliBoard === 400 && stripos($sqliBoardBody, 'sql') === false, 'board number SQL injection rejected without SQL disclosure');
[$sqliCategory, $sqliCategoryBody] = get_response('localhost', '/app/api.php?board_no=1&category_no=1%29%20OR%201=1--');
qa_expect($sqliCategory === 400 && stripos($sqliCategoryBody, 'syntax') === false, 'category SQL injection rejected');
$traversal = request_status('localhost', '/pages/board/board.file.download.php?id=1&fld=../../260918.sql');
qa_expect(in_array($traversal, [400,403,404], true), 'download traversal does not return a file');
qa_done();
