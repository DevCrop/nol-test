<?php
/** Isolated HTTP/DB acceptance: synthetic subjects only, no production writes. */
ob_start();
require __DIR__.'/bootstrap.php';
require_once dirname(__DIR__).'/inc/lib/db.php';
if (!is_file('/.dockerenv') || PHP_SAPI!=='cli' || APP_ENV!=='development' || DB_USER!=='qa_app') exit(1);
$db=DB::getInstance();
$rootDb=new PDO('mysql:host='.DB_HOST.';port='.DB_PORT.';dbname='.DB_NAME.';charset=utf8mb4','root',(string)getenv('DB_ROOT_PASSWORD'),[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
$tag='qa_bpriv_'.bin2hex(random_bytes(4));$admin=0;$sid='';$boards=[];$ownedPosts=[];$trigger='qa_privacy_'.bin2hex(random_bytes(5));$baselineProbe=in_array('--baseline-probe',$argv,true);

function bpHttp(string $path,array $data=[],array $files=[],bool $get=false):array {
    $prefix=blue_env('GATE_ROUTE_MODE','path')==='php'?'/index.php':(blue_env('GATE_ROUTE_MODE','path')==='internal'?'':'/admin');
    $headers='Host: '.blue_env('GATE_HOST')."\r\nCookie: ".session_name().'='.$GLOBALS['sid']."\r\n";
    $options=['method'=>$get?'GET':'POST','ignore_errors'=>true,'follow_location'=>0,'timeout'=>15];
    if(!$get){
        $data['_csrf']=$GLOBALS['csrf'];$content=http_build_query($data);$type='application/x-www-form-urlencoded';
        if($files){
            $boundary='qa'.bin2hex(random_bytes(8));$type='multipart/form-data; boundary='.$boundary;$content='';
            foreach($data as $key=>$value)foreach(is_array($value)?$value:[$value]as $item)$content.="--$boundary\r\nContent-Disposition: form-data; name=\"".$key.(is_array($value)?'[]':'')."\"\r\n\r\n$item\r\n";
            foreach($files as $field=>[$name,$mime,$bytes])$content.="--$boundary\r\nContent-Disposition: form-data; name=\"$field\"; filename=\"$name\"\r\nContent-Type: $mime\r\n\r\n$bytes\r\n";
            $content.="--$boundary--\r\n";
        }
        $headers.="X-Requested-With: XMLHttpRequest\r\nContent-Type: $type\r\n";$options['content']=$content;
    }
    $options['header']=$headers;
    $body=(string)file_get_contents('http://127.0.0.1'.$prefix.$path,false,stream_context_create(['http'=>$options]));
    preg_match('/\s(\d{3})\s/',$http_response_header[0]??'',$status);
    return [(int)($status[1]??0),json_decode($body,true),$body];
}
function bpPost(array $data,array $files=[]):array { return bpHttp('/pages/board/ajax/board.process.php',$data,$files); }
function bpComment(array $data):array { return bpHttp('/pages/board/ajax/board.comment.process.php',$data); }
function bpOk(array $response):bool { return $response[0]===200 && ($response[1]['result']??'')==='success'; }
function bpRow(int $id):array { $q=$GLOBALS['db']->prepare('SELECT * FROM nb_board WHERE no=?');$q->execute([$id]);return $q->fetch(PDO::FETCH_ASSOC)?:[]; }
function bpLogs(string $action,int $target):array {
    $q=$GLOBALS['db']->prepare("SELECT action,target_no,subject_label,task FROM nb_admin_privacy_access WHERE actor_no=? AND entity='board' AND action=? AND target_no=? ORDER BY no");$q->execute([$GLOBALS['admin'],$action,$target]);return $q->fetchAll(PDO::FETCH_ASSOC);
}
function bpNewId(string $title):int {
    $q=$GLOBALS['db']->prepare('SELECT no FROM nb_board WHERE title=? ORDER BY no DESC LIMIT 1');$q->execute([$title]);$id=(int)$q->fetchColumn();if($id)$GLOBALS['ownedPosts'][$id]=$id;return $id;
}
function bpFiles():array { $paths=glob(dirname(__DIR__).'/uploads/board/*')?:[];sort($paths);return $paths; }
function bpState():array {
    $q=$GLOBALS['rootDb']->prepare('SELECT * FROM nb_board WHERE title LIKE ? ORDER BY no');$q->execute([$GLOBALS['tag'].'%']);$posts=$q->fetchAll(PDO::FETCH_ASSOC);$ids=array_map(static fn($row)=>(int)$row['no'],$posts);
    $comments=[];if($ids){$q=$GLOBALS['rootDb']->prepare('SELECT * FROM nb_board_comment WHERE parent_no IN('.implode(',',array_fill(0,count($ids),'?')).') ORDER BY no');$q->execute($ids);$comments=$q->fetchAll(PDO::FETCH_ASSOC);}
    $counts=[];foreach(['nb_admin_audit','nb_admin_privacy_access']as $table){$q=$GLOBALS['rootDb']->prepare('SELECT COUNT(*) FROM '.$table.' WHERE actor_no=?');$q->execute([$GLOBALS['admin']]);$counts[$table]=(int)$q->fetchColumn();}
    return [$posts,$comments,$counts,bpFiles()];
}
function bpFault(bool $enabled):void {
    $db=$GLOBALS['rootDb'];$name=$GLOBALS['trigger'];$actor=(int)$GLOBALS['admin'];
    $db->exec('DROP TRIGGER IF EXISTS `'.$name.'`');
    if($enabled)$db->exec("CREATE TRIGGER `$name` BEFORE INSERT ON nb_admin_privacy_access FOR EACH ROW BEGIN IF NEW.actor_no=$actor AND NEW.actor_uid=".$db->quote($GLOBALS['tag'])." AND NEW.entity='board' THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='QA privacy record unavailable'; END IF; END");
}
function bpSeedComment(int $parent,string $writer):int {
    $q=$GLOBALS['db']->prepare("INSERT INTO nb_board_comment(sitekey,parent_no,user_no,write_name,regdate,contents,isAdmin) VALUES('BLUESQ',?,-1,?,NOW(),?,'Y')");$q->execute([$parent,$writer,$GLOBALS['tag'].' comment']);$id=(int)$GLOBALS['db']->lastInsertId();
    $GLOBALS['db']->prepare('UPDATE nb_board SET comment_cnt=comment_cnt+1 WHERE no=?')->execute([$parent]);return $id;
}
try {
    $db->prepare("INSERT INTO nb_admin(sitekey,uid,upwd,uname,email,active_status,role_code,password_changed_at) VALUES('BLUESQ',?,?,?,?,'Y','super',NOW())")->execute([$tag,password_hash(bin2hex(random_bytes(24)),PASSWORD_DEFAULT),'검증관리자',$tag.'@example.com']);
    $admin=(int)$db->lastInsertId();\Security\AdminAccount::establish(\Security\AdminAccount::findByNo($admin));$sid=session_id();$csrf=\Security\Csrf::token();session_write_close();if(posix_geteuid()===0)chown((session_save_path()?:sys_get_temp_dir()).'/sess_'.$sid,'www-data');
    $source=$db->query('SELECT * FROM nb_board_manage LIMIT 1')->fetch(PDO::FETCH_ASSOC);if(!$source)throw new RuntimeException('Missing board fixture template');unset($source['no']);$source['title']=$tag.'_board';$source['sitekey']='BLUESQ';$source['top_banner_image']='';
    $keys=array_keys($source);$db->prepare('INSERT INTO nb_board_manage (`'.implode('`,`',$keys).'`) VALUES('.implode(',',array_fill(0,count($keys),'?')).')')->execute(array_values($source));$boards[]=(int)$db->lastInsertId();
    $base=['board_no'=>$boards[0],'title'=>$tag.'_primary','write_name'=>'홍길동','contents'=>'Synthetic QA content','is_view'=>'N','is_notice'=>'N'];
    $png=['qa.png','image/png',base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+aN2QAAAAASUVORK5CYII=')];
    $made=bpPost(['mode'=>'save']+$base,['thumb_image'=>$png]);$id=bpNewId($base['title']);
    qa_expect(bpOk($made)&&$id>0,'board creation succeeds with mandatory privacy record');if(!$id)throw new RuntimeException('Cannot continue without own fixture');
    $logs=bpLogs('create',$id);qa_expect(count($logs)===1&&$logs[0]['subject_label']==='홍*동','creation records actual board id and masked author');
    $view=bpHttp('/pages/board/board.view.php?no='.$id,[],[],true);$logs=bpLogs('view',$id);
    qa_expect($view[0]===200&&strpos($view[2],'홍길동')!==false&&count($logs)===1&&$logs[0]['subject_label']==='홍*동','detail author disclosure has a linked privacy record');
    if($baselineProbe)throw new RuntimeException('QA_BASELINE_PROBE_COMPLETE');
    $before=bpState();bpFault(true);$blocked=bpHttp('/pages/board/board.view.php?no='.$id,[],[],true);
    qa_expect($blocked[0]===503&&strpos($blocked[2],'홍길동')===false&&bpState()===$before,'detail privacy fault denies disclosure before author output');bpFault(false);

    $edit=['mode'=>'edit','no'=>$id,'write_name'=>'김철수']+$base;$r=bpPost($edit);$logs=bpLogs('update',$id);$subjects=array_column($logs,'subject_label');sort($subjects);
    qa_expect(bpOk($r)&&bpRow($id)['write_name']==='김철수'&&$subjects===['김*수','홍*동'],'author change records both previous and replacement subjects');
    $before=count($logs);$r=bpPost($edit);qa_expect(bpOk($r)&&count(bpLogs('update',$id))===$before+1,'same-value edit still records processing of selected author');
    $r=bpPost(['mode'=>'board.copy','no'=>$id]);$copy=bpNewId($base['title']);$logs=bpLogs('create',$copy);
    qa_expect(bpOk($r)&&$copy!==$id&&count($logs)===1&&$logs[0]['subject_label']==='김*수','copy records new target id and masked author');
    $copy2=0;$r=bpPost(['mode'=>'board.copy','no'=>$id]);$copy2=bpNewId($base['title']);qa_expect(bpOk($r)&&$copy2!==$copy,'second isolated copy is available');

    $comment=bpSeedComment($id,'박영희');$view=bpHttp('/pages/board/board.comment.view.php?no='.$id,[],[],true);$logs=bpLogs('view',$id);
    qa_expect($view[0]===200&&strpos($view[2],'박*희')!==false&&strpos($view[2],'박영희')===false&&in_array('박*희',array_column($logs,'subject_label'),true),'comment view records and masks comment author');
    $before=bpState();bpFault(true);$view=bpHttp('/pages/board/board.comment.view.php?no='.$id,[],[],true);
    qa_expect($view[0]===503&&strpos($view[2],'김철수')===false&&strpos($view[2],'박영희')===false&&bpState()===$before,'comment view privacy fault denies personal output');bpFault(false);

    $clone=bpRow($id);unset($clone['no']);$clone['sitekey']='QAOTH';$clone['title']=$tag.'_foreign';foreach(['thumb_image','file_attach_1','file_attach_2','file_attach_3','file_attach_4','file_attach_5']as $column)$clone[$column]='';$clone['comment_cnt']=0;
    $keys=array_keys($clone);$db->prepare('INSERT INTO nb_board (`'.implode('`,`',$keys).'`) VALUES('.implode(',',array_fill(0,count($keys),'?')).')')->execute(array_values($clone));$foreign=(int)$db->lastInsertId();$ownedPosts[$foreign]=$foreign;
    foreach([$foreign,2147483647]as $missing){
        $before=bpState();$view=bpHttp('/pages/board/board.view.php?no='.$missing,[],[],true);$r=bpPost(['mode'=>'edit','no'=>$missing]+$base);
        qa_expect($view[0]===404&&strpos($view[2],'김철수')===false&&!bpOk($r)&&bpState()===$before,'foreign or missing board exposes no subject and records no successful processing');
    }

    bpFault(true);
    $faults=[
        'create'=>fn()=>bpPost(['mode'=>'save','title'=>$tag.'_faultcreate']+$base,['thumb_image'=>$png]),
        'edit'=>fn()=>bpPost(['mode'=>'edit','no'=>$id,'write_name'=>'이변경']+$base),
        'upload replacement'=>fn()=>bpPost(['mode'=>'edit','no'=>$id,'write_name'=>'김철수']+$base,['thumb_image'=>$png]),
        'single deletion including comments'=>fn()=>bpPost(['mode'=>'delete','no'=>$id]),
        'batch deletion including comments'=>fn()=>bpPost(['mode'=>'delete.array','board_file_check_no'=>[$id,$copy]]),
        'copy'=>fn()=>bpPost(['mode'=>'board.copy','no'=>$id]),
    ];
    foreach($faults as $label=>$operation){$before=bpState();$r=$operation();qa_expect(!bpOk($r)&&bpState()===$before,'privacy fault rolls back '.$label.' including audit, comments and files');}
    $before=bpState();$r=bpComment(['mode'=>'save','no'=>$id,'board_no'=>$copy,'comment'=>'QA fault comment']);qa_expect(!bpOk($r)&&bpState()===$before,'comment create privacy fault rolls back comment and parent counter');
    $before=bpState();$r=bpComment(['mode'=>'delete','no'=>$comment,'board_no'=>$copy]);qa_expect(!bpOk($r)&&bpState()===$before,'comment delete privacy fault rolls back comment and parent counter');bpFault(false);

    $beforeA=(int)bpRow($id)['comment_cnt'];$beforeB=(int)bpRow($copy)['comment_cnt'];
    $r=bpComment(['mode'=>'save','no'=>$id,'board_no'=>$copy,'parent_no'=>$copy,'comment'=>'QA actual comment']);
    $q=$db->prepare('SELECT no,parent_no FROM nb_board_comment WHERE parent_no=? AND contents=? ORDER BY no DESC LIMIT 1');$q->execute([$id,'QA actual comment']);$createdComment=$q->fetch(PDO::FETCH_ASSOC);
    qa_expect(bpOk($r)&&$createdComment&&(int)$createdComment['parent_no']===$id&&(int)bpRow($id)['comment_cnt']===$beforeA+1&&(int)bpRow($copy)['comment_cnt']===$beforeB,'comment create resolves actual parent despite spoofed request fields');
    $logs=bpLogs('create',$id);qa_expect(in_array(\Security\PiiMask::name('검증관리자'),array_column($logs,'subject_label'),true),'comment creation links actual parent and masked administrator author');
    $r=bpComment(['mode'=>'delete','no'=>(int)$createdComment['no'],'board_no'=>$copy,'parent_no'=>$copy]);
    qa_expect(bpOk($r)&&(int)bpRow($id)['comment_cnt']===$beforeA&&(int)bpRow($copy)['comment_cnt']===$beforeB,'comment deletion uses DB parent and cannot change spoofed target counter');
    $logs=bpLogs('delete',$id);qa_expect(in_array(\Security\PiiMask::name('검증관리자'),array_column($logs,'subject_label'),true),'comment deletion records its original author subject');
    foreach([['mode'=>'save','no'=>$foreign,'comment'=>'invalid'],['mode'=>'delete','no'=>2147483647]]as $data){$before=bpState();$r=bpComment($data);qa_expect(!bpOk($r)&&bpState()===$before,'invalid comment target cannot mutate parent or create success logs');}

    $r=bpPost(['mode'=>'delete.array','board_file_check_no'=>[$copy,$copy2]]);$logsA=bpLogs('delete',$copy);$logsB=bpLogs('delete',$copy2);
    qa_expect(bpOk($r)&&!bpRow($copy)&&!bpRow($copy2)&&count($logsA)===1&&count($logsB)===1&&$logsA[0]['subject_label']==='김*수'&&$logsB[0]['subject_label']==='김*수','successful batch deletion records every actual subject before removal');
    $row=bpRow($id);$oldFile=(string)$row['thumb_image'];$r=bpPost(['mode'=>'delete','no'=>$id]);$logs=bpLogs('delete',$id);
    qa_expect(bpOk($r)&&!bpRow($id)&&in_array('김*수',array_column($logs,'subject_label'),true)&&in_array('박*희',array_column($logs,'subject_label'),true),'parent deletion records post and deleted comment subjects');
    $q=$db->prepare('SELECT COUNT(*) FROM nb_board_comment WHERE parent_no=?');$q->execute([$id]);clearstatcache();qa_expect((int)$q->fetchColumn()===0&&!is_file(dirname(__DIR__).'/uploads/board/'.$oldFile),'successful parent deletion removes comments and final unreferenced upload');
} catch(Throwable $error) { if($error->getMessage()!=='QA_BASELINE_PROBE_COMPLETE')qa_expect(false,get_class($error).': '.$error->getMessage()); }
finally {
    bpFault(false);
    if($db->inTransaction())$db->rollBack();
    $q=$rootDb->prepare('SELECT no,thumb_image,file_attach_1,file_attach_2,file_attach_3,file_attach_4,file_attach_5 FROM nb_board WHERE title LIKE ?');$q->execute([$tag.'%']);$rows=$q->fetchAll(PDO::FETCH_ASSOC);
    foreach($rows as $row){$ownedPosts[(int)$row['no']]=(int)$row['no'];foreach(['thumb_image','file_attach_1','file_attach_2','file_attach_3','file_attach_4','file_attach_5']as $column){$file=(string)$row[$column];if($file!==''&&basename($file)===$file){$path=dirname(__DIR__).'/uploads/board/'.$file;if(is_file($path))unlink($path);}}}
    if($ownedPosts){$ids=array_values($ownedPosts);$marks=implode(',',array_fill(0,count($ids),'?'));$rootDb->prepare('DELETE FROM nb_board_comment WHERE parent_no IN('.$marks.')')->execute($ids);$rootDb->prepare('DELETE FROM nb_board WHERE no IN('.$marks.')')->execute($ids);}
    foreach($boards as $board)$rootDb->prepare('DELETE FROM nb_board_manage WHERE no=? AND title=?')->execute([$board,$tag.'_board']);
    if($admin){foreach(['nb_admin_audit','nb_admin_privacy_access']as $table)$rootDb->prepare('DELETE FROM '.$table.' WHERE actor_no=?')->execute([$admin]);$rootDb->prepare('DELETE FROM nb_admin WHERE no=? AND uid=?')->execute([$admin,$tag]);}
    if($sid){$session=(session_save_path()?:sys_get_temp_dir()).'/sess_'.$sid;if(is_file($session))unlink($session);}
}
qa_done();
