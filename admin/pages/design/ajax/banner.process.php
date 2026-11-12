<?php
require_once '../../../../inc/lib/base.class.php';
require_once '../../../lib/admin.check.ajax.php';
$pdo=DB::getInstance();$uploads_dir=$UPLOAD_DIR_BANNER;$fileColumns=['b_img','b_img_mobile','b_poster_img'];
$files=\Security\UploadLifecycle::forTable($uploads_dir,$pdo,'nb_banner',$fileColumns);
function bannerReply(bool $ok,string $message,int $status=200):void {
    if(ob_get_level()>0)ob_clean();http_response_code($status);header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['result'=>$ok?'success':'fail','msg'=>$message],JSON_UNESCAPED_UNICODE);exit;
}
if(($_SERVER['REQUEST_METHOD']??'GET')!=='POST')bannerReply(false,'POST 요청만 허용됩니다.',405);
$mode=(string)($_POST['mode']??'');
try {
    if(!in_array($mode,['save','edit','delete'],true))throw new InvalidArgumentException('잘못된 요청입니다.');
    $current=[];$no=0;
    if($mode!=='save'){
        $no=filter_var($_POST['no']??null,FILTER_VALIDATE_INT);if(!$no||$no<1)bannerReply(false,'정보를 찾을 수 없습니다.',404);
        $stmt=$pdo->prepare('SELECT * FROM nb_banner WHERE no=? AND sitekey=?');$stmt->execute([$no,$NO_SITE_UNIQUE_KEY]);$current=$stmt->fetch(PDO::FETCH_ASSOC);
        if(!$current)bannerReply(false,'정보를 찾을 수 없습니다.',404);
    }
    if($mode==='delete'){
        $stmt=$pdo->prepare('DELETE FROM nb_banner WHERE no=? AND sitekey=?');$stmt->execute([$no,$NO_SITE_UNIQUE_KEY]);
        foreach($fileColumns as $column)$files->retire($current[$column]??null);
        $files->commit();bannerReply(true,'정상적으로 삭제되었습니다.');
    }
    $input=array_replace($_POST,\Security\BannerInput::validate($_POST));
    $values=['b_loc'=>(string)($input['b_loc']??''),'b_link'=>$input['b_link'],'b_target'=>$input['b_target'],'b_view'=>(string)($input['b_view']??''),'b_title'=>trim((string)($input['b_title']??'')),
        'b_none_limit'=>$input['b_none_limit'],'b_none_view'=>$input['b_none_view'],'b_sdate'=>$input['b_sdate'],'b_edate'=>$input['b_edate'],'b_sdate_view'=>$input['b_sdate_view'],'b_edate_view'=>$input['b_edate_view'],
        'b_desc'=>(string)($input['b_desc']??''),'b_contents'=>(string)($input['content']??''),'b_location'=>(string)($input['b_location']??'')];
    if($values['b_title']===''||$values['b_loc']==='')throw new InvalidArgumentException('배너 제목과 노출 위치를 입력하세요.');
    if(isset($input['b_idx'])&&!is_scalar($input['b_idx']))throw new InvalidArgumentException('노출 순위를 확인하세요.');
    $rank=trim((string)($input['b_idx']??''));
    if($rank!==''){$values['b_idx']=filter_var($rank,FILTER_VALIDATE_INT,['options'=>['min_range'=>0,'max_range'=>2147483647]]);if($values['b_idx']===false)throw new InvalidArgumentException('노출 순위를 확인하세요.');}
    elseif($mode==='save'){$stmt=$pdo->prepare('SELECT IFNULL(MAX(b_idx)+1,1) FROM nb_banner WHERE sitekey=? AND b_loc=?');$stmt->execute([$NO_SITE_UNIQUE_KEY,$values['b_loc']]);$values['b_idx']=(int)$stmt->fetchColumn();}
    else $values['b_idx']=(int)$current['b_idx'];
    foreach($fileColumns as $column){
        $result=isset($_FILES[$column])?imageUpload($uploads_dir,$_FILES[$column],'',false,['jpg','jpeg','png','gif','webp']):null;$saved=(string)($result['saved']??'');
        if($saved!==''){$values[$column]=$saved;$files->track($saved);if($mode==='edit')$files->retire($current[$column]??null);}
        elseif($mode==='save')$values[$column]='';
    }
    if($mode==='save'){$values['sitekey']=$NO_SITE_UNIQUE_KEY;$columns=array_keys($values);$sql='INSERT INTO nb_banner (`'.implode('`,`',$columns).'`,b_rdate) VALUES (:'.implode(',:',$columns).',NOW())';}
    else{$sql='UPDATE nb_banner SET '.implode(',',array_map(static function($column){return '`'.$column.'`=:'.$column;},array_keys($values))).' WHERE no=:no AND sitekey=:sitekey';$values['no']=$no;$values['sitekey']=$NO_SITE_UNIQUE_KEY;}
    $stmt=$pdo->prepare($sql);$stmt->execute($values);$files->commit();bannerReply(true,$mode==='save'?'정상적으로 등록되었습니다.':'정상적으로 수정되었습니다.');
}catch(Throwable $e){
    $files->rollback();
    if($e instanceof InvalidArgumentException||($e instanceof RuntimeException&&!($e instanceof PDOException)))bannerReply(false,$e->getMessage(),400);
    error_log('Banner operation failed: '.blue_safe_error($e));bannerReply(false,'요청을 처리할 수 없습니다. 다시 시도하세요.',500);
}
