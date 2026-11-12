<?php
ob_start();require __DIR__.'/bootstrap.php';require dirname(__DIR__).'/inc/lib/db.php';
if(!is_file('/.dockerenv')||APP_ENV!=='development'||DB_USER!=='qa_app')exit(1);
$db=DB::getInstance();$uid='qa_save_'.bin2hex(random_bytes(5));$admin=0;$sid='';$rows=[];$paths=[];$siteBackup=null;
$UPLOAD_SITEINFO_DIR_LOGO=dirname(__DIR__).'/uploads/logo';$UPLOAD_META_DIR=dirname(__DIR__).'/uploads/meta';
function saveHttp(string $path,?array $data=null,array $uploads=[]):array {
    $headers='Host: '.GATE_HOST."\r\nCookie: ".session_name().'='.$GLOBALS['sid']."\r\n";$body='';
    if($data!==null){$data['_csrf']=$GLOBALS['csrf'];$headers.="X-Requested-With: XMLHttpRequest\r\n";
        if($uploads){$boundary='qa'.bin2hex(random_bytes(8));$headers.="Content-Type: multipart/form-data; boundary=$boundary\r\n";
            foreach($data as $k=>$v)$body.="--$boundary\r\nContent-Disposition: form-data; name=\"$k\"\r\n\r\n$v\r\n";
            foreach($uploads as $k=>$valid){$bytes=$valid?base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+aN2QAAAAASUVORK5CYII='):'<svg><script>1</script></svg>';$body.="--$boundary\r\nContent-Disposition: form-data; name=\"$k\"; filename=\"qa.png\"\r\nContent-Type: image/png\r\n\r\n$bytes\r\n";}$body.="--$boundary--\r\n";
        }else{$headers.="Content-Type: application/x-www-form-urlencoded\r\n";$body=http_build_query($data);}
    }
    $ctx=stream_context_create(['http'=>['method'=>$data===null?'GET':'POST','header'=>$headers,'content'=>$body,'ignore_errors'=>true,'follow_location'=>0,'timeout'=>15]]);
    $body=(string)file_get_contents('http://127.0.0.1/index.php'.$path,false,$ctx);preg_match('/\s(\d{3})\s/',$http_response_header[0]??'',$m);
    return [(int)($m[1]??0),json_decode($body,true),$body,implode("\n",$http_response_header)];
}
function saveRow(string $table,int $id,string $key='id'):array {$q=$GLOBALS['db']->prepare("SELECT * FROM $table WHERE $key=?");$q->execute([$id]);return $q->fetch(PDO::FETCH_ASSOC)?:[];}
function saveFiles(string $root):array {clearstatcache();$a=glob($root.'/*')?:[];sort($a);return $a;}
try{
    $db->prepare("INSERT INTO nb_admin(sitekey,uid,upwd,uname,email,active_status,role_code,password_changed_at)VALUES('BLUESQ',?,?,?,?,'Y','super',NOW())")->execute([$uid,password_hash(bin2hex(random_bytes(24)),PASSWORD_DEFAULT),'QA',$uid.'@example.com']);
    $admin=(int)$db->lastInsertId();\Security\AdminAccount::establish(\Security\AdminAccount::findByNo($admin));$sid=session_id();$csrf=\Security\Csrf::token();session_write_close();if(posix_geteuid()===0)chown((session_save_path()?:sys_get_temp_dir()).'/sess_'.$sid,'www-data');
    $account=['mode'=>'create','uid'=>$uid.'_u','uname'=>'QA','email'=>$uid.'_u@example.com','role_code'=>'admin','active_status'=>'Y','change_reason'=>'QA account validation'];
    $r=saveHttp('/pages/account/process.php',$account);$id=(int)($r[1]['credential']['id']??0);if($id)$rows[]=['nb_admin',$id,'no'];
    qa_expect($r[0]===200&&!empty($r[1]['success'])&&$id>0,'account create returns JSON credential');
    $again=saveHttp('/pages/account/process.php',$account);qa_expect($again[0]===200&&($again[1]['success']??null)===false&&strpos($again[3],'application/json')!==false,'duplicate account stays JSON domain error for hosting ErrorDocument');
    $r=saveHttp('/pages/account/process.php',array_merge($account,['uid'=>$uid.'_v','change_reason'=>'']));qa_expect($r[0]===200&&empty($r[1]['success']),'missing reason yields JSON and no account');
    if($id){$r=saveHttp('/pages/account/process.php',array_merge($account,['mode'=>'update','no'=>$id,'permissions_present'=>'1']));qa_expect($r[0]===302&&strpos($r[3],'/index.php/pages/account/index.php')!==false&&strpos($r[3],'/index.php/index.php/')===false,'account update redirect resolves to gate account list');}
    $r=saveHttp('/pages/account/new.php');qa_expect($r[0]===200&&strpos($r[2],'account-permissions__scroll')!==false&&strpos($r[2],'/index.php/pages/account/process.php')!==false,'account form action and scoped permissions markup');
    $path='/pages/works/ajax/works.process.php';$work=['_method'=>'POST','title'=>$uid,'genre'=>'1','start_date'=>'2026-11-01','end_date'=>'2026-11-02','contents'=>'</textarea><script>window.qa=1</script>'];
    $r=saveHttp($path,$work,['thumbnail_image'=>true]);$id=(int)($r[1]['data']['id']??0);if(!$id)throw new RuntimeException('work fixture create failed');$rows[]=['nb_works',$id,'id'];$old=saveRow('nb_works',$id);$root=dirname(__DIR__).'/uploads/works';$paths[]=$root.'/'.$old['thumbnail_image'];
    qa_expect(!empty($r[1]['success'])&&is_file(end($paths)),'works multipart create');
    $base=array_merge($work,['_method'=>'UPDATE','id'=>$id]);$r=saveHttp($path,$base);qa_expect(!empty($r[1]['success'])&&saveRow('nb_works',$id)['thumbnail_image']===$old['thumbnail_image'],'works no-file update preserves image');
    foreach(['true','no']as $title){$r=saveHttp($path,array_merge($base,['title'=>$title]));qa_expect(!empty($r[1]['success'])&&saveRow('nb_works',$id)['title']===$title,'works title '.$title.' preserved as text');}
    $before=saveRow('nb_works',$id);$files=saveFiles($root);$r=saveHttp($path,$base,['thumbnail_image'=>true,'tablet_image'=>false]);qa_expect(empty($r[1]['success'])&&saveRow('nb_works',$id)===$before&&is_file($root.'/'.$old['thumbnail_image']),'works second upload failure preserves existing DB and image');qa_expect(saveFiles($root)===$files,'works staged first upload removed after second upload rejection');
    $r=saveHttp($path,array_merge($base,['end_date'=>'2026-10-31']));qa_expect(empty($r[1]['success'])&&saveRow('nb_works',$id)===$before,'works reversed dates rejected without mutation');
    $r=saveHttp($path,array_merge($base,['id'=>$id.' OR 1=1']));qa_expect(empty($r[1]['success'])&&saveRow('nb_works',$id)===$before,'works invalid ID rejected');
    $r=saveHttp('/pages/works/edit.php?id='.$id);qa_expect($r[0]===200&&strpos($r[2],'&lt;/textarea&gt;')!==false&&strpos($r[2],'</textarea><script>window.qa=1')===false,'works textarea output escapes closing tags');
    $r=saveHttp('/pages/works/edit.php?id=2147483647');qa_expect($r[0]===404,'works missing edit target returns 404');
    $clone=$before;unset($clone['id']);$clone['title']=$uid.'_shared';$keys=array_keys($clone);$db->prepare('INSERT INTO nb_works (`'.implode('`,`',$keys).'`)VALUES('.implode(',',array_fill(0,count($keys),'?')).')')->execute(array_values($clone));$shared=(int)$db->lastInsertId();$rows[]=['nb_works',$shared,'id'];
    $r=saveHttp($path,$base,['thumbnail_image'=>true]);$new=saveRow('nb_works',$id)['thumbnail_image'];$paths[]=$root.'/'.$new;clearstatcache();qa_expect(!empty($r[1]['success'])&&$new!==$old['thumbnail_image']&&is_file($root.'/'.$old['thumbnail_image']),'works replacement retains shared original');
    $r=saveHttp($path,['_method'=>'BATCH_DELETE','ids'=>json_encode([$id,$shared])]);clearstatcache();qa_expect(!empty($r[1]['success'])&&!saveRow('nb_works',$id)&&!saveRow('nb_works',$shared)&&!is_file($root.'/'.$new)&&!is_file($root.'/'.$old['thumbnail_image']),'works batch delete removes only final references');
    $path='/pages/request/ajax/request.process.php';$schedule=['mode'=>'manageSave','r_title'=>$uid,'r_view'=>'N','r_sdate'=>'2026-11-01','r_edate'=>'2026-11-02','contents'=>'QA'];$r=saveHttp($path,$schedule);$q=$db->prepare('SELECT no FROM nb_request_manage WHERE r_title=?');$q->execute([$uid]);$id=(int)$q->fetchColumn();if(!$id)throw new RuntimeException('schedule fixture create failed');$rows[]=['nb_request_manage',$id,'no'];qa_expect(($r[1]['result']??'')==='success','rental schedule creates without caller supplied ID');
    $old=saveRow('nb_request_manage',$id,'no');$r=saveHttp($path,array_merge($schedule,['no'=>$id,'r_title'=>'unexpected overwrite']));qa_expect(($r[1]['result']??'')==='fail'&&saveRow('nb_request_manage',$id,'no')===$old,'create schedule cannot overwrite existing ID');
    $r=saveHttp($path,array_merge($schedule,['mode'=>'manageEdit','no'=>$id,'r_title'=>$uid.'_edit']));qa_expect(($r[1]['result']??'')==='success'&&saveRow('nb_request_manage',$id,'no')['r_title']===$uid.'_edit','rental schedule update');
    $old=saveRow('nb_request_manage',$id,'no');$r=saveHttp($path,array_merge($schedule,['mode'=>'manageEdit','no'=>$id,'r_edate'=>'2026-10-01']));qa_expect(($r[1]['result']??'')==='fail'&&saveRow('nb_request_manage',$id,'no')===$old,'rental reversed dates rejected');
    $r=saveHttp($path,['mode'=>'manageDelete','no'=>$id]);qa_expect(($r[1]['result']??'')==='success'&&!saveRow('nb_request_manage',$id,'no'),'rental schedule delete');
    foreach(['match','other']as $suffix){$db->prepare("INSERT INTO nb_request(sitekey,manager_name,email,phone,performance_name,regdate)VALUES('BLUESQ',?,?,?,?,?)")->execute(['QA',$uid.'@example.com','01000000000',$uid.'_'.$suffix,'2026-11-10 12:00:00']);$rows[]=['nb_request',(int)$db->lastInsertId(),'no'];}
    $r=saveHttp('/pages/request/request.list.php?'.http_build_query(['searchColumn'=>'a.performance_name','searchKeyword'=>$uid.'_match','sdate'=>'2026-11-10','edate'=>'2026-11-10']));qa_expect($r[0]===200&&strpos($r[2],$uid.'_match')!==false&&strpos($r[2],$uid.'_other')===false,'rental date and performance search actually filters rows');
    $r=saveHttp('/pages/request/request.list.php?'.http_build_query(['searchColumn'=>'a.performance_name','searchKeyword'=>$uid.'_match','sdate'=>'2026-11-11']));qa_expect($r[0]===200&&strpos($r[2],'/request.view.php?no=')===false,'rental date range excludes earlier requests');
    $r=saveHttp('/pages/request/request.list.php?'.http_build_query(['searchColumn'=>'a.title OR 1=1','searchKeyword'=>"' OR 1=1 --",'sdate'=>'2026-02-30']));qa_expect($r[0]===200&&strpos($r[2],'<b>Warning</b>')===false&&strpos($r[2],'<b>Notice</b>')===false,'rental invalid search column and calendar value remain safe');
    // Preserve the exact local site row and its files even though business tables use MyISAM.
    $siteBackup=$db->query("SELECT * FROM nb_siteinfo WHERE sitekey='BLUESQ'")->fetch(PDO::FETCH_ASSOC);$siteFiles=[];
    foreach(['logo_top','logo_footer','meta_thumb','meta_favicon_ico']as $key){$root=in_array($key,['logo_top','logo_footer'],true)?$UPLOAD_SITEINFO_DIR_LOGO:$UPLOAD_META_DIR;$f=$root.'/'.basename($siteBackup[$key]??'');if(is_file($f))$siteFiles[$f]=file_get_contents($f);}
    $config=['mode'=>'setting.config.save']+$siteBackup;$logoBefore=saveFiles($UPLOAD_SITEINFO_DIR_LOGO);$metaBefore=saveFiles($UPLOAD_META_DIR);
    $r=saveHttp('/pages/setting/ajax/setting.process.php',$config,['logo_top'=>true,'meta_thumb'=>false]);$now=$db->query("SELECT * FROM nb_siteinfo WHERE sitekey='BLUESQ'")->fetch(PDO::FETCH_ASSOC);qa_expect(($r[1]['result']??'')==='fail'&&$now===$siteBackup,'site configuration failed upload preserves original row');qa_expect(saveFiles($UPLOAD_SITEINFO_DIR_LOGO)===$logoBefore&&saveFiles($UPLOAD_META_DIR)===$metaBefore,'site configuration failed upload removes staged image');
    $r=saveHttp('/pages/setting/ajax/setting.process.php',$config,['logo_top'=>true]);$now=$db->query("SELECT * FROM nb_siteinfo WHERE sitekey='BLUESQ'")->fetch(PDO::FETCH_ASSOC);$paths[]=$UPLOAD_SITEINFO_DIR_LOGO.'/'.$now['logo_top'];qa_expect(($r[1]['result']??'')==='success'&&$now['logo_top']!==$siteBackup['logo_top']&&is_file(end($paths)),'site configuration successful image replacement');
}catch(Throwable $e){qa_expect(false,$e->getMessage());}
finally{
    if($siteBackup){$keys=array_diff(array_keys($siteBackup),['no']);$values=[];foreach($keys as $k)$values[]=$siteBackup[$k];$values[]=$siteBackup['no'];$db->prepare('UPDATE nb_siteinfo SET '.implode(',',array_map(static fn($k)=>'`'.$k.'`=?',$keys)).' WHERE no=?')->execute($values);foreach($siteFiles??[]as $f=>$bytes)file_put_contents($f,$bytes);}
    foreach($rows as [$table,$id,$key]){$db->prepare("DELETE FROM $table WHERE $key=?")->execute([$id]);if($table==='nb_admin')$db->prepare('DELETE FROM nb_admin_audit WHERE target_no=? AND actor_no=?')->execute([$id,$admin]);}
    foreach($paths as $f){clearstatcache(true,$f);if(is_file($f))unlink($f);}
    if($admin){$db->prepare('DELETE FROM nb_admin_privacy_access WHERE actor_no=?')->execute([$admin]);$db->prepare('DELETE FROM nb_admin_audit WHERE actor_no=?')->execute([$admin]);$db->prepare('DELETE FROM nb_admin WHERE no=? AND uid=?')->execute([$admin,$uid]);}
    if($sid){$f=(session_save_path()?:sys_get_temp_dir()).'/sess_'.$sid;if(is_file($f))unlink($f);}
}
qa_done();
