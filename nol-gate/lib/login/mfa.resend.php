<?php
require_once dirname(__DIR__, 3) . "/inc/lib/base.class.php";
require_once dirname(__DIR__) . "/mfa.php";

try {
    Mfa::resend();
} catch (Throwable $error) {
    $_SESSION['mfa_error'] = Mfa::noticeFor($error, Mfa::DELIVERY_ERROR);
}
header('Location: ../../mfa.php');
exit;
