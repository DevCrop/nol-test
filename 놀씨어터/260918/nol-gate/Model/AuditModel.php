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

    public static function count(string $sitekey, string $entity = ''): int
    {
        try {
            $sql = 'SELECT COUNT(*) FROM nb_admin_audit WHERE sitekey = :site';
            $params = ['site' => $sitekey];
            if ($entity !== '') {
                $sql .= ' AND entity = :entity';
                $params['entity'] = $entity;
            }
            $stmt = DB::getInstance()->prepare($sql);
            $stmt->execute($params);
            return (int) $stmt->fetchColumn();
        } catch (PDOException $e) {
            return 0;
        }
    }

    public static function list(string $sitekey, int $offset, int $limit, string $entity = ''): array
    {
        try {
            $sql = 'SELECT no, actor_no, actor_uid, actor_ip, action, entity, target_no, target_label, summary, created_at
                    FROM nb_admin_audit
                    WHERE sitekey = :site';
            $params = ['site' => $sitekey];
            if ($entity !== '') {
                $sql .= ' AND entity = :entity';
                $params['entity'] = $entity;
            }
            $sql .= ' ORDER BY no DESC LIMIT :off, :lim';
            $stmt = DB::getInstance()->prepare($sql);
            $stmt->bindValue(':site', $sitekey);
            if ($entity !== '') {
                $stmt->bindValue(':entity', $entity);
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
