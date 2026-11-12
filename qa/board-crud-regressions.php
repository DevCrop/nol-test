<?php
ob_start();
require __DIR__.'/bootstrap.php';
require_once dirname(__DIR__).'/inc/lib/db.php';
if (!is_file('/.dockerenv') || DB_USER !== 'qa_app' || APP_ENV !== 'development') exit(1);
$db=DB::getInstance();$uid='qa_board_'.bin2hex(random_bytes(5));$admin=0;$sid='';$boards=[];$posts=[];$files=[];
function boardHttp(array $data,array $files=[],string $path='/pages/board/ajax/board.process.php',string $formSuffix=''):array {
    $data['_csrf']=$GLOBALS['csrf'];$type='application/x-www-form-urlencoded';$body=http_build_query($data).$formSuffix;
    if($files){$b='qa'.bin2hex(random_bytes(8));$type='multipart/form-data; boundary='.$b;$body='';
        foreach($data as $k=>$v)foreach(is_array($v)?$v:[$v] as $item)$body.="--$b\r\nContent-Disposition: form-data; name=\"".$k.(is_array($v)?'[]':'')."\"\r\n\r\n$item\r\n";
        foreach($files as $field=>[$name,$mime,$bytes])$body.="--$b\r\nContent-Disposition: form-data; name=\"$field\"; filename=\"$name\"\r\nContent-Type: $mime\r\n\r\n$bytes\r\n";
        $body.="--$b--\r\n";
    }
    $route=blue_env('GATE_ROUTE_MODE','path');$prefix=$route==='php'?'/index.php':($route==='internal'?'':'/admin');
    $ctx=stream_context_create(['http'=>['method'=>'POST','header'=>'Host: '.blue_env('GATE_HOST')."\r\nCookie: ".session_name().'='.$GLOBALS['sid']."\r\nX-Requested-With: XMLHttpRequest\r\nContent-Type: $type\r\n",'content'=>$body,'ignore_errors'=>true,'follow_location'=>0,'timeout'=>15]]);
    $raw=(string)file_get_contents('http://127.0.0.1'.$prefix.$path,false,$ctx);$json=json_decode($raw,true);
    if(!is_array($json))echo 'Non-JSON board response: '.substr(strip_tags($raw),0,180)."\n";
    return $json??[];
}
function boardEditDom(int $id, string $routePath = ''): DOMDocument {
    $route=blue_env('GATE_ROUTE_MODE','path');$prefix=$route==='php'?'/index.php':($route==='internal'?'':'/admin');
    $ctx=stream_context_create(['http'=>['header'=>'Host: '.blue_env('GATE_HOST')."\r\nCookie: ".session_name().'='.$GLOBALS['sid']."\r\n",'ignore_errors'=>true,'follow_location'=>0,'timeout'=>15]]);
    $html=(string)file_get_contents('http://127.0.0.1'.$prefix.($routePath !== '' ? $routePath : '/pages/board/board.view.php?no='.$id),false,$ctx);
    $dom=new DOMDocument();$previous=libxml_use_internal_errors(true);$dom->loadHTML($html);libxml_clear_errors();libxml_use_internal_errors($previous);
    return $dom;
}
function boardNoticeForm(int $id): array {
    $dom=boardEditDom($id);
    $controls=[];foreach($dom->getElementsByTagName('input')as $input)if($input->getAttribute('name')==='is_notice')$controls[]=['type'=>$input->getAttribute('type'),'value'=>$input->getAttribute('value'),'checked'=>$input->hasAttribute('checked')];
    return $controls;
}
function boardNoticeEncode(array $controls,bool $checked): string {
    $parts=[];foreach($controls as $control)if($control['type']!=='checkbox'||$checked)$parts[]='is_notice='.rawurlencode($control['value']);
    return $parts?'&'.implode('&',$parts):'';
}
function boardUnchangedForm(DOMDocument $dom): string {
    $xpath=new DOMXPath($dom);$fields=[];
    foreach($xpath->query('//form[@id="frm"]//*[self::input or self::select or self::textarea]')as $field){
        $name=$field->getAttribute('name');if($name===''||$field->hasAttribute('disabled'))continue;
        if($field->tagName==='input'){$type=strtolower($field->getAttribute('type'));if(in_array($type,['file','submit','button','reset'],true)||in_array($type,['checkbox','radio'],true)&&!$field->hasAttribute('checked'))continue;$value=$field->getAttribute('value');}
        elseif($field->tagName==='textarea')$value=$field->textContent;
        else{$value='';$first=true;foreach($field->getElementsByTagName('option')as $option){if($first||$option->hasAttribute('selected'))$value=$option->getAttribute('value');$first=false;if($option->hasAttribute('selected'))break;}}
        $fields[]=rawurlencode($name).'='.rawurlencode($value);
    }
    return '&'.implode('&',$fields);
}
function boardRow(int $id):array{$s=$GLOBALS['db']->prepare('SELECT * FROM nb_board WHERE no=?');$s->execute([$id]);return $s->fetch(PDO::FETCH_ASSOC)?:[];}
function boardFile(string $name):bool{clearstatcache();return $name!==''&&is_file(dirname(__DIR__).'/uploads/board/'.$name);}
function boardSuccess(array $r):bool{return ($r['result']??'')==='success';}
try{
    $db->prepare("INSERT INTO nb_admin(sitekey,uid,upwd,uname,email,active_status,role_code,password_changed_at) VALUES('BLUESQ',?,?,?,?,'Y','super',NOW())")->execute([$uid,password_hash(random_bytes(20),PASSWORD_DEFAULT),'QA',$uid.'@example.com']);
    $admin=(int)$db->lastInsertId();\Security\AdminAccount::establish(\Security\AdminAccount::findByNo($admin));$sid=session_id();$csrf=\Security\Csrf::token();session_write_close();if(posix_geteuid()===0)chown((session_save_path()?:sys_get_temp_dir()).'/sess_'.$sid,'www-data');
    $source=$db->query('SELECT * FROM nb_board_manage LIMIT 1')->fetch(PDO::FETCH_ASSOC);if(!$source)throw new RuntimeException('Board fixture source is unavailable');unset($source['no']);
    foreach([1,2]as $n){$source['title']=$uid.$n;$source['sitekey']='BLUESQ';$source['top_banner_image']='';$source['extra_match_field1']='QA extra';$keys=array_keys($source);$s=$db->prepare('INSERT INTO nb_board_manage (`'.implode('`,`',$keys).'`) VALUES ('.implode(',',array_fill(0,count($keys),'?')).')');$s->execute(array_values($source));$boards[]=(int)$db->lastInsertId();}
    $db->prepare("INSERT INTO nb_board_category(sitekey,board_no,name,sort_no) VALUES('BLUESQ',?,?,1)")->execute([$boards[0],$uid]);$category=(int)$db->lastInsertId();
    $png=['qa.png','image/png',base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+aN2QAAAAASUVORK5CYII=')];
    $base=['board_no'=>$boards[0],'category_no'=>$category,'title'=>$uid,'write_name'=>'Saved author','contents'=>'QA','is_view'=>'N','is_notice'=>'Y','direct_url'=>'https://example.com/?q="QA"&other=<QA>','extra1'=>'QA "quoted" & <value>'];
    $r=boardHttp(['mode'=>'save']+$base,['thumb_image'=>$png,'addFile1'=>['qa.txt','text/plain','synthetic attachment']]);
    $s=$db->prepare('SELECT no FROM nb_board WHERE title=? ORDER BY no DESC LIMIT 1');$s->execute([$uid]);$id=(int)$s->fetchColumn();if($id)$posts[]=$id;
    qa_expect(boardSuccess($r)&&$id>0,'board HTTP create with uploads');if(!$id)throw new RuntimeException('Cannot continue without fixture');
    $attack = 'QA <img src=x onerror=alert(1)> & "quoted"';
    $db->prepare('UPDATE nb_board SET title=?,write_name=? WHERE no=?')->execute([htmlspecialchars($attack,ENT_QUOTES,'UTF-8'),'홍길동',$id]);
    $listDom=boardEditDom(0,'/pages/board/board.list.php?board_no='.$boards[0]);$listXpath=new DOMXPath($listDom);
    $titleNode=$listXpath->query('//a[contains(@href,"board.view.php?no='.$id.'&")]')->item(0);
    qa_expect($titleNode && $titleNode->textContent===$attack && $titleNode->getElementsByTagName('img')->length===0,'board list renders decoded title as literal text without executable markup');
    qa_expect(strpos($listDom->textContent,'홍*동')!==false && strpos($listDom->textContent,'홍길동')===false && $listXpath->query('//td[@data-pii-lock]')->length>0,'board list masks author with copy-lock scope');
    $privacy=$db->prepare("SELECT subject_label FROM nb_admin_privacy_access WHERE actor_no=? AND entity='board' AND action='view' AND target_no=? ORDER BY no DESC LIMIT 1");$privacy->execute([$admin,$id]);
    qa_expect($privacy->fetchColumn()==='홍*동','board author view links actor target and masked subject');
    $db->prepare('UPDATE nb_board SET title=?,write_name=? WHERE no=?')->execute([$uid,$base['write_name'],$id]);
    $old=boardRow($id);foreach(['thumb_image','file_attach_1']as $k)$files[]=$old[$k];qa_expect(boardFile($old['thumb_image'])&&boardFile($old['file_attach_1']),'created files exist');
    $formDom=boardEditDom($id);$xpath=new DOMXPath($formDom);$linkInput=$xpath->query('//input[@name="direct_url"]')->item(0);$extraInput=$xpath->query('//input[@name="extra1"]')->item(0);
    qa_expect($linkInput&&$linkInput->getAttribute('value')===$base['direct_url']&&!$linkInput->hasAttribute('onfocus')&&!$linkInput->hasAttribute('autofocus'),'direct URL restores decoded value without attribute escape');
    qa_expect($extraInput&&$extraInput->getAttribute('value')===$base['extra1'],'extra field restores decoded value without entity accumulation');
    $beforeUnchanged=boardRow($id);$sameForm=boardHttp([],[], '/pages/board/ajax/board.process.php',boardUnchangedForm($formDom));$afterUnchanged=boardRow($id);
    qa_expect(boardSuccess($sameForm)&&$afterUnchanged['direct_url']===$beforeUnchanged['direct_url']&&$afterUnchanged['extra1']===$beforeUnchanged['extra1']&&$afterUnchanged['title']===$beforeUnchanged['title']&&$afterUnchanged['write_name']===$beforeUnchanged['write_name'],'actual unchanged edit form preserves URL extra title and author');
    $noticeControls=boardNoticeForm($id);
    qa_expect(count($noticeControls)===2&&$noticeControls[0]['type']==='hidden'&&$noticeControls[0]['value']==='N'&&$noticeControls[1]['type']==='checkbox'&&$noticeControls[1]['checked'],'saved notice renders checked with unchecked fallback');
    $noticeBase=$base;unset($noticeBase['is_notice']);
    $off=boardHttp(['mode'=>'edit','no'=>$id]+$noticeBase,[], '/pages/board/ajax/board.process.php',boardNoticeEncode($noticeControls,false));
    $offControls=boardNoticeForm($id);
    qa_expect(boardSuccess($off)&&boardRow($id)['is_notice']==='N'&&count($offControls)===2&&!$offControls[1]['checked'],'unchecked notice form persists N and reopens unchecked');
    $on=boardHttp(['mode'=>'edit','no'=>$id]+$noticeBase,[], '/pages/board/ajax/board.process.php',boardNoticeEncode($offControls,true));
    $onControls=boardNoticeForm($id);
    qa_expect(boardSuccess($on)&&boardRow($id)['is_notice']==='Y'&&count($onControls)===2&&$onControls[1]['checked'],'checked notice form duplicate-name payload persists Y and reopens checked');
    $edit=['mode'=>'edit','no'=>$id];$r=boardHttp($edit+['write_name'=>'Changed author','category_no'=>'']+$base);$row=boardRow($id);
    qa_expect(boardSuccess($r)&&$row['write_name']==='Changed author'&&(int)$row['category_no']===0,'author persists and empty category clears');
    qa_expect(boardSuccess(boardHttp($edit+['write_name'=>'Changed author','category_no'=>'']+$base)),'same-value board update succeeds');
    $r=boardHttp($edit+['board_no'=>$boards[1]]+$base);qa_expect(!boardSuccess($r)&&(int)boardRow($id)['board_no']===$boards[0],'foreign-board category rejected without mutation');
    $r=boardHttp($edit+$base,['thumb_image'=>['bad.svg','image/svg+xml','<svg><script>alert(1)</script></svg>']]);qa_expect(!boardSuccess($r)&&boardRow($id)['thumb_image']===$old['thumb_image']&&boardFile($old['thumb_image']),'invalid upload preserves existing file');
    $before=glob(dirname(__DIR__).'/uploads/board/*');$r=boardHttp(['mode'=>'edit','no'=>2147483647]+$base,['thumb_image'=>$png]);qa_expect(!boardSuccess($r)&&glob(dirname(__DIR__).'/uploads/board/*')===$before,'missing target moves no upload');
    $before=glob(dirname(__DIR__).'/uploads/board/*');$r=boardHttp($edit+['regdate'=>'not-a-date']+$base,['thumb_image'=>$png]);qa_expect(!boardSuccess($r)&&boardRow($id)['thumb_image']===$old['thumb_image']&&boardFile($old['thumb_image'])&&glob(dirname(__DIR__).'/uploads/board/*')===$before,'failed DB update preserves old file and removes new upload');
    $before=glob(dirname(__DIR__).'/uploads/board/*');$r=boardHttp(['mode'=>'save','regdate'=>'not-a-date','title'=>$uid.'_failed']+$base,['thumb_image'=>$png]);qa_expect(!boardSuccess($r)&&glob(dirname(__DIR__).'/uploads/board/*')===$before,'failed DB create removes new upload');
    $r=boardHttp($edit+$base,['thumb_image'=>$png]);$row=boardRow($id);$files[]=$row['thumb_image'];qa_expect(boardSuccess($r)&&boardFile($row['thumb_image'])&&!boardFile($old['thumb_image']),'successful replacement retires unreferenced old file');
    $r=boardHttp(['mode'=>'board.copy','no'=>$id]);if(!boardSuccess($r))echo 'Copy diagnostic '.json_encode($r,JSON_UNESCAPED_UNICODE)."\n";$s=$db->prepare('SELECT no FROM nb_board WHERE title=? ORDER BY no DESC LIMIT 1');$s->execute([$uid]);$copy=(int)$s->fetchColumn();if($copy&&$copy!==$id)$posts[]=$copy;qa_expect(boardSuccess($r)&&$copy!==$id&&boardRow($copy)['file_attach_1']===$old['file_attach_1'],'copy retains shared attachment safely');
    $r=boardHttp($edit+['attach_file_del'=>[1]]+$base);qa_expect(boardSuccess($r)&&empty(boardRow($id)['file_attach_1'])&&boardFile($old['file_attach_1']),'removing original attachment preserves copied reference');
    $r=boardHttp(['mode'=>'delete','no'=>$copy]);qa_expect(boardSuccess($r)&&!boardFile($old['file_attach_1'])&&boardFile($row['thumb_image']),'copy delete retires only final attachment reference');
    $batch=[];
    for($j=0;$j<2;$j++){$made=boardHttp(['mode'=>'board.copy','no'=>$id]);$copied=(int)$db->query('SELECT MAX(no) FROM nb_board')->fetchColumn();if(boardSuccess($made)&&$copied!==$id){$batch[]=$copied;$posts[]=$copied;}}
    $batchResult=boardHttp(['mode'=>'delete.array','board_file_check_no'=>$batch]);
    $audit=$db->prepare("SELECT detail_json FROM nb_admin_audit WHERE actor_no=? AND action='delete' AND entity='nb_board' ORDER BY no DESC LIMIT 1");$audit->execute([$admin]);$batchDetail=json_decode((string)$audit->fetchColumn(),true);$loggedIds=$batchDetail['target_ids']??[];sort($loggedIds);sort($batch);
    qa_expect(count($batch)===2 && boardSuccess($batchResult) && $loggedIds===$batch && ($batchDetail['affected_rows']??0)===2,'batch board deletion audit records exactly the two deleted target IDs');
    $r=boardHttp(['mode'=>'delete','no'=>$id]);qa_expect(boardSuccess($r)&&!boardRow($id)&&!boardFile($row['thumb_image']),'final delete retires final image reference');
    $manage=['mode'=>'edit','no'=>$boards[0],'title'=>$uid.'1','skin'=>'bbs','content'=>'QA','view_yn'=>'N','secret_yn'=>'N','list_size'=>10,'fileattach_yn'=>'Y','fileattach_cnt'=>5,'comment_yn'=>'N','category_yn'=>'Y','depth1'=>0,'depth2'=>0,'depth3'=>0,'lnb_path'=>''];
    $path='/pages/board/ajax/board.manage.process.php';$r=boardHttp($manage,['top_banner_image'=>$png],$path);
    $banner=$db->prepare('SELECT top_banner_image FROM nb_board_manage WHERE no=?');$banner->execute([$boards[0]]);$bannerFile=(string)$banner->fetchColumn();$files[]=$bannerFile;
    qa_expect(boardSuccess($r)&&boardFile($bannerFile),'board management banner upload');
    $r=boardHttp($manage,['top_banner_image'=>['bad.svg','image/svg+xml','<svg/>']],$path);$banner->execute([$boards[0]]);
    qa_expect(!boardSuccess($r)&&$banner->fetchColumn()===$bannerFile&&boardFile($bannerFile),'board management rejected upload preserves banner');
    $r=boardHttp($manage,['top_banner_image'=>$png],$path);$banner->execute([$boards[0]]);$newBanner=(string)$banner->fetchColumn();$files[]=$newBanner;
    qa_expect(boardSuccess($r)&&boardFile($newBanner)&&!boardFile($bannerFile),'board management replacement retires old banner');
    $r=boardHttp(['mode'=>'delete','no'=>$boards[0]],[],$path);qa_expect(boardSuccess($r)&&!boardFile($newBanner),'board management delete retires banner after SQL');
}catch(Throwable $e){qa_expect(false,$e->getMessage());}
finally{
    foreach($posts as $id)$db->prepare('DELETE FROM nb_board WHERE no=? AND sitekey=?')->execute([$id,'BLUESQ']);
    foreach($boards as $id){$db->prepare('DELETE FROM nb_board_category WHERE board_no=?')->execute([$id]);$db->prepare('DELETE FROM nb_board_manage WHERE no=?')->execute([$id]);}
    foreach(array_unique($files)as $file){$path=dirname(__DIR__).'/uploads/board/'.basename($file);clearstatcache(true,$path);if($file!==''&&is_file($path))unlink($path);}
    if($admin){$db->prepare('DELETE FROM nb_admin_audit WHERE actor_no=?')->execute([$admin]);$db->prepare('DELETE FROM nb_admin_privacy_access WHERE actor_no=?')->execute([$admin]);$db->prepare('DELETE FROM nb_admin WHERE no=? AND uid=?')->execute([$admin,$uid]);}
    if($sid){$path=(session_save_path()?:sys_get_temp_dir()).'/sess_'.$sid;if(is_file($path))unlink($path);}
}
qa_done();
