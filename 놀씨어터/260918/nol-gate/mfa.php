<?php
include_once dirname(__DIR__) . "/inc/lib/base.class.php";
require_once __DIR__ . "/lib/mfa.php";

if (!empty($_SESSION['no_adm_login_uid'])) {
    header('Location: ./pages/board/board.list.php');
    exit;
}

$pending = Mfa::pending();
if ($pending === null) {
    header('Location: ./index.php');
    exit;
}

$sent = Mfa::sent($pending);
$minutes = Mfa::ttlMinutes();
$codeLen = Mfa::codeLength();
$masked = $sent ? Mfa::maskEmail((string) $pending['email']) : '';
$expires = $sent ? (int) ($pending['expires'] ?? 0) : 0;
?>
<!DOCTYPE html>
<html lang="ko">

<head>
  <title>2단계 인증 | 사이트 관리 시스템</title>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <link rel="stylesheet" href="/resource/vendor/boxicons/css/boxicons.min.css" />
  <link rel="stylesheet" href="/resource/vendor/fontawsome/css/all.css" />
  <link rel="stylesheet" href="./resource/css/style.css?v=<?= filemtime(__DIR__ . '/resource/css/style.css') ?>" />
  <script type="text/javascript" src="./resource/js/auth-ui.js?v=<?= filemtime(__DIR__ . '/resource/js/auth-ui.js') ?>" defer></script>
  <script type="text/javascript" src="./resource/js/mfa.js?v=<?= filemtime(__DIR__ . '/resource/js/mfa.js') ?>" defer></script>
</head>

<body class="no-sub-body">
  <div class="no-sub-wrap">
    <header class="no-login-header">
      <h1 class="no-login-logo">
        <img src="/resource/images/admin/logo.png" alt="나인원랩스" />
      </h1>
    </header>

    <main id="no-main">
      <?php if (!$sent): ?>
      <form method="POST" action="./lib/login/mfa.email.php" autocomplete="off" data-loading-message="인증번호를 이메일로 보내고 있습니다.">
        <div class="no-login-wrap">
          <div class="no-login-content">
            <div class="no-login-top">
              <h2 class="no-login-title">이메일 인증</h2>
              <p class="no-login-desc">올바른 이메일을 입력하세요. 메일을 받을 수 있는 주소여야 합니다.</p>
            </div>
            <div class="no-login-control">
              <div class="no-auth-field">
                <label for="mfa_email">이메일</label>
                <div class="no-auth-input">
                  <i class="bx bxs-envelope" aria-hidden="true"></i>
                  <input type="email" name="mfa_email" id="mfa_email" placeholder="name@example.com" required maxlength="254" />
                </div>
              </div>
            </div>
            <div class="no-login-bot">
              <div class="no-form-btn">
                <button type="submit" class="no-btn--submit">인증번호 받기</button>
              </div>
              <small>
                <span><a href="./index.php">로그인으로</a></span>
              </small>
            </div>
          </div>
        </div>
      </form>
      <?php else: ?>
      <form method="POST" action="./lib/login/mfa.process.php" autocomplete="off" data-mfa-form data-loading-message="인증번호를 확인하고 있습니다.">
        <div class="no-login-wrap">
          <div class="no-login-content">
            <div class="no-login-top">
              <h2 class="no-login-title">이메일 인증</h2>
              <p class="no-login-desc">
                <?= htmlspecialchars($masked, ENT_QUOTES, 'UTF-8') ?> 으로 보낸<br>
                <?= $codeLen ?>자리 인증번호를 <?= (int) $minutes ?>분 안에 입력하세요.
              </p>
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
                  <input type="text" name="mfa_code" id="mfa_code" inputmode="numeric" pattern="[0-9]*" maxlength="<?= $codeLen ?>" placeholder="<?= $codeLen ?>자리 인증번호" required />
                </div>
              </div>
            </div>
            <div class="no-login-bot">
              <div class="no-form-btn">
                <button type="submit" class="no-btn--submit">확인</button>
              </div>
              <small>
                <span><a href="./lib/login/mfa.resend.php" data-loading-message="인증번호를 다시 보내고 있습니다.">인증번호 다시 받기</a></span>
                <span><a href="./index.php">로그인으로</a></span>
              </small>
            </div>
          </div>
        </div>
      </form>
      <?php endif; ?>
    </main>

    <footer class="no-login-footer">
      <span> Copyright © nineonelabs.co.kr All rights reserved. </span>
    </footer>
  </div>
  <?php include __DIR__ . '/inc/auth.loading.php'; ?>
</body>
</html>
