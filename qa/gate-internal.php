<?php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
if (!is_file('/.dockerenv') || getenv('DB_USER')!=='qa_app' || getenv('GATE_ROUTE_MODE')!=='internal') {
    echo "SKIP internal gate requires isolated internal environment\n"; exit;
}
$pass=0; $fail=0; $host=getenv('GATE_HOST'); $dir='nol-gate';
function internalExpect($ok,$label) { global $pass,$fail; if($ok){$pass++;echo "PASS {$label}\n";}else{$fail++;echo "FAIL {$label}\n";} }
function internalRequest($path,$host,$method='GET') {
    $ctx=stream_context_create(['http'=>['method'=>$method,'header'=>"Host: {$host}\r\n",'ignore_errors'=>true,'follow_location'=>0,'timeout'=>10]]);
    $body=(string)file_get_contents('http://127.0.0.1'.$path,false,$ctx);
    preg_match('/\s(\d{3})\s/',$http_response_header[0]??'',$m);
    return [(int)($m[1]??0),$body,implode("\n",$http_response_header??[])];
}
foreach(['/', '/index.php'] as $path){
    [$s,$body,$h]=internalRequest($path,$host);
    internalExpect($s===200 && strpos($body,'id="login_form"')!==false,'login served internally '.$path);
    internalExpect(stripos($h,'Location:')===false,'no external directory redirect '.$path);
    internalExpect(!preg_match('#(?:href|src|action)="/'.$dir.'/#',$body),'login contains no physical directory URL '.$path);
}
[$s,$body]=internalRequest('/resource/css/style.css',$host);
internalExpect($s===200 && hash('sha256',$body)===hash_file('sha256',dirname(__DIR__).'/'.$dir.'/resource/css/style.css'),'virtual CSS is actual admin CSS, not public CSS');
foreach(['/resource/js/login.js','/resource/vendor/boxicons/css/boxicons.min.css','/resource/images/admin/logo.png'] as $path){
    [$s,$body]=internalRequest($path,$host);internalExpect($s===200 && $body!=='','asset reachable '.$path);
}
[$s,$body,$h]=internalRequest('/mfa.php',$host);
internalExpect($s===302 && preg_match('#Location: (?:\./)?/?index\.php(?:\r|\n|$)#i',$h)===1,'MFA redirects to valid login URL without physical directory');
[$s]=internalRequest('/lib/login/login.process.php',$host,'POST');
internalExpect($s===403,'virtual login keeps CSRF guard');
[$s]=internalRequest('/lib/login/logout.php',$host);
internalExpect($s===405,'virtual logout rejects GET');
[$s,$body,$h]=internalRequest('/pages/account/index.php',$host);
internalExpect(in_array($s,[302,401],true) && strpos($h,'/'.$dir.'/')===false,'anonymous account access requires auth with clean URL');
foreach(['/'.$dir.'/','/'.$dir.'/index.php','/.env','/.env.production.upload','/sql/release.php','/qa/run-all.php','/does-not-exist.php'] as $path){
    [$s]=internalRequest($path,$host);internalExpect(in_array($s,[403,404],true),'invalid/private path blocked '.$path);
}
[$s]=internalRequest('/'.$dir.'/index.php','localhost');
internalExpect($s===404,'public host hides admin');
[$s,$body]=internalRequest('/','localhost');
internalExpect($s===200 && strpos($body,'id="login_form"')===false,'public site unaffected');
[$s,$body]=internalRequest('/',strtoupper($host).':80');
internalExpect($s===200 && strpos($body,'id="login_form"')!==false,'host case and port supported');
[$s,$body]=internalRequest('/',$host.'.evil.example');
internalExpect(strpos($body,'id="login_form"')===false,'host spoof does not serve admin');
echo "{$pass} PASS / {$fail} FAIL\n";exit($fail?1:0);
