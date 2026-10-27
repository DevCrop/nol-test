<?php

class AuditModel
{
    public static function insert(array $row): bool
    {
        try {
            $stmt = DB::getInstance()->prepare(
                'INSERT INTO nb_admin_audit
                    (sitekey, actor_no, actor_uid, actor_ip, action, entity, target_no, target_label, summary, detail_json, created_at)
                 VALUES
                    (:sitekey, :actor_no, :actor_uid, :actor_ip, :action, :entity, :target_no, :target_label, :summary, :detail_json, :created_at)'
            );
            return $stmt->execute($row);
        } catch (PDOException $e) {
            error_log('[audit] insert ' . $e->getMessage());
            return false;
        }
    }

    private static function criteria(string $sitekey, string $entity, array $filters): array
    {
        $sql = ' WHERE sitekey = :site';
        $params = ['site' => $sitekey];
        if ($entity !== '') {
            $sql .= ' AND entity = :entity';
            $params['entity'] = $entity;
        }
        if (in_array($filters['action'] ?? '', ['create', 'update', 'delete'], true)) {
            $sql .= ' AND action = :action';
            $params['action'] = $filters['action'];
        }
        $keyword = trim((string) ($filters['keyword'] ?? ''));
        if ($keyword !== '') {
            $sql .= ' AND (LOCATE(:actor, actor_uid) > 0 OR LOCATE(:summary, summary) > 0 OR LOCATE(:ip, actor_ip) > 0)';
            $params['actor'] = $params['summary'] = $params['ip'] = $keyword;
        }
        return [$sql, $params];
    }

    public static function count(string $sitekey, string $entity = '', array $filters = []): int
    {
        try {
            [$where, $params] = self::criteria($sitekey, $entity, $filters);
            $sql = 'SELECT COUNT(*) FROM nb_admin_audit' . $where;
            $stmt = DB::getInstance()->prepare($sql);
            $stmt->execute($params);
            return (int) $stmt->fetchColumn();
        } catch (PDOException $e) {
            return 0;
        }
    }

    public static function list(string $sitekey, int $offset, int $limit, string $entity = '', array $filters = []): array
    {
        try {
            [$where, $params] = self::criteria($sitekey, $entity, $filters);
            $sql = 'SELECT no, actor_no, actor_uid, actor_ip, action, entity, target_no, target_label, summary, detail_json, created_at FROM nb_admin_audit' . $where;
            $sql .= ' ORDER BY no DESC LIMIT :off, :lim';
            $stmt = DB::getInstance()->prepare($sql);
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
