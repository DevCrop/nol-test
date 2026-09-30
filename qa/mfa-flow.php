<?php
ob_start();
require __DIR__.'/bootstrap.php'; require_once dirname(__DIR__).'/inc/lib/db.php';
if(APP_ENV!=='development'||!is_file('/.dockerenv'))exit(1);
// No external mail from QA, irrespective of the developer's SMTP settings.
$serverWithoutSmtp = (string) blue_env('SMTP_USER', '') === '' && (string) blue_env('SMTP_PASS', '') === '';
$legacyLog = dirname(__DIR__) . '/storage/mfa-mail.log';
$legacyHash = is_file($legacyLog) ? hash_file('sha256', $legacyLog) : null;
$_ENV['SMTP_USER']=''; $_ENV['SMTP_PASS']=''; putenv('SMTP_USER=');putenv('SMTP_PASS=');
$pdo=DB::getInstance();$no=0;$uid='qa_mfa_'.bin2hex(random_bytes(4));
try {
    $pdo->prepare("INSERT INTO nb_admin(sitekey,uid,upwd,uname,email,active_status,password_changed_at) VALUES('BLUESQ',?,?,?,?,'Y',NOW())")->execute([$uid,password_hash('Example123!',PASSWORD_DEFAULT),'인증검사',$uid.'@example.com']);
    $no=(int)$pdo->lastInsertId();$row=\Security\AdminAccount::findByNo($no);
    \Security\Mfa::begin($row);
    qa_expect(empty($_SESSION['mfa_pending']['code_hash']) && empty($_SESSION['no_adm_login_uid']),'MFA starts with email entry, not authenticated');
    $rejected=false;try{\Security\Mfa::bindEmail('someone@example.com');}catch(RuntimeException $e){$rejected=true;}
    qa_expect($rejected && empty($_SESSION['mfa_pending']['code_hash']),'arbitrary recipient rejected before sending');
    \Security\Mfa::bindEmail($row['email']);
    $p=$_SESSION['mfa_pending'];
    qa_expect(!empty($p['code_hash']) && $p['sent_at']>0,'registered email issues challenge');
    $rejected=false;try{\Security\Mfa::resend();}catch(RuntimeException $e){$rejected=true;}
    qa_expect($rejected,'resend cooldown enforced');
    $_SESSION['mfa_pending']['sent_at']=time()-MFA_RESEND_SECONDS-1;
    $_SESSION['mfa_pending']['attempts']=2;
    \Security\Mfa::resend();
    qa_expect($_SESSION['mfa_pending']['attempts']===2 && $_SESSION['mfa_pending']['expires']===$p['expires'],'resend preserves expiry and attempt budget');
    $pdo->prepare('UPDATE nb_admin SET email=? WHERE no=?')->execute(['changed@example.com',$no]);
    $_SESSION['mfa_pending']['sent_at']=time()-MFA_RESEND_SECONDS-1;
    $rejected=false;try{\Security\Mfa::resend();}catch(RuntimeException $e){$rejected=true;}
    qa_expect($rejected && empty($_SESSION['mfa_pending']),'email change invalidates pending challenge');
    qa_expect((is_file($legacyLog) ? hash_file('sha256', $legacyLog) : null) === $legacyHash, 'offline MFA QA never writes codes to hosted log files');
    if ($serverWithoutSmtp) {
        $row=\Security\AdminAccount::findByNo($no);
        \Security\Mfa::begin($row);
        $csrf=\Security\Csrf::token(); $sid=session_id(); $cookie=session_name();
        session_write_close();
        $sessionFile=(session_save_path() ?: sys_get_temp_dir()).'/sess_'.$sid;
        if(is_file($sessionFile) && function_exists('posix_geteuid') && posix_geteuid()===0) chown($sessionFile,'www-data');
        $ctx=stream_context_create(['http'=>['method'=>'POST','header'=>"Host: gate.local\r\nCookie: $cookie=$sid\r\nContent-Type: application/x-www-form-urlencoded\r\n",'content'=>http_build_query(['_csrf'=>$csrf,'mfa_email'=>$row['email']]),'ignore_errors'=>true,'follow_location'=>0,'timeout'=>10]]);
        file_get_contents('http://127.0.0.1/admin/lib/login/mfa.email.php',false,$ctx);
        session_id($sid); session_start();
        qa_expect(($_SESSION['mfa_error'] ?? '') === '인증 메일 설정이 완료되지 않았습니다.' && empty($_SESSION['mfa_pending']['code_hash']) && empty($_SESSION['no_adm_login_uid']), 'HTTP MFA without SMTP fails before challenge or authentication');
        $_SESSION=[];session_destroy();
    }
} catch(Throwable $e){qa_expect(false,$e->getMessage());}
finally {if($no)$pdo->prepare('DELETE FROM nb_admin WHERE no=? AND uid=?')->execute([$no,$uid]);}
qa_done();
