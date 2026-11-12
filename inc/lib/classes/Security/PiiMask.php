<?php
namespace Security;

final class PiiMask
{
    public static function identifier(string $value): string
    {
        $value = trim($value); $length = mb_strlen($value, 'UTF-8');
        if ($length === 0) return '';
        $visible = $length >= 4 ? 3 : max(0, $length - 1);
        return mb_substr($value, 0, $visible, 'UTF-8') . str_repeat('*', $length - $visible);
    }
    public static function name(string $value): string
    {
        $value = trim($value); $len = mb_strlen($value, 'UTF-8');
        if ($len === 0) return '';
        // Full-name fields do not identify surname order. Never expose a guessed surname.
        if (preg_match('/^[\p{Latin}\p{M} .\x27-]+$/u', $value)) {
            $parts = preg_split('/\s+/u', $value);
            foreach ($parts as $i => $part) {
                $length = mb_strlen($part, 'UTF-8');
                $parts[$i] = $length > 1 ? mb_substr($part, 0, 1, 'UTF-8') . str_repeat('*', $length - 1) : '*';
            }
            return implode(' ', $parts);
        }
        if ($len === 1) return '*';
        if ($len === 2) return mb_substr($value, 0, 1, 'UTF-8') . '*';
        return mb_substr($value, 0, 1, 'UTF-8') . str_repeat('*', $len - 2) . mb_substr($value, -1, 1, 'UTF-8');
    }

    public static function phone(string $value): string
    {
        $digits = (string) preg_replace('/\D+/', '', $value); $len = strlen($digits);
        if ($len < 7) return str_repeat('*', $len);
        $prefix = strpos($digits, '02') === 0 ? 2 : 3;
        if ($value !== '' && trim($value)[0] === '+') return str_repeat('*', $len - 4) . substr($digits, -4);
        return substr($digits, 0, $prefix) . '-' . str_repeat('*', $len - $prefix - 4) . '-' . substr($digits, -4);
    }

    public static function email(string $value): string
    {
        if (strpos($value, '@') === false) return self::name($value);
        [$local, $domain] = explode('@', trim($value), 2); $len = mb_strlen($local, 'UTF-8');
        $visible = $len >= 4 ? 3 : max(0, $len - 1);
        return mb_substr($local, 0, $visible, 'UTF-8') . str_repeat('*', max(1, $len - $visible)) . '@' . $domain;
    }
}
