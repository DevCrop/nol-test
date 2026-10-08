<?php
if (PHP_SAPI !== 'cli' || !is_file('/.dockerenv')) exit(1);
ob_start();
require dirname(__DIR__) . '/config/env.php';
require dirname(__DIR__) . '/inc/lib/session.boot.php';
if (env('APP_ENV') !== 'development') exit(1);
require dirname(__DIR__) . '/src/Database/DB.php';
require dirname(__DIR__) . '/inc/lib/db.php';
foreach (['HOST','NAME','USER','PASS','PORT'] as $key) if (!defined('DB_'.$key)) define('DB_'.$key, env('DB_'.$key));
define('DB_CHARSET', 'utf8mb4');
require dirname(__DIR__) . '/nol-gate/lib/mfa.php';
$GLOBALS['NO_SITE_UNIQUE_KEY'] = 'NOLTHE';
$admin_roles = [1 => ['code'=>'superadmin'], 2 => ['code'=>'manager']];
class QaMfa extends Mfa { public static $mail = []; protected static function deliver(array $mail): void { self::$mail = $mail; } }
$db = DB::getInstance(); $no = 0; $pass = 0; $fail = 0; $sessions = [];
function checkFollowup($ok, $label) { global $pass,$fail; $ok ? $pass++ : $fail++; echo ($ok?'PASS ':'FAIL ').$label."\n"; }
function rejectedFollowup(callable $fn): bool { try { $fn(); return false; } catch (RuntimeException $e) { return true; } }
function followupSession(array $row, string $token): string {
    global $sessions;
    if (session_status() === PHP_SESSION_ACTIVE) session_write_close();
    session_id(''); session_start();
    $_SESSION = ['no_adm_login_no'=>(int)$row['no'],'no_adm_login_uid'=>$row['uid'],'no_adm_login_uname'=>'QA','no_adm_login_role_id'=>1,'no_adm_login_role'=>'superadmin','no_adm_login_token'=>$token,'no_adm_last_activity'=>time()];
    $sid=session_id(); $sessions[]=$sid; session_write_close();
    $file=(session_save_path()?:sys_get_temp_dir()).'/sess_'.$sid; if(posix_geteuid()===0) chown($file,'www-data');
    return $sid;
}
function followupHttp(string $path, string $sid='', string $host='gate.local'): array {
    $ctx=stream_context_create(['http'=>['header'=>"Host: $host\r\nCookie: ".session_name()."=$sid\r\nX-Requested-With: XMLHttpRequest\r\n",'ignore_errors'=>true,'follow_location'=>0,'timeout'=>10]]);
    $body=file_get_contents('http://127.0.0.1'.$path,false,$ctx); preg_match('/\s(\d{3})\s/',$http_response_header[0]??'',$m);
    return [(int)($m[1]??0),$body];
}
$uid='qa_follow_'.bin2hex(random_bytes(5));
$privateFile=dirname(__DIR__).'/uploads/request/'.$uid.'.txt';
try {
    file_put_contents($privateFile,'QA synthetic private attachment');
    foreach(['localhost','gate.local'] as $host) {
        foreach(['/resource/vendor/fullPage.js-2.9.7/examples/backgrounds.html','/resource/vendor/tinymce/plugins/jbimages/ci/index.php/uploader/upload'] as $vendorPath) {
            [$vendorStatus]=followupHttp($vendorPath,'',$host);
            checkFollowup(in_array($vendorStatus,[403,404],true),'vendor example or retired uploader denied on '.$host.' '.$vendorPath);
        }
        [$status,$body]=followupHttp('/uploads/request/'.$uid.'.txt','',$host);
        checkFollowup(in_array($status,[403,404],true) && strpos($body,'QA synthetic private attachment')===false,'private attachment denied on '.$host);
    }
    $db->prepare("INSERT INTO nb_admin(sitekey,uid,upwd,uname,email,phone,active_status,role_id,password_changed_at,last_login_at,created_at) VALUES('NOLTHE',?,?,?,?,'','Y',1,NOW(),NOW(),NOW())")->execute([$uid,password_hash('Qa!234567890',PASSWORD_DEFAULT),'검증계정',$uid.'@example.com']);
    $no=(int)$db->lastInsertId(); $row=AccountModel::authenticationRow($no);
    QaMfa::begin($row);
    checkFollowup(rejectedFollowup(static function(){ QaMfa::bindEmail('attacker@example.com'); }) && !QaMfa::$mail,'MFA rejects arbitrary recipient before delivery');
    QaMfa::bindEmail($row['email']);
    checkFollowup(QaMfa::$mail['to']===$row['email'] && !isset($_SESSION['mfa_pending']['code_plain']),'MFA sends only registered email without plaintext session code');
    $expires=$_SESSION['mfa_pending']['expires'];
    checkFollowup(rejectedFollowup(static function(){ QaMfa::resend(); }),'MFA resend cooldown enforced');
    checkFollowup(rejectedFollowup(static function(){ QaMfa::verify('1x23456'); }),'MFA rejects malformed six-digit code');
    $_SESSION['mfa_pending']['sent_at']=time()-31; QaMfa::resend();
    checkFollowup($_SESSION['mfa_pending']['attempts']===1 && $_SESSION['mfa_pending']['expires']===$expires,'MFA resend preserves attempt and expiry budget');
    preg_match('/\b([0-9]{6})\b/',QaMfa::$mail['body'],$m); $code=$m[1];
    QaMfa::verify($code);
    checkFollowup((int)$_SESSION['no_adm_login_no']===$no && QaMfa::pending()===null,'valid MFA establishes session and consumes challenge');
    QaMfa::begin($row); QaMfa::bindEmail($row['email']); preg_match('/\b([0-9]{6})\b/',QaMfa::$mail['body'],$m);
    $db->prepare('UPDATE nb_admin SET upwd=? WHERE no=?')->execute([password_hash('Different!2345',PASSWORD_DEFAULT),$no]);
    checkFollowup(rejectedFollowup(static function() use($row){ AuthSession::establish($row,'superadmin'); }),'stale verified password cannot issue a token after reset');
    checkFollowup(rejectedFollowup(static function() use($m){ QaMfa::verify($m[1]); }),'password reset invalidates pending MFA');
    $row=AccountModel::authenticationRow($no);
    $db->prepare('UPDATE nb_admin SET email=?,login_token=NULL WHERE no=?')->execute(['changed-'.$row['email'],$no]);
    checkFollowup(rejectedFollowup(static function() use($row){ AuthSession::establish($row,'superadmin'); }),'stale verified mailbox cannot issue a token after email change');
    $db->prepare('UPDATE nb_admin SET email=?,role_id=2 WHERE no=?')->execute([$row['email'],$no]);
    checkFollowup(rejectedFollowup(static function() use($row){ AuthSession::establish($row,'superadmin'); }),'stale verified role cannot issue a token after role change');
    $db->prepare('UPDATE nb_admin SET role_id=1 WHERE no=?')->execute([$no]);
    $row=AccountModel::authenticationRow($no); QaMfa::begin($row); $_SESSION['mfa_pending']['expires']=time();
    checkFollowup(rejectedFollowup(static function(){ QaMfa::resend(); }) && QaMfa::pending()===null,'expired MFA cannot be renewed by resend');
    QaMfa::begin($row); QaMfa::bindEmail($row['email']);
    for($i=0;$i<5;$i++) rejectedFollowup(static function(){ QaMfa::verify('invalid'); });
    checkFollowup(QaMfa::pending()===null,'five invalid MFA attempts destroy challenge');
    foreach(['valid','empty','disabled','demoted','deleted'] as $case) {
        $token=bin2hex(random_bytes(32));
        $db->prepare("UPDATE nb_admin SET login_token=?,active_status='Y',role_id=1 WHERE no=?")->execute([$token,$no]);
        $sid=followupSession($row,$token);
        if($case==='empty') $db->prepare('UPDATE nb_admin SET login_token=NULL WHERE no=?')->execute([$no]);
        if($case==='disabled') $db->prepare("UPDATE nb_admin SET active_status='N' WHERE no=?")->execute([$no]);
        if($case==='demoted') $db->prepare('UPDATE nb_admin SET role_id=2 WHERE no=?')->execute([$no]);
        if($case==='deleted') $db->prepare('DELETE FROM nb_admin WHERE no=?')->execute([$no]);
        [$status]=followupHttp('/pages/account/index.php',$sid);
        checkFollowup($status===($case==='valid'?200:401),'account index validates live session: '.$case);
        if($case==='valid') {
            [$detailStatus]=followupHttp('/pages/account/edit.php?no='.$no,$sid);
            $audit=$db->prepare("SELECT COUNT(*) FROM nb_admin_privacy_access WHERE actor_no=? AND actor_uid=? AND target_no=? AND entity='admin_account' AND action='view' AND task='관리자 계정 상세 열람'");
            $audit->execute([$no,$uid,$no]);
            checkFollowup($detailStatus===200 && (int)$audit->fetchColumn()===1,'account detail disclosure has one privacy access record');
        }
    }
} catch(Throwable $e) { checkFollowup(false,$e->getMessage()); }
finally {
    if(is_file($privateFile)) unlink($privateFile);
    if($no) $db->prepare('DELETE FROM nb_admin WHERE no=? AND uid=?')->execute([$no,$uid]);
    if($no) $db->prepare('DELETE FROM nb_admin_privacy_access WHERE actor_no=? AND actor_uid=?')->execute([$no,$uid]);
    if(session_status()===PHP_SESSION_ACTIVE) session_write_close();
    foreach($sessions as $sid){$file=(session_save_path()?:sys_get_temp_dir()).'/sess_'.$sid;if(is_file($file))unlink($file);}
}
echo "pass=$pass fail=$fail\n"; exit($fail?1:0);
