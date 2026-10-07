<?php
ob_start();
require __DIR__.'/bootstrap.php';
require_once dirname(__DIR__).'/inc/lib/db.php';
if (APP_ENV !== 'development' || !is_file('/.dockerenv')) exit(1);
if (DB_USER !== 'qa_app') { echo "SKIP active CRUD requires isolated qa_app database\n"; exit; }
$db=DB::getInstance(); $admin=0; $sid=''; $uid='qa_crud_'.bin2hex(random_bytes(3)); $cleanup=[]; $files=[];
function crudRequest(string $path, array $data, bool $popupFile=false): array {
    $data['_csrf']=$GLOBALS['csrf'];
    $content=http_build_query($data); $type='application/x-www-form-urlencoded';
    if($popupFile) {
        $boundary='qa'.bin2hex(random_bytes(8));$content='';$type='multipart/form-data; boundary='.$boundary;
        foreach($data as $key=>$value)$content.="--$boundary\r\nContent-Disposition: form-data; name=\"$key\"\r\n\r\n$value\r\n";
        $content.="--$boundary\r\nContent-Disposition: form-data; name=\"p_img\"; filename=\"qa.png\"\r\nContent-Type: image/png\r\n\r\n".base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+aN2QAAAAASUVORK5CYII=')."\r\n--$boundary--\r\n";
    }
    $qaHost=(string)blue_env('GATE_HOST','gate.local');
    $requestPath=blue_env('GATE_ROUTE_MODE','path')==='internal' ? substr($path,6) : $path;
    $ctx=stream_context_create(['http'=>['method'=>'POST','header'=>"Host: ".$qaHost."\r\nCookie: ".session_name().'='.$GLOBALS['sid']."\r\nX-Requested-With: XMLHttpRequest\r\nContent-Type: $type\r\n",'content'=>$content,'ignore_errors'=>true,'follow_location'=>0,'timeout'=>10]]);
    $body=file_get_contents('http://127.0.0.1'.$requestPath,false,$ctx);preg_match('/\s(\d{3})\s/',$http_response_header[0]??'',$m);
    return [(int)($m[1]??0),json_decode($body,true),(string)$body];
}
function crudOk(array $response): bool {return $response[0]===200 && (!empty($response[1]['success']) || ($response[1]['result']??'')==='success');}
try {
    $db->prepare("INSERT INTO nb_admin(sitekey,uid,upwd,uname,email,active_status,role_code,password_changed_at) VALUES('BLUESQ',?,?,?,?,'Y','super',NOW())")->execute([$uid,password_hash(bin2hex(random_bytes(24)),PASSWORD_DEFAULT),'QA',$uid.'@example.com']);
    $admin=(int)$db->lastInsertId(); \Security\AdminAccount::establish(\Security\AdminAccount::findByNo($admin));
    $sid=session_id();$csrf=\Security\Csrf::token();session_write_close();
    if(posix_geteuid()===0)chown((session_save_path()?:sys_get_temp_dir()).'/sess_'.$sid,'www-data');
    $cases=[
        ['opera','id','title','/admin/pages/opera/ajax/opera.process.php',['title'=>$uid,'state'=>0,'link'=>'https://example.com'],['mode'=>'save'],['mode'=>'edit'],['mode'=>'delete']],
        ['works','id','title','/admin/pages/works/ajax/works.process.php',['title'=>$uid,'genre'=>1,'start_date'=>'2026-10-01','end_date'=>'2026-10-02'],['_method'=>'POST'],['_method'=>'UPDATE'],['_method'=>'DELETE']],
        ['popup','no','p_title','/admin/pages/design/ajax/popup.process.php',['p_title'=>$uid,'p_view'=>'N','p_none_limit'=>'Y','p_loc'=>'M','p_target'=>'_self','p_idx'=>1],['mode'=>'save'],['mode'=>'edit'],['mode'=>'delete']],
        ['banner','no','b_title','/admin/pages/design/ajax/banner.process.php',['b_title'=>$uid,'b_loc'=>'qa','b_location'=>'qa','b_target'=>'_self','b_view'=>'N','b_none_view'=>'N','b_none_limit'=>'N','b_sdate'=>'2026-10-01','b_edate'=>'2026-10-02','b_sdate_view'=>'2026-10-01','b_edate_view'=>'2026-10-02'],['mode'=>'save'],['mode'=>'edit'],['mode'=>'delete']],
        ['data','no','target','/admin/pages/setting/ajax/setting.data.process.php',['target'=>$uid,'content'=>'QA'],['mode'=>'save'],['mode'=>'edit'],['mode'=>'delete']],
    ];
    foreach($cases as [$name,$key,$label,$path,$data,$create,$update,$delete]) {
        $table='nb_'.$name;
        if($name==='banner') $data['b_idx']=1;
        $response=crudRequest($path,$create+$data,$name==='popup');
        $q=$db->prepare("SELECT $key FROM $table WHERE $label=? ORDER BY $key DESC LIMIT 1");$q->execute([$data[$label]]);$id=(int)$q->fetchColumn();
        qa_expect(crudOk($response) && $id>0,$name.' actual HTTP create');
        if(!$id)continue;
        $cleanup[]=[$table,$key,$id];
        if($name==='popup') {
            $q=$db->prepare('SELECT p_img FROM nb_popup WHERE no=?');$q->execute([$id]);
            $files[]=dirname(__DIR__).'/uploads/popup/'.$q->fetchColumn();
        }
        $response=crudRequest($path,$update+['id'=>$id,'no'=>$id]+$data);
        qa_expect(crudOk($response),$name.' same-value update');
        if($name==='popup') {
            $old=end($files);
            $response=crudRequest($path,$update+['id'=>$id,'no'=>$id]+$data,true);
            $q=$db->prepare('SELECT p_img FROM nb_popup WHERE no=?');$q->execute([$id]);
            $replacement=dirname(__DIR__).'/uploads/popup/'.$q->fetchColumn();$files[]=$replacement;
            qa_expect(crudOk($response) && is_file($replacement) && $replacement!==$old,'popup replacement image persists');
        }
        $changed=$data;$changed[$label].='_updated';
        $response=crudRequest($path,$update+['id'=>$id,'no'=>$id]+$changed);
        $q=$db->prepare("SELECT $label FROM $table WHERE $key=?");$q->execute([$id]);
        qa_expect(crudOk($response) && $q->fetchColumn()===$changed[$label],$name.' changed value persisted');
        $q=$db->prepare('SELECT COUNT(*) FROM nb_admin_audit WHERE actor_no=?');$q->execute([$admin]);$before=(int)$q->fetchColumn();
        crudRequest($path,$delete+['id'=>2147483647,'no'=>2147483647]);$q->execute([$admin]);
        qa_expect((int)$q->fetchColumn()===$before,$name.' nonexistent delete creates no audit');
        $response=crudRequest($path,$delete+['id'=>$id,'no'=>$id]);
        $q=$db->prepare("SELECT COUNT(*) FROM $table WHERE $key=?");$q->execute([$id]);
        qa_expect(crudOk($response) && (int)$q->fetchColumn()===0,$name.' actual HTTP delete');
        $q=$db->prepare("SELECT action FROM nb_admin_audit WHERE actor_no=? AND entity=? AND target_no=? ORDER BY no");$q->execute([$admin,$table,$id]);$actions=$q->fetchAll(PDO::FETCH_COLUMN);
        qa_expect(in_array('create',$actions,true)&&in_array('update',$actions,true)&&end($actions)==='delete',$name.' CRUD audit action and target');
    }
    $filename=$uid.'.txt';$private=dirname(__DIR__).'/uploads/board/'.$filename;
    file_put_contents($private,'QA synthetic private attachment');$files[]=$private;
    $db->prepare("INSERT INTO nb_request(sitekey,manager_name,email,phone,performance_name,file_1,file_1_origin,regdate) VALUES('BLUESQ',?,?,?,?,?,?,NOW())")
       ->execute(['검증담당자',$uid.'@example.com','01012345678',$uid,$filename,'qa.txt']);
    $request=(int)$db->lastInsertId();$cleanup[]=['nb_request','no',$request];
    $path='/admin/pages/request/ajax/request.download.php';
    $response=crudRequest($path,['no'=>$request,'field'=>'file_1','reason'=>'']);
    qa_expect($response[0]===400,'private attachment requires reason');
    $response=crudRequest($path,['no'=>$request,'field'=>'file_1','reason'=>'QA attachment verification']);
    if ($response[2] !== 'QA synthetic private attachment') echo 'DOWNLOAD diagnostic status='.$response[0].' length='.strlen($response[2]).' prefix='.bin2hex(substr($response[2],0,12))."\n";
    qa_expect($response[0]===200 && $response[2]==='QA synthetic private attachment','authorized private attachment exact bytes');
    $q=$db->prepare("SELECT COUNT(*) FROM nb_admin_privacy_access WHERE actor_no=? AND target_no=? AND action='download' AND reason=?");$q->execute([$admin,$request,'QA attachment verification']);
    qa_expect((int)$q->fetchColumn()===1,'private attachment subject and reason logged once');
    $response=crudRequest($path,['no'=>2147483647,'field'=>'file_1','reason'=>'QA attachment verification']);
    qa_expect($response[0]===404,'nonexistent private subject denied');
    $response=crudRequest($path,['no'=>$request,'field'=>'../file_1','reason'=>'QA attachment verification']);
    qa_expect($response[0]===400,'private attachment field injection denied');
    $response=crudRequest('/admin/pages/request/ajax/request.process.php',['mode'=>'delete','no'=>$request]);
    $q=$db->prepare('SELECT COUNT(*) FROM nb_request WHERE no=?');$q->execute([$request]);
    qa_expect(crudOk($response) && (int)$q->fetchColumn()===0,'rental fixture deleted through authenticated HTTP');
} catch(Throwable $e) {qa_expect(false,$e->getMessage());}
finally {
    foreach($files as $file)if(is_file($file))unlink($file);
    foreach($cleanup as [$table,$key,$id])$db->prepare("DELETE FROM $table WHERE $key=?")->execute([$id]);
    if($admin){$db->prepare('DELETE FROM nb_admin_audit WHERE actor_no=?')->execute([$admin]);$db->prepare('DELETE FROM nb_admin_privacy_access WHERE actor_no=?')->execute([$admin]);$db->prepare('DELETE FROM nb_admin WHERE no=? AND uid=?')->execute([$admin,$uid]);}
    if($sid){$file=(session_save_path()?:sys_get_temp_dir()).'/sess_'.$sid;if(is_file($file))unlink($file);}
}
qa_done();
