<?php
ob_start();require __DIR__.'/bootstrap.php';require dirname(__DIR__).'/inc/lib/db.php';
if(!is_file('/.dockerenv')||APP_ENV!=='development'||DB_USER!=='qa_app')exit(1);
$db=DB::getInstance();$tag='qa_logui_'.bin2hex(random_bytes(4));$actors=[];$sids=[];
function logUiGet(string $kind,array $params=[],?string $sid=null):array {
 $path='/index.php/pages/account/'.$kind.'.php?'.http_build_query($params);
 $ctx=stream_context_create(['http'=>['header'=>'Host: '.GATE_HOST."\r\nCookie: ".session_name().'='.($sid??$GLOBALS['sid'])."\r\n",'ignore_errors'=>true,'follow_location'=>0,'timeout'=>15]]);
 $body=(string)file_get_contents('http://127.0.0.1'.$path,false,$ctx);preg_match('/\s(\d{3})\s/',$http_response_header[0]??'',$m);return [(int)($m[1]??0),$body];
}
function logUiDom(string $html):DOMXPath {$d=new DOMDocument();libxml_use_internal_errors(true);$d->loadHTML($html);libxml_clear_errors();return new DOMXPath($d);}
function logUiRows(string $html):int {return logUiDom($html)->query('//table[contains(@class,"admin-log-table")]/tbody/tr[td/@data-label]')->length;}
function logUiTotal(string $html):int {$n=logUiDom($html)->query('//p[contains(@class,"admin-log-total")]/strong')->item(0);return $n?(int)str_replace(',','',$n->textContent):-1;}
try{
 foreach(['super','admin']as $role){$uid=$tag.'_'.$role;$db->prepare("INSERT INTO nb_admin(sitekey,uid,upwd,uname,email,active_status,role_code,password_changed_at,menu_permissions)VALUES('BLUESQ',?,?,?,?,'Y',?,NOW(),?)")->execute([$uid,password_hash(bin2hex(random_bytes(20)),PASSWORD_DEFAULT),'QA',$uid.'@example.com',$role,'{}']);$id=(int)$db->lastInsertId();$actors[]=$id;\Security\AdminAccount::establish(\Security\AdminAccount::findByNo($id));$sids[$role]=session_id();session_write_close();if(posix_geteuid()===0)chown((session_save_path()?:sys_get_temp_dir()).'/sess_'.$sids[$role],'www-data');session_id('');session_start();}
 $GLOBALS['sid']=$sids['super'];
 $css=file_get_contents(dirname(__DIR__).'/admin/resource/css/admin-logs.css');qa_expect(strpos($css,'table-layout:fixed;min-width:0')!==false && strpos($css,'height:auto;min-height:7rem')!==false,'log table overrides legacy 1024px minimum and header can wrap');
 $audit=$db->prepare('INSERT INTO nb_admin_audit(sitekey,actor_no,actor_uid,actor_ip,action,entity,target_no,target_label,detail_json,created_at) VALUES(?,?,?,?,?,?,?,?,?,?)');
 $access=$db->prepare('INSERT INTO nb_admin_privacy_access(sitekey,actor_no,actor_uid,actor_ip,action,entity,target_no,subject_label,task,reason,created_at)VALUES(?,?,?,?,?,?,?,?,?,?,?)');
 $auditActions=['create','update','delete'];$accessActions=['view','download','create','update','delete'];$stamp=[];
 for($i=0;$i<205;$i++){
  $date=$i<100?'2026-11-09 23:59:59':'2026-11-10 00:00:00';$uid=$tag.($i===204?'<script>BAD</script>':'');$detail=['affected_rows'=>1,'password'=>'NEVER_LEAK_RAW_JSON','target_ids'=>[1000+$i]];if($i===204)$detail['change_reason']=$tag.'literal%_!';
  $audit->execute(['BLUESQ',$actors[0],$uid,'127.0.0.1',$auditActions[$i%3],$i%2?'nb_board':'account',1000+$i,$tag.'_target_'.$i,json_encode($detail),$date]);$stamp[$i]=(int)$db->lastInsertId();
  $access->execute(['BLUESQ',$actors[0],$uid,'127.0.0.1',$accessActions[$i%5],$i%2?'request':'admin_account',1000+$i,'홍*동',$tag.'_task_'.$i,$i===204?$tag.'literal%_! <script>BAD</script>':'테스트 다운로드 사유',$date]);
 }
 $audit->execute(['QAOTH',$actors[0],$tag,'127.0.0.1','create','nb_board',0,'foreign','{}','2026-11-10 00:00:00']);
 $access->execute(['QAOTH',$actors[0],$tag,'127.0.0.1','view','request',0,'외*인','foreign','', '2026-11-10 00:00:00']);
 foreach(['audit','access']as $kind){
  [$status,$html]=logUiGet($kind,['q'=>$tag]);qa_expect($status===200&&logUiRows($html)===20&&logUiTotal($html)===205,$kind.' first page counts all 205 site-scoped rows');
  $xp=logUiDom($html);$link=$xp->query('//nav[contains(@class,"admin-log-pager")]/a[@aria-label="다음 페이지"]')->item(0);parse_str((string)parse_url($link?$link->getAttribute('href'):'',PHP_URL_QUERY),$next);
  qa_expect(($next['q']??'')===$tag&&($next['page']??'')==='2'&&($next['perpage']??'')==='20',$kind.' pager retains active search and page size');
  [$status,$middle]=logUiGet($kind,['q'=>$tag,'page'=>6]);qa_expect($status===200&&logUiRows($middle)===20&&strpos($middle,'101-120건')!==false,$kind.' middle page stable range');
  [$status,$last]=logUiGet($kind,['q'=>$tag,'page'=>999999]);qa_expect($status===200&&logUiRows($last)===5&&strpos($last,'11/11페이지')!==false,$kind.' oversized page clamps to final five rows');
  [$status,$hundred]=logUiGet($kind,['q'=>$tag,'perpage'=>100]);qa_expect($status===200&&logUiRows($hundred)===100,$kind.' 100 page size supported');
  [$status,$fifty]=logUiGet($kind,['q'=>$tag,'perpage'=>50]);qa_expect($status===200&&logUiRows($fifty)===50,$kind.' 50 page size supported');
  [$status,$invalidSize]=logUiGet($kind,['q'=>$tag,'perpage'=>1000000,'page'=>-1]);qa_expect($status===200&&logUiRows($invalidSize)===20&&strpos($invalidSize,'1/11페이지')!==false,$kind.' invalid pagination uses safe bounded defaults');
  [$status,$zero]=logUiGet($kind,['q'=>$tag.'_not_found']);qa_expect($status===200&&logUiRows($zero)===0&&strpos($zero,'조회 조건에 맞는 기록이 없습니다.')!==false,$kind.' empty search has explicit state');
  [$status,$filtered]=logUiGet($kind,['q'=>$tag,'from'=>'2026-11-10','to'=>'2026-11-10','action'=>$kind==='audit'?'create':'view','entity'=>$kind==='audit'?'account':'admin_account']);$expected=0;for($i=100;$i<205;$i++)if($i%2===0&&$i%($kind==='audit'?3:5)===0)$expected++;
  qa_expect($status===200&&logUiTotal($filtered)===$expected,$kind.' date action entity query combination matches exact rows');
  [$status,$bad]=logUiGet($kind,['q'=>$tag,'from'=>'2026-02-30']);qa_expect($status===200&&logUiRows($bad)===0&&strpos($bad,'올바른 날짜')!==false,$kind.' invalid calendar date shows safe validation');
  [$status,$reverse]=logUiGet($kind,['q'=>$tag,'from'=>'2026-11-11','to'=>'2026-11-10']);qa_expect($status===200&&logUiRows($reverse)===0&&strpos($reverse,'시작일보다 빠를')!==false,$kind.' reversed dates rejected');
  foreach(['action','entity']as $field){[$status,$bad]=logUiGet($kind,[$field=>"x' OR 1=1 --",'q'=>$tag]);qa_expect($status===200&&logUiRows($bad)===0&&strpos($bad,'검색 조건을 확인한 후')!==false,$kind.' '.$field.' allowlist rejects SQL payload');}
  [$status,$literal]=logUiGet($kind,['q'=>$tag.'literal%_!']);qa_expect($status===200&&logUiTotal($literal)===1,$kind.' LIKE wildcard search remains literal');
  [$status,$sql]=logUiGet($kind,['q'=>"' OR 1=1 --"]);qa_expect($status===200&&logUiTotal($sql)===0,$kind.' search SQL payload cannot broaden results');
  $xp=logUiDom($html);$actorNode=$xp->query('//td[@data-label="작업자 / 접속 IP"]/strong')->item(0);$rawActor=$tag.'<script>BAD</script>';
  qa_expect($actorNode&&$actorNode->textContent===\Security\PiiMask::identifier($rawActor)&&strpos($html,$rawActor)===false&&strpos($html,htmlspecialchars($rawActor,ENT_QUOTES,'UTF-8'))===false&&strpos($html,'<script>BAD</script>')===false,$kind.' actor ID is masked without raw actor value or executable markup');
  qa_expect($xp->query('//div[contains(@class,"admin-log-results")][@data-pii-lock]')->length===1&&$xp->query('//div[contains(@class,"admin-log-results")]/preceding-sibling::form')->length===1,$kind.' copy lock covers result card and preserves separate editable filters');
  $rawReader=new \Security\AdminLogReader($db,$kind);$exactActor=$rawReader->read(['q'=>$rawActor]);
  qa_expect($exactActor['total']===1&&$exactActor['rows'][0]['actor_uid']===$rawActor,$kind.' stored actor ID and exact search remain unmasked');
  qa_expect(strpos($html,'admin-log-badge--')!==false&&strpos($html,'admin-logs.css')!==false,$kind.' badge and scoped responsive CSS served');
  [$status,$regular]=logUiGet($kind,['q'=>$tag],$sids['admin']);qa_expect($status===403&&strpos($regular,$tag.'_target')===false,$kind.' ordinary administrator denied before data');
 }
 [$status,$html]=logUiGet('audit',['q'=>$tag]);qa_expect(strpos($html,'NEVER_LEAK_RAW_JSON')===false&&strpos($html,'affected_rows')===false&&strpos($html,'처리 건수: 1건')!==false,'audit exposes safe business detail without raw JSON secrets');
 qa_expect(strpos($html,'대상 번호: 1203')!==false,'audit displays explicit bulk target IDs');
 [$status,$html]=logUiGet('access',['q'=>$tag]);foreach(['열람','다운로드','입력','변경','삭제']as $word)qa_expect(strpos($html,$word)!==false,'privacy action Korean label '.$word);
 $reader=new \Security\AdminLogReader($db,'audit');$one=$reader->read(['q'=>$tag,'perpage'=>100]);qa_expect((int)$one['rows'][0]['no']===$stamp[204]&&(int)$one['rows'][99]['no']===$stamp[105],'stable no-desc ordering even at identical timestamps');
 $data=$reader->read(['q'=>[$tag]]);qa_expect(!empty($data['errors'])&&!$data['rows'],'non-scalar query rejected');
 foreach(['audit','access']as $kind){
  $marker=$tag.'_actorlabels';
  if($kind==='audit'){
   foreach(['','비회원']as $label)$audit->execute(['BLUESQ',$actors[0],$label,'127.0.0.1','create','nb_board',0,$marker,'{}','2026-11-10 00:00:00']);
  }else{
   foreach(['','비회원']as $label)$access->execute(['BLUESQ',$actors[0],$label,'127.0.0.1','view','request',0,'홍*동',$marker,'','2026-11-10 00:00:00']);
  }
  [$status,$html]=logUiGet($kind,['q'=>$marker]);$xp=logUiDom($html);$labels=[];foreach($xp->query('//td[@data-label="작업자 / 접속 IP"]/strong')as $node)$labels[]=$node->textContent;sort($labels);
  $expectedLabels=['비회원','작업자 정보 없음'];sort($expectedLabels);qa_expect($status===200&&$labels===$expectedLabels,$kind.' missing actor and non-member labels preserve meaning');
 }
}catch(Throwable $e){qa_expect(false,get_class($e).': '.$e->getMessage());}
finally{
 if($actors)foreach(['nb_admin_audit','nb_admin_privacy_access']as $table){$s=$db->prepare('DELETE FROM '.$table.' WHERE actor_no IN('.implode(',',array_fill(0,count($actors),'?')).')');$s->execute($actors);}
 foreach($actors as $id)$db->prepare('DELETE FROM nb_admin WHERE no=? AND uid LIKE ?')->execute([$id,$tag.'%']);
 foreach($sids as $id){$file=(session_save_path()?:sys_get_temp_dir()).'/sess_'.$id;if(is_file($file))unlink($file);}
}
qa_done();
