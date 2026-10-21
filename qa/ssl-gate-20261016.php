<?php
if(PHP_SAPI!=='cli'||!is_file('/.dockerenv')||getenv('DB_USER')!=='qa_app')exit(1);
$host=getenv('GATE_HOST');$fail=0;$pass=0;
function sslReq($secure=false,$path='/',$extra=''){
 global $host;$h="Host: $host\r\n".($secure?"X-QA-HTTPS: 1\r\n":'').$extra;
 $ctx=stream_context_create(['http'=>['header'=>$h,'ignore_errors'=>true,'follow_location'=>0,'timeout'=>10]]);
 $b=file_get_contents('http://127.0.0.1'.$path,false,$ctx);$headers=implode("\n",$http_response_header??[]);preg_match('/\s(\d{3})\s/',$http_response_header[0]??'',$m);return[(int)($m[1]??0),(string)$b,$headers];
}
function sslCheck($v,$label){global $fail,$pass;if($v){$pass++;echo "PASS $label\n";}else{$fail++;echo "FAIL $label\n";}}
[$s,$b,$h]=sslReq();sslCheck(in_array($s,[301,302],true)&&strpos($h,'Location: https://'.$host.'/')!==false,'production HTTP redirects to HTTPS');
[$s,$b,$h]=sslReq(false,'/','X-Forwarded-Proto: https\r\n');sslCheck(in_array($s,[301,302],true),'untrusted forwarded HTTPS cannot bypass redirect');
[$s,$b,$h]=sslReq(true);sslCheck($s===200&&strpos($b,'id="login_form"')!==false,'HTTPS simulation gate root serves real admin login');
sslCheck(stripos($h,'Location:')===false&&strpos($b,'<fieldset disabled')===false,'production auth is not preview');
sslCheck(preg_match('/Set-Cookie:[^\n]*;\s*secure/i',$h)&&stripos($h,'HttpOnly')!==false&&stripos($h,'SameSite=Lax')!==false,'Secure HttpOnly SameSite session cookie');
foreach(['/index.php/resource/css/style.css','/index.php/resource/images/admin/logo.png'] as $p){[$s]=sslReq(true,$p);sslCheck($s===200,'HTTPS asset '.$p);}
$gateHost=$host;$host=preg_replace('/^gate\./','',$host);
$admin=is_dir(dirname(__DIR__).'/admin')?'admin':'nol-gate';
[$s]=sslReq(true,'/'.$admin.'/index.php');sslCheck($s===404,'public HTTPS host does not expose admin');$host=$gateHost;
echo "$pass PASS / $fail FAIL\n";exit($fail?1:0);
