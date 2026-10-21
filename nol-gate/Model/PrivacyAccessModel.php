<?php

class PrivacyAccessModel
{
    public static function insert(array $row): bool
    {
        try {
            $stmt = DB::getInstance()->prepare(
                'INSERT INTO nb_admin_privacy_access
                    (sitekey, actor_no, actor_uid, actor_ip, action, entity, target_no, subject_label, task, reason, created_at)
                 VALUES
                    (:sitekey, :actor_no, :actor_uid, :actor_ip, :action, :entity, :target_no, :subject_label, :task, :reason, :created_at)'
            );
            return $stmt->execute($row);
        } catch (PDOException $e) {
            error_log('[privacy-access] insert ' . $e->getMessage());
            return false;
        }
    }

    private static function criteria(string $sitekey, array $filters): array
    {
        $where = ' WHERE sitekey = :site';
        $params = ['site' => $sitekey];
        foreach (['action' => ['view', 'download'], 'entity' => ['inquiry', 'admin_account']] as $field => $allowed) {
            if (in_array($filters[$field] ?? '', $allowed, true)) {
                $where .= ' AND ' . $field . ' = :' . $field;
                $params[$field] = $filters[$field];
            }
        }
        $keyword = trim((string) ($filters['keyword'] ?? ''));
        if ($keyword !== '') {
            $parts = [];
            foreach (['actor_uid', 'actor_ip', 'subject_label', 'task', 'reason'] as $field) {
                $parts[] = 'LOCATE(:q_' . $field . ', ' . $field . ') > 0';
                $params['q_' . $field] = $keyword;
            }
            $where .= ' AND (' . implode(' OR ', $parts) . ')';
        }
        return [$where, $params];
    }

    public static function count(string $sitekey, array $filters = []): int
    {
        try {
            [$where, $params] = self::criteria($sitekey, $filters);
            $stmt = DB::getInstance()->prepare('SELECT COUNT(*) FROM nb_admin_privacy_access' . $where);
            $stmt->execute($params);
            return (int) $stmt->fetchColumn();
        } catch (PDOException $e) {
            return 0;
        }
    }

    public static function list(string $sitekey, int $offset, int $limit, array $filters = []): array
    {
        try {
            [$where, $params] = self::criteria($sitekey, $filters);
            $stmt = DB::getInstance()->prepare(
                'SELECT no, actor_uid, actor_ip, action, entity, target_no, subject_label, task, reason, created_at
                 FROM nb_admin_privacy_access
                 ' . $where . ' ORDER BY no DESC LIMIT :off, :lim'
            );
            foreach ($params as $key => $value) {
                $stmt->bindValue(':' . $key, $value);
            }
            $stmt->bindValue(':off', max(0, $offset), PDO::PARAM_INT);
            $stmt->bindValue(':lim', max(1, $limit), PDO::PARAM_INT);
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return is_array($rows) ? $rows : [];
        } catch (PDOException $e) {
            return [];
        }
    }
}
