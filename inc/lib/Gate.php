<?php

class Gate
{
    /** Shared document root uses real admin URLs; dedicated vhosts keep root URLs. */
    public static function usesSharedRoot(): bool
    {
        return function_exists('env') && env('GATE_ROUTE_MODE', 'root') === 'path';
    }

    public static function routeSharedRoot(): void
    {
        if (PHP_SAPI === 'cli' || !self::usesSharedRoot() || !self::isCurrent()) return;
        $path = (string) parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
        $dir = trim((string) ADMIN_DIR, '/');
        // Never turn configuration or user input into an include path.
        if (!preg_match('/^[a-zA-Z0-9_-]+$/D', $dir)) {
            http_response_code(503); echo 'Service Unavailable'; exit;
        }
        if ($path === '/' || $path === '/index.php') {
            if (!in_array($_SERVER['REQUEST_METHOD'] ?? 'GET', ['GET', 'HEAD'], true)) {
                http_response_code(405); header('Allow: GET, HEAD'); exit;
            }
            header('Location: /' . $dir . '/', true, 302); exit;
        }
        if (self::adminPathHit($dir, '', $path)
            || preg_match('#^/(?:resource|captcha|uploads)(?:/|$)#', $path)) return;
        http_response_code(404); echo 'Not Found'; exit;
    }

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
        // Explicit temporary fallback on the public HTTPS host only. IP, MFA,
        // session, ACL and CSRF guards still run in the normal admin bootstrap.
        if (self::allowsLegacyAdmin()) return false;
        if (function_exists('env') && env('GATE_ROUTE_MODE', 'root') === 'php' && !defined('PHP_GATE_ACTIVE')
            && self::adminPathHit(defined('ADMIN_DIR') ? ADMIN_DIR : 'nol-gate', (string) ($_SERVER['SCRIPT_NAME'] ?? ''), (string) ($_SERVER['REQUEST_URI'] ?? ''))) return true;
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

    private static function allowsLegacyAdmin(): bool
    {
        if (!function_exists('env') || env('GATE_ALLOW_LEGACY_ADMIN', false) !== true
            || defined('PHP_GATE_ACTIVE') || !defined('PUBLIC_HOST')
            || !self::hostMatches((string) PUBLIC_HOST)) return false;
        $dir = defined('ADMIN_DIR') ? (string) ADMIN_DIR : 'nol-gate';
        if (!preg_match('/^[a-zA-Z0-9_-]+$/D', $dir)) return false;
        $path = (string) parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
        if (!preg_match('#^/' . preg_quote($dir, '#') . '(?:/|$)#D', $path)) return false;
        return \Http\TransportSecurity::isSecure($_SERVER, (string) env('TRUSTED_PROXY_CIDRS', ''));
    }
}
