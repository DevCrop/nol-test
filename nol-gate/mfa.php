<?php
include_once dirname(__DIR__) . "/inc/lib/base.class.php";
require_once __DIR__ . "/lib/mfa.php";

if (!empty($_SESSION['no_adm_login_uid'])) {
    header('Location: ' . (new PasswordChangeGate())->landingAfterLogin());
    exit;
}

$pending = Mfa::pending();
$error = (string) ($_SESSION['mfa_error'] ?? '');
unset($_SESSION['mfa_error']);
if ($pending === null || (int) ($pending['expires'] ?? 0) <= time()) {
    Mfa::clear();
    if ($error !== '' || $pending !== null) {
        $_SESSION['admin_flash_error'] = $error !== '' ? $error : '인증번호가 만료되었습니다. 다시 로그인하세요.';
    }
    header('Location: ./index.php');
    exit;
}

$sent = Mfa::sent($pending);
$minutes = Mfa::ttlMinutes();
$codeLen = Mfa::codeLength();
$masked = Mfa::maskEmail((string) $pending['email']);
$expires = (int) $pending['expires'];
?>
<!DOCTYPE html>
<html lang="ko">

<head>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>2단계 인증 | 사이트 관리 시스템</title>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <link rel="stylesheet" href="/resource/vendor/boxicons/css/boxicons.min.css" />
  <link rel="stylesheet" href="/resource/vendor/fontawsome/css/all.css" />
  <link rel="stylesheet" href="./resource/css/style.css?v=<?= filemtime(__DIR__ . '/resource/css/style.css') ?>" />
  <script type="text/javascript" src="./resource/js/auth-ui.js?v=<?= filemtime(__DIR__ . '/resource/js/auth-ui.js') ?>" defer></script>
  <script type="text/javascript" src="./resource/js/mfa.js?v=<?= filemtime(__DIR__ . '/resource/js/mfa.js') ?>" defer></script>
  <script src="./resource/js/private-page.js?v=20261021" defer></script>
</head>

<body class="no-sub-body">
  <div class="no-sub-wrap">
    <header class="no-login-header">
      <h1 class="no-login-logo">
        <img src="/resource/images/admin/logo.png" alt="나인원랩스" />
      </h1>
    </header>

    <main id="no-main">
      <form method="POST" action="./lib/login/mfa.process.php" autocomplete="off" data-mfa-form data-mfa-sent="<?= $sent ? 'true' : 'false' ?>" data-loading-message="인증번호를 확인하고 있습니다.">
        <?= Csrf::field() ?>
        <div class="no-login-wrap">
          <div class="no-login-content">
            <div class="no-login-top">
              <h2 class="no-login-title">이메일 인증</h2>
              <p class="no-login-desc">
                <?php if ($sent): ?>
                등록된 <?= htmlspecialchars($masked, ENT_QUOTES, 'UTF-8') ?>로 보낸<br>
                <?= $codeLen ?>자리 인증번호를 <?= (int) $minutes ?>분 안에 입력하세요.
                <?php else: ?>
                등록된 <?= htmlspecialchars($masked, ENT_QUOTES, 'UTF-8') ?>로<br>
                인증번호를 보내지 못했습니다. 다시 요청해 주세요.
                <?php endif; ?>
              </p>
              <?php if ($error !== '' && ($sent || $error !== Mfa::DELIVERY_ERROR)): ?>
              <p class="no-login-error show" role="alert"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
              <?php endif; ?>
            </div>
            <div class="no-mfa-timer" data-mfa-expires="<?= $expires ?>" aria-live="polite">
              <i class="bx bx-time-five" aria-hidden="true"></i>
              <span>인증 유효시간</span>
              <strong data-mfa-countdown>--:--</strong>
            </div>
            <div class="no-login-control">
              <div class="no-auth-field">
                <label for="mfa_code">인증번호</label>
                <div class="no-auth-input">
                  <i class="bx bxs-key" aria-hidden="true"></i>
                  <input type="text" name="mfa_code" id="mfa_code" inputmode="numeric" pattern="[0-9]*" maxlength="<?= $codeLen ?>" placeholder="<?= $codeLen ?>자리 인증번호" autocomplete="one-time-code" required<?= $sent ? '' : ' disabled' ?> />
                </div>
              </div>
            </div>
            <div class="no-login-bot">
              <div class="no-form-btn">
                <button type="submit" class="no-btn--submit"<?= $sent ? '' : ' disabled' ?>>확인</button>
              </div>
              <small>
                <span><button type="submit" form="mfa-resend" class="no-btn--link">인증번호 다시 받기</button></span>
                <span><a href="./index.php">로그인으로</a></span>
              </small>
            </div>
          </div>
        </div>
      </form>
      <form id="mfa-resend" method="POST" action="./lib/login/mfa.resend.php" data-loading-message="인증번호를 다시 보내고 있습니다."><?= Csrf::field() ?></form>
    </main>

    <footer class="no-login-footer">
      <span> Copyright © nineonelabs.co.kr All rights reserved. </span>
    </footer>
  </div>
  <?php include __DIR__ . '/inc/auth.loading.php'; ?>
</body>
</html>
