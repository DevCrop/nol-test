<?php
require_once '../../../inc/lib/base.class.php';
$depthnum = 4; $pagenum = 1;
$message = (string) ($_SESSION['password_message'] ?? ''); unset($_SESSION['password_message']);
include_once '../../inc/admin.title.php'; include_once '../../inc/admin.css.php'; include_once '../../inc/admin.js.php';
?>

</head>
<body class="no-account-page"><div class="no-wrap">
<?php include_once '../../inc/admin.header.php'; ?>
<main class="no-app no-container"><?php include_once '../../inc/admin.drawer.php'; ?>
<section class="no-content">
<div class="no-toolbar"><div class="no-toolbar-container no-flex-stack"><div class="no-page-indicator"><h1 class="no-page-title">비밀번호 변경</h1><div class="no-breadcrumb-container"><ul class="no-breadcrumb-list"><li class="no-breadcrumb-item"><span>설정</span></li><li class="no-breadcrumb-item"><span>비밀번호 변경</span></li></ul></div></div></div></div>
<div class="no-toolbar-container">
<div class="no-card security-card"><div class="no-card-header no-card-header--detail"><h2 class="no-card-title">비밀번호 변경</h2></div><form method="post" action="/admin/pages/account/ajax/password.process.php" class="no-card-body no-admin-column no-admin-column--detail">
<p class="security-help">8~72바이트, 영문 대문자·소문자·숫자·특수문자 중 3가지 이상을 포함하세요.</p>
<?php if($message): ?><p class="security-message" role="alert"><?=htmlspecialchars($message, ENT_QUOTES, 'UTF-8')?></p><?php endif; ?>
<div class="no-admin-block"><h3 class="no-admin-title"><label for="current_password">현재 비밀번호</label></h3><div class="no-admin-content"><input class="no-input--detail" id="current_password" type="password" name="current_password" required autocomplete="current-password"></div></div>
<div class="no-admin-block"><h3 class="no-admin-title"><label for="new_password">새 비밀번호</label></h3><div class="no-admin-content"><input class="no-input--detail" id="new_password" type="password" name="new_password" required autocomplete="new-password" minlength="8" maxlength="72"></div></div>
<div class="no-admin-block"><h3 class="no-admin-title"><label for="new_password_confirm">새 비밀번호 확인</label></h3><div class="no-admin-content"><input class="no-input--detail" id="new_password_confirm" type="password" name="new_password_confirm" required autocomplete="new-password" minlength="8" maxlength="72"></div></div>
<div class="security-actions no-items-center center"><button class="no-btn no-btn--main no-btn--big" type="submit">확인</button></div></form></div>
</div></section></main><?php include_once '../../inc/admin.footer.php'; ?></div></body></html>
