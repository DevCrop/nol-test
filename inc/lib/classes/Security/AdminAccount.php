<?php
namespace Security;

final class AdminAccount
{
    private const SITE = 'BLUESQ';

    public static function findByUid(string $uid): array
    {
        $stmt = \DB::getInstance()->prepare('SELECT * FROM nb_admin WHERE uid = :uid AND sitekey = :sitekey LIMIT 1');
        $stmt->execute(['uid' => $uid, 'sitekey' => self::SITE]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return is_array($row) ? $row : [];
    }

    public static function findByNo(int $no): array
    {
        $stmt = \DB::getInstance()->prepare('SELECT * FROM nb_admin WHERE no = ? AND sitekey = ? LIMIT 1');
        $stmt->execute([$no, self::SITE]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return is_array($row) ? $row : [];
    }

    public static function all(): array
    {
        $stmt = \DB::getInstance()->prepare('SELECT no, uid, uname, email, active_status, role_code, login_fail_count, login_locked_until, last_login_at, idle_locked_at, password_changed_at, password_must_change, created_at FROM nb_admin WHERE sitekey = ? ORDER BY no ASC');
        $stmt->execute([self::SITE]);
        return (array) $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public static function requireSuper(): array
    {
        $row = self::findByNo((int) ($_SESSION['no_adm_login_no'] ?? 0));
        if (!$row || ($row['role_code'] ?? 'admin') !== 'super') {
            http_response_code(403);
            header('Content-Type: text/plain; charset=utf-8');
            exit('권한이 없습니다.');
        }
        return $row;
    }

    public static function createManaged(array $input): int
    {
        $data = self::validated($input, true);
        if (self::taken('uid', $data['uid']) || self::taken('email', $data['email'])) throw new \RuntimeException('이미 사용 중인 아이디 또는 이메일입니다.');
        $stmt = \DB::getInstance()->prepare('INSERT INTO nb_admin (sitekey, uid, upwd, uname, email, active_status, role_code, login_fail_count, password_changed_at, password_must_change, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, 0, NOW(), 1, NOW(), NOW())');
        $stmt->execute([self::SITE, $data['uid'], password_hash($data['password'], PASSWORD_DEFAULT), $data['uname'], $data['email'], $data['active_status'], $data['role_code']]);
        return (int) \DB::getInstance()->lastInsertId();
    }

    public static function updateManaged(int $no, array $input, int $actorNo): bool
    {
        $target = self::findByNo($no);
        if (!$target) throw new \RuntimeException('계정을 찾을 수 없습니다.');
        $data = self::validated($input, false);
        if (self::taken('email', $data['email'], $no)) throw new \RuntimeException('이미 사용 중인 이메일입니다.');
        if ($actorNo === $no && ($data['active_status'] !== 'Y' || $data['role_code'] !== 'super')) throw new \RuntimeException('본인 계정은 비활성화하거나 관리자 권한을 낮출 수 없습니다.');
        if (($target['role_code'] ?? '') === 'super' && ($data['active_status'] !== 'Y' || $data['role_code'] !== 'super') && self::activeSuperCount($no) === 0) throw new \RuntimeException('마지막 최고 관리자는 비활성화하거나 권한을 낮출 수 없습니다.');
        $stmt = \DB::getInstance()->prepare("UPDATE nb_admin SET uname = ?, email = ?, active_status = ?, role_code = ?, login_token = CASE WHEN ? = 'N' THEN NULL ELSE login_token END, updated_at = NOW() WHERE no = ? AND sitekey = ?");
        $stmt->execute([$data['uname'], $data['email'], $data['active_status'], $data['role_code'], $data['active_status'], $no, self::SITE]);
        return $stmt->rowCount() === 1;
    }

    public static function resetPassword(int $no, string $password, string $confirm, int $actorNo): bool
    {
        if ($no === $actorNo) throw new \RuntimeException('본인 비밀번호는 계정 보안 화면에서 변경하세요.');
        $target = self::findByNo($no);
        if (!$target) throw new \RuntimeException('계정을 찾을 수 없습니다.');
        self::assertPassword($password, $confirm);
        if (stripos($password, (string) $target['uid']) !== false) throw new \RuntimeException('비밀번호에 아이디를 포함할 수 없습니다.');
        $stmt = \DB::getInstance()->prepare('UPDATE nb_admin SET upwd = ?, password_must_change = 1, login_token = NULL, login_fail_count = 0, login_locked_until = NULL, updated_at = NOW() WHERE no = ? AND sitekey = ?');
        $stmt->execute([password_hash($password, PASSWORD_DEFAULT), $no, self::SITE]);
        return $stmt->rowCount() === 1;
    }

    public static function unlock(int $no, int $actorNo): bool
    {
        if ($no === $actorNo) throw new \RuntimeException('본인 계정의 잠금은 직접 해제할 수 없습니다.');
        $stmt = \DB::getInstance()->prepare('UPDATE nb_admin SET login_fail_count = 0, login_locked_until = NULL, idle_locked_at = NULL, last_login_at = NOW(), login_token = NULL, updated_at = NOW() WHERE no = ? AND sitekey = ?');
        $stmt->execute([$no, self::SITE]);
        return $stmt->rowCount() === 1;
    }

    public static function deleteManaged(int $no, int $actorNo): bool
    {
        if ($no === $actorNo) throw new \RuntimeException('본인 계정은 삭제할 수 없습니다.');
        $target = self::findByNo($no);
        if (!$target) throw new \RuntimeException('계정을 찾을 수 없습니다.');
        if (($target['role_code'] ?? '') === 'super' && self::activeSuperCount($no) === 0) throw new \RuntimeException('마지막 최고 관리자는 삭제할 수 없습니다.');
        $stmt = \DB::getInstance()->prepare('DELETE FROM nb_admin WHERE no = ? AND sitekey = ?');
        $stmt->execute([$no, self::SITE]);
        return $stmt->rowCount() === 1;
    }

    public static function assertLoginAllowed(array $row): void
    {
        if (($row['active_status'] ?? 'N') !== 'Y') throw new \RuntimeException('사용이 중지된 계정입니다.');
        if (!empty($row['login_locked_until']) && strtotime($row['login_locked_until']) > time()) throw new \RuntimeException('로그인 실패 횟수를 초과했습니다. 잠시 후 다시 시도하세요.');
        if (!empty($row['idle_locked_at'])) {
            throw new \RuntimeException('장기 미접속으로 잠긴 계정입니다.');
        }
        $idleBase = (string) ($row['last_login_at'] ?: ($row['created_at'] ?? ''));
        if ($idleBase !== '' && strtotime($idleBase) <= strtotime('-' . ACCOUNT_IDLE_DAYS . ' days')) {
            $stmt = \DB::getInstance()->prepare('UPDATE nb_admin SET idle_locked_at = NOW(), login_token = NULL WHERE no = ?');
            $stmt->execute([(int) $row['no']]);
            throw new \RuntimeException('장기 미접속으로 잠긴 계정입니다.');
        }
    }

    public static function verifyPassword(array $row, string $plain): bool
    {
        $stored = (string) ($row['upwd'] ?? '');
        $info = password_get_info($stored);
        $modern = ($info['algoName'] ?? 'unknown') !== 'unknown';
        $ok = $modern ? password_verify($plain, $stored) : hash_equals($stored, hash('sha256', $plain));
        if (!$ok) return false;
        if (!$modern || password_needs_rehash($stored, PASSWORD_DEFAULT)) {
            $stmt = \DB::getInstance()->prepare('UPDATE nb_admin SET upwd = ?, password_changed_at = COALESCE(password_changed_at, NOW()) WHERE no = ? AND upwd = ?');
            $stmt->execute([password_hash($plain, PASSWORD_DEFAULT), (int) $row['no'], $stored]);
            if ($stmt->rowCount() !== 1) return false;
        }
        return true;
    }

    public static function failure(?array $row): void
    {
        if (!$row) return;
        // Increment inside one statement: stale requests must not overwrite each other.
        $stmt = \DB::getInstance()->prepare('UPDATE nb_admin SET login_fail_count = CASE WHEN login_locked_until IS NOT NULL AND login_locked_until <= NOW() THEN 1 ELSE login_fail_count + 1 END, login_locked_until = CASE WHEN login_fail_count >= ? THEN DATE_ADD(NOW(), INTERVAL ? SECOND) ELSE NULL END WHERE no = ? AND sitekey = ? AND (login_locked_until IS NULL OR login_locked_until <= NOW())');
        $stmt->execute([LOGIN_MAX_ATTEMPTS, LOGIN_LOCK_SECONDS, (int) $row['no'], self::SITE]);
    }

    public static function establish(array $row): void
    {
        self::assertLoginAllowed($row);
        $token = bin2hex(random_bytes(32));
        $stmt = \DB::getInstance()->prepare("UPDATE nb_admin SET login_fail_count = 0, login_locked_until = NULL, last_login_at = NOW(), idle_locked_at = NULL, login_token = ? WHERE no = ? AND sitekey = ? AND upwd = ? AND active_status = 'Y' AND idle_locked_at IS NULL AND (login_locked_until IS NULL OR login_locked_until <= NOW())");
        $stmt->execute([$token, (int) $row['no'], self::SITE, (string) $row['upwd']]);
        if ($stmt->rowCount() !== 1) throw new \RuntimeException('계정 정보가 변경되었습니다. 다시 로그인하세요.');
        if (session_status() === PHP_SESSION_ACTIVE && !headers_sent()) session_regenerate_id(true);
        $_SESSION['no_adm_login_no'] = (int) $row['no'];
        $_SESSION['no_adm_login_uid'] = (string) $row['uid'];
        $_SESSION['no_adm_login_uname'] = (string) $row['uname'];
        $_SESSION['no_adm_login_token'] = $token;
        $_SESSION['no_adm_last_activity'] = time();
        $changed = !empty($row['password_changed_at']) ? strtotime($row['password_changed_at']) : 0;
        $_SESSION['no_adm_password_change_required'] = !empty($row['password_must_change']) || $changed === 0 || $changed < strtotime('-' . PASSWORD_MAX_DAYS . ' days');
    }

    private static function validated(array $input, bool $creating): array
    {
        $uid = trim((string) ($input['uid'] ?? ''));
        $uname = AccountValidator::name((string) ($input['uname'] ?? ''));
        $email = AccountValidator::email((string) ($input['email'] ?? ''));
        $active = (string) ($input['active_status'] ?? 'Y');
        $role = (string) ($input['role_code'] ?? 'admin');
        if ($creating && !preg_match('/^[a-zA-Z0-9_]{4,20}$/', $uid)) throw new \RuntimeException('아이디는 영문, 숫자, 밑줄 4~20자로 입력하세요.');
        if ($uname === '' || mb_strlen($uname) > 25) throw new \RuntimeException('이름을 1~25자로 입력하세요.');
        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 190) throw new \RuntimeException('올바른 이메일 주소를 입력하세요.');
        if (!in_array($active, ['Y', 'N'], true) || !in_array($role, ['super', 'admin'], true)) throw new \RuntimeException('잘못된 계정 설정입니다.');
        $data = ['uid' => $uid, 'uname' => $uname, 'email' => $email, 'active_status' => $active, 'role_code' => $role];
        if ($creating) {
            self::assertPassword((string) ($input['password'] ?? ''), (string) ($input['password_confirm'] ?? ''));
            if (stripos((string) $input['password'], $uid) !== false) throw new \RuntimeException('비밀번호에 아이디를 포함할 수 없습니다.');
            $data['password'] = (string) $input['password'];
        }
        return $data;
    }

    private static function assertPassword(string $password, string $confirm): void
    {
        AccountValidator::password($password, $confirm);
    }

    private static function taken(string $column, string $value, int $exceptNo = 0): bool
    {
        if (!in_array($column, ['uid', 'email'], true)) return true;
        $sql = "SELECT COUNT(*) FROM nb_admin WHERE sitekey = ? AND LOWER(TRIM({$column})) = LOWER(?)";
        $params = [self::SITE, $value];
        if ($exceptNo > 0) { $sql .= ' AND no <> ?'; $params[] = $exceptNo; }
        $stmt = \DB::getInstance()->prepare($sql); $stmt->execute($params);
        return (int) $stmt->fetchColumn() > 0;
    }

    private static function activeSuperCount(int $exceptNo = 0): int
    {
        $stmt = \DB::getInstance()->prepare("SELECT COUNT(*) FROM nb_admin WHERE sitekey = ? AND role_code = 'super' AND active_status = 'Y' AND no <> ?");
        $stmt->execute([self::SITE, $exceptNo]);
        return (int) $stmt->fetchColumn();
    }
}
