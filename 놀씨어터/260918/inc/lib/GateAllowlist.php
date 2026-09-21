<?php

class GateAllowlist
{
    public static function rulesFrom(string $raw): array
    {
        $out = [];
        foreach (explode(',', $raw) as $part) {
            $rule = strtolower(trim($part));
            if ($rule === '') {
                continue;
            }
            if (self::validRule($rule)) {
                $out[] = $rule;
            }
        }
        return $out;
    }

    public static function allows(string $ip, array $rules): bool
    {
        if ($rules === []) {
            return true;
        }
        $ip = strtolower(trim($ip));
        if ($ip === '' || $ip === 'unknown') {
            return false;
        }
        foreach ($rules as $rule) {
            if (self::match($ip, $rule)) {
                return true;
            }
        }
        return false;
    }

    public static function clientIp(): string
    {
        return trim((string) ($_SERVER['REMOTE_ADDR'] ?? ''));
    }

    public static function shouldEnforce(): bool
    {
        if (!defined('GATE_ALLOW_IPS')) {
            return false;
        }
        if (self::rulesFrom((string) GATE_ALLOW_IPS) === []) {
            return false;
        }
        if (class_exists('Gate') && Gate::isCurrent()) {
            return true;
        }
        $dir = defined('ADMIN_DIR') ? (string) ADMIN_DIR : 'nol-gate';
        return Gate::adminPathHit(
            $dir,
            (string) ($_SERVER['SCRIPT_NAME'] ?? ''),
            (string) ($_SERVER['REQUEST_URI'] ?? '')
        );
    }

    public static function enforce(): void
    {
        if (PHP_SAPI === 'cli' || !self::shouldEnforce()) {
            return;
        }
        $rules = self::rulesFrom((string) GATE_ALLOW_IPS);
        if (self::allows(self::clientIp(), $rules)) {
            return;
        }
        if (!headers_sent()) {
            http_response_code(403);
            header('Content-Type: text/plain; charset=utf-8');
        }
        echo 'Forbidden';
        exit;
    }

    private static function validRule(string $rule): bool
    {
        if (strpos($rule, '/') === false) {
            return filter_var($rule, FILTER_VALIDATE_IP) !== false;
        }
        [$net, $bits] = explode('/', $rule, 2);
        if (filter_var($net, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) === false) {
            return false;
        }
        $mask = (int) $bits;
        return $mask >= 0 && $mask <= 32;
    }

    private static function match(string $ip, string $rule): bool
    {
        if (strpos($rule, '/') === false) {
            return $ip === $rule;
        }
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) === false) {
            return false;
        }
        [$net, $bits] = explode('/', $rule, 2);
        $bits = (int) $bits;
        $ipLong = ip2long($ip);
        $netLong = ip2long($net);
        if ($ipLong === false || $netLong === false) {
            return false;
        }
        $ipLong = $ipLong & 0xFFFFFFFF;
        $netLong = $netLong & 0xFFFFFFFF;
        $mask = $bits === 0 ? 0 : ((~((1 << (32 - $bits)) - 1)) & 0xFFFFFFFF);
        return ($ipLong & $mask) === ($netLong & $mask);
    }
}
