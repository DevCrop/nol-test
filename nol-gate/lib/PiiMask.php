<?php

class PiiMask
{
    public static function identifier(string $value): string
    {
        $chars = preg_split('//u', trim($value), -1, PREG_SPLIT_NO_EMPTY) ?: []; $length = count($chars);
        if ($length === 0) return '';
        $visible = $length >= 4 ? 3 : max(0, $length - 1);
        return implode('', array_slice($chars, 0, $visible)) . str_repeat('*', $length - $visible);
    }
    public static function name(string $value): string
    {
        $value = trim($value);
        if ($value === '') {
            return '';
        }
        if (preg_match('/^[A-Za-z .\x27-]+$/', $value) && strpos($value, ' ') !== false) {
            $parts = preg_split('/\s+/', $value); $surname = array_pop($parts);
            foreach ($parts as $i => $part) $parts[$i] = ($i === 0 ? substr($part, 0, 1) : '') . str_repeat('*', strlen($part) - ($i === 0 ? 1 : 0));
            return implode(' ', $parts) . ' ' . $surname;
        }
        $len = function_exists('mb_strlen') ? mb_strlen($value, 'UTF-8') : strlen($value);
        $ch = static function (int $i) use ($value): string {
            return function_exists('mb_substr')
                ? (string) mb_substr($value, $i, 1, 'UTF-8')
                : (string) substr($value, $i, 1);
        };
        if ($len === 1) {
            return '*';
        }
        if ($len === 2) {
            return $ch(0) . '*';
        }
        return $ch(0) . str_repeat('*', $len - 2) . $ch($len - 1);
    }

    public static function phone(string $value): string
    {
        $digits = preg_replace('/\D+/', '', $value);
        if ($digits === '') {
            return '';
        }
        $len = strlen($digits);
        if ($len < 7) {
            return str_repeat('*', $len);
        }
        if (strpos($digits, '02') === 0) return '02-' . str_repeat('*', $len - 6) . '-' . substr($digits, -4);
        if (strpos(trim($value), '+') === 0) return str_repeat('*', $len - 4) . substr($digits, -4);
        $head = substr($digits, 0, 3);
        $tail = substr($digits, -4);
        $mid = str_repeat('*', max(0, $len - 7));
        if ($len === 11) {
            return $head . '-' . substr($mid . '****', 0, 4) . '-' . $tail;
        }
        if ($len === 10) {
            return $head . '-' . substr($mid . '***', 0, 3) . '-' . $tail;
        }
        return $head . '-' . $mid . '-' . $tail;
    }

    public static function email(string $value): string
    {
        $value = trim($value);
        if ($value === '' || strpos($value, '@') === false) {
            return $value === '' ? '' : self::name($value);
        }
        [$local, $domain] = explode('@', $value, 2);
        $length = function_exists('mb_strlen') ? mb_strlen($local, 'UTF-8') : strlen($local);
        $visible = $length >= 4 ? 3 : max(0, $length - 1);
        $head = function_exists('mb_substr')
            ? (string) mb_substr($local, 0, $visible, 'UTF-8')
            : substr($local, 0, $visible);
        return $head . str_repeat('*', max(1, $length - $visible)) . '@' . $domain;
    }
}
