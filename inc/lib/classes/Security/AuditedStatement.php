<?php
namespace Security;

/** A successful administrator DML and its required audit form one atomic unit. */
final class AuditedStatement extends \PDOStatement
{
    private $connection;
    private $bindings = [];
    private $normalizedSql = '';
    protected function __construct(\PDO $connection) { $this->connection = $connection; }

    public function bindValue($parameter, $value, $dataType = \PDO::PARAM_STR)
    {
        $ok = parent::bindValue($parameter, $value, $dataType);
        if ($ok) $this->bindings[$parameter] = ['value' => $value, 'type' => $dataType];
        return $ok;
    }

    public function bindParam($parameter, &$variable, $dataType = \PDO::PARAM_STR, $length = 0, $driverOptions = null)
    {
        $ok = parent::bindParam($parameter, $variable, $dataType, $length, $driverOptions);
        if ($ok) $this->bindings[$parameter] = ['value' => &$variable, 'type' => $dataType];
        return $ok;
    }

    private function run($params)
    {
        $ok = $params === null ? parent::execute() : parent::execute($params);
        if ($this->connection instanceof AuditedConnection) {
            $this->connection->rememberInsertId($this->connection->databaseInsertId());
        }
        return $ok;
    }

    /** Ignore ordinary comments/quoted strings, but reject executable comments and stacked SQL. */
    private function sqlShape(): string
    {
        $sql = $this->queryString; $shape = ''; $clean = ''; $length = strlen($sql);
        for ($i = 0; $i < $length; $i++) {
            $c = $sql[$i];
            if ($c === "'" || $c === '"' || $c === '`') {
                $quote = $c; $start = $i;
                for ($i++; $i < $length; $i++) {
                    if ($sql[$i] === '\\') { $i++; continue; }
                    if ($sql[$i] === $quote) {
                        if ($i + 1 < $length && $sql[$i+1] === $quote) { $i++; continue; }
                        break;
                    }
                }
                if ($i >= $length) throw new \RuntimeException('허용되지 않은 관리자 SQL입니다.');
                $clean .= substr($sql, $start, $i-$start+1);
                $shape .= $quote === '`' ? substr($sql, $start, $i-$start+1) : "''";
            } elseif ($c === '/' && substr($sql, $i, 2) === '/*') {
                if (substr($sql, $i, 3) === '/*!') throw new \RuntimeException('허용되지 않은 관리자 SQL입니다.');
                $end = strpos($sql, '*/', $i+2);
                if ($end === false) throw new \RuntimeException('허용되지 않은 관리자 SQL입니다.');
                $i = $end+1; $shape .= ' '; $clean .= ' ';
            } elseif ($c === '#' || ($c === '-' && substr($sql, $i, 2) === '--' && ($i+2 === $length || ctype_space($sql[$i+2])))) {
                $end = strpos($sql, "\n", $i); $i = $end === false ? $length : $end; $shape .= ' '; $clean .= ' ';
            } else { $shape .= $c; $clean .= $c; }
        }
        $shape = trim($shape);
        if (strpos(rtrim($shape, "; \t\r\n"), ';') !== false) throw new \RuntimeException('허용되지 않은 관리자 SQL입니다.');
        $this->normalizedSql = rtrim(trim($clean), "; \t\r\n");
        return rtrim($shape, "; \t\r\n");
    }

    private function assertTransactional(string $table): void
    {
        $check = $this->connection->prepare('SELECT TABLE_NAME, ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME IN (?, ?)');
        $check->execute([$table, 'nb_admin_audit']);
        $engines = $check->fetchAll(\PDO::FETCH_KEY_PAIR);
        if (($engines[$table] ?? '') !== 'InnoDB' || ($engines['nb_admin_audit'] ?? '') !== 'InnoDB') {
            throw new \RuntimeException('변경 이력 저장을 위한 DB 준비가 필요합니다. 관리자에게 문의해 주세요.');
        }
    }

    public function execute($params = null)
    {
        if (!AuditLogger::isCapturing()) return $this->run($params);
        // FTP installs the shared classes before switching db.php. Public reads
        // and legacy public forms remain usable during that short mixed state;
        // administrator writes wait for the complete transactional connection.
        if (!$this->connection instanceof AuditedConnection) {
            throw new \RuntimeException('보안 업데이트 중입니다. 잠시 후 다시 시도해 주세요.');
        }
        $sql = $this->sqlShape();
        if (preg_match('/^(SELECT|SHOW|DESCRIBE|DESC|EXPLAIN)\b/i', $sql)) return $this->run($params);
        if (!preg_match('/^(INSERT\s+INTO|UPDATE|DELETE\s+FROM)\s+`?(nb_[a-z0-9_]+)`?(?=\s|\()/i', $sql, $match)) {
            throw new \RuntimeException('허용되지 않은 관리자 SQL입니다.');
        }
        $table = strtolower($match[2]);
        $verb = strtoupper(substr($match[1], 0, 6));
        // Only a single target table is supported. JOIN/multi-table mutations
        // must never silently bypass engine and audit enforcement.
        $rest = substr($sql, strlen($match[0]));
        if (($verb === 'UPDATE' && !preg_match('/^\s+SET\b/i', $rest))
            || ($verb === 'DELETE' && !preg_match('/^(\s+WHERE\b|\s*$)/i', $rest))) {
            throw new \RuntimeException('허용되지 않은 관리자 SQL입니다.');
        }
        if (in_array($table, ['nb_admin', 'nb_admin_audit', 'nb_admin_privacy_access'], true)) return $this->run($params);
        $action = $verb === 'INSERT' ? 'create' : ($verb === 'UPDATE' ? 'update' : 'delete');
        $own = !$this->connection->inTransaction(); $savepoint = 'audit_' . bin2hex(random_bytes(8));
        $beforeId = $this->connection->lastInsertId();
        if ($own) $this->connection->beginTransaction();
        else $this->connection->auditSavepoint('SAVEPOINT', $savepoint);
        try {
            $this->assertTransactional($table);
            $targetIds = [];
            if ($action === 'delete' && in_array($table, ['nb_board', 'nb_works'], true)) {
                // Use the original WHERE and the actual bound parameter values.
                $where = preg_replace('/^DELETE\s+FROM\s+`?'.preg_quote($table,'/').'`?/i', '', $this->normalizedSql, 1);
                $where = rtrim(trim($where), ';');
                $key = $table === 'nb_works' ? 'id' : 'no';
                $snapshot = $this->connection->prepare('SELECT '.$key.' FROM '.$table.' '.$where.' FOR UPDATE');
                if ($params === null) foreach ($this->bindings as $key => $binding) $snapshot->bindValue($key, $binding['value'], $binding['type']);
                $params === null ? $snapshot->execute() : $snapshot->execute($params);
                $targetIds = array_values(array_unique(array_map('intval', $snapshot->fetchAll(\PDO::FETCH_COLUMN))));
            }
            $ok = $this->run($params); $businessId = $this->connection->lastInsertId();
            if (!$ok) throw new \RuntimeException('데이터 변경에 실패했습니다.');
            if ($this->rowCount() > 0) {
                $values = $params;
                if ($values === null) { $values = []; foreach ($this->bindings as $key => $binding) $values[$key] = $binding['value']; }
                $target = $action === 'create' ? (int)$businessId : (int)($values['no'] ?? $values[':no'] ?? $values['id'] ?? $values[':id'] ?? $_POST['no'] ?? $_POST['id'] ?? 0);
                if (count($targetIds) === 1) $target = $targetIds[0];
                $detail = ['affected_rows' => $this->rowCount()];
                if ($targetIds) $detail['target_ids'] = $targetIds;
                AuditLogger::record($action, $table, $target, '', $detail, true);
            }
            if ($own) $this->connection->commit();
            else $this->connection->auditSavepoint('RELEASE SAVEPOINT', $savepoint);
            $this->connection->rememberInsertId($businessId);
            return $ok;
        } catch (\Throwable $error) {
            if ($this->connection->inTransaction()) {
                if ($own) $this->connection->rollBack();
                else {
                    $this->connection->auditSavepoint('ROLLBACK TO SAVEPOINT', $savepoint);
                    $this->connection->auditSavepoint('RELEASE SAVEPOINT', $savepoint);
                }
            }
            $this->connection->rememberInsertId($beforeId);
            throw $error;
        }
    }
}
