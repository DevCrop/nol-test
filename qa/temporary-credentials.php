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
    if($data!==null && !array_key_exists('change_reason',$data))$data['change_reason']='QA 계정 관리 회귀 확인';
    $base=blue_env('GATE_ROUTE_MODE')==='php'?'/index.php':'/admin';
    $headers='Host: '.GATE_HOST."\r\nCookie: ".session_name().'='.$session[0]."\r\n";
    if($data!==null){$data['_csrf']=$session[1];$headers.="X-Requested-With: XMLHttpRequest\r\nContent-Type: application/x-www-form-urlencoded\r\n";}
    $ctx=stream_context_create(['http'=>['method'=>$data===null?'GET':'POST','header'=>$headers,'content'=>$data===null?'':http_build_query($data),'ignore_errors'=>true,'follow_location'=>0,'timeout'=>15]]);
    $body=file_get_contents('http://127.0.0.1'.$base.$path,false,$ctx);preg_match('/\s(\d{3})\s/',$http_response_header[0]??'',$m);
    return [(int)($m[1]??0),(string)$body,json_decode((string)$body,true),implode("\n",$http_response_header)];
}
function tempChallenge(array $row):array {return ['admin_no'=>(int)$row['no'],'email'=>$row['email'],'credential_version'=>hash('sha256',$row['upwd']),'authority_version'=>\AccountAccessPolicy::version($row),'code_hash'=>password_hash('123456',PASSWORD_DEFAULT),'expires'=>time()+300,'attempts'=>0,'sent_at'=>time()];}
try {
    $superUid=$tag.'_s';
    $db->prepare("INSERT INTO nb_admin(sitekey,uid,upwd,uname,email,active_status,role_code,password_changed_at,last_login_at,created_at) VALUES('BLUESQ',?,?,?,?,'Y','super',NOW(),NOW(),NOW())")->execute([$superUid,password_hash(\Security\TemporaryPassword::generate(),PASSWORD_DEFAULT),'테스트 관리자',$superUid.'@gmail.com']);
    $super=(int)$db->lastInsertId();$accounts[$super]=$superUid;$superSession=tempSession($super);
    $input=['mode'=>'create','uid'=>$tag,'uname'=>'테스트 계정','email'=>$tag.'@gmail.com','role_code'=>'admin','active_status'=>'Y'];
    $superInput=$input;$superInput['uid']=$tag.'_su';$superInput['email']=$tag.'_su@gmail.com';$superInput['role_code']='super';
    [$superStatus,,$superResult]=tempHttp($superSession,'/pages/account/process.php',$superInput);
    $secondSuper=(int)($superResult['credential']['id']??0);
    if($secondSuper)$accounts[$secondSuper]=$superInput['uid'];
    qa_expect($superStatus===200 && $secondSuper>0 && (\Security\AdminAccount::findByNo($secondSuper)['role_code']??'')==='super','highest administrator creates another highest administrator via HTTP');
    $faultUid=$tag.'_f';$faultTrigger=$tag.'_constraint';$faultCreated=false;
    try{
        $db->exec("ALTER TABLE nb_admin ADD CONSTRAINT ".$faultTrigger." CHECK (uid <> ".$db->quote($faultUid)." OR password_must_change=0)");
        $faultCreated=true;
        $faultInput=$input;$faultInput['uid']=$faultUid;$faultInput['email']=$faultUid.'@gmail.com';
        $rejected=false;
        try{(new \Security\TemporaryAccountService())->create($faultInput);}catch(PDOException $e){$rejected=true;}
        $st=$db->prepare("SELECT COUNT(*) FROM nb_admin WHERE uid=? AND sitekey='BLUESQ'");$st->execute([$faultUid]);
        qa_expect($rejected && (int)$st->fetchColumn()===0,'credential metadata failure rolls back real service account insert');
    }finally{if($faultCreated)$db->exec('ALTER TABLE nb_admin DROP CHECK '.$faultTrigger);}
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
    $policyInput=$input;$policyInput['mode']='update';$policyInput['no']=$id;
    $policyInput['authority_valid_from']=date('Y-m-d\TH:i',time()-86400);$policyInput['authority_valid_until']=date('Y-m-d\TH:i',time()+86400);$policyInput['change_reason']='권한 기간 갱신 QA';
    $beforePolicy=\Security\AdminAccount::findByNo($id);
    tempHttp($superSession,'/pages/account/process.php',array_merge($policyInput,['change_reason'=>'']));
    qa_expect(\Security\AdminAccount::findByNo($id)===$beforePolicy,'missing governance reason rejects account update without mutation');
    $policySession=tempSession($id);tempHttp($superSession,'/pages/account/process.php',$policyInput);$periodRow=\Security\AdminAccount::findByNo($id);
    qa_expect($periodRow['authority_valid_until']!==null && empty($periodRow['login_token']),'HTTP authority period update persists and revokes session');
    if(session_status()!==PHP_SESSION_ACTIVE){session_id('');session_start();}$sessions[]=session_id();$_SESSION['mfa_pending']=tempChallenge($periodRow);
    $db->prepare('UPDATE nb_admin SET authority_valid_until=DATE_ADD(NOW(),INTERVAL 2 DAY) WHERE no=?')->execute([$id]);
    qa_expect(tempReject(function(){\Security\Mfa::verify('123456');}),'authority change invalidates pending MFA while new period is valid');
    $db->prepare('UPDATE nb_admin SET authority_valid_until=? WHERE no=?')->execute([$periodRow['authority_valid_until'],$id]);session_write_close();
    $audit=$db->prepare("SELECT detail_json FROM nb_admin_audit WHERE actor_no=? AND target_no=? AND entity='account' ORDER BY no DESC LIMIT 1");$audit->execute([$super,$id]);$detail=json_decode((string)$audit->fetchColumn(),true);
    qa_expect(($detail['change_reason']??'')===$policyInput['change_reason'] && ($detail['authority_before']['account_identifier']??'')===$tag && ($detail['authority_after']['owner_display_name']??'')===$input['uname'] && ($detail['authority_after']['authority_valid_until']??null)===$periodRow['authority_valid_until'],'authority audit preserves reason owner role and before-after period');
    tempHttp($superSession,'/pages/account/process.php',array_merge($policyInput,['authority_valid_from'=>'2026-02-30T10:00']));
    qa_expect(\Security\AdminAccount::findByNo($id)===$periodRow,'invalid calendar date rejected without changing account');
    $faultName=$tag.'_audit';$faultReason='QA_AUDIT_FAIL_'.$tag;$faultCreated=false;$beforeFault=\Security\AdminAccount::findByNo($id);
    try{
        $db->exec("ALTER TABLE nb_admin_audit ADD CONSTRAINT ".$faultName." CHECK (LOCATE(".$db->quote($faultReason).",IFNULL(detail_json,''))=0)");$faultCreated=true;
        tempHttp($superSession,'/pages/account/process.php',array_merge($policyInput,['uname'=>'변경 QA','change_reason'=>$faultReason]));
        qa_expect(\Security\AdminAccount::findByNo($id)===$beforeFault,'failed mandatory authority audit rolls back account profile');
        $faultCreate=$input;$faultCreate['uid']=$tag.'_fc';$faultCreate['email']=$tag.'_fc@gmail.com';$faultCreate['change_reason']=$faultReason;
        [,,$faultCreateResult]=tempHttp($superSession,'/pages/account/process.php',$faultCreate);
        $st=$db->prepare("SELECT COUNT(*) FROM nb_admin WHERE uid=? AND sitekey='BLUESQ'");$st->execute([$faultCreate['uid']]);
        qa_expect(empty($faultCreateResult['success']) && empty($faultCreateResult['credential']) && (int)$st->fetchColumn()===0,'failed mandatory audit rolls back HTTP creation without issuing credential');
    }finally{if($faultCreated)$db->exec('ALTER TABLE nb_admin_audit DROP CHECK '.$faultName);}
    $periodSession=tempSession($id);
    $db->prepare('UPDATE nb_admin SET authority_valid_until=DATE_SUB(NOW(),INTERVAL 1 SECOND) WHERE no=?')->execute([$id]);
    [$expiredStatus]=tempHttp($periodSession,'/pages/account/password.php');
    qa_expect($expiredStatus!==200 && tempReject(function()use($id){\Security\AdminAccount::establish(\Security\AdminAccount::findByNo($id));}),'expiry blocks existing HTTP session and new authentication');
    $db->prepare('UPDATE nb_admin SET authority_valid_from=NULL,authority_valid_until=NULL WHERE no=?')->execute([$id]);
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
    if(isset($faultCreate))$db->prepare("DELETE FROM nb_admin WHERE uid=? AND sitekey='BLUESQ'")->execute([$faultCreate['uid']]);
    if(isset($faultUid)){$db->prepare("DELETE FROM nb_admin WHERE uid=? AND sitekey='BLUESQ'")->execute([$faultUid]);}
    if(session_status()===PHP_SESSION_ACTIVE)session_write_close();
    foreach($accounts as $id=>$uid){$db->prepare('DELETE FROM nb_admin_audit WHERE actor_no=?')->execute([$id]);$db->prepare('DELETE FROM nb_admin_privacy_access WHERE actor_no=?')->execute([$id]);$db->prepare('DELETE FROM nb_admin WHERE no=? AND uid=?')->execute([$id,$uid]);}
    foreach($sessions as $sid){$file=(session_save_path()?:sys_get_temp_dir()).'/sess_'.$sid;if(is_file($file))unlink($file);}
}
qa_done();
