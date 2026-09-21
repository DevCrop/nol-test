<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/inc/lib/base.class.php';

function blue_login_fail(string $message): void
{
    $_SESSION['admin_flash_error'] = $message;
    header('Location: /admin/index.php'); exit;
}

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') { http_response_code(405); exit; }
$uid = trim((string) ($_POST['uid'] ?? ''));
$password = (string) ($_POST['upwd'] ?? '');
$captcha = trim((string) ($_POST['r_captcha'] ?? ''));
if ($uid === '' || $password === '' || $captcha === '') blue_login_fail('아이디, 비밀번호, 보안코드를 모두 입력하세요.');
if (empty($_SESSION['captcha_secure']) || !hash_equals((string) $_SESSION['captcha_secure'], $captcha)) blue_login_fail('아이디 또는 비밀번호가 일치하지 않습니다.');

try {
    $row = \Security\AdminAccount::findByUid($uid);
    if (!$row) blue_login_fail('아이디 또는 비밀번호가 일치하지 않습니다.');
    \Security\AdminAccount::assertLoginAllowed($row);
    if (!\Security\AdminAccount::verifyPassword($row, $password)) {
        \Security\AdminAccount::failure($row);
        blue_login_fail('아이디 또는 비밀번호가 일치하지 않습니다.');
    }
    $row = \Security\AdminAccount::findByNo((int) $row['no']);
    if (!$row || !\Security\AdminAccount::verifyPassword($row, $password)) blue_login_fail('계정 정보가 변경되었습니다. 다시 로그인하세요.');
    unset($_SESSION['captcha_secure'], $_SESSION['mfa_pending']);
    if (MFA_ENABLED) { \Security\Mfa::begin($row); header('Location: /admin/mfa.php'); exit; }
    \Security\AdminAccount::establish($row);
    header('Location: /admin/pages/board/board.list.php'); exit;
} catch (Throwable $e) {
    blue_login_fail($e instanceof RuntimeException ? $e->getMessage() : '로그인을 처리할 수 없습니다.');
}
