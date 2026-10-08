<?php
if(PHP_SAPI!=='cli') exit(1);
function env(string $key,$default=null) { return $key==='TRUSTED_PROXY_CIDRS' ? ($GLOBALS['qa_proxy_rules']??'') : $default; }
require dirname(__DIR__).'/src/Http/TransportSecurity.php';
require dirname(__DIR__).'/src/Http/Request.php';
$pass=0;$fail=0;
function transportCheck(bool $ok,string $label):void {global $pass,$fail;$ok?$pass++:$fail++;echo ($ok?'PASS ':'FAIL ').$label."\n";}
$plain=['REMOTE_ADDR'=>'198.51.100.12','SERVER_PORT'=>80,'HTTP_HOST'=>'gate.example.com','REQUEST_URI'=>'/pages/account/?page=2'];
foreach([
    [$plain,'',false,'direct HTTP'],
    [$plain+['HTTPS'=>'on'],'',true,'native TLS'],
    [array_replace($plain,['SERVER_PORT'=>443]),'',true,'native TLS port'],
    [$plain+['HTTP_X_FORWARDED_PROTO'=>'https'],'',false,'unregistered proxy header'],
    [$plain+['HTTP_X_FORWARDED_PROTO'=>'HTTPS'],'198.51.100.12',true,'registered exact proxy'],
    [$plain+['HTTP_X_FORWARDED_PROTO'=>'https'],'198.51.100.0/24',true,'registered proxy CIDR'],
    [$plain+['HTTP_X_FORWARDED_PROTO'=>'https'],'203.0.113.0/24',false,'outside proxy CIDR'],
    [$plain+['HTTP_X_FORWARDED_PROTO'=>'https'],'invalid/99',false,'invalid trust rules fail closed'],
    [$plain+['HTTP_X_FORWARDED_PROTO'=>'https,http'],'198.51.100.12',false,'ambiguous forwarded chain'],
    [$plain+['HTTP_X_FORWARDED_PROTO'=>'http'],'198.51.100.12',false,'trusted proxy reports plain HTTP'],
    [$plain+['HTTP_X_FORWARDED_PROTO'=>'https','HTTP_X_FORWARDED_FOR'=>'203.0.113.12'],'203.0.113.12',false,'forwarded client address cannot grant proxy trust'],
] as [$server,$rules,$expected,$label]) {
    transportCheck(\Http\TransportSecurity::isSecure($server,$rules)===$expected,$label);
}
$_SERVER=$plain+['HTTP_X_FORWARDED_PROTO'=>'https'];
$GLOBALS['qa_proxy_rules']='198.51.100.0/24';
$request=new \Http\Request();
transportCheck($request->isSecure() && $request->url()==='https://gate.example.com/pages/account/?page=2','Request shares trusted transport and preserves URL');
$GLOBALS['qa_proxy_rules']='';
transportCheck(!$request->isSecure() && $request->url()==='http://gate.example.com/pages/account/?page=2','Request rejects untrusted forwarded transport');
foreach(['inc/lib/base.class.php','views/layouts/main.php','src/Routing/Router.php','inc/lib/site.info.php'] as $path) {
    transportCheck(strpos(file_get_contents(dirname(__DIR__).'/'.$path),'TransportSecurity::isSecure')!==false,'scheme consumer shares boundary '.$path);
}
echo "pass=".$pass." fail=".$fail."\n";exit($fail?1:0);
