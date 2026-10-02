<?php
namespace Security;

final class BannerInput
{
    public static function validate(array $input): array
    {
        foreach (['b_target','b_link','b_none_limit','b_none_view','b_sdate','b_edate','b_sdate_view','b_edate_view'] as $key) {
            if (isset($input[$key]) && !is_scalar($input[$key])) throw new \InvalidArgumentException('배너 입력값을 확인하세요.');
        }
        $target = (string) ($input['b_target'] ?? '_none');
        if (!in_array($target, ['_none','_self','_blank'], true)) throw new \InvalidArgumentException('링크 형식을 확인하세요.');
        $raw = trim((string) ($input['b_link'] ?? ''));
        $link = $target === '_none' ? '' : SafeLink::normalize($raw);
        if ($target !== '_none' && $raw !== '' && $link === '') throw new \InvalidArgumentException('HTTP(S) 또는 사이트 내부 링크를 입력하세요.');
        $result = ['b_target'=>$target, 'b_link'=>$link];
        foreach ([['b_none_limit','b_sdate','b_edate'], ['b_none_view','b_sdate_view','b_edate_view']] as [$flag,$start,$end]) {
            $value = (string) ($input[$flag] ?? 'N');
            if (!in_array($value, ['Y','N'], true)) throw new \InvalidArgumentException('기간 설정을 확인하세요.');
            $result[$flag] = $value;
            if ($value === 'Y') { $result[$start] = null; $result[$end] = null; continue; }
            $a = self::date((string) ($input[$start] ?? ''));
            $b = self::date((string) ($input[$end] ?? ''));
            if ($a === null || $b === null || $a > $b) throw new \InvalidArgumentException('올바른 시작일과 종료일을 입력하세요.');
            $result[$start] = $a; $result[$end] = $b;
        }
        return $result;
    }

    private static function date(string $value): ?string
    {
        if (!preg_match('/^(\d{4})-(\d{2})-(\d{2})$/D', $value, $parts)
            || !checkdate((int)$parts[2], (int)$parts[3], (int)$parts[1])) return null;
        return $value;
    }
}
