<?php
    include_once "../../../../inc/lib/base.class.php";
    include_once "../../../lib/admin.check.ajax.php";

    $mode = $_POST['mode'];
    $connect = DB::getInstance();  // PDO 객체 생성

    if ($mode == "save") {
        try {
            $board_no = $_POST['board_no'];

            // nb_board_lev_manage에서 기존 데이터 삭제
            $query = "DELETE FROM nb_board_lev_manage WHERE sitekey = :sitekey AND board_no = :board_no";
            $stmt = $connect->prepare($query);
            $stmt->execute([':sitekey' => $NO_SITE_UNIQUE_KEY, ':board_no' => $board_no]);

            // 새 권한 데이터 삽입
            $i = 0;
            $result = true;

            foreach ($_POST['nb_auth_lev_no'] as $value) {
                $nb_auth_lev_no = $value;
                $role_list = !empty($_POST['role_list'][$i]) ? "Y" : "N";
                $role_write = !empty($_POST['role_write'][$i]) ? "Y" : "N";
                $role_view = !empty($_POST['role_view'][$i]) ? "Y" : "N";
                $role_edit = !empty($_POST['role_edit'][$i]) ? "Y" : "N";
                $role_delete = !empty($_POST['role_delete'][$i]) ? "Y" : "N";
                $role_comment = !empty($_POST['role_comment'][$i]) ? "Y" : "N";

                // 권한 데이터 삽입
                $query = "INSERT INTO nb_board_lev_manage 
                          (sitekey, board_no, lev_no, role_write, role_edit, role_view, role_list, role_delete, role_comment) 
                          VALUES 
                          (:sitekey, :board_no, :lev_no, :role_write, :role_edit, :role_view, :role_list, :role_delete, :role_comment)";
                $stmt = $connect->prepare($query);
                $result = $stmt->execute([
                    ':sitekey' => $NO_SITE_UNIQUE_KEY,
                    ':board_no' => $board_no,
                    ':lev_no' => $nb_auth_lev_no,
                    ':role_write' => $role_write,
                    ':role_edit' => $role_edit,
                    ':role_view' => $role_view,
                    ':role_list' => $role_list,
                    ':role_delete' => $role_delete,
                    ':role_comment' => $role_comment
                ]);

                $i++;
            }

            if ($result) {
                echo json_encode(["result" => "success", "msg" => "정상적으로 저장되었습니다."]);
            } else {
                echo json_encode(["result" => "fail", "msg" => "처리중 문제가 발생하였습니다.[Error-DB]"]);
            }
        } catch (Exception $e) {
            echo json_encode(["result" => "fail", "msg" => "데이터베이스 오류가 발생하였습니다: " . blue_safe_error($e)]);
        }
    }
?>
