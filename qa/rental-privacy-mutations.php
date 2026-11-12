<?php
ob_start();
require __DIR__.'/bootstrap.php';
require_once dirname(__DIR__).'/inc/lib/db.php';
if (!is_file('/.dockerenv') || APP_ENV !== 'development' || DB_USER !== 'qa_app') { echo "SKIP isolated Docker required\n"; exit; }
if (trim((string)blue_env('REQUEST_NOTIFY_EMAILS', '')) !== '') { echo "SKIP notification recipients must be empty (no SMTP delivery)\n"; exit; }
$db=DB::getInstance(); $uid='qa_rental_'.bin2hex(random_bytes(5)); $admin=0; $ids=[]; $sessions=[]; $trigger=''; $files=[];
function rentalHttp(string $path,array $data,string $sid,bool $public=false,bool $upload=false): array {
    $type='application/x-www-form-urlencoded';$content=http_build_query($data);
    if($upload){$boundary='qa'.bin2hex(random_bytes(8));$type='multipart/form-data; boundary='.$boundary;$content='';foreach($data as $k=>$v)$content.="--$boundary\r\nContent-Disposition: form-data; name=\"$k\"\r\n\r\n$v\r\n";$content.="--$boundary\r\nContent-Disposition: form-data; name=\"file_1\"; filename=\"qa.txt\"\r\nContent-Type: text/plain\r\n\r\nQA synthetic rental attachment\r\n--$boundary--\r\n";}
    if(!$public){$route=(string)blue_env('GATE_ROUTE_MODE','path');if($route==='php')$path='/index.php'.substr($path,6);elseif($route==='internal')$path=substr($path,6);}
    $ctx=stream_context_create(['http'=>['method'=>'POST','header'=>'Host: '.($public?blue_env('PUBLIC_HOST','localhost'):blue_env('GATE_HOST','gate.local'))."\r\nCookie: ".session_name().'='.$sid."\r\nX-Requested-With: XMLHttpRequest\r\nContent-Type: $type\r\n",'content'=>$content,'ignore_errors'=>true,'follow_location'=>0,'timeout'=>10]]);
    $body=file_get_contents('http://127.0.0.1'.$path,false,$ctx);preg_match('/\s(\d{3})\s/',$http_response_header[0]??'',$m);return [(int)($m[1]??0),json_decode((string)$body,true),(string)$body];
}
function rentalPublicSession(): array {
    if(session_status()===PHP_SESSION_ACTIVE)session_write_close();session_id('qarental'.bin2hex(random_bytes(10)));session_start();$_SESSION=[];$_SESSION['captcha_secure']='QAT123';$csrf=\Security\Csrf::token();$sid=session_id();session_write_close();if(posix_geteuid()===0)chown((session_save_path()?:sys_get_temp_dir()).'/sess_'.$sid,'www-data');$GLOBALS['sessions'][]=$sid;return [$sid,$csrf];
}
try {
    [$publicSid,$publicCsrf]=rentalPublicSession();
    $data=['_csrf'=>$publicCsrf,'r_captcha'=>'QAT123','performance_name'=>$uid,'organization_name'=>'QA','manager_name'=>'검증담당자','phone'=>'01012345678','email'=>$uid.'@example.com','contents'=>'QA synthetic rental'];
    $r=rentalHttp('/module/ajax/request.process.php',$data,$publicSid,true,true);
    $q=$db->prepare('SELECT * FROM nb_request WHERE performance_name=?');$q->execute([$uid]);$row=$q->fetch(PDO::FETCH_ASSOC);$id=(int)($row['no']??0);if($id)$ids[]=$id;if(!empty($row['file_1']))$files[]=dirname(__DIR__).'/uploads/board/'.$row['file_1'];
    qa_expect($r[0]===200 && ($r[1]['result']??'')==='success' && $id>0,'public HTTP rental create succeeds');
    qa_expect(!empty($row['file_1']) && is_file(end($files)) && $row['file_1_origin']==='qa.txt','public attachment persisted with original filename');
    $q=$db->prepare("SELECT * FROM nb_admin_privacy_access WHERE entity='request' AND target_no=? AND action='create'");$q->execute([$id]);$log=$q->fetch(PDO::FETCH_ASSOC);
    qa_expect($log && (int)$log['actor_no']===0 && $log['actor_uid']==='비회원' && $log['actor_ip']==='127.0.0.1' && $log['subject_label']===\Security\PiiMask::name('검증담당자'),'create privacy log links target, masked subject, anonymous actor and IP');
    $db->prepare("INSERT INTO nb_admin(sitekey,uid,upwd,uname,email,active_status,role_code,password_changed_at,menu_permissions) VALUES('BLUESQ',?,?,?,?,'Y','admin',NOW(),?)")->execute([$uid,password_hash(bin2hex(random_bytes(20)),PASSWORD_DEFAULT),'QA',$uid.'@example.com',json_encode(['rental'=>['view'=>true,'delete'=>true]])]);$admin=(int)$db->lastInsertId();
    session_id('qarental'.bin2hex(random_bytes(10)));session_start();$_SESSION=[];\Security\AdminAccount::establish(\Security\AdminAccount::findByNo($admin));$adminSid=session_id();$csrf=\Security\Csrf::token();session_write_close();$sessions[]=$adminSid;if(posix_geteuid()===0)chown((session_save_path()?:sys_get_temp_dir()).'/sess_'.$adminSid,'www-data');
    $faultDb=new PDO('mysql:host='.blue_env('DB_HOST').';dbname='.blue_env('DB_NAME').';charset=utf8mb4','root',blue_env('DB_ROOT_PASSWORD'),[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);$trigger='qa_rental_'.bin2hex(random_bytes(5));
    $faultDb->exec("CREATE TRIGGER ".$trigger." BEFORE INSERT ON nb_admin_privacy_access FOR EACH ROW BEGIN IF NEW.entity='request' AND (NEW.actor_no=".$admin." OR NEW.actor_no=0) THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='QA synthetic rental privacy failure'; END IF; END");
    $r=rentalHttp('/admin/pages/request/ajax/request.process.php',['mode'=>'delete','no'=>$id,'_csrf'=>$csrf],$adminSid);$q=$db->prepare('SELECT COUNT(*) FROM nb_request WHERE no=?');$q->execute([$id]);qa_expect(($r[1]['result']??'')==='fail' && (int)$q->fetchColumn()===1 && is_file(end($files)),'privacy logging failure prevents authorized deletion and preserves attachment');
    [$sid,$token]=rentalPublicSession();$data['_csrf']=$token;$data['performance_name']=$uid.'_failed';$before=glob(dirname(__DIR__).'/uploads/board/file_*')?:[];
    $r=rentalHttp('/module/ajax/request.process.php',$data,$sid,true,true);$q=$db->prepare('SELECT COUNT(*) FROM nb_request WHERE performance_name=?');$q->execute([$data['performance_name']]);$after=glob(dirname(__DIR__).'/uploads/board/file_*')?:[];
    qa_expect(($r[1]['result']??'')==='fail' && (int)$q->fetchColumn()===0 && !array_diff($after,$before),'privacy logging failure blocks public insertion and removes only its new attachment');
    $faultDb->exec('DROP TRIGGER '.$trigger);$trigger='';
    $trigger='qa_rental_attach_'.bin2hex(random_bytes(5));
    $faultDb->exec("CREATE TRIGGER ".$trigger." BEFORE UPDATE ON nb_admin_privacy_access FOR EACH ROW BEGIN IF OLD.entity='request' AND OLD.action='create' AND OLD.actor_no=0 AND OLD.target_no=0 THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='QA synthetic target link failure'; END IF; END");
    [$sid,$token]=rentalPublicSession();$data['_csrf']=$token;$data['performance_name']=$uid.'_attach_failed';$before=glob(dirname(__DIR__).'/uploads/board/file_*')?:[];
    $r=rentalHttp('/module/ajax/request.process.php',$data,$sid,true,true);$q=$db->prepare('SELECT COUNT(*) FROM nb_request WHERE performance_name=?');$q->execute([$data['performance_name']]);$after=glob(dirname(__DIR__).'/uploads/board/file_*')?:[];
    qa_expect(($r[1]['result']??'')==='fail' && (int)$q->fetchColumn()===0 && !array_diff($after,$before),'post-insert log-link failure compensates MyISAM row and uploaded attachment');
    $faultDb->exec('DROP TRIGGER '.$trigger);$trigger='';
    $r=rentalHttp('/admin/pages/request/ajax/request.process.php',['mode'=>'delete','no'=>$id,'_csrf'=>$csrf],$adminSid);$q=$db->prepare('SELECT COUNT(*) FROM nb_request WHERE no=?');$q->execute([$id]);qa_expect($r[0]===200 && ($r[1]['result']??'')==='success' && (int)$q->fetchColumn()===0,'ordinary authorized admin deletes through HTTP');
    $q=$db->prepare("SELECT * FROM nb_admin_privacy_access WHERE entity='request' AND target_no=? AND action='delete'");$q->execute([$id]);$log=$q->fetch(PDO::FETCH_ASSOC);qa_expect($log && (int)$log['actor_no']===$admin && $log['actor_uid']===$uid && $log['actor_ip']==='127.0.0.1' && $log['subject_label']===\Security\PiiMask::name('검증담당자'),'delete privacy record links administrator, subject, action and IP');
    $r=rentalHttp('/admin/pages/request/ajax/request.process.php',['mode'=>'delete','no'=>$id,'_csrf'=>$csrf],$adminSid);qa_expect(($r[1]['result']??'')==='fail','missing rental deletion does not falsely succeed');
} catch(Throwable $e){qa_expect(false,$e->getMessage());}
finally {
    if($trigger && isset($faultDb))$faultDb->exec('DROP TRIGGER IF EXISTS '.$trigger);
    foreach($ids as $id){$db->prepare('DELETE FROM nb_request WHERE no=? AND performance_name=?')->execute([$id,$uid]);$db->prepare("DELETE FROM nb_admin_privacy_access WHERE entity='request' AND target_no=?")->execute([$id]);}
    if($admin){$db->prepare('DELETE FROM nb_admin_audit WHERE actor_no=?')->execute([$admin]);$db->prepare('DELETE FROM nb_admin WHERE no=? AND uid=?')->execute([$admin,$uid]);}
    foreach($files as $file)if(is_file($file))unlink($file);
    foreach($sessions as $sid){$file=(session_save_path()?:sys_get_temp_dir()).'/sess_'.$sid;if(is_file($file))unlink($file);}
}
qa_done();
