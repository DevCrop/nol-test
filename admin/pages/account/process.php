<?php
require_once '../../../inc/lib/base.class.php';
$actor = \Security\AdminAccount::requireSuper();
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') { http_response_code(405); exit('Method Not Allowed'); }

$return = static function (string $message, bool $error = false, string $path = '/admin/pages/account/index.php'): void {
    $_SESSION['account_flash'] = $message;
    $_SESSION['account_flash_error'] = $error;
    header('Location: ' . $path); exit;
};

$mode = (string) ($_POST['mode'] ?? '');
$targetNo = (int) ($_POST['no'] ?? 0);
try {
    if ($mode === 'create') {
        $targetNo = \Security\AdminAccount::createManaged($_POST);
        \Security\AuditLogger::record('create', 'account', $targetNo, (string) $_POST['uid'], ['role_code' => (string) ($_POST['role_code'] ?? 'admin')]);
        $return('관리자 계정을 등록했습니다.');
    }
    if ($targetNo < 1) throw new RuntimeException('대상 계정이 없습니다.');
    $target = \Security\AdminAccount::findByNo($targetNo);
    if ($mode === 'update') {
        if (!\Security\AdminAccount::updateManaged($targetNo, $_POST, (int) $actor['no'])) $return('변경된 내용이 없습니다.');
        \Security\AuditLogger::record('update', 'account', $targetNo, (string) ($target['uid'] ?? ''), ['role_code' => (string) ($_POST['role_code'] ?? ''), 'active_status' => (string) ($_POST['active_status'] ?? '')]);
        $return('관리자 계정을 수정했습니다.');
    }
    if ($mode === 'reset_password') {
        if (!\Security\AdminAccount::resetPassword($targetNo, (string) ($_POST['password'] ?? ''), (string) ($_POST['password_confirm'] ?? ''), (int) $actor['no'])) throw new RuntimeException('변경된 계정이 없습니다.');
        \Security\AuditLogger::record('update', 'account', $targetNo, (string) ($target['uid'] ?? ''), ['password_reset' => true]);
        $return('임시 비밀번호를 설정했습니다. 대상 계정은 다음 로그인에서 비밀번호를 변경해야 합니다.');
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
    $path = in_array($mode, ['update', 'reset_password'], true) && $targetNo > 0 ? '/admin/pages/account/edit.php?no=' . $targetNo : '/admin/pages/account/index.php';
    $return($e instanceof RuntimeException ? $e->getMessage() : '계정 작업을 처리할 수 없습니다.', true, $path);
}
