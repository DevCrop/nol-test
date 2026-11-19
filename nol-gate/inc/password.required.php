<?php $temporaryRequired = !empty($pwdAccount['must_change_password']); ?>
<!doctype html>
<html lang="ko">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $temporaryRequired ? '임시 비밀번호 변경' : '비밀번호 변경' ?> | 사이트 관리 시스템</title>
    <link rel="stylesheet" href="<?= htmlspecialchars($NO_ADMIN_RESOURCE_BASE, ENT_QUOTES, 'UTF-8') ?>/vendor/boxicons/css/boxicons.min.css">
    <link rel="stylesheet" href="<?= htmlspecialchars($NO_ADMIN_RESOURCE_BASE, ENT_QUOTES, 'UTF-8') ?>/css/style.css?v=<?= filemtime($NO_ADMIN_PATH . '/resource/css/style.css') ?>">
    <link rel="stylesheet" href="<?= htmlspecialchars($NO_ADMIN_RESOURCE_BASE, ENT_QUOTES, 'UTF-8') ?>/css/admin-ui.css?v=<?= filemtime($NO_ADMIN_PATH . '/resource/css/admin-ui.css') ?>">
    <script>window.NO_ADMIN_BASE = <?= json_encode($NO_ADMIN_BASE) ?>;</script>
    <script src="<?= htmlspecialchars($NO_ADMIN_RESOURCE_BASE, ENT_QUOTES, 'UTF-8') ?>/js/private-page.js?v=20261021" defer></script>
    <script src="<?= htmlspecialchars($NO_ADMIN_RESOURCE_BASE, ENT_QUOTES, 'UTF-8') ?>/js/required-password.js?v=<?= filemtime($NO_ADMIN_PATH . '/resource/js/required-password.js') ?>" defer></script>
</head>
<body class="no-sub-body no-login-page no-password-required-page">
    <div class="no-sub-wrap">
        <header class="no-login-header"><h1 class="no-login-logo"><img src="<?= htmlspecialchars($NO_ADMIN_RESOURCE_BASE, ENT_QUOTES, 'UTF-8') ?>/images/admin/logo.png" alt="나인원랩스"></h1></header>
        <main id="no-main">
            <form id="required-password-form" method="post" action="./ajax/setting.process.php" autocomplete="off">
                <?= Csrf::field() ?><input type="hidden" name="mode" value="pwd.change">
                <div class="no-login-wrap"><div class="no-login-content">
                    <div class="no-login-top"><h2 class="no-login-title"><?= $temporaryRequired ? '임시 비밀번호 변경' : '비밀번호 변경' ?></h2><p class="no-login-desc"><?= $temporaryRequired ? '안전한 계정 사용을 위해 새 비밀번호를 설정해 주세요.' : '비밀번호 사용 기간이 지났습니다. 새 비밀번호를 설정해 주세요.' ?></p></div>
                    <div class="no-mfa-timer"><i class="bx bx-check-shield" aria-hidden="true"></i><span>로그인 인증 완료</span></div>
                    <div class="no-login-control">
                        <?php
                        $fields = $temporaryRequired ? [] : ['pwd_old' => '현재 비밀번호'];
                        $fields += ['pwd_new' => '새 비밀번호', 'pwd_new_confirm' => '새 비밀번호 확인'];
                        foreach ($fields as $id => $label):
                            $current = $id === 'pwd_old';
                        ?>
                        <div class="no-auth-field">
                            <div class="no-auth-field__head">
                                <label for="<?= $id ?>"><?= $label ?></label>
                                <button type="button" class="no-pwd-btn" data-password-toggle="<?= $id ?>"
                                        aria-controls="<?= $id ?>" aria-pressed="false" aria-label="<?= $label ?> 보기">
                                    <i class="bx bx-show" aria-hidden="true"></i><span class="no-pwd-text">보기</span>
                                </button>
                            </div>
                            <div class="no-auth-input">
                                <i class="bx bxs-lock" aria-hidden="true"></i>
                                <input id="<?= $id ?>" name="<?= $id ?>" type="password" required
                                       autocomplete="<?= $current ? 'current-password' : 'new-password' ?>"
                                       placeholder="<?= $label ?>" <?= $current ? '' : 'minlength="8" maxlength="72"' ?>
                                       <?= $id === 'pwd_new' ? 'aria-describedby="required-password-hint"' : '' ?>>
                            </div>
                            <?php if ($id === 'pwd_new'): ?>
                            <p class="no-auth-help" id="required-password-hint">8~72바이트, 영문 대·소문자·숫자·특수문자 중 3종 이상</p>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <p id="required-password-error" class="no-login-error" role="alert" hidden></p>
                    <div class="no-login-bot"><div class="no-form-btn"><button type="submit" class="no-btn--submit">비밀번호 변경</button></div><p class="no-auth-help no-password-return">변경 후 새 비밀번호로 다시 로그인해 주세요.</p></div>
                </div></div>
            </form>
        </main>
        <footer class="no-login-footer"><span>Copyright © nineonelabs.co.kr All rights reserved.</span></footer>
    </div>
</body>
</html>
