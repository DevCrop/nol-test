<?php
/** Pure shared-view acceptance: no DB, sessions, mail or HTTP requests. */
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
$root=dirname(__DIR__);
foreach(['PiiMask','AdminLogReader','AdminLogView']as $class)require $root.'/inc/lib/classes/Security/'.$class.'.php';
$actor='member123<script>BAD</script>';
$e=static function($value):string{return htmlspecialchars((string)$value,ENT_QUOTES,'UTF-8');};
$failed=0;$passed=0;
$check=static function(bool $ok,string $label)use(&$failed,&$passed):void{echo ($ok?'PASS ':'FAIL ').$label.PHP_EOL;if($ok)$passed++;else $failed++;};
foreach(['audit','access']as $logKind){
    $logTitle=$logKind==='audit'?'작업 이력':'개인정보 접속기록';
    $logPath='/index.php/pages/account/'.$logKind.'.php';
    $base=['created_at'=>'2026-11-13 09:00:00','actor_ip'=>'127.0.0.1','action'=>'view','entity'=>'request','target_no'=>1,'target_label'=>'대상','detail_json'=>'{}','subject_label'=>'홍*동','task'=>'조회','reason'=>''];
    if($logKind==='audit')$base=array_replace($base,['action'=>'create','entity'=>'nb_board']);
    $logData=['filters'=>['from'=>'','to'=>'','entity'=>'','action'=>'','q'=>'','perpage'=>20],'errors'=>[],'rows'=>[array_replace($base,['actor_uid'=>$actor]),array_replace($base,['actor_uid'=>'']),array_replace($base,['actor_uid'=>'비회원'])],'total'=>3,'start'=>1,'end'=>3,'page'=>1,'pages'=>1];
    ob_start();include $root.'/admin/inc/log.view.php';$html=ob_get_clean();
    $dom=new DOMDocument();libxml_use_internal_errors(true);$dom->loadHTML('<?xml encoding="UTF-8">'.$html);libxml_clear_errors();$xp=new DOMXPath($dom);
    $nodes=$xp->query('//td[@data-label="작업자 / 접속 IP"]/strong');
    $check($nodes->length===3&&$nodes->item(0)->textContent===\Security\PiiMask::identifier($actor),$logKind.' actor identifier masked');
    $check(strpos($html,$actor)===false&&strpos($html,$e($actor))===false&&strpos($html,'<script>BAD</script>')===false,$logKind.' raw actor absent from HTML');
    $check($nodes->item(1)->textContent==='작업자 정보 없음'&&$nodes->item(2)->textContent==='비회원',$logKind.' missing/non-member labels preserved');
    $check($xp->query('//div[contains(@class,"admin-log-results")][@data-pii-lock]')->length===1,$logKind.' results scoped for copy/drag restriction');
    $check($xp->query('//form[contains(@class,"admin-log-filters")]/ancestor::*[@data-pii-lock]')->length===0&&$xp->query('//form[contains(@class,"admin-log-filters")]//input[@name="q"]')->length===1,$logKind.' editable filters remain outside restriction');
}
echo $failed?'FAIL '.$failed.PHP_EOL:'OK '.$passed.PHP_EOL;
exit($failed?1:0);
