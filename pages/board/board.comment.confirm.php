<?php 
include_once $_SERVER['DOCUMENT_ROOT'] . '/inc/lib/base.class.php';

$board_no = $_GET['board_no'] ?? null;
$no = $_REQUEST['no'] ?? null;
$comment_no = $_REQUEST['comment_no'] ?? null;
$searchKeyword = $_REQUEST['searchKeyword'] ?? '';
$searchColumn = $_REQUEST['searchColumn'] ?? '';
$page = $_REQUEST['page'] ?? 1;
$returnUrl = $_REQUEST['returnUrl'] ?? '';

$board_title = $board_info[0]['title'] ?? '';
?>

<?php include_once $STATIC_ROOT . '/inc/layouts/head.php'; ?>
<!-- css, js -->

<?php include_once $STATIC_ROOT . '/inc/layouts/header.php'; ?>

<main>
    <?php include_once $STATIC_ROOT . '/inc/components/sub.visual.php'; ?>
    <?php include_once $STATIC_ROOT . '/inc/components/sub.nav.php'; ?>

    <!-- visual 영역 끝 -->
    <form id="frm" name="frm" method="post" action="">
        <input type="hidden" id="mode" name="mode" value="">
        <input type="hidden" id="board_no" name="board_no" value="<?= htmlspecialchars($board_no, ENT_QUOTES, 'UTF-8') ?>">
        <input type="hidden" id="no" name="no" value="<?= htmlspecialchars($no, ENT_QUOTES, 'UTF-8') ?>">
        <input type="hidden" name="comment_no" value="<?= htmlspecialchars($comment_no, ENT_QUOTES, 'UTF-8') ?>">
        <input type="hidden" name="searchKeyword" value="<?= htmlspecialchars($searchKeyword, ENT_QUOTES, 'UTF-8') ?>">
        <input type="hidden" name="searchColumn" value="<?= htmlspecialchars($searchColumn, ENT_QUOTES, 'UTF-8') ?>">
        <input type="hidden" name="page" value="<?= htmlspecialchars($page, ENT_QUOTES, 'UTF-8') ?>">
        <input type="hidden" id="returnUrl" name="returnUrl" value="<?= htmlspecialchars($returnUrl, ENT_QUOTES, 'UTF-8') ?>">

        <!-- BEGIN :: CONTENT -->
        <section class="no-section-md">
            <div class="no-container-md --tac">
                <h3 class="f-heading-5">
                    비밀번호 확인
                </h3>
                <p class="no-content-desc">
                    댓글 삭제 시에는 비밀번호가 필요합니다. <br>
                    <span>댓글 작성 시 입력한 비밀번호를 입력해 주세요.</span>
                </p>

                <div class="no-content-block">
                    <div class="no-form-control">
                        <label for="pwd">
                            <input type="password" name="pwd" id="pwd" placeholder="비밀번호를 입력해주세요." autofocus>
                        </label>
                    </div>
                </div>

                <div class="no-btn-pos no-content-block">
                    <a href="javascript:void(0);" class="no-btn-sz--action no-btn-light" onclick="history.back(-1);" title="취소">취소</a>
                    <a href="javascript:void(0);" class="no-btn-sz--action no-btn-primary" onclick="doCommentPasswordConfirm('<?= htmlspecialchars($_REQUEST['mode'] ?? '', ENT_QUOTES, 'UTF-8') ?>')" title="확인">확인</a>
                </div>
            </div>
        </section>
    </form>
</main>

<?php include_once $STATIC_ROOT . '/inc/layouts/footer.php'; ?>

<script type="text/javascript" src="<?= htmlspecialchars($NO_IS_SUBDIR, ENT_QUOTES, 'UTF-8') ?>/pages/board/js/board.js?v=<?= htmlspecialchars($STATIC_FRONT_JS_MODIFY_DATE, ENT_QUOTES, 'UTF-8') ?>"></script>

<?php include_once $STATIC_ROOT . '/inc/layouts/end.php'; ?>
