<?php
if(PHP_SAPI!=='cli')exit(1);
require dirname(__DIR__).'/inc/lib/AccountAccessPolicy.php';
$fail=0;
function policyCheck(bool $ok,string $label):void {global $fail;echo ($ok?'PASS ':'FAIL ').$label.PHP_EOL;if(!$ok)$fail++;}
function policyReject(callable $fn):bool {try{$fn();return false;}catch(RuntimeException $e){return true;}}
$from='2026-10-27 10:00:00';$until='2026-10-27 11:00:00';
$fromEpoch=(new DateTimeImmutable($from,new DateTimeZone('Asia/Seoul')))->getTimestamp();
$untilEpoch=(new DateTimeImmutable($until,new DateTimeZone('Asia/Seoul')))->getTimestamp();
$row=['authority_valid_from'=>$from,'authority_valid_until'=>$until];
policyCheck(AccountAccessPolicy::allowed([],strtotime($from)),'legacy NULL period remains unrestricted');
policyCheck(!AccountAccessPolicy::allowed($row,$fromEpoch-1),'before start denied');
policyCheck(AccountAccessPolicy::allowed($row,$fromEpoch),'start boundary inclusive');
policyCheck(AccountAccessPolicy::allowed($row,$untilEpoch-1),'before end allowed');
policyCheck(!AccountAccessPolicy::allowed($row,$untilEpoch),'end boundary exclusive');
policyCheck(AccountAccessPolicy::allowed($row,strtotime('2026-10-27T01:00:00Z')),'Korean policy time independent of PHP UTC default');
policyCheck(policyReject(function(){AccountAccessPolicy::reason([]);}), 'reason missing rejected');
policyCheck(policyReject(function(){AccountAccessPolicy::reason(['change_reason'=>[]]);}), 'reason array rejected');
policyCheck(policyReject(function(){AccountAccessPolicy::reason(['change_reason'=>str_repeat('가',301)]);}), 'reason length enforced');
policyCheck(AccountAccessPolicy::reason(['change_reason'=>' 권한 갱신 '])==='권한 갱신','reason normalized');
policyCheck(policyReject(function(){AccountAccessPolicy::period(['authority_valid_from'=>'2026-02-30T10:00']);}), 'impossible calendar date rejected');
policyCheck(policyReject(function(){AccountAccessPolicy::period(['authority_valid_from'=>[]]);}), 'date array rejected');
policyCheck(policyReject(function(){AccountAccessPolicy::period(['authority_valid_from'=>'2026-10-28T10:00','authority_valid_until'=>'2026-10-27T10:00']);}), 'inverted period rejected');
policyCheck(AccountAccessPolicy::period([],$row)===$row,'omitted fields preserve existing policy');
policyCheck(AccountAccessPolicy::period(['authority_valid_from'=>'','authority_valid_until'=>''],$row)===['authority_valid_from'=>null,'authority_valid_until'=>null],'explicit unrestricted period supported');
policyCheck(AccountAccessPolicy::version($row)!==AccountAccessPolicy::version(array_merge($row,['authority_valid_until'=>null])),'policy mutation changes MFA authority version');
$lines=AccountAccessPolicy::auditLines(['change_reason'=>'검토','authority_before'=>[],'authority_after'=>['account_identifier'=>'raw_id','owner_display_name'=>'raw_name','role'=>'super']],static function($v){return 'MASKED_ID';},static function($v){return 'MASKED_NAME';});
policyCheck(strpos(implode(' ',$lines),'raw_id')===false && strpos(implode(' ',$lines),'MASKED_ID')!==false,'audit display masks subject identity');
echo $fail?"AUTHORITY POLICY QA FAILED\n":"AUTHORITY POLICY QA PASSED\n";exit($fail?1:0);
