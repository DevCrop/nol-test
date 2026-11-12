<?php
// Dedicated Docker-only HTTP QA. SMTP terminates locally with a test CA; never send real mail.
ob_start(); require __DIR__.'/bootstrap.php'; require dirname(__DIR__).'/inc/lib/db.php';
if (!is_file('/.dockerenv') || APP_ENV!=='development' || DB_USER!=='qa_app' || !MFA_ENABLED || blue_env('SMTP_HOST')!=='localhost' || (int)blue_env('SMTP_PORT')!==2525 || blue_env('SMTP_USER')!=='qa-mfa@example.com') exit(1);
if (($argv[1]??'')==='--smtp') {
    $dir=$argv[2]; $ctx=stream_context_create(['ssl'=>['local_cert'=>'/tmp/blue-mfa-cert.pem','local_pk'=>'/tmp/blue-mfa-key.pem']]);
    $server=stream_socket_server('tcp://127.0.0.1:2525',$errno,$error,STREAM_SERVER_BIND|STREAM_SERVER_LISTEN,$ctx);
    if(!$server) exit(2); file_put_contents($dir.'/ready','1');
    while($client=@stream_socket_accept($server,30)) {
        stream_set_timeout($client,10); fwrite($client,"220 localhost QA SMTP\r\n"); $to=''; $auth=0;
        while(($line=fgets($client))!==false) {
            $cmd=rtrim($line,"\r\n");
            if($auth>0){fwrite($client,$auth===2?"334 UGFzc3dvcmQ6\r\n":"235 Authenticated\r\n");$auth--;continue;}
            if(stripos($cmd,'EHLO')===0){fwrite($client,"250-localhost\r\n250-STARTTLS\r\n250 AUTH LOGIN\r\n");}
            elseif($cmd==='STARTTLS'){fwrite($client,"220 Ready\r\n");if(!stream_socket_enable_crypto($client,true,STREAM_CRYPTO_METHOD_TLS_SERVER))break;}
            elseif(stripos($cmd,'AUTH LOGIN')===0){$auth=2;fwrite($client,"334 VXNlcm5hbWU6\r\n");}
            elseif(stripos($cmd,'RCPT TO:')===0){$to=substr($cmd,8);fwrite($client,"250 Recipient\r\n");}
            elseif($cmd==='DATA'){
                fwrite($client,"354 Message\r\n");$body='';while(($l=fgets($client))!==false&&$l!==".\r\n")$body.=$l;
                clearstatcache(true,$dir.'/reject');if(is_file($dir.'/reject'))fwrite($client,"451 QA temporary delivery failure\r\n");
                else{file_put_contents($dir.'/messages',json_encode(['to'=>$to,'body'=>$body])."\n",FILE_APPEND);fwrite($client,"250 Accepted\r\n");}
            }elseif($cmd==='QUIT'){fwrite($client,"221 Bye\r\n");break;}else fwrite($client,"250 OK\r\n");
        }fclose($client);
    }exit;
}
$db=DB::getInstance();$id=0;$uid='qa_auto_mfa_'.bin2hex(random_bytes(4));$sessions=[];$dir=sys_get_temp_dir().'/'.$uid;mkdir($dir,0700);$proc=null;
function autoFixture(array $session):array {
    if(session_status()===PHP_SESSION_ACTIVE)session_write_close();session_id('');session_start();$_SESSION=$session;
    $jar=['sid'=>session_id(),'csrf'=>\Security\Csrf::token()];$GLOBALS['sessions'][]=$jar['sid'];session_write_close();chown((session_save_path()?:sys_get_temp_dir()).'/sess_'.$jar['sid'],'www-data');return $jar;
}
function autoState(array $jar,?callable $change=null):array {
    if(session_status()===PHP_SESSION_ACTIVE)session_write_close();session_id($jar['sid']);session_start();if($change)$change();$s=$_SESSION;session_write_close();chown((session_save_path()?:sys_get_temp_dir()).'/sess_'.$jar['sid'],'www-data');return $s;
}
function autoHttp(array &$jar,string $path,?array $data=null):array {
    $h='Host: '.GATE_HOST."\r\nCookie: ".session_name().'='.$jar['sid']."\r\n";
    if($data!==null){$data['_csrf']=$jar['csrf'];$h.="Content-Type: application/x-www-form-urlencoded\r\n";}
    $ctx=stream_context_create(['http'=>['method'=>$data===null?'GET':'POST','header'=>$h,'content'=>$data===null?'':http_build_query($data),'ignore_errors'=>true,'follow_location'=>0,'timeout'=>15]]);
    $body=(string)file_get_contents('http://127.0.0.1/index.php'.$path,false,$ctx);preg_match('/\s(\d{3})\s/',$http_response_header[0]??'',$m);$loc='';
    foreach($http_response_header as $l){if(preg_match('/^Set-Cookie: '.preg_quote(session_name(),'/').'=([^;]+)/i',$l,$c)){$jar['sid']=$c[1];$GLOBALS['sessions'][]=$c[1];}if(stripos($l,'Location:')===0)$loc=trim(substr($l,9));}
    return [(int)($m[1]??0),$body,$loc];
}
function autoMessages():array { $file=$GLOBALS['dir'].'/messages';return is_file($file)?array_map(function($l){return json_decode($l,true);},file($file,FILE_IGNORE_NEW_LINES)):[]; }
function autoLogin(string $uid):array { $jar=autoFixture(['captcha_secure'=>'1234']);$r=autoHttp($jar,'/lib/login/login.process.php',['uid'=>$uid,'upwd'=>'AutoPassword9!','r_captcha'=>'1234']);return [$jar,$r]; }
function autoCooldown(array $jar):void {autoState($jar,function(){$_SESSION['mfa_pending']['sent_at']=time()-MFA_RESEND_SECONDS-1;$_SESSION['mfa_pending']['attempted_at']=time()-MFA_RESEND_SECONDS-1;});}
try {
    $proc=proc_open([PHP_BINARY,__FILE__,'--smtp',$dir],[0=>['pipe','r'],1=>['file',$dir.'/smtp-output','a'],2=>['file',$dir.'/smtp-error','a']],$pipes);
    for($i=0;$i<40&&!is_file($dir.'/ready');$i++)usleep(50000);if(!is_file($dir.'/ready'))throw new RuntimeException('Local SMTP stub did not start');
    $db->prepare("INSERT INTO nb_admin(sitekey,uid,upwd,uname,email,active_status,role_code,password_changed_at) VALUES('BLUESQ',?,?,?,?,'Y','admin',NOW())")->execute([$uid,password_hash('AutoPassword9!',PASSWORD_DEFAULT),'QA auto MFA',$uid.'@example.com']);$id=(int)$db->lastInsertId();
    [$jar,$r]=autoLogin($uid);$state=autoState($jar);$p=$state['mfa_pending']??[];
    qa_expect($r[0]===302&&strpos($r[2],'/mfa.php')!==false&&!empty($p['code_hash'])&&empty($state['no_adm_login_uid']),'HTTP password login automatically sends registered-email challenge without authenticating');
    $messages=autoMessages();qa_expect(count($messages)===1&&strpos($messages[0]['to'],$uid.'@example.com')!==false,'exactly one SMTP message addressed to registered account');
    $r=autoHttp($jar,'/mfa.php');preg_match('/name="_csrf" value="([^"]+)"/',$r[1],$c);$jar['csrf']=$c[1]??'';
    qa_expect($r[0]===200&&strpos($r[1],'name="mfa_email"')===false&&strpos($r[1],'type="email"')===false&&strpos($r[1],'name="code"')!==false&&strpos($r[1],$uid.'@example.com')===false,'MFA UI has OTP and masked address, no recipient field');
    autoHttp($jar,'/mfa.php');autoHttp($jar,'/mfa.php');qa_expect(count(autoMessages())===1,'GET refresh never resends challenge');
    autoHttp($jar,'/lib/login/mfa.email.php',['mfa_email'=>'attacker@example.com']);qa_expect(count(autoMessages())===1&&autoState($jar)['mfa_pending']['email']===$uid.'@example.com','legacy email endpoint ignores tampering and is idempotent');
    autoHttp($jar,'/lib/login/mfa.resend.php',[]);qa_expect(count(autoMessages())===1,'HTTP immediate resend is throttled');
    autoCooldown($jar);autoState($jar,function(){$_SESSION['mfa_pending']['attempts']=2;});autoHttp($jar,'/lib/login/mfa.resend.php',[]);$next=autoState($jar)['mfa_pending'];
    qa_expect(count(autoMessages())===2&&$next['expires']===$p['expires']&&$next['attempts']===2,'resend retains original TTL and verification budget');
    $messages=autoMessages();$mail=quoted_printable_decode(end($messages)['body']);preg_match('/인증번호:\s*([0-9]{6})/u',$mail,$code);
    if(empty($code[1]))throw new RuntimeException('Local SMTP code extraction failed');$oldSid=$jar['sid'];autoHttp($jar,'/lib/login/mfa.process.php',['code'=>$code[1]]);$state=autoState($jar);
    qa_expect(!empty($state['no_adm_login_uid'])&&empty($state['mfa_pending'])&&$jar['sid']!==$oldSid,'actual SMTP OTP verifies and rotates authenticated session');
    file_put_contents($dir.'/reject','1');[$failed,$r]=autoLogin($uid);$state=autoState($failed);
    qa_expect(strpos($r[2],'/mfa.php')!==false&&!empty($state['mfa_pending'])&&empty($state['mfa_pending']['code_hash'])&&!empty($state['mfa_error'])&&empty($state['no_adm_login_uid']),'initial SMTP failure retains pending retry screen and grants no authentication');
    $r=autoHttp($failed,'/mfa.php');preg_match('/name="_csrf" value="([^"]+)"/',$r[1],$c);$failed['csrf']=$c[1]??'';unlink($dir.'/reject');$count=count(autoMessages());autoHttp($failed,'/lib/login/mfa.resend.php',[]);
    qa_expect(count(autoMessages())===$count&&strpos(autoState($failed)['mfa_error']??'','잠시')!==false,'failed SMTP attempt still enforces cooldown');
    autoCooldown($failed);autoHttp($failed,'/lib/login/mfa.resend.php',[]);qa_expect(count(autoMessages())===$count+1&&!empty(autoState($failed)['mfa_pending']['code_hash']),'initial failure can recover through registered-email resend');
    $db->prepare('UPDATE nb_admin SET email=? WHERE no=?')->execute(['changed@example.com',$id]);autoCooldown($failed);autoHttp($failed,'/lib/login/mfa.resend.php',[]);qa_expect(empty(autoState($failed)['mfa_pending']),'registered email change invalidates pending challenge');
    $db->prepare('UPDATE nb_admin SET email=? WHERE no=?')->execute(['',$id]);$count=count(autoMessages());[$missing,$r]=autoLogin($uid);qa_expect(empty(autoState($missing)['mfa_pending'])&&strpos($r[2],'/mfa.php')===false&&count(autoMessages())===$count,'missing account email fails login without SMTP or pending authentication');
    $db->prepare('UPDATE nb_admin SET email=? WHERE no=?')->execute([$uid.'@example.com',$id]);[$budget,$r]=autoLogin($uid);$state=autoState($budget);$budget['csrf']=$state['csrf_token']??$budget['csrf'];$r=autoHttp($budget,'/mfa.php');preg_match('/name="_csrf" value="([^"]+)"/',$r[1],$c);$budget['csrf']=$c[1]??'';
    for($i=0;$i<5;$i++)autoHttp($budget,'/lib/login/mfa.process.php',['code'=>'not-an-otp']);qa_expect(empty(autoState($budget)['mfa_pending'])&&empty(autoState($budget)['no_adm_login_uid']),'five invalid codes invalidate pending challenge');
    [$expired,$r]=autoLogin($uid);autoState($expired,function(){$_SESSION['mfa_pending']['expires']=time()-1;});$r=autoHttp($expired,'/mfa.php');preg_match('/name="_csrf" value="([^"]+)"/',$r[1],$c);$expired['csrf']=$c[1]??'';autoHttp($expired,'/lib/login/mfa.process.php',['code'=>'123456']);qa_expect(empty(autoState($expired)['mfa_pending']),'expired OTP cannot authenticate');
} catch(Throwable $e){qa_expect(false,$e->getMessage());}
finally {
    if(is_resource($proc)){proc_terminate($proc);proc_close($proc);}if(session_status()===PHP_SESSION_ACTIVE)session_write_close();
    if($id){$db->prepare('DELETE FROM nb_admin_privacy_access WHERE actor_no=?')->execute([$id]);$db->prepare('DELETE FROM nb_admin WHERE no=? AND uid=?')->execute([$id,$uid]);}
    foreach(array_unique($sessions)as $sid){$file=(session_save_path()?:sys_get_temp_dir()).'/sess_'.$sid;if(is_file($file))unlink($file);}
    foreach(glob($dir.'/*')as $file)unlink($file);rmdir($dir);
}
qa_done();
