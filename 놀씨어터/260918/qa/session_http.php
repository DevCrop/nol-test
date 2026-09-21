<?php
if(PHP_SAPI!=='cli') {http_response_code(404);exit;}
ob_start();
require dirname(__DIR__).'/config/env.php';
require dirname(__DIR__).'/inc/lib/session.boot.php';
if(env('APP_ENV')!=='development'||!is_file('/.dockerenv')) exit(1);
$pdo=new PDO('mysql:host='.env('DB_HOST').';dbname='.env('DB_NAME').';charset=utf8mb4',env('DB_USER'),env('DB_PASS'),[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
$uid='qa_idle_'.bin2hex(random_bytes(4));$no=0;$sessions=[];$pass=0;$fail=0;
function expectHttp($ok,$name) {global $pass,$fail;if($ok){$pass++;echo "PASS {$name}\n";}else{$fail++;echo "FAIL {$name}\n";}}
function requestIdle($sid,$method='GET') {
    $ctx=stream_context_create(['http'=>['method'=>$method,'header'=>"Host: gate.local\r\nCookie: ".session_name()."=".$sid."\r\nX-Requested-With: XMLHttpRequest\r\n",'ignore_errors'=>true,'follow_location'=>0,'timeout'=>10]]);
    $body=file_get_contents('http://127.0.0.1/ajax/session.activity.php',false,$ctx);
    preg_match('/\s(\d{3})\s/',$http_response_header[0]??'',$m);
    return [(int)($m[1]??0),json_decode($body,true)];
}
try {
    $token=bin2hex(random_bytes(32));
    $pdo->prepare("INSERT INTO nb_admin(sitekey,uid,upwd,uname,email,active_status,role_id,password_changed_at,last_login_at,created_at,login_token) VALUES('NOLTHE',?,?,?,?,'Y',1,NOW(),NOW(),NOW(),?)")->execute([$uid,password_hash(bin2hex(random_bytes(24)),PASSWORD_DEFAULT),'세션검사',$uid.'@example.com',$token]);
    $no=(int)$pdo->lastInsertId();
    $_SESSION=['no_adm_login_no'=>$no,'no_adm_login_uid'=>$uid,'no_adm_login_uname'=>'세션검사','no_adm_login_role_id'=>1,'no_adm_login_role'=>'super','no_adm_login_token'=>$token,'no_adm_last_activity'=>time()-120];
    $sid=session_id();$sessions[]=$sid;session_write_close();
    $file=(session_save_path()?:sys_get_temp_dir()).'/sess_'.$sid;if(function_exists('posix_geteuid')&&posix_geteuid()===0)chown($file,'www-data');
    [$status,$data]=requestIdle($sid);
    expectHttp($status===200 && ($data['remaining']??0)<=1680 && ($data['remaining']??0)>1660,'status polling does not extend NOL session');
    [$status,$data]=requestIdle($sid,'POST');
    expectHttp($status===200 && ($data['remaining']??0)>=1798,'activity extends NOL server deadline');
    session_id($sid);session_start();$_SESSION['no_adm_last_activity']=time()-1800;session_write_close();
    [$status]=requestIdle($sid);
    expectHttp($status===401,'NOL idle expiry returns JSON 401');
    $params=session_get_cookie_params();
    expectHttp($params['httponly'] && $params['domain']==='' && $params['lifetime']===0 && ini_get('session.use_strict_mode')==='1','host-only browser-session HttpOnly strict cookies');
} catch(Throwable $e) {expectHttp(false,$e->getMessage());}
finally {
    if($no)$pdo->prepare('DELETE FROM nb_admin WHERE no=? AND uid=?')->execute([$no,$uid]);
    foreach($sessions as $sid){$file=(session_save_path()?:sys_get_temp_dir()).'/sess_'.$sid;if(is_file($file))unlink($file);}
}
echo "pass={$pass} fail={$fail}\n";exit($fail?1:0);
