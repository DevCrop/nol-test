<?php
include_once "../../../inc/lib/base.class.php";
require_once dirname(__DIR__, 2) . "/lib/PasswordChangeGate.php";
require_once dirname(__DIR__, 2) . "/Model/AccountModel.php";

$pwdGate = new PasswordChangeGate();
$pwdAccount = AccountModel::findByNo((int) ($_SESSION['no_adm_login_no'] ?? 0));
$pwdState = $pwdGate->evaluate(
    $pwdAccount,
    true
);
$pwdForced = $pwdState['reason'] !== null;
if ($pwdForced) {
    include dirname(__DIR__, 2) . '/inc/password.required.php';
    exit;
}

include_once "../../inc/admin.title.php";
include_once "../../inc/admin.css.php";
include_once "../../inc/admin.js.php";
?>
</head>

<body class="no-account-form-page no-password-change-page">
    <div class="no-wrap">
        <!-- Header -->
        <?php
        include_once "../../inc/admin.header.php";
        ?>

        <!-- Main -->
        <main class="no-app no-container">
            <!-- Drawer -->
            <?php include_once "../../inc/admin.drawer.php"; ?>

            <section class="no-content">
                <div class="no-toolbar">
                    <div class="no-toolbar-container no-flex-stack">
                        <div class="no-page-indicator">
                            <h1 class="no-page-title">비밀번호 변경</h1>
                            <div class="no-breadcrumb-container">
                                <ul class="no-breadcrumb-list">
                                    <li class="no-breadcrumb-item">
                                        <span>설정</span>
                                    </li>
                                    <li class="no-breadcrumb-item">
                                        <span>비밀번호 변경</span>
                                    </li>
                                </ul>
                            </div>
                        </div>
                        <!-- page indicator -->
                    </div>
                </div>

                <div class="no-toolbar-container">
                    <div class="no-card security-card">
                        <div class="no-card-header no-card-header--detail">
                            <h2 class="no-card-title">비밀번호 변경</h2>
                        </div>
                        <form id="password-change-form" method="post" action="./ajax/setting.process.php"
                              class="no-card-body no-admin-column no-admin-column--detail">
                            <?= Csrf::field() ?>
                            <input type="hidden" name="mode" value="pwd.change">
                            <p class="security-help" id="password-policy">8~72바이트, 영문 대·소문자·숫자·특수문자 중 3종 이상</p>
                            <p id="required-password-error" class="security-message" role="alert" hidden></p>
                            <div class="no-admin-block">
                                <h3 class="no-admin-title"><label for="pwd_old">현재 비밀번호</label></h3>
                                <div class="no-admin-content">
                                    <input class="no-input--detail" type="password" id="pwd_old" name="pwd_old"
                                           required autocomplete="current-password">
                                </div>
                            </div>
                            <div class="no-admin-block">
                                <h3 class="no-admin-title"><label for="pwd_new">새 비밀번호</label></h3>
                                <div class="no-admin-content">
                                    <input class="no-input--detail" type="password" id="pwd_new" name="pwd_new"
                                           required autocomplete="new-password" minlength="8" maxlength="72" aria-describedby="password-policy">
                                </div>
                            </div>
                            <div class="no-admin-block">
                                <h3 class="no-admin-title"><label for="pwd_new_confirm">새 비밀번호 확인</label></h3>
                                <div class="no-admin-content">
                                    <input class="no-input--detail" type="password" id="pwd_new_confirm" name="pwd_new_confirm"
                                           required autocomplete="new-password" minlength="8" maxlength="72">
                                </div>
                            </div>
                            <div class="security-actions no-items-center center">
                                <button type="submit" class="no-btn no-btn--main no-btn--big">확인</button>
                            </div>
                        </form>
                    </div>
                </div>
            </section>
        </main>



        <!-- Footer -->
        <script src="<?= $NO_ADMIN_RESOURCE_BASE ?>/js/required-password.js?v=<?= filemtime($NO_ADMIN_PATH . '/resource/js/required-password.js') ?>" defer></script>
        <?php
        include_once "../../inc/admin.footer.php";
        ?>

    </div>
</body>

</html>
