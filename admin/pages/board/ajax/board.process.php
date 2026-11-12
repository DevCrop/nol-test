<?php
require_once '../../../../inc/lib/base.class.php';
require_once '../../../lib/admin.check.ajax.php';
$db=DB::getInstance();$mode=(string)($_POST['mode']??'');
$fileColumns=['thumb_image','file_attach_1','file_attach_2','file_attach_3','file_attach_4','file_attach_5'];
$uploads=\Security\UploadLifecycle::forTable($UPLOAD_DIR_BOARD,$db,'nb_board',$fileColumns,['contents']);
$reply=static function(string $result,string $message,array $extra=[]):void{if(ob_get_level()>0)ob_clean();echo json_encode(array_merge(['result'=>$result,'msg'=>$message],$extra),JSON_UNESCAPED_UNICODE);exit;};
$find=static function(int $id)use($db,$NO_SITE_UNIQUE_KEY):array{$s=$db->prepare('SELECT * FROM nb_board WHERE no=? AND sitekey=?');$s->execute([$id,$NO_SITE_UNIQUE_KEY]);$row=$s->fetch(PDO::FETCH_ASSOC);if(!$row)throw new RuntimeException('정보를 찾을 수 없습니다.');return $row;};
$board=static function($value)use($db,$NO_SITE_UNIQUE_KEY):int{$id=filter_var($value,FILTER_VALIDATE_INT);if(!$id||$id<1)throw new RuntimeException('게시판을 선택해주세요.');$s=$db->prepare('SELECT COUNT(*) FROM nb_board_manage WHERE no=? AND sitekey=?');$s->execute([$id,$NO_SITE_UNIQUE_KEY]);if(!(int)$s->fetchColumn())throw new RuntimeException('게시판을 찾을 수 없습니다.');return $id;};
$category=static function($value,int $boardNo)use($db,$NO_SITE_UNIQUE_KEY):int{if($value===''||$value===null||$value==='0'||$value===0)return 0;$id=filter_var($value,FILTER_VALIDATE_INT);if(!$id||$id<1)throw new RuntimeException('카테고리를 확인해주세요.');$s=$db->prepare('SELECT COUNT(*) FROM nb_board_category WHERE no=? AND board_no=? AND sitekey=?');$s->execute([$id,$boardNo,$NO_SITE_UNIQUE_KEY]);if(!(int)$s->fetchColumn())throw new RuntimeException('선택한 게시판의 카테고리가 아닙니다.');return $id;};
$escape=static function($value):string{if(!is_scalar($value)&&$value!==null)throw new RuntimeException('입력 형식을 확인해주세요.');return htmlspecialchars((string)$value,ENT_QUOTES,'UTF-8');};
$quoted=static fn(string $key):string=>chr(96).$key.chr(96);
try{
    if($mode==='save'||$mode==='edit'){
        // Resolve the target before moving any uploaded files.
        $old=$mode==='edit'?$find((int)($_POST['no']??0)):[];
        $boardNo=$board($_POST['board_no']??($old['board_no']??null));
        $categoryNo=$category($_POST['category_no']??($old['category_no']??0),$boardNo);
        $fields=['board_no'=>$boardNo,'category_no'=>$categoryNo];
        foreach(['title','write_name','contents','direct_url']as $key)$fields[$key]=array_key_exists($key,$_POST)?$escape($_POST[$key]):($old[$key]??'');
        if(trim(htmlspecialchars_decode($fields['title'],ENT_QUOTES))==='')throw new RuntimeException('제목을 입력해주세요.');
        if(trim(htmlspecialchars_decode($fields['write_name'],ENT_QUOTES))==='')throw new RuntimeException('작성자를 입력해주세요.');
        $fields['is_notice']=($_POST['is_notice']??($old['is_notice']??'N'))==='Y'?'Y':'N';
        $fields['is_view']=($_POST['is_view']??($old['is_view']??'N'))==='Y'?'Y':'N';
        $fields['regdate']=$_POST['regdate']??($old['regdate']??date('Y-m-d H:i:s'));
        for($i=1;$i<=15;$i++)$fields['extra'.$i]=array_key_exists('extra'.$i,$_POST)?$escape($_POST['extra'.$i]):($old['extra'.$i]??'');
        if(!$old){$fields['sitekey']=$NO_SITE_UNIQUE_KEY;$fields['user_no']=-1;foreach($fileColumns as $column)$fields[$column]='';for($i=1;$i<=5;$i++)$fields['file_attach_origin_'.$i]='';}
        $deletions=$_POST['attach_file_del']??[];if(!is_array($deletions))throw new RuntimeException('첨부 삭제 항목을 확인해주세요.');
        foreach($deletions as $index){
            if(!is_scalar($index)||!preg_match('/^[0-5]$/D',(string)$index))throw new RuntimeException('첨부 삭제 항목을 확인해주세요.');
            $column=$fileColumns[(int)$index];$fields[$column]=null;$uploads->retire($old[$column]??null);
            if((int)$index>0)$fields['file_attach_origin_'.(int)$index]='';
        }
        foreach($fileColumns as $index=>$column){
            $input=$index===0?'thumb_image':'addFile'.$index;
            $result=imageUpload($UPLOAD_DIR_BOARD,$_FILES[$input]??null,'',$index>0,$board_file_allow??null);
            $name=(string)($result['saved']??'');if($name==='')continue;
            $uploads->track($name);$uploads->retire($old[$column]??null);$fields[$column]=$name;
            if($index>0)$fields['file_attach_origin_'.$index]=(string)($result['origin']??'');
        }
        if($old){
            $sets=implode(',',array_map(static fn($key)=>chr(96).$key.chr(96).'=:'.$key,array_keys($fields)));
            $fields['no']=(int)$old['no'];$fields['sitekey']=$NO_SITE_UNIQUE_KEY;
            $s=$db->prepare('UPDATE nb_board SET '.$sets.' WHERE no=:no AND sitekey=:sitekey');$s->execute($fields);
        }else{
            $keys=array_keys($fields);$s=$db->prepare('INSERT INTO nb_board ('.implode(',',array_map($quoted,$keys)).')VALUES(:'.implode(',:',$keys).')');$s->execute($fields);
        }
        $uploads->commit();$reply('success',$old?'정상적으로 수정되었습니다.':'정상적으로 등록되었습니다.');
    }
    if($mode==='delete'||$mode==='delete.array'){
        $ids=$mode==='delete'?[($_POST['no']??0)]:($_POST['board_file_check_no']??[]);
        if(!is_array($ids)||!$ids)throw new RuntimeException('삭제할 항목을 선택해주세요.');
        $targets=[];foreach($ids as $id){$id=filter_var($id,FILTER_VALIDATE_INT);if(!$id||$id<1)throw new RuntimeException('삭제할 항목을 확인해주세요.');$targets[$id]=$find($id);}
        $marks=implode(',',array_fill(0,count($targets),'?'));
        $s=$db->prepare('DELETE FROM nb_board WHERE sitekey=? AND no IN('.$marks.')');$s->execute(array_merge([$NO_SITE_UNIQUE_KEY],array_keys($targets)));
        foreach($targets as $row)foreach($fileColumns as $column)$uploads->retire($row[$column]??null);
        $uploads->commit();
        // Comment cleanup cannot roll back a successful primary MyISAM deletion.
        try{$s=$db->prepare('DELETE FROM nb_board_comment WHERE parent_no IN('.$marks.')');$s->execute(array_keys($targets));}catch(Throwable $e){error_log('Board comment cleanup failed after deleting posts.');}
        $reply('success','정상적으로 삭제되었습니다.');
    }
    if($mode==='board.copy'){
        $row=$find((int)($_POST['no']??0));unset($row['no']);
        // Copies retain file references; cleanup checks all committed copies before deleting files.
        foreach(array_keys($row)as $key)if(!preg_match('/^[a-zA-Z0-9_]+$/D',$key))throw new RuntimeException('복사할 정보를 확인해주세요.');
        $keys=array_keys($row);$s=$db->prepare('INSERT INTO nb_board ('.implode(',',array_map($quoted,$keys)).')VALUES(:'.implode(',:',$keys).')');$s->execute($row);
        $uploads->commit();$reply('success','정상적으로 복사되었습니다.');
    }
    if(in_array($mode,['board.category','category.depth','board.field'],true)){
        $boardNo=$board($_POST['board_no']??null);$info=getBoardManageInfoByNo($boardNo);$manage=$info[0]??[];
        if($mode==='category.depth')$reply('success','정상적으로 불러왔습니다.',['depth_category_yn'=>$manage['depth_category_yn']??'N']);
        if($mode==='board.category'){$s=$db->prepare('SELECT no,name FROM nb_board_category WHERE sitekey=? AND board_no=? ORDER BY sort_no');$s->execute([$NO_SITE_UNIQUE_KEY,$boardNo]);$reply('success','정상적으로 불러왔습니다.',['category_yn'=>$manage['category_yn']??'N','rows'=>$s->fetchAll(PDO::FETCH_ASSOC)]);}
        $rows=[];foreach($manage as $key=>$value)if(preg_match('/^extra_match_field(\d+)$/D',$key,$m)&&$value!=='')$rows[]=['name'=>$value,'fields'=>'extra'.$m[1]];
        $reply('success','정상적으로 불러왔습니다.',['rows'=>$rows]);
    }
    throw new RuntimeException('지원하지 않는 요청입니다.');
}catch(Throwable $e){$uploads->rollback();$reply('fail',$e instanceof PDOException?'DB 처리 중 오류가 발생했습니다.':($e instanceof RuntimeException?$e->getMessage():blue_safe_error($e)));}
