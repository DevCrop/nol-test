<?php
if(PHP_SAPI!=='cli'||!is_file('/.dockerenv')||getenv('DB_USER')!=='qa_app'||getenv('QA_PRODUCTION_HTTP')!=='1')exit(1);
$admin=is_dir(dirname(__DIR__).'/admin')?'admin':'nol-gate';
$enabled=getenv('GATE_ALLOW_LEGACY_ADMIN')==='true';$pass=0;$fail=0;
function toggleCheck($ok,$label){global $pass,$fail;$ok?$pass++:$fail++;echo($ok?'PASS ':'FAIL ').$label."\n";}
function toggleGet($host,$path='/', $tls=false,$remote=false){
 $ctx=stream_context_create(['http'=>['header'=>'Host: '.$host."\r\n".($tls?"X-QA-HTTPS: 1\r\n":''),'ignore_errors'=>true,'follow_location'=>0,'timeout'=>15]]);
 $b=(string)file_get_contents('http://'.($remote?gethostbyname(gethostname()):'127.0.0.1').$path,false,$ctx);preg_match('/\s(\d{3})\s/',$http_response_header[0],$m);return[(int)$m[1],$b,implode("\n",$http_response_header)];
}
[$s,$b]=toggleGet(getenv('PUBLIC_HOST'),'/'.$admin.'/',true);toggleCheck($enabled?($s===200&&strpos($b,'id="login_form"')!==false):$s===404,'legacy HTTPS matches explicit flag');
[$s,$b,$h]=toggleGet(getenv('GATE_HOST'));toggleCheck($s===200&&strpos($b,'id="login_form"')!==false&&stripos($h,'Location:')===false,'HTTP gate is directly usable, no automatic fallback');
toggleCheck(!preg_match('/Set-Cookie:[^\n]*;\s*secure/i',$h)&&stripos($h,'HttpOnly')!==false,'only HTTP gate gets non-Secure HttpOnly cookie');
[$s,$b,$h]=toggleGet(getenv('GATE_HOST'),'/',true);toggleCheck($s===200&&strpos($b,'id="login_form"')!==false,'HTTPS gate root remains usable');
toggleCheck((bool)preg_match('/Set-Cookie:[^\n]*;\s*secure/i',$h),'HTTPS gate keeps Secure cookie');
[$s]=toggleGet(getenv('GATE_HOST'),'/'.$admin.'/index.php',true);toggleCheck($s===404,'gate physical admin path stays hidden');
[$s]=toggleGet('unknown.example','/'.$admin.'/',true);toggleCheck($s===404,'unknown host stays denied');
[$s]=toggleGet(getenv('PUBLIC_HOST'),'/'.$admin.'/',true,true);toggleCheck($enabled?$s===403:in_array($s,[403,404],true),'legacy IP guard cannot be bypassed by temporary flag');
echo "$pass PASS / $fail FAIL\n";exit($fail?1:0);
