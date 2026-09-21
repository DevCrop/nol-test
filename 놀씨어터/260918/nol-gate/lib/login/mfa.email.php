<?php
require_once dirname(__DIR__, 3) . "/inc/lib/base.class.php";
require_once dirname(__DIR__) . "/mfa.php";

$fail = static function (string $message, string $to = '../../mfa.php'): void {
    echo "<script>alert(" . json_encode($message) . "); location.href=" . json_encode($to) . ";</script>";
    exit;
};

$toLogin = static function (string $message) use ($fail): void {
    $fail($message, strpos($message, '다시 로그인') !== false ? '../../index.php' : '../../mfa.php');
};

try {
    Mfa::bindEmail((string) ($_POST['mfa_email'] ?? ''));
} catch (Throwable $e) {
    error_log('[mfa] bind ' . $e->getMessage());
    $toLogin($e->getMessage());
}

header('Location: ../../mfa.php');
exit;
