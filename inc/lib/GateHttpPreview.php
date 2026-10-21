<?php

/** Isolated HTTP connectivity preview: no session, DB, credentials or authentication. */
final class GateHttpPreview
{
    public static function enabled(string $root): bool
    {
        $settings = [];
        foreach (['.env', '.env.gate'] as $name) {
            if (!is_readable($root . '/' . $name)) continue;
            foreach (file($root . '/' . $name, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
                if (preg_match('/^(GATE_HOST|GATE_HTTP_PREVIEW)=(.*)$/', trim($line), $m)) {
                    $settings[$m[1]] = trim(trim($m[2]), "\"'");
                }
            }
        }
        foreach (['GATE_HOST', 'GATE_HTTP_PREVIEW'] as $key) {
            if (getenv($key) !== false) $settings[$key] = getenv($key);
        }
        $host = strtolower(preg_replace('/:\d+$/', '', (string) ($_SERVER['HTTP_HOST'] ?? '')));
        // Keep preview isolated even when SSL becomes available: activation is a separate release.
        return $host !== '' && $host === strtolower($settings['GATE_HOST'] ?? '')
            && in_array(strtolower($settings['GATE_HTTP_PREVIEW'] ?? ''), ['true', '1', 'on'], true);
    }

    public static function render(string $root): void
    {
        header('Cache-Control: no-store');
        header('X-Content-Type-Options: nosniff');
        header("Content-Security-Policy: default-src 'self'; style-src 'self' 'unsafe-inline'; img-src 'self' data:; font-src 'self' data:; script-src 'none'; form-action 'none'; frame-ancestors 'none'; base-uri 'self'");
        $route = defined('PHP_GATE_ROUTE') ? PHP_GATE_ROUTE : '';
        if ($route !== '/index.php' || !in_array($_SERVER['REQUEST_METHOD'] ?? 'GET', ['GET', 'HEAD'], true)) {
            http_response_code(426);
            header('Content-Type: text/plain; charset=utf-8');
            echo 'HTTPS is required for administrator authentication and operations.';
            return;
        }
        header('Content-Type: text/html; charset=utf-8');
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'HEAD') readfile($root . '/inc/lib/gate-http-preview.html');
    }
}
