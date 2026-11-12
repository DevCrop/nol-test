<?php
namespace Security;

/** Record changed database rows, never infer CRUD success from an HTTP status. */
final class AuditedStatement extends \PDOStatement
{
    private $connection;
    protected function __construct(\PDO $connection) { $this->connection = $connection; }

    public function execute($params = null)
    {
        $targetIds = [];
        if (AuditLogger::isCapturing() && preg_match('/^\s*DELETE\s+FROM\s+`?(nb_board|nb_works)`?\s+(WHERE\s+.+?)\s*;?\s*$/isD', $this->queryString, $deletion)) {
            $key = strtolower($deletion[1]) === 'nb_works' ? 'id' : 'no';
            $snapshot = $this->connection->prepare('SELECT ' . $key . ' FROM ' . $deletion[1] . ' ' . rtrim($deletion[2], ';'));
            $params === null ? $snapshot->execute() : $snapshot->execute($params);
            $targetIds = array_values(array_unique(array_map('intval', $snapshot->fetchAll(\PDO::FETCH_COLUMN))));
        }
        $ok = $params === null ? parent::execute() : parent::execute($params);
        if (!$ok || $this->rowCount() < 1 || !AuditLogger::isCapturing()) return $ok;
        if (!preg_match('/^\s*(INSERT\s+INTO|UPDATE|DELETE\s+FROM)\s+`?(nb_[a-z0-9_]+)`?/i', $this->queryString, $match)) return $ok;
        $table = strtolower($match[2]);
        if (in_array($table, ['nb_admin', 'nb_admin_audit', 'nb_admin_privacy_access'], true)) return $ok;
        $verb = strtoupper(substr($match[1], 0, 6));
        $action = $verb === 'INSERT' ? 'create' : ($verb === 'UPDATE' ? 'update' : 'delete');
        $target = $action === 'create' ? (int) $this->connection->lastInsertId() : (int) ($params['no'] ?? $params[':no'] ?? $params['id'] ?? $params[':id'] ?? $_POST['no'] ?? $_POST['id'] ?? 0);
        if (count($targetIds) === 1) $target = $targetIds[0];
        $affected = $this->rowCount();
        // Defer the audit INSERT so callers retain their business lastInsertId().
        register_shutdown_function(static function () use ($action, $table, $target, $affected, $targetIds): void {
            $detail = ['affected_rows' => $affected];
            if ($targetIds) $detail['target_ids'] = $targetIds;
            AuditLogger::record($action, $table, $target, '', $detail);
        });
        return $ok;
    }
}
