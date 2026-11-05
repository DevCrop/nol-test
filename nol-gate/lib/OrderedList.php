<?php
/** Serialize ordering requests and derive positions from current server state. */
final class OrderedList
{
    private PDO $db;
    public function __construct(PDO $db) {$this->db=$db;}
    public function move(string $table,int $id,string $action='',?int $position=null): array
    {
        $columns=['nb_banners'=>'sort_no','nb_popups'=>'sort_no','nb_works'=>'sort_order'];
        if(!isset($columns[$table])||$id<1||!in_array($action,['','up','down','first','last'],true))throw new RuntimeException('잘못된 정렬 요청입니다.');
        $column=$columns[$table];$lock='nol-order-'.substr(hash('sha256',(string)$this->db->query('SELECT DATABASE()')->fetchColumn().$table),0,32);
        $st=$this->db->prepare('SELECT GET_LOCK(?,5)');$st->execute([$lock]);
        if((int)$st->fetchColumn()!==1)throw new RuntimeException('정렬 처리 중입니다. 잠시 후 다시 시도하세요.');
        try{
            $st=$this->db->prepare("SELECT * FROM {$table} WHERE id=?");$st->execute([$id]);$target=$st->fetch(PDO::FETCH_ASSOC);
            if(!$target)throw new RuntimeException('존재하지 않는 항목입니다.');
            $where=$table==='nb_popups'?' WHERE popup_path=?':'';
            $st=$this->db->prepare("SELECT id,{$column} AS position FROM {$table}{$where} ORDER BY {$column} ASC,id DESC");
            $st->execute($where!==''?[$target['popup_path']]:[]);$rows=$st->fetchAll(PDO::FETCH_ASSOC);
            $ids=array_map('intval',array_column($rows,'id'));$old=array_search($id,$ids,true);$last=count($ids)-1;
            if($old===false)throw new RuntimeException('존재하지 않는 항목입니다.');
            if($action==='') {
                if($position===null||$position<1||$position>count($ids))throw new RuntimeException('잘못된 순서 번호입니다.');
                $next=$position-1;
            } else $next=['up'=>max(0,$old-1),'down'=>min($last,$old+1),'first'=>0,'last'=>$last][$action];
            array_splice($ids,$old,1);array_splice($ids,$next,0,[$id]);
            $cases=[];$values=[];
            foreach($ids as $index=>$rowId){$cases[]='WHEN ? THEN ?';$values[]=$rowId;$values[]=$index+1;}
            $marks=implode(',',array_fill(0,count($ids),'?'));
            $st=$this->db->prepare("UPDATE {$table} SET {$column}=CASE id ".implode(' ',$cases)." END WHERE id IN ({$marks})");
            $st->execute(array_merge($values,$ids));
            return ['changed'=>$st->rowCount()>0,'position'=>$next+1,'target'=>$target];
        }finally{$st=$this->db->prepare('SELECT RELEASE_LOCK(?)');$st->execute([$lock]);}
    }
}
