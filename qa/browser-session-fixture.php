<?php
if(PHP_SAPI!=='cli') {http_response_code(404);exit;}
require __DIR__.'/bootstrap.php';
require_once dirname(__DIR__).'/inc/lib/db.php';
if(APP_ENV!=='development'||!is_file('/.dockerenv')) exit(1);
$pdo=DB::getInstance(); $mode=$argv[1]??'';
if(in_array($mode,['create','mfa'],true)) {
    $uid='qa_visual_'.bin2hex(random_bytes(4));
    $pdo->prepare("INSERT INTO nb_admin(sitekey,uid,upwd,uname,email,active_status,role_code,password_changed_at) VALUES('BLUESQ',?,?,? ,?,'Y','super',NOW())")->execute([$uid,password_hash(bin2hex(random_bytes(24)),PASSWORD_DEFAULT),'화면검사',$uid.'@example.com']);
    $row=\Security\AdminAccount::findByNo((int)$pdo->lastInsertId());
    \Security\AdminAccount::establish($row);
    $_SESSION['no_adm_last_activity']=time()-120;
    if($mode==='mfa') \Security\Mfa::begin($row);
    $sid=session_id(); session_write_close();
    $file=(session_save_path()?:sys_get_temp_dir()).'/sess_'.$sid;
    if(function_exists('posix_geteuid')&&posix_geteuid()===0) chown($file,'www-data');
    echo json_encode(['uid'=>$uid,'sid'=>$sid,'name'=>session_name()]);
} elseif($mode==='cleanup') {
    $uid=$argv[2]??''; $sid=$argv[3]??'';
    if(!preg_match('/^qa_visual_[a-f0-9]{8}$/D',$uid)||!preg_match('/^[a-zA-Z0-9,-]+$/D',$sid)) exit(1);
    $pdo->prepare("DELETE FROM nb_admin WHERE sitekey='BLUESQ' AND uid=?")->execute([$uid]);
    $file=(session_save_path()?:sys_get_temp_dir()).'/sess_'.$sid;
    if(is_file($file)) unlink($file);
}
