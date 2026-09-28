<?php
require_once dirname(__DIR__, 3) . "/inc/lib/base.class.php";
require_once dirname(__DIR__) . "/mfa.php";

$fail = static function (string $message, string $to = '../../mfa.php'): void {
    echo "<script>alert(" . json_encode($message) . "); location.href=" . json_encode($to) . ";</script>";
    exit;
};

try {
    Mfa::verify((string) ($_POST['mfa_code'] ?? ''));
} catch (Throwable $e) {
    $to = strpos($e->getMessage(), '다시 로그인') !== false ? '../../index.php' : '../../mfa.php';
    $fail($e->getMessage(), $to);
}

require_once dirname(__DIR__) . "/PasswordChangeGate.php";
header('Location: ' . (new PasswordChangeGate())->landingAfterLogin());
exit;
