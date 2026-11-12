<?php
$root=$_SERVER['DOCUMENT_ROOT'];
require_once $root.'/inc/lib/base.class.php';require_once $root.'/admin/lib/admin.check.ajax.php';
header('Content-Type: application/json; charset=utf-8');
$db=DB::getInstance();$uploadDir=$root.'/uploads/works';
$files=\Security\UploadLifecycle::forTable($uploadDir,$db,'nb_works',\Security\WorksInput::IMAGES,['contents','price']);
$data=['success'=>false,'data'=>null,'message'=>''];
try{
    $method=strtoupper((string)($_REQUEST['_method']??$_SERVER['REQUEST_METHOD']));
    if(!in_array($method,['GET','POST','UPDATE','DELETE','BATCH_DELETE'],true))throw new RuntimeException('잘못된 요청입니다.');
    if(($_SERVER['REQUEST_METHOD']??'GET')!=='POST'&&$method!=='GET'){http_response_code(405);throw new RuntimeException('상태 변경은 POST 요청만 허용됩니다.');}
    if($method==='GET'){
        if(isset($_REQUEST['id'])&&$_REQUEST['id']!==''){
            $id=\Security\WorksInput::id($_REQUEST['id']);$stmt=$db->prepare('SELECT * FROM nb_works WHERE id=?');$stmt->execute([$id]);$data['data']=$stmt->fetchAll(PDO::FETCH_ASSOC);
            if(!$data['data'])throw new RuntimeException('공연 정보를 찾을 수 없습니다.');
        }else $data['data']=$db->query('SELECT * FROM nb_works')->fetchAll(PDO::FETCH_ASSOC);
        $data['success']=true;
    }elseif($method==='POST'||$method==='UPDATE'){
        $old=[];
        if($method==='UPDATE'){
            $id=\Security\WorksInput::id($_REQUEST['id']??null);$stmt=$db->prepare('SELECT * FROM nb_works WHERE id=?');$stmt->execute([$id]);$old=$stmt->fetch(PDO::FETCH_ASSOC);
            if(!$old)throw new RuntimeException('공연 정보를 찾을 수 없습니다.');
        }
        $fields=\Security\WorksInput::fields($_POST);
        foreach(\Security\WorksInput::IMAGES as $field){
            $saved=uploadImage($field,$uploadDir);
            if($saved!==null){$files->track($saved);$fields[$field]=$saved;if($old)$files->retire($old[$field]??null);}
            elseif($method==='POST')$fields[$field]=null;
        }
        if($method==='POST'){
            $keys=array_keys($fields);$stmt=$db->prepare('INSERT INTO nb_works (`'.implode('`,`',$keys).'`)VALUES('.implode(',',array_fill(0,count($keys),'?')).')');$stmt->execute(array_values($fields));
            $data['data']=['id'=>(int)$db->lastInsertId()];$data['message']='성공적으로 등록되었습니다.';
        }else{
            $fields['updated_at']=date('Y-m-d H:i:s');$keys=array_keys($fields);
            $stmt=$db->prepare('UPDATE nb_works SET '.implode(',',array_map(static fn($k)=>'`'.$k.'`=?',$keys)).' WHERE id=?');$stmt->execute(array_merge(array_values($fields),[$id]));$data['message']='업데이트에 성공했습니다.';
        }
        $files->commit();$data['success']=true;
    }else{
        if($method==='BATCH_DELETE'){
            $raw=json_decode((string)($_POST['ids']??'[]'),true);if(!is_array($raw)||!$raw||count($raw)>1000)throw new RuntimeException('삭제할 공연을 선택하세요.');
            $ids=array_values(array_unique(array_map([\Security\WorksInput::class,'id'],$raw)));
        }else $ids=[\Security\WorksInput::id($_REQUEST['id']??null)];
        $marks=implode(',',array_fill(0,count($ids),'?'));$stmt=$db->prepare('SELECT * FROM nb_works WHERE id IN('.$marks.')');$stmt->execute($ids);$rows=$stmt->fetchAll(PDO::FETCH_ASSOC);if(!$rows)throw new RuntimeException('공연 정보를 찾을 수 없습니다.');
        foreach($rows as $row)foreach(\Security\WorksInput::IMAGES as $field)$files->retire($row[$field]??null);
        $stmt=$db->prepare('DELETE FROM nb_works WHERE id IN('.$marks.')');$stmt->execute($ids);$files->commit();$data['success']=true;$data['message']='정상적으로 삭제되었습니다.';
    }
}catch(Throwable $e){$files->rollback();$data['message']=$e instanceof RuntimeException&&!($e instanceof PDOException)?$e->getMessage():'공연 정보를 저장하지 못했습니다. 다시 시도하세요.';if(!($e instanceof RuntimeException)||$e instanceof PDOException)error_log('Works operation failed: '.get_class($e));}
if(ob_get_level()>0)ob_clean();echo json_encode($data,JSON_UNESCAPED_UNICODE);exit;
