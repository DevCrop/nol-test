<?php
if(PHP_SAPI!=='cli'||!is_file('/.dockerenv')||getenv('DB_USER')!=='qa_app')exit(1);
$pass=0;$fail=0;
function policyCheck($ok,$label){global $pass,$fail;$ok?$pass++:$fail++;echo ($ok?'PASS ':'FAIL ').$label."\n";}
function policyGet($host,$path='/', $tls=false,$extra='') {
 $ctx=stream_context_create(['http'=>['header'=>"Host: $host\r\n".($tls?"X-QA-HTTPS: 1\r\n":'').$extra,'ignore_errors'=>true,'follow_location'=>0]]);
 $body=file_get_contents('http://127.0.0.1'.$path,false,$ctx);$h=implode("\n",$http_response_header);
 preg_match('/\\s(\\d{3})\\s/',$http_response_header[0]??'',$m);return [(int)($m[1]??0),$body,$h];
}
$gate=getenv('GATE_HOST');$public=preg_replace('/^gate\\./','',$gate);
if(getenv('QA_DENY_IP')==='1'){
 [$s]=policyGet($gate);policyCheck($s===403,'unregistered IP still denied in temporary HTTP mode');
 [$s]=policyGet($gate,'/',false,"X-Forwarded-For: 127.0.0.1\r\n");policyCheck($s===403,'spoofed forwarded IP cannot bypass gate restriction');
}else{
 [$s,$body,$h]=policyGet($gate);policyCheck($s===200&&strpos($body,'id="login_form"')!==false,'production HTTP gate root serves authenticated application');
 policyCheck(!preg_match('/Set-Cookie:[^\\n]*;\\s*secure/i',$h)&&stripos($h,'HttpOnly')!==false&&stripos($h,'SameSite=Lax')!==false,'HTTP-only gate cookie exception');
 [$s,$body,$h]=policyGet($gate,'/',true);policyCheck($s===200&&stripos($h,'Location:')===false,'HTTPS not downgraded when temporary flag enabled');
 policyCheck(preg_match('/Set-Cookie:[^\\n]*;\\s*secure/i',$h),'HTTPS cookie remains Secure with HTTP exception enabled');
 [$s,$body,$h]=policyGet($gate,'/',false,"X-Forwarded-Proto: https\r\n");
 policyCheck($s===200&&!preg_match('/Set-Cookie:[^\\n]*;\\s*secure/i',$h),'untrusted forwarded TLS cannot break HTTP cookie');
 [$s,$body,$h]=policyGet($public);policyCheck(in_array($s,[301,302],true)&&strpos($h,'Location: https://'.$public.'/')!==false,'public HTTP still redirects to HTTPS');
 [$s,$body,$h]=policyGet($public,'/',true);policyCheck($s===200&&strpos($body,'id="login_form"')===false,'public HTTPS homepage preserved');
}
echo "$pass PASS / $fail FAIL\n";exit($fail?1:0);
