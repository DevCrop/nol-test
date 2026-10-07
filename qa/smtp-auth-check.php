<?php
// Explicit opt-in: reads private local .env, never sends mail or prints credentials.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
if (($argv[1]??'')!=='--live') {echo "SKIP SMTP auth check requires --live\n";exit;}
$values=[];
foreach((array)file(dirname(__DIR__).'/.env',FILE_IGNORE_NEW_LINES) as $line){
    if(preg_match('/^(SMTP_[A-Z_]+)=(.*)$/',$line,$m)) $values[$m[1]]=trim($m[2],"\"' ");
}
$socket=null;
try {
    if(($values['SMTP_HOST']??'')!=='smtp.gmail.com' || ($values['SMTP_PORT']??'')!=='465') throw new RuntimeException('Unsupported TLS test target');
    $ctx=stream_context_create(['ssl'=>['verify_peer'=>true,'verify_peer_name'=>true,'peer_name'=>'smtp.gmail.com']]);
    $socket=stream_socket_client('ssl://smtp.gmail.com:465',$errno,$error,15,STREAM_CLIENT_CONNECT,$ctx);
    if(!$socket) throw new RuntimeException('TLS connection failed');
    stream_set_timeout($socket,15);
    $read=function($expected) use($socket){
        do { $line=fgets($socket,4096); if($line===false)throw new RuntimeException('SMTP read failed'); } while(isset($line[3]) && $line[3]==='-');
        if((int)substr($line,0,3)!==$expected) throw new RuntimeException('Unexpected SMTP status '.(int)substr($line,0,3));
    };
    $send=function($command,$expected) use($socket,$read){fwrite($socket,$command."\r\n");$read($expected);};
    $read(220);$send('EHLO gate.local',250);$send('AUTH LOGIN',334);
    $send(base64_encode($values['SMTP_USER']??''),334);
    $send(base64_encode($values['SMTP_PASS']??''),235);
    $send('NOOP',250);$send('QUIT',221);
    echo "PASS verified TLS, SMTP authentication, NOOP and QUIT; no mail sent\n";
} catch(Throwable $e) {echo "FAIL ".$e->getMessage()."\n";exit(1);}
finally {if(is_resource($socket))fclose($socket);}
