<?php
ob_start();
require __DIR__ . '/bootstrap.php';
require_once dirname(__DIR__) . '/inc/lib/db.php';
if (APP_ENV !== 'development' || !is_file('/.dockerenv')) exit("Docker development only\n");
$db = DB::getInstance(); $no = 0; $posts = []; $sid = ''; $files = []; $bannerNo = 0;
function diagnosticRequest(string $path, string $host = 'gate.local', string $sid = '', array $data = [], ?array $file = null): array {
    $headers = "Host: $host\r\nCookie: " . session_name() . "=$sid\r\nX-Requested-With: XMLHttpRequest\r\n";
    $body = http_build_query($data);
    if ($file !== null) {
        $boundary = 'qa'.bin2hex(random_bytes(8)); $body = '';
        foreach ($data as $key=>$value) $body .= "--$boundary\r\nContent-Disposition: form-data; name=\"$key\"\r\n\r\n$value\r\n";
        $body .= "--$boundary\r\nContent-Disposition: form-data; name=\"file\"; filename=\"{$file[0]}\"\r\nContent-Type: image/png\r\n\r\n{$file[1]}\r\n--$boundary--\r\n";
        $headers .= "Content-Type: multipart/form-data; boundary=$boundary\r\n";
    } else $headers .= "Content-Type: application/x-www-form-urlencoded\r\n";
    $ctx=stream_context_create(['http'=>['method'=>$data || $file !== null ? 'POST':'GET','header'=>$headers,'content'=>$body,'ignore_errors'=>true,'follow_location'=>0,'timeout'=>10]]);
    $body=file_get_contents('http://127.0.0.1'.$path,false,$ctx);
    preg_match('/\s(\d{3})\s/', $http_response_header[0] ?? '', $m);
    return [(int)($m[1]??0),(string)$body,json_decode($body,true)];
}
try {
    $uid='qa_diag_'.bin2hex(random_bytes(4));
    $db->prepare("INSERT INTO nb_admin(sitekey,uid,upwd,uname,email,active_status,role_code,password_changed_at) VALUES('BLUESQ',?,?,?,?,'Y','super',NOW())")->execute([$uid,password_hash('Qa!234567890',PASSWORD_DEFAULT),'품질검사',$uid.'@example.com']);
    $no=(int)$db->lastInsertId();
    $stale=\Security\AdminAccount::findByNo($no);
    for($i=0;$i<LOGIN_MAX_ATTEMPTS;$i++) \Security\AdminAccount::failure($stale);
    $locked=\Security\AdminAccount::findByNo($no);
    qa_expect(strtotime($locked['login_locked_until']??'')>time(),'stale concurrent snapshots cannot lose failure increments');
    \Security\AdminAccount::failure($stale);
    qa_expect(\Security\AdminAccount::findByNo($no)['login_locked_until']===$locked['login_locked_until'],'in-flight failure cannot clear existing lock');
    $db->prepare('UPDATE nb_admin SET login_locked_until=DATE_SUB(NOW(),INTERVAL 1 SECOND) WHERE no=?')->execute([$no]);
    \Security\AdminAccount::failure($stale);
    $row=\Security\AdminAccount::findByNo($no);
    qa_expect((int)$row['login_fail_count']===1 && !$row['login_locked_until'],'expired lock starts new failure budget');
    \Security\AdminAccount::establish($row);
    $sid=session_id(); $csrf=\Security\Csrf::token(); session_write_close();
    $sessionFile=(session_save_path() ?: sys_get_temp_dir()).'/sess_'.$sid;
    if(function_exists('posix_geteuid') && posix_geteuid()===0) chown($sessionFile,'www-data');
    $db->prepare('UPDATE nb_admin SET password_changed_at=DATE_SUB(NOW(),INTERVAL 91 DAY) WHERE no=?')->execute([$no]);
    [$status,$body,$json]=diagnosticRequest('/admin/pages/board/board.list.php','gate.local',$sid);
    qa_expect($status===403 && ($json['redirect']??'')==='/admin/pages/account/password.php','expired 90-day password redirects authenticated HTTP session');
    $db->prepare('UPDATE nb_admin SET password_changed_at=NOW() WHERE no=?')->execute([$no]);
    [$status]=diagnosticRequest('/admin/pages/board/ajax/upload.php','gate.local',$sid,['_csrf'=>'incorrect','_method'=>'delete','link'=>'../../260918.sql']);
    qa_expect($status===403,'incorrect CSRF rejected before file mutation');
    [$status,$body]=diagnosticRequest('/admin/pages/board/board.list.php?searchColumn='.rawurlencode("title) OR 1=1 --").'&searchKeyword=qa','gate.local',$sid);
    qa_expect($status===200 && stripos($body,'SQLSTATE')===false && stripos($body,'Fatal error')===false,'authenticated board search rejects injected column structure');
    foreach(['N','Y'] as $visible) {
        $db->prepare("INSERT INTO nb_board(sitekey,board_no,title,contents,is_view,is_secret,regdate) VALUES('BLUESQ',999999,?,?,?,'N',NOW())")->execute([$uid.$visible,'QA synthetic fixture',$visible]);
        $posts[$visible]=(int)$db->lastInsertId();
    }
    [$status,$body,$json]=diagnosticRequest('/app/api.php?board_no=999999','localhost');
    $ids=array_column($json['rows']??[],'no');
    qa_expect($status===200 && in_array($posts['Y'],$ids) && !in_array($posts['N'],$ids),'API excludes hidden fixture while returning public fixture');
    [$status]=diagnosticRequest('/pages/board/board.view.php?board_no=1&no='.$posts['N'],'localhost');
    qa_expect($status===404,'hidden board direct URL denied even with forged board number');
    [$status]=diagnosticRequest('/pages/board/board.file.download.php?fld=attach1&no='.$posts['N'],'localhost',$sid);
    qa_expect($status===404,'hidden attachment denied even with administrator cookie');
    $before=(int)$db->query("SELECT COUNT(*) FROM nb_admin_audit WHERE actor_no=$no")->fetchColumn();
    [$status]=diagnosticRequest('/admin/pages/works/ajax/works.process.php?_method=DELETE&id=2147483647','gate.local',$sid);
    qa_expect($status===405,'GET method override cannot bypass CSRF for works deletion');
    diagnosticRequest('/admin/pages/design/ajax/banner.process.php','gate.local',$sid,['_csrf'=>$csrf,'mode'=>'edit','no'=>2147483647]);
    diagnosticRequest('/admin/pages/design/ajax/banner.process.php','gate.local',$sid,['_csrf'=>$csrf,'mode'=>'delete','no'=>2147483647]);
    diagnosticRequest('/admin/pages/board/board.list.php','gate.local',$sid,['_csrf'=>$csrf,'searchKeyword'=>'qa']);
    qa_expect((int)$db->query("SELECT COUNT(*) FROM nb_admin_audit WHERE actor_no=$no")->fetchColumn()===$before,'validation failure / nonexistent delete / read POST produce no success audit');
    [$status,$body,$json]=diagnosticRequest('/admin/pages/design/ajax/banner.process.php','gate.local',$sid,['_csrf'=>$csrf,'mode'=>'save','b_title'=>$uid,'b_loc'=>'qa','b_location'=>'qa','b_target'=>'_self','b_view'=>'N','b_none_view'=>'N','b_none_limit'=>'N','b_sdate'=>'2026-01-01','b_edate'=>'2026-01-02','b_sdate_view'=>'2026-01-01','b_edate_view'=>'2026-01-02']);
    $stmt=$db->prepare('SELECT no FROM nb_banner WHERE b_title=?'); $stmt->execute([$uid]); $bannerNo=(int)$stmt->fetchColumn();
    qa_expect($status===200 && ($json['result']??'')==='success' && $bannerNo>0,'real banner creation succeeds');
    if($bannerNo) diagnosticRequest('/admin/pages/design/ajax/banner.process.php','gate.local',$sid,['_csrf'=>$csrf,'mode'=>'delete','no'=>$bannerNo]);
    $stmt=$db->prepare("SELECT action,target_no FROM nb_admin_audit WHERE actor_no=? AND entity='nb_banner' ORDER BY no"); $stmt->execute([$no]); $audit=$stmt->fetchAll(PDO::FETCH_ASSOC);
    qa_expect(count($audit)===2 && $audit[0]['action']==='create' && $audit[1]['action']==='delete' && (int)$audit[0]['target_no']===$bannerNo && (int)$audit[1]['target_no']===$bannerNo,'database mutations audited once with correct action and target');
    [$status]=diagnosticRequest('/admin/pages/request/ajax/request.excel.php','gate.local',$sid,['_csrf'=>$csrf,'reason'=>'']);
    qa_expect($status===422,'privacy export without reason denied');
    [$status,$body,$json]=diagnosticRequest('/admin/pages/request/ajax/request.excel.php','gate.local',$sid,['_csrf'=>$csrf,'reason'=>'QA diagnostic export verification']);
    $stmt=$db->prepare("SELECT COUNT(*) FROM nb_admin_privacy_access WHERE actor_no=? AND action='download' AND reason=?"); $stmt->execute([$no,'QA diagnostic export verification']);
    qa_expect($status===200 && ($json['ok']??false) && (int)$stmt->fetchColumn()===count($json['rows']??[]),'privacy export uses actual schema and logs each subject with reason');
    $payload='"><img src=x onerror=alert(97531)>';
    $db->prepare('UPDATE nb_board SET title=?, contents=? WHERE no=?')->execute([$payload,'</textarea><script>alert(97531)</script>',$posts['Y']]);
    [$status,$body]=diagnosticRequest('/admin/pages/board/board.view.php?no='.$posts['Y'].'&board_no=999999','gate.local',$sid);
    qa_expect($status===200 && strpos($body,'<script>alert(97531)</script>')===false && strpos($body,'<img src=x onerror=alert(97531)>')===false,'stored XSS cannot escape title attribute or editor textarea');
    foreach([['bad.php','<?php echo 1;'],['bad.svg','<svg onload="alert(1)"/>'],['fake.png','<?php echo 1;'],['bad.png.php','<?php echo 1;']] as $file) {
        [$status]=diagnosticRequest('/admin/pages/board/ajax/upload.php','gate.local',$sid,['_csrf'=>$csrf],$file);
        qa_expect($status===400,'multipart rejects '.$file[0]);
    }
    $png=base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+jRZkAAAAASUVORK5CYII=');
    [$status]=diagnosticRequest('/admin/pages/board/ajax/upload.php','gate.local',$sid,['_csrf'=>$csrf],['hidden.php.png',$png]);
    qa_expect($status===400,'executable double extension rejected even with genuine PNG MIME');
    [$status,$body,$json]=diagnosticRequest('/admin/pages/board/ajax/upload.php','gate.local',$sid,['_csrf'=>$csrf],['pixel.png',$png]);
    $uploaded=$json['filename']??''; if($uploaded) $files[]=$uploaded;
    qa_expect($status===200 && preg_match('~^/uploads/board/[a-f0-9]{32}\.png$~',$uploaded),'valid multipart gets server-generated filename');
    [$status]=diagnosticRequest('/admin/pages/board/ajax/upload.php','gate.local',$sid,['_csrf'=>$csrf,'_method'=>'delete','link'=>'../../260918.sql']);
    qa_expect($status===400 && is_file(dirname(__DIR__).'/260918.sql'),'traversal deletion rejected and dump preserved');
    if($uploaded) {
        [$status,$body,$json]=diagnosticRequest('/admin/pages/board/ajax/upload.php','gate.local',$sid,['_csrf'=>$csrf,'_method'=>'delete','link'=>$uploaded]);
        qa_expect($status===200 && ($json['success']??false),'valid uploaded fixture can be deleted');
    }
    qa_expect((int)$db->query("SELECT COUNT(*) FROM nb_admin_audit WHERE actor_no=$no AND entity='board_upload'")->fetchColumn()===2,'successful upload/delete audited exactly once each');
    foreach(['/admin/pages/works/ajax/works.process.php','/admin/pages/request/ajax/request.download.php','/admin/pages/board/board.list.php'] as $path) {
        [$status]=diagnosticRequest($path);
        qa_expect($status===401,'anonymous denied before '.$path);
    }
    foreach(['board/board.role.php','admission/admission.list.php','member/member.list.php','member/member.level.php','calendar/calendar.list.php','employment/employment.list.php','admission/admission.view.php','member/member.view.php','member/member.level.view.php','calendar/calendar.view.php','employment/employment.view.php','works/edit.php','request/request.list.php','request/request.schedule.list.php','request/request.view.php','request/request.shedule.view.php'] as $relative) {
        $path='/admin/pages/'.$relative;
        if(!is_file(dirname(__DIR__).$path)) { qa_expect(false,'diagnostic route inventory missing: '.$path); continue; }
        [$status]=diagnosticRequest($path);
        qa_expect($status===401,'source CASE.3 authenticates before '.$relative);
    }
    foreach(['a@example.com'=>'*@example.com','ab@example.com'=>'a*@example.com','abc@example.com'=>'ab*@example.com'] as $input=>$expected) qa_expect(\Security\PiiMask::email($input)===$expected,'NPG email '.$input);
    qa_expect(\Security\PiiMask::phone('02-1234-5678')==='02-****-5678','NPG Seoul phone');
    qa_expect(\Security\PiiMask::name('Lionel Andres Messi')==='L***** ****** Messi','NPG English given/middle/surname');
} catch(Throwable $e) { qa_expect(false,$e->getMessage()); }
finally {
    foreach($posts as $id) $db->prepare('DELETE FROM nb_board WHERE no=? AND board_no=999999')->execute([$id]);
    if($bannerNo) $db->prepare('DELETE FROM nb_banner WHERE no=? AND b_title=?')->execute([$bannerNo,$uid]);
    foreach($files as $file) { $path=dirname(__DIR__).$file; if(is_file($path)) unlink($path); }
    if($no) { $db->prepare('DELETE FROM nb_admin_audit WHERE actor_no=?')->execute([$no]); $db->prepare('DELETE FROM nb_admin_privacy_access WHERE actor_no=?')->execute([$no]); $db->prepare('DELETE FROM nb_admin WHERE no=? AND uid=?')->execute([$no,$uid]); }
    if(!empty($sessionFile) && is_file($sessionFile)) unlink($sessionFile);
}
qa_done();
