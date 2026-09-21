<?php
include_once $_SERVER['DOCUMENT_ROOT'] . "/inc/lib/base.class.php";
$depthnum = 1;
$pagenum = 5;

// board_no 유효성 검사
$board_no = $_REQUEST['board_no'] ?? null;
if (!$board_no) {
    error("잘못된 접근입니다", $NO_IS_SUBDIR . "/");
}

// 검색 조건 설정
$searchKeyword = $_REQUEST['searchKeyword'] ?? null;
$searchColumn = $_REQUEST['searchColumn'] ?? null;
$sdate = $_REQUEST['sdate'] ?? null;
$edate = $_REQUEST['edate'] ?? null;
$RtsearchKeyword = $_REQUEST['RtsearchKeyword'] ?? null;
$RtsearchColumn = $_REQUEST['RtsearchColumn'] ?? null;

if ($RtsearchKeyword) {
    $searchKeyword = base64_decode($RtsearchKeyword);
}
if ($RtsearchColumn) {
    $searchColumn = base64_decode($RtsearchColumn);
}

// 게시판 정보 및 권한 설정
$board_info = getBoardInfoByNo($board_no);
$isSecret = ($board_info[0]['secret_yn'] === "Y");
$role_info = getBoardRole($board_no, $NO_USR_LEV);

if ($role_info[0]['role_write'] === "N") {
    alert("접근 권한이 없습니다.");
}

// 게시판 제목 설정
$board_title = explode('(', $board_info[0]['title']);

// Depth 및 메뉴 설정
$depthNum = 2;
$lnbNum = 5;
$subNum = 1;
$detailNum = null;
$club = false;

switch ($board_no) {
    case 42:
        $lnbNum = 3;
        break;
    case 41:
        $lnbNum = 5;
        $subNum = 3;
        break;
    case 40:
        $lnbNum = 5;
        $subNum = 4;
        break;
    case 54:
        $lnbNum = 2;
        $subNum = 1;
        $club = true;
        break;
    case 55:
        $lnbNum = 2;
        $subNum = 2;
        $club = true;
        break;
    case 59:
        $detailNum = 2;
        break;
    case 53:
        $detailNum = 1;
        break;
    default:
        // 기본값 유지
        break;
}

// HTML 및 페이지 렌더링
?>

<!DOCTYPE HTML>
<html lang="ko">
<head>
<?php
include_once "../../inc/inc_titlemeta.php";
include_once "../../inc/inc_css.php";
include_once "../../inc/inc_script.php";
?>
<script type="text/javascript" src="<?= $NO_IS_SUBDIR ?>/pages/board/js/board.js?v=<?= $STATIC_FRONT_JS_MODIFY_DATE ?>"></script>
</head>
<body>
    <div class="no_wrap">
        <?php include_once "../../inc/header.php"; ?>

        <main>
            <?php include_once "../../inc/visual.php"; ?>
            <?php include_once "../../inc/lnb.php"; ?>

            <section class="no-sec-pd no-pt-8">
                <div class="no-form-container">
                    <div class="no-subject" data-aos="fade-up" data-aos-duration="1000">
                        <h2 class="no-subject__title"><?= htmlspecialchars($board_title[0]) ?></h2>
                    </div>

                    <form id="frm" name="frm" method="post" action="board.submit.php">
                        <input type="hidden" id="mode" name="mode" value="" />
                        <input type="hidden" id="is_secret" name="is_secret" value="<?= $board_info[0]['secret_yn'] ?>" />
                        <input type="hidden" id="board_no" name="board_no" value="<?= htmlspecialchars($board_no) ?>" />

                        <div class="no-input-wrap">
                            <div class="no-input-box no-input-box--wide">
                                <label for="title">제목</label>
                                <input type="text" name="title" id="title" required>
                            </div>
                            <div class="no-input-box no-input-box--wide">
                                <label for="write_name">성명</label>
                                <input type="text" name="write_name" id="write_name" value="<?= htmlspecialchars($NO_USR_NAME) ?>" required>
                            </div>
                            <div class="no-input-box no-input-box--wide">
                                <label for="contents">내용</label>
                                <textarea name="contents" id="contents" cols="30" rows="10" required></textarea>
                            </div>

                            <?php if ($NO_USR_NO && $board_info[0]['secret_yn'] === "Y"): ?>
                                <div class="no-input-box no-input-box--wide">
                                    <label for="secret_pwd">비밀번호 확인</label>
                                    <input type="password" name="secret_pwd" id="secret_pwd">
                                </div>
                            <?php endif; ?>

                            <?php $image_text = ($board_no == 40 || $board_no == 50 || $board_no == 63) ? '졸업 이미지' : '이미지'; ?>

                            <?php if ($board_info['skin'] == 'alm'): ?>
                                <div class="no-input-box no-input-box--wide">
                                    <label for="addFile1"><?= htmlspecialchars($image_text) ?></label>
                                    <div class="no-file-control">
                                        <input type="text" class="no-fake-file" id="fakeFileTxt1" placeholder="파일을 선택해주세요." readonly="" disabled="">
                                        <div class="no-file-box">
                                            <input type="file" id="addFile1" onchange="document.getElementById('fakeFileTxt1').value = this.value">
                                            <button type="button" class="no-btn no-btn--main">파일찾기</button>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <div class="no-input-box no-input-box--wide">
                                <label for="r_captcha">보안코드</label>
                                <div class="no-input-capt">
                                    <div class="no-input-capt__img">
                                        <img src="/inc/lib/captcha.n.php" alt="captcha" style="height: 30px">
                                    </div>
                                    <input type="text" name="r_captcha" id="r_captcha" maxlength="5" required>
                                </div>
                            </div>
                        </div>

                        <div class="no-confirm-btns">
                            <div class="no-confirm-btns__cancel">
                                <a href="javascript:void(0);" onclick="history.back();" title="취소">취소</a>
                            </div>
                            <div class="no-confirm-btns__post">
                                <a href="javascript:void(0);" onclick="doBoardSubmit(<?= $isSecret ?>)" title="확인">확인</a>
                            </div>
                        </div>
                    </form>
                </div>
            </section>
        </main>

        <?php include_once "../../inc/footer.php"; ?>
    </div>
</body>
</html>
