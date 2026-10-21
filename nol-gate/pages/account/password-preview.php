<?php
// UI only: no login/MFA routing or password mutation is connected.
if (!in_array($_SERVER['REQUEST_METHOD'] ?? 'GET', ['GET', 'HEAD'], true)) {
    http_response_code(405);
    header('Allow: GET, HEAD');
    exit;
}
include_once "../../../inc/lib/base.class.php";
$role->redirectIfCannotView();
header('X-Robots-Tag: noindex, nofollow');
header('Cache-Control: no-store');
$previewCssVersion = filemtime(dirname(__DIR__, 2) . '/resource/css/style.css');
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>임시 비밀번호 변경 | 사이트 관리 시스템</title>
    <link rel="stylesheet" href="<?= htmlspecialchars($NO_ADMIN_RESOURCE_BASE, ENT_QUOTES, 'UTF-8') ?>/vendor/boxicons/css/boxicons.min.css">
    <link rel="stylesheet" href="<?= htmlspecialchars($NO_ADMIN_RESOURCE_BASE, ENT_QUOTES, 'UTF-8') ?>/css/style.css?v=<?= (int) $previewCssVersion ?>">
</head>
<body class="no-sub-body">
    <div class="no-sub-wrap">
        <header class="no-login-header">
            <h1 class="no-login-logo"><img src="<?= htmlspecialchars($NO_ADMIN_RESOURCE_BASE, ENT_QUOTES, 'UTF-8') ?>/images/admin/logo.png" alt="나인원랩스"></h1>
        </header>
        <main id="no-main">
            <div class="no-login-wrap">
                <section class="no-login-content" aria-labelledby="password-preview-title">
                    <div class="no-login-top">
                        <h2 class="no-login-title" id="password-preview-title">임시 비밀번호 변경</h2>
                        <p class="no-login-desc">임시 비밀번호로 로그인하셨습니다.<br>관리자 이용을 위해 새 비밀번호로 변경해 주세요.</p>
                    </div>
                    <div class="no-mfa-timer">
                        <i class="bx bx-check-shield" aria-hidden="true"></i>
                        <span>로그인 · 이메일 인증 완료</span>
                    </div>
                    <div class="no-login-control">
                        <div class="no-auth-field">
                            <label for="preview-password">새 비밀번호</label>
                            <div class="no-auth-input">
                                <i class="bx bxs-lock" aria-hidden="true"></i>
                                <input type="password" id="preview-password" placeholder="새 비밀번호" autocomplete="off" aria-describedby="preview-password-hint">
                            </div>
                            <p class="no-admin-acl__hint" id="preview-password-hint">다른 서비스와 다른 비밀번호를 설정해 주세요.</p>
                        </div>
                        <div class="no-auth-field">
                            <label for="preview-password-confirm">새 비밀번호 확인</label>
                            <div class="no-auth-input">
                                <i class="bx bxs-lock" aria-hidden="true"></i>
                                <input type="password" id="preview-password-confirm" placeholder="새 비밀번호 확인" autocomplete="off" aria-describedby="preview-confirm-hint">
                            </div>
                            <p class="no-admin-acl__hint" id="preview-confirm-hint">새 비밀번호를 한 번 더 입력해 주세요.</p>
                        </div>
                    </div>
                    <div class="no-login-bot">
                        <div class="no-form-btn"><button type="button" class="no-btn--submit" aria-describedby="preview-only-hint" title="디자인 미리보기이며 실제 비밀번호는 변경되지 않습니다.">비밀번호 변경</button></div>
                        <small><span>변경 후에는 임시 비밀번호를 사용할 수 없습니다.</span></small>
                    </div>
                </section>
            </div>
        </main>
        <footer class="no-login-footer">
            <p class="no-admin-acl__hint" id="preview-only-hint">디자인 미리보기 · 실제 비밀번호는 변경되지 않습니다.</p>
            <span>Copyright © nineonelabs.co.kr All rights reserved.</span>
        </footer>
    </div>
</body>
</html>
