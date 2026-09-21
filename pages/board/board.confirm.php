<?php
include_once "../../inc/lib/base.class.php";

$board_no = $_GET['board_no'] ?? null;
?>

<!DOCTYPE HTML>
<html lang="en">
<head>
    <?php
    include_once "../../inc/inc_titlemeta.php";
    include_once "../../inc/inc_css.php";
    include_once "../../inc/inc_script.php";
    ?>
    <script type="text/javascript" src="<?= htmlspecialchars($NO_IS_SUBDIR) ?>/pages/board/js/board.js?v=<?= htmlspecialchars($STATIC_FRONT_JS_MODIFY_DATE) ?>"></script>
</head>
<body>
    <!-- 전체영역 -->
    <div class="no_wrap">
        <!-- BEGIN : HEADER -->
        <?php include_once "../../inc/header.php"; ?>
        <!-- END : HEADER -->

        <!-- BEGIN :: visual 영역 -->
        <?php include_once "../../inc/visual.php"; ?>
        <!-- END :: visual 영역 -->

        <form id="frm" name="frm" method="post" action="board.confirm.process.php">
            <input type="hidden" id="mode" name="mode" value="">
            <input type="hidden" name="board_no" value="<?= htmlspecialchars($_REQUEST['board_no'] ?? '') ?>">
            <input type="hidden" name="no" value="<?= htmlspecialchars($_REQUEST['no'] ?? '') ?>">
            <input type="hidden" name="searchKeyword" value="<?= htmlspecialchars($_REQUEST['searchKeyword'] ?? '') ?>">
            <input type="hidden" name="searchColumn" value="<?= htmlspecialchars($_REQUEST['searchColumn'] ?? '') ?>">
            <input type="hidden" name="page" value="<?= htmlspecialchars($_REQUEST['page'] ?? '') ?>">
            <input type="hidden" id="returnUrl" name="returnUrl" value="<?= htmlspecialchars($_REQUEST['returnUrl'] ?? '') ?>">

            <!-- BEGIN :: CONTENT -->
            <section class="no-sec-pd">
                <div class="no-form-container">
                    <h3 class="no-confirm-title">
                        Verify Password
                    </h3>
                    <p class="no-confirm-desc">
                        A password is required to view and edit/delete posts. <br>
                        <span>Please enter the password you entered when writing the article.</span>
                    </p>

                    <div class="no-input-wrap center">
                        <div class="no-input-box password">
                            <label for="pwd">Password</label>
                            <input type="password" id="pwd" name="pwd" placeholder="Password">
                        </div>
                    </div>

                    <div class="no-confirm-btns">
                        <div class="no-confirm-btns__cancel">
                            <a href="javascript:void(0);" onclick="history.back(-1);" title="cancel">Cancel</a>
                        </div>
                        <div class="no-confirm-btns__post">
                            <a href="javascript:void(0);" onclick="doPasswordConfirm('<?= htmlspecialchars($_REQUEST['mode'] ?? '') ?>')" title="confirm">Confirm</a>
                        </div>
                    </div>
                </div>
            </section>
            <!-- END :: CONTENT -->
        </form>

        <!-- BEGIN : FOOTER -->
        <?php include_once "../../inc/footer.php"; ?>
        <!-- END : FOOTER -->
    </div>
</body>
</html>
