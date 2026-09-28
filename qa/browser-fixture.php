<?php
if(PHP_SAPI!=='cli'||!is_file('/.dockerenv'))exit(1);
require dirname(__DIR__).'/config/env.php';require dirname(__DIR__).'/inc/lib/session.boot.php';
if(env('APP_ENV')!=='development')exit(1);
require dirname(__DIR__).'/src/Database/DB.php';require dirname(__DIR__).'/inc/lib/db.php';
foreach(['HOST','NAME','USER','PASS','PORT'] as $key)define('DB_'.$key,env('DB_'.$key));define('DB_CHARSET','utf8mb4');
require dirname(__DIR__).'/nol-gate/lib/mfa.php';
$NO_SITE_UNIQUE_KEY='NOLTHE';$db=DB::getInstance();$mode=$argv[1]??'';
if(in_array($mode,['create','mfa'],true)){
    $uid='qa_visual_'.bin2hex(random_bytes(4));
    $db->prepare("INSERT INTO nb_admin(sitekey,uid,upwd,uname,email,phone,active_status,role_id,password_changed_at,last_login_at,created_at) VALUES('NOLTHE',?,?,?,?,'','Y',1,NOW(),NOW(),NOW())")->execute([$uid,password_hash(bin2hex(random_bytes(24)),PASSWORD_DEFAULT),'화면검사',$uid.'@example.com']);
    $row=AccountModel::authenticationRow((int)$db->lastInsertId());
    if($mode==='mfa')Mfa::begin($row);else {AuthSession::establish($row,'superadmin');$_SESSION['no_adm_last_activity']=time()-120;}
    $sid=session_id();session_write_close();$file=(session_save_path()?:sys_get_temp_dir()).'/sess_'.$sid;if(posix_geteuid()===0)chown($file,'www-data');
    echo json_encode(['uid'=>$uid,'sid'=>$sid,'name'=>session_name()]);
}elseif($mode==='expire'){
    $uid=$argv[2]??'';$sid=$argv[3]??'';
    if(!preg_match('/^qa_visual_[a-f0-9]{8}$/D',$uid)||!preg_match('/^[a-zA-Z0-9,-]+$/D',$sid))exit(1);
    session_write_close();session_id($sid);session_start();
    if(($_SESSION['no_adm_login_uid']??'')!==$uid)exit(1);
    $_SESSION['no_adm_last_activity']=time()-1801;session_write_close();
}elseif($mode==='cleanup'){
    $uid=$argv[2]??'';$sid=$argv[3]??'';
    if(!preg_match('/^qa_visual_[a-f0-9]{8}$/D',$uid)||!preg_match('/^[a-zA-Z0-9,-]+$/D',$sid))exit(1);
    $db->prepare("DELETE FROM nb_admin WHERE sitekey='NOLTHE' AND uid=?")->execute([$uid]);
    $file=(session_save_path()?:sys_get_temp_dir()).'/sess_'.$sid;if(is_file($file))unlink($file);
}
