<?php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
if (!is_file('/.dockerenv') || getenv('DB_USER') !== 'qa_app' || getenv('GATE_ROUTE_MODE') !== 'path') {
    echo "SKIP PHP gate routing requires compose.php-gate.yml\n"; exit;
}
$host = getenv('GATE_HOST');
$dir = 'admin';
$pass = 0; $fail = 0;
function routeCheck($ok, $label) {
    global $pass, $fail;
    if ($ok) { $pass++; echo "PASS {$label}\n"; } else { $fail++; echo "FAIL {$label}\n"; }
}
function routeRequest($path, $host, $method = 'GET') {
    $ctx = stream_context_create(['http' => ['method'=>$method, 'header'=>"Host: {$host}\r\n", 'ignore_errors'=>true, 'follow_location'=>0, 'timeout'=>15]]);
    $body = file_get_contents('http://127.0.0.1'.$path, false, $ctx);
    $headers = implode("\n", $http_response_header ?? []);
    preg_match('/\s(\d{3})\s/', $http_response_header[0] ?? '', $m);
    return [(int)($m[1] ?? 0), (string)$body, $headers];
}
foreach (['/', '/index.php'] as $path) {
    [$s,$body,$h] = routeRequest($path,$host);
    routeCheck($s===302 && strpos($h,'Location: /'.$dir.'/')!==false, 'PHP root redirects within gate '.$path);
}
[$s] = routeRequest('/',$host,'POST');
routeCheck($s===405,'root POST is not redirected');
[$s,$body] = routeRequest('/'.$dir.'/',$host);
routeCheck($s===200 && stripos($body,'password')!==false,'admin login served from shared root');
foreach (['/'.$dir.'/resource/css/style.css', '/'.$dir.'/resource/js/login.js', '/resource/vendor/boxicons/css/boxicons.min.css', '/resource/images/admin/logo.png'] as $asset) {
    [$s,$body] = routeRequest($asset,$host);
    routeCheck($s===200 && $body!=='','login asset reachable '.$asset);
}
[$s,$body,$h] = routeRequest('/'.$dir.'/mfa.php',$host);
routeCheck($s===302 && stripos($h,'index.php')!==false,'MFA without challenge returns to login');
[$s] = routeRequest('/'.$dir.'/lib/login/logout.php',$host);
routeCheck($s===405,'logout rejects GET');
[$s] = routeRequest('/'.$dir.'/index.php','localhost');
routeCheck($s===404,'public host hides admin');
[$s,$body,$h] = routeRequest('/'.$dir.'/pages/account/index.php',$host);
routeCheck(in_array($s,[302,401],true) && strpos($body,'no-admin-container')===false,'account requires authentication');
[$s] = routeRequest('/'.$dir.'/lib/login/login.process.php',$host,'POST');
routeCheck($s===403,'login POST requires CSRF');
foreach (['/.env','/.env.production.upload','/sql/release.php','/qa/run-all.php'] as $path) {
    [$s] = routeRequest($path,$host); routeCheck(in_array($s,[403,404],true),'private file blocked '.$path);
}
[$s,$body] = routeRequest('/', 'localhost');
routeCheck($s===200 && stripos($body,'id="login_form"')===false,'public homepage preserved');
[$s,$body,$h] = routeRequest('/',strtoupper($host).':80');
routeCheck($s===302 && strpos($h,'Location: /'.$dir.'/')!==false,'host case and port normalized');
[$s,$body,$h] = routeRequest('/',$host.'.evil.example');
routeCheck(strpos($h,'Location: /'.$dir.'/')===false,'lookalike host is not gate');
echo "{$pass} PASS / {$fail} FAIL\n"; exit($fail ? 1 : 0);
