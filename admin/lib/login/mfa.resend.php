<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/inc/lib/base.class.php';
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') { http_response_code(405); exit; }
try { \Security\Mfa::resend(); } catch (Throwable $e) { $_SESSION['mfa_error'] = $e instanceof RuntimeException ? $e->getMessage() : '인증 메일을 발송할 수 없습니다.'; }
header('Location: /admin/mfa.php'); exit;
