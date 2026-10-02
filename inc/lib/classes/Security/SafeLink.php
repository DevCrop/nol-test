<?php
namespace Security;

final class SafeLink
{
    public static function normalize(string $value): string
    {
        $value = trim($value);
        if ($value === '' || preg_match('/[\x00-\x20\x7f\\\\]/', $value)) return '';
        if ($value[0] === '/' && substr($value, 0, 2) !== '//') return $value;
        if ($value[0] === '#' || $value[0] === '?') return $value;
        $scheme = strtolower((string) parse_url($value, PHP_URL_SCHEME));
        return in_array($scheme, ['http', 'https'], true) && filter_var($value, FILTER_VALIDATE_URL) ? $value : '';
    }
}
