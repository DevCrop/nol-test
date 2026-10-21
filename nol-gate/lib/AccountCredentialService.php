<?php

require_once __DIR__ . '/TemporaryPassword.php';
require_once dirname(__DIR__) . '/Model/AccountModel.php';
require_once dirname(__DIR__) . '/Model/AclModel.php';
require_once dirname(__DIR__) . '/core/Validator.php';

final class AccountCredentialService
{
    private PDO $db;

    public function __construct(?PDO $db = null) { $this->db = $db ?? DB::getInstance(); }

    /** The plaintext exists only in this request and its single successful response. */
    public function create(array $input, array $grants, string $temporary): array
    {
        $hash = password_hash($temporary, PASSWORD_DEFAULT);
        $deadline = TemporaryPassword::deadline();
        $this->db->beginTransaction();
        try {
            $roleId = (int) $input['role_id'];
            $lock = $this->db->prepare('SELECT role_id FROM nb_roles WHERE role_id = ? FOR UPDATE');
            $lock->execute([$roleId]);
            if (!$lock->fetchColumn()) throw new RuntimeException('유효하지 않은 권한입니다.');
            if (AccountModel::taken('uid', (string) $input['uid']) || AccountModel::taken('email', (string) $input['email'])) throw new RuntimeException('이미 사용 중인 아이디 또는 이메일입니다.');
            $limit = Role::getRoleLimit($roleId);
            if ($limit !== null && AccountModel::countByRole($roleId) >= $limit) throw new RuntimeException(Role::roleLimitMessage($roleId));
            $input['upwd'] = $hash;
            $id = (int) AccountModel::insert($input);
            $stmt = $this->db->prepare('UPDATE nb_admin SET must_change_password=1, temporary_password_expires_at=:expires, login_token=NULL WHERE no=:no');
            $stmt->execute(['expires' => $deadline, 'no' => $id]);
            if ($roleId !== 1) AclModel::replaceAll($id, $grants);
            $this->db->commit();
            return ['id' => $id, 'uid' => (string) $input['uid'], 'temporary_password' => $temporary, 'expires_at' => $deadline];
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            throw $e;
        }
    }

    public function reissue(int $id, int $actor): array
    {
        if ($id < 1 || $id === $actor) throw new RuntimeException('본인 계정은 비밀번호 변경 화면을 이용하세요.');
        $temporary = TemporaryPassword::generate();
        $hash = password_hash($temporary, PASSWORD_DEFAULT);
        $deadline = TemporaryPassword::deadline();
        $this->db->beginTransaction();
        try {
            $row = $this->locked($id);
            $stmt = $this->db->prepare('UPDATE nb_admin SET upwd=:hash, must_change_password=1, temporary_password_expires_at=:expires, login_token=NULL WHERE no=:no AND sitekey=:site');
            $stmt->execute(['hash' => $hash, 'expires' => $deadline, 'no' => $id, 'site' => $row['sitekey']]);
            $this->db->commit();
            return ['id' => $id, 'uid' => (string) $row['uid'], 'temporary_password' => $temporary, 'expires_at' => $deadline];
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            throw $e;
        }
    }

    public function change(int $id, string $sessionToken, string $old, string $password, string $confirmation): void
    {
        if ($password === '' || $password !== $confirmation) throw new RuntimeException('새 비밀번호와 확인 값이 일치하지 않습니다.');
        $validator = new Validator();
        $validator->passwordPolicy('upwd', $password, '비밀번호');
        if ($validator->fails()) throw new RuntimeException(implode("\n", $validator->getErrors()));
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $this->db->beginTransaction();
        try {
            $row = $this->locked($id);
            if (($row['active_status'] ?? '') !== 'Y' || !empty($row['idle_locked_at']) || $sessionToken === '' || !hash_equals((string) ($row['login_token'] ?? ''), $sessionToken)) {
                throw new RuntimeException('인증 정보가 변경되었습니다. 다시 로그인하세요.');
            }
            TemporaryPassword::assertUsable($row);
            if (empty($row['must_change_password']) && !password_verify($old, $row['upwd'])) throw new RuntimeException('현재 비밀번호가 일치하지 않습니다.');
            if (password_verify($password, $row['upwd'])) throw new RuntimeException('현재 비밀번호와 다른 비밀번호로 변경하세요.');
            $stmt = $this->db->prepare('UPDATE nb_admin SET upwd=:hash, must_change_password=0, temporary_password_expires_at=NULL, password_changed_at=NOW(), login_token=NULL WHERE no=:no AND sitekey=:site');
            $stmt->execute(['hash' => $hash, 'no' => $id, 'site' => $row['sitekey']]);
            $this->db->commit();
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            throw $e;
        }
    }

    private function locked(int $id): array
    {
        global $NO_SITE_UNIQUE_KEY;
        $stmt = $this->db->prepare('SELECT * FROM nb_admin WHERE no=:no AND sitekey=:site FOR UPDATE');
        $stmt->execute(['no' => $id, 'site' => (string) $NO_SITE_UNIQUE_KEY]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) throw new RuntimeException('계정을 찾을 수 없습니다.');
        return $row;
    }
}
