<?php
if(PHP_SAPI!=='cli'||!is_file('/.dockerenv'))exit(1);
require dirname(__DIR__).'/deploy/preflight.php';
$pass=0;$fail=0;
function deploymentCheck($ok,$label){global $pass,$fail;if($ok){$pass++;echo "PASS $label\n";}else{$fail++;echo "FAIL $label\n";}}
foreach([false,true] as $blue){
 $domain=$blue?'bluesquare.kr':'noltheater-daehakro.com';
 $settings=['APP_ENV'=>'production','GATE_HOST'=>'gate.'.$domain,'PUBLIC_HOST'=>$domain,'GATE_ROUTE_MODE'=>'php','GATE_ENFORCE'=>'true',
 'GATE_HTTP_PREVIEW'=>'false','GATE_ALLOW_IPS'=>'203.0.113.5,192.0.2.0/24','SESSION_SECURE'=>'true','SESSION_DOMAIN'=>'','SESSION_SAMESITE'=>'Lax',
 'SESSION_NAME'=>$blue?'BLUESQGATESESSID':'NOLGATESESSID','MFA_ENABLED'=>'true','SMTP_HOST'=>'smtp.gmail.com','SMTP_PORT'=>'465',
 'SMTP_USER'=>'qa@example.invalid','SMTP_PASS'=>'qa-not-a-real-password','SMTP_FROM'=>$blue?'bluesquare@nol-theater.com':'daehakro@nol-theater.com',
 'DB_HOST'=>'qa-db.invalid','DB_USER'=>'deployment_fixture','DB_PASS'=>'qa-not-a-real-password','DB_NAME'=>$blue?'dbusrbluesq0211':'dbusrdaehakro0605'];
 deploymentCheck(!in_array(false,DeploymentPreflight::configuration($settings,$blue),true),'complete project configuration '.($blue?'blue':'nol'));
 $httpSettings=$settings;$httpSettings['GATE_ALLOW_HTTP']='true';
 deploymentCheck(!in_array(false,DeploymentPreflight::configuration($httpSettings,$blue),true),'explicit HTTP exception retains IP/MFA/secure baseline configuration');
 foreach(['APP_ENV'=>'development','GATE_HOST'=>'gate.other.invalid','GATE_ROUTE_MODE'=>'internal','GATE_HTTP_PREVIEW'=>'true',
 'GATE_ALLOW_IPS'=>'','SESSION_SECURE'=>'false','MFA_ENABLED'=>'false','SMTP_PASS'=>'','DB_NAME'=>'wrong','DB_USER'=>'qa_app'] as $k=>$v){
  $bad=$settings;$bad[$k]=$v;deploymentCheck(in_array(false,DeploymentPreflight::configuration($bad,$blue),true),'reject unsafe '.$k.' '.($blue?'blue':'nol'));
 }
 foreach(['203.0.113.1/33','203.0.113.1/-1','not-an-ip','203.0.113.999','203.0.113.1/24/extra'] as $ip){
  $bad=$settings;$bad['GATE_ALLOW_IPS']=$ip;
  deploymentCheck(!DeploymentPreflight::configuration($bad,$blue)['admin_ip_rules_configured'],'invalid IP/CIDR rejected');
 }
}
ob_start();$result=DeploymentPreflight::run(dirname(__DIR__),'not-a-phase');$out=ob_get_clean();
deploymentCheck($result===2,'invalid command rejected');
deploymentCheck(strpos($out,'qa-not-a-real-password')===false,'credentials never printed');
echo "$pass PASS / $fail FAIL\n";exit($fail?1:0);
