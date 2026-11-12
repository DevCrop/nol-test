<?php
namespace Security;

/** Persist rental subjects and their privacy records as one application operation. */
final class RentalRequestService
{
    private $db;
    private $sitekey;

    public function __construct(\PDO $db, string $sitekey)
    {
        $this->db = $db;
        $this->sitekey = $sitekey;
    }

    public function create(array $fields, array $files = [], array $originalNames = []): int
    {
        $id = 0;
        if ($this->db->inTransaction()) throw new \LogicException('Nested rental transaction is not supported.');
        try {
            $this->db->beginTransaction();
            $log = PrivacyLogger::record('create', 'request', 0, (string) ($fields['manager_name'] ?? ''), '대관신청 등록');
            $columns = ['sitekey', 'performance_name', 'organization_name', 'manager_name', 'phone', 'email', 'contents'];
            $values = [$this->sitekey];
            foreach (array_slice($columns, 1) as $column) $values[] = (string) ($fields[$column] ?? '');
            for ($i = 1; $i <= 5; $i++) { $columns[] = 'file_'.$i; $values[] = $files['file_'.$i] ?? null; }
            for ($i = 1; $i <= 5; $i++) { $columns[] = 'file_'.$i.'_origin'; $values[] = $originalNames['file_'.$i] ?? null; }
            $stmt = $this->db->prepare('INSERT INTO nb_request ('.implode(',', $columns).',regdate) VALUES ('.implode(',', array_fill(0, count($values), '?')).',NOW())');
            $stmt->execute($values);
            $id = (int) $this->db->lastInsertId();
            if ($id <= 0) throw new \RuntimeException('Rental insert did not return an identifier.');
            PrivacyLogger::attachTarget($log, $id);
            $this->db->commit();
            return $id;
        } catch (\Throwable $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            // Legacy nb_request may be MyISAM: rollback cannot undo its INSERT.
            if ($id > 0) {
                $cleanup = $this->db->prepare('DELETE FROM nb_request WHERE no=? AND sitekey=?');
                $cleanup->execute([$id, $this->sitekey]);
            }
            throw $e;
        }
    }

    public function delete(int $id): void
    {
        if ($id <= 0 || $this->db->inTransaction()) throw new \RuntimeException('잘못된 대관신청 요청입니다.');
        $stmt = $this->db->prepare('SELECT manager_name FROM nb_request WHERE no=? AND sitekey=?');
        $stmt->execute([$id, $this->sitekey]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        if (!$row) throw new \RuntimeException('삭제할 항목이 없습니다.');
        try {
            $this->db->beginTransaction();
            PrivacyLogger::record('delete', 'request', $id, (string) $row['manager_name'], '대관신청 삭제');
            $stmt = $this->db->prepare('DELETE FROM nb_request WHERE no=? AND sitekey=?');
            $stmt->execute([$id, $this->sitekey]);
            if ($stmt->rowCount() !== 1) throw new \RuntimeException('삭제할 항목이 없습니다.');
            $this->db->commit();
        } catch (\Throwable $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            throw $e;
        }
    }
}
