<?php

// 데이터베이스 연결
$mysqli = new mysqli("localhost", "username", "password", "database");

if ($mysqli->connect_error) {
    die("Database connection failed: " . $mysqli->connect_error);
}

// 쿼리 작성 및 실행
$query = "SELECT * FROM nb_popup WHERE sitekey = '$NO_SITE_UNIQUE_KEY' AND p_loc = 'M' AND p_view = 'Y' 
          AND ((p_sdate <= CURDATE() AND p_edate >= CURDATE()) OR p_none_limit = 'Y') ORDER BY p_idx ASC, no DESC";
$result = $mysqli->query($query);

$arrPopup = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $arrPopup[] = $row;
    }
    $result->free();
}

$defaultNo = 9999999;

// 팝업 표시
foreach ($arrPopup as $v) {
    if ($_COOKIE["AuthPopupCloseM_" . $v['no']] !== "Y") {
        $_img = $UPLOAD_WDIR_POPUP . "/" . $v['p_img'];
        $_s = @getimagesize($_img);
        $app_div_name = "event_popup_div_" . $v['no'];
        $zindex = $defaultNo + ($v['no'] * 1);
?>
        <!-- BEGIN ::  POPUP 영역 -->
        <div class="no_m_ad_popup" id="<?= htmlspecialchars($app_div_name) ?>" style="z-index:<?= htmlspecialchars($zindex) ?>">
            <div class="no_m_ad_popup_pos">
                <div class="no_m_ad_popup_con">
                    <!-- event 발생할 시를 위한 a tag -->
                    <a href="<?= htmlspecialchars($v['p_link']) ?>" title="<?= htmlspecialchars($v['p_title']) ?>" target="<?= htmlspecialchars($v['p_target']) ?>" class="no_m_ad_popup_con_img">
                        <img src="<?= htmlspecialchars($_img) ?>" title="<?= htmlspecialchars($v['p_title']) ?>" />
                    </a>
                </div>
                <div class="no_m_ad_popup_con_btn">
                    <a href="javascript:void(0);" class="no_m_ad_btn_01" onclick="common_frame.location.href=('./inc/lib/popup.m.close.php?_mode=popup_close&uid=<?= htmlspecialchars($v['no']) ?>');">오늘 하루 보지 않기</a>
                    <a href="javascript:void(0);" onclick="popup_m_display('<?= htmlspecialchars($app_div_name) ?>')" class="no_m_ad_btn_02">닫기</a>
                </div>
            </div>
        </div>
        <!-- END ::  POPUP 영역 -->
<?php
    }
}

$mysqli->close();
?>
<script>
    function popup_m_display(divname) {
        document.getElementById(divname).style.display = 'none';
    }
</script>
<!-- 팝업 -->
