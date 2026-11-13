<?php
namespace Security;

/** Bind administrator board subjects to mandatory privacy records and transaction. */
final class BoardPrivacyService
{
    private $db;
    private string $site;
    private bool $active = false;

    public function __construct(\PDO $db, string $site)
    {
        if ($db !== \DB::getInstance()) throw new \LogicException('Privacy records require the application connection.');
        $this->db = $db;
        $this->site = $site;
        // Registered before UploadLifecycle, so aborted requests first release
        // uncommitted references and then clean their staged uploads.
        register_shutdown_function(function (): void { $this->rollback(); });
    }

    public function begin(bool $comments = false): void
    {
        if ($this->active || $this->db->inTransaction()) throw new \LogicException('Nested board operation is not supported.');
        if (!$this->db instanceof AuditedConnection) throw new \RuntimeException('게시판 보안 업데이트를 먼저 완료해 주세요.');
        $tables = ['nb_board','nb_admin_audit','nb_admin_privacy_access'];
        if ($comments) $tables[] = 'nb_board_comment';
        $stmt = $this->db->prepare('SELECT TABLE_NAME, ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME IN ('.implode(',',array_fill(0,count($tables),'?')).')');
        $stmt->execute($tables); $engines = $stmt->fetchAll(\PDO::FETCH_KEY_PAIR);
        foreach ($tables as $table) if (($engines[$table] ?? '') !== 'InnoDB') throw new \RuntimeException('개인정보 처리기록을 위한 DB 준비가 필요합니다.');
        $this->db->beginTransaction();
        $this->active = true;
    }

    public function board(int $id): array
    {
        return $this->locked('nb_board', $id);
    }

    public function comment(int $id): array
    {
        if (!$this->active || !$this->db->inTransaction() || $id < 1) throw new \RuntimeException('댓글 정보를 확인해주세요.');
        $stmt = $this->db->prepare('SELECT parent_no FROM nb_board_comment WHERE no=? AND sitekey=?');
        $stmt->execute([$id,$this->site]); $parent = (int)$stmt->fetchColumn();
        if ($parent < 1) throw new \RuntimeException('정보를 찾을 수 없습니다.');
        // Use the same parent-before-comment lock order as parent deletion.
        $this->board($parent);
        $row = $this->locked('nb_board_comment', $id);
        if ((int)$row['parent_no'] !== $parent) throw new \RuntimeException('댓글 대상을 다시 확인해주세요.');
        return $row;
    }

    public function commentsFor(array $ids): array
    {
        if (!$this->active || !$this->db->inTransaction()) throw new \LogicException('Comment snapshot requires a transaction.');
        if (!$ids) return [];
        foreach ($ids as $id) if (!is_int($id) || $id < 1) throw new \LogicException('Invalid comment parent.');
        $stmt = $this->db->prepare('SELECT * FROM nb_board_comment WHERE sitekey=? AND parent_no IN ('.implode(',',array_fill(0,count($ids),'?')).') ORDER BY parent_no,no FOR UPDATE');
        $stmt->execute(array_merge([$this->site],$ids));
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    private function locked(string $table, int $id): array
    {
        if (!$this->active || !$this->db->inTransaction()) throw new \LogicException('Board snapshot requires a transaction.');
        if ($id < 1) throw new \RuntimeException('정보를 찾을 수 없습니다.');
        $stmt = $this->db->prepare('SELECT * FROM '.$table.' WHERE no=? AND sitekey=? FOR UPDATE');
        $stmt->execute([$id,$this->site]); $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        if (!$row) throw new \RuntimeException('정보를 찾을 수 없습니다.');
        return $row;
    }

    public function record(string $action, int $id, string $writer, string $task): void
    {
        if ($id < 1 || !in_array($action,['view','create','update','delete'],true)) throw new \LogicException('Invalid board privacy target.');
        if ($action !== 'view' && (!$this->active || !$this->db->inTransaction())) throw new \LogicException('Board mutation record requires its transaction.');
        PrivacyLogger::record($action,'board',$id,htmlspecialchars_decode($writer,ENT_QUOTES),$task);
    }

    public function changed(int $id, string $before, string $after): void
    {
        if (htmlspecialchars_decode($before,ENT_QUOTES) !== htmlspecialchars_decode($after,ENT_QUOTES)) {
            $this->record('update',$id,$before,'게시글 수정: 이전 작성자 정보 처리');
        }
        // A successful same-value edit still processes the selected subject.
        $this->record('update',$id,$after,'게시글 수정: 작성자 정보 처리');
    }

    public function commit(): void
    {
        if (!$this->active || !$this->db->inTransaction()) throw new \LogicException('No board operation to commit.');
        $this->db->commit(); $this->active = false;
    }

    public function rollback(): void
    {
        if ($this->active && $this->db->inTransaction()) $this->db->rollBack();
        $this->active = false;
    }
}
