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

    public static function count(string $sitekey): int
    {
        try {
            $stmt = DB::getInstance()->prepare(
                'SELECT COUNT(*) FROM nb_admin_privacy_access WHERE sitekey = :site'
            );
            $stmt->execute(['site' => $sitekey]);
            return (int) $stmt->fetchColumn();
        } catch (PDOException $e) {
            return 0;
        }
    }

    public static function list(string $sitekey, int $offset, int $limit): array
    {
        try {
            $stmt = DB::getInstance()->prepare(
                'SELECT no, actor_uid, actor_ip, action, entity, target_no, subject_label, task, reason, created_at
                 FROM nb_admin_privacy_access
                 WHERE sitekey = :site
                 ORDER BY no DESC
                 LIMIT :off, :lim'
            );
            $stmt->bindValue(':site', $sitekey);
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
