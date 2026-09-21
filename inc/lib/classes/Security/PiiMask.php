<?php
namespace Security;

final class PiiMask
{
    public static function name(string $value): string
    {
        $value = trim($value); $len = mb_strlen($value, 'UTF-8');
        if ($len === 0) return '';
        if (preg_match('/^[A-Za-z .\x27-]+$/', $value) && strpos($value, ' ') !== false) {
            $parts = preg_split('/\s+/', $value);
            $surname = array_pop($parts);
            foreach ($parts as $i => $part) $parts[$i] = ($i === 0 ? substr($part, 0, 1) : '') . str_repeat('*', strlen($part) - ($i === 0 ? 1 : 0));
            return implode(' ', $parts) . ' ' . $surname;
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
