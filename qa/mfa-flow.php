<?php
ob_start();
require __DIR__.'/bootstrap.php'; require_once dirname(__DIR__).'/inc/lib/db.php';
if(APP_ENV!=='development'||!is_file('/.dockerenv'))exit(1);
// No external mail from QA, irrespective of the developer's SMTP settings.
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
} catch(Throwable $e){qa_expect(false,$e->getMessage());}
finally {if($no)$pdo->prepare('DELETE FROM nb_admin WHERE no=? AND uid=?')->execute([$no,$uid]);}
qa_done();
