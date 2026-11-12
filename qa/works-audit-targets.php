<?php
ob_start();
require __DIR__.'/bootstrap.php';
require_once dirname(__DIR__).'/inc/lib/db.php';
if (!is_file('/.dockerenv') || (APP_ENV !== 'development' && !(APP_ENV === 'production' && getenv('QA_PRODUCTION_HTTP') === '1' && getenv('GATE_ALLOW_HTTP') === 'true'))) exit(1);
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
    $legacy=getenv('QA_LEGACY_ADMIN')==='1';
    if($legacy){$qaHost=(string)blue_env('PUBLIC_HOST');$requestPath=$path;}
    elseif(blue_env('GATE_ROUTE_MODE','path')==='php')$requestPath='/index.php'.substr($path,6);
    $ctx=stream_context_create(['http'=>['method'=>'POST','header'=>"Host: ".$qaHost."\r\n".($legacy?"X-QA-HTTPS: 1\r\n":'')."Cookie: ".session_name().'='.$GLOBALS['sid']."\r\nX-Requested-With: XMLHttpRequest\r\nContent-Type: $type\r\n",'content'=>$content,'ignore_errors'=>true,'follow_location'=>0,'timeout'=>10]]);
    $body=file_get_contents('http://127.0.0.1'.$requestPath,false,$ctx);preg_match('/\s(\d{3})\s/',$http_response_header[0]??'',$m);
    return [(int)($m[1]??0),json_decode($body,true),(string)$body];
}
function crudOk(array $response): bool {return $response[0]===200 && (!empty($response[1]['success']) || ($response[1]['result']??'')==='success');}
try {
 $db->prepare("INSERT INTO nb_admin(sitekey,uid,upwd,uname,email,active_status,role_code,password_changed_at) VALUES('BLUESQ',?,?,?,?,'Y','super',NOW())")->execute([$uid,password_hash(bin2hex(random_bytes(20)),PASSWORD_DEFAULT),'QA',$uid.'@example.com']);$admin=(int)$db->lastInsertId();
 \Security\AdminAccount::establish(\Security\AdminAccount::findByNo($admin));$sid=session_id();$csrf=\Security\Csrf::token();session_write_close();if(posix_geteuid()===0)chown((session_save_path()?:sys_get_temp_dir()).'/sess_'.$sid,'www-data');
 $ids=[];
 for($i=0;$i<2;$i++){$r=crudRequest('/admin/pages/works/ajax/works.process.php',['_method'=>'POST','title'=>$uid.'_'.$i,'genre'=>1,'start_date'=>'2026-10-01','end_date'=>'2026-10-02']);$id=(int)($r[1]['data']['id']??0);if(!$id)throw new RuntimeException('Cannot create isolated works fixture');$ids[]=$id;$cleanup[]=['nb_works','id',$id];}
 $r=crudRequest('/admin/pages/works/ajax/works.process.php',['_method'=>'BATCH_DELETE','ids'=>json_encode(array_merge($ids,[2147483647]))]);qa_expect(crudOk($r),'works batch delete accepts existing targets with one missing ID');
 $s=$db->prepare("SELECT detail_json FROM nb_admin_audit WHERE actor_no=? AND action='delete' AND entity='nb_works' ORDER BY no DESC LIMIT 1");$s->execute([$admin]);$detail=json_decode((string)$s->fetchColumn(),true);$got=$detail['target_ids']??[];sort($got);sort($ids);
 qa_expect($got===$ids && ($detail['affected_rows']??0)===2,'works id-column audit records only actual deleted target IDs');
 $s=$db->prepare('SELECT COUNT(*) FROM nb_works WHERE id IN(?,?)');$s->execute($ids);qa_expect((int)$s->fetchColumn()===0,'works audited batch actually deletes both fixtures');
}catch(Throwable $e){qa_expect(false,$e->getMessage());}
finally{
 foreach($cleanup as [$table,$key,$id])$db->prepare("DELETE FROM $table WHERE $key=?")->execute([$id]);
 if($admin){$db->prepare('DELETE FROM nb_admin_audit WHERE actor_no=?')->execute([$admin]);$db->prepare('DELETE FROM nb_admin_privacy_access WHERE actor_no=?')->execute([$admin]);$db->prepare('DELETE FROM nb_admin WHERE no=? AND uid=?')->execute([$admin,$uid]);}
 if($sid){$p=(session_save_path()?:sys_get_temp_dir()).'/sess_'.$sid;if(is_file($p))unlink($p);}
}
qa_done();
