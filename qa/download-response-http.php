<?php
ob_start();require __DIR__.'/bootstrap.php';require dirname(__DIR__).'/inc/lib/db.php';
if(!is_file('/.dockerenv')||APP_ENV!=='development'||DB_USER!=='qa_app')exit(1);
$artifactRoot=__DIR__.'/artifacts/download-corruption-20261110';if(!is_dir($artifactRoot))mkdir($artifactRoot,0755,true);
$db=DB::getInstance();$tag='qadlb_'.bin2hex(random_bytes(4));$actors=[];$jars=[];$posts=[];$files=[];$rental=0;
function dlHttp(string $path,string $host,?array $jar=null,string $method='GET',array $data=[]):array{
 $headers='Host: '.$host."\r\nConnection: close\r\n";if($jar)$headers.='Cookie: '.session_name().'='.$jar['sid']."\r\n";
 if($method==='POST'){$data['_csrf']=$jar['csrf']??'';$headers.="Content-Type: application/x-www-form-urlencoded\r\n";}
 $ctx=stream_context_create(['http'=>['method'=>$method,'header'=>$headers,'content'=>$method==='POST'?http_build_query($data):'','ignore_errors'=>true,'follow_location'=>0,'timeout'=>15]]);
 $body=(string)file_get_contents('http://127.0.0.1'.$path,false,$ctx);$status=0;$h=[];
 foreach($http_response_header as $line){if(preg_match('~^HTTP/\S+ (\d+)~',$line,$m))$status=(int)$m[1];elseif(strpos($line,':')!==false){[$k,$v]=explode(':',$line,2);$h[strtolower($k)]=trim($v);}}
 return ['status'=>$status,'body'=>$body,'headers'=>$h];
}
function dlPost(array $template,string $stored,string $origin,string $state='public',string $site='BLUESQ'):int{
 global $db,$posts,$tag;$row=$template;unset($row['no']);foreach(['thumb_image','file_attach_1','file_attach_2','file_attach_3','file_attach_4','file_attach_5']as $c)$row[$c]='';
 $row['sitekey']=$site;$row['title']=$tag;$row['write_name']='홍길동';$row['contents']='QA';$row['is_view']=$state==='hidden'?'N':'Y';$row['is_secret']=$state==='secret'?'Y':'N';$row['file_attach_1']=$stored;$row['file_attach_origin_1']=$origin;$row['thumb_image']=$stored;
 $keys=array_keys($row);$db->prepare('INSERT INTO nb_board (`'.implode('`,`',$keys).'`)VALUES('.implode(',',array_fill(0,count($keys),'?')).')')->execute(array_values($row));$id=(int)$db->lastInsertId();$posts[]=$id;return $id;
}
function dlExact(array $r,string $bytes,string $label):void{
 if($r['status']===200)file_put_contents(__DIR__.'/artifacts/download-corruption-20261110/received-'.sha1($label).'.bin',$r['body']);
 qa_expect($r['status']===200 && hash_equals(hash('sha256',$bytes),hash('sha256',$r['body'])),$label.' exact original bytes including first/last byte');
 qa_expect(($r['headers']['content-length']??'')===(string)strlen($bytes) && strlen($r['body'])===strlen($bytes),$label.' declared and received length match');
 qa_expect(strpos($r['headers']['content-type']??'','application/octet-stream')===0 && strpos($r['headers']['content-disposition']??'','attachment;')===0 && empty($r['headers']['content-encoding']),$label.' binary attachment headers');
}
try{
 foreach(['super','board','denied']as $role){$uid=$tag.'_'.$role;$grants=$role==='board'?json_encode(['board'=>['view'=>true]]):'{}';$db->prepare("INSERT INTO nb_admin(sitekey,uid,upwd,uname,email,active_status,role_code,password_changed_at,menu_permissions)VALUES('BLUESQ',?,?,?,?,'Y',?,NOW(),?)")->execute([$uid,password_hash(bin2hex(random_bytes(20)),PASSWORD_DEFAULT),'QA',$uid.'@example.com',$role==='super'?'super':'admin',$grants]);$id=(int)$db->lastInsertId();$actors[]=$id;
  if(session_status()!==PHP_SESSION_ACTIVE){session_id('');session_start();}Security\AdminAccount::establish(Security\AdminAccount::findByNo($id));$jars[$role]=['sid'=>session_id(),'csrf'=>Security\Csrf::token()];session_write_close();if(posix_geteuid()===0)chown((session_save_path()?:sys_get_temp_dir()).'/sess_'.$jars[$role]['sid'],'www-data');
 }
 $template=$db->query("SELECT * FROM nb_board WHERE sitekey='BLUESQ' LIMIT 1")->fetch(PDO::FETCH_ASSOC);if(!$template)throw new RuntimeException('Board template missing');
 $base=dirname(__DIR__).'/uploads/board';if(!is_dir($base))mkdir($base,0755,true);$ids=[];$bodies=[];
 foreach(['pdf','zip','xls','xlsx','png']as $ext){$bytes=file_get_contents(__DIR__.'/fixtures/download/'.$ext.'.bin');$stored='file_'.$tag.'.'.$ext;$files[]=$base.'/'.$stored;file_put_contents(end($files),$bytes);$id=dlPost($template,$stored,'한글 "검증" 파일.'.$ext);$ids[$ext]=$id;$bodies[$ext]=$bytes;
  dlExact(dlHttp('/pages/board/board.file.download.php?no='.$id.'&fld=attach1','localhost'),$bytes,'public '.$ext);
  dlExact(dlHttp('/index.php/pages/board/board.file.download.php?no='.$id.'&fld=attach1','gate.local',$jars['board']),$bytes,'gate authorized '.$ext);
 }
 $id=$ids['zip'];$url='/pages/board/board.file.download.php?no='.$id.'&fld=attach1';
 $r=dlHttp($url,'localhost',null,'HEAD');qa_expect($r['status']===200&&$r['body']===''&&($r['headers']['content-length']??'')===(string)strlen($bodies['zip']),'public HEAD returns length without binary body');
 $r=dlHttp('/index.php'.$url,'gate.local',$jars['board'],'HEAD');qa_expect($r['status']===200&&$r['body']==='','gate HEAD returns no body');
 $r=dlHttp($url,'localhost',$jars['super'],'POST');qa_expect($r['status']===405&&!isset($r['headers']['content-disposition']),'public POST rejected without misleading download header');
 $r=dlHttp('/index.php'.$url,'gate.local');qa_expect(in_array($r['status'],[302,401],true)&&!isset($r['headers']['content-disposition'])&&$r['body']!==$bodies['zip'],'unauthenticated gate cannot download file');
 $r=dlHttp('/index.php'.$url,'gate.local',$jars['denied']);qa_expect($r['status']===403&&!isset($r['headers']['content-disposition']),'administrator without board permission denied');
 foreach(['hidden','secret']as $state){$id=dlPost($template,'file_'.$tag.'.zip','비공개.zip',$state);$url='/pages/board/board.file.download.php?no='.$id.'&fld=attach1';$r=dlHttp($url,'localhost');qa_expect($r['status']===404&&!isset($r['headers']['content-disposition']),'public '.$state.' attachment denied');dlExact(dlHttp('/index.php'.$url,'gate.local',$jars['board']),$bodies['zip'],'gate '.$state.' attachment');}
 foreach(['foreign','missing','traversal']as $case){$stored=$case==='missing'?'file_'.$tag.'_absent.zip':($case==='traversal'?'../../.env':'file_'.$tag.'.zip');$id=dlPost($template,$stored,'검증.zip','public',$case==='foreign'?'QAFRGN':'BLUESQ');foreach([['','localhost',null],['/index.php','gate.local',$jars['board']]]as [$prefix,$host,$jar]){$r=dlHttp($prefix.'/pages/board/board.file.download.php?no='.$id.'&fld=attach1',$host,$jar);qa_expect($r['status']===404&&!isset($r['headers']['content-disposition']),$host.' '.$case.' target denied');}}
 foreach(['fld=../attach1','fld[]=attach1','fld=unknown','fld=attach1&no=-1']as $query){$r=dlHttp('/pages/board/board.file.download.php?no='.$ids['zip'].'&'.$query,'localhost');qa_expect($r['status']===400&&!isset($r['headers']['content-disposition']),'invalid download query safely rejected '.$query);}
 $r=dlHttp('/inc/lib/board.file.download.php?no='.$ids['zip'].'&fld=attach1','localhost');qa_expect(in_array($r['status'],[403,404],true)&&!isset($r['headers']['content-disposition']),'retired unguarded legacy download denied by server or endpoint');
 $r=dlHttp('/index.php/pages/board/board.view.php?no='.$ids['zip'],'gate.local',$jars['super']);$dom=new DOMDocument();libxml_use_internal_errors(true);$dom->loadHTML($r['body']);libxml_clear_errors();$links=[];foreach($dom->getElementsByTagName('a')as $a)if(strpos($a->getAttribute('href'),'board.file.download.php')!==false)$links[]=$a->getAttribute('href');
 qa_expect($r['status']===200&&count($links)===2&&count(array_filter($links,static fn($u)=>strpos($u,'/index.php/pages/board/board.file.download.php?')===0))===2,'actual admin thumbnail and attachment links use PHP gate route');
 foreach($links as $link)dlExact(dlHttp(html_entity_decode($link,ENT_QUOTES,'UTF-8'),'gate.local',$jars['super']),$bodies['zip'],'actual admin rendered download link');
 $empty=dlPost($template,'file_'.$tag.'.zip','');$r=dlHttp('/pages/board/board.file.download.php?no='.$empty.'&fld=attach1','localhost');qa_expect(strpos($r['headers']['content-disposition']??'',rawurlencode('file_'.$tag.'.zip'))!==false,'empty original name falls back to stored extension');
 $weird=dlPost($template,'file_'.$tag.'.zip',"문서\r\nX-Evil: yes.zip");$r=dlHttp('/pages/board/board.file.download.php?no='.$weird.'&fld=attach1','localhost');qa_expect(!isset($r['headers']['x-evil'])&&strpos($r['headers']['content-disposition']??'',"\r")===false,'filename cannot inject response headers');
 $db->prepare("INSERT INTO nb_request(sitekey,organization_name,manager_name,file_1,file_1_origin,regdate)VALUES('BLUESQ','QA','홍길동',?,'검증.zip',NOW())")->execute(['file_'.$tag.'.zip']);$rental=(int)$db->lastInsertId();
 $view=dlHttp('/index.php/pages/request/request.view.php?no='.$rental,'gate.local',$jars['super']);$doc=new DOMDocument();libxml_use_internal_errors(true);$doc->loadHTML($view['body']);libxml_clear_errors();$xp=new DOMXPath($doc);
 $downloadForms=$xp->query('//form[contains(concat(" ",normalize-space(@class)," ")," privacy-download ")]');$form=$downloadForms->item(0);
 qa_expect($view['status']===200 && $downloadForms->length===1 && $form->hasAttribute('data-native-download') && strtolower($form->getAttribute('method'))==='post' && $form->getAttribute('action')==='/index.php/pages/request/ajax/request.download.php','actual rental form classifies attachment response independently of page navigation');
 $reason=$form?$xp->query('.//input[@name="reason"]',$form)->item(0):null;
 qa_expect($reason && $reason->hasAttribute('required') && $reason->getAttribute('minlength')==='5' && $reason->getAttribute('maxlength')==='500','actual native download retains required reason validation');
 $r=dlHttp('/index.php/pages/request/ajax/request.download.php','gate.local',$jars['super'],'POST',['no'=>$rental,'field'=>'file_1','reason'=>'첨부파일 무결성 검증']);dlExact($r,$bodies['zip'],'rental authorized attachment');
 $q=$db->prepare("SELECT COUNT(*) FROM nb_admin_privacy_access WHERE actor_no=? AND entity='request' AND target_no=? AND action='download' AND reason=?");$q->execute([$actors[0],$rental,'첨부파일 무결성 검증']);qa_expect((int)$q->fetchColumn()===1,'rental reason and privacy download record preserved');
 $r=dlHttp('/index.php/pages/request/ajax/request.download.php','gate.local',$jars['super'],'POST',['no'=>$rental,'field'=>'file_1','reason'=>'']);qa_expect($r['status']===400&&!isset($r['headers']['content-disposition']),'rental blank reason still denied');
 $r=dlHttp('/index.php/pages/request/ajax/request.download.php','gate.local',$jars['board'],'POST',['no'=>$rental,'field'=>'file_1','reason'=>'첨부파일 무결성 검증']);qa_expect($r['status']===403&&!isset($r['headers']['content-disposition']),'board-only role cannot download rental attachment');
}catch(Throwable $e){qa_expect(false,$e->getMessage());}
finally{
 foreach($posts as $id)$db->prepare('DELETE FROM nb_board WHERE no=? AND title=?')->execute([$id,$tag]);
 if($rental)$db->prepare("DELETE FROM nb_request WHERE no=? AND organization_name='QA'")->execute([$rental]);
 foreach($files as $p)if(is_file($p)&&strpos(basename($p),'file_'.$tag.'.')===0)unlink($p);
 foreach($actors as $i=>$id){$db->prepare('DELETE FROM nb_admin_audit WHERE actor_no=?')->execute([$id]);$db->prepare('DELETE FROM nb_admin_privacy_access WHERE actor_no=?')->execute([$id]);$db->prepare("DELETE FROM nb_admin WHERE no=? AND uid LIKE ?")->execute([$id,$tag.'_%']);}
 foreach($jars as $jar){$p=(session_save_path()?:sys_get_temp_dir()).'/sess_'.$jar['sid'];if(is_file($p))unlink($p);}
}
qa_done();
