<?php
require_once '../../../../inc/lib/base.class.php';
$current = (string) ($_POST['current_password'] ?? '');
$new = (string) ($_POST['new_password'] ?? '');
$confirm = (string) ($_POST['new_password_confirm'] ?? '');
$fail = function (string $message): void { $_SESSION['password_message'] = $message; header('Location: /admin/pages/account/password.php'); exit; };
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') { http_response_code(405); exit; }
try { \Security\AccountValidator::password($new, $confirm); } catch (RuntimeException $e) { $fail($e->getMessage()); }
$row = \Security\AdminAccount::findByNo((int) $_SESSION['no_adm_login_no']);
if (!$row || !\Security\AdminAccount::verifyPassword($row, $current)) $fail('현재 비밀번호가 올바르지 않습니다.');
if ($new === $current) $fail('기존 비밀번호와 다른 비밀번호를 입력하세요.');
if (stripos($new, (string) $row['uid']) !== false) $fail('비밀번호에 아이디를 포함할 수 없습니다.');
$stmt = DB::getInstance()->prepare('UPDATE nb_admin SET upwd = ?, password_changed_at = NOW(), password_must_change = 0 WHERE no = ?');
$stmt->execute([password_hash($new, PASSWORD_DEFAULT), (int) $row['no']]);
$_SESSION['no_adm_password_change_required'] = false;
\Security\AdminAccount::establish(\Security\AdminAccount::findByNo((int) $row['no']));
\Security\AuditLogger::record('update', 'account', (int) $row['no'], (string) $row['uid'], ['password_changed' => true]);
header('Location: /admin/pages/board/board.list.php'); exit;
