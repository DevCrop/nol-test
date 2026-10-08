<?php
if(PHP_SAPI!=='cli') {http_response_code(404);exit;}
ob_start();require __DIR__.'/bootstrap.php';require_once dirname(__DIR__).'/inc/lib/db.php';
if(APP_ENV!=='development'||!is_file('/.dockerenv')||blue_env('DB_USER')!=='qa_app') exit(1);
$db=DB::getInstance();$admin=0;$sid='';$ids=[];$uid='qa_statread_'.bin2hex(random_bytes(4));
$http=static function(string $method,string $date,string $csrf='') use(&$sid):array {
    $path='/admin/pages/log/log.time.php';
    if(blue_env('GATE_ROUTE_MODE','path')==='internal') $path=substr($path,6);
    $content=$method==='POST'?http_build_query(['sdate'=>$date,'_csrf'=>$csrf]):'';
    if($method!=='POST') $path.='?sdate='.rawurlencode($date);
    $ctx=stream_context_create(['http'=>['method'=>$method,'header'=>"Host: ".blue_env('GATE_HOST','gate.local')."\r\nCookie: ".session_name()."=".$sid."\r\nContent-Type: application/x-www-form-urlencoded\r\n",'content'=>$content,'ignore_errors'=>true,'follow_location'=>0,'timeout'=>10]]);
    $body=(string)file_get_contents('http://127.0.0.1'.$path,false,$ctx);preg_match('/\s(\d{3})\s/',$http_response_header[0]??'',$m);
    return [(int)($m[1]??0),$body];
};
try {
    $q=$db->query('SELECT COUNT(*) FROM nb_counter_data WHERE Year=2099 AND Month=12 AND Day IN (26,27,28)');
    if((int)$q->fetchColumn()!==0) throw new RuntimeException('QA synthetic date collision');
    $db->prepare("INSERT INTO nb_admin(sitekey,uid,upwd,uname,email,active_status,role_code,password_changed_at) VALUES('BLUESQ',?,?,?,?,'Y','super',NOW())")->execute([$uid,password_hash('Fixture!234',PASSWORD_DEFAULT),'QA',$uid.'@example.com']);
    $admin=(int)$db->lastInsertId();\Security\AdminAccount::establish(\Security\AdminAccount::findByNo($admin));
    $csrf=\Security\Csrf::token();$sid=session_id();session_write_close();
    if(posix_geteuid()===0) chown((session_save_path()?:sys_get_temp_dir()).'/sess_'.$sid,'www-data');
    $db->exec('INSERT INTO nb_counter_data(Year,Month,Day,Visit_Num,Hour00) VALUES(2099,12,27,30303,30303)');$ids[]=(int)$db->lastInsertId();
    [$status,$body]=$http('GET','2099-12-27');
    qa_expect($status===200 && strpos($body,'30,303')!==false,'ordinary statistics read renders original total');
    [$status,$body]=$http('GET','2099-12-26');
    qa_expect($status===200 && strpos($body,'class="no-admin-cnt">0명')!==false,'empty statistics date remains valid with zero visitors');
    foreach(['GET','HEAD','POST'] as $method) {
        $pair=[];
        foreach([300009,400003] as $visits) {
            $db->prepare('INSERT INTO nb_counter_data(Year,Month,Day,Visit_Num,Hour00) VALUES(2099,12,28,?,?)')->execute([$visits,$visits]);
            $pair[]=$ids[]=(int)$db->lastInsertId();
        }
        $q=$db->prepare('SELECT * FROM nb_counter_data WHERE uid IN (?,?) ORDER BY uid');$q->execute($pair);$before=$q->fetchAll(PDO::FETCH_ASSOC);
        [$status,$body]=$http($method,'2099-12-28',$csrf);
        $q->execute($pair);$after=$q->fetchAll(PDO::FETCH_ASSOC);
        qa_expect($status===200 && $after===$before,$method.' statistics query preserves all original rows');
        if($method!=='HEAD') {
            qa_expect(substr_count($body,'700,012')>=2,$method.' visitor and hourly totals include both rows');
            preg_match_all("/width='([0-9.]+)%'/",$body,$widths);
            qa_expect(!empty($widths[1]) && max(array_map('floatval',$widths[1]))<=100,$method.' graph uses aggregated hourly maximum');
        }
        $db->prepare('DELETE FROM nb_counter_data WHERE uid IN (?,?)')->execute($pair);
    }
} catch(Throwable $e) {qa_expect(false,$e->getMessage());}
finally {
    foreach($ids as $id) $db->prepare('DELETE FROM nb_counter_data WHERE uid=? AND Year=2099 AND Month=12')->execute([$id]);
    if($admin) {$db->prepare('DELETE FROM nb_admin_privacy_access WHERE actor_no=?')->execute([$admin]);$db->prepare('DELETE FROM nb_admin_audit WHERE actor_no=?')->execute([$admin]);$db->prepare('DELETE FROM nb_admin WHERE no=? AND uid=?')->execute([$admin,$uid]);}
    if($sid){$file=(session_save_path()?:sys_get_temp_dir()).'/sess_'.$sid;if(is_file($file))unlink($file);}
}
qa_done();
