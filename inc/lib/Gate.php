<?php

class Gate
{
    public static function normalizeHost(string $host): string
    {
        return strtolower((string) preg_replace('/:\d+$/', '', $host));
    }

    public static function hostMatches(string $expected, string $current = ''): bool
    {
        if ($expected === '') {
            return false;
        }
        if ($current === '') {
            $current = (string) ($_SERVER['HTTP_HOST'] ?? '');
        }
        return self::normalizeHost($current) === self::normalizeHost($expected);
    }

    public static function isCurrent(): bool
    {
        return defined('GATE_HOST') && GATE_HOST !== '' && self::hostMatches(GATE_HOST);
    }

    public static function adminPathHit(string $dir, string $script, string $uri): bool
    {
        $dir = trim($dir, '/');
        if ($dir === '') {
            return false;
        }
        $haystack = $script . ' ' . $uri;
        return (bool) preg_match('#' . preg_quote('/' . $dir, '#') . '(?:/|\?|$)#', $haystack);
    }

    public static function shouldHide(bool $enforce, string $gateHost, string $httpHost, string $dir, string $script, string $uri): bool
    {
        if (!$enforce || $gateHost === '') {
            return false;
        }
        if (self::hostMatches($gateHost, $httpHost)) {
            return false;
        }
        return self::adminPathHit($dir, $script, $uri);
    }

    public static function shouldHideAdminDir(): bool
    {
        $enforce = defined('GATE_ENFORCE') && GATE_ENFORCE === true;
        $gateHost = defined('GATE_HOST') ? (string) GATE_HOST : '';
        $dir = defined('ADMIN_DIR') ? (string) ADMIN_DIR : 'nol-gate';
        return self::shouldHide(
            $enforce,
            $gateHost,
            (string) ($_SERVER['HTTP_HOST'] ?? ''),
            $dir,
            (string) ($_SERVER['SCRIPT_NAME'] ?? ''),
            (string) ($_SERVER['REQUEST_URI'] ?? '')
        );
    }
}
