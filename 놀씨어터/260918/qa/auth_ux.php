<?php

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "cli only\n");
    exit(1);
}

$root = dirname(__DIR__);
$fail = 0;
$pass = 0;

function expect($ok, string $name): void
{
    global $fail, $pass;
    if ($ok) {
        $pass++;
        echo "PASS  {$name}\n";
        return;
    }
    $fail++;
    echo "FAIL  {$name}\n";
}

$mfa = file_get_contents($root . '/nol-gate/mfa.php');
$login = file_get_contents($root . '/nol-gate/index.php');
$loginJs = file_get_contents($root . '/nol-gate/resource/js/login.js');
$header = file_get_contents($root . '/nol-gate/inc/admin.header.php');
$app = file_get_contents($root . '/nol-gate/resource/js/app.js');
$sessionTimer = file_get_contents($root . '/nol-gate/resource/js/utils/SessionIdleTimer.js');
$authUi = file_get_contents($root . '/nol-gate/resource/js/auth-ui.js');
$loading = file_get_contents($root . '/nol-gate/inc/auth.loading.php');
$style = file_get_contents($root . '/nol-gate/resource/css/style.css');

expect(strpos($mfa, 'data-mfa-expires') !== false, 'MFA expiry is exposed to countdown UI');
expect(strpos($mfa, "filemtime(__DIR__ . '/resource/css/style.css')") !== false, 'MFA assets bypass stale browser cache');
expect(strpos($mfa, 'no-auth-field') !== false && strpos($mfa, 'no-auth-input') !== false, 'MFA fields use stable responsive layout');
expect(strpos($mfa, '인증번호를 이메일로 보내고 있습니다.') !== false, 'MFA send action has loading message');
expect(strpos($loginJs, "NoAuthLoading?.show('로그인 정보를 확인하고 있습니다.')") !== false, 'validated login action has loading message');
expect(substr_count($login, 'class="no-auth-field"') === 3, 'login and MFA share the same field component');
expect(strpos($login, 'no-form-control--login') === false, 'legacy login field component removed');
expect(strpos($header, 'data-session-idle') !== false && strpos($header, 'data-session-countdown') !== false, 'admin header renders idle timer');
expect(strpos($app, 'new SessionIdleTimer(sessionTimer).init()') !== false, 'idle timer starts on admin pages');
expect(strpos($sessionTimer, 'fetch(this.activityUrl') !== false, 'actual activity refreshes server session');
expect(strpos($sessionTimer, 'window.location.assign(this.logoutUrl)') !== false, 'zero countdown forces logout');
expect(strpos($authUi, 'form[data-loading-message]') !== false && strpos($loading, 'aria-busy="true"') !== false, 'auth loading is accessible and binds to network forms');
expect(strpos($style, '.no-global-loading') !== false && strpos($style, '.no-session-timer') !== false, 'loading and session timer styling exists');
expect(is_file($root . '/nol-gate/ajax/session.activity.php'), 'authenticated activity endpoint exists');

echo $fail === 0 ? "OK {$pass}\n" : "FAIL {$fail}\n";
exit($fail === 0 ? 0 : 1);
