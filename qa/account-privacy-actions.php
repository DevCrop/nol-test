<?php
ob_start();require __DIR__.'/bootstrap.php';require dirname(__DIR__).'/inc/lib/db.php';
if(!is_file('/.dockerenv')||APP_ENV!=='development'||DB_USER!=='qa_app')exit(1);
$db=DB::getInstance();$tag='qpa_'.bin2hex(random_bytes(4));$actor=0;$target=0;$sid='';$trigger='';
function accountPrivacyHttp(array $input):array {
    $input['_csrf']=$GLOBALS['csrf'];$path='/admin/pages/account/process.php';$route=blue_env('GATE_ROUTE_MODE','path');if($route==='php')$path='/index.php'.substr($path,6);elseif($route==='internal')$path=substr($path,6);
    $ctx=stream_context_create(['http'=>['method'=>'POST','header'=>'Host: '.blue_env('GATE_HOST','gate.local')."\r\nCookie: ".session_name().'='.$GLOBALS['sid']."\r\nX-Requested-With: XMLHttpRequest\r\nContent-Type: application/x-www-form-urlencoded\r\n",'content'=>http_build_query($input),'ignore_errors'=>true,'follow_location'=>0,'timeout'=>15]]);
    $body=(string)file_get_contents('http://127.0.0.1'.$path,false,$ctx);preg_match('/\s(\d{3})\s/',$http_response_header[0]??'',$m);return [(int)($m[1]??0),json_decode($body,true),$body];
}
function accountPrivacyRow(int $id):array {$q=$GLOBALS['db']->prepare('SELECT * FROM nb_admin WHERE no=?');$q->execute([$id]);return $q->fetch(PDO::FETCH_ASSOC)?:[];}
function accountPrivacyLogs():array {$q=$GLOBALS['db']->prepare('SELECT * FROM nb_admin_privacy_access WHERE actor_no=? ORDER BY no');$q->execute([$GLOBALS['actor']]);return $q->fetchAll(PDO::FETCH_ASSOC);}
try {
    $db->prepare("INSERT INTO nb_admin(sitekey,uid,upwd,uname,email,active_status,role_code,password_changed_at) VALUES('BLUESQ',?,?,?,?,'Y','super',NOW())")->execute([$tag,password_hash(bin2hex(random_bytes(24)),PASSWORD_DEFAULT),'QA',$tag.'@example.com']);$actor=(int)$db->lastInsertId();
    \Security\AdminAccount::establish(\Security\AdminAccount::findByNo($actor));$sid=session_id();$csrf=\Security\Csrf::token();session_write_close();if(posix_geteuid()===0)chown((session_save_path()?:sys_get_temp_dir()).'/sess_'.$sid,'www-data');
    $input=['mode'=>'create','uid'=>$tag.'_target','uname'=>'검증담당자','email'=>$tag.'_target@example.com','role_code'=>'admin','active_status'=>'N','permissions_present'=>'1','change_reason'=>'QA 계정 개인정보 기록 검증'];
    $r=accountPrivacyHttp($input);$target=(int)($r[1]['credential']['id']??0);$plain=(string)($r[1]['credential']['temporary_password']??'');
    qa_expect($r[0]===200 && !empty($r[1]['success']) && $target>0,'HTTP temporary account creation succeeds');
    if($target<1)throw new RuntimeException('Account fixture creation failed: '.($r[1]['message']??'unexpected response'));
    $logs=accountPrivacyLogs();$log=end($logs);qa_expect($log && $log['action']==='create' && $log['entity']==='admin_account' && (int)$log['target_no']===$target && $log['subject_label']===\Security\PiiMask::name('검증담당자') && $log['actor_uid']===$tag && $log['actor_ip']==='127.0.0.1','account create privacy action target subject actor and IP');
    $input['mode']='update';$input['no']=$target;$input['uname']='수정담당자';$r=accountPrivacyHttp($input);qa_expect($r[0]===302 && (accountPrivacyRow($target)['uname']??'')==='수정담당자','HTTP managed account update succeeds');
    $logs=accountPrivacyLogs();$log=end($logs);qa_expect($log && $log['action']==='update' && (int)$log['target_no']===$target && $log['subject_label']===\Security\PiiMask::name('수정담당자'),'account update privacy action links updated subject');
    $fault=new PDO('mysql:host='.blue_env('DB_HOST').';dbname='.blue_env('DB_NAME').';charset=utf8mb4','root',blue_env('DB_ROOT_PASSWORD'),[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);$trigger='qa_account_priv_'.bin2hex(random_bytes(5));$fault->exec("CREATE TRIGGER ".$trigger." BEFORE INSERT ON nb_admin_privacy_access FOR EACH ROW BEGIN IF NEW.actor_no=".$actor." AND NEW.entity='admin_account' THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='QA synthetic account privacy failure'; END IF; END");
    $before=accountPrivacyRow($target);$logBefore=count(accountPrivacyLogs());$q=$db->prepare('SELECT COUNT(*) FROM nb_admin_audit WHERE actor_no=?');$q->execute([$actor]);$auditBefore=(int)$q->fetchColumn();
    $input['uname']='실패담당자';accountPrivacyHttp($input);qa_expect(accountPrivacyRow($target)===$before,'privacy write failure rolls back account update entirely');
    accountPrivacyHttp(['mode'=>'delete','no'=>$target,'change_reason'=>'QA 실패 주입 삭제']);qa_expect(accountPrivacyRow($target)===$before,'privacy write failure rolls back account delete');
    $new=$input;$new['mode']='create';unset($new['no']);$new['uid']=$tag.'_blocked';$new['email']=$tag.'_blocked@example.com';$r=accountPrivacyHttp($new);$q=$db->prepare('SELECT COUNT(*) FROM nb_admin WHERE uid=?');$q->execute([$new['uid']]);qa_expect(empty($r[1]['success']) && (int)$q->fetchColumn()===0,'privacy write failure rolls back newly generated account and credentials');
    $q=$db->prepare('SELECT COUNT(*) FROM nb_admin_audit WHERE actor_no=?');$q->execute([$actor]);qa_expect((int)$q->fetchColumn()===$auditBefore && count(accountPrivacyLogs())===$logBefore,'failed account operations roll back both audit and privacy logs');
    $fault->exec('DROP TRIGGER '.$trigger);$trigger='';
    $r=accountPrivacyHttp(['mode'=>'delete','no'=>$target,'change_reason'=>'QA 계정 검증 완료 삭제']);qa_expect($r[0]===302 && accountPrivacyRow($target)===[],'HTTP managed account delete succeeds after logger recovery');
    $logs=accountPrivacyLogs();$log=end($logs);qa_expect($log && $log['action']==='delete' && (int)$log['target_no']===$target && $log['subject_label']===\Security\PiiMask::name('수정담당자'),'account delete privacy action retains former subject');
    $q=$db->prepare('SELECT * FROM nb_admin_audit WHERE actor_no=?');$q->execute([$actor]);$encoded=json_encode([$logs,$q->fetchAll(PDO::FETCH_ASSOC)],JSON_UNESCAPED_UNICODE);qa_expect($plain!=='' && strpos($encoded,$plain)===false && strpos($encoded,'temporary_password')===false,'privacy and audit records omit generated password plaintext');
} catch(Throwable $e){qa_expect(false,$e->getMessage());}
finally {
    if($trigger && isset($fault))$fault->exec('DROP TRIGGER IF EXISTS '.$trigger);
    if($actor){$db->prepare('DELETE FROM nb_admin_privacy_access WHERE actor_no=?')->execute([$actor]);$db->prepare('DELETE FROM nb_admin_audit WHERE actor_no=?')->execute([$actor]);}
    $db->prepare('DELETE FROM nb_admin WHERE uid IN (?,?,?)')->execute([$tag,$tag.'_target',$tag.'_blocked']);
    if($sid){$file=(session_save_path()?:sys_get_temp_dir()).'/sess_'.$sid;if(is_file($file))unlink($file);}
}
qa_done();
