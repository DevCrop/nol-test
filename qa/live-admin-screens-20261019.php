<?php
if(PHP_SAPI!=='cli'||!is_file('/.dockerenv')||getenv('DB_USER')!=='qa_app'||getenv('QA_PRODUCTION_HTTP')!=='1')exit(1);
ob_start();
require dirname(__DIR__).'/config/env.php';
require dirname(__DIR__).'/inc/lib/session.boot.php';
$db=new PDO('mysql:host=db;dbname='.getenv('DB_NAME').';charset=utf8mb4',getenv('DB_USER'),getenv('DB_PASS'),[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
$pass=0;$fail=0;$actor=0;$created=0;$sid='';$uid='qa_live_'.bin2hex(random_bytes(4));$csrf=bin2hex(random_bytes(32));
function screenCheck($ok,$label){global $pass,$fail;$ok?$pass++:$fail++;echo ($ok?'PASS ':'FAIL ').$label."\n";}
function liveHttp($path,$data=null,$token=true){
 global $sid,$csrf;
 $legacy=getenv('QA_LEGACY_ADMIN')==='1';
 $headers='Host: '.getenv($legacy?'PUBLIC_HOST':'GATE_HOST')."\r\n".($legacy?"X-QA-HTTPS: 1\r\n":'')."Cookie: ".session_name().'='.$sid."\r\n";
 if($data!==null){$headers.="Content-Type: application/x-www-form-urlencoded\r\nX-Requested-With: XMLHttpRequest\r\n";if($token)$data['_csrf']=$csrf;}
 $ctx=stream_context_create(['http'=>['header'=>$headers,'method'=>$data===null?'GET':'POST','content'=>$data===null?'':http_build_query($data),'ignore_errors'=>true,'follow_location'=>0,'timeout'=>15]]);
 $body=(string)file_get_contents('http://127.0.0.1'.($legacy?'/nol-gate':'/index.php').$path,false,$ctx);
 preg_match('/\s(\d{3})\s/',$http_response_header[0]??'',$m);return[(int)($m[1]??0),$body,json_decode($body,true)];
}
try{
 $db->prepare("INSERT INTO nb_admin(sitekey,uid,upwd,uname,email,active_status,role_id,password_changed_at,last_login_at,created_at,login_token) VALUES('NOLTHE',?,?,?,?,'Y',1,NOW(),NOW(),NOW(),?)")
  ->execute([$uid,password_hash(bin2hex(random_bytes(24)),PASSWORD_DEFAULT),'QA Actor',$uid.'@mail.qa.test',$token=bin2hex(random_bytes(32))]);
 $actor=(int)$db->lastInsertId();
 $_SESSION=['_csrf'=>$csrf,'no_adm_login_no'=>$actor,'no_adm_login_uid'=>$uid,'no_adm_login_uname'=>'QA Actor','no_adm_login_role_id'=>1,'no_adm_login_role'=>'superadmin','no_adm_login_token'=>$token,'no_adm_last_activity'=>time()];
 $sid=session_id();session_write_close();chown((session_save_path()?:sys_get_temp_dir()).'/sess_'.$sid,'www-data');
 $paths=['/pages/account/index.php','/pages/account/new.php','/pages/account/access.php','/pages/account/audit.php','/pages/account/edit.php?no=1',
 '/pages/board/board.list.php','/pages/works/index.php','/pages/works/new.php','/pages/design/banner.list.php','/pages/design/banner.new.php',
 '/pages/design/popup.list.php','/pages/design/popup.new.php','/pages/faq/index.php','/pages/faq/new.php','/pages/privacy/index.php','/pages/privacy/new.php',
 '/pages/inquiry/index.php','/pages/inquiry/setting.php','/pages/setting/index.php','/pages/setting/pwd.php','/pages/setting/seo.php',
 '/pages/setting/external.tag.php','/pages/log/log.day.php','/pages/log/log.month.php','/pages/log/log.year.php'];
 foreach(['nb_works'=>'/pages/works/edit.php?id=','nb_banners'=>'/pages/design/banner.edit.php?id=','nb_popups'=>'/pages/design/popup.edit.php?id=',
 'nb_faqs'=>'/pages/faq/edit.php?id=','nb_privacy_policy'=>'/pages/privacy/edit.php?id=','nb_branch_seos'=>'/pages/setting/seo.edit.php?id='] as $table=>$path){
  $id=(int)$db->query('SELECT MIN(id) FROM `'.$table.'`')->fetchColumn();if($id)$paths[]=$path.$id;
 }
 foreach($paths as $path){
  [$status,$html]=liveHttp($path);
  screenCheck($status===200&&strpos($html,'no-wrap')!==false&&!preg_match('/Fatal error|SQLSTATE\[|Uncaught|Warning:/',$html),'existing-data administrator screen '.$path);
 }
 $path='/Controller/AccountController.php';
 $password='Qa!'.bin2hex(random_bytes(9));
 $data=['mode'=>'save','uid'=>$uid.'_m','uname'=>'QA Managed','email'=>$uid.'managed@mail.qa.test','phone'=>'','upwd'=>$password,'upwd_confirm'=>$password,'active_status'=>'Y','role_id'=>2,'acl'=>['board'=>['view'=>1]]];
 [$status,$body,$json]=liveHttp($path,$data,false);screenCheck($status===403,'account create missing CSRF denied');
 [$status,$body,$json]=liveHttp($path,$data);$created=(int)($json['id']??0);
 screenCheck($status===200&&!empty($json['success'])&&$created>0,'actual HTTP account create and ACL');
 if($created){
  [$status,$html]=liveHttp('/pages/account/edit.php?no='.$created);screenCheck($status===200&&strpos($html,'no-wrap')!==false,'created account read UI');
  unset($data['upwd'],$data['upwd_confirm']);$data['mode']='update';$data['no']=$created;$data['uname']='QA Updated';
  [$status,$body,$json]=liveHttp($path,$data);screenCheck($status===200&&!empty($json['success']),'actual HTTP account update');
  $q=$db->prepare('SELECT uname,upwd FROM nb_admin WHERE no=?');$q->execute([$created]);$row=$q->fetch(PDO::FETCH_ASSOC);
  screenCheck($row['uname']==='QA Updated'&&password_verify($password,$row['upwd']),'account change persisted without replacing password');
  [$status,$body,$json]=liveHttp($path,['mode'=>'delete','id'=>$created]);screenCheck($status===200&&!empty($json['success']),'actual HTTP account delete');
  $q=$db->prepare('SELECT COUNT(*) FROM nb_admin WHERE no=?');$q->execute([$created]);screenCheck((int)$q->fetchColumn()===0,'deleted account absent');
  $q=$db->prepare("SELECT action FROM nb_admin_audit WHERE actor_no=? AND entity='account' AND target_no=? ORDER BY no");$q->execute([$actor,$created]);
  screenCheck($q->fetchAll(PDO::FETCH_COLUMN)===['create','update','delete'],'account CRUD audit correct');
 }
}catch(Throwable $e){screenCheck(false,'isolated screen/account QA exception (customer details redacted)');}
finally{
 if($actor){
  $db->prepare('DELETE FROM nb_admin_privacy_access WHERE actor_no=?')->execute([$actor]);
  $db->prepare('DELETE FROM nb_admin_audit WHERE actor_no=?')->execute([$actor]);
  foreach([$created,$actor] as $id)if($id){
   $db->prepare('DELETE FROM nb_admin_acl WHERE admin_no=?')->execute([$id]);
   $db->prepare('DELETE FROM nb_admin WHERE no=? AND uid LIKE ?')->execute([$id,$uid.'%']);
  }
 }
 if($sid){$file=(session_save_path()?:sys_get_temp_dir()).'/sess_'.$sid;if(is_file($file))unlink($file);}
}
echo "$pass PASS / $fail FAIL\n";exit($fail?1:0);
