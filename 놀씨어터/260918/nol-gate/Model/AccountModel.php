<?php

class AccountModel {
    
    public static function insert($data) {
        $db = DB::getInstance();
        global $NO_SITE_UNIQUE_KEY;

        $sql = "
            INSERT INTO nb_admin (uid, upwd, uname, email, phone, active_status, role_id, sitekey, created_at, last_login_at, password_changed_at)
            VALUES (:uid, :upwd, :uname, :email, :phone, :active_status, :role_id, :sitekey, :created_at, :last_login_at, :password_changed_at)
        ";

        $created = date('Y-m-d H:i:s');
        $stmt = $db->prepare($sql);
        $stmt->execute([
            ':uid' => $data['uid'],
            ':upwd' => $data['upwd'],
            ':uname' => $data['uname'],
            ':email' => $data['email'],
            ':phone' => $data['phone'],
            ':active_status' => $data['active_status'] ?? 'Y',
            ':role_id' => $data['role_id'] ?? 3, // 기본값: 외부인
            ':sitekey' => $NO_SITE_UNIQUE_KEY,
            ':created_at' => $created,
            ':last_login_at' => $created,
            ':password_changed_at' => $created,
        ]);

        return $db->lastInsertId();
    }

    
    public static function delete($id) 
    {
        $db = DB::getInstance();
        $sql = "DELETE FROM nb_admin WHERE no = :no";
        $stmt = $db->prepare($sql);
        return $stmt->execute([':no' => $id]);
    }

    public static function deleteMultiple(array $ids): bool
    {
        if (empty($ids)) return false;

        $placeholders = implode(',', array_fill(0, count($ids), '?'));

        $db = DB::getInstance();
        $stmt = $db->prepare("DELETE FROM nb_admin WHERE no IN ($placeholders)");

        return $stmt->execute($ids);
    }

    public static function update($id, $data) 
    {
        $db = DB::getInstance();

        $fields = [
            'uid' => $data['uid'], 
            'uname' => $data['uname'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'active_status' => $data['active_status'] ?? 'Y',
            'role_id' => $data['role_id'] ?? 3,
        ];

        if (!empty($data['upwd'])) {
            $fields['upwd'] = $data['upwd'];
        }

        $set = implode(', ', array_map(fn($key) => "$key = :$key", array_keys($fields)));
        $fields['no'] = $id;

        $sql = "UPDATE nb_admin SET $set WHERE no = :no";
        $stmt = $db->prepare($sql);
        return $stmt->execute($fields);
    }


    public static function taken(string $column, string $value, ?int $exceptId = null): bool
    {
        $map = [
            'uid' => 'LOWER(TRIM(uid)) = LOWER(:v)',
            'email' => 'LOWER(TRIM(email)) = LOWER(:v)',
            'phone' => "REPLACE(REPLACE(IFNULL(phone,''), '-', ''), ' ', '') = :v",
        ];
        if (!isset($map[$column]) || $value === '') {
            return false;
        }
        $sql = 'SELECT COUNT(*) FROM nb_admin WHERE ' . $map[$column];
        $params = ['v' => $value];
        if ($exceptId !== null && $exceptId > 0) {
            $sql .= ' AND no != :no';
            $params['no'] = $exceptId;
        }
        $stmt = DB::getInstance()->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn() > 0;
    }

    public static function exists(array $conditions): bool
    {
        foreach ($conditions as $key => $val) {
            if (self::taken((string) $key, (string) $val)) {
                return true;
            }
        }
        return false;
    }

    /** 역할별 계정 수 조회 */
    public static function countByRole(int $roleId): int
    {
        $db = DB::getInstance();
        $stmt = $db->prepare("SELECT COUNT(*) FROM nb_admin WHERE role_id = :role_id");
        $stmt->execute([':role_id' => $roleId]);
        return (int) $stmt->fetchColumn();
    }

    /** 계정의 현재 role_id 조회 */
    public static function getRoleId(int $id): ?int
    {
        $db = DB::getInstance();
        $stmt = $db->prepare("SELECT role_id FROM nb_admin WHERE no = :no");
        $stmt->execute([':no' => $id]);
        $v = $stmt->fetchColumn();
        return $v !== false ? (int) $v : null;
    }

    public static function existsExceptSelf(array $conditions, int $excludeId): bool
    {
        foreach ($conditions as $key => $val) {
            if (self::taken((string) $key, (string) $val, $excludeId)) {
                return true;
            }
        }
        return false;
    }

    public static function setLoginToken(int $id, string $token): void
    {
        if ($id < 1) {
            return;
        }
        try {
            $stmt = DB::getInstance()->prepare('UPDATE nb_admin SET login_token = :t WHERE no = :no');
            $stmt->execute(['t' => $token, 'no' => $id]);
        } catch (PDOException $e) {
            error_log('[login_token] php sql/migrate.php up');
        }
    }

    public static function getLoginToken(int $id): string
    {
        if ($id < 1) {
            return '';
        }
        try {
            $stmt = DB::getInstance()->prepare('SELECT login_token FROM nb_admin WHERE no = :no');
            $stmt->execute(['no' => $id]);
            $v = $stmt->fetchColumn();
            return is_string($v) ? $v : '';
        } catch (PDOException $e) {
            return '';
        }
    }

    public static function clearLoginTokenIfMatch(int $id, string $token): void
    {
        if ($id < 1 || $token === '') {
            return;
        }
        try {
            $stmt = DB::getInstance()->prepare(
                'UPDATE nb_admin SET login_token = NULL WHERE no = :no AND login_token = :t'
            );
            $stmt->execute(['no' => $id, 't' => $token]);
        } catch (PDOException $e) {
            error_log('[login_token] php sql/migrate.php up');
        }
    }

    public static function countLiveSupers(int $exceptId = 0): int
    {
        global $NO_SITE_UNIQUE_KEY;
        try {
            $sql = "SELECT COUNT(*) FROM nb_admin
                    WHERE role_id = 1
                      AND IFNULL(active_status, 'Y') = 'Y'
                      AND idle_locked_at IS NULL
                      AND sitekey = :site";
            $params = ['site' => (string) $NO_SITE_UNIQUE_KEY];
            if ($exceptId > 0) {
                $sql .= ' AND no != :no';
                $params['no'] = $exceptId;
            }
            $stmt = DB::getInstance()->prepare($sql);
            $stmt->execute($params);
            return (int) $stmt->fetchColumn();
        } catch (PDOException $e) {
            return 0;
        }
    }

    public static function stampLastLogin(int $id, string $at): void
    {
        if ($id < 1) {
            return;
        }
        try {
            $stmt = DB::getInstance()->prepare(
                'UPDATE nb_admin SET last_login_at = :t WHERE no = :no'
            );
            $stmt->execute(['t' => $at, 'no' => $id]);
        } catch (PDOException $e) {
            error_log('[idle_lock] php sql/migrate.php up');
        }
    }

    public static function lockIdle(int $id, string $at): void
    {
        if ($id < 1) {
            return;
        }
        try {
            $stmt = DB::getInstance()->prepare(
                'UPDATE nb_admin SET idle_locked_at = :t, login_token = NULL WHERE no = :no'
            );
            $stmt->execute(['t' => $at, 'no' => $id]);
        } catch (PDOException $e) {
            error_log('[idle_lock] php sql/migrate.php up');
        }
    }

    public static function unlockIdle(int $id, string $lastLoginAt): void
    {
        if ($id < 1) {
            return;
        }
        try {
            $stmt = DB::getInstance()->prepare(
                'UPDATE nb_admin SET idle_locked_at = NULL, last_login_at = :t WHERE no = :no'
            );
            $stmt->execute(['t' => $lastLoginAt, 'no' => $id]);
        } catch (PDOException $e) {
            error_log('[idle_lock] php sql/migrate.php up');
        }
    }

    public static function findByNo(int $id): array
    {
        if ($id < 1) {
            return [];
        }
        try {
            $stmt = DB::getInstance()->prepare(
                'SELECT no, uid, uname, email, role_id, active_status, created_at,
                        last_login_at, idle_locked_at, must_change_password, password_changed_at
                 FROM nb_admin WHERE no = :no'
            );
            $stmt->execute(['no' => $id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return is_array($row) ? $row : [];
        } catch (PDOException $e) {
            return [];
        }
    }

    public static function passwordGateRow(int $id): array
    {
        return self::findByNo($id);
    }

    public static function mustChangePassword(int $id): bool
    {
        $row = self::findByNo($id);
        $v = $row['must_change_password'] ?? 0;
        return $v === 1 || $v === '1';
    }

    public static function markPasswordReset(int $id): void
    {
        if ($id < 1) {
            return;
        }
        try {
            $stmt = DB::getInstance()->prepare(
                'UPDATE nb_admin SET must_change_password = 1, login_token = NULL WHERE no = :no'
            );
            $stmt->execute(['no' => $id]);
        } catch (PDOException $e) {
            error_log('[must_change_password] php sql/migrate.php up');
        }
    }

    public static function finishPasswordChange(int $id, string $at): void
    {
        if ($id < 1) {
            return;
        }
        try {
            $stmt = DB::getInstance()->prepare(
                'UPDATE nb_admin SET must_change_password = 0, password_changed_at = :t WHERE no = :no'
            );
            $stmt->execute(['t' => $at, 'no' => $id]);
        } catch (PDOException $e) {
            error_log('[must_change_password] php sql/migrate.php up');
        }
    }

}