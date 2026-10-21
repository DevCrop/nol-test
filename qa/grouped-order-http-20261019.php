<?php
if(PHP_SAPI!=='cli'||!is_file('/.dockerenv')||getenv('DB_USER')!=='qa_app'||getenv('QA_PRODUCTION_HTTP')!=='1')exit(1);
ob_start();
require dirname(__DIR__).'/config/env.php';require dirname(__DIR__).'/inc/lib/session.boot.php';
$db=new PDO('mysql:host=db;dbname='.getenv('DB_NAME').';charset=utf8mb4',getenv('DB_USER'),getenv('DB_PASS'),[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
$pass=0;$fail=0;$actor=0;$uid='qa_order_'.bin2hex(random_bytes(4));$sid='';$csrf=bin2hex(random_bytes(32));
function orderCheck($v,$label){global $pass,$fail;$v?$pass++:$fail++;echo ($v?'PASS ':'FAIL ').$label."\n";}
function orderPost($path,$data,$badImage=false){
 global $sid,$csrf;$data['_csrf']=$csrf;$type='application/x-www-form-urlencoded';$body=http_build_query($data);
 if($badImage){$boundary='qa'.bin2hex(random_bytes(5));$type='multipart/form-data; boundary='.$boundary;$body='';
 foreach($data as $k=>$v)$body.="--$boundary\r\nContent-Disposition: form-data; name=\"$k\"\r\n\r\n$v\r\n";
 $body.="--$boundary\r\nContent-Disposition: form-data; name=\"popup_image\"; filename=\"fake.png\"\r\nContent-Type: image/png\r\n\r\nnot a png\r\n--$boundary--\r\n";}
 $ctx=stream_context_create(['http'=>['header'=>'Host: '.getenv('GATE_HOST')."\r\nCookie: ".session_name()."=$sid\r\nX-Requested-With: XMLHttpRequest\r\nContent-Type: $type\r\n",'method'=>'POST','content'=>$body,'ignore_errors'=>true,'follow_location'=>0]]);
 $raw=(string)file_get_contents('http://127.0.0.1/index.php'.$path,false,$ctx);return json_decode($raw,true)??[];
}
function orderRows($db,$table,$where,$params){$q=$db->prepare('SELECT * FROM '.$table.' WHERE '.$where.' ORDER BY '.($table==='nb_board'?'no':'id'));$q->execute($params);return $q->fetchAll(PDO::FETCH_ASSOC);}
try{
 $db->prepare("INSERT INTO nb_admin(sitekey,uid,upwd,uname,email,active_status,role_id,password_changed_at,last_login_at,created_at,login_token) VALUES('NOLTHE',?,?,?,?,'Y',1,NOW(),NOW(),NOW(),?)")->execute([$uid,password_hash(bin2hex(random_bytes(20)),PASSWORD_DEFAULT),'QA Order',$uid.'@mail.qa.test',$token=bin2hex(random_bytes(32))]);$actor=(int)$db->lastInsertId();
 $_SESSION=['_csrf'=>$csrf,'no_adm_login_no'=>$actor,'no_adm_login_uid'=>$uid,'no_adm_login_uname'=>'QA Order','no_adm_login_role_id'=>1,'no_adm_login_role'=>'superadmin','no_adm_login_token'=>$token,'no_adm_last_activity'=>time()];$sid=session_id();session_write_close();chown((session_save_path()?:sys_get_temp_dir()).'/sess_'.$sid,'www-data');
 $paths=['/'.$uid.'/a','/'.$uid.'/b','/'.$uid.'/c','/'.$uid.'/ties'];$ids=[];
 foreach($paths as $index=>$path){$ranks=$index===3?[1,1,2]:[1,2,3,4,5,6];foreach($ranks as $rank){
  $db->prepare("INSERT INTO nb_popups(title,popup_type,popup_path,sort_no,is_active,is_unlimited) VALUES(?,2,?,?,0,1)")->execute([$uid,$path,$rank]);$ids[$index][]=(int)$db->lastInsertId();
 }}
 $b=orderRows($db,'nb_popups','popup_path=?',[$paths[1]]);$c=orderRows($db,'nb_popups','popup_path=?',[$paths[2]]);
 $result=orderPost('/Controller/PopupController.php',['mode'=>'delete_array','ids'=>json_encode([$ids[0][1],$ids[0][4],$ids[0][1],2147483647])]);
 $rows=orderRows($db,'nb_popups','popup_path=?',[$paths[0]]);
 orderCheck(!empty($result['success'])&&array_map('intval',array_column($rows,'sort_no'))===[1,2,3,4],'popup noncontiguous bulk delete closes each actual position');
 orderCheck($b===orderRows($db,'nb_popups','popup_path=?',[$paths[1]])&&$c===orderRows($db,'nb_popups','popup_path=?',[$paths[2]]),'popup bulk delete preserves other paths including timestamps');
 $moving=$ids[0][5];$result=orderPost('/Controller/PopupController.php',['mode'=>'sort','id'=>$moving,'new_no'=>1]);
 $q=$db->prepare('SELECT sort_no FROM nb_popups WHERE id=?');$q->execute([$moving]);
 orderCheck(!empty($result['success'])&&(int)$q->fetchColumn()===1,'popup move within its path');
 orderCheck($b===orderRows($db,'nb_popups','popup_path=?',[$paths[1]]),'popup move leaves another path unchanged');
 $result=orderPost('/Controller/PopupController.php',['mode'=>'update','id'=>$moving,'title'=>$uid,'popup_type'=>2,'popup_path'=>$paths[1],'sort_no'=>2,'is_active'=>0]);
 $q=$db->prepare('SELECT popup_path,sort_no FROM nb_popups WHERE id=?');$q->execute([$moving]);$m=$q->fetch(PDO::FETCH_ASSOC);
 orderCheck(!empty($result['success'])&&$m['popup_path']===$paths[1]&&(int)$m['sort_no']===2,'popup path transfer inserts into target position');
 orderCheck($c===orderRows($db,'nb_popups','popup_path=?',[$paths[2]]),'popup path transfer preserves third path');
 $before=orderRows($db,'nb_popups','title=?',[$uid]);
 $result=orderPost('/Controller/PopupController.php',['mode'=>'update','id'=>$moving,'title'=>$uid,'popup_type'=>1,'popup_path'=>$paths[0],'sort_no'=>1],true);
 orderCheck(empty($result['success'])&&$before===orderRows($db,'nb_popups','title=?',[$uid]),'invalid image does not reorder or transfer existing popup');
 $result=orderPost('/Controller/PopupController.php',['mode'=>'delete','id'=>$ids[3][0]]);
 $rows=orderRows($db,'nb_popups','popup_path=?',[$paths[3]]);
 orderCheck(!empty($result['success'])&&array_map('intval',array_column($rows,'sort_no'))===[1,2],'deleting one preexisting tied rank creates no extra tie');

 $aNo=90000000+random_int(1000,100000);$bNo=$aNo+100001;
 foreach([['NOLTHE',$aNo],['NOLTHE',$bNo],['QAOTHR',$aNo]] as $g)foreach([1,2,3] as $rank){
  $db->prepare("INSERT INTO nb_board(sitekey,board_no,title,contents,write_name,sort_no) VALUES(?,?,?,'QA','QA',?)")->execute([$g[0],$g[1],$uid,$rank]);
 }
 $orig=orderRows($db,'nb_board','sitekey=? AND board_no=?',['NOLTHE',$aNo]);$other=orderRows($db,'nb_board','board_no=? OR sitekey=?',[$bNo,'QAOTHR']);
 $result=orderPost('/pages/board/ajax/board.process.php',['mode'=>'save','board_no'=>$aNo,'title'=>$uid.'_new','contents'=>'QA','write_name'=>'QA']);
 $q=$db->prepare('SELECT no FROM nb_board WHERE title=?');$q->execute([$uid.'_new']);$new=(int)$q->fetchColumn();
 orderCheck(($result['result']??'')==='success'&&$new>0,'board scoped create');
 $result=orderPost('/pages/board/ajax/board.process.php',['mode'=>'board.copy','no'=>$new]);
 $q->execute([$uid.'_new']);$newIds=$q->fetchAll(PDO::FETCH_COLUMN);
 orderCheck(($result['result']??'')==='success'&&count($newIds)===2,'board copy has its own first rank');
 orderPost('/pages/board/ajax/board.process.php',['mode'=>'delete.array','board_file_check_no'=>array_merge($newIds,[$new,2147483647])]);
 orderCheck($orig===orderRows($db,'nb_board','sitekey=? AND board_no=?',['NOLTHE',$aNo]),'board create copy bulk-delete restores original ranks and values');
 orderCheck($other===orderRows($db,'nb_board','board_no=? OR sitekey=?',[$bNo,'QAOTHR']),'board lifecycle preserves other board and site');
 $result=orderPost('/pages/board/ajax/board.process.php',['mode'=>'delete.array','board_file_check_no'=>[$orig[0]['no'],$orig[2]['no'],$orig[0]['no'],2147483647]]);
 $left=orderRows($db,'nb_board','sitekey=? AND board_no=?',['NOLTHE',$aNo]);
 orderCheck(($result['result']??'')==='success'&&count($left)===1&&(int)$left[0]['sort_no']===1,'board noncontiguous delete closes positions correctly');
 $result=orderPost('/pages/board/ajax/board.process.php',['mode'=>'edit','no'=>$left[0]['no'],'board_no'=>$bNo,'title'=>$uid,'contents'=>'QA','write_name'=>'QA']);
 orderCheck(($result['result']??'')==='success'&&count(orderRows($db,'nb_board','sitekey=? AND board_no=?',['NOLTHE',$aNo]))===0,'board edit transfer leaves source group clean');
 orderPost('/pages/board/ajax/board.process.php',['mode'=>'delete','no'=>$left[0]['no']]);
 orderCheck($other===orderRows($db,'nb_board','board_no=? OR sitekey=?',[$bNo,'QAOTHR']),'board transfer and removal restores target group and other site');
}catch(Throwable $e){orderCheck(false,'grouped-order QA exception (customer values redacted)');}
finally{
 foreach(['nb_popups','nb_board'] as $table)$db->prepare('DELETE FROM '.$table.' WHERE title LIKE ?')->execute([$uid.'%']);
 if($actor){
  $db->prepare('DELETE FROM nb_admin_privacy_access WHERE actor_no=?')->execute([$actor]);
  $db->prepare('DELETE FROM nb_admin_audit WHERE actor_no=?')->execute([$actor]);
  $db->prepare('DELETE FROM nb_admin WHERE no=? AND uid=?')->execute([$actor,$uid]);
 }
 if($sid){$f=(session_save_path()?:sys_get_temp_dir()).'/sess_'.$sid;if(is_file($f))unlink($f);}
}
echo "$pass PASS / $fail FAIL\n";exit($fail?1:0);
