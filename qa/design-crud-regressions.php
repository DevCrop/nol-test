<?php
ob_start();
require __DIR__.'/bootstrap.php';
require_once dirname(__DIR__).'/inc/lib/db.php';
if(!is_file('/.dockerenv') || DB_USER!=='qa_app' || APP_ENV!=='development')exit("SKIP isolated development Docker required\n");
$db=DB::getInstance();$admin=0;$sid='';$rows=[];$roots=[];$uid='qa_design_'.bin2hex(random_bytes(4));
function designHttp(string $kind,array $data,array $uploads=[]):array {
    $data['_csrf']=$GLOBALS['csrf'];$boundary='qa'.bin2hex(random_bytes(8));$body='';
    foreach($data as $key=>$value)$body.="--$boundary\r\nContent-Disposition: form-data; name=\"$key\"\r\n\r\n$value\r\n";
    foreach($uploads as $key=>$valid){$bytes=$valid?base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+aN2QAAAAASUVORK5CYII='):'<?php echo 1;';
        $body.="--$boundary\r\nContent-Disposition: form-data; name=\"$key\"; filename=\"qa.png\"\r\nContent-Type: image/png\r\n\r\n$bytes\r\n";}
    $body.="--$boundary--\r\n";$mode=blue_env('GATE_ROUTE_MODE','path');$path='/pages/design/ajax/'.$kind.'.process.php';
    $path=$mode==='php'?'/index.php'.$path:($mode==='internal'?$path:'/admin'.$path);
    $ctx=stream_context_create(['http'=>['method'=>'POST','header'=>'Host: '.blue_env('GATE_HOST','gate.local')."\r\nCookie: ".session_name().'='.$GLOBALS['sid']."\r\nX-Requested-With: XMLHttpRequest\r\nContent-Type: multipart/form-data; boundary=$boundary\r\n",'content'=>$body,'ignore_errors'=>true,'follow_location'=>0,'timeout'=>10]]);
    $result=file_get_contents('http://127.0.0.1'.$path,false,$ctx);preg_match('/\s(\d{3})\s/',$http_response_header[0]??'',$m);
    return [(int)($m[1]??0),json_decode($result,true)];
}
function designFiles(string $root):array {clearstatcache();$files=glob($root.'/*')?:[];sort($files);return $files;}
function designPublicHome():array {
    $ctx=stream_context_create(['http'=>['header'=>'Host: '.blue_env('PUBLIC_HOST','localhost')."\r\n",'ignore_errors'=>true,'follow_location'=>0,'timeout'=>10]]);
    $body=(string)file_get_contents('http://127.0.0.1/',false,$ctx);preg_match('/\s(\d{3})\s/',$http_response_header[0]??'',$m);
    return [(int)($m[1]??0),$body];
}
try {
    $db->prepare("INSERT INTO nb_admin(sitekey,uid,upwd,uname,email,active_status,role_code,password_changed_at) VALUES('BLUESQ',?,?,?,?,'Y','super',NOW())")->execute([$uid,password_hash(bin2hex(random_bytes(24)),PASSWORD_DEFAULT),'QA',$uid.'@example.com']);
    $admin=(int)$db->lastInsertId();\Security\AdminAccount::establish(\Security\AdminAccount::findByNo($admin));$sid=session_id();$csrf=\Security\Csrf::token();session_write_close();
    if(posix_geteuid()===0)chown((session_save_path()?:sys_get_temp_dir()).'/sess_'.$sid,'www-data');
    foreach(['banner','popup']as $kind) {
        $root=dirname(__DIR__).'/uploads/'.$kind;$roots[$kind]=$root;
        $column=$kind==='banner'?'b_img':'p_img';$label=$kind==='banner'?'b_title':'p_title';$table='nb_'.$kind;
        $data=$kind==='banner'?['b_title'=>$uid,'b_loc'=>'qa','b_location'=>'qa','b_target'=>'_self','b_view'=>'N','b_none_view'=>'Y','b_none_limit'=>'Y']:['p_title'=>$uid,'p_view'=>'N','p_none_limit'=>'Y','p_loc'=>'M','p_target'=>'_self'];
        $response=designHttp($kind,['mode'=>'save']+$data,[$column=>true]);
        $q=$db->prepare("SELECT * FROM $table WHERE $label=? ORDER BY no DESC LIMIT 1");$q->execute([$uid]);$row=$q->fetch(PDO::FETCH_ASSOC);$id=(int)($row['no']??0);
        qa_expect($response[0]===200&&$id>0,$kind.' multipart create');
        if(!$id)continue;$rows[]=[$table,$id];$old=$row[$column];$before=designFiles($root);
        $bad=$kind==='banner'?['b_img'=>true,'b_img_mobile'=>false]:['p_img'=>false];
        $response=designHttp($kind,['mode'=>'edit','no'=>$id]+$data,$bad);
        $q=$db->prepare("SELECT $column FROM $table WHERE no=?");$q->execute([$id]);
        qa_expect($response[0]===400 && $q->fetchColumn()===$old && is_file($root.'/'.$old),$kind.' failed upload preserves DB and old image');
        qa_expect(designFiles($root)===$before,$kind.' failed upload leaves no orphan');
        $response=designHttp($kind,['mode'=>'edit','no'=>$id]+$data);$q->execute([$id]);
        qa_expect($response[0]===200&&$q->fetchColumn()===$old,$kind.' no-file edit preserves image column');
        $shared=$uid.'_shared';
        $clone=$row;unset($clone['no']);$clone[$label]=$shared;$columns=array_keys($clone);
        $copy=$db->prepare("INSERT INTO $table (`".implode('`,`',$columns)."`) VALUES (".implode(',',array_fill(0,count($columns),'?')).")");$copy->execute(array_values($clone));$sharedId=(int)$db->lastInsertId();$rows[]=[$table,$sharedId];
        $response=designHttp($kind,['mode'=>'edit','no'=>$id]+$data,[$column=>true]);$q->execute([$id]);$new=$q->fetchColumn();clearstatcache();
        qa_expect($response[0]===200&&$new!==$old&&is_file($root.'/'.$new)&&is_file($root.'/'.$old),$kind.' replacement preserves shared old reference');
        $response=designHttp($kind,['mode'=>'delete','no'=>$id]);clearstatcache();
        qa_expect($response[0]===200&&!is_file($root.'/'.$new)&&is_file($root.'/'.$old),$kind.' delete retires only unreferenced image');
        designHttp($kind,['mode'=>'delete','no'=>$sharedId]);clearstatcache();
        qa_expect(!is_file($root.'/'.$old),$kind.' final reference delete retires old image');
    }
    // Submit the actual banner endpoint, then compare the anonymous public home.
    $base=['b_loc'=>'site_main','b_location'=>'qa-rank','b_target'=>'_self','b_link'=>'/pages/cs/parking.php','b_view'=>'Y','b_none_view'=>'Y','b_none_limit'=>'Y'];
    $rankIds=[];
    foreach(['A'=>999,'B'=>1000]as $suffix=>$rank){
        $data=array_merge($base,['mode'=>'save','b_title'=>$uid.'_rank'.$suffix,'b_idx'=>(string)$rank]);
        $r=designHttp('banner',$data,['b_img'=>true]);$q=$db->prepare('SELECT * FROM nb_banner WHERE b_title=? ORDER BY no DESC LIMIT 1');$q->execute([$data['b_title']]);$row=$q->fetch(PDO::FETCH_ASSOC);
        $id=(int)($row['no']??0);if($id){$rows[]=['nb_banner',$id];$rankIds[$suffix]=$id;}
        qa_expect($r[0]===200&&($r[1]['result']??'')==='success'&&$id>0&&(int)$row['b_idx']===$rank,'banner new explicit rank '.$rank.' persists');
    }
    if(count($rankIds)===2){
        [$status,$html]=designPublicHome();$a=strpos($html,$uid.'_rankA');$b=strpos($html,$uid.'_rankB');
        qa_expect($status===200&&$a!==false&&$b!==false&&$a<$b,'banner new ranks control actual public order');
        $data=array_merge($base,['mode'=>'edit','no'=>$rankIds['A'],'b_title'=>$uid.'_rankA','b_idx'=>'1001']);$r=designHttp('banner',$data);
        [$status,$html]=designPublicHome();$a=strpos($html,$uid.'_rankA');$b=strpos($html,$uid.'_rankB');
        qa_expect($r[0]===200&&($r[1]['result']??'')==='success'&&$status===200&&$a!==false&&$b!==false&&$b<$a,'banner edited rank reorders actual public home');
        $r=designHttp('banner',array_replace($data,['b_idx'=>'']));$q=$db->prepare('SELECT b_idx FROM nb_banner WHERE no=?');$q->execute([$rankIds['A']]);
        qa_expect($r[0]===200&&(int)$q->fetchColumn()===1001,'banner omitted edit rank preserves previous value');
        foreach(['-1','1.5','2147483648']as $bad){
            $badTitle=$uid.'_bad';$r=designHttp('banner',array_merge($base,['mode'=>'save','b_title'=>$badTitle,'b_idx'=>$bad]));
            $q=$db->prepare('SELECT no FROM nb_banner WHERE b_title=?');$q->execute([$badTitle]);$badId=(int)$q->fetchColumn();if($badId)$rows[]=['nb_banner',$badId];
            qa_expect($r[0]===400&&($r[1]['result']??'')==='fail'&&$badId===0,'banner invalid new rank '.$bad.' rejected before creation');
        }
        $q=$db->prepare("SELECT IFNULL(MAX(b_idx)+1,1) FROM nb_banner WHERE sitekey='BLUESQ' AND b_loc='site_main'");$q->execute();$expected=(int)$q->fetchColumn();
        $r=designHttp('banner',array_merge($base,['mode'=>'save','b_title'=>$uid.'_rankC','b_idx'=>'','b_location'=>'different-location']));
        $q=$db->prepare('SELECT no,b_idx FROM nb_banner WHERE b_title=?');$q->execute([$uid.'_rankC']);$row=$q->fetch(PDO::FETCH_ASSOC);if($row)$rows[]=['nb_banner',(int)$row['no']];
        qa_expect($r[0]===200&&$row&&(int)$row['b_idx']===$expected,'banner blank new rank follows full shared placement order');
        $r=designHttp('banner',array_replace($data,['b_view'=>'N']));[, $html]=designPublicHome();
        qa_expect(($r[1]['result']??'')==='success'&&strpos($html,$uid.'_rankA')===false&&strpos($html,$uid.'_rankB')!==false,'banner hidden update removes only target from public home');
    }
}catch(Throwable $e){qa_expect(false,$e->getMessage());}
finally {
    foreach($rows as [$table,$id]){$q=$db->prepare("SELECT * FROM $table WHERE no=?");$q->execute([$id]);$row=$q->fetch(PDO::FETCH_ASSOC);
        $db->prepare("DELETE FROM $table WHERE no=?")->execute([$id]);
        if($row)foreach($table==='nb_banner'?['b_img','b_img_mobile','b_poster_img']:['p_img']as $key){$file=($roots[$table==='nb_banner'?'banner':'popup']??'').'/'.basename($row[$key]??'');clearstatcache();if(is_file($file))unlink($file);}}
    if($admin){$db->prepare('DELETE FROM nb_admin_audit WHERE actor_no=?')->execute([$admin]);$db->prepare('DELETE FROM nb_admin WHERE no=? AND uid=?')->execute([$admin,$uid]);}
    if($sid){$file=(session_save_path()?:sys_get_temp_dir()).'/sess_'.$sid;clearstatcache();if(is_file($file))unlink($file);}
}
qa_done();

