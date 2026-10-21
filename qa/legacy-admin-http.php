<?php
// Isolated fixture only. HTTPS is simulated by the QA Apache config, not by a trusted request header in PHP.
if(PHP_SAPI!=='cli'||!is_file('/.dockerenv')||getenv('DB_USER')!=='qa_app'||getenv('QA_PRODUCTION_HTTP')!=='1')exit(1);
ob_start();session_name('BLUESQGATESESSID');
$sid='';$sessions=[];$pass=0;$fail=0;$id=0;
function legacyCheck($ok,$label){global $pass,$fail;$ok?$pass++:$fail++;echo($ok?'PASS ':'FAIL ').$label."\n";}
function legacyHttp($path,$data=null,$tls=true,$host=null,$remote=false){global $sid,$sessions;
 $h='Host: '.($host??getenv('PUBLIC_HOST'))."\r\n".($tls?"X-QA-HTTPS: 1\r\n":'')."Cookie: BLUESQGATESESSID=$sid\r\n";
 if($data!==null)$h.="Content-Type: application/x-www-form-urlencoded\r\n";
 $ctx=stream_context_create(['http'=>['method'=>$data===null?'GET':'POST','header'=>$h,'content'=>$data===null?'':http_build_query($data),'ignore_errors'=>true,'follow_location'=>0,'timeout'=>15]]);
 $b=(string)file_get_contents('http://'.($remote?gethostbyname(gethostname()):'127.0.0.1').$path,false,$ctx);$raw=$http_response_header;preg_match('/\s(\d{3})\s/',$raw[0],$m);
 foreach($raw as $h)if(preg_match('/^Set-Cookie: BLUESQGATESESSID=([^;]+)/i',$h,$c)){$sid=$c[1];$sessions[]=$sid;}
 return[(int)$m[1],$b,implode("\n",$raw)];
}
function legacyState($changes=[]){global $sid;if(session_status()===PHP_SESSION_ACTIVE)session_write_close();session_id($sid);session_start();foreach($changes as $k=>$v)$_SESSION[$k]=$v;$state=$_SESSION;session_write_close();chown((session_save_path()?:sys_get_temp_dir()).'/sess_'.$sid,'www-data');return $state;}
if(getenv('QA_EXPECT_HIDDEN')==='1'){
 [$s]=legacyHttp('/admin/');legacyCheck($s===404,'legacy flag false hides public HTTPS admin');
 [$s,$b]=legacyHttp('/',null,false,getenv('GATE_HOST'));legacyCheck($s===200&&strpos($b,'id="login_form"')!==false,'closing legacy access preserves HTTP gate');
 echo "$pass PASS / $fail FAIL\n";exit($fail?1:0);
}
$db=new PDO('mysql:host=db;dbname='.getenv('DB_NAME').';charset=utf8mb4',getenv('DB_USER'),getenv('DB_PASS'),[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
$uid='qa_legacy_'.bin2hex(random_bytes(5));$password='Qa!'.bin2hex(random_bytes(10));
try{
 $db->prepare("INSERT INTO nb_admin(sitekey,uid,upwd,uname,email,active_status,role_code,password_changed_at,last_login_at,created_at) VALUES('BLUESQ',?,?,?,?,'Y','super',NOW(),NOW(),NOW())")->execute([$uid,password_hash($password,PASSWORD_DEFAULT),'QA Legacy',$uid.'@example.com']);$id=(int)$db->lastInsertId();
 [$s,$html,$h]=legacyHttp('/admin/');legacyCheck($s===200&&strpos($html,'id="login_form"')!==false,'public HTTPS legacy login renders');
 legacyCheck((bool)preg_match('/Set-Cookie:[^\n]*;\s*secure/i',$h)&&stripos($h,'HttpOnly')!==false,'legacy HTTPS cookie is Secure and HttpOnly');
 preg_match('/name="_csrf"\s+value="([^"]+)"/',$html,$c);$csrf=$c[1]??'';legacyState(['captcha_secure'=>'12345']);
 [$s]=legacyHttp('/admin/lib/login/login.process.php',['uid'=>$uid,'upwd'=>$password,'r_captcha'=>'12345','_csrf'=>'invalid']);legacyCheck($s===403,'legacy login POST requires CSRF');
 [$s]=legacyHttp('/admin/lib/login/login.process.php',['uid'=>$uid,'upwd'=>$password,'r_captcha'=>'12345','_csrf'=>$csrf]);$state=legacyState();
 legacyCheck($s===302&&!empty($state['mfa_pending'])&&empty($state['no_adm_login_no']),'legacy credentials still require MFA');
 $pending=$state['mfa_pending']??[];$pending['code_hash']=password_hash('123456',PASSWORD_DEFAULT);$pending['sent_at']=time();legacyState(['mfa_pending'=>$pending]);$before=$sid;
 [$s]=legacyHttp('/admin/lib/login/mfa.process.php',['code'=>'123456','_csrf'=>$state['blue_csrf']??'']);$state=legacyState();legacyCheck($s===302&&(int)($state['no_adm_login_no']??0)===$id&&$before!==$sid,'legacy MFA verifies and rotates session');
 foreach(['/admin/pages/board/board.list.php','/admin/pages/account/index.php','/admin/pages/account/password.php','/admin/pages/design/banner.list.php','/admin/pages/design/popup.list.php','/admin/pages/works/index.php','/admin/pages/request/request.list.php']as $path){
  [$s,$html]=legacyHttp($path);legacyCheck($s===200&&!preg_match('/Fatal error|SQLSTATE\[|Uncaught|Warning:/',$html),'legacy authenticated screen '.$path);
 }
 [$s,$b,$h]=legacyHttp('/admin/pages/setting/setting.php');legacyCheck($s===302&&strpos($h,'Location: /admin/pages/account/password.php')!==false,'legacy retired password page redirects to current account screen');
 [$s]=legacyHttp('/admin/',null,true,null,true);legacyCheck($s===403,'legacy IP allowlist enforced');
 [$s]=legacyHttp('/admin/',null,true,'unknown.example');legacyCheck($s===404,'legacy unknown host rejected');
 [$s,$b,$h]=legacyHttp('/admin/',null,false);legacyCheck(in_array($s,[301,302,404],true)&&strpos($b,'id="login_form"')===false,'public legacy HTTP never serves login');
 [$s,$b]=legacyHttp('/');legacyCheck($s===200&&strpos($b,'id="login_form"')===false,'public HTTPS homepage unchanged');
 [$s,$b]=legacyHttp('/',null,false,getenv('GATE_HOST'));legacyCheck($s===200&&strpos($b,'id="login_form"')!==false,'gate HTTP remains directly usable without automatic fallback');
 [$s]=legacyHttp('/admin/lib/login/logout.php',['_csrf'=>$state['blue_csrf']??'']);legacyCheck(in_array($s,[200,302],true),'legacy CSRF logout succeeds');
 [$s,$b]=legacyHttp('/admin/pages/board/board.list.php');legacyCheck(in_array($s,[302,401],true),'legacy logged-out session denied');
}catch(Throwable $e){legacyCheck(false,'legacy exception (details redacted)');}
finally{if($id){foreach(['nb_admin_audit'=>'actor_no','nb_admin_privacy_access'=>'actor_no','nb_admin'=>'no']as $table=>$column)$db->prepare('DELETE FROM '.$table.' WHERE '.$column.'=?')->execute([$id]);}foreach(array_unique($sessions)as $session){$p=(session_save_path()?:sys_get_temp_dir()).'/sess_'.$session;if(is_file($p))unlink($p);}}
echo "$pass PASS / $fail FAIL\n";exit($fail?1:0);
