<?php
// Actual HTTP checks against shared DocumentRoot with AllowOverride None.
if (PHP_SAPI !== 'cli' || !is_file('/.dockerenv') || getenv('DB_USER') !== 'qa_app'
    || (getenv('APP_ENV') !== 'development' && !(getenv('APP_ENV') === 'production' && getenv('QA_PRODUCTION_HTTP') === '1' && getenv('GATE_ALLOW_HTTP') === 'true'))
    || getenv('GATE_ROUTE_MODE') !== 'php') {
    echo "SKIP PHP-router QA requires isolated development php mode\n"; exit;
}
ob_start();
$root = dirname(__DIR__);
$blue = is_dir($root . '/admin');
$site = $blue ? 'BLUESQ' : 'NOLTHE';
$name = (string) getenv('SESSION_NAME');
session_name($name);
$db = new PDO('mysql:host=db;dbname=' . getenv('DB_NAME') . ';charset=utf8mb4', getenv('DB_USER'), getenv('DB_PASS'), [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
$pass=0; $fail=0; $admin=0; $sessions=[];
$uid='qa_router_' . bin2hex(random_bytes(5)); $password='Qa!' . bin2hex(random_bytes(8));
function routerCheck($ok, string $label): void { global $pass,$fail; $ok?$pass++:$fail++; echo ($ok?'PASS ':'FAIL ') . $label . "\n"; }
function routerHttp(string $path, string &$sid, string $method='GET', array $data=[], string $host='gate.local', bool $ajax=false): array {
    global $name,$sessions;
    if ($host === 'gate.local') $host = getenv('GATE_HOST') ?: $host;
    $headers="Host: $host\r\nConnection: close\r\n";
    if($sid!=='')$headers.="Cookie: $name=$sid\r\n";
    if($ajax)$headers.="X-Requested-With: XMLHttpRequest\r\n";
    if($method==='POST')$headers.="Content-Type: application/x-www-form-urlencoded\r\n";
    $ctx=stream_context_create(['http'=>['method'=>$method,'header'=>$headers,'content'=>$method==='POST'?http_build_query($data):'','ignore_errors'=>true,'follow_location'=>0,'timeout'=>15]]);
    $body=(string)file_get_contents('http://127.0.0.1'.$path,false,$ctx);
    $response=$http_response_header;
    preg_match('/\s(\d{3})\s/',$response[0]??'',$m);
    $location='';
    foreach($response as $line) {
        if(preg_match('/^Set-Cookie: '.preg_quote($name,'/').'=([^;]*)/i',$line,$cookie) && $cookie[1]!==''){$sid=$cookie[1];$sessions[]=$sid;}
        if(stripos($line,'Location:')===0)$location=trim(substr($line,9));
    }
    return [(int)($m[1]??0),$body,$location,$response];
}
function routerSession(string $sid, array $changes): array {
    if(session_status()===PHP_SESSION_ACTIVE)session_write_close();
    session_id($sid);session_start();
    foreach($changes as $key=>$value)$_SESSION[$key]=$value;
    $result=$_SESSION;session_write_close();
    if(posix_geteuid()===0)chown((session_save_path()?:sys_get_temp_dir()).'/sess_'.$sid,'www-data');
    return $result;
}
function routerLogin(string &$sid): array {
    global $uid,$password;
    [$status,$html]=routerHttp('/',$sid);
    preg_match('/name="_csrf"\s+value="([^"]+)"/',$html,$m);
    routerSession($sid,['captcha_secure'=>'12345']);
    $login = routerHttp('/index.php/lib/login/login.process.php',$sid,'POST',['uid'=>$uid,'upwd'=>$password,'r_captcha'=>'12345','_csrf'=>$m[1]??'']);
    if (getenv('MFA_ENABLED') !== 'true') return $login;
    routerCheck($login[0] === 302 && strpos($login[2], '/index.php/mfa.php') === 0, 'credential login still requires MFA on production HTTP');
    $state = routerSession($sid, []);
    routerCheck(empty($state['no_adm_login_no']) && !empty($state['mfa_pending']), 'password alone grants no authenticated session');
    // Isolated QA session only: simulate mail delivery without sending any customer mail.
    $pending = $state['mfa_pending'] ?? [];
    $pending['code_hash'] = password_hash('123456', PASSWORD_DEFAULT); $pending['sent_at'] = time();
    routerSession($sid, ['mfa_pending' => $pending]);
    $token = $state[$GLOBALS['blue'] ? 'blue_csrf' : '_csrf'] ?? '';
    return routerHttp('/index.php/lib/login/mfa.process.php',$sid,'POST',[$GLOBALS['blue'] ? 'code' : 'mfa_code'=>'123456','_csrf'=>$token]);
}
try {
    $role=$blue?'role_code':'role_id';
    $db->prepare("INSERT INTO nb_admin(sitekey,uid,upwd,uname,email,active_status,$role,password_changed_at,last_login_at,created_at) VALUES(?,?,?,?,?,'Y',?,NOW(),NOW(),NOW())")
       ->execute([$site,$uid,password_hash($password,PASSWORD_DEFAULT),'QA',$uid.'@example.com',$blue?'super':1]);
    $admin=(int)$db->lastInsertId();
    $sid='';[$status,$html,$loc,$headers]=routerHttp('/',$sid);
    routerCheck($status===200 && strpos($html,'id="login_form"')!==false && $loc==='','gate root serves login without directory redirect');
    routerCheck(strpos($html,'<base href="/index.php/">')!==false,'root relative form and assets use PHP front controller');
    routerCheck(strpos(implode("\n",$headers),'HttpOnly')!==false && strpos(implode("\n",$headers),'SameSite=Lax')!==false,'HTTP session cookie preserves HttpOnly and SameSite');
    routerCheck(!preg_match('/Set-Cookie:[^\n]*;\s*secure/i',implode("\n",$headers)), 'temporary HTTP cookie is usable without weakening HTTPS cookie policy');
    preg_match_all('/(?:src|href)="([^"]+)"/',$html,$assets);
    $tested=0;
    foreach($assets[1] as $asset) {
        if(!preg_match('/\.(?:css|js|png|php)(?:\?|$)/',$asset) || strpos($asset,'http')===0)continue;
        if(strpos($asset,'./')===0)$asset='/index.php/'.substr($asset,2);
        if($asset==='/index.php/')continue;
        [$assetStatus,$bytes]=routerHttp(html_entity_decode($asset),$sid);
        routerCheck($assetStatus===200 && $bytes!=='','rendered login asset '.$asset);
        if(++$tested>=10)break;
    }
    routerCheck($tested>=4,'actual rendered asset URLs exercised');
    [$mfaStatus,$mfaBody,$mfaLocation]=routerHttp('/index.php/mfa.php',$sid);
    routerCheck($mfaStatus===302 && strpos($mfaLocation,'/index.php')===0,'MFA without challenge returns through PHP router');
    [$headStatus,$headBody]=routerHttp('/index.php/resource/js/login.js',$sid,'HEAD');
    routerCheck($headStatus===200 && $headBody==='','static HEAD has no response body');
    [$assetPostStatus]=routerHttp('/index.php/resource/js/login.js',$sid,'POST');
    routerCheck($assetPostStatus===405,'static assets reject POST');
    [$status]=routerHttp('/index.php/lib/login/login.process.php',$sid,'POST');
    routerCheck($status===403,'login POST remains CSRF protected');
    $before=$sid;[$status,$body,$location]=routerLogin($sid);
    routerCheck($status===302 && strpos($location,'/index.php/pages/')===0,'real credential POST redirects through PHP router');
    routerCheck($sid!==$before && $sid!=='','successful login rotates session ID');
    [$status,$page]=routerHttp($location,$sid);
    routerCheck($status===200 && strpos($page,'data-session-countdown')!==false,'authenticated landing and session countdown render');
    routerCheck(strpos($page,'/index.php/pages/account/')!==false,'administrator navigation uses front controller');
    $livePage=preg_replace('/<!--.*?-->/s','',$page);
    preg_match_all('/(?:src|href)="([^"]+)"/',$livePage,$pageAssets);
    foreach($pageAssets[1] as $asset){
        if(strpos($asset,'/index.php/resource/')!==0 || !preg_match('/\.(?:js|css)(?:\?|$)/',$asset))continue;
        [$assetStatus]=routerHttp(html_entity_decode($asset),$sid);
        routerCheck($assetStatus===200,'authenticated asset '.$asset);
    }
    $csrf=routerSession($sid,[])[$blue?'blue_csrf':'_csrf']??'';
    $ping=$blue?'/index.php/lib/session/ping.php':'/index.php/ajax/session.activity.php';
    routerSession($sid,['no_adm_last_activity'=>time()-120]);
    [$status,$body]=routerHttp($ping,$sid,'GET',[],'gate.local',true);
    $json=json_decode($body,true);$remaining=$json[$blue?'expiresIn':'remaining']??0;
    routerCheck($status===200 && $remaining<=1680 && $remaining>1650,'session polling does not reset idle countdown');
    [$status,$body]=routerHttp($ping,$sid,'POST',['_csrf'=>$csrf],'gate.local',true);
    $json=json_decode($body,true);
    routerCheck($status===200 && ($json[$blue?'expiresIn':'remaining']??0)>=1798,'CSRF-authorized activity renews server session');
    [$status]=routerHttp($ping,$sid,'POST',[],'gate.local',true);
    routerCheck($status===403,'activity without CSRF is denied');
    $old=$sid;$fresh='';routerLogin($fresh);
    [$status]=routerHttp($ping,$old,'GET',[],'gate.local',true);
    routerCheck($status===401,'new login invalidates previous session through PHP router');
    [$status]=routerHttp($ping,$fresh,'GET',[],'gate.local',true);
    routerCheck($status===200,'invalidated old session does not terminate new session');
    routerSession($fresh,['no_adm_last_activity'=>time()-1800]);
    [$status]=routerHttp($ping,$fresh,'GET',[],'gate.local',true);
    routerCheck($status===401,'30-minute idle expiry enforced');
    $logout='';[$unusedStatus,$unusedBody,$logoutLanding]=routerLogin($logout);
    routerHttp($logoutLanding,$logout);
    $csrf=routerSession($logout,[])[$blue?'blue_csrf':'_csrf']??'';
    [$status]=routerHttp('/index.php/lib/login/logout.php',$logout,'POST',['_csrf'=>$csrf]);
    routerCheck(in_array($status,[200,302],true),'CSRF-protected logout succeeds');
    [$status]=routerHttp($ping,$logout,'GET',[],'gate.local',true);
    routerCheck($status===401,'logout session cannot access protected endpoint');
    $anon='';[$status]=routerHttp('/index.php/pages/account/index.php',$anon,'GET',[],'gate.local',true);
    routerCheck($status===401,'anonymous account access remains denied');
    foreach(['/index.php/.env','/index.php/../.env','/index.php/%2e%2e/.env','/index.php/%5c..%5c.env','/index.php//pages/account/index.php','/index.php/inc/lib/PhpGateRouter.php','/index.php/uploads/request/secret.txt','/index.php/uploads/board/file_secret.jpg','/index.php/resource/vendor/tinymce/plugins/jbimages/dialog.htm','/index.php/resource/vendor/fullPage.js-2.9.7/examples/backgrounds.html'] as $path){
        [$status]=routerHttp($path,$anon);
        routerCheck(in_array($status,[400,403,404],true),'private/traversal route denied '.$path);
    }
    [$status]=routerHttp('/'.($blue?'admin':'nol-gate').'/index.php',$anon);
    routerCheck($status===404,'physical administrator entry hidden on gate');
    [$status]=routerHttp('/index.php/pages/account/index.php',$anon,'GET',[],'localhost');
    routerCheck($status===404 || (getenv('APP_ENV')==='production' && in_array($status,[301,302],true)),'public host cannot invoke gate front controller');
    [$status,$body]=routerHttp('/',$anon,'GET',[],'localhost');
    routerCheck(getenv('APP_ENV')==='production' ? in_array($status,[301,302],true) : ($status===200 && strpos($body,'id="login_form"')===false),'public homepage transport policy preserved');
} catch(Throwable $e) { routerCheck(false,$e->getMessage()); }
finally {
    if($admin) {
        $db->prepare('DELETE FROM nb_admin_privacy_access WHERE actor_no=?')->execute([$admin]);
        $db->prepare('DELETE FROM nb_admin_audit WHERE actor_no=?')->execute([$admin]);
        $db->prepare('DELETE FROM nb_admin WHERE no=? AND uid=?')->execute([$admin,$uid]);
        if(!$blue){$attempt=sys_get_temp_dir().'/nol_admin_login_attempts/'.hash('sha256',$site.'|account:'.$admin).'.json.lock';if(is_file($attempt))unlink($attempt);}
    }
    foreach(array_unique($sessions) as $id){$file=(session_save_path()?:sys_get_temp_dir()).'/sess_'.$id;if(is_file($file))unlink($file);}
}
echo "$pass PASS / $fail FAIL\n";exit($fail?1:0);
