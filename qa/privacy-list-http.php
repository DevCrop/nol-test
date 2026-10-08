<?php
// Isolated Docker QA only. Fault injection is limited to this synthetic actor.
if (PHP_SAPI!=='cli') { http_response_code(404); exit; }
ob_start();
require dirname(__DIR__).'/config/env.php';
require dirname(__DIR__).'/inc/lib/session.boot.php';
require dirname(__DIR__).'/nol-gate/lib/PiiMask.php';
if (env('APP_ENV')!=='development' || !is_file('/.dockerenv') || env('DB_USER')!=='qa_app') exit(1);
$db=new PDO('mysql:host='.env('DB_HOST').';dbname='.env('DB_NAME').';charset=utf8mb4',env('DB_USER'),env('DB_PASS'),[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
$site='NOLTHE';
$host=(string)env('GATE_HOST','gate.local');
$base=env('GATE_ROUTE_MODE','root')==='path'?'/nol-gate':'';
$path='/pages/inquiry/index.php';
$admin=0;$request=0;$sid='';$trigger='';$pass=0;$fail=0;
$uid='qa_privlist_'.bin2hex(random_bytes(5));
$marker=$uid.'_visible';
function listPrivacyCheck(bool $ok,string $label):void {
    global $pass,$fail;
    $ok?$pass++:$fail++;
    echo ($ok?'PASS ':'FAIL ').$label."\n";
}
$http=static function(string $cookie) use($host,$base,$path):array {
    $ctx=stream_context_create(['http'=>['header'=>"Host: ".$host."\r\nCookie: ".session_name()."=".$cookie."\r\n",'ignore_errors'=>true,'follow_location'=>0,'timeout'=>10]]);
    $html=(string)file_get_contents('http://127.0.0.1'.$base.$path,false,$ctx);
    preg_match('/\s(\d{3})\s/',$http_response_header[0]??'',$m);
    return [(int)($m[1]??0),$html];
};
try {
    $token=bin2hex(random_bytes(32));
    $db->prepare("INSERT INTO nb_admin(sitekey,uid,upwd,uname,email,active_status,role_id,password_changed_at,last_login_at,created_at,login_token) VALUES(?,?,?,?,?,'Y',1,NOW(),NOW(),NOW(),?)")->execute([$site,$uid,password_hash('Fixture!234',PASSWORD_DEFAULT),'QA',$uid.'@example.com',$token]);
    $admin=(int)$db->lastInsertId();
    $_SESSION=['no_adm_login_no'=>$admin,'no_adm_login_uid'=>$uid,'no_adm_login_uname'=>'QA','no_adm_login_role_id'=>1,'no_adm_login_role'=>'super','no_adm_login_token'=>$token,'no_adm_last_activity'=>time()];
    $sid=session_id();session_write_close();
    $sessionFile=(session_save_path()?:sys_get_temp_dir()).'/sess_'.$sid;
    if (posix_geteuid()===0) chown($sessionFile,'www-data');
    $db->prepare("INSERT INTO nb_request(sitekey,name,email,phone,performance_name,regdate) VALUES(?,?,?,?,?,NOW())")->execute([$site,'검증담당자',$uid.'@example.com','01012345678',$marker]);
    $request=(int)$db->lastInsertId();
    $count=static function() use($db,$admin,$request):int {
        $q=$db->prepare("SELECT COUNT(*) FROM nb_admin_privacy_access WHERE actor_no=? AND target_no=? AND entity='inquiry' AND task='대관신청 목록 열람'");
        $q->execute([$admin,$request]);return (int)$q->fetchColumn();
    };
    [$status,$html]=$http('');
    listPrivacyCheck(in_array($status,[302,401,403],true) && strpos($html,$marker)===false,'anonymous list does not disclose fixture');
    listPrivacyCheck($count()===0,'anonymous access creates no fixture disclosure log');
    [$status,$html]=$http($sid);
    listPrivacyCheck($status===200 && strpos($html,$marker)!==false,'authenticated rental list displays fixture');
    listPrivacyCheck($count()===1,'rental list logs fixture exactly once');
    preg_match('#<tbody[^>]*>(.*?)</tbody>#s',$html,$table);
    listPrivacyCheck(isset($table[1]) && strpos($table[1],'검증담당자')===false && strpos($table[1],PiiMask::name('검증담당자'))!==false,'rental list masks subject name');
    $faultDb=new PDO('mysql:host='.env('DB_HOST').';dbname='.env('DB_NAME').';charset=utf8mb4','root',env('DB_ROOT_PASSWORD'),[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
    $trigger=$createdTrigger='qa_privfail_'.bin2hex(random_bytes(5));
    $faultDb->exec("CREATE TRIGGER ".$trigger." BEFORE INSERT ON nb_admin_privacy_access FOR EACH ROW BEGIN IF NEW.actor_no=".$admin." THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='QA synthetic privacy failure'; END IF; END");
    [$status,$html]=$http($sid);
    listPrivacyCheck($status===503 && strpos($html,$marker)===false,'failed privacy recording blocks list before fixture disclosure');
    listPrivacyCheck($count()===1,'failed recording does not create success log');
    $faultDb->exec("DROP TRIGGER ".$trigger);$trigger='';
    [$status,$html]=$http($sid);
    listPrivacyCheck($status===200 && strpos($html,$marker)!==false && $count()===2,'restored recording restores list and records exactly once');
} catch(Throwable $e) { listPrivacyCheck(false,$e->getMessage()); }
finally {
    if($trigger && isset($faultDb)) $faultDb->exec("DROP TRIGGER IF EXISTS ".$trigger);
    if($admin) $db->prepare('DELETE FROM nb_admin_privacy_access WHERE actor_no=?')->execute([$admin]);
    if($request) $db->prepare('DELETE FROM nb_request WHERE no=? AND performance_name=?')->execute([$request,$marker]);
    if($admin) $db->prepare('DELETE FROM nb_admin WHERE no=? AND uid=?')->execute([$admin,$uid]);
    if($sid) { $file=(session_save_path()?:sys_get_temp_dir()).'/sess_'.$sid; if(is_file($file)) unlink($file); }
}
$q=$db->prepare('SELECT COUNT(*) FROM nb_admin WHERE uid=?');$q->execute([$uid]);
listPrivacyCheck((int)$q->fetchColumn()===0,'synthetic administrator removed');
$q=$db->prepare('SELECT COUNT(*) FROM nb_request WHERE performance_name=?');$q->execute([$marker]);
listPrivacyCheck((int)$q->fetchColumn()===0,'synthetic rental removed');
$q=$db->prepare('SELECT COUNT(*) FROM information_schema.TRIGGERS WHERE TRIGGER_SCHEMA=DATABASE() AND TRIGGER_NAME=?');$q->execute([$createdTrigger??'']);
listPrivacyCheck((int)$q->fetchColumn()===0,'failure injection trigger removed');
echo "pass=".$pass." fail=".$fail."\n";exit($fail?1:0);
