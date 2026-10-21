<?php

/** Ordering operations share one server-selected group definition. No schema/data reset. */
final class GroupedOrder
{
    private const TABLES = [
        'nb_popups' => ['id', ['popup_path'], true],
        'nb_board' => ['no', ['sitekey', 'board_no'], false],
    ];
    private PDO $db;
    private string $table;
    private string $key;
    private array $groups;
    private string $stamp;

    public function __construct(PDO $db, string $table)
    {
        if (!isset(self::TABLES[$table])) throw new InvalidArgumentException('Unknown order table');
        $this->db = $db; $this->table = $table;
        [$this->key, $this->groups, $timestamps] = self::TABLES[$table];
        // Moving a neighbour is not editing its content.
        $this->stamp = $timestamps ? ', updated_at = updated_at' : '';
    }

    public function locked(callable $action, bool $copySource = false)
    {
        $this->db->exec('LOCK TABLES '.$this->table.' WRITE'.($copySource ? ', '.$this->table.' AS order_source READ' : ''));
        try { return $action(); }
        finally { $this->db->exec('UNLOCK TABLES'); }
    }

    private function scope(array $group, array &$params, string $prefix = 'g'): string
    {
        $parts = [];
        foreach ($this->groups as $column) {
            if (!array_key_exists($column, $group)) throw new InvalidArgumentException('Missing order group');
            $name = $prefix.'_'.$column; $parts[] = $column.' <=> :'.$name; $params[$name] = $group[$column];
        }
        return implode(' AND ', $parts);
    }

    public function head(array $group, int $exclude = 0): void
    {
        $params = ['exclude'=>$exclude]; $where = $this->scope($group, $params);
        $s = $this->db->prepare('UPDATE '.$this->table.' SET sort_no=sort_no+1'.$this->stamp.
            ' WHERE '.$where.' AND sort_no>=1 AND '.$this->key.'<>:exclude');
        $s->execute($params);
    }

    public function maximum(array $group): int
    {
        $params=[]; $where=$this->scope($group,$params);
        $s=$this->db->prepare('SELECT COALESCE(MAX(sort_no),0) FROM '.$this->table.' WHERE '.$where);
        $s->execute($params); return (int)$s->fetchColumn();
    }

    private function close(array $row): void
    {
        $params=['rank'=>(int)$row['sort_no'], 'exclude'=>(int)$row[$this->key]];
        $where=$this->scope($row,$params);
        // Do not close an occupied rank when old data already has ties.
        $s=$this->db->prepare('SELECT COUNT(*) FROM '.$this->table.' WHERE '.$where.' AND sort_no=:rank AND '.$this->key.'<>:exclude');
        $s->execute($params); if ((int)$s->fetchColumn()>0) return;
        unset($params['exclude']);
        $s=$this->db->prepare('UPDATE '.$this->table.' SET sort_no=sort_no-1'.$this->stamp.' WHERE '.$where.' AND sort_no>:rank');
        $s->execute($params);
    }

    /** Shift neighbours only; caller updates the selected row under the same table lock. */
    public function move(array $current, array $target, int $rank): int
    {
        $params=['id'=>(int)$current[$this->key]]; $where=$this->scope($target,$params);
        $s=$this->db->prepare('SELECT COUNT(*) FROM '.$this->table.' WHERE '.$this->key.'=:id AND '.$where);
        $s->execute($params); $same=(int)$s->fetchColumn()>0;
        if ($same && $rank===(int)$current['sort_no']) return $rank;
        $rank=max(1,min($rank,$this->maximum($target)+($same?0:1)));
        if (!$same) {
            $this->close($current);
            $params=['rank'=>$rank,'id'=>(int)$current[$this->key]]; $where=$this->scope($target,$params);
            $s=$this->db->prepare('UPDATE '.$this->table.' SET sort_no=sort_no+1'.$this->stamp.' WHERE '.$where.' AND sort_no>=:rank AND '.$this->key.'<>:id');
        } else {
            $old=(int)$current['sort_no']; if($old===$rank)return $rank;
            $params=['old'=>$old,'rank'=>$rank,'id'=>(int)$current[$this->key]]; $where=$this->scope($current,$params);
            $range=$rank<$old?'sort_no>=:rank AND sort_no<:old':'sort_no>:old AND sort_no<=:rank';
            $s=$this->db->prepare('UPDATE '.$this->table.' SET sort_no=sort_no'.($rank<$old?'+1':'-1').$this->stamp.' WHERE '.$where.' AND '.$range.' AND '.$this->key.'<>:id');
        }
        $s->execute($params); return $rank;
    }

    public function remove(array $ids, ?string $site = null): bool
    {
        $ids=array_values(array_unique(array_filter(array_map('intval',$ids),static function($id){return $id>0;})));
        if(!$ids)return false;
        return $this->locked(function()use($ids,$site){
            $params=$ids; $where=$this->key.' IN ('.implode(',',array_fill(0,count($ids),'?')).')';
            if($site!==null){ if($this->table!=='nb_board')throw new InvalidArgumentException('Invalid site scope'); $where.=' AND sitekey=?';$params[]=$site; }
            $s=$this->db->prepare('SELECT * FROM '.$this->table.' WHERE '.$where.' ORDER BY sort_no DESC, '.$this->key.' DESC');
            $s->execute($params);$rows=$s->fetchAll(PDO::FETCH_ASSOC); if(!$rows)return false;
            // Close each actual removed position, highest first, not N positions from MIN.
            foreach($rows as $row){
                $s=$this->db->prepare('DELETE FROM '.$this->table.' WHERE '.$this->key.'=?');
                $s->execute([(int)$row[$this->key]]);
                if($s->rowCount()===1)$this->close($row);
            }
            return true;
        });
    }
}
