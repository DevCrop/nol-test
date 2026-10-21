<?php
// Isolated, exact-ID fixtures only. No SMTP delivery or production execution.
if (PHP_SAPI !== 'cli' || !is_file('/.dockerenv')) exit(1);
ob_start();
$root = dirname(__DIR__);
require $root . '/config/env.php';
if (env('APP_ENV') !== 'development' || env('DB_USER') !== 'qa_app') exit(1);
require $root . '/inc/lib/session.boot.php';
require $root . '/src/Database/DB.php';
require $root . '/inc/lib/db.php';
foreach (['HOST','PORT','NAME','USER','PASS','CHARSET'] as $key) define('DB_'.$key, env('DB_'.$key, $key === 'CHARSET' ? 'utf8mb4' : ''));
require $root . '/nol-gate/lib/Role.php';
require $root . '/nol-gate/lib/AccountCredentialService.php';
require $root . '/nol-gate/lib/AuthSession.php';
require $root . '/nol-gate/lib/PasswordChangeGate.php';
require $root . '/nol-gate/lib/mfa.php';
class QaTemporaryMfa extends Mfa {
    public static string $code = '';
    protected static function deliver(array $outbox): void {
        preg_match('/\b[0-9]{6}\b/', $outbox['body'], $matches);
        self::$code = $matches[0] ?? '';
    }
}
$GLOBALS['NO_SITE_UNIQUE_KEY'] = 'NOLTHE';
$GLOBALS['admin_roles'] = [1=>['code'=>'superadmin'],2=>['code'=>'admin']];
$GLOBALS['NO_ADMIN_PAGES_BASE'] = '/index.php/pages';
$db = DB::getInstance(); $service = new AccountCredentialService();
$ids=[]; $sessions=[]; $pass=0; $fail=0;
$uid='qatmp_'.bin2hex(random_bytes(4));
function checkTemp($ok, $label) { global $pass,$fail; $ok ? $pass++ : $fail++; echo ($ok?'PASS ':'FAIL ').$label."\n"; }
function rejectsTemp(callable $call): bool { try {$call(); return false;} catch(RuntimeException $e) {return true;} }
function rowTemp($id): array {return AccountModel::authenticationRow($id);}
function sessionTemp(array $row): array {
    global $sessions;
    if(session_status()!==PHP_SESSION_ACTIVE) {session_id('');session_start();}
    AuthSession::loginAdmin($row, (int)$row['role_id']===1?'superadmin':'admin');
    $_SESSION['_csrf']=$csrf=bin2hex(random_bytes(32));
    $sid=session_id();$sessions[]=$sid;session_write_close();
    $file=(session_save_path()?:sys_get_temp_dir()).'/sess_'.$sid;
    if(function_exists('posix_geteuid')&&posix_geteuid()===0)chown($file,'www-data');
    return [$sid,$csrf];
}
function httpTemp(array $session, string $path, ?array $data=null): array {
    $headers='Host: '.env('GATE_HOST','gate.local')."\r\nCookie: ".session_name().'='.$session[0]."\r\n";
    if($data!==null) {$headers.="Content-Type: application/x-www-form-urlencoded\r\nX-Requested-With: XMLHttpRequest\r\nX-CSRF-Token: ".$session[1]."\r\n";}
    $context=stream_context_create(['http'=>['method'=>$data===null?'GET':'POST','header'=>$headers,'content'=>$data===null?'':http_build_query($data),'ignore_errors'=>true,'follow_location'=>0,'timeout'=>15]]);
    $body=file_get_contents('http://127.0.0.1/index.php'.$path,false,$context);
    preg_match('/\s(\d{3})\s/',$http_response_header[0]??'',$m);
    return [(int)($m[1]??0),(string)$body,json_decode((string)$body,true),implode("\n",$http_response_header)];
}
try {
    $superUid=$uid.'_s';
    $db->prepare("INSERT INTO nb_admin(uid,upwd,uname,email,active_status,role_id,sitekey,created_at,last_login_at,password_changed_at) VALUES(?,?,?,?,'Y',1,'NOLTHE',NOW(),NOW(),NOW())")->execute([$superUid,password_hash(TemporaryPassword::generate(),PASSWORD_DEFAULT),'테스트 관리자',$superUid.'@gmail.com']);
    $superId=(int)$db->lastInsertId();$superSession=sessionTemp(rowTemp($superId));
    $apiUid=$uid.'_a';
    $apiInput=['mode'=>'save','uid'=>$apiUid,'uname'=>'테스트 계정','email'=>$apiUid.'@gmail.com','phone'=>'010-7999-9912','role_id'=>2,'acl'=>['board'=>['view'=>1]]];
    [$apiStatus,,$apiJson,$apiHeaders]=httpTemp($superSession,'/Controller/AccountController.php',$apiInput);
    $apiId=(int)($apiJson['id']??0);
    checkTemp($apiStatus===200 && !empty($apiJson['success']) && !empty($apiJson['credential']['temporary_password']),'supervisor API creates account without client password');
    if($apiId) {
        $apiRow=rowTemp($apiId);
        checkTemp(password_verify($apiJson['credential']['temporary_password'],$apiRow['upwd'])&& (int)$apiRow['must_change_password']===1,'API one-time credential matches persisted hash and state');
        [$listStatus,$listBody]=httpTemp($superSession,'/pages/account/index.php');
        checkTemp($listStatus===200 && preg_match('/>\s*임시\s*<\/span>/u',$listBody) && strpos($listBody,$apiJson['credential']['temporary_password'])===false,'account list shows temporary state without secret');
        [$apiStatus,,$resetJson]=httpTemp($superSession,'/Controller/AccountController.php',['mode'=>'reissue_temporary','id'=>$apiId]);
        checkTemp($apiStatus===200 && !empty($resetJson['credential']['temporary_password']) && !password_verify($apiJson['credential']['temporary_password'],rowTemp($apiId)['upwd']),'supervisor API reissues and revokes previous password');
        checkTemp(stripos($apiHeaders,'no-store')!==false,'one-time credential API response is no-store');
        [$csrfStatus]=httpTemp([$superSession[0],'wrong'],'/Controller/AccountController.php',['mode'=>'reissue_temporary','id'=>$apiId]);
        checkTemp($csrfStatus===403,'reissue rejects invalid CSRF');
    }
    $password=TemporaryPassword::generate();
    $validator=new Validator();$validator->passwordPolicy('upwd',$password,'password');
    checkTemp(!$validator->fails() && strlen($password)>=20,'generated credential satisfies password policy');
    $input=['uid'=>$uid,'uname'=>'임시 QA','email'=>$uid.'@gmail.com','phone'=>'010-7999-9911','active_status'=>'Y','role_id'=>2];
    $created=$service->create($input,[], $password);$id=(int)$created['id'];$ids[]=$id;$row=rowTemp($id);
    checkTemp(password_verify($password,$row['upwd'])&&$row['upwd']!==$password,'only password hash persisted');
    checkTemp((int)$row['must_change_password']===1 && strtotime($row['temporary_password_expires_at'])>time(),'temporary flag and expiry persisted');
    $gate=new PasswordChangeGate();
    checkTemp($gate->mustChange($row)&&$gate->landingAfterLogin()==='/index.php/pages/board/board.list.php','temporary status policy available');
    AccountModel::update($id,$input);
    checkTemp(rowTemp($id)['upwd']===$row['upwd'] && (int)rowTemp($id)['must_change_password']===1,'profile update preserves hash and temporary state');
    checkTemp(rejectsTemp(function()use($service,$id){$service->reissue($id,$id);}), 'self reissue rejected');
    $oldSession=sessionTemp($row);
    session_id('');session_start();
    QaTemporaryMfa::begin($row);QaTemporaryMfa::bindEmail($row['email']);
    checkTemp(strlen(QaTemporaryMfa::$code)===6 && empty($_SESSION['no_adm_login_no']),'MFA pending does not grant admin access');
    $reissued=$service->reissue($id,0);$newPassword=$reissued['temporary_password'];$row=rowTemp($id);
    checkTemp(rejectsTemp(function(){QaTemporaryMfa::verify(QaTemporaryMfa::$code);}), 'reissue invalidates pending MFA');
    $sessions[]=session_id();
    session_write_close();
    checkTemp(!password_verify($password,$row['upwd'])&&password_verify($newPassword,$row['upwd'])&&empty($row['login_token']),'reissue invalidates old credential and session token');
    [$status,,$json]=httpTemp($oldSession,'/ajax/session.activity.php',[]);
    checkTemp($status===401,'old authenticated HTTP session rejected after reissue');
    checkTemp(rejectsTemp(function()use($service,$id){$service->change($id,'stale','','NewPassword9!','NewPassword9!');}),'stale session cannot change password');
    session_id('');session_start();QaTemporaryMfa::begin($row);QaTemporaryMfa::bindEmail($row['email']);
    QaTemporaryMfa::verify(QaTemporaryMfa::$code);
    checkTemp((int)($_SESSION['no_adm_login_no']??0)===$id && empty($_SESSION['mfa_pending']) && $gate->landingAfterLogin()==='/index.php/pages/setting/pwd.php','successful MFA leads to mandatory change');
    $_SESSION['_csrf']=$csrf=bin2hex(random_bytes(32));$sid=session_id();$sessions[]=$sid;session_write_close();
    $file=(session_save_path()?:sys_get_temp_dir()).'/sess_'.$sid;if(function_exists('posix_geteuid')&&posix_geteuid()===0)chown($file,'www-data');
    $session=[$sid,$csrf];
    [$status,$body,,$headers]=httpTemp($session,'/pages/setting/pwd.php');
    checkTemp($status===200 && strpos($body,'required-password.js')!==false && strpos($body,'<aside')===false,'forced change renders login-style page without admin navigation');
    checkTemp(stripos($headers,'no-store')!==false,'required page is no-store');
    preg_match_all('/(?:src|href)="([^"]+\.(?:css|js|png)(?:\?[^"]*)?)"/',$body,$pageAssets);
    $assetsOk=count($pageAssets[1])>=4;
    foreach($pageAssets[1] as $asset){$asset=html_entity_decode($asset);if(strpos($asset,'/index.php')===0)$asset=substr($asset,10);[$assetStatus,$assetBody]=httpTemp($session,$asset);$assetsOk=$assetsOk&&$assetStatus===200&&$assetBody!=='';}
    checkTemp($assetsOk,'rendered required-page CSS JS and image URLs load through PHP router');
    [$status,$body]=httpTemp($session,'/pages/board/board.list.php');
    checkTemp($status!==200 || strpos($body,'비밀번호')!==false,'temporary account cannot enter ordinary admin page');
    [$status,,$json]=httpTemp($session,'/Controller/AccountController.php',['mode'=>'reissue_temporary','id'=>$id]);
    checkTemp(($json['success']??true)===false,'temporary account blocked from ordinary mutation API');
    $token=(string)rowTemp($id)['login_token'];
    checkTemp(rejectsTemp(function()use($service,$id,$token){$service->change($id,$token,'','short','short');}),'weak password rejected');
    checkTemp(rejectsTemp(function()use($service,$id,$token,$newPassword){$service->change($id,$token,'',$newPassword,$newPassword);}), 'unchanged temporary password rejected');
    checkTemp(rejectsTemp(function()use($service,$id,$token){$service->change($id,$token,'','NewPassword9!','Mismatch9!');}),'confirmation mismatch rejected');
    [$status,,$json,$headers]=httpTemp($session,'/pages/setting/ajax/setting.process.php',['mode'=>'pwd.change','pwd_new'=>'NewPassword9!','pwd_new_confirm'=>'NewPassword9!']);
    checkTemp($status===200 && ($json['result']??'')==='success','actual HTTP password change succeeds');
    $row=rowTemp($id);
    checkTemp((int)$row['must_change_password']===0 && $row['temporary_password_expires_at']===null && empty($row['login_token']) && password_verify('NewPassword9!',$row['upwd']),'change atomically clears temporary status expiry and active token');
    checkTemp(stripos($headers,'Set-Cookie:')!==false && stripos($headers,'no-store')!==false,'change expires cookie and disallows caching');
    [$status]=httpTemp($session,'/ajax/session.activity.php',[]);
    checkTemp(in_array($status,[401,403],true),'old session rejected after completed change (authentication or stale CSRF)');
    $normalSession=sessionTemp(rowTemp($id));
    [$status,,$json]=httpTemp($normalSession,'/pages/setting/ajax/setting.process.php',['mode'=>'pwd.change','pwd_old'=>'WrongOld9!','pwd_new'=>'SecondPassword9!','pwd_new_confirm'=>'SecondPassword9!']);
    checkTemp(($json['result']??'')==='fail' && password_verify('NewPassword9!',rowTemp($id)['upwd']),'ordinary change still requires current password');
    [$status,,$json]=httpTemp($normalSession,'/pages/setting/ajax/setting.process.php',['mode'=>'pwd.change','pwd_old'=>'NewPassword9!','pwd_new'=>'SecondPassword9!','pwd_new_confirm'=>'SecondPassword9!']);
    checkTemp(($json['result']??'')==='success' && password_verify('SecondPassword9!',rowTemp($id)['upwd']),'ordinary password change regression succeeds');
    $service->reissue($id,0);
    $db->prepare('UPDATE nb_admin SET temporary_password_expires_at=DATE_SUB(NOW(),INTERVAL 1 SECOND) WHERE no=?')->execute([$id]);
    checkTemp(rejectsTemp(function()use($id){TemporaryPassword::assertUsable(rowTemp($id));}),'expired temporary credential rejected');
    checkTemp(rejectsTemp(function()use($id){AuthSession::establish(rowTemp($id),'admin');}),'expired credential cannot establish authenticated session');
} catch(Throwable $e) {checkTemp(false,'exception: '.$e->getMessage());}
finally {
    foreach([[$apiId??0,$apiUid??''],[$superId??0,$superUid??'']] as [$cleanId,$cleanUid]) {
        if($cleanId){$db->prepare('DELETE FROM nb_admin_audit WHERE actor_no=?')->execute([$cleanId]);$db->prepare('DELETE FROM nb_admin_privacy_access WHERE actor_no=?')->execute([$cleanId]);$db->prepare('DELETE FROM nb_admin_acl WHERE admin_no=?')->execute([$cleanId]);$db->prepare('DELETE FROM nb_admin WHERE no=? AND uid=?')->execute([$cleanId,$cleanUid]);}
    }
    foreach($ids as $id){$db->prepare('DELETE FROM nb_admin_audit WHERE actor_no=?')->execute([$id]);$db->prepare('DELETE FROM nb_admin_privacy_access WHERE actor_no=?')->execute([$id]);$db->prepare('DELETE FROM nb_admin_acl WHERE admin_no=?')->execute([$id]);$db->prepare('DELETE FROM nb_admin WHERE no=? AND uid=?')->execute([$id,$uid]);}
    foreach($sessions as $sid){$file=(session_save_path()?:sys_get_temp_dir()).'/sess_'.$sid;if(is_file($file))unlink($file);}
}
echo "pass={$pass} fail={$fail}\n";exit($fail?1:0);
