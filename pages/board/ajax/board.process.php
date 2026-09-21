<?php

include_once "../../../inc/lib/base.class.php";
$connect = DB::getInstance();


$mode = $_POST['mode'] ?? '';

if ($mode == "board.password.confirm") {

    $no = $_POST['no'] ?? 0;
    $board_no = $_POST['board_no'] ?? 0;
    $pwd = $_POST['pwd'] ?? '';
    $hashed = hash("sha256", $pwd);

    $query = "SELECT a.no, a.board_no, a.secret_pwd FROM nb_board a WHERE a.no = ? AND a.secret_pwd = ?";
    $stmt = $connect->prepare($query);
    $stmt->bind_param("is", $no, $hashed);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();

    if (!$data) {
        echo json_encode(["result" => "fail", "msg" => "비밀번호가 일치하지 않습니다."]);
        exit;
    } else {
        $_SESSION['board_secret_confirmed_' . $no] = "Y";
        echo json_encode(["result" => "success", "msg" => "정상적으로 확인되었습니다."]);
        exit;
    }

} elseif ($mode == "board.write") {

    $board_no = xss_clean($_POST['board_no'] ?? '');
    $title = xss_clean($_POST['title'] ?? '');
    $write_name = xss_clean($_POST['write_name'] ?? '');
    $contents = xss_clean($_POST['contents'] ?? '');
    $r_captcha = xss_clean($_POST['r_captcha'] ?? '');
    $is_secret = xss_clean($_POST['is_secret'] ?? '');
    $secret_pwd = xss_clean($_POST['secret_pwd'] ?? '');
    $hashed = hash("sha256", $secret_pwd);

    if ($_SESSION['captcha_secure'] != $r_captcha) {
        echo json_encode(["result" => "fail", "msg" => "보안코드가 일치하지 않습니다. 정확히 입력해주세요"]);
        exit;
    }

    $query = "INSERT INTO nb_board (sitekey, board_no, user_no, title, contents, regdate, is_notice, is_secret, write_name, secret_pwd) VALUES (?, ?, ?, ?, ?, NOW(), ?, ?, ?, ?)";
    $stmt = $connect->prepare($query);
    $stmt->bind_param("siissssss", $NO_SITE_UNIQUE_KEY, $board_no, $NO_USR_NO, $title, $contents, $is_notice, $is_secret, $write_name, $hashed);

    if ($stmt->execute()) {
        echo json_encode(["result" => "success", "msg" => "정상적으로 등록되었습니다."]);
    } else {
        echo json_encode(["result" => "fail", "msg" => "처리중 문제가 발생하였습니다.[Error-DB]"]);
    }

} elseif ($mode == "board.edit") {

    $no = xss_clean($_POST['no'] ?? 0);
    $board_no = xss_clean($_POST['board_no'] ?? 0);
    $title = xss_clean($_POST['title'] ?? '');
    $write_name = xss_clean($_POST['write_name'] ?? '');
    $contents = xss_clean($_POST['contents'] ?? '');
    $r_captcha = xss_clean($_POST['r_captcha'] ?? '');

    if ($_SESSION['captcha_secure'] != $r_captcha) {
        echo json_encode(["result" => "fail", "msg" => "보안코드가 일치하지 않습니다. 정확히 입력해주세요"]);
        exit;
    }

    $query = "UPDATE nb_board SET title = ?, write_name = ?, contents = ? WHERE no = ? AND board_no = ?";
    $stmt = $connect->prepare($query);
    $stmt->bind_param("sssii", $title, $write_name, $contents, $no, $board_no);

    if ($stmt->execute()) {
        echo json_encode(["result" => "success", "msg" => "정상적으로 수정되었습니다."]);
    } else {
        echo json_encode(["result" => "fail", "msg" => "처리중 문제가 발생하였습니다.[Error-DB]"]);
    }

} elseif ($mode == "board.delete.user") {

    $no = xss_clean($_POST['no'] ?? 0);
    $board_no = xss_clean($_POST['board_no'] ?? 0);

    $query = "SELECT a.no, a.board_no, a.secret_pwd, a.is_secret FROM nb_board a WHERE a.no = ?";
    $stmt = $connect->prepare($query);
    $stmt->bind_param("i", $no);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();

    if (!$data) {
        echo json_encode(["result" => "fail", "msg" => "비정상적인 접근입니다."]);
        exit;
    }

    if ($data['is_secret'] == "Y" && ($_SESSION['board_secret_confirmed_' . $no] != "Y")) {
        echo json_encode(["result" => "fail", "msg" => "비밀번호 확인이 필요한 게시물입니다."]);
        exit;
    }

    $query = "DELETE FROM nb_board WHERE no = ? AND sitekey = ?";
    $stmt = $connect->prepare($query);
    $stmt->bind_param("is", $no, $NO_SITE_UNIQUE_KEY);

    if ($stmt->execute()) {
        echo json_encode(["result" => "success", "msg" => "정상적으로 삭제되었습니다."]);
    } else {
        echo json_encode(["result" => "fail", "msg" => "처리중 문제가 발생하였습니다.[Error-DB]"]);
    }

} elseif ($mode == "comment.save") {

    $no = xss_clean($_POST['no'] ?? 0);
    $board_no = xss_clean($_POST['board_no'] ?? 0);
    $write_name = xss_clean($_POST['write_name'] ?? '');
    $pwd = xss_clean($_POST['pwd'] ?? '');
    $comment = wrap_tag_iframe($_POST['comment_contents'] ?? '');
    $r_captcha = xss_clean($_POST['r_captcha'] ?? '');

    if ($_SESSION['captcha_secure'] != $r_captcha) {
        echo json_encode(["result" => "fail", "msg" => "보안코드가 일치하지 않습니다. 정확히 입력해주세요"]);
        exit;
    }

    $hashed = $pwd ? hash("sha256", $pwd) : "";

    $query = "INSERT INTO nb_board_comment (sitekey, parent_no, user_no, write_name, regdate, contents, pwd) VALUES (?, ?, ?, ?, NOW(), ?, ?)";
    $stmt = $connect->prepare($query);
    $stmt->bind_param("siisss", $NO_SITE_UNIQUE_KEY, $no, $NO_USR_NO, $NO_USR_NAME, $comment, $hashed);

    if ($stmt->execute()) {
        echo json_encode(["result" => "success", "msg" => "정상적으로 등록되었습니다."]);
    } else {
        echo json_encode(["result" => "fail", "msg" => "처리중 문제가 발생하였습니다.[Error-DB]"]);
    }

}
?>
