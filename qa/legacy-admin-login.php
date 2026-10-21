<?php
if(PHP_SAPI!=='cli'||!is_file('/.dockerenv')||getenv('DB_USER')!=='qa_app'||getenv('QA_PRODUCTION_HTTP')!=='1')exit(1);
ob_start();session_name('NOLGATESESSID');
$db=new PDO('mysql:host=db;dbname='.getenv('DB_NAME').';charset=utf8mb4',getenv('DB_USER'),getenv('DB_PASS'),[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
$uid='qa_legacy_'.bin2hex(random_bytes(5));$password='Qa!'.bin2hex(random_bytes(10));$sid='';$id=0;$fail=0;$pass=0;$sessions=[];
function legacyCheck($ok,$label){global $pass,$fail;$ok?$pass++:$fail++;echo ($ok?'PASS ':'FAIL ').$label."\n";}
function legacyHttp($path,$data=null,$tls=true,$host=null,$remote=false){global $sid,$sessions;
 $headers='Host: '.($host??getenv('PUBLIC_HOST'))."\r\n".($tls?"X-QA-HTTPS: 1\r\n":'')."Cookie: NOLGATESESSID=$sid\r\n";
 if($data!==null)$headers.="Content-Type: application/x-www-form-urlencoded\r\n";
 $ctx=stream_context_create(['http'=>['method'=>$data===null?'GET':'POST','header'=>$headers,'content'=>$data===null?'':http_build_query($data),'ignore_errors'=>true,'follow_location'=>0]]);
 $body=(string)file_get_contents('http://'.($remote?gethostbyname(gethostname()):'127.0.0.1').$path,false,$ctx);$raw=$http_response_header;preg_match('/\s(\d{3})\s/',$raw[0],$m);
 foreach($raw as $h)if(preg_match('/^Set-Cookie: NOLGATESESSID=([^;]+)/i',$h,$c)){$sid=$c[1];$sessions[]=$sid;}
 return[(int)$m[1],$body,implode("\n",$raw)];
}
function legacyState($changes=[]){global $sid;if(session_status()===PHP_SESSION_ACTIVE)session_write_close();session_id($sid);session_start();foreach($changes as $k=>$v)$_SESSION[$k]=$v;$state=$_SESSION;session_write_close();chown((session_save_path()?:sys_get_temp_dir()).'/sess_'.$sid,'www-data');return $state;}
try{
 $db->prepare("INSERT INTO nb_admin(sitekey,uid,upwd,uname,email,active_status,role_id,password_changed_at,last_login_at,created_at) VALUES('NOLTHE',?,?,?,?,'Y',1,NOW(),NOW(),NOW())")->execute([$uid,password_hash($password,PASSWORD_DEFAULT),'QA Legacy',$uid.'@mail.qa.test']);$id=(int)$db->lastInsertId();
 [$s,$html,$h]=legacyHttp('/nol-gate/');legacyCheck($s===200&&strpos($html,'id="login_form"')!==false,'public HTTPS legacy login renders');
 legacyCheck((bool)preg_match('/Set-Cookie:[^\n]*;\s*secure/i',$h)&&stripos($h,'HttpOnly')!==false,'legacy HTTPS cookie remains Secure and HttpOnly');
 preg_match('/name="_csrf"\s+value="([^"]+)"/',$html,$c);$csrf=$c[1]??'';legacyState(['captcha_secure'=>'12345']);
 [$s,$body,$h]=legacyHttp('/nol-gate/lib/login/login.process.php',['uid'=>$uid,'upwd'=>$password,'r_captcha'=>'12345','_csrf'=>$csrf]);
 $state=legacyState();legacyCheck($s===302&&!empty($state['mfa_pending'])&&empty($state['no_adm_login_no']),'legacy credential POST still requires MFA');
 $pending=$state['mfa_pending']??[];$pending['code_hash']=password_hash('123456',PASSWORD_DEFAULT);$pending['sent_at']=time();legacyState(['mfa_pending'=>$pending]);$before=$sid;
 [$s]=legacyHttp('/nol-gate/lib/login/mfa.process.php',['mfa_code'=>'123456','_csrf'=>$state['_csrf']??'']);
 $state=legacyState();legacyCheck($s===302&&(int)($state['no_adm_login_no']??0)===$id&&$before!==$sid,'legacy MFA verifies and rotates session');
 [$s,$html]=legacyHttp('/nol-gate/pages/board/board.list.php');legacyCheck($s===200&&strpos($html,'no-wrap')!==false,'legacy authenticated session reads administrator screen');
 [$s]=legacyHttp('/nol-gate/pages/account/index.php',null,false);legacyCheck($s===404,'legacy HTTP remains hidden');
 [$s]=legacyHttp('/nol-gate/',null,true,'unknown.example');legacyCheck($s===404,'legacy unknown host remains hidden');
 [$s]=legacyHttp('/nol-gate/',null,true,null,true);legacyCheck($s===403,'legacy unregistered IP remains denied');
 [$s,$html]=legacyHttp('/');legacyCheck($s===200&&strpos($html,'id="login_form"')===false,'public HTTPS homepage is unchanged');
 [$s]=legacyHttp('/nol-gate/lib/login/logout.php',['_csrf'=>$state['_csrf']??'']);legacyCheck(in_array($s,[200,302],true),'legacy CSRF logout succeeds');
 [$s]=legacyHttp('/nol-gate/pages/board/board.list.php');legacyCheck(in_array($s,[302,401],true),'legacy logged-out session denied');
}catch(Throwable $e){legacyCheck(false,'legacy login exception (details redacted)');}
finally{if($id){foreach(['nb_admin_audit'=>'actor_no','nb_admin_privacy_access'=>'actor_no','nb_admin_acl'=>'admin_no','nb_admin'=>'no']as $table=>$column)$db->prepare('DELETE FROM '.$table.' WHERE '.$column.'=?')->execute([$id]);}foreach(array_unique($sessions)as $session){$p=(session_save_path()?:sys_get_temp_dir()).'/sess_'.$session;if(is_file($p))unlink($p);}}
echo "$pass PASS / $fail FAIL\n";exit($fail?1:0);
