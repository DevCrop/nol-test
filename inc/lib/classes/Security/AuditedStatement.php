<?php
namespace Security;

/** Record changed database rows, never infer CRUD success from an HTTP status. */
final class AuditedStatement extends \PDOStatement
{
    private $connection;
    protected function __construct(\PDO $connection) { $this->connection = $connection; }

    public function execute($params = null)
    {
        $ok = $params === null ? parent::execute() : parent::execute($params);
        if (!$ok || $this->rowCount() < 1 || !AuditLogger::isCapturing()) return $ok;
        if (!preg_match('/^\s*(INSERT\s+INTO|UPDATE|DELETE\s+FROM)\s+`?(nb_[a-z0-9_]+)`?/i', $this->queryString, $match)) return $ok;
        $table = strtolower($match[2]);
        if (in_array($table, ['nb_admin', 'nb_admin_audit', 'nb_admin_privacy_access'], true)) return $ok;
        $verb = strtoupper(substr($match[1], 0, 6));
        $action = $verb === 'INSERT' ? 'create' : ($verb === 'UPDATE' ? 'update' : 'delete');
        $target = $action === 'create' ? (int) $this->connection->lastInsertId() : (int) ($params['no'] ?? $params[':no'] ?? $params['id'] ?? $params[':id'] ?? $_POST['no'] ?? $_POST['id'] ?? 0);
        $affected = $this->rowCount();
        // Defer the audit INSERT so callers retain their business lastInsertId().
        register_shutdown_function(static function () use ($action, $table, $target, $affected): void {
            AuditLogger::record($action, $table, $target, '', ['affected_rows' => $affected]);
        });
        return $ok;
    }
}
