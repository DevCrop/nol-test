<?php
namespace Security;

final class Runtime
{
    public static function boot(): void
    {
        self::cors(); self::https(); Gate::enforce(); Csrf::validateRequest();
    }

    private static function https(): void
    {
        if (APP_ENV !== 'production') return;
        if (TransportSecurity::isSecure($_SERVER, TRUSTED_PROXY_CIDRS)
            || TransportSecurity::allowsTemporaryHttp($_SERVER, GATE_HOST,
                (bool) blue_env('GATE_ALLOW_HTTP', false), TRUSTED_PROXY_CIDRS)) return;
        $host = (string) ($_SERVER['HTTP_HOST'] ?? '');
        if ($host !== '' && !headers_sent()) {
            header('Location: https://' . $host . ($_SERVER['REQUEST_URI'] ?? '/'), true, 302); exit;
        }
    }

    private static function cors(): void
    {
        $origin = (string) ($_SERVER['HTTP_ORIGIN'] ?? '');
        if ($origin === '') return;
        $allowed = array_filter(array_map('trim', explode(',', (string) blue_env('CORS_ORIGINS', ''))));
        if (in_array($origin, $allowed, true)) {
            header('Access-Control-Allow-Origin: ' . $origin);
            header('Vary: Origin'); header('Access-Control-Allow-Credentials: true');
        }
    }
}
