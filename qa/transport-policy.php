<?php
if (PHP_SAPI !== 'cli') exit(1);
$blue=is_dir(dirname(__DIR__).'/admin');
require $blue ? dirname(__DIR__).'/inc/lib/classes/Security/TransportSecurity.php' : dirname(__DIR__).'/src/Http/TransportSecurity.php';
$class=$blue?'Security\\TransportSecurity':'Http\\TransportSecurity';
$pass=0;$fail=0;
function transportCheck($ok,$label){global $pass,$fail;$ok?$pass++:$fail++;echo ($ok?'PASS ':'FAIL ').$label."\n";}
$host=$blue?'gate.bluesquare.kr':'gate.noltheater-daehakro.com';
$server=['HTTP_HOST'=>$host,'REMOTE_ADDR'=>'203.0.113.2','SERVER_PORT'=>80];
transportCheck(!$class::allowsTemporaryHttp($server,$host,false),'HTTP disabled by default');
transportCheck($class::allowsTemporaryHttp($server,$host,true),'exact gate HTTP explicitly allowed');
foreach (['www.'.substr($host,5),$host.'.evil.invalid',$host.'.',$host.'@evil.invalid','evil.invalid',$host.':bad'] as $bad) {
 $s=$server;$s['HTTP_HOST']=$bad;transportCheck(!$class::allowsTemporaryHttp($s,$host,true),'other or malformed host cannot downgrade');
}
$s=$server;$s['HTTP_HOST']=strtoupper($host).':8628';transportCheck($class::allowsTemporaryHttp($s,$host,true),'case and numeric local port supported');
$s=$server;$s['HTTPS']='on';transportCheck(!$class::allowsTemporaryHttp($s,$host,true),'HTTPS never downgraded with flag enabled');
$s=$server;$s['SERVER_PORT']=443;transportCheck(!$class::allowsTemporaryHttp($s,$host,true),'TLS port preserves secure mode');
$s=$server;$s['HTTP_X_FORWARDED_PROTO']='https';
transportCheck(!$class::isSecure($s),'untrusted forwarding cannot claim TLS');
transportCheck($class::allowsTemporaryHttp($s,$host,true),'untrusted forwarding does not drop HTTP session cookie');
transportCheck($class::isSecure($s,'203.0.113.0/24'),'registered proxy may report TLS');
transportCheck(!$class::allowsTemporaryHttp($s,$host,true,'203.0.113.0/24'),'trusted TLS preserves secure cookie');
transportCheck(!$class::allowsTemporaryHttp($server,'',true),'empty gate fails closed');
echo "$pass PASS / $fail FAIL\n";exit($fail?1:0);
