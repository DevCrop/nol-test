<?php
ob_start();
require __DIR__.'/bootstrap.php';
require dirname(__DIR__).'/inc/lib/db.php';
require_once dirname(__DIR__).'/inc/lib/PhpGateRouter.php';
if(!is_file('/.dockerenv')||APP_ENV!=='development'||DB_USER!=='qa_app'||getenv('GATE_ROUTE_MODE')!=='php')exit("SKIP isolated PHP-router development required\n");
$db=DB::getInstance();$tag='qa_form_values_'.bin2hex(random_bytes(5));$admin=0;$sid='';$rows=[];
function formValueHttp(string $path,?array $data=null,string $host='gate.local'):array {
    $host=$host==='gate.local'?GATE_HOST:$host;$headers='Host: '.$host."\r\nCookie: ".session_name().'='.$GLOBALS['sid']."\r\n";
    $opt=['header'=>$headers,'ignore_errors'=>true,'follow_location'=>0,'timeout'=>15];
    if($data!==null){$opt['method']='POST';$opt['header'].="X-Requested-With: XMLHttpRequest\r\nContent-Type: application/x-www-form-urlencoded\r\n";$opt['content']=http_build_query($data+['_csrf'=>$GLOBALS['csrf']]);}
    $body=(string)file_get_contents('http://127.0.0.1'.$path,false,stream_context_create(['http'=>$opt]));preg_match('/\s(\d{3})\s/',$http_response_header[0]??'',$m);
    return [(int)($m[1]??0),$body,json_decode($body,true)];
}
function formValueDom(string $html):DOMDocument {$dom=new DOMDocument();$prev=libxml_use_internal_errors(true);$dom->loadHTML($html);libxml_clear_errors();libxml_use_internal_errors($prev);return $dom;}
function formValueData(DOMDocument $dom):array {
    $fields=[];$xp=new DOMXPath($dom);
    foreach($xp->query('//form[@id="frm"]//*[self::input or self::textarea or self::select]')as $node){
        $name=$node->getAttribute('name');if($name===''||$node->hasAttribute('disabled'))continue;
        if($node->tagName==='input'){$type=strtolower($node->getAttribute('type'));if(in_array($type,['file','submit','button','reset'],true)||in_array($type,['checkbox','radio'],true)&&!$node->hasAttribute('checked'))continue;$value=$node->getAttribute('value');}
        elseif($node->tagName==='textarea')$value=$node->textContent;
        else{$value='';$first=true;foreach($node->getElementsByTagName('option')as $option){if($first||$option->hasAttribute('selected'))$value=$option->getAttribute('value');$first=false;if($option->hasAttribute('selected'))break;}}
        $fields[$name]=$value;
    }
    return $fields;
}
try{
    $router=new PhpGateRouter(dirname(__DIR__),'admin');$method=new ReflectionMethod($router,'rewriteHtml');$method->setAccessible(true);
    $sample='<form action="/admin/pages/design/ajax/banner.process.php"><input value="/pages/cs/parking.php"><input VALUE=\'/uploads/a.png\'><input value=/pages/plain><select><option value="/pages/choice">Choice</option></select><textarea name="body"><a href="/pages/cs/parking.php">QA "body"</a></textarea><a href="/pages/account/index.php">Account</a><img src="/uploads/banner/a.png"><button onclick="location.href=\'/pages/board/board.list.php\'">Go</button><script>const endpoint="/pages/works/ajax/works.process.php";</script><style>.a{background:url(/uploads/a.png)}</style></form>';
    $rewritten=$method->invoke($router,$sample);
    foreach(['<input value="/pages/cs/parking.php">',"<input VALUE='/uploads/a.png'>",'<input value=/pages/plain>','<option value="/pages/choice">','<textarea name="body"><a href="/pages/cs/parking.php">QA "body"</a></textarea>']as $value)qa_expect(strpos($rewritten,$value)!==false,'HTML form payload preserved '.$value);
    foreach(['action="/index.php/pages/design/ajax/banner.process.php"','href="/index.php/pages/account/index.php"','src="/index.php/uploads/banner/a.png"',"location.href='/index.php/pages/board/board.list.php'",'endpoint="/index.php/pages/works/ajax/works.process.php"','url(/index.php/uploads/a.png)']as $value)qa_expect(strpos($rewritten,$value)!==false,'navigation and code still routed '.$value);
    $script=new ReflectionMethod($router,'rewrite');$script->setAccessible(true);qa_expect($script->invoke($router,'const u="/pages/board/ajax/board.process.php";')==='const u="/index.php/pages/board/ajax/board.process.php";','served JS rewrite remains unchanged');
    $db->prepare("INSERT INTO nb_admin(sitekey,uid,upwd,uname,email,active_status,role_code,password_changed_at)VALUES('BLUESQ',?,?,?,?,'Y','super',NOW())")->execute([$tag,password_hash(bin2hex(random_bytes(24)),PASSWORD_DEFAULT),'QA',$tag.'@example.com']);
    $admin=(int)$db->lastInsertId();\Security\AdminAccount::establish(\Security\AdminAccount::findByNo($admin));$sid=session_id();$csrf=\Security\Csrf::token();session_write_close();if(posix_geteuid()===0)chown((session_save_path()?:sys_get_temp_dir()).'/sess_'.$sid,'www-data');
    $banner=['mode'=>'save','b_title'=>$tag,'b_loc'=>'site_main','b_location'=>'qa','b_target'=>'_self','b_link'=>'/pages/cs/parking.php?qa="quoted"&n=1','b_view'=>'Y','b_none_limit'=>'Y','b_none_view'=>'Y'];
    $r=formValueHttp('/index.php/pages/design/ajax/banner.process.php',$banner);$q=$db->prepare('SELECT * FROM nb_banner WHERE b_title=? ORDER BY no DESC LIMIT 1');$q->execute([$tag]);$row=$q->fetch(PDO::FETCH_ASSOC);$id=(int)($row['no']??0);if($id)$rows[]=['nb_banner','no',$id];
    qa_expect($r[0]===200&&($r[2]['result']??'')==='success'&&$id>0,'banner create through real HTTP endpoint');if(!$id)throw new RuntimeException('banner fixture unavailable');
    [$status,$html]=formValueHttp('/index.php/pages/design/banner.view.php?no='.$id);$fields=formValueData(formValueDom($html));
    qa_expect($status===200&&($fields['b_link']??'')===$banner['b_link'],'served banner edit preserves root-relative public URL and quotes');
    $fields['mode']='edit';$r=formValueHttp('/index.php/pages/design/ajax/banner.process.php',$fields);$q->execute([$tag]);$after=$q->fetch(PDO::FETCH_ASSOC);
    qa_expect($r[0]===200&&($r[2]['result']??'')==='success'&&$after['b_link']===$row['b_link'],'actual unchanged banner form save preserves public URL');
    [$publicStatus,$publicHtml]=formValueHttp('/',null,'localhost');$publicDom=formValueDom($publicHtml);$found=false;foreach($publicDom->getElementsByTagName('a')as $a)if($a->getAttribute('href')===$banner['b_link'])$found=true;
    qa_expect($publicStatus===200&&$found&&strpos($publicHtml,'/index.php/pages/cs/parking.php?qa=')===false,'public home still links to public path after unchanged gate edit');
    $boardNo=(int)$db->query("SELECT no FROM nb_board_manage WHERE sitekey='BLUESQ' ORDER BY no LIMIT 1")->fetchColumn();
    $body='<p>QA "body" <a href="/pages/cs/parking.php">parking</a></p>';
    $board=['mode'=>'save','board_no'=>$boardNo,'title'=>$tag.'_board','write_name'=>'QA','contents'=>$body,'direct_url'=>'/pages/cs/parking.php?qa="quoted"&n=1','is_view'=>'N','is_notice'=>'N'];
    $r=formValueHttp('/index.php/pages/board/ajax/board.process.php',$board);$q=$db->prepare('SELECT * FROM nb_board WHERE title=? ORDER BY no DESC LIMIT 1');$q->execute([$board['title']]);$row=$q->fetch(PDO::FETCH_ASSOC);$id=(int)($row['no']??0);if($id)$rows[]=['nb_board','no',$id];
    qa_expect($r[0]===200&&($r[2]['result']??'')==='success'&&$id>0,'board body fixture creates through real HTTP');if(!$id)throw new RuntimeException('board fixture unavailable');
    [$status,$html]=formValueHttp('/index.php/pages/board/board.view.php?no='.$id);$fields=formValueData(formValueDom($html));
    qa_expect($status===200&&($fields['direct_url']??'')===$board['direct_url']&&($fields['contents']??'')===$body,'served board form preserves URL and textarea HTML with quotes');
    $fields['mode']='edit';$r=formValueHttp('/index.php/pages/board/ajax/board.process.php',$fields);$q->execute([$board['title']]);$after=$q->fetch(PDO::FETCH_ASSOC);
    qa_expect($r[0]===200&&($r[2]['result']??'')==='success'&&$after['contents']===$row['contents']&&$after['direct_url']===$row['direct_url'],'actual unchanged board form save preserves body and public URL bytes');
}catch(Throwable $e){qa_expect(false,get_class($e).': '.$e->getMessage());}
finally{
    foreach($rows as [$table,$key,$id])$db->prepare("DELETE FROM $table WHERE $key=?")->execute([$id]);
    if($admin){$db->prepare('DELETE FROM nb_admin_audit WHERE actor_no=?')->execute([$admin]);$db->prepare('DELETE FROM nb_admin_privacy_access WHERE actor_no=?')->execute([$admin]);$db->prepare('DELETE FROM nb_admin WHERE no=? AND uid=?')->execute([$admin,$tag]);}
    if($sid){$f=(session_save_path()?:sys_get_temp_dir()).'/sess_'.$sid;if(is_file($f))unlink($f);}
}
qa_done();
