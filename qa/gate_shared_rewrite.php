<?php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require_once dirname(__DIR__).'/config/env.php';
if (!is_file('/.dockerenv') || env('DB_USER') !== 'qa_app' || env('APP_ENV') !== 'development'
    || env('GATE_HOST') !== 'gate.noltheater-daehakro.com' || env('GATE_ROUTE_MODE') !== 'rewrite') {
    echo "SKIP shared gate requires isolated compose.shared-gate.yml environment\n"; exit;
}
$pass=0; $fail=0;
function sharedExpect(bool $ok, string $name): void {
    global $pass,$fail; if($ok){$pass++;echo "PASS {$name}\n";}else{$fail++;echo "FAIL {$name}\n";}
}
function sharedRequest(string $path, string $host='gate.noltheater-daehakro.com', string $method='GET'): array {
    $context=stream_context_create(['http'=>['method'=>$method,'header'=>"Host: {$host}\r\n",'ignore_errors'=>true,'follow_location'=>0,'timeout'=>10]]);
    $body=(string)file_get_contents('http://127.0.0.1'.$path,false,$context);
    preg_match('/\s(\d{3})\s/',$http_response_header[0]??'',$match);
    return [(int)($match[1]??0),$body,implode("\n",$http_response_header??[])];
}
[$status,$body,$headers]=sharedRequest('/');
sharedExpect($status===200 && strpos($body,'id="login_form"')!==false,'shared DocumentRoot serves gate login at /');
sharedExpect(stripos($headers,'Location:')===false && strpos($body,'action="./lib/login/login.process.php"')!==false,'no physical-directory redirect and root-relative login action');
foreach(['/index.php','/resource/css/style.css','/resource/js/auth-ui.js','/resource/js/login.js','/resource/vendor/boxicons/css/boxicons.min.css','/resource/images/admin/logo.png'] as $path){
    [$status,$body]=sharedRequest($path); sharedExpect($status===200 && $body!=='','gate and shared resource reachable '.$path);
}
[$status,$body]=sharedRequest('/lib/login/login.process.php','gate.noltheater-daehakro.com','POST');
sharedExpect($status===403 && strpos($body,'"success":false')!==false,'rewritten login keeps CSRF validation');
[$status]=sharedRequest('/lib/login/logout.php'); sharedExpect($status===405,'rewritten logout rejects GET');
[$status,$body,$headers]=sharedRequest('/pages/account/index.php');
sharedExpect(in_array($status,[302,401],true) && strpos($body,'no-admin-container')===false,'account page still requires authentication');
foreach(['/nol-gate/','/nol-gate/index.php','/inc/lib/base.class.php','/sql/release.php','/qa/run-all.php','/storage/test.log','/resource/vendor/tinymce/plugins/jbimages/ci/index.php'] as $path){
    [$status]=sharedRequest($path); sharedExpect(in_array($status,[403,404],true),'sensitive or physical path denied '.$path);
}
[$status]=sharedRequest('/uploads/request/qa-private.pdf'); sharedExpect($status===403,'private rental uploads remain blocked');
[$status]=sharedRequest('/uploads/qa.php'); sharedExpect($status===403,'upload PHP execution blocked');
[$status,$body]=sharedRequest('/','GATE.NOLTHEATER-DAEHAKRO.COM:80');
sharedExpect($status===200 && strpos($body,'id="login_form"')!==false,'exact host routing allows case and port');
[$status,$body]=sharedRequest('/','gate.noltheater-daehakro.com.evil.example');
sharedExpect(strpos($body,'id="login_form"')===false,'lookalike host does not receive gate login');
[$status]=sharedRequest('/nol-gate/index.php','localhost'); sharedExpect($status===404,'public host still hides admin physical path');
foreach(['localhost','gate.noltheater-daehakro.com'] as $host) {
    foreach(['/.env','/.env.production.upload','/.env.production.example'] as $path) {
        [$status]=sharedRequest($path,$host); sharedExpect($status===404,'environment file denied '.$host.' '.$path);
    }
}
echo "OK {$pass}; FAIL {$fail}\n"; exit($fail?1:0);
