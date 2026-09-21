<?php

include_once "../../../../inc/lib/base.class.php";
include_once "../../../lib/admin.check.ajax.php";
$role->requireLogin();
$role->requireCanModify();

$mode = $_POST['mode'];
$db = DB::getInstance();

if ($mode == "save") {
    try {
        $no = $_POST['no'];
        $board_no = $_POST['board_no'];
		$comment = htmlspecialchars($_POST['comment'], ENT_QUOTES, 'UTF-8');

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

        // Update comment count
        $query = "UPDATE nb_board SET comment_cnt = comment_cnt + 1 WHERE no = :no";
        $stmt = $db->prepare($query);
        $stmt->execute(['no' => $no]);

        echo json_encode(["result" => "success", "msg" => "정상적으로 등록되었습니다."]);

    } catch (Exception $e) {
        echo json_encode(["result" => "fail", "msg" => ClientFault::message($e)]);
    }

} else if ($mode == "delete") {
    try {
        $no = $_REQUEST['no'];
        $board_no = $_REQUEST['board_no'];

        // Delete comment
        $query = "DELETE FROM nb_board_comment WHERE no = :no";
        $stmt = $db->prepare($query);
        $stmt->execute(['no' => $no]);

        // Update comment count
        $query = "UPDATE nb_board SET comment_cnt = comment_cnt - 1 WHERE no = :board_no";
        $stmt = $db->prepare($query);
        $stmt->execute(['board_no' => $board_no]);

        echo json_encode(["result" => "success", "msg" => "정상적으로 삭제되었습니다."]);

    } catch (Exception $e) {
        echo json_encode(["result" => "fail", "msg" => ClientFault::message($e)]);
    }
}
?>
