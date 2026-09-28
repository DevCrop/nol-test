<?php

class Cors
{
    public static function apply(): void
    {
        if (headers_sent()) {
            return;
        }

        $origin = trim((string) ($_SERVER['HTTP_ORIGIN'] ?? ''));
        $allowed = self::allows($origin);
        $isOptions = strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? '')) === 'OPTIONS';

        if ($origin === '' || !$allowed) {
            if ($isOptions) {
                http_response_code(403);
                exit;
            }
            return;
        }

        header('Access-Control-Allow-Origin: ' . $origin);
        header('Vary: Origin');
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Allow-Headers: Content-Type, X-CSRF-Token');
        header('Access-Control-Allow-Methods: GET, POST, PATCH, DELETE, OPTIONS');
        header('Access-Control-Max-Age: 600');

        if ($isOptions) {
            http_response_code(204);
            exit;
        }
    }

    public static function allows(string $origin): bool
    {
        if ($origin === '' || !defined('CORS_ORIGINS') || CORS_ORIGINS === '') {
            return false;
        }
        $want = strtolower(rtrim($origin, '/'));
        foreach (explode(',', (string) CORS_ORIGINS) as $item) {
            $item = strtolower(rtrim(trim($item), '/'));
            if ($item !== '' && $item === $want) {
                return true;
            }
        }
        return false;
    }
}
