<?php
namespace Security;

/** Read-only, site-scoped log queries. No schema changes or log retention policy. */
final class AdminLogReader
{
    private \PDO $db;
    private string $kind;
    public function __construct(\PDO $db, string $kind)
    {
        if (!in_array($kind, ['audit', 'access'], true)) throw new \InvalidArgumentException('지원하지 않는 기록입니다.');
        $this->db = $db; $this->kind = $kind;
    }
    public static function actions(string $kind): array
    {
        return $kind === 'audit' ? ['create'=>'생성','update'=>'수정','delete'=>'삭제'] : ['view'=>'열람','download'=>'다운로드','create'=>'입력','update'=>'변경','delete'=>'삭제'];
    }
    public static function entities(string $kind): array
    {
        return $kind === 'audit' ? ['account'=>'관리자 계정·권한','nb_board'=>'게시판','board_upload'=>'게시판 이미지','nb_board_manage'=>'게시판 설정','nb_board_category'=>'게시판 분류','nb_banner'=>'배너','nb_popup'=>'팝업','nb_works'=>"What's ON",'nb_request'=>'대관신청','nb_request_manage'=>'대관 일정','nb_opera'=>'오페라글라스','nb_siteinfo'=>'사이트 정보','nb_data'=>'사이트 데이터'] : ['admin_account'=>'관리자 계정','request'=>'대관신청','board'=>'게시판 작성자'];
    }
    public function read(array $input): array
    {
        $filters=['from'=>'','to'=>'','entity'=>'','action'=>'','q'=>'','perpage'=>20]; $errors=[];
        foreach (['from','to','entity','action','q'] as $key) {
            if (isset($input[$key]) && !is_scalar($input[$key])) { $errors[]='검색 조건의 형식을 확인하세요.'; continue; }
            $filters[$key]=trim((string)($input[$key]??''));
        }
        $filters['q']=mb_substr($filters['q'],0,200,'UTF-8');
        $size=isset($input['perpage'])&&is_scalar($input['perpage'])?filter_var($input['perpage'],FILTER_VALIDATE_INT):false;
        if (in_array($size,[20,50,100],true)) $filters['perpage']=$size;
        $page=isset($input['page'])&&is_scalar($input['page'])?filter_var($input['page'],FILTER_VALIDATE_INT):1;
        $page=$page===false||$page<1?1:$page;
        foreach (['from','to'] as $key) if ($filters[$key]!=='') {
            $date=\DateTimeImmutable::createFromFormat('!Y-m-d',$filters[$key]);
            if (!$date||$date->format('Y-m-d')!==$filters[$key]||substr($filters[$key],0,4)<'1000'||substr($filters[$key],0,4)>'9998') $errors[]='조회 기간은 올바른 날짜(YYYY-MM-DD)로 입력하세요.';
        }
        if (!$errors && $filters['from']!=='' && $filters['to']!=='' && $filters['from']>$filters['to']) $errors[]='조회 종료일은 시작일보다 빠를 수 없습니다.';
        if ($filters['action']!=='' && !array_key_exists($filters['action'],self::actions($this->kind))) $errors[]='조회 유형을 확인하세요.';
        if ($filters['entity']!=='' && !array_key_exists($filters['entity'],self::entities($this->kind))) $errors[]='조회 대상을 확인하세요.';
        $result=['filters'=>$filters,'errors'=>array_values(array_unique($errors)),'rows'=>[],'total'=>0,'page'=>1,'pages'=>1,'start'=>0,'end'=>0];
        if ($errors) return $result;
        $table=$this->kind==='audit'?'nb_admin_audit':'nb_admin_privacy_access';
        $where=['sitekey = :site'];$params=['site'=>'BLUESQ'];
        foreach (['entity','action'] as $key) if ($filters[$key]!=='') { $where[]=$key.' = :'.$key; $params[$key]=$filters[$key]; }
        if ($filters['from']!=='') { $where[]='created_at >= :from_date'; $params['from_date']=$filters['from'].' 00:00:00'; }
        if ($filters['to']!=='') { $where[]='created_at < :after_date'; $params['after_date']=(new \DateTimeImmutable($filters['to']))->modify('+1 day')->format('Y-m-d').' 00:00:00'; }
        if ($filters['q']!=='') {
            $columns=$this->kind==='audit'?['actor_uid','actor_ip','target_label','detail_json']:['actor_uid','actor_ip','subject_label','task','reason'];
            $parts=[];$needle='%'.strtr($filters['q'],['!'=>'!!','%'=>'!%','_'=>'!_']).'%';
            foreach ($columns as $i=>$column) { $parts[]=$column." LIKE :q".$i." ESCAPE '!'"; $params['q'.$i]=$needle; }
            $where[]='('.implode(' OR ',$parts).')';
        }
        $sqlWhere=implode(' AND ',$where);$count=$this->db->prepare('SELECT COUNT(*) FROM '.$table.' WHERE '.$sqlWhere);$count->execute($params);
        $total=(int)$count->fetchColumn();$pages=max(1,(int)ceil($total/$filters['perpage']));$page=min($page,$pages);$offset=($page-1)*$filters['perpage'];
        $columns=$this->kind==='audit'?'no, created_at, actor_uid, actor_ip, action, entity, target_no, target_label, detail_json':'no, created_at, actor_uid, actor_ip, action, entity, target_no, subject_label, task, reason';
        $stmt=$this->db->prepare('SELECT '.$columns.' FROM '.$table.' WHERE '.$sqlWhere.' ORDER BY no DESC LIMIT :take OFFSET :skip');
        foreach ($params as $key=>$value) $stmt->bindValue(':'.$key,$value,\PDO::PARAM_STR);
        $stmt->bindValue(':take',$filters['perpage'],\PDO::PARAM_INT);$stmt->bindValue(':skip',$offset,\PDO::PARAM_INT);$stmt->execute();
        return array_replace($result,['rows'=>$stmt->fetchAll(\PDO::FETCH_ASSOC),'total'=>$total,'page'=>$page,'pages'=>$pages,'start'=>$total?$offset+1:0,'end'=>min($offset+$filters['perpage'],$total)]);
    }
}
