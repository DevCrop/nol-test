<?php
if(PHP_SAPI!=='cli') {http_response_code(404);exit;}
require dirname(__DIR__).'/inc/lib/security.bootstrap.php';
$environment=APP_ENV;$host=(string)blue_env('GATE_HOST');
$isolated=blue_env('DB_USER')==='qa_app';
if($environment!=='production') { echo "SKIP isolated production HTTP policy requires production override\n";exit(0); }
if(!is_file('/.dockerenv') || !$isolated) exit(1);
$pass=0;$fail=0;
foreach(['','X-Forwarded-Proto: https','X-Forwarded-Proto: HTTPS','X-Forwarded-Proto: http','X-Forwarded-Proto: https,http'] as $forwarded) {
    $ctx=stream_context_create(['http'=>['header'=>"Host: ".$host."\r\n".$forwarded."\r\n",'ignore_errors'=>true,'follow_location'=>0,'timeout'=>10]]);
    $body=file_get_contents('http://127.0.0.1/?qa_transport=1',false,$ctx);
    preg_match('/\s(\d{3})\s/',$http_response_header[0]??'',$m);
    $location='';
    foreach($http_response_header as $header) if(stripos($header,'Location:')===0) $location=trim(substr($header,9));
    $ok=in_array((int)($m[1]??0),[301,302,307,308],true) && $location==='https://'.$host.'/?qa_transport=1';
    $ok?$pass++:$fail++;
    echo ($ok?'PASS ':'FAIL ')."production HTTP enforces HTTPS: ".($forwarded?:'no forwarded header')."\n";
}
echo "pass=".$pass." fail=".$fail."\n";exit($fail?1:0);
