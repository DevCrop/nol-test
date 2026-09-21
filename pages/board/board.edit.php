<?php
include_once "../../inc/lib/base.class.php";

$depthnum = 1;
$pagenum = 5;

$no = $_REQUEST['no'] ?? null;
$board_no = $_REQUEST['board_no'] ?? null;

if (!$board_no) {
    error("잘못된 접근입니다", $NO_IS_SUBDIR . "/");
}

$searchKeyword = $_REQUEST['searchKeyword'] ?? '';
$searchColumn = $_REQUEST['searchColumn'] ?? '';
$sdate = $_REQUEST['sdate'] ?? '';
$edate = $_REQUEST['edate'] ?? '';

// 상세페이지 리턴 처리
$RtsearchKeyword = $_REQUEST['RtsearchKeyword'] ?? '';
$RtsearchColumn = $_REQUEST['RtsearchColumn'] ?? '';

if ($RtsearchKeyword) {
    $searchKeyword = base64_decode($RtsearchKeyword);
}

if ($RtsearchColumn) {
    $searchColumn = base64_decode($RtsearchColumn);
}

try {
    // Obtain PDO instance
    $db = DB::getInstance();

    // 데이터베이스에서 게시물 정보 가져오기
    $query = "SELECT 
                a.no, a.board_no, a.user_no, a.category_no, a.comment_cnt, a.title, a.contents, a.regdate, a.read_cnt, 
                a.thumb_image, a.is_admin_writed, a.is_notice, a.is_secret, a.secret_pwd, a.write_name, a.isFile,
                file_attach_1, file_attach_origin_1, file_attach_2, file_attach_origin_2, 
                file_attach_3, file_attach_origin_3, file_attach_4, file_attach_origin_4, 
                file_attach_5, file_attach_origin_5 
              FROM nb_board a
              WHERE a.no = :no";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':no', $no, PDO::PARAM_INT);
    $stmt->execute();
    $data = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$data) {
        error("정보를 찾을 수 없습니다");
    }

} catch (PDOException $e) {
    error("데이터를 불러오는 중 오류가 발생했습니다: " . blue_safe_error($e));
    exit;
}

// 비밀글 확인
if ($data['is_secret'] === "Y" && $_SESSION['board_secret_confirmed_' . $no] !== "Y") {
    error("비밀번호 확인이 필요한 게시물입니다.");
}

// Board and Role Information
$board_info = getBoardInfoByNo($board_no);
$isSecret = ($board_info[0]['secret_yn'] === "Y");
$role_info = getBoardRole($board_no, $NO_USR_LEV);

if ($role_info[0]['role_edit'] === "N") {
    alert("접근 권한이 없습니다.");
}

// 타이틀과 메뉴 항목 설정
$board_title = explode('(', $board_info[0]['title']);

// 메뉴 설정
$depthNum = 1;
$lnbNum = $board_no == 42 ? 3 : ($board_no == 41 ? 5 : null);
$subNum = $board_no == 41 ? 3 : ($board_no == 40 ? 4 : null);

// 특수한 경우 설정
if ($board_no >= 44 && $board_no <= 53) {
    switch ($board_no) {
        case 44:
            $depthNum = 4;
            $lnbNum = 5;
            $subNum = 1;
            break;
        case 45:
            $depthNum = 4;
            $lnbNum = 3;
            $subNum = 1;
            break;
        case 43:
            $depthNum = 2;
            $lnbNum = 4;
            break;
        case 50:
            $depthNum = 4;
            $lnbNum = 4;
            break;
        case 47:
            $depthNum = 3;
            $lnbNum = 3;
            break;
        case 46:
            $depthNum = 4;
            $lnbNum = 4;
            break;
        case 49:
            $depthNum = 4;
            $lnbNum = 5;
            $subNum = 4;
            break;
        case 53:
            $depthNum = 2;
            $lnbNum = 5;
            $subNum = 1;
            $detailNum = 1;
            break;
    }
}
?>

<!DOCTYPE HTML>
<html lang="ko">
<head>
    <?php
    include_once "../../inc/inc_titlemeta.php";
    include_once "../../inc/inc_css.php";
    include_once "../../inc/inc_script.php";
    ?>
    <script type="text/javascript" src="<?= htmlspecialchars($NO_IS_SUBDIR, ENT_QUOTES, 'UTF-8') ?>/pages/board/js/board.js?v=<?= htmlspecialchars($STATIC_FRONT_JS_MODIFY_DATE, ENT_QUOTES, 'UTF-8') ?>"></script>
</head>
<body>
<div class="no_wrap">
    <?php include_once "../../inc/header.php"; ?>

    <main>
        <?php include_once "../../inc/visual.php"; ?>
        <?php include_once "../../inc/lnb.php"; ?>

        <section class="no-sec-pd">
            <div class="no-form-container">
                <div class="no-subject" data-aos="fade-up" data-aos-duration="1000">
                    <h2 class="no-subject__title"><?= htmlspecialchars($board_title[0], ENT_QUOTES, 'UTF-8') ?></h2>
                </div>

                <form id="frm" name="frm" method="post">
                    <input type="hidden" id="mode" name="mode" value="" />
                    <input type="hidden" id="is_secret" name="is_secret" value="<?= htmlspecialchars($board_info[0]['secret_yn'], ENT_QUOTES, 'UTF-8') ?>" />
                    <input type="hidden" id="board_no" name="board_no" value="<?= htmlspecialchars($board_no, ENT_QUOTES, 'UTF-8') ?>" />
                    <input type="hidden" id="no" name="no" value="<?= htmlspecialchars($data['no'], ENT_QUOTES, 'UTF-8') ?>" />

                    <div class="no-input-wrap">
                        <div class="no-input-box no-input-box--wide">
                            <label for="title">제목</label>
                            <input type="text" name="title" id="title" value="<?= htmlspecialchars($data['title'], ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                        <div class="no-input-box no-input-box--wide">
                            <label for="write_name">성명</label>
                            <input type="text" name="write_name" id="write_name" value="<?= htmlspecialchars($data['write_name'], ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                        <div class="no-input-box no-input-box--wide">
                            <label for="contents">내용</label>
                            <textarea name="contents" id="contents" cols="30" rows="10"><?= htmlspecialchars($data['contents'], ENT_QUOTES, 'UTF-8') ?></textarea>
                        </div>
                        <div class="no-input-box">
                            <label for="r_captcha">보안코드</label>
                            <div class="no-input-capt">
                                <div class="no-input-capt__img">
                                    <img src="/inc/lib/captcha.n.php" alt="captcha" style="height: 30px">
                                </div>
                                <input type="text" name="r_captcha" id="r_captcha" maxlength="5">
                            </div>
                        </div>
                    </div>

                    <div class="no-confirm-btns">
                        <div class="no-confirm-btns__cancel">
                            <a href="javascript:void(0);" onClick="location.href='./board.list.php?board_no=<?= htmlspecialchars($board_no, ENT_QUOTES, 'UTF-8') ?>'" title="취소">취소</a>
                        </div>
                        <div class="no-confirm-btns__post">
                            <a href="javascript:void(0);" onClick="doBoardEditSubmit(<?= $isSecret ?>)" title="확인">확인</a>
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
