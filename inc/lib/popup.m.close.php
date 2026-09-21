<?php
// `uid`가 설정되었는지 확인
if (isset($_GET['uid'])) {
    $id_list = explode(',', $_GET['uid']);

    if (empty($id_list)) {
        exit;
    }

    foreach ($id_list as $uid) {
        // 쿠키 적용 (도메인에 "www."가 포함되어 있으면 제거)
        setcookie(
            "AuthPopupClose_" . $uid,
            "Y",
            time() + 3600 * 24,
            "/",
            "." . str_replace("www.", "", $_SERVER['HTTP_HOST'])
        );
    }

    echo "<script>parent.document.querySelector('.no-popup').style.display = 'none';</script>";
} else {
    exit;
}
?>
