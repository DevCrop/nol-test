<?php
require_once '../../../../inc/lib/base.class.php';
require_once '../../../lib/admin.check.ajax.php';
$db=DB::getInstance();$uploads_dir=$UPLOAD_DIR_POPUP;$files=\Security\UploadLifecycle::forTable($uploads_dir,$db,'nb_popup',['p_img']);
function popupReply(bool $ok,string $message,int $status=200):void {
    if(ob_get_level()>0)ob_clean();http_response_code($status);header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['result'=>$ok?'success':'fail','msg'=>$message],JSON_UNESCAPED_UNICODE);exit;
}
if(($_SERVER['REQUEST_METHOD']??'GET')!=='POST')popupReply(false,'POST 요청만 허용됩니다.',405);
$mode=(string)($_POST['mode']??'');
try{
    if(!in_array($mode,['save','edit','delete'],true))throw new InvalidArgumentException('잘못된 요청입니다.');
    $current=[];$no=0;
    if($mode!=='save'){
        $no=filter_var($_POST['no']??null,FILTER_VALIDATE_INT);if(!$no||$no<1)popupReply(false,'정보를 찾을 수 없습니다.',404);
        $stmt=$db->prepare('SELECT * FROM nb_popup WHERE no=? AND sitekey=?');$stmt->execute([$no,$NO_SITE_UNIQUE_KEY]);$current=$stmt->fetch(PDO::FETCH_ASSOC);
        if(!$current)popupReply(false,'정보를 찾을 수 없습니다.',404);
    }
    if($mode==='delete'){$stmt=$db->prepare('DELETE FROM nb_popup WHERE no=? AND sitekey=?');$stmt->execute([$no,$NO_SITE_UNIQUE_KEY]);$files->retire($current['p_img']??null);$files->commit();popupReply(true,'정상적으로 삭제되었습니다.');}
    $values=[];foreach(['p_view','p_none_limit','p_title','p_target','p_link','p_loc']as $key){if(isset($_POST[$key])&&!is_scalar($_POST[$key]))throw new InvalidArgumentException('입력값을 확인하세요.');$values[$key]=trim((string)($_POST[$key]??''));}
    $values['p_target']=$values['p_target']===''?'_none':$values['p_target'];
    // Current shared responsive forms do not expose p_loc or PC coordinates.
    if (!array_key_exists('p_loc', $_POST)) $values['p_loc'] = in_array($current['p_loc'] ?? '', ['P','M'], true) ? $current['p_loc'] : 'M';
    if($values['p_title']===''||!in_array($values['p_none_limit'],['Y','N'],true)||!in_array($values['p_view'],['Y','N'],true)||!in_array($values['p_loc'],['P','M'],true))throw new InvalidArgumentException('제목과 팝업 설정을 확인하세요.');
    if(!in_array($values['p_target'],['_none','_self','_blank'],true))throw new InvalidArgumentException('링크 형식을 확인하세요.');
    // The legacy enum supports only _self/_blank; no-link is _self plus an empty URL.
    if($values['p_target']==='_none'){$values['p_link']='';$values['p_target']='_self';}
    elseif($values['p_link']!==''){$link=\Security\SafeLink::normalize($values['p_link']);if($link==='')throw new InvalidArgumentException('HTTP(S) 또는 사이트 내부 링크를 입력하세요.');$values['p_link']=$link;}
    $values['p_sdate']=$values['p_edate']=null;
    if($values['p_none_limit']==='N'){
        foreach(['p_sdate','p_edate']as $key){$date=(string)($_POST[$key]??'');if(!preg_match('/^(\d{4})-(\d{2})-(\d{2})$/D',$date,$parts)||!checkdate((int)$parts[2],(int)$parts[3],(int)$parts[1]))throw new InvalidArgumentException('올바른 시작일과 종료일을 입력하세요.');$values[$key]=$date;}
        if($values['p_sdate']>$values['p_edate'])throw new InvalidArgumentException('시작일은 종료일보다 늦을 수 없습니다.');
    }
    $values['p_left']=$values['p_top']=null;
    if($values['p_loc']==='P')foreach(['p_left','p_top']as $key){$coordinate=filter_var($_POST[$key]??($current[$key]??0),FILTER_VALIDATE_INT);if($coordinate===false||$coordinate<0)throw new InvalidArgumentException('팝업 위치를 확인하세요.');$values[$key]=$coordinate;}
    if(isset($_POST['p_idx'])&&!is_scalar($_POST['p_idx']))throw new InvalidArgumentException('노출 순위를 확인하세요.');
    $rank=trim((string)($_POST['p_idx']??''));
    if($rank!==''){$values['p_idx']=filter_var($rank,FILTER_VALIDATE_INT,['options'=>['min_range'=>0,'max_range'=>2147483647]]);if($values['p_idx']===false)throw new InvalidArgumentException('노출 순위를 확인하세요.');}
    elseif($mode==='save'){$stmt=$db->prepare('SELECT IFNULL(MAX(p_idx)+1,1) FROM nb_popup WHERE sitekey=?');$stmt->execute([$NO_SITE_UNIQUE_KEY]);$values['p_idx']=(int)$stmt->fetchColumn();}
    else $values['p_idx']=(int)$current['p_idx'];
    $result=isset($_FILES['p_img'])?imageUpload($uploads_dir,$_FILES['p_img'],'',false,['jpg','jpeg','png','gif','webp']):null;$saved=(string)($result['saved']??'');
    if($saved!==''){$values['p_img']=$saved;$files->track($saved);if($mode==='edit')$files->retire($current['p_img']??null);}
    elseif($mode==='save')$values['p_img']='';
    if($mode==='save'){$values['sitekey']=$NO_SITE_UNIQUE_KEY;$columns=array_keys($values);$sql='INSERT INTO nb_popup (`'.implode('`,`',$columns).'`,p_rdate)VALUES(:'.implode(',:',$columns).',NOW())';}
    else{$sql='UPDATE nb_popup SET '.implode(',',array_map(static function($column){return '`'.$column.'`=:'.$column;},array_keys($values))).' WHERE no=:no AND sitekey=:sitekey';$values['no']=$no;$values['sitekey']=$NO_SITE_UNIQUE_KEY;}
    $stmt=$db->prepare($sql);$stmt->execute($values);$files->commit();popupReply(true,$mode==='save'?'정상적으로 등록되었습니다.':'정상적으로 수정되었습니다.');
}catch(Throwable $e){
    $files->rollback();
    if($e instanceof InvalidArgumentException||($e instanceof RuntimeException&&!($e instanceof PDOException)))popupReply(false,$e->getMessage(),400);
    error_log('Popup operation failed: '.blue_safe_error($e));popupReply(false,'요청을 처리할 수 없습니다. 다시 시도하세요.',500);
}
