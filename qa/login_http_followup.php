<?php
if(PHP_SAPI!=='cli'||!is_file('/.dockerenv'))exit(1);
ob_start();require dirname(__DIR__).'/config/env.php';require dirname(__DIR__).'/inc/lib/session.boot.php';
if(env('APP_ENV')!=='development')exit(1);
$db=new PDO('mysql:host='.env('DB_HOST').';dbname='.env('DB_NAME').';charset=utf8mb4',env('DB_USER'),env('DB_PASS'),[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
$uid='qa_login_'.bin2hex(random_bytes(5));$no=0;$sessions=[];$files=[];$pass=0;$fail=0;
function loginCheck($ok,$label){global $pass,$fail;$ok?$pass++:$fail++;echo ($ok?'PASS ':'FAIL ').$label."\n";}
function loginRequest($uid,$pwd,$captcha): string {
    global $sessions;
    if(session_status()===PHP_SESSION_ACTIVE)session_write_close();
    session_id('');session_start();$_SESSION=['_csrf'=>$csrf=bin2hex(random_bytes(32))];
    if($captcha!==null)$_SESSION['captcha_secure']=$captcha;
    $sid=session_id();$sessions[]=$sid;session_write_close();
    $file=(session_save_path()?:sys_get_temp_dir()).'/sess_'.$sid;if(posix_geteuid()===0)chown($file,'www-data');
    $ctx=stream_context_create(['http'=>['method'=>'POST','header'=>"Host: gate.local\r\nCookie: ".session_name()."=$sid\r\nContent-Type: application/x-www-form-urlencoded\r\n",'content'=>http_build_query(['_csrf'=>$csrf,'uid'=>$uid,'upwd'=>$pwd,'r_captcha'=>$captcha??'']),'ignore_errors'=>true,'follow_location'=>0,'timeout'=>10]]);
    return (string)file_get_contents('http://127.0.0.1/lib/login/login.process.php',false,$ctx);
}
try{
    $db->prepare("INSERT INTO nb_admin(sitekey,uid,upwd,uname,email,phone,active_status,role_id,password_changed_at,last_login_at,created_at) VALUES('NOLTHE',?,?,?,?,'','Y',2,NOW(),NOW(),NOW())")->execute([$uid,password_hash('Qa!234567890',PASSWORD_DEFAULT),'검증',$uid.'@example.com']);$no=(int)$db->lastInsertId();
    $base=sys_get_temp_dir().'/nol_admin_login_attempts/'.hash('sha256','NOLTHE|account:'.$no);$files=[$base.'.json',$base.'.json.lock'];
    loginCheck(strpos(loginRequest($uid,'Qa!234567890',null),'보안코드가 일치하지 않습니다')!==false,'missing CAPTCHA rejected even with correct password');
    loginCheck(!is_file($base.'.json.lock'),'missing CAPTCHA cannot allocate account lock files');
    for($i=1;$i<=5;$i++)loginCheck(strpos(loginRequest($uid,'wrong-password','qa123'),json_encode('아이디 또는 비밀번호가 일치하지 않습니다.'))!==false,'fresh-session wrong password attempt '.$i);
    loginCheck(strpos(loginRequest(strtoupper($uid),'Qa!234567890','qa123'),'Too many login attempts')!==false,'correct password and UID alias blocked after fifth failure');
    $state=json_decode(file_get_contents($base.'.json'),true);
    loginCheck(($state['blocked_until']??0)>time() && ($state['blocked_until']-time())<=900,'15-minute account lock persisted');
    $state['blocked_until']=time()-1;file_put_contents($base.'.json',json_encode($state));
    $body=loginRequest($uid,'Qa!234567890','qa123');
    loginCheck(strpos($body,'Too many login attempts')===false && strpos($body,'보안코드가 일치하지 않습니다')===false && !is_file($base.'.json'),'expired lock permits correct credentials and resets budget');
}catch(Throwable $e){loginCheck(false,$e->getMessage());}
finally{
    if($no)$db->prepare('DELETE FROM nb_admin WHERE no=? AND uid=?')->execute([$no,$uid]);
    foreach($sessions as $sid){$file=(session_save_path()?:sys_get_temp_dir()).'/sess_'.$sid;if(is_file($file))unlink($file);}
    foreach($files as $file)if(is_file($file))unlink($file);
}
echo "pass=$pass fail=$fail\n";exit($fail?1:0);
