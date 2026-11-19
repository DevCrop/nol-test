<?php
require_once dirname(__DIR__, 3) . "/inc/lib/base.class.php";
require_once dirname(__DIR__) . "/mfa.php";

try {
    Mfa::verify((string) ($_POST['mfa_code'] ?? ''));
} catch (Throwable $error) {
    $_SESSION['mfa_error'] = Mfa::noticeFor($error, '인증번호를 확인할 수 없습니다. 다시 시도하세요.');
    header('Location: ../../mfa.php');
    exit;
}

require_once dirname(__DIR__) . "/PasswordChangeGate.php";
header('Location: ' . (new PasswordChangeGate())->landingAfterLogin());
exit;
