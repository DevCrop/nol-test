<?php
// Run in a dedicated local web container with MFA_ENABLED=true; no SMTP is used.
ob_start();require __DIR__.'/bootstrap.php';require dirname(__DIR__).'/inc/lib/db.php';
if(!is_file('/.dockerenv')||APP_ENV!=='development'||DB_USER!=='qa_app'||!MFA_ENABLED)exit(1);
$db=DB::getInstance();$id=0;$uid='qa_continue_'.bin2hex(random_bytes(4));$sessions=[];$jar=['sid'=>'','csrf'=>''];
function transitionHttp(array &$jar,string $path,?array $data=null):array {
    $headers='Host: '.GATE_HOST."\r\nCookie: ".session_name().'='.$jar['sid']."\r\nX-Requested-With: XMLHttpRequest\r\n";
    if($data!==null){$data['_csrf']=$jar['csrf'];$headers.="Content-Type: application/x-www-form-urlencoded\r\n";}
    $ctx=stream_context_create(['http'=>['method'=>$data===null?'GET':'POST','header'=>$headers,'content'=>$data===null?'':http_build_query($data),'ignore_errors'=>true,'follow_location'=>0,'timeout'=>15]]);
    $body=(string)file_get_contents('http://127.0.0.1/index.php'.$path,false,$ctx);preg_match('/\s(\d{3})\s/',$http_response_header[0]??'',$m);$location='';
    foreach($http_response_header as $line){if(preg_match('/^Set-Cookie: '.preg_quote(session_name(),'/').'=([^;]+)/i',$line,$cookie)){$jar['sid']=$cookie[1];$GLOBALS['sessions'][]=$cookie[1];}if(stripos($line,'Location:')===0)$location=trim(substr($line,9));}
    return [(int)($m[1]??0),json_decode($body,true),$body,$location,implode("\n",$http_response_header)];
}
function transitionFixture(array $session):array {
    if(session_status()===PHP_SESSION_ACTIVE)session_write_close();session_id('');session_start();$_SESSION=$session;
    $jar=['sid'=>session_id(),'csrf'=>\Security\Csrf::token()];$GLOBALS['sessions'][]=$jar['sid'];session_write_close();if(posix_geteuid()===0)chown((session_save_path()?:sys_get_temp_dir()).'/sess_'.$jar['sid'],'www-data');return $jar;
}
function transitionChallenge(array $row):array {return ['admin_no'=>(int)$row['no'],'email'=>$row['email'],'credential_version'=>hash('sha256',$row['upwd']),'authority_version'=>\AccountAccessPolicy::version($row),'code_hash'=>password_hash('123456',PASSWORD_DEFAULT),'expires'=>time()+300,'attempts'=>0,'sent_at'=>time()];}
function transitionReject(callable $fn):bool {try{$fn();return false;}catch(RuntimeException $e){return true;}}
try{
    $permissions=\Security\AdminAcl::normalize(['board'=>['view'=>true]]);
    $db->prepare("INSERT INTO nb_admin(sitekey,uid,upwd,uname,email,active_status,role_code,password_changed_at,password_must_change,temporary_password_expires_at,menu_permissions)VALUES('BLUESQ',?,?,?,?,'Y','admin',NOW(),1,DATE_ADD(NOW(),INTERVAL 1 DAY),?)")
       ->execute([$uid,password_hash('InitialTemp9!',PASSWORD_DEFAULT),'QA',$uid.'@example.com',json_encode($permissions)]);$id=(int)$db->lastInsertId();$row=\Security\AdminAccount::findByNo($id);$oldChallenge=transitionChallenge($row);
    $jar=transitionFixture(['mfa_pending'=>$oldChallenge]);$pendingJar=transitionFixture(['mfa_pending'=>$oldChallenge]);
    $r=transitionHttp($pendingJar,'/pages/board/board.list.php');qa_expect($r[0]===401,'MFA pending cannot read admin data');
    $r=transitionHttp($jar,'/lib/login/mfa.process.php',['code'=>'123456']);qa_expect($r[0]===302&&strpos($r[3],'/index.php/pages/')===0,'real HTTP MFA verifies and establishes limited session');
    if($r[0]!==302||strpos($r[3],'/index.php/pages/')!==0)throw new RuntimeException('MFA fixture failed: '.$r[0].' '.$r[3]);
    $loginJar=$jar;$loginSid=$jar['sid'];$before=\Security\AdminAccount::findByNo($id);$oldToken=$before['login_token'];
    $r=transitionHttp($jar,'/pages/board/board.list.php');qa_expect($r[0]===403&&strpos($r[1]['message']??'','비밀번호')!==false,'MFA alone still requires temporary password change');
    $r=transitionHttp($jar,'/pages/account/password.php');preg_match('/name="_csrf" value="([^"]+)"/',$r[2],$csrf);$jar['csrf']=$csrf[1]??'';$loginJar['csrf']=$jar['csrf'];
    qa_expect($r[0]===200&&strpos($r[2],'변경 후 추가 인증 없이')!==false,'temporary-password UI explains direct continuation');
    $fault='qa_transition_'.bin2hex(random_bytes(4));$beforeFault=\Security\AdminAccount::findByNo($id);
    try{
        $db->exec('ALTER TABLE nb_admin_audit ADD CONSTRAINT '.$fault.' CHECK (NOT (entity=\'account\' AND target_no='.$id.' AND action=\'update\'))');
        $failed=transitionHttp($jar,'/pages/account/ajax/password.process.php',['new_password'=>'NewPassword9!','new_password_confirm'=>'NewPassword9!']);
        qa_expect(($failed[1]['result']??'')==='fail'&&\Security\AdminAccount::findByNo($id)===$beforeFault&&$jar['sid']===$loginSid,'mandatory audit failure rolls back password/token without continuing session');
    }finally{$db->exec('ALTER TABLE nb_admin_audit DROP CHECK '.$fault);}
    $r=transitionHttp($jar,'/pages/account/ajax/password.process.php',['new_password'=>'NewPassword9!','new_password_confirm'=>'NewPassword9!']);$redirect=$r[1]['redirect']??'';
    qa_expect($r[0]===200&&($r[1]['result']??'')==='success'&&$redirect==='/index.php/pages/board/board.list.php','forced password change returns authorized admin landing, not login/MFA');
    $after=\Security\AdminAccount::findByNo($id);
    qa_expect(password_verify('NewPassword9!',$after['upwd'])&&!password_verify('InitialTemp9!',$after['upwd'])&&empty($after['password_must_change'])&&$after['temporary_password_expires_at']===null,'temporary credential replaced and temporary state cleared');
    qa_expect($jar['sid']!==$loginSid&&$jar['sid']!==''&&$after['login_token']!==$oldToken&&!empty($after['login_token']),'session cookie and active DB token both rotate');
    qa_expect(stripos($r[4],'no-store')!==false&&strpos($r[2],$after['login_token'])===false,'transition response is no-store and does not expose session token');
    $r=transitionHttp($jar,substr($redirect,10));qa_expect($r[0]===200&&strpos($r[2],'required-password-form')===false,'same browser cookie reaches admin after one MFA');
    preg_match('/name="csrf-token" content="([^"]+)"/',$r[2],$csrf);$newCsrf=$csrf[1]??'';qa_expect(strlen($newCsrf)===64&&$newCsrf!==$loginJar['csrf'],'CSRF rotates with credential transition');
    $jar['csrf']=$newCsrf;$r=transitionHttp($jar,'/lib/session/ping.php',[]);qa_expect($r[0]===200,'continued session can renew idle timer without another MFA');
    $staleCsrf=$jar;$staleCsrf['csrf']=$loginJar['csrf'];$r=transitionHttp($staleCsrf,'/lib/session/ping.php',[]);qa_expect($r[0]===403,'previous CSRF cannot mutate continued session');
    $r=transitionHttp($loginJar,'/lib/session/ping.php');qa_expect($r[0]===401,'previous session cookie cannot be reused');
    $r=transitionHttp($jar,'/pages/board/ajax/board.process.php',['mode'=>'save']);qa_expect($r[0]===403,'continued session preserves view-only CRUD restrictions');
    $r=transitionHttp($jar,'/pages/account/index.php');qa_expect($r[0]===403,'continued ordinary account cannot manage other accounts');
    $replay=transitionFixture(['mfa_pending'=>$oldChallenge]);$r=transitionHttp($replay,'/lib/login/mfa.process.php',['code'=>'123456']);qa_expect($r[0]===302&&strpos($r[3],'/mfa.php')!==false,'challenge from previous password cannot establish new authentication');
    $r=transitionHttp($replay,'/pages/board/board.list.php');qa_expect($r[0]===401,'replayed old MFA challenge grants no admin access');
    $r=transitionHttp($jar,'/lib/login/logout.php',[]);qa_expect($r[0]===302&&empty(\Security\AdminAccount::findByNo($id)['login_token']),'logout still revokes continued token');
    $fresh=transitionFixture(['captcha_secure'=>'1234']);$r=transitionHttp($fresh,'/lib/login/login.process.php',['uid'=>$uid,'upwd'=>'NewPassword9!','r_captcha'=>'1234']);qa_expect($r[0]===302&&strpos($r[3],'/mfa.php')!==false,'fresh login after logout still requires MFA when enabled');
    $r=transitionHttp($fresh,'/pages/board/board.list.php');qa_expect($r[0]===401,'fresh password login cannot skip MFA');
    // A later authenticated ordinary password change retains the previous logout policy.
    $row=\Security\AdminAccount::findByNo($id);$normal=transitionFixture(['mfa_pending'=>transitionChallenge($row)]);transitionHttp($normal,'/lib/login/mfa.process.php',['code'=>'123456']);
    $r=transitionHttp($normal,'/pages/account/ajax/password.process.php',['current_password'=>'NewPassword9!','new_password'=>'SecondPassword9!','new_password_confirm'=>'SecondPassword9!']);qa_expect(($r[1]['redirect']??'')==='/index.php'&&empty(\Security\AdminAccount::findByNo($id)['login_token']),'ordinary password change still logs out');
    // A concurrent token change must never be overwritten by the continuation helper.
    $service=new \Security\TemporaryAccountService();$service->reissue($id,0);
    if(session_status()===PHP_SESSION_ACTIVE)session_write_close();session_id('');session_start();$_SESSION=[];\Security\AdminAccount::establish(\Security\AdminAccount::findByNo($id));$sessions[]=session_id();
    $continuation=$service->change($id,$_SESSION['no_adm_login_token'],'','ThirdPassword9!','ThirdPassword9!');$competing=bin2hex(random_bytes(32));$db->prepare('UPDATE nb_admin SET login_token=? WHERE no=?')->execute([$competing,$id]);
    qa_expect(transitionReject(function()use($id,$continuation){\Security\AuthSession::continueAfterPasswordChange($id,$continuation);})&&\Security\AdminAccount::findByNo($id)['login_token']===$competing,'concurrent login/reissue prevents stale continuation without overwriting token');
    $_SESSION=[];qa_expect(transitionReject(function()use($id,$competing){\Security\AuthSession::continueAfterPasswordChange($id,$competing);}), 'continuation cannot bootstrap an unauthenticated session');
}catch(Throwable $e){qa_expect(false,$e->getMessage());}
finally{if(session_status()===PHP_SESSION_ACTIVE)session_write_close();if($id){$db->prepare('DELETE FROM nb_admin_audit WHERE actor_no=? OR (entity=? AND target_no=?)')->execute([$id,'account',$id]);$db->prepare('DELETE FROM nb_admin_privacy_access WHERE actor_no=?')->execute([$id]);$db->prepare('DELETE FROM nb_admin WHERE no=? AND uid=?')->execute([$id,$uid]);}foreach(array_unique($sessions)as $sid){$file=(session_save_path()?:sys_get_temp_dir()).'/sess_'.$sid;if(is_file($file))unlink($file);}}
qa_done();
