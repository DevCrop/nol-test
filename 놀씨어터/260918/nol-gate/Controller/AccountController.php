<?php

require_once "../../inc/lib/base.class.php";
require_once "../Model/AccountModel.php";
require_once "../Model/AclModel.php";
require_once "../lib/AccountValidator.php";
require_once "../lib/AclValidator.php";
require_once "../lib/AuthSession.php";
require_once "../lib/AccountIdleLock.php";
require_once "../lib/PasswordChangeGate.php";
require_once "../lib/AuditLogger.php";

$role->requireLogin();
if (!$role->isSuper()) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => '권한이 없습니다.']);
    exit;
}

header('Content-Type: application/json');

$currentAdminNo = (int)($_SESSION['no_adm_login_no'] ?? 0);

$denyDelete = static function (int $id) use ($currentAdminNo): ?string {
    if ($id === $currentAdminNo) {
        return '본인 계정은 삭제할 수 없습니다.';
    }
    $roleId = AccountModel::getRoleId($id);
    if ($roleId === 1 && AccountModel::countByRole(1) <= 1) {
        return '마지막 최고 관리자는 삭제할 수 없습니다.';
    }
    return null;
};

$resolveRoleId = static function (array $input) use (&$admin_roles): int {
    $roleId = isset($input['role_id']) ? (int) $input['role_id'] : 2;
    if ($roleId !== 1 && $roleId !== 2) {
        $roleId = 2;
    }
    if (!isset($admin_roles[$roleId])) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => '유효하지 않은 권한입니다.'
        ]);
        exit;
    }
    return $roleId;
};

try {
    $input = $_POST;
    $mode = $input['mode'] ?? '';

    if ($mode === 'save') {
        $validator = AccountValidator::create($input);
        if ($validator->fails()) {
            echo json_encode([
                'success' => false,
                'message' => implode("\n", $validator->errors())
            ]);
            exit;
        }
        $clean = $validator->values();
        $input['uid'] = $clean['uid'];
        $input['uname'] = $clean['uname'];
        $input['email'] = $clean['email'];
        $input['phone'] = $clean['phone'];
        $input['upwd'] = $clean['upwd'];

        $roleId = $resolveRoleId($input);
        try {
            $grants = AclValidator::assert($roleId, $input);
        } catch (RuntimeException $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            exit;
        }

        $input['role_id'] = $roleId;
        $limit = Role::getRoleLimit($roleId);
        if ($limit !== null && AccountModel::countByRole($roleId) >= $limit) {
            echo json_encode([
                'success' => false,
                'message' => Role::roleLimitMessage($roleId)
            ]);
            exit;
        }

        $input['created_at'] = date('Y-m-d H:i:s');
        $input['upwd'] = password_hash($input['upwd'], PASSWORD_DEFAULT);
        unset($input['upwd_confirm']);

        $id = AccountModel::insert($input);
        if ((int) $roleId !== 1) {
            AclModel::replaceAll((int) $id, $grants);
        } else {
            AclModel::deleteByAdmin((int) $id);
        }
        AuditLogger::ifOk($id, 'create', 'account', $id, (string) $input['uid'], [
            'role_id' => $roleId,
            'uname' => $input['uname'],
            'grants' => $grants,
        ]);

        echo json_encode([
            'success' => true,
            'message' => '계정을 등록했습니다.',
            'id' => $id
        ]);
        exit;
    }

    if ($mode === 'delete') {
        $id = (int) ($input['id'] ?? 0);
        if ($id < 1) {
            echo json_encode([
                'success' => false,
                'message' => '삭제할 ID가 없습니다.'
            ]);
            exit;
        }
        $deny = $denyDelete($id);
        if ($deny !== null) {
            echo json_encode(['success' => false, 'message' => $deny]);
            exit;
        }

        $target = AccountModel::findByNo($id);
        $deleted = AccountModel::delete($id);
        if ($deleted) {
            AclModel::deleteByAdmin((int) $id);
            AuditLogger::record('delete', 'account', $id, (string) ($target['uid'] ?? $id));
        }

        echo json_encode([
            'success' => $deleted,
            'message' => $deleted ? '계정을 삭제했습니다.' : '계정 삭제에 실패했습니다.'
        ]);
        exit;
    }

    if ($mode === 'update') {
        $id = $input['no'] ?? null;

        if (!$id) {
            echo json_encode([
                'success' => false,
                'message' => '수정할 ID가 없습니다.'
            ]);
            exit;
        }

        $validator = AccountValidator::update($input, (int) $id);
        if ($validator->fails()) {
            echo json_encode([
                'success' => false,
                'message' => implode("\n", $validator->errors())
            ]);
            exit;
        }
        $clean = $validator->values();
        $input['uid'] = $clean['uid'];
        $input['uname'] = $clean['uname'];
        $input['email'] = $clean['email'];
        $input['phone'] = $clean['phone'];
        if (isset($clean['upwd'])) {
            $input['upwd'] = password_hash($clean['upwd'], PASSWORD_DEFAULT);
        } else {
            unset($input['upwd']);
        }
        unset($input['upwd_confirm']);

        $newRoleId = $resolveRoleId($input);
        $currentRoleId = AccountModel::getRoleId((int)$id);
        if ($currentRoleId === null) {
            echo json_encode([
                'success' => false,
                'message' => '계정을 찾을 수 없습니다.'
            ]);
            exit;
        }

        if ($currentAdminNo === (int) $id && ($input['active_status'] ?? '') === 'N') {
            echo json_encode([
                'success' => false,
                'message' => '본인 계정은 비활성화할 수 없습니다.'
            ]);
            exit;
        }

        if ($currentRoleId === 1 && $newRoleId !== 1 && AccountModel::countByRole(1) <= 1) {
            echo json_encode([
                'success' => false,
                'message' => '마지막 최고 관리자는 일반으로 내릴 수 없습니다. 다른 최고 관리자를 먼저 두세요.'
            ]);
            exit;
        }

        if (!$role->canEdit() && $currentRoleId !== $newRoleId) {
            http_response_code(403);
            echo json_encode([
                'success' => false,
                'message' => '권한이 없습니다.'
            ]);
            exit;
        }

        $input['role_id'] = $newRoleId;
        try {
            $grants = AclValidator::assert((int) $newRoleId, $input);
        } catch (RuntimeException $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            exit;
        }
        if ($currentRoleId !== $newRoleId) {
            $limit = Role::getRoleLimit($newRoleId);
            if ($limit !== null && AccountModel::countByRole($newRoleId) >= $limit) {
                echo json_encode([
                    'success' => false,
                    'message' => Role::roleLimitMessage($newRoleId)
                ]);
                exit;
            }
        }

        $updated = AccountModel::update($id, $input);
        if ($updated) {
            if (isset($input['upwd'])) {
                $gate = new PasswordChangeGate();
                if ($currentAdminNo === (int) $id) {
                    $gate->complete((int) $id);
                } else {
                    $gate->markResetByAdmin((int) $id, $currentAdminNo);
                }
            }
            if ((int) $newRoleId === 1) {
                AclModel::deleteByAdmin((int) $id);
            } else {
                AclModel::replaceAll((int) $id, $grants);
            }
            if ($currentAdminNo === (int) $id) {
                global $admin_roles;
                AuthSession::grantAdmin([
                    'no' => (int) $id,
                    'uid' => $input['uid'],
                    'uname' => $input['uname'],
                    'role_id' => $newRoleId,
                ], (string) ($admin_roles[$newRoleId]['code'] ?? ''));
            }
            AuditLogger::record('update', 'account', $id, (string) $input['uid'], [
                'role_id' => $newRoleId,
                'grants' => $grants,
                'password_reset' => isset($input['upwd']) && $currentAdminNo !== (int) $id,
            ]);
        }

        echo json_encode([
            'success' => $updated,
            'message' => $updated ? '계정을 수정했습니다.' : '계정 수정에 실패했습니다.'
        ]);
        exit;
    }

    if ($mode === 'delete_array') {
        $ids = json_decode($input['ids'] ?? '[]', true);

        if (!is_array($ids) || empty($ids)) {
            echo json_encode(['success' => false, 'message' => '삭제할 항목이 없습니다.']);
            exit;
        }

        $ids = array_values(array_unique(array_map('intval', $ids)));
        $kept = [];
        foreach ($ids as $id) {
            if ($id < 1) {
                continue;
            }
            if ($denyDelete($id) !== null) {
                continue;
            }
            $kept[] = $id;
        }
        if ($kept === []) {
            echo json_encode(['success' => false, 'message' => '삭제할 수 있는 계정이 없습니다.']);
            exit;
        }

        $result = AccountModel::deleteMultiple($kept);
        if ($result) {
            foreach ($kept as $id) {
                AclModel::deleteByAdmin((int) $id);
            }
            AuditLogger::record('delete', 'account', 0, count($kept) . '건', ['ids' => $kept]);
        }

        echo json_encode([
            'success' => $result,
            'message' => $result ? '선택한 계정을 삭제했습니다.' : '계정 삭제에 실패했습니다.'
        ]);
        exit;
    }

    if ($mode === 'unlock_idle') {
        $id = (int) ($input['id'] ?? 0);
        try {
            (new AccountIdleLock())->unlock($id, $currentAdminNo);
        } catch (RuntimeException $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            exit;
        }
        $target = AccountModel::findByNo($id);
        AuditLogger::record('update', 'account', $id, (string) ($target['uid'] ?? $id), ['unlock_idle' => true]);
        echo json_encode([
            'success' => true,
            'message' => '미접속 잠금을 해제했습니다.',
        ]);
        exit;
    }

    echo json_encode([
        'success' => false,
        'message' => '유효하지 않은 요청입니다.'
    ]);
    exit;
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => ClientFault::message($e)
    ]);
}
