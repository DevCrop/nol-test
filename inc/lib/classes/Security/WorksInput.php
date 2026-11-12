<?php
namespace Security;
final class WorksInput
{
    public const IMAGES=['thumbnail_image','tablet_image','poster_image','detail_page_top_image','mobile_poster_image'];
    private const FIELDS=['title','genre','start_date','end_date','show_time','place','is_featured','notes','contents','price','running_time','viewing_age','contact','opera_glass_link','ticket_link','subtitle','created_at'];
    public static function id($value): int
    {
        if(!is_scalar($value)||!preg_match('/^[1-9][0-9]*$/D',(string)$value)||filter_var($value,FILTER_VALIDATE_INT)===false)throw new \RuntimeException('올바른 공연 번호를 선택하세요.');
        return (int)$value;
    }
    public static function fields(array $input): array
    {
        $out=[];
        foreach(self::FIELDS as $key){if(!array_key_exists($key,$input))continue;if(!is_scalar($input[$key])&&$input[$key]!==null)throw new \RuntimeException('입력 형식이 올바르지 않습니다.');$out[$key]=$input[$key]===null||$input[$key]===''?null:(string)$input[$key];}
        foreach(['title'=>'제목','genre'=>'장르','start_date'=>'공연 시작일','end_date'=>'공연 종료일']as $key=>$label)if(trim((string)($out[$key]??''))==='')throw new \RuntimeException($label.'란을 입력해주세요.');
        foreach(['start_date','end_date']as $key){$date=\DateTimeImmutable::createFromFormat('!Y-m-d',(string)$out[$key]);if(!$date||$date->format('Y-m-d')!==$out[$key])throw new \RuntimeException('공연 날짜를 YYYY-MM-DD 형식으로 입력하세요.');}
        if($out['end_date']<$out['start_date'])throw new \RuntimeException('공연 종료일은 시작일보다 빠를 수 없습니다.');
        $out['is_featured']=in_array(strtolower((string)($out['is_featured']??'0')),['1','on','yes','true'],true)?1:0;
        if(array_key_exists('created_at',$out)){
            if($out['created_at']===null)unset($out['created_at']);
            else{$raw=trim($out['created_at']);if(preg_match('/^\d{4}-\d{2}-\d{2}$/D',$raw))$raw.=' 00:00:00';$date=\DateTimeImmutable::createFromFormat('!Y-m-d H:i:s',$raw);if(!$date||$date->format('Y-m-d H:i:s')!==$raw)throw new \RuntimeException('생성 날짜 형식을 확인하세요.');$out['created_at']=$raw;}
        }
        return $out;
    }
}
