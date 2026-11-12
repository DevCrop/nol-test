<?php
namespace Security;

/** Presentation only: keep arbitrary JSON and sensitive legacy payloads out of the UI. */
final class AdminLogView
{
    public static function entity(string $kind,string $entity): string { return AdminLogReader::entities($kind)[$entity]??'기타 작업'; }
    public static function action(string $kind,string $action): string { return AdminLogReader::actions($kind)[$action]??'기타'; }
    public static function badge(string $action): string { return in_array($action,['create','update','delete','view','download'],true)?$action:'other'; }
    public static function details(array $row): array
    {
        $detail=json_decode((string)($row['detail_json']??''),true);
        if (!is_array($detail)) return [];
        if (($row['entity']??'')==='account') {
            $lines=\AccountAccessPolicy::auditLines($detail,[PiiMask::class,'identifier'],[PiiMask::class,'name']);
            if ($lines) {
                foreach ($lines as &$line) foreach (AdminAcl::labels() as $key=>$label) $line=str_replace(' 메뉴 '.$key.':',' 메뉴 '.$label.':',$line);
                unset($line); return $lines;
            }
        }
        $lines=[];
        if (isset($detail['target_ids'])&&is_array($detail['target_ids'])) { $ids=array_values(array_filter($detail['target_ids'],static function($id):bool{return is_int($id)&&$id>0;})); if($ids)$lines[]='대상 번호: '.implode(', ',array_slice($ids,0,100)); }
        if (isset($detail['affected_rows'])&&is_numeric($detail['affected_rows'])) $lines[]='처리 건수: '.max(0,(int)$detail['affected_rows']).'건';
        foreach (['password_reset'=>'임시 비밀번호 재발급','password_changed'=>'본인 비밀번호 변경','unlocked'=>'계정 잠금 해제'] as $key=>$label) if (!empty($detail[$key])) $lines[]=$label;
        if (isset($detail['role_code'])&&is_string($detail['role_code'])) $lines[]='권한: '.($detail['role_code']==='super'?'최고 관리자':'일반 관리자');
        if (isset($detail['active_status'])&&is_string($detail['active_status'])) $lines[]='상태: '.($detail['active_status']==='Y'?'활성':'비활성');
        if (isset($detail['change_reason'])&&is_scalar($detail['change_reason'])) $lines[]='변경 사유: '.mb_substr((string)$detail['change_reason'],0,300,'UTF-8');
        return $lines;
    }
    public static function summary(array $row): string
    {
        $special=self::details($row);
        return $special[0]??(self::entity('audit',(string)$row['entity']).' '.self::action('audit',(string)$row['action']).' 처리');
    }
    public static function pageUrl(string $path,array $filters,int $page): string { return $path.'?'.http_build_query(array_merge($filters,['page'=>$page])); }
}
