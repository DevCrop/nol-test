<?php $NO_ADMIN_RESOURCE_BASE=blue_admin_base().'/resource'; $NO_ADMIN_BASE=blue_admin_base(); $NO_ADMIN_PATH=dirname(__DIR__); $temporaryRequired = !empty($account['password_must_change']); ?>
<!doctype html>
<html lang="ko">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $temporaryRequired ? '임시 비밀번호 변경' : '비밀번호 변경' ?> | 사이트 관리 시스템</title>
    <link rel="stylesheet" href="/resource/vendor/boxicons/css/boxicons.min.css">
    <link rel="stylesheet" href="<?= htmlspecialchars($NO_ADMIN_RESOURCE_BASE, ENT_QUOTES, 'UTF-8') ?>/css/style.css?v=<?= filemtime($NO_ADMIN_PATH . '/resource/css/style.css') ?>">
    <link rel="stylesheet" href="<?=blue_admin_base()?>/resource/css/auth-parity.css?v=20261021">
    <script>window.NO_ADMIN_BASE = <?= json_encode($NO_ADMIN_BASE) ?>;</script>
    <script src="<?= htmlspecialchars($NO_ADMIN_RESOURCE_BASE, ENT_QUOTES, 'UTF-8') ?>/js/private-page.js?v=20261021" defer></script>
    <script src="<?= htmlspecialchars($NO_ADMIN_RESOURCE_BASE, ENT_QUOTES, 'UTF-8') ?>/js/required-password.js?v=20261021" defer></script>
</head>
<body class="no-sub-body no-login-page">
    <div class="no-sub-wrap">
        <header class="no-login-header"><h1 class="no-login-logo"><img src="/resource/images/admin/logo.png" alt="나인원랩스"></h1></header>
        <main id="no-main">
            <form id="required-password-form" method="post" action="./ajax/password.process.php" autocomplete="off">
                <input type="hidden" name="_csrf" value="<?=htmlspecialchars(\Security\Csrf::token(),ENT_QUOTES,'UTF-8')?>"><input type="hidden" name="mode" value="pwd.change">
                <div class="no-login-wrap"><div class="no-login-content">
                    <div class="no-login-top"><h2 class="no-login-title"><?= $temporaryRequired ? '임시 비밀번호 변경' : '비밀번호 변경' ?></h2><p class="no-login-desc"><?= $temporaryRequired ? '임시 비밀번호로 로그인하셨습니다.' : '비밀번호 사용 기간이 지났습니다.' ?><br>새 비밀번호로 변경한 뒤 다시 로그인해 주세요.</p></div>
                    <div class="no-mfa-timer"><i class="bx bx-check-shield" aria-hidden="true"></i><span>로그인 인증 완료 · 비밀번호 변경 필요</span></div>
                    <div class="no-login-control">
                        <?php if (!$temporaryRequired): ?>
                        <div class="no-auth-field"><label for="current_password">현재 비밀번호</label><div class="no-auth-input"><i class="bx bxs-lock" aria-hidden="true"></i><input id="current_password" name="current_password" type="password" required autocomplete="current-password" placeholder="현재 비밀번호"></div></div>
                        <?php endif; ?>
                        <div class="no-auth-field"><label for="new_password">새 비밀번호</label><div class="no-auth-input"><i class="bx bxs-lock" aria-hidden="true"></i><input id="new_password" name="new_password" type="password" required minlength="8" maxlength="72" autocomplete="new-password" placeholder="새 비밀번호" aria-describedby="required-password-hint"></div><p class="no-admin-acl__hint" id="required-password-hint">8자 이상, 대문자·소문자·숫자·특수문자 중 3종 이상</p></div>
                        <div class="no-auth-field"><label for="new_password_confirm">새 비밀번호 확인</label><div class="no-auth-input"><i class="bx bxs-lock" aria-hidden="true"></i><input id="new_password_confirm" name="new_password_confirm" type="password" required minlength="8" maxlength="72" autocomplete="new-password" placeholder="새 비밀번호 확인"></div></div>
                    </div>
                    <p id="required-password-error" class="no-admin-acl__hint" role="alert"></p>
                    <div class="no-login-bot"><div class="no-form-btn"><button type="submit" class="no-btn--submit">비밀번호 변경</button></div></div>
                </div></div>
            </form>
        </main>
        <footer class="no-login-footer"><span>Copyright © nineonelabs.co.kr All rights reserved.</span></footer>
    </div>
</body>
</html>
