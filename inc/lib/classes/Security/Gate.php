<?php
namespace Security;

final class Gate
{
    public static function enforce(): void
    {
        $host = strtolower(preg_replace('/:\d+$/', '', (string) ($_SERVER['HTTP_HOST'] ?? '')));
        $path = '/' . ltrim((string) parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH), '/');
        if (defined('PHP_GATE_ACTIVE') && defined('PHP_GATE_ROUTE')) $path = PHP_GATE_ROUTE;
        $internal = in_array(blue_env('GATE_ROUTE_MODE', 'path'), ['internal', 'php'], true);
        $script = (string) ($_SERVER['SCRIPT_NAME'] ?? '');
        $admin = $path === '/admin' || strpos($path, '/admin/') === 0 || ($internal && strpos($script, '/admin/') === 0);
        $gate = GATE_HOST !== '' && hash_equals(strtolower(GATE_HOST), $host);
        $legacy = $admin && !defined('PHP_GATE_ACTIVE') && self::allowsLegacyAdmin($host, $path);
        if ($gate && blue_env('GATE_ROUTE_MODE', 'path') === 'php' && $admin && !defined('PHP_GATE_ACTIVE')) self::deny(404);
        if (GATE_ENFORCE && $admin && !$gate && !$legacy) self::deny(404);
        if ($gate && !$internal && ($path === '/' || $path === '/index.php')) {
            if (!in_array($_SERVER['REQUEST_METHOD'] ?? 'GET', ['GET', 'HEAD'], true)) {
                http_response_code(405); header('Allow: GET, HEAD'); exit;
            }
            header('Location: /admin/', true, 302);
            exit;
        }
        if ($gate && !$admin && !self::shared($path)) self::deny(404);
        if ((!$gate && !$legacy) || !$admin) return;
        $rules = ClientIp::items(GATE_ALLOW_IPS);
        if ($rules === [] && APP_ENV !== 'production') return;
        if ($rules === [] || !ClientIp::allowed(ClientIp::get(), $rules)) self::deny(403);
    }

    /** Temporary HTTPS-only public-host admin access; all authentication/IP guards remain. */
    private static function allowsLegacyAdmin(string $host, string $path): bool
    {
        return blue_env('GATE_ALLOW_LEGACY_ADMIN', false) === true
            && PUBLIC_HOST !== '' && hash_equals(strtolower(PUBLIC_HOST), $host)
            && ($path === '/admin' || strpos($path, '/admin/') === 0)
            && TransportSecurity::isSecure($_SERVER, TRUSTED_PROXY_CIDRS);
    }

    private static function shared(string $path): bool
    {
        return strpos($path, '/resource/') === 0 || strpos($path, '/uploads/') === 0 || strpos($path, '/inc/lib/captcha.') === 0;
    }

    private static function deny(int $status): void
    {
        http_response_code($status);
        header('Content-Type: text/plain; charset=utf-8');
        echo $status === 403 ? 'Forbidden' : 'Not Found';
        exit;
    }
}
