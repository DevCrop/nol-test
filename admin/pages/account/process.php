<?php
require_once '../../../inc/lib/base.class.php';
$actor = \Security\AdminAccount::requireSuper();
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') { http_response_code(405); exit('Method Not Allowed'); }

$return = static function (string $message, bool $error = false, string $path = '/admin/pages/account/index.php'): void {
    $_SESSION['account_flash'] = $message;
    $_SESSION['account_flash_error'] = $error;
    header('Location: ' . blue_admin_url($path)); exit;
};

$mode = (string) ($_POST['mode'] ?? '');
$targetNo = (int) ($_POST['no'] ?? 0);
$credentialMode = in_array($mode, ['create','reissue_temporary','reset_password'], true);
if ($credentialMode && strtolower((string)($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '')) !== 'xmlhttprequest') {
    http_response_code(400); exit('계정 화면에서 다시 요청하세요.');
}
try {
    if ($mode === 'create') {
        $credential = (new \Security\TemporaryAccountService())->create($_POST);
        $targetNo = $credential['id'];
        \Security\AuditLogger::record('create', 'account', $targetNo, (string) $_POST['uid'], ['role_code' => (string) ($_POST['role_code'] ?? 'admin')]);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success'=>true,'credential'=>$credential]); exit;
    }
    if ($targetNo < 1) throw new RuntimeException('대상 계정이 없습니다.');
    $target = \Security\AdminAccount::findByNo($targetNo);
    if ($mode === 'update') {
        if (!\Security\AdminAccount::updateManaged($targetNo, $_POST, (int) $actor['no'])) $return('변경된 내용이 없습니다.');
        \Security\AuditLogger::record('update', 'account', $targetNo, (string) ($target['uid'] ?? ''), ['role_code' => (string) ($_POST['role_code'] ?? ''), 'active_status' => (string) ($_POST['active_status'] ?? '')]);
        $return('관리자 계정을 수정했습니다.');
    }
    if ($mode === 'reset_password' || $mode === 'reissue_temporary') {
        if (!empty($_POST['password']) || !empty($_POST['password_confirm'])) throw new RuntimeException('비밀번호는 시스템에서 자동 발급합니다.');
        $credential = (new \Security\TemporaryAccountService())->reissue($targetNo, (int)$actor['no']);
        \Security\AuditLogger::record('update', 'account', $targetNo, (string) ($target['uid'] ?? ''), ['password_reset' => true]);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success'=>true,'credential'=>$credential]); exit;
    }
    if ($mode === 'unlock') {
        if (!\Security\AdminAccount::unlock($targetNo, (int) $actor['no'])) throw new RuntimeException('변경된 계정이 없습니다.');
        \Security\AuditLogger::record('update', 'account', $targetNo, (string) ($target['uid'] ?? ''), ['unlocked' => true]);
        $return('계정 잠금을 해제했습니다.');
    }
    if ($mode === 'delete') {
        if (!\Security\AdminAccount::deleteManaged($targetNo, (int) $actor['no'])) throw new RuntimeException('삭제된 계정이 없습니다.');
        \Security\AuditLogger::record('delete', 'account', $targetNo, (string) ($target['uid'] ?? ''));
        $return('관리자 계정을 삭제했습니다.');
    }
    throw new RuntimeException('지원하지 않는 작업입니다.');
} catch (Throwable $e) {
    if ($credentialMode) {
        http_response_code($e instanceof PDOException ? 500 : 400);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success'=>false,'message'=>$e instanceof RuntimeException && !($e instanceof PDOException) ? $e->getMessage() : '계정 작업을 처리할 수 없습니다.']); exit;
    }
    $path = in_array($mode, ['update', 'reset_password'], true) && $targetNo > 0 ? '/admin/pages/account/edit.php?no=' . $targetNo : '/admin/pages/account/index.php';
    $return($e instanceof RuntimeException && !($e instanceof PDOException) ? $e->getMessage() : '계정 작업을 처리할 수 없습니다.', true, $path);
}
