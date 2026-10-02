<?php
ob_start();
require __DIR__.'/bootstrap.php';
require_once dirname(__DIR__).'/inc/lib/db.php';
if (APP_ENV !== 'development' || !is_file('/.dockerenv') || DB_USER !== 'qa_app') { echo "SKIP isolated QA required\n"; exit; }
$db=DB::getInstance(); $ids=[]; $tag='qa_banner_'.bin2hex(random_bytes(4)); $admin=0; $sid='';
$mode=(string)$db->query('SELECT @@SESSION.sql_mode')->fetchColumn();
function bannerHttp(string $path, ?array $data=null, bool $gate=false): array {
    $headers="Host: ".($data===null && !$gate?'localhost':'gate.local')."\r\n";
    if ($data!==null || $gate) $headers.='Cookie: '.session_name().'='.$GLOBALS['sid']."\r\n";
    if ($data!==null) $headers.="Content-Type: application/x-www-form-urlencoded\r\nX-Requested-With: XMLHttpRequest\r\n";
    $options=['header'=>$headers,'ignore_errors'=>true,'follow_location'=>0,'timeout'=>10];
    if($data!==null){$options['method']='POST';$options['content']=http_build_query($data+['_csrf'=>$GLOBALS['csrf']]);}
    $body=(string)file_get_contents('http://127.0.0.1'.$path,false,stream_context_create(['http'=>$options]));
    preg_match('/\s(\d{3})\s/',$http_response_header[0]??'',$m); return [(int)($m[1]??0),$body,json_decode($body,true)];
}
try {
    foreach(['https://example.com/pages/cs/parking.php','/pages/cs/parking.php','#parking','?tab=parking'] as $url)
        qa_expect(\Security\SafeLink::normalize($url)===$url,'allowed banner link '.$url);
    foreach(['javascript:alert(1)','data:text/html,test','//evil.example','/\\evil.example',"https://example.com/\nattack"] as $url)
        qa_expect(\Security\SafeLink::normalize($url)==='','unsafe banner scheme/control rejected');
    $unlimited=['b_target'=>'_self','b_link'=>'/pages/cs/parking.php','b_none_limit'=>'Y','b_none_view'=>'Y'];
    $normalized=\Security\BannerInput::validate($unlimited);
    qa_expect($normalized['b_sdate']===null && $normalized['b_sdate_view']===null,'unlimited uses nullable dates, no SQL mode relaxation');
    foreach([['b_target'=>'bad'],['b_link'=>'javascript:alert(1)'],['b_none_view'=>'N','b_sdate_view'=>'2026-02-30','b_edate_view'=>'2026-03-01'],['b_none_view'=>'N','b_sdate_view'=>'2026-10-03','b_edate_view'=>'2026-10-02']] as $bad){
        $denied=false;try{\Security\BannerInput::validate(array_replace($unlimited,$bad));}catch(InvalidArgumentException $e){$denied=true;}
        qa_expect($denied,'invalid banner input rejected before mutation');
    }
    // Legacy fixture only: simulate old dump data, then restore strict mode for all requests.
    $db->exec("SET SESSION sql_mode=".$db->quote(str_replace(['NO_ZERO_DATE','NO_ZERO_IN_DATE'],'',$mode)));
    foreach(['legacy'=>['BLUESQ','site_main','Y','N','0000-00-00','0000-00-00','javascript:alert(77331)'],
        'unlimited'=>['BLUESQ','site_main','Y','Y',null,null,'/pages/cs/parking.php'],
        'hidden'=>['BLUESQ','site_main','N','Y',null,null,'/pages/cs/parking.php'],
        'foreign'=>['QAOTH','site_main','Y','Y',null,null,'/pages/cs/parking.php'],
        'other_position'=>['BLUESQ','other','Y','Y',null,null,'/pages/cs/parking.php'],
        'expired'=>['BLUESQ','site_main','Y','N','2001-01-01','2001-01-02','/pages/cs/parking.php']] as $kind=>$v){
        $q=$db->prepare('INSERT INTO nb_banner(sitekey,b_loc,b_view,b_none_view,b_sdate_view,b_edate_view,b_link,b_title,b_img,b_img_mobile,b_target) VALUES(?,?,?,?,?,?,?,?,?,?,?)');
        $q->execute(array_merge($v,[$tag.$kind,'qa.png','qa.png','_self'])); $ids[$kind]=(int)$db->lastInsertId();
    }
    $db->exec('SET SESSION sql_mode='.$db->quote($mode));
    $visible=array_column(\Security\MainBanner::visible($db,'BLUESQ',date('Y-m-d')),'no');
    qa_expect(in_array($ids['legacy'],$visible) && in_array($ids['unlimited'],$visible),'strict MySQL includes legacy zero and nullable unlimited banners');
    qa_expect(!array_intersect([$ids['hidden'],$ids['foreign'],$ids['other_position'],$ids['expired']],$visible),'hidden foreign non-main and expired banners excluded');
    [$status,$html]=bannerHttp('/');
    qa_expect($status===200 && strpos($html,$tag.'legacy')!==false && strpos($html,$tag.'unlimited')!==false,'main HTTP actually renders eligible banners');
    qa_expect(strpos($html,'javascript:alert(77331)')===false && strpos($html,'Invalid argument supplied')===false,'main has no executable banner URL or foreach warning');
    qa_expect(strpos($html,'href="/pages/cs/parking.php"')!==false,'parking destination rendered without modifying real banner');
    $uid=$tag.'admin';
    $db->prepare("INSERT INTO nb_admin(sitekey,uid,upwd,uname,email,active_status,role_code,password_changed_at) VALUES('BLUESQ',?,?,?,?,'Y','super',NOW())")->execute([$uid,password_hash(bin2hex(random_bytes(16)),PASSWORD_DEFAULT),'QA',$uid.'@example.com']);
    $admin=(int)$db->lastInsertId(); \Security\AdminAccount::establish(\Security\AdminAccount::findByNo($admin));
    $sid=session_id();$csrf=\Security\Csrf::token();session_write_close();if(posix_geteuid()===0)chown((session_save_path()?:sys_get_temp_dir()).'/sess_'.$sid,'www-data');
    $input=$unlimited+['mode'=>'edit','no'=>$ids['unlimited'],'b_loc'=>'site_main','b_title'=>$tag.'updated','b_view'=>'Y','b_idx'=>'99','b_location'=>'QA'];
    [$status,,$json]=bannerHttp('/admin/pages/design/ajax/banner.process.php',$input);
    qa_expect($status===200 && ($json['result']??'')==='success','authenticated HTTP saves unlimited nullable schedule under strict mode');
    [$status,,$json]=bannerHttp('/admin/pages/design/ajax/banner.process.php',array_replace($input,['b_link'=>'javascript:alert(1)']));
    qa_expect($status===400 && ($json['result']??'')==='fail','authenticated HTTP rejects dangerous banner link');
    $q=$db->prepare('SELECT b_link,b_sdate_view,b_edate_view FROM nb_banner WHERE no=?');$q->execute([$ids['unlimited']]);$row=$q->fetch(PDO::FETCH_ASSOC);
    qa_expect($row['b_link']==='/pages/cs/parking.php' && $row['b_sdate_view']===null && $row['b_edate_view']===null,'rejected update preserves saved link and schedule');
    $payload='" onfocus="alert(1)';
    $db->prepare('UPDATE nb_banner SET b_title=?,b_desc=? WHERE no=?')->execute([$tag.$payload,$payload,$ids['unlimited']]);
    [$status,$html]=bannerHttp('/admin/pages/design/banner.view.php?no='.$ids['unlimited'],null,true);
    qa_expect($status===200 && strpos($html,htmlspecialchars($payload,ENT_QUOTES,'UTF-8'))!==false && strpos($html,'value="'.$payload.'"')===false,'banner edit title and subtitle cannot escape quoted input attributes');
} catch(Throwable $e){qa_expect(false,get_class($e).': '.$e->getMessage());}
finally {
    $db->exec('SET SESSION sql_mode='.$db->quote($mode));
    foreach($ids as $id)$db->prepare('DELETE FROM nb_banner WHERE no=? AND b_title LIKE ?')->execute([$id,$tag.'%']);
    if($admin){$db->prepare('DELETE FROM nb_admin_audit WHERE actor_no=?')->execute([$admin]);$db->prepare('DELETE FROM nb_admin WHERE no=? AND uid LIKE ?')->execute([$admin,$tag.'%']);}
    if($sid!=='' && is_file((session_save_path()?:sys_get_temp_dir()).'/sess_'.$sid))unlink((session_save_path()?:sys_get_temp_dir()).'/sess_'.$sid);
}
qa_done();
