<?php
if(PHP_SAPI!=='cli'||!is_file('/.dockerenv'))exit(1);
ob_start();require dirname(__DIR__).'/config/env.php';require dirname(__DIR__).'/inc/lib/session.boot.php';
if(env('APP_ENV')!=='development'||env('DB_USER')!=='qa_app')exit(1);
require dirname(__DIR__).'/src/Database/DB.php';require dirname(__DIR__).'/inc/lib/db.php';
foreach(['HOST','PORT','NAME','USER','PASS'] as $key)define('DB_'.$key,env('DB_'.$key));define('DB_CHARSET','utf8mb4');
require dirname(__DIR__).'/nol-gate/lib/DisplayStatus.php';require dirname(__DIR__).'/nol-gate/lib/PublicSiteLink.php';
$db=DB::getInstance();$tag='qa_ui30_'.bin2hex(random_bytes(4));$ids=['nb_banners'=>[],'nb_popups'=>[],'nb_works'=>[]];$original=[];$admin=0;$pass=0;$fail=0;
function request30(string $path,string $method='GET',array $data=[],?string $token=null):array{
 global $sid,$csrf;
 if($method==='POST')$data['_csrf']=$token??$csrf;
 $headers='Host: '.env('GATE_HOST','gate.local')."\r\nCookie: ".session_name().'='.$sid."\r\n";
 if($method==='POST')$headers.="Content-Type: application/x-www-form-urlencoded\r\nX-Requested-With: XMLHttpRequest\r\n";
 $ctx=stream_context_create(['http'=>['method'=>$method,'header'=>$headers,'content'=>$method==='POST'?http_build_query($data):'','ignore_errors'=>true,'follow_location'=>0,'timeout'=>15]]);
 $body=(string)file_get_contents('http://127.0.0.1/index.php'.$path,false,$ctx);preg_match('/\s(\d{3})\s/',$http_response_header[0]??'',$m);
 return [(int)($m[1]??0),$body,json_decode($body,true)];
}
function expect30(bool $ok,string $label):void{global $pass,$fail;$ok?$pass++:$fail++;echo ($ok?'PASS ':'FAIL ').$label.PHP_EOL;}
try{
 $token=bin2hex(random_bytes(32));$db->prepare("INSERT INTO nb_admin(sitekey,uid,upwd,uname,email,active_status,role_id,password_changed_at,last_login_at,created_at,login_token) VALUES('NOLTHE',?,?,?,?,'Y',1,NOW(),NOW(),NOW(),?)")->execute([$tag,password_hash(bin2hex(random_bytes(24)),PASSWORD_DEFAULT),'화면 QA',$tag.'@example.com',$token]);$admin=(int)$db->lastInsertId();
 $_SESSION=['_csrf'=>$csrf=bin2hex(random_bytes(32)),'no_adm_login_no'=>$admin,'no_adm_login_uid'=>$tag,'no_adm_login_uname'=>'화면 QA','no_adm_login_role_id'=>1,'no_adm_login_role'=>'superadmin','no_adm_login_token'=>$token,'no_adm_last_activity'=>time()];
 $sid=session_id();session_write_close();if(posix_geteuid()===0)chown((session_save_path()?:sys_get_temp_dir()).'/sess_'.$sid,'www-data');
 foreach(['nb_banners'=>'sort_no','nb_popups'=>'sort_no','nb_works'=>'sort_order'] as $table=>$column)$original[$table]=$db->query("SELECT * FROM {$table}")->fetchAll(PDO::FETCH_ASSOC);
 $base=(int)$db->query('SELECT COALESCE(MAX(sort_no),0) FROM nb_banners')->fetchColumn();
 foreach(['expired','off','unlimited','scheduled'] as $i=>$state){
  $db->prepare("INSERT INTO nb_banners(title,banner_type,is_active,is_unlimited,display_start_at,display_end_at,sort_no) VALUES(?,1,?,?,DATE_SUB(CURDATE(),INTERVAL 3 DAY),DATE_SUB(CURDATE(),INTERVAL 1 DAY),?)")->execute([$tag.'_'.$state,$state==='off'?0:1,$state==='unlimited'?1:2,$base+$i+1]);$ids['nb_banners'][]=(int)$db->lastInsertId();
 }
 $db->prepare('UPDATE nb_banners SET display_start_at=DATE_ADD(CURDATE(),INTERVAL 1 DAY),display_end_at=DATE_ADD(CURDATE(),INTERVAL 3 DAY) WHERE id=?')->execute([$ids['nb_banners'][3]]);
 [$status,$html]=request30('/pages/design/banner.list.php?searchKeyword='.$tag);
 expect30($status===200&&strpos($html,'기간 종료')!==false&&strpos($html,'노출 예정')!==false,'banner list reflects expired and scheduled public display state');
 [$status,$filtered]=request30('/pages/design/banner.list.php?searchKeyword='.$tag.'&is_active=1');
 expect30($status===200&&strpos($filtered,$tag.'_unlimited')!==false&&strpos($filtered,$tag.'_expired')===false,'active filter uses actual display eligibility');
 [$status,,$a]=request30('/Controller/BannerController.php','POST',['mode'=>'sort','id'=>$ids['nb_banners'][0],'direction'=>'down']);
 [$status2,,$b]=request30('/Controller/BannerController.php','POST',['mode'=>'sort','id'=>$ids['nb_banners'][0],'direction'=>'down']);
 expect30($status===200&&$status2===200&&!empty($a['success'])&&!empty($b['success'])&&$b['position']===$a['position']+1,'two successive banner moves use fresh server positions');
 [$csrfStatus]=request30('/Controller/BannerController.php','POST',['mode'=>'sort','id'=>$ids['nb_banners'][0],'direction'=>'up'],'invalid');
 expect30($csrfStatus===403,'sorting still rejects invalid CSRF');
 foreach([0,1,2] as $i){
  $db->prepare("INSERT INTO nb_popups(title,popup_type,popup_path,is_active,is_unlimited,start_at,end_at,sort_no) VALUES(?,1,?,1,0,DATE_SUB(NOW(),INTERVAL 3 DAY),DATE_SUB(NOW(),INTERVAL 1 DAY),?)")->execute([$tag.'_popup'.$i,'/'.$tag,$i+1]);$ids['nb_popups'][]=(int)$db->lastInsertId();
 }
 [$status,$html]=request30('/pages/design/popup.list.php?searchKeyword='.$tag);
 expect30($status===200&&strpos($html,'기간 종료')!==false,'popup list reflects timestamp expiry');
 [$status,,$a]=request30('/Controller/PopupController.php','POST',['mode'=>'sort','id'=>$ids['nb_popups'][0],'direction'=>'down']);
 [$status2,,$b]=request30('/Controller/PopupController.php','POST',['mode'=>'sort','id'=>$ids['nb_popups'][0],'direction'=>'down']);
 expect30(!empty($a['success'])&&!empty($b['success'])&&$a['position']===2&&$b['position']===3,'popup ordering supports repeated moves within its page');
 foreach([1,2] as $i){
  [$status,,$result]=request30('/pages/works/process.php','POST',['mode'=>'insert','title'=>$tag.'_work'.$i,'venue'=>1,'genre'=>1,'is_published'=>1,'sort_order'=>$i,'start_date'=>'2026-01-01','end_date'=>'2027-01-01']);
  if(!empty($result['id']))$ids['nb_works'][]=(int)$result['id'];
 }
 [$status,$html]=request30('/pages/works/index.php?searchColumn=title&searchKeyword='.$tag);
 expect30($status===200&&strpos($html,$tag.'_work2')<strpos($html,$tag.'_work1'),'works default list shows newest registration first');
 [$status,$manual]=request30('/pages/works/index.php?searchColumn=title&searchKeyword='.$tag.'&list_order=display');
 expect30($status===200&&strpos($manual,$tag.'_work1')<strpos($manual,$tag.'_work2'),'manual public-display order remains available');
 expect30(strpos($html,'previewImage.js?v=20261030')!==false,'file preview module is loaded in administrator pages');
 $home=PublicSiteLink::home('noltheater-daehakro.com',false);
 expect30($home==='https://noltheater-daehakro.com/'&&PublicSiteLink::home('localhost:8618',true)==='http://localhost:8618/','homepage URL keeps public HTTPS separate from development gate HTTP');
 expect30(PublicSiteLink::home('evil.example/redirect',false)===$home,'homepage URL rejects malformed host configuration');
 expect30(PublicSiteLink::home('localhost',true,8618)==='http://localhost:8618/'&&PublicSiteLink::home('localhost:9000',true,8618)==='http://localhost:9000/'&&PublicSiteLink::home('noltheater-daehakro.com',false,8618)===$home,'development homepage preserves incoming port without changing explicit or production ports');
}finally{
 foreach($ids as $table=>$values){if($values){$marks=implode(',',array_fill(0,count($values),'?'));$db->prepare("DELETE FROM {$table} WHERE id IN ({$marks})")->execute($values);}}
 foreach($original as $table=>$rows){$column=$table==='nb_works'?'sort_order':'sort_no';foreach($rows as $row){$hasUpdated=array_key_exists('updated_at',$row);$sql="UPDATE {$table} SET {$column}=?".($hasUpdated?',updated_at=?':'').' WHERE id=?';$values=[$row[$column]];if($hasUpdated)$values[]=$row['updated_at'];$values[]=$row['id'];$db->prepare($sql)->execute($values);}}
 if($admin){$db->prepare('DELETE FROM nb_admin_audit WHERE actor_no=?')->execute([$admin]);$db->prepare('DELETE FROM nb_admin_privacy_access WHERE actor_no=?')->execute([$admin]);$db->prepare('DELETE FROM nb_admin WHERE no=? AND uid=?')->execute([$admin,$tag]);}
 if(isset($sid)){ $file=(session_save_path()?:sys_get_temp_dir()).'/sess_'.$sid;if(is_file($file))unlink($file);}
}
echo "RESULT {$pass} PASS / {$fail} FAIL\n";exit($fail?1:0);
