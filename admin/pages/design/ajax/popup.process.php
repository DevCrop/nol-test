<?php

include_once "../../../../inc/lib/base.class.php";
include_once "../../../lib/admin.check.ajax.php";

$mode = isset($_POST['mode']) ? $_POST['mode'] : null;
$db = DB::getInstance();

try {
    if ($mode === "save") {
        $p_view = isset($_POST['p_view']) ? $_POST['p_view'] : '';
        $p_none_limit = isset($_POST['p_none_limit']) ? $_POST['p_none_limit'] : '';
        $p_sdate = isset($_POST['p_sdate']) ? $_POST['p_sdate'] : null;
        $p_edate = isset($_POST['p_edate']) ? $_POST['p_edate'] : null;
        $p_title = isset($_POST['p_title']) ? $_POST['p_title'] : '';
        $p_target = isset($_POST['p_target']) && $_POST['p_target'] !== '' ? $_POST['p_target'] : '_self';
        $p_link = isset($_POST['p_link']) ? $_POST['p_link'] : '';
        $p_idx = isset($_POST['p_idx']) ? $_POST['p_idx'] : '';
        $p_loc = isset($_POST['p_loc']) ? $_POST['p_loc'] : '';
        $p_left = isset($_POST['p_left']) ? $_POST['p_left'] : null;
        $p_top = isset($_POST['p_top']) ? $_POST['p_top'] : null;

        if ($p_none_limit === "Y") {
            $p_sdate = null;
            $p_edate = null;
        }

        if ($p_loc === "M") {
            $p_left = null;
            $p_top = null;
        }

        // 파일 업로드
        $uploads_dir = isset($UPLOAD_DIR_POPUP) ? $UPLOAD_DIR_POPUP : "";
        $savedFile = null;
        if (!empty($_FILES['p_img']['name'])) {
            $uploadResult = imageUpload($uploads_dir, $_FILES['p_img']);
            $savedFile = isset($uploadResult['saved']) ? $uploadResult['saved'] : null;
        }

        // 최대 순서 조회
        $stmt = $db->prepare("SELECT IFNULL(MAX(p_idx) + 1, 1) AS maxcnt FROM nb_popup WHERE sitekey = :sitekey AND p_loc = :p_loc");
        $stmt->execute(['sitekey' => $NO_SITE_UNIQUE_KEY, 'p_loc' => $p_loc]);
        $maxcnt = $stmt->fetchColumn();

        // 데이터 삽입
        $query = "INSERT INTO nb_popup 
            (sitekey, p_title, p_img, p_target, p_link, p_view, p_left, p_top, p_idx, p_sdate, p_edate, p_rdate, p_none_limit, p_loc) 
            VALUES 
            (:sitekey, :p_title, :p_img, :p_target, :p_link, :p_view, :p_left, :p_top, :p_idx, :p_sdate, :p_edate, NOW(), :p_none_limit, :p_loc)";

        $stmt = $db->prepare($query);
        $result = $stmt->execute([
            'sitekey' => $NO_SITE_UNIQUE_KEY,
            'p_title' => $p_title,
            'p_img' => $savedFile,
            'p_target' => $p_target,
            'p_link' => $p_link,
            'p_view' => $p_view,
            'p_left' => $p_left,
            'p_top' => $p_top,
            'p_idx' => $maxcnt,
            'p_sdate' => $p_sdate,
            'p_edate' => $p_edate,
            'p_none_limit' => $p_none_limit,
            'p_loc' => $p_loc
        ]);

        ob_clean();
        echo json_encode(["result" => $result ? "success" : "fail", "msg" => $result ? "정상적으로 등록되었습니다." : "처리 중 문제가 발생하였습니다. [Error-DB]"]);
    } elseif ($mode === "edit") {
        $no = isset($_POST['no']) ? intval($_POST['no']) : 0;
        $p_view = isset($_POST['p_view']) ? $_POST['p_view'] : '';
        $p_none_limit = isset($_POST['p_none_limit']) ? $_POST['p_none_limit'] : '';
        $p_sdate = isset($_POST['p_sdate']) ? $_POST['p_sdate'] : null;
        $p_edate = isset($_POST['p_edate']) ? $_POST['p_edate'] : null;
        $p_title = isset($_POST['p_title']) ? $_POST['p_title'] : '';
        $p_target = isset($_POST['p_target']) ? $_POST['p_target'] : '_self';
        $p_link = isset($_POST['p_link']) ? $_POST['p_link'] : '';
        $p_idx = isset($_POST['p_idx']) ? $_POST['p_idx'] : '';
        $p_loc = isset($_POST['p_loc']) ? $_POST['p_loc'] : '';
        $p_left = isset($_POST['p_left']) ? $_POST['p_left'] : null;
        $p_top = isset($_POST['p_top']) ? $_POST['p_top'] : null;

        // 기존 이미지 확인
        $stmt = $db->prepare("SELECT p_img FROM nb_popup WHERE no = :no");
        $stmt->execute(['no' => $no]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$data) throw new Exception("정보를 찾을 수 없습니다");

        $p_img = isset($data['p_img']) ? $data['p_img'] : '';

        // 파일 업로드
        if (!empty($_FILES['p_img']['name'])) {
            $uploadResult = imageUpload($uploads_dir, $_FILES['p_img'], $p_img);
            $p_img = isset($uploadResult['saved']) ? $uploadResult['saved'] : '';
        }

        $query = "UPDATE nb_popup SET 
                    p_title = :p_title, p_target = :p_target, p_link = :p_link, p_view = :p_view, 
                    p_left = :p_left, p_top = :p_top, p_idx = :p_idx, p_sdate = :p_sdate, p_edate = :p_edate, 
                    p_none_limit = :p_none_limit, p_loc = :p_loc";
        if (!empty($p_img)) {
            $query .= ", p_img = :p_img";
        }
        $query .= " WHERE no = :no";

        $params = compact('p_title', 'p_target', 'p_link', 'p_view', 'p_left', 'p_top', 'p_idx', 'p_sdate', 'p_edate', 'p_none_limit', 'p_loc', 'no');
        if (!empty($p_img)) $params['p_img'] = $p_img;

        $stmt = $db->prepare($query);
        $result = $stmt->execute($params);

        ob_clean();
        echo json_encode(["result" => $result ? "success" : "fail", "msg" => $result ? "정상적으로 수정되었습니다." : "처리 중 문제가 발생하였습니다. [Error-DB]"]);
    } elseif ($mode === "delete") {
        $no = isset($_POST['no']) ? intval($_POST['no']) : 0;

        $stmt = $db->prepare("DELETE FROM nb_popup WHERE no = :no");
        $result = $stmt->execute(['no' => $no]);

        ob_clean();
        echo json_encode(["result" => $result ? "success" : "fail", "msg" => $result ? "정상적으로 삭제되었습니다." : "파일 삭제에 실패했습니다."]);
    }
} catch (Exception $e) {
    ob_clean();
    echo json_encode(["result" => "fail", "msg" => blue_safe_error($e)]);
    error_log("Error: " . blue_safe_error($e));
}

?>
