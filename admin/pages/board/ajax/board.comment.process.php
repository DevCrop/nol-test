<?php
require_once '../../../../inc/lib/base.class.php';
require_once '../../../lib/admin.check.ajax.php';
$db = DB::getInstance();
$privacy = new \Security\BoardPrivacyService($db,$NO_SITE_UNIQUE_KEY);
$mode = (string)($_POST['mode'] ?? '');
$reply = static function(string $result,string $message): void {
    if(ob_get_level()>0)ob_clean();
    echo json_encode(['result'=>$result,'msg'=>$message],JSON_UNESCAPED_UNICODE);exit;
};
try {
    if(!in_array($mode,['save','delete'],true))throw new RuntimeException('지원하지 않는 요청입니다.');
    $id=filter_var($_POST['no']??0,FILTER_VALIDATE_INT);
    if(!$id||$id<1)throw new RuntimeException('대상을 확인해주세요.');
    $privacy->begin(true);
    if($mode==='save') {
        $board=$privacy->board($id);
        if(!is_scalar($_POST['comment']??null))throw new RuntimeException('댓글을 입력해주세요.');
        $comment=htmlspecialchars((string)$_POST['comment'],ENT_QUOTES,'UTF-8');
        $writer=(string)$NO_ADM_NAME;
        $stmt=$db->prepare("INSERT INTO nb_board_comment(sitekey,parent_no,user_no,write_name,regdate,contents,isAdmin) VALUES(?,?,-1,?,NOW(),?,'Y')");
        $stmt->execute([$NO_SITE_UNIQUE_KEY,$id,$writer,$comment]);
        $commentId=(int)$db->lastInsertId();
        if($commentId<1)throw new RuntimeException('댓글 등록을 확인할 수 없습니다.');
        $stmt=$db->prepare('UPDATE nb_board SET comment_cnt=comment_cnt+1 WHERE no=:no AND sitekey=:sitekey');
        $stmt->execute(['no'=>$id,'sitekey'=>$NO_SITE_UNIQUE_KEY]);
        $privacy->record('create',$id,$writer,'댓글 등록: 작성자 정보 입력 #'.$commentId);
    } else {
        $comment=$privacy->comment($id);
        $parent=(int)$comment['parent_no'];
        $privacy->board($parent);
        $stmt=$db->prepare('DELETE FROM nb_board_comment WHERE no=? AND sitekey=?');
        $stmt->execute([$id,$NO_SITE_UNIQUE_KEY]);
        if($stmt->rowCount()!==1)throw new RuntimeException('삭제할 댓글이 없습니다.');
        $stmt=$db->prepare('UPDATE nb_board SET comment_cnt=GREATEST(comment_cnt-1,0) WHERE no=:no AND sitekey=:sitekey');
        $stmt->execute(['no'=>$parent,'sitekey'=>$NO_SITE_UNIQUE_KEY]);
        $privacy->record('delete',$parent,(string)$comment['write_name'],'댓글 삭제: 작성자 정보 처리 #'.$id);
    }
    $privacy->commit();
    $reply('success',$mode==='save'?'정상적으로 등록되었습니다.':'정상적으로 삭제되었습니다.');
} catch(Throwable $error) {
    $privacy->rollback();
    $reply('fail',$error instanceof PDOException?'DB 처리 중 오류가 발생했습니다.':($error instanceof RuntimeException?$error->getMessage():blue_safe_error($error)));
}
