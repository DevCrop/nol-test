<?php
if(PHP_SAPI!=='cli') {http_response_code(404);exit;}
ob_start();
require __DIR__.'/bootstrap.php';
require_once dirname(__DIR__).'/inc/lib/db.php';
if(APP_ENV!=='development' || !is_file('/.dockerenv') || blue_env('DB_USER')!=='qa_app') exit(1);
$paths=['admin/pages/admission/admission.list.php','admin/pages/admission/admission.view.php','admin/pages/admission/ajax/process.php','admin/pages/admission/js/process.js','admin/pages/calendar/ajax/process.php','admin/pages/calendar/calendar.add.php','admin/pages/calendar/calendar.list.php','admin/pages/calendar/calendar.view.php','admin/pages/employment/ajax/employment.process.php','admin/pages/employment/employment.list.php','admin/pages/employment/employment.view.php','admin/pages/employment/js/employment.process.js','admin/pages/member/ajax/member.level.process.php','admin/pages/member/ajax/member.process.php','admin/pages/member/js/member.level.process.js','admin/pages/member/js/member.process.js','admin/pages/member/member.add.php','admin/pages/member/member.level.add.php','admin/pages/member/member.level.php','admin/pages/member/member.level.view.php','admin/pages/member/member.list.php','admin/pages/member/member.view.php','admin/pages/sms/sms.php'];
$db=DB::getInstance();$admin=0;$sid='';$uid='qa_retired_'.bin2hex(random_bytes(5));
$http=static function(string $path,string $cookie='',string $method='GET',bool $public=false):array {
    $host=$public ? (string)blue_env('PUBLIC_HOST','localhost') : (string)blue_env('GATE_HOST','gate.local');
    if(!$public && blue_env('GATE_ROUTE_MODE','path')==='internal') $path=substr($path,6);
    $ctx=stream_context_create(['http'=>['method'=>$method,'header'=>"Host: ".$host."\r\nCookie: ".session_name()."=".$cookie."\r\n",'ignore_errors'=>true,'follow_location'=>0,'timeout'=>10]]);
    $body=(string)file_get_contents('http://127.0.0.1'.$path,false,$ctx);
    preg_match('/\s(\d{3})\s/',$http_response_header[0]??'',$m);
    return [(int)($m[1]??0),$body];
};
try {
    $db->prepare("INSERT INTO nb_admin(sitekey,uid,upwd,uname,email,active_status,role_code,password_changed_at,last_login_at,created_at) VALUES('BLUESQ',?,?,?,?,'Y','super',NOW(),NOW(),NOW())")->execute([$uid,password_hash('Fixture!234',PASSWORD_DEFAULT),'QA',$uid.'@example.com']);
    $admin=(int)$db->lastInsertId();
    \Security\AdminAccount::establish(\Security\AdminAccount::findByNo($admin));
    $sid=session_id();session_write_close();
    $file=(session_save_path()?:sys_get_temp_dir()).'/sess_'.$sid;
    if(posix_geteuid()===0) chown($file,'www-data');
    foreach($paths as $relative) {
        $path='/'.$relative;
        [$anonymous]=$http($path);
        [$authenticated]=$http($path,$sid);
        [$post]=$http($path,$sid,'POST');
        [$public]=$http($path,'','GET',true);
        qa_expect(!is_file(dirname(__DIR__).$path) && $anonymous===404 && $authenticated===404 && $post===404 && $public===404,'retired file absent and GET/POST inaccessible: '.$relative);
    }
    foreach(['member','admission','employment','calendar','sms'] as $module) {
        [$status]=$http('/admin/pages/'.strtoupper($module).'/missing.php/path-info',$sid);
        qa_expect($status===404,'retired alias/path-info blocked: '.$module);
    }
    [$status]=$http('/admin/pages/board/board.list.php',$sid);
    qa_expect($status===200,'active board administration preserved');
    $ctx=stream_context_create(['http'=>['header'=>"Host: ".blue_env('PUBLIC_HOST','localhost')."\r\n",'ignore_errors'=>true,'follow_location'=>0,'timeout'=>10]]);
    file_get_contents('http://127.0.0.1/',false,$ctx);
    preg_match('/\s(\d{3})\s/',$http_response_header[0]??'',$m);
    qa_expect((int)($m[1]??0)===200,'public homepage preserved');
} catch(Throwable $e) {qa_expect(false,$e->getMessage());}
finally {
    if($admin) {
        $db->prepare('DELETE FROM nb_admin_privacy_access WHERE actor_no=?')->execute([$admin]);
        $db->prepare('DELETE FROM nb_admin_audit WHERE actor_no=?')->execute([$admin]);
        $db->prepare('DELETE FROM nb_admin WHERE no=? AND uid=?')->execute([$admin,$uid]);
    }
    if($sid) {$file=(session_save_path()?:sys_get_temp_dir()).'/sess_'.$sid;if(is_file($file)) unlink($file);}
}
qa_done();
