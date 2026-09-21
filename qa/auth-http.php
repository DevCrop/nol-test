<?php
ob_start();
require __DIR__.'/bootstrap.php';
require_once dirname(__DIR__).'/inc/lib/db.php';
if (APP_ENV !== 'development' || !is_file('/.dockerenv')) exit("Docker development only\n");
$pdo=DB::getInstance(); $no=0; $sessions=[];
function httpQa(string $path,string $sid,string $method='GET',array $data=[]): array {
    $body=http_build_query($data);
    $context=stream_context_create(['http'=>['method'=>$method,'header'=>"Host: gate.local\r\nCookie: ".session_name()."=".$sid."\r\nX-Requested-With: XMLHttpRequest\r\nContent-Type: application/x-www-form-urlencoded\r\n",'content'=>$body,'ignore_errors'=>true,'follow_location'=>0,'timeout'=>10]]);
    $result=file_get_contents('http://127.0.0.1'.$path,false,$context);
    preg_match('/\s(\d{3})\s/',$http_response_header[0]??'',$m);
    return [(int)($m[1]??0),json_decode($result,true),$http_response_header];
}
function fixtureQa(int $no,int $age=0): array {
    global $sessions;
    if(session_status()===PHP_SESSION_ACTIVE) session_write_close();
    session_id(''); session_start();
    $_SESSION=[];
    \Security\AdminAccount::establish(\Security\AdminAccount::findByNo($no));
    $_SESSION['no_adm_last_activity']=time()-$age;
    $sid=session_id(); $token=\Security\Csrf::token();
    session_write_close(); $sessions[]=$sid;
    // CLI suite can run as root, while the HTTP server runs as www-data.
    $file=(session_save_path() ?: sys_get_temp_dir()).'/sess_'.$sid;
    if(is_file($file) && function_exists('posix_geteuid') && posix_geteuid()===0) chown($file,'www-data');
    return [$sid,$token];
}
try {
    $uid='qa_http_'.bin2hex(random_bytes(4));
    $pdo->prepare("INSERT INTO nb_admin(sitekey,uid,upwd,uname,email,active_status,role_code,password_changed_at) VALUES('BLUESQ',?,?,? ,?,'Y','admin',NOW())")->execute([$uid,password_hash('Qa!234567890',PASSWORD_DEFAULT),'품질검사',$uid.'@example.com']);
    $no=(int)$pdo->lastInsertId();
    [$sid,$csrf]=fixtureQa($no,120);
    [$status,$body]=httpQa('/admin/lib/session/ping.php',$sid);
    qa_expect($status===200 && $body['expiresIn']<=1680 && $body['expiresIn']>1660,'GET status does not extend idle deadline');
    [$status,$body]=httpQa('/admin/lib/session/ping.php',$sid,'POST',['_csrf'=>$csrf]);
    qa_expect($status===200 && $body['expiresIn']>=1798,'POST activity extends server deadline');
    [$status]=httpQa('/admin/lib/session/ping.php',$sid,'POST');
    qa_expect($status===403,'activity requires CSRF');
    [$status]=httpQa('/admin/pages/account/index.php',$sid);
    qa_expect($status===403,'regular administrator cannot manage accounts');
    $pdo->prepare('UPDATE nb_admin SET password_must_change=1 WHERE no=?')->execute([$no]);
    [$status,$body]=httpQa('/admin/pages/board/board.list.php',$sid);
    qa_expect($status===403 && ($body['redirect']??'')==='/admin/pages/account/password.php','DB password reset is enforced on existing session');
    [$status]=httpQa('/admin/pages/account/password.php',$sid);
    qa_expect($status===200,'forced password change page remains accessible');
    $pdo->prepare('UPDATE nb_admin SET password_must_change=0 WHERE no=?')->execute([$no]);
    [$old]=fixtureQa($no);
    [$fresh]=fixtureQa($no);
    [$status]=httpQa('/admin/lib/session/ping.php',$old);
    qa_expect($status===401,'previous login is terminated');
    [$status]=httpQa('/admin/lib/session/ping.php',$fresh);
    qa_expect($status===200,'rejecting old login preserves new login');
    [$expired]=fixtureQa($no,SESSION_LIFETIME);
    [$status]=httpQa('/admin/lib/session/ping.php',$expired);
    qa_expect($status===401,'idle session expires at boundary');
    [$sid]=fixtureQa($no);
    $pdo->prepare("UPDATE nb_admin SET active_status='N' WHERE no=?")->execute([$no]);
    [$status]=httpQa('/admin/lib/session/ping.php',$sid);
    qa_expect($status===401,'disabled account loses active session');
    [$status]=httpQa('/admin/lib/login/login.process.php','','POST');
    qa_expect($status===403,'login requires CSRF');
    [$status]=httpQa('/admin/lib/login/mfa.process.php','','POST');
    qa_expect($status===403,'MFA requires CSRF');
    \Security\AccountValidator::password('Good1234','Good1234');
    qa_expect(true,'NOL eight character / three group policy accepted');
    $rejected=false; try {\Security\AccountValidator::password(str_repeat('A',73).'a1!',str_repeat('A',73).'a1!');} catch(RuntimeException $e) {$rejected=true;}
    qa_expect($rejected,'bcrypt truncation length rejected');
    $pdo->prepare("UPDATE nb_admin SET active_status='Y' WHERE no=?")->execute([$no]);
    $row=\Security\AdminAccount::findByNo($no);
    $pending=['admin_no'=>$no,'code_hash'=>password_hash('123456',PASSWORD_DEFAULT),'expires'=>time()+60,'attempts'=>0,'sent_at'=>time(),'email'=>$row['email'],'credential_version'=>hash('sha256',$row['upwd'])];
    $_SESSION['mfa_pending']=$pending;
    qa_expect(\Security\Mfa::verify('123456')['no']==$no && empty($_SESSION['mfa_pending']),'MFA success consumes bound challenge');
    $_SESSION['mfa_pending']=$pending;
    $bad=false;try{\Security\Mfa::verify('123-456');}catch(RuntimeException $e){$bad=true;}
    qa_expect($bad,'MFA does not normalize malformed codes');
    $_SESSION['mfa_pending']=$pending;
    $pdo->prepare("UPDATE nb_admin SET active_status='N' WHERE no=?")->execute([$no]);
    $bad=false;try{\Security\Mfa::verify('123456');}catch(RuntimeException $e){$bad=true;}
    qa_expect($bad,'MFA rechecks disabled account after password step');
    $pdo->prepare("UPDATE nb_admin SET active_status='Y', upwd=? WHERE no=?")->execute([password_hash('Changed123!',PASSWORD_DEFAULT),$no]);
    $_SESSION['mfa_pending']=$pending;
    $bad=false;try{\Security\Mfa::verify('123456');}catch(RuntimeException $e){$bad=true;}
    qa_expect($bad,'MFA rejects challenge after credential change');
} catch(Throwable $e) { qa_expect(false,$e->getMessage()); }
finally {
    if($no) $pdo->prepare('DELETE FROM nb_admin WHERE no=? AND uid=?')->execute([$no,$uid]);
    foreach($sessions as $sid) {
        $file=(session_save_path() ?: sys_get_temp_dir()).'/sess_'.$sid;
        if(is_file($file)) unlink($file);
    }
}
qa_done();
