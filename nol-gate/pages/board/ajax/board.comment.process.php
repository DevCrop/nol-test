<?php

include_once "../../../../inc/lib/base.class.php";
include_once "../../../lib/admin.check.ajax.php";
require_once "../../../lib/AuditLogger.php";
$role->requireLogin();
$role->requireCanModify();

$mode = $_POST['mode'];
$db = DB::getInstance();

if ($mode == "save") {
    try {
        $no = $_POST['no'];
        $board_no = $_POST['board_no'];
		$comment = htmlspecialchars($_POST['comment'], ENT_QUOTES, 'UTF-8');
        if (!MutationTargets::existing('nb_board', [(int)$no])) throw new RuntimeException('게시물을 찾을 수 없습니다.');
        $db->beginTransaction();

        // Insert comment
        $query = "INSERT INTO nb_board_comment (sitekey, parent_no, user_no, write_name, regdate, contents, isAdmin) 
                  VALUES (:sitekey, :parent_no, :user_no, :write_name, NOW(), :contents, 'Y')";
        $stmt = $db->prepare($query);
        $stmt->execute([
            'sitekey' => $NO_SITE_UNIQUE_KEY,
            'parent_no' => $no,
            'user_no' => -1,
            'write_name' => $NO_ADM_NAME,
            'contents' => $comment,
        ]);
        $commentNo = (int)$db->lastInsertId();

        // Update comment count
        $query = "UPDATE nb_board SET comment_cnt = comment_cnt + 1 WHERE no = :no";
        $stmt = $db->prepare($query);
        $stmt->execute(['no' => $no]);
        $db->commit();
        AuditLogger::record('create', 'board', $commentNo, '댓글', ['parent_no'=>(int)$no]);

        echo json_encode(["result" => "success", "msg" => "정상적으로 등록되었습니다."]);

    } catch (Exception $e) {
        if ($db->inTransaction()) $db->rollBack();
        echo json_encode(["result" => "fail", "msg" => ClientFault::message($e)]);
    }

} else if ($mode == "delete") {
    try {
        $no = (int)($_POST['no'] ?? 0);
        $db->beginTransaction();
        $parent = $db->prepare('SELECT parent_no FROM nb_board_comment WHERE no = ? FOR UPDATE');
        $parent->execute([$no]);
        $board_no = $parent->fetchColumn();
        if ($board_no === false) throw new RuntimeException('댓글을 찾을 수 없습니다.');

        // Delete comment
        $query = "DELETE FROM nb_board_comment WHERE no = :no";
        $stmt = $db->prepare($query);
        $stmt->execute(['no' => $no]);

        // Update comment count
        $query = "UPDATE nb_board SET comment_cnt = GREATEST(0, comment_cnt - 1) WHERE no = :board_no";
        $stmt = $db->prepare($query);
        $stmt->execute(['board_no' => $board_no]);
        $db->commit();
        AuditLogger::record('delete', 'board', $no, '댓글', ['parent_no'=>(int)$board_no]);

        echo json_encode(["result" => "success", "msg" => "정상적으로 삭제되었습니다."]);

    } catch (Exception $e) {
        if ($db->inTransaction()) $db->rollBack();
        echo json_encode(["result" => "fail", "msg" => ClientFault::message($e)]);
    }
}
?>
