<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/inc/lib/base.class.php';
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') { http_response_code(405); exit; }
try { $row = \Security\Mfa::verify((string) ($_POST['code'] ?? '')); \Security\AdminAccount::establish($row); header('Location: /admin/pages/board/board.list.php'); exit; }
catch (Throwable $e) { $_SESSION['mfa_error'] = $e instanceof RuntimeException ? $e->getMessage() : '인증을 처리할 수 없습니다.'; header('Location: /admin/mfa.php'); exit; }
