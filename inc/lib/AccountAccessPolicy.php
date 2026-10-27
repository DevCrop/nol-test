<?php
/** Shared account-validity and governance-log policy; contains no credential material. */
final class AccountAccessPolicy
{
    private static ?array $context = null;
    public static function reason(array $input): string
    {
        if(!is_string($input['change_reason']??null))throw new RuntimeException('변경 사유를 입력하세요.');
        $reason=trim((string)($input['change_reason']??''));
        if(mb_strlen($reason)<2 || mb_strlen($reason)>300 || preg_match('/[\x00-\x08\x0b\x0c\x0e-\x1f]/u',$reason))throw new RuntimeException('변경 사유를 2~300자로 입력하세요.');
        return $reason;
    }
    public static function period(array $input,array $current=[]): array
    {
        $out=[];
        foreach(['authority_valid_from','authority_valid_until'] as $key){
            if(!array_key_exists($key,$input)){$out[$key]=$current[$key]??null;continue;}
            if(!is_string($input[$key]) && $input[$key]!==null)throw new RuntimeException('권한 유효기간 형식이 올바르지 않습니다.');
            $value=trim((string)$input[$key]);if($value===''){$out[$key]=null;continue;}
            $format=strpos($value,'T')!==false?'!Y-m-d\TH:i':'!Y-m-d H:i:s';
            $date=DateTimeImmutable::createFromFormat($format,$value,new DateTimeZone('Asia/Seoul'));
            $errors=DateTimeImmutable::getLastErrors();
            if(!$date || ($errors && ($errors['warning_count']||$errors['error_count'])) || $date->format(substr($format,1))!==$value)throw new RuntimeException('권한 유효기간을 올바른 날짜와 시간으로 입력하세요.');
            $out[$key]=$date->format('Y-m-d H:i:s');
        }
        if($out['authority_valid_from']!==null && $out['authority_valid_until']!==null && $out['authority_valid_from'] >= $out['authority_valid_until'])throw new RuntimeException('권한 종료일은 시작일보다 뒤여야 합니다.');
        return $out;
    }
    public static function allowed(array $row,?int $now=null): bool
    {
        $now=$now??time();
        foreach(['authority_valid_from','authority_valid_until'] as $key){
            $value=$row[$key]??null;if($value===null||$value==='')continue;
            $stamp=self::stamp((string)$value);
            if($stamp===null || ($key==='authority_valid_from'?$now<$stamp:$now>=$stamp))return false;
        }
        return true;
    }
    public static function assertAllowed(array $row): void
    {
        if(!self::allowed($row))throw new RuntimeException('계정 권한 유효기간이 아닙니다. 최고 관리자에게 문의하세요.');
    }
    public static function periodLabel(array $row): ?string
    {
        if(self::allowed($row))return null;
        return !empty($row['authority_valid_from']) && (self::stamp((string)$row['authority_valid_from'])??0)>time()?'사용 예정':'기간 만료';
    }
    public static function now(): string {return (new DateTimeImmutable('now',new DateTimeZone('Asia/Seoul')))->format('Y-m-d H:i:s');}
    private static function stamp(string $value): ?int
    {
        $date=DateTimeImmutable::createFromFormat('!Y-m-d H:i:s',$value,new DateTimeZone('Asia/Seoul'));
        $errors=DateTimeImmutable::getLastErrors();
        if(!$date || ($errors && ($errors['warning_count']||$errors['error_count'])) || $date->format('Y-m-d H:i:s')!==$value)return null;
        return $date->getTimestamp();
    }
    public static function version(array $row): string
    {
        return hash('sha256',json_encode([(int)($row['no']??0),(string)($row['uid']??''),(string)($row['email']??''),(string)($row['role_id']??$row['role_code']??''),(string)($row['active_status']??''),$row['authority_valid_from']??null,$row['authority_valid_until']??null]));
    }
    public static function beginAudit(string $reason,array $before): void {self::$context=['reason'=>$reason,'before'=>$before];}
    public static function audit(int $id,array $after): array
    {
        if(self::$context===null)return [];
        return ['change_reason'=>self::$context['reason'],'authority_before'=>self::snapshot(self::$context['before'][$id]??[]),'authority_after'=>self::snapshot($after)];
    }
    public static function snapshot(array $row): array
    {
        if(!$row)return [];
        return ['account_no'=>(int)($row['no']??0),'account_identifier'=>(string)($row['uid']??''),'owner_display_name'=>(string)($row['uname']??''),'role'=>$row['role_id']??$row['role_code']??null,'permissions'=>$row['_grants']??[],'active_status'=>$row['active_status']??null,'authority_valid_from'=>$row['authority_valid_from']??null,'authority_valid_until'=>$row['authority_valid_until']??null];
    }
    public static function auditLines(array $detail,callable $maskId,callable $maskName): array
    {
        if(!isset($detail['change_reason']))return [];
        $lines=['변경 사유: '.(string)$detail['change_reason']];
        foreach(['authority_before'=>'변경 전','authority_after'=>'변경 후'] as $key=>$label){
            $state=$detail[$key]??[];
            if(!$state){$lines[]=$label.': 없음';continue;}
            $role=in_array((string)($state['role']??''),['1','super'],true)?'최고 관리자':'일반 관리자';
            $lines[]=$label.': '.$maskId((string)($state['account_identifier']??'')).' / '.$maskName((string)($state['owner_display_name']??'')).' / '.$role.' / '.(($state['active_status']??'')==='Y'?'활성':'비활성');
            $lines[]=$label.' 유효기간: '.($state['authority_valid_from']??'시작 제한 없음').' ~ '.($state['authority_valid_until']??'종료 제한 없음');
            foreach(($state['permissions']??[]) as $menu=>$flags){
                $grants=[];foreach(['view'=>'조회','create'=>'생성','update'=>'수정','delete'=>'삭제'] as $flag=>$title)if(!empty($flags[$flag]))$grants[]=$title;
                if($grants)$lines[]=$label.' 메뉴 '.$menu.': '.implode(', ',$grants);
            }
        }
        return $lines;
    }
}
