<?php
ob_start();require __DIR__.'/bootstrap.php';require dirname(__DIR__).'/inc/lib/db.php';
if(APP_ENV!=='development'||!is_file('/.dockerenv')||DB_USER!=='qa_app')exit(1);
$db=DB::getInstance();$accounts=[];$sessions=[];$tag='qatmp_'.bin2hex(random_bytes(4));
function tempReject(callable $fn):bool {try{$fn();return false;}catch(RuntimeException $e){return true;}}
function tempSession(int $id):array {
    global $sessions;
    if(session_status()===PHP_SESSION_ACTIVE)session_write_close();session_id('');session_start();$_SESSION=[];
    \Security\AdminAccount::establish(\Security\AdminAccount::findByNo($id));
    $csrf=\Security\Csrf::token();$sid=session_id();$sessions[]=$sid;session_write_close();
    $file=(session_save_path()?:sys_get_temp_dir()).'/sess_'.$sid;if(posix_geteuid()===0)chown($file,'www-data');
    return [$sid,$csrf];
}
function tempHttp(array $session,string $path,?array $data=null):array {
    $base=blue_env('GATE_ROUTE_MODE')==='php'?'/index.php':'/admin';
    $headers='Host: '.GATE_HOST."\r\nCookie: ".session_name().'='.$session[0]."\r\n";
    if($data!==null){$data['_csrf']=$session[1];$headers.="X-Requested-With: XMLHttpRequest\r\nContent-Type: application/x-www-form-urlencoded\r\n";}
    $ctx=stream_context_create(['http'=>['method'=>$data===null?'GET':'POST','header'=>$headers,'content'=>$data===null?'':http_build_query($data),'ignore_errors'=>true,'follow_location'=>0,'timeout'=>15]]);
    $body=file_get_contents('http://127.0.0.1'.$base.$path,false,$ctx);preg_match('/\s(\d{3})\s/',$http_response_header[0]??'',$m);
    return [(int)($m[1]??0),(string)$body,json_decode((string)$body,true),implode("\n",$http_response_header)];
}
function tempChallenge(array $row):array {return ['admin_no'=>(int)$row['no'],'email'=>$row['email'],'credential_version'=>hash('sha256',$row['upwd']),'code_hash'=>password_hash('123456',PASSWORD_DEFAULT),'expires'=>time()+300,'attempts'=>0,'sent_at'=>time()];}
try {
    $superUid=$tag.'_s';
    $db->prepare("INSERT INTO nb_admin(sitekey,uid,upwd,uname,email,active_status,role_code,password_changed_at,last_login_at,created_at) VALUES('BLUESQ',?,?,?,?,'Y','super',NOW(),NOW(),NOW())")->execute([$superUid,password_hash(\Security\TemporaryPassword::generate(),PASSWORD_DEFAULT),'테스트 관리자',$superUid.'@gmail.com']);
    $super=(int)$db->lastInsertId();$accounts[$super]=$superUid;$superSession=tempSession($super);
    $input=['mode'=>'create','uid'=>$tag,'uname'=>'테스트 계정','email'=>$tag.'@gmail.com','role_code'=>'admin','active_status'=>'Y'];
    [$status,,$result,$headers]=tempHttp($superSession,'/pages/account/process.php',$input);
    qa_expect($status===200 && !empty($result['success'])&&!empty($result['credential']['temporary_password']),'HTTP create automatically issues one-time credential');
    $id=(int)($result['credential']['id']??0);if(!$id)throw new RuntimeException('fixture creation failed');$accounts[$id]=$tag;
    $plain=$result['credential']['temporary_password'];$row=\Security\AdminAccount::findByNo($id);
    qa_expect(password_verify($plain,$row['upwd'])&&$plain!==$row['upwd'],'DB stores hash not plaintext');
    qa_expect((int)$row['password_must_change']===1 && strtotime($row['temporary_password_expires_at'])>time(),'temporary flag and expiry stored');
    qa_expect(stripos($headers,'no-store')!==false,'credential response is no-store');
    [$status,$html]=tempHttp($superSession,'/pages/account/index.php');
    qa_expect($status===200 && strpos($html,'임시</span>')!==false && strpos($html,$plain)===false,'list shows real temporary state without secret');
    [$status,$html]=tempHttp($superSession,'/pages/account/new.php');
    qa_expect($status===200 && strpos($html,'data-credential-create')!==false && strpos($html,'password_confirm')===false && strpos($html,'disabled placeholder=')!==false,'create UI uses one disabled password field');
    [$status]=tempHttp([$superSession[0],'wrong'],'/pages/account/process.php',['mode'=>'reissue_temporary','no'=>$id]);
    qa_expect($status===403,'reissue requires CSRF');
    $service=new \Security\TemporaryAccountService();
    qa_expect(tempReject(function()use($service,$super){$service->reissue($super,$super);}), 'self reissue prohibited');
    $oldSession=tempSession($id);$pending=tempChallenge($row);
    [$status,,$result]=tempHttp($superSession,'/pages/account/process.php',['mode'=>'reissue_temporary','no'=>$id]);
    $newPlain=$result['credential']['temporary_password']??'';$row=\Security\AdminAccount::findByNo($id);
    qa_expect($status===200 && password_verify($newPlain,$row['upwd'])&&!password_verify($plain,$row['upwd'])&&empty($row['login_token']),'reissue revokes previous password and session token');
    [$status]=tempHttp($oldSession,'/lib/session/ping.php',[]);qa_expect($status===401,'previous authenticated session rejected');
    session_id('');session_start();$_SESSION['mfa_pending']=$pending;
    qa_expect(tempReject(function(){\Security\Mfa::verify('123456');}),'pending MFA invalidated by reissue');
    \Security\Mfa::begin($row);qa_expect(empty($_SESSION['no_adm_login_no'])&&!empty($_SESSION['mfa_pending']),'MFA pending grants no administrator session');
    // No SMTP: inject a challenge only into the isolated test session, then use real verification.
    $_SESSION['mfa_pending']=tempChallenge($row);$verified=\Security\Mfa::verify('123456');
    \Security\AdminAccount::establish($verified);$token=$_SESSION['no_adm_login_token'];
    qa_expect(empty($_SESSION['mfa_pending'])&&\Security\AuthSession::passwordDue($verified),'verified MFA retains mandatory password change');
    $sessions[]=session_id();session_write_close();$session=tempSession($id);
    [$status,$html,,$headers]=tempHttp($session,'/pages/account/password.php');
    qa_expect($status===200 && strpos($html,'required-password.js')!==false && strpos($html,'<aside')===false && strpos($html,'name="current_password"')===false,'forced change uses login shell without menus or old password input');
    qa_expect(stripos($headers,'no-store')!==false,'forced change page no-store');
    preg_match_all('/(?:src|href)="([^"]+\.(?:css|js|png)(?:\?[^"]*)?)"/',$html,$assets);$assetsOk=count($assets[1])>=4;
    foreach($assets[1] as $asset){$asset=html_entity_decode($asset);if(strpos($asset,'/index.php')===0)$asset=substr($asset,10);[$assetStatus,$assetBody]=tempHttp($session,$asset);$assetsOk=$assetsOk&&$assetStatus===200&&$assetBody!=='';}
    qa_expect($assetsOk,'rendered required-page CSS JS and image paths load');
    [$status]=tempHttp($session,'/pages/board/board.list.php');qa_expect($status===302,'temporary account redirected from admin page');
    [$status,,$result]=tempHttp($session,'/pages/account/process.php',['mode'=>'delete','no'=>$super]);qa_expect($status===403,'temporary account cannot mutate admin data');
    $row=\Security\AdminAccount::findByNo($id);$token=$row['login_token'];
    qa_expect(tempReject(function()use($service,$id){$service->change($id,'stale','','NewPassword9!','NewPassword9!');}),'stale session cannot change password');
    qa_expect(tempReject(function()use($service,$id,$token){$service->change($id,$token,'','weak','weak');}),'weak password rejected');
    qa_expect(tempReject(function()use($service,$id,$token,$newPlain){$service->change($id,$token,'',$newPlain,$newPlain);}), 'unchanged temporary password rejected');
    [$status,,$result,$headers]=tempHttp($session,'/pages/account/ajax/password.process.php',['new_password'=>'NewPassword9!','new_password_confirm'=>'NewPassword9!']);
    qa_expect($status===200 && ($result['result']??'')==='success','HTTP forced password change succeeds');
    $row=\Security\AdminAccount::findByNo($id);
    qa_expect(password_verify('NewPassword9!',$row['upwd'])&&empty($row['password_must_change'])&&$row['temporary_password_expires_at']===null&&empty($row['login_token']),'atomic hash change and temporary expiry token clearance');
    qa_expect(stripos($headers,'Set-Cookie:')!==false,'password change expires cookie');
    [$status]=tempHttp($session,'/lib/session/ping.php',[]);qa_expect(in_array($status,[401,403],true),'completed change cannot reuse old session');
    $session=tempSession($id);
    [$status,,$result]=tempHttp($session,'/pages/account/process.php',['mode'=>'reissue_temporary','no'=>$super]);qa_expect($status===403,'ordinary administrator cannot manage credentials');
    [$status,,$result]=tempHttp($session,'/pages/account/ajax/password.process.php',['current_password'=>'wrong','new_password'=>'SecondPassword9!','new_password_confirm'=>'SecondPassword9!']);qa_expect(($result['result']??'')==='fail','ordinary change requires current password');
    [$status,,$result]=tempHttp($session,'/pages/account/ajax/password.process.php',['current_password'=>'NewPassword9!','new_password'=>'SecondPassword9!','new_password_confirm'=>'SecondPassword9!']);qa_expect(($result['result']??'')==='success','ordinary change regression succeeds');
    $service->reissue($id,$super);$row=\Security\AdminAccount::findByNo($id);$hash=$row['upwd'];
    \Security\AdminAccount::updateManaged($id,['uname'=>'테스트 계정','email'=>$row['email'],'role_code'=>'admin','active_status'=>'Y'],$super);
    $row=\Security\AdminAccount::findByNo($id);qa_expect($row['upwd']===$hash && !empty($row['password_must_change']),'profile update preserves temporary credential');
    $session=tempSession($id);
    \Security\AdminAccount::updateManaged($id,['uname'=>'테스트 계정','email'=>$tag.'_changed@gmail.com','role_code'=>'admin','active_status'=>'Y'],$super);
    qa_expect(empty(\Security\AdminAccount::findByNo($id)['login_token']),'email change revokes authenticated session');
    $row=\Security\AdminAccount::findByNo($id);
    $pending=tempChallenge($row);$db->prepare('UPDATE nb_admin SET temporary_password_expires_at=DATE_SUB(NOW(),INTERVAL 1 SECOND) WHERE no=?')->execute([$id]);
    qa_expect(tempReject(function()use($id){\Security\AdminAccount::assertLoginAllowed(\Security\AdminAccount::findByNo($id));}),'expired temporary password blocked before login');
    if(session_status()!==PHP_SESSION_ACTIVE){session_id('');session_start();}$sessions[]=session_id();$_SESSION['mfa_pending']=$pending;
    qa_expect(tempReject(function(){\Security\Mfa::verify('123456');}),'expiry during MFA prevents authentication');
}catch(Throwable $e){qa_expect(false,$e->getMessage());}
finally {
    if(session_status()===PHP_SESSION_ACTIVE)session_write_close();
    foreach($accounts as $id=>$uid){$db->prepare('DELETE FROM nb_admin_audit WHERE actor_no=?')->execute([$id]);$db->prepare('DELETE FROM nb_admin_privacy_access WHERE actor_no=?')->execute([$id]);$db->prepare('DELETE FROM nb_admin WHERE no=? AND uid=?')->execute([$id,$uid]);}
    foreach($sessions as $sid){$file=(session_save_path()?:sys_get_temp_dir()).'/sess_'.$sid;if(is_file($file))unlink($file);}
}
qa_done();
