<?php
namespace Security;

/** Keep the application INSERT id stable while its required audit is persisted. */
final class AuditedConnection extends \PDO
{
    private $applicationInsertId = null;

    public function databaseInsertId() { return parent::lastInsertId(); }
    public function rememberInsertId($id): void { $this->applicationInsertId = $id; }
    public function lastInsertId($name = null)
    {
        return $name === null && $this->applicationInsertId !== null
            ? $this->applicationInsertId : parent::lastInsertId($name);
    }

    public function auditSavepoint(string $operation, string $name): void
    {
        if (!in_array($operation, ['SAVEPOINT', 'ROLLBACK TO SAVEPOINT', 'RELEASE SAVEPOINT'], true)
            || !preg_match('/^audit_[a-f0-9]{16}$/D', $name)) throw new \LogicException('Invalid audit savepoint.');
        parent::exec($operation . ' ' . $name);
    }

    public function query($query, $fetchMode = null, ...$fetchModeArgs)
    {
        $statement = $this->prepare($query);
        if ($statement === false) return false;
        if ($fetchMode !== null) {
            if (!$fetchModeArgs && $fetchMode === \PDO::FETCH_COLUMN) $fetchModeArgs = [0];
            if (!$fetchModeArgs && $fetchMode === \PDO::FETCH_CLASS) $fetchModeArgs = ['stdClass'];
            $statement->setFetchMode($fetchMode, ...$fetchModeArgs);
        }
        return $statement->execute() ? $statement : false;
    }

    public function exec($statement)
    {
        // Native exec/query bypass PDOStatement::execute. During administrator
        // writes they must use the same enforcement boundary as prepared SQL.
        if (AuditLogger::isCapturing()) {
            $query = $this->prepare($statement);
            return $query->execute() ? $query->rowCount() : false;
        }
        $result = parent::exec($statement);
        $this->rememberInsertId(parent::lastInsertId());
        return $result;
    }
}
