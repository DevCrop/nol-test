<?php
require_once dirname(__DIR__, 3) . "/inc/lib/base.class.php";
require_once dirname(__DIR__) . "/mfa.php";

$fail = static function (string $message, string $to = '../../mfa.php'): void {
    echo "<script>alert(" . json_encode($message) . "); location.href=" . json_encode($to) . ";</script>";
    exit;
};

try {
    Mfa::resend();
} catch (Throwable $e) {
    error_log('[mfa] resend ' . $e->getMessage());
    $to = strpos($e->getMessage(), '다시 로그인') !== false ? '../../index.php' : '../../mfa.php';
    $fail($e->getMessage(), $to);
}

header('Location: ../../mfa.php');
exit;
