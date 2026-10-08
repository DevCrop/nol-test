<?php
if(PHP_SAPI!=='cli') {http_response_code(404);exit;}
if(getenv('APP_ENV')!=='production' || getenv('DB_NAME')!=='qa_missing_database') {
    echo "SKIP production error QA requires isolated fault override\n";exit(0);
}
if(!is_file('/.dockerenv') || getenv('DB_USER')!=='qa_app') exit(1);
$pass=0;$fail=0;
foreach([getenv('PUBLIC_HOST'),getenv('GATE_HOST')] as $host) {
    $ctx=stream_context_create(['http'=>['header'=>"Host: ".$host."\r\nX-Forwarded-Proto: https\r\n",'ignore_errors'=>true,'follow_location'=>0,'timeout'=>10]]);
    $body=(string)file_get_contents('http://127.0.0.1/?qa_fault=1',false,$ctx);
    preg_match('/\s(\d{3})\s/',$http_response_header[0]??'',$m);
    $failed=(int)($m[1]??0)>=500;
    $clean=!preg_match('#SQLSTATE|PDOException|Stack trace|Fatal error|/var/www|qa_missing_database|dbusr|qa-isolated#i',$body);
    foreach(['DB outage returns server error'=>$failed,'DB outage hides internal diagnostics'=>$clean] as $label=>$ok) {
        $ok?$pass++:$fail++;echo ($ok?'PASS ':'FAIL ').$label.' on '.$host."\n";
    }
}
echo "pass=".$pass." fail=".$fail."\n";exit($fail?1:0);
