<?php include_once $_SERVER['DOCUMENT_ROOT'] . '/inc/lib/base.class.php'; ?>
<?php include_once $_SERVER['DOCUMENT_ROOT'] . '/inc/lib/site.info.php'; ?>
<?php $loginError = (string) ($_SESSION['admin_flash_error'] ?? ''); unset($_SESSION['admin_flash_error']); ?>



<!DOCTYPE html>
<html lang="ko">
<head>				
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>사이트 관리 시스템</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">

	<link rel="stylesheet" href="/resource/vendor/boxicons/css/boxicons.min.css" />
    <link rel="stylesheet" href="/resource/vendor/fontawsome/css/all.css" />
	<link rel="stylesheet" href="./resource/css/style.css"/>
	<link rel="stylesheet" href="./resource/css/security.css"/>
	<link rel="stylesheet" href="./resource/css/auth-parity.css?v=<?=filemtime(__DIR__ . '/resource/css/auth-parity.css')?>"/>

	<!-- admin only -->
	<script type="text/javascript" src="./resource/js/login.js?v=<?=filemtime(__DIR__ . '/resource/js/login.js')?>" defer></script>
</head>
	<body class="no-sub-body no-login-page">
    <div class="no-sub-wrap">
      <header class="no-login-header">
        <h1 class="no-login-logo">
          <img src="/resource/images/admin/logo.png" alt="나인원랩스" />
        </h1>
      </header>

      <main id="no-main">
        <form
          method="POST"
          action="./lib/login/login.process.php"
          id="login_form"
          name="login_form"
          autocomplete="off"
        >
          <input type="hidden" name="_csrf" value="<?=htmlspecialchars(\Security\Csrf::token(), ENT_QUOTES, 'UTF-8')?>">
          <div class="no-login-wrap">
            <div class="no-login-content">
              <div class="no-login-top">
                <h2 class="no-login-title">사이트 관리 시스템</h2>
                <p class="no-login-desc">
                  본 페이지는 인증이 필요한 관리자 페이지 입니다.
                </p>
              </div>

              <div class="no-login-control">
                <div class="no-login-error" <?=$loginError === '' ? '' : 'style="display:flex"'?> role="alert">
                  <i class="bx bx-info-circle"></i>
                  <span><?=htmlspecialchars($loginError, ENT_QUOTES, 'UTF-8')?></span>
                </div>

                <div class="no-auth-field">
                  <label for="uid">아이디</label>
                  <div class="no-auth-input">
                    <i class="bx bxs-user" aria-hidden="true"></i>
                    <input type="text" name="uid" id="uid" placeholder="아이디" autocomplete="username" aria-describedby="uid-error" />
                  </div>
                  <p class="no-invalid" id="uid-error">
                    <i class="bx bxs-info-circle"></i>
                    <span>아이디를 입력하세요.</span>
                  </p>
                </div>
                <!-- form-control -->
                <div class="no-auth-field">
                  <div class="no-auth-field__head">
                    <label for="upwd">비밀번호</label>

                    <button type="button" class="no-pwd-btn" aria-label="비밀번호 보기" aria-pressed="false">
                      <i class="fa-regular fa-eye no-pwd-icon"></i>
                      <span class="no-pwd-text">보기</span>
                    </button>
                  </div>
                  <div class="no-auth-input">
                  <i class="bx bxs-lock" aria-hidden="true"></i>
                  <input
                    type="password"
                    name="upwd"
                    id="upwd"
                    placeholder="비밀번호"
                    autocomplete="current-password"
                    aria-describedby="upwd-error"
                  />
                  </div>
                  <p class="no-invalid" id="upwd-error">
                    <i class="bx bxs-info-circle"></i>
                    <span>비밀번호를 입력하세요.</span>
                  </p>
                </div>
                <!-- form-control -->
                <div class="no-auth-field">
                  <label for="r_captcha">보안코드</label>

                  <div class="no-auth-captcha">
                    <div class="no-captcha-image">
                      <img src="../inc/lib/captcha.admin.n.php" alt="captcha" style="height:32px;" />
                    </div>
                    <div class="no-auth-input no-auth-input--captcha">
                    <input
                      type="text"
                      name="r_captcha"
                      id="r_captcha"
                      placeholder="보안코드"
                      autocomplete="off"
                      maxlength="5"
                      inputmode="numeric"
                      aria-describedby="captcha-error"
                      class="no-captcha-input"
                    />
                    </div>
                  </div>
                    <p class="no-invalid" id="captcha-error">
                      <i class="bx bxs-info-circle"></i>
                      <span>보안코드를 입력하세요.</span>
                    </p>
                </div>
                <!-- form-control -->
              </div>

              <div class="no-login-bot">
                <div class="no-form-btn">
                  <button type="submit" class="no-btn--submit">로그인</button>
                </div>

                <small>
                  <span>비밀번호 분실시</span>
                  <span>
                    <b>고객센터</b>
                    <a href="tel:02-951-9154">02-951-9154</a>
                  </span>
                </small>
              </div>
            </div>
            <!-- login content -->
          </div>
          <!-- login wrap -->
        </form>
      </main>

      <footer class="no-login-footer">
        <span> Copyright © nineonelabs.co.kr All rights reserved. </span>
      </footer>
    </div>

  </body>
</html>
