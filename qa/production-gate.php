<?php
if(PHP_SAPI!=='cli') {http_response_code(404);exit;}
if(getenv('APP_ENV')!=='production' || getenv('GATE_ALLOW_IPS')!=='') {
    echo "SKIP empty production gate QA requires isolated empty IP override\n";exit(0);
}
if(!is_file('/.dockerenv') || getenv('DB_USER')!=='qa_app') exit(1);
$pass=0;$fail=0;
foreach(['','X-Forwarded-For: 198.51.100.12'] as $forwarded) {
    $ctx=stream_context_create(['http'=>['header'=>"Host: ".getenv('GATE_HOST')."\r\nX-Forwarded-Proto: https\r\n".$forwarded."\r\n",'ignore_errors'=>true,'follow_location'=>0,'timeout'=>10]]);
    $body=(string)file_get_contents('http://127.0.0.1/',false,$ctx);
    preg_match('/\s(\d{3})\s/',$http_response_header[0]??'',$m);
    $ok=(int)($m[1]??0)===403 && stripos($body,'Forbidden')!==false;
    $ok?$pass++:$fail++;
    echo ($ok?'PASS ':'FAIL ').'empty production IP list denies gate '.($forwarded?:'without forwarded client')."\n";
}
$ctx=stream_context_create(['http'=>['header'=>"Host: ".getenv('PUBLIC_HOST')."\r\nX-Forwarded-Proto: https\r\n",'ignore_errors'=>true,'follow_location'=>0,'timeout'=>10]]);
$body=file_get_contents('http://127.0.0.1/',false,$ctx);
preg_match('/\s(\d{3})\s/',$http_response_header[0]??'',$m);
$ok=(int)($m[1]??0)===200;
$ok?$pass++:$fail++;echo ($ok?'PASS ':'FAIL ')."public site unaffected by empty administrator IP list\n";
echo "pass=".$pass." fail=".$fail."\n";exit($fail?1:0);
