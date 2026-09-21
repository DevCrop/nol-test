<?php
namespace Security;

final class Gate
{
    public static function enforce(): void
    {
        $host = strtolower(preg_replace('/:\d+$/', '', (string) ($_SERVER['HTTP_HOST'] ?? '')));
        $path = '/' . ltrim((string) parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH), '/');
        $admin = $path === '/admin' || strpos($path, '/admin/') === 0;
        $gate = GATE_HOST !== '' && hash_equals(strtolower(GATE_HOST), $host);
        if (GATE_ENFORCE && $admin && !$gate) self::deny(404);
        if ($gate && $path === '/') {
            header('Location: /admin/', true, 302);
            exit;
        }
        if ($gate && !$admin && !self::shared($path)) self::deny(404);
        if (!$gate || !$admin) return;
        $rules = ClientIp::items(GATE_ALLOW_IPS);
        if ($rules === [] && APP_ENV !== 'production') return;
        if ($rules === [] || !ClientIp::allowed(ClientIp::get(), $rules)) self::deny(403);
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
