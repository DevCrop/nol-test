<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/inc/lib/base.class.php';
if (!is_array($_SESSION['mfa_pending'] ?? null)) { header('Location: /admin/index.php'); exit; }
$error = (string) ($_SESSION['mfa_error'] ?? ''); unset($_SESSION['mfa_error']);
$sent = !empty($_SESSION['mfa_pending']['code_hash']);
$csrf = htmlspecialchars(\Security\Csrf::token(), ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html><html lang="ko"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>2단계 인증 | 사이트 관리 시스템</title>
<link rel="stylesheet" href="/resource/vendor/boxicons/css/boxicons.min.css">
<link rel="stylesheet" href="/admin/resource/css/style.css">
<link rel="stylesheet" href="/admin/resource/css/security.css">
<link rel="stylesheet" href="/admin/resource/css/auth-parity.css?v=<?=filemtime(__DIR__.'/resource/css/auth-parity.css')?>">
<meta name="csrf-token" content="<?=$csrf?>"><script src="/admin/resource/js/security.js" defer></script>
</head><body class="no-sub-body"><div class="no-sub-wrap">
<header class="no-login-header"><h1 class="no-login-logo"><img src="/resource/images/admin/logo.png" alt="나인원랩스"></h1></header>
<main id="no-main"><div class="no-login-wrap"><div class="no-login-content">
<div class="no-login-top"><h2 class="no-login-title">이메일 인증</h2><p class="no-login-desc"><?php if ($sent): ?><?=htmlspecialchars(\Security\Mfa::maskedEmail(), ENT_QUOTES, 'UTF-8')?>로 보낸<br>6자리 인증번호를 입력하세요.<?php else: ?>계정에 등록된 이메일을 입력하세요.<br>해당 주소로 인증번호를 보내드립니다.<?php endif; ?></p></div>
<?php if ($error !== ''): ?><p class="no-login-error show" role="alert"><?=htmlspecialchars($error, ENT_QUOTES, 'UTF-8')?></p><?php endif; ?>
<?php if (!$sent): ?>
<form method="post" action="/admin/lib/login/mfa.email.php" autocomplete="off">
<input type="hidden" name="_csrf" value="<?=$csrf?>">
<div class="no-login-control"><div class="no-auth-field"><label for="mfa_email">이메일</label><div class="no-auth-input"><i class="bx bxs-envelope" aria-hidden="true"></i><input type="email" id="mfa_email" name="mfa_email" placeholder="name@example.com" required maxlength="190" autocomplete="email"></div></div></div>
<div class="no-login-bot"><div class="no-form-btn"><button type="submit" class="no-btn--submit">인증번호 받기</button></div><small><a href="/admin/index.php">로그인으로</a></small></div>
</form>
<?php else: ?>
<form method="post" action="/admin/lib/login/mfa.process.php" autocomplete="off">
<input type="hidden" name="_csrf" value="<?=$csrf?>">
<div class="no-mfa-timer" aria-live="polite">인증 유효시간 <strong id="mfa-countdown" data-seconds="<?=max(0,(int)$_SESSION['mfa_pending']['expires']-time())?>">--:--</strong></div>
<div class="no-login-control"><div class="no-auth-field"><label for="code">인증번호</label><div class="no-auth-input"><i class="bx bxs-key" aria-hidden="true"></i><input id="code" name="code" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" autocomplete="one-time-code" placeholder="6자리 인증번호" required></div></div></div>
<div class="no-login-bot"><div class="no-form-btn"><button class="no-btn--submit" type="submit">확인</button></div></div>
</form>
<form method="post" action="/admin/lib/login/mfa.resend.php" class="no-mfa-resend"><input type="hidden" name="_csrf" value="<?=$csrf?>"><button type="submit">인증번호 다시 받기</button><a href="/admin/index.php">로그인으로</a></form>
<?php endif; ?>
</div></div></main><footer class="no-login-footer"><span>Copyright © nineonelabs.co.kr All rights reserved.</span></footer></div>
<script>
(() => {
  const el = document.getElementById('mfa-countdown');
  if (!el) return;
  const deadline = Date.now() + Number(el.dataset.seconds) * 1000;
  const render = () => {
    const seconds = Math.max(0, Math.ceil((deadline - Date.now()) / 1000));
    el.textContent = String(Math.floor(seconds/60)).padStart(2,'0') + ':' + String(seconds%60).padStart(2,'0');
  };
  render(); setInterval(render,1000);
})();
</script></body></html>
