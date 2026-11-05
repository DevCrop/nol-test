<?php
/** Match the public page's enabled flag and inclusive calendar-day display period. */
final class DisplayStatus
{
    public static function expression(string $alias,string $kind): string
    {
        if(!in_array($alias,['b','p'],true)||!in_array($kind,['banner','popup'],true))throw new InvalidArgumentException('Invalid display source');
        if($kind==='popup')return "({$alias}.is_active=1 AND ({$alias}.is_unlimited=1 OR ({$alias}.is_unlimited=0 AND ({$alias}.start_at IS NULL OR {$alias}.start_at<=NOW()) AND ({$alias}.end_at IS NULL OR {$alias}.end_at>=NOW()))))";
        $start=$kind==='banner'?'display_start_at':'start_at';$end=$kind==='banner'?'display_end_at':'end_at';
        return "({$alias}.is_active=1 AND ({$alias}.is_unlimited=1 OR ({$alias}.{$start} IS NOT NULL AND {$alias}.{$end} IS NOT NULL AND CURDATE() BETWEEN DATE({$alias}.{$start}) AND DATE({$alias}.{$end}))))";
    }
    public static function label(array $row,string $kind): string
    {
        if(!(int)($row['is_active']??0))return '비활성화';
        if(!empty($row['effective_active']))return '활성화';
        $start=$kind==='banner'?'display_start_at':'start_at';$end=$kind==='banner'?'display_end_at':'end_at';
        $today=(string)$row['display_today'];
        $endValue=$kind==='banner'?substr((string)($row[$end]??''),0,10):(string)($row[$end]??'');
        $startValue=$kind==='banner'?substr((string)($row[$start]??''),0,10):(string)($row[$start]??'');
        if($endValue!=='' && $endValue<$today)return '기간 종료';
        if($startValue!=='' && $startValue>$today)return '노출 예정';
        return '비활성화';
    }
}
