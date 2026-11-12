<?php
ob_start();require __DIR__.'/bootstrap.php';require dirname(__DIR__).'/inc/lib/db.php';
if(APP_ENV!=='development'||!is_file('/.dockerenv')||DB_USER!=='qa_app')exit(1);
$db=DB::getInstance();$tag='qa_acl_'.bin2hex(random_bytes(4));$ids=[];$sessions=[];
function aclHttp(array $session,string $path,?array $data=null):array{
    $headers='Host: '.GATE_HOST."\r\nCookie: ".session_name().'='.$session[0]."\r\n";
    if($data!==null){$data['_csrf']=$session[1];$headers.="X-Requested-With: XMLHttpRequest\r\nContent-Type: application/x-www-form-urlencoded\r\n";}
    $base=blue_env('GATE_ROUTE_MODE')==='php'?'/index.php':'/admin';
    $ctx=stream_context_create(['http'=>['method'=>$data===null?'GET':'POST','header'=>$headers,'content'=>$data===null?'':http_build_query($data),'ignore_errors'=>true,'follow_location'=>0,'timeout'=>15]]);
    $body=(string)file_get_contents('http://127.0.0.1'.$base.$path,false,$ctx);preg_match('/\s(\d{3})\s/',$http_response_header[0]??'',$m);
    return [(int)($m[1]??0),$body,json_decode($body,true)];
}
function aclSession(int $id):array{
    global $sessions;
    if(session_status()===PHP_SESSION_ACTIVE)session_write_close();session_id('');session_start();$_SESSION=[];
    \Security\AdminAccount::establish(\Security\AdminAccount::findByNo($id));$token=\Security\Csrf::token();$sid=session_id();$sessions[]=$sid;session_write_close();
    if(posix_geteuid()===0)chown((session_save_path()?:sys_get_temp_dir()).'/sess_'.$sid,'www-data');return [$sid,$token];
}
try{
    qa_expect(\Security\AdminAcl::grants(['menu_permissions'=>null])===[],'legacy null grants fail closed');
    qa_expect(!\Security\AdminAcl::allows(['role_code'=>'admin','menu_permissions'=>'{invalid'],'board'),'malformed grants fail closed');
    qa_expect(\Security\AdminAcl::actionForRequest('/admin/pages/works/ajax/works.process.php',['REQUEST_METHOD'=>'POST'],['mode'=>'save'],['_method'=>'DELETE'])==='delete','works parser ignores decoy mode');
    qa_expect(\Security\AdminAcl::menuForPath('/admin/pages/design/ajax/popup.process.php')==='popup','popup API has its own menu');
    qa_expect(\Security\AdminAcl::landing(['role_code'=>'admin','menu_permissions'=>'[]'])==='/admin/pages/account/password.php','no menu grants retain own password landing');
    $grants=[];foreach(['board','banner','popup','works'] as $menu)$grants[$menu]=array_fill_keys(['view','create','update','delete'],true);
    $withRental=$grants+['rental'=>array_fill_keys(['view','create','update','delete'],true)];
    foreach([0,1,2] as $i){
        $credential=(new \Security\TemporaryAccountService())->create(['uid'=>$tag.$i,'uname'=>'권한 검사','email'=>$tag.$i.'@example.com','role_code'=>'admin','active_status'=>'Y','permissions'=>$i===0?$withRental:$grants]);
        $id=(int)$credential['id'];$ids[]=$id;$row=\Security\AdminAccount::findByNo($id);
        qa_expect(!empty($row['password_must_change'])&&password_verify($credential['temporary_password'],$row['upwd']),'phone-free account is temporary with hashed password '.$i);
        $pending=aclSession($id);[$status]=aclHttp($pending,'/pages/board/board.list.php');qa_expect($status===302,'temporary account cannot enter a business page '.$i);
        // ACL tests represent the state after the independently tested password-change flow.
        $db->prepare('UPDATE nb_admin SET password_must_change=0,temporary_password_expires_at=NULL,password_changed_at=NOW() WHERE no=?')->execute([$id]);
        $session=aclSession($id);
        foreach(['/pages/board/board.list.php','/pages/design/banner.list.php','/pages/design/popup.list.php','/pages/works/index.php'] as $path){[$status]=aclHttp($session,$path);qa_expect($status===200,'allowed menu returns 200 '.$i.' '.$path.' status='.$status);}
        [$status,$html]=aclHttp($session,'/pages/request/request.list.php');qa_expect($status===($i===0?200:403),'rental is granted only to requested profile '.$i);
        foreach(['/pages/setting/site.config.php','/pages/account/index.php','/pages/opera/index.php','/pages/unknown/index.php'] as $path){[$status]=aclHttp($session,$path);qa_expect($status===403||($path==='/pages/unknown/index.php'&&$status===404),'unrequested or unknown menu is denied '.$i.' '.$path.' status='.$status);}
        [$status,,$json]=aclHttp($session,'/pages/request/ajax/request.process.php',['mode'=>'delete','no'=>0]);qa_expect($i===0?$status!==403:($status===403&&is_array($json)),'rental mutation respects server grant '.$i);
        [$status,$html]=aclHttp($session,'/pages/board/board.list.php');qa_expect(($i===0?strpos($html,'대관 일정')!==false:strpos($html,'대관 일정')===false)&&strpos($html,'/pages/account/password.php')!==false,'drawer follows grants and retains password action '.$i);
        [$status]=aclHttp($session,'/pages/account/password.php');qa_expect($status===200,'own password remains available '.$i);
        $old=\Security\AdminAccount::findByNo($id);$version=\AccountAccessPolicy::version($old);
        \Security\AdminAccount::updateManaged($id,['uname'=>$old['uname'],'email'=>$old['email'],'active_status'=>'Y','role_code'=>'admin','permissions_present'=>'1','permissions'=>[]],0);
        $changed=\Security\AdminAccount::findByNo($id);
        qa_expect($changed['login_token']===null&&\AccountAccessPolicy::version($changed)!==$version,'permission update revokes session and pending MFA version '.$i);
        [$status]=aclHttp($session,'/pages/board/board.list.php');qa_expect($status===302,'old HTML session rejected after permission update '.$i);
        if($i===2){
            $viewOnly=['board'=>['view'=>true],'works'=>['view'=>true]];
            $db->prepare('UPDATE nb_admin SET menu_permissions=? WHERE no=?')->execute([json_encode(\Security\AdminAcl::normalize($viewOnly)),$id]);$readSession=aclSession($id);
            foreach([['/pages/board/ajax/board.process.php',['mode'=>'save']],['/pages/works/ajax/works.process.php',['_method'=>'DELETE','mode'=>'save']],['/pages/board/ajax/upload.php',['_method'=>'post']]] as [$path,$data]){
                [$status,,$json]=aclHttp($readSession,$path,$data);qa_expect($status===403&&is_array($json),'view-only request cannot mutate or upload '.$path);
            }
        }
    }
}finally{
    foreach($ids as $id){$db->prepare("DELETE FROM nb_admin_privacy_access WHERE actor_no=?")->execute([$id]);$db->prepare('DELETE FROM nb_admin_audit WHERE actor_no=? OR (entity=? AND target_no=?)')->execute([$id,'account',$id]);$db->prepare('DELETE FROM nb_admin WHERE no=? AND uid LIKE ?')->execute([$id,$tag.'%']);}
    foreach($sessions as $sid){$p=(session_save_path()?:sys_get_temp_dir()).'/sess_'.$sid;if(is_file($p))unlink($p);}
}
qa_done();
