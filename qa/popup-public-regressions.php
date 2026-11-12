<?php
ob_start();
require __DIR__.'/bootstrap.php';
require_once dirname(__DIR__).'/inc/lib/db.php';
if (!is_file('/.dockerenv') || APP_ENV !== 'development' || DB_USER !== 'qa_app') exit("SKIP isolated development Docker required\n");
$db=DB::getInstance();$tag='qa_popup_public_'.bin2hex(random_bytes(5));$ids=[];$uid='qa_pp_'.bin2hex(random_bytes(4));$admin=0;$sid='';$files=[];
function popupPublicHttp(string $path, ?array $data=null, string $cookie='', bool $upload=false, string $agent='QA desktop'): array {
    $gate=$data!==null || strpos($path,'/index.php/')===0;
    $headers='Host: '.($gate?GATE_HOST:PUBLIC_HOST)."\r\nUser-Agent: $agent\r\n";
    if($cookie!=='')$headers.="Cookie: $cookie\r\n";
    $body='';
    if($data!==null){
        $headers.="X-Requested-With: XMLHttpRequest\r\n";$data['_csrf']=$GLOBALS['csrf'];
        if($upload){$boundary='qa'.bin2hex(random_bytes(8));$headers.="Content-Type: multipart/form-data; boundary=$boundary\r\n";
            foreach($data as $key=>$value)$body.="--$boundary\r\nContent-Disposition: form-data; name=\"$key\"\r\n\r\n$value\r\n";
            $body.="--$boundary\r\nContent-Disposition: form-data; name=\"p_img\"; filename=\"qa.png\"\r\nContent-Type: image/png\r\n\r\n".base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+aN2QAAAAASUVORK5CYII=')."\r\n--$boundary--\r\n";
        }else{$headers.="Content-Type: application/x-www-form-urlencoded\r\n";$body=http_build_query($data);}
    }
    $ctx=stream_context_create(['http'=>['method'=>$data===null?'GET':'POST','header'=>$headers,'content'=>$body,'ignore_errors'=>true,'follow_location'=>0,'timeout'=>15]]);
    $html=(string)file_get_contents('http://127.0.0.1'.$path,false,$ctx);preg_match('/\s(\d{3})\s/',$http_response_header[0]??'',$m);
    return [(int)($m[1]??0),$html,json_decode($html,true)];
}
function popupFixture(array $changes): int {
    $row=array_merge(['sitekey'=>'BLUESQ','p_title'=>$GLOBALS['tag'].'fixture','p_view'=>'Y','p_none_limit'=>'Y','p_sdate'=>null,'p_edate'=>null,'p_loc'=>'M','p_target'=>'_self','p_link'=>'/pages/cs/parking.php','p_img'=>'qa.png','p_idx'=>900000,'p_rdate'=>date('Y-m-d H:i:s')],$changes);
    $keys=array_keys($row);$GLOBALS['db']->prepare('INSERT INTO nb_popup (`'.implode('`,`',$keys).'`)VALUES('.implode(',',array_fill(0,count($keys),'?')).')')->execute(array_values($row));
    $id=(int)$GLOBALS['db']->lastInsertId();$GLOBALS['ids'][]=$id;return $id;
}
try {
    $today=date('Y-m-d');
    $cases=['unlimited'=>[], 'today'=>['p_none_limit'=>'N','p_sdate'=>$today,'p_edate'=>$today], 'future'=>['p_none_limit'=>'N','p_sdate'=>date('Y-m-d',strtotime('+1 day')),'p_edate'=>date('Y-m-d',strtotime('+2 day'))], 'expired'=>['p_none_limit'=>'N','p_sdate'=>'2000-01-01','p_edate'=>'2000-01-02'], 'hidden'=>['p_view'=>'N'], 'foreign'=>['sitekey'=>'QAOTH'], 'self'=>['p_target'=>'_self'], 'blank'=>['p_target'=>'_blank'], 'none'=>['p_target'=>'_self','p_link'=>''], 'unsafe'=>['p_target'=>'_self','p_link'=>'javascript:alert(1234567)'], 'legacy_pc'=>['p_loc'=>'P'], 'legacy_mobile'=>['p_loc'=>'M']];
    foreach($cases as $kind=>$changes)popupFixture(array_merge($changes,['p_title'=>$tag.$kind]));
    [$status,$html]=popupPublicHttp('/');
    qa_expect($status===200,'popup actual public home returns 200');
    foreach(['unlimited','today','self','blank','none','unsafe','legacy_pc','legacy_mobile']as $kind)qa_expect(strpos($html,$tag.$kind)!==false,'popup visible '.$kind);
    foreach(['future','expired','hidden','foreign']as $kind)qa_expect(strpos($html,$tag.$kind)===false,'popup excluded '.$kind);
    $doc=new DOMDocument();@$doc->loadHTML($html);$xp=new DOMXPath($doc);
    foreach(['self'=>'_self','blank'=>'_blank']as $kind=>$target){$img=$xp->query('//img[@alt="'.$tag.$kind.'"]')->item(0);$a=$img?$img->parentNode:null;qa_expect($a&&$a->nodeName==='a'&&$a->getAttribute('target')===$target,'popup public target '.$kind);if($kind==='blank')qa_expect(strpos($a->getAttribute('rel'),'noopener')!==false,'popup blank isolates opener');}
    foreach(['none','unsafe']as $kind){$img=$xp->query('//img[@alt="'.$tag.$kind.'"]')->item(0);qa_expect($img&&$img->parentNode->nodeName!=='a','popup non-navigation '.$kind);}
    qa_expect(strpos($html,'javascript:alert(1234567)')===false,'legacy unsafe popup URL not emitted');
    [, $closed]=popupPublicHttp('/',null,'popupClosed=true');qa_expect(strpos($closed,$tag.'unlimited')===false,'today-close cookie omits popup');
    [, $mobile]=popupPublicHttp('/',null,'',false,'Mozilla/5.0 (iPhone; Mobile)');qa_expect(strpos($mobile,$tag.'legacy_pc')!==false&&strpos($mobile,$tag.'legacy_mobile')!==false,'legacy locations remain shared responsive popup (no active device selector)');

    $db->prepare("INSERT INTO nb_admin(sitekey,uid,upwd,uname,email,active_status,role_code,password_changed_at)VALUES('BLUESQ',?,?,?,?,'Y','super',NOW())")->execute([$uid,password_hash(bin2hex(random_bytes(24)),PASSWORD_DEFAULT),'QA',$tag.'@example.com']);
    $admin=(int)$db->lastInsertId();\Security\AdminAccount::establish(\Security\AdminAccount::findByNo($admin));$sid=session_id();$csrf=\Security\Csrf::token();session_write_close();if(posix_geteuid()===0)chown((session_save_path()?:sys_get_temp_dir()).'/sess_'.$sid,'www-data');
    $cookie=session_name().'='.$sid;$endpoint='/index.php/pages/design/ajax/popup.process.php';
    $input=['mode'=>'save','p_title'=>$tag.'saved','p_view'=>'Y','p_none_limit'=>'Y','p_target'=>''];
    [$status,,$json]=popupPublicHttp($endpoint,$input,$cookie,true);
    $q=$db->prepare('SELECT * FROM nb_popup WHERE p_title=?');$q->execute([$input['p_title']]);$row=$q->fetch(PDO::FETCH_ASSOC);
    qa_expect($status===200&&($json['result']??'')==='success'&&$row,'actual form without p_loc creates popup');
    if($row){
        $id=(int)$row['no'];$ids[]=$id;$files[]=dirname(__DIR__).'/uploads/popup/'.$row['p_img'];
        qa_expect((int)$row['p_idx']>=900001,'omitted new rank assigned automatically');
        qa_expect($row['p_loc']==='M'&&$row['p_target']==='_self'&&$row['p_link']==='','omitted location and legacy empty no-link normalize safely');
        [, $form]=popupPublicHttp('/index.php/pages/design/popup.view.php?no='.$id,null,$cookie);$formDoc=new DOMDocument();@$formDoc->loadHTML($form);$formXp=new DOMXPath($formDoc);qa_expect($formXp->query('//input[@name="p_target" and @value="_none" and @checked]')->length===1&&$formXp->query('//div[@id="no_link_target_box" and contains(@style,"display:none")]')->length===1,'saved no-link restores checked radio and hidden URL field');
        [$status,$bytes]=popupPublicHttp('/uploads/popup/'.rawurlencode($row['p_img']));qa_expect($status===200&&$bytes===file_get_contents(end($files)),'saved popup upload served exact bytes publicly');
        [$status,$html]=popupPublicHttp('/');qa_expect($status===200&&strpos($html,$tag.'saved')!==false,'admin save reflected in public home');
        $ranked=array_merge($input,['mode'=>'save','p_title'=>$tag.'ranked','p_idx'=>'1']);[$status,,$json]=popupPublicHttp($endpoint,$ranked,$cookie);$q->execute([$ranked['p_title']]);$rankRow=$q->fetch(PDO::FETCH_ASSOC);if($rankRow)$ids[]=(int)$rankRow['no'];qa_expect(($json['result']??'')==='success'&&$rankRow&&(int)$rankRow['p_idx']===1,'new popup respects supplied numeric rank');[, $rankHtml]=popupPublicHttp('/');qa_expect(strpos($rankHtml,$tag.'ranked')!==false&&strpos($rankHtml,$tag.'ranked')<strpos($rankHtml,$tag.'unlimited'),'saved rank controls actual public order');
        foreach(['-1','1.5','2147483648',['1']]as $badRank){[$badStatus,,$badJson]=popupPublicHttp($endpoint,array_merge($ranked,['p_title'=>$tag.'bad','p_idx'=>$badRank]),$cookie);$q->execute([$tag.'bad']);qa_expect($badStatus===400&&($badJson['result']??'')==='fail'&&!$q->fetch(),'invalid new popup rank rejected without creation');}
        $edit=array_merge($input,['mode'=>'edit','no'=>$id,'p_target'=>'_self','p_link'=>'/pages/cs/parking.php','p_none_limit'=>'N','p_sdate'=>$today,'p_edate'=>$today]);
        [$status,,$json]=popupPublicHttp($endpoint,$edit,$cookie);qa_expect($status===200&&($json['result']??'')==='success','actual edit without p_loc saves inclusive current date');
        [, $html]=popupPublicHttp('/');$doc=new DOMDocument();@$doc->loadHTML($html);$xp=new DOMXPath($doc);$img=$xp->query('//img[@alt="'.$tag.'saved"]')->item(0);qa_expect($img&&$img->parentNode->getAttribute('target')==='_self','admin target update reflected in public link');
        [$status,,$json]=popupPublicHttp($endpoint,array_merge($edit,['p_sdate'=>'2000-01-01','p_edate'=>'2000-01-02']),$cookie);[, $html]=popupPublicHttp('/');qa_expect(($json['result']??'')==='success'&&strpos($html,$tag.'saved')===false,'admin expired-period update removed from public home');
        [$status,,$json]=popupPublicHttp($endpoint,array_merge($edit,['p_view'=>'N']),$cookie);[, $html]=popupPublicHttp('/');qa_expect(($json['result']??'')==='success'&&strpos($html,$tag.'saved')===false,'admin hidden update removed from public home');
        [$status,,$json]=popupPublicHttp($endpoint,['mode'=>'delete','no'=>$id],$cookie);clearstatcache();qa_expect($status===200&&($json['result']??'')==='success'&&!is_file(end($files)),'admin delete retires popup upload');
        [, $html]=popupPublicHttp('/');qa_expect(strpos($html,$tag.'saved')===false,'admin deleted popup absent publicly');
    }
    [, $form]=popupPublicHttp('/index.php/pages/design/popup.add.php',null,$cookie);qa_expect(strpos($form,'팝업 등록')!==false&&strpos($form,'onClick="doRegSave();">등록</a>')!==false,'popup create form headings and register label');
} catch(Throwable $e){qa_expect(false,get_class($e).': '.$e->getMessage());}
finally {
    foreach($ids as $id)$db->prepare('DELETE FROM nb_popup WHERE no=? AND p_title LIKE ?')->execute([$id,$tag.'%']);
    foreach($files as $path){clearstatcache();if(is_file($path))unlink($path);}
    if($admin){$db->prepare('DELETE FROM nb_admin_audit WHERE actor_no=?')->execute([$admin]);$db->prepare('DELETE FROM nb_admin WHERE no=? AND uid=?')->execute([$admin,$uid]);}
    if($sid){$path=(session_save_path()?:sys_get_temp_dir()).'/sess_'.$sid;clearstatcache();if(is_file($path))unlink($path);}
}
qa_done();
