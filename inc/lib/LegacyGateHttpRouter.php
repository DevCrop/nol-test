<?php

/** Temporary gate screen routing only; does not run or replace legacy authentication. */
final class LegacyGateHttpRouter
{
    public static function dispatch(string $root, string $gateHost = 'gate.bluesquare.kr'): void
    {
        $host = strtolower(preg_replace('/:\d+$/', '', (string) ($_SERVER['HTTP_HOST'] ?? '')));
        if ($host !== strtolower($gateHost)) return;

        header('Cache-Control: no-store');
        header('X-Content-Type-Options: nosniff');
        $path = (string) parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
        if (!in_array($_SERVER['REQUEST_METHOD'] ?? 'GET', ['GET', 'HEAD'], true)) self::stop(426);
        if (in_array($path, ['/', '/index.php', '/index.php/'], true)) {
            self::loginScreen($root);
        }
        if (strpos($path, '/index.php/') === 0 && strtolower(pathinfo($path, PATHINFO_EXTENSION)) === 'php') self::stop(426);
        if (strpos($path, '/index.php/resource/') !== 0 || preg_match('/[%\\\\\x00-\x20\x7f]/', $path)) self::stop(404);
        $relative = substr($path, strlen('/index.php/'));
        foreach (explode('/', $relative) as $part) {
            if ($part === '' || $part[0] === '.') self::stop(404);
        }
        if (preg_match('#/(?:examples?|tests?|jbimages)(?:/|$)#i', $relative)) self::stop(404);
        // Admin-local CSS/JS/images first; shared vendor/images/fonts retain their original root.
        $base = preg_match('#^resource/(?:vendor|images|fonts)/#', $relative) ? $root : $root . '/admin';
        $base = realpath($base);
        $file = $base === false ? false : realpath($base . '/' . $relative);
        if ($file === false || !is_file($file) || strpos($file, $base . '/') !== 0) self::stop(404);
        $types = ['css'=>'text/css', 'js'=>'application/javascript', 'png'=>'image/png', 'jpg'=>'image/jpeg',
            'jpeg'=>'image/jpeg', 'gif'=>'image/gif', 'svg'=>'image/svg+xml', 'webp'=>'image/webp',
            'ico'=>'image/x-icon', 'woff'=>'font/woff', 'woff2'=>'font/woff2', 'ttf'=>'font/ttf', 'eot'=>'application/vnd.ms-fontobject'];
        $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        if (!isset($types[$extension])) self::stop(404);
        header('Content-Type: ' . $types[$extension]);
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'HEAD') {
            if (in_array($extension, ['css', 'js'], true)) echo self::assetUrls((string) file_get_contents($file));
            else readfile($file);
        }
        exit;
    }

    private static function loginScreen(string $root): void
    {
        $template = (string) file_get_contents($root . '/admin/index.php');
        $start = stripos($template, '<!DOCTYPE html>');
        $html = $start === false ? '' : substr($template, $start);
        // This production login template was checked to contain no PHP after its initial bootstrap.
        if (strpos($html, '<?') !== false && is_readable($root . '/inc/lib/gate-http-preview.html')) {
            $html = (string) file_get_contents($root . '/inc/lib/gate-http-preview.html');
        }
        if ($html === '' || strpos($html, '<?') !== false) self::stop(503);
        $html = preg_replace('/<head([^>]*)>/i', '<head$1><base href="/index.php/">', $html, 1);
        // Authentication is intentionally unavailable until the SSL/operational release.
        $html = preg_replace('#<script\b[^>]*>.*?</script>#is', '', $html);
        $html = preg_replace('#<img\b[^>]*src="[^"<>]*captcha[^"<>]*"[^>]*>#i', '<span>SSL 설치 후 사용</span>', $html);
        if (stripos($html, '<fieldset disabled') === false) {
        $html = preg_replace('/(<form\b[^>]*>)/i', '$1<fieldset disabled style="border:0;padding:0;margin:0;min-width:0">', $html);
        $html = str_ireplace('</form>', '</fieldset></form>', $html);
        }
        if (strpos($html, 'HTTP 접속 확인용') === false) {
        $html = preg_replace('/(<body\b[^>]*>)/i', '$1<p role="alert" style="margin:0;padding:16px;background:#fff3cd;color:#664d03;text-align:center">HTTP 접속 확인용입니다. SSL 설치 전에는 로그인 및 관리자 작업을 사용할 수 없습니다.</p>', $html, 1);
        }
        header("Content-Security-Policy: default-src 'self'; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; font-src 'self' https://fonts.gstatic.com data:; img-src 'self' data:; script-src 'none'; form-action 'none'; frame-ancestors 'none'; base-uri 'self'");
        header('Content-Type: text/html; charset=utf-8');
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'HEAD') echo self::assetUrls($html);
        exit;
    }

    private static function assetUrls(string $body): string
    {
        $body = preg_replace('#(["\x27])(/resource/)#', '$1/index.php$2', $body);
        return preg_replace('#url\((["\x27]?)(/resource/)#', 'url($1/index.php$2', $body);
    }

    private static function stop(int $status): void
    {
        http_response_code($status);
        header('Content-Type: text/plain; charset=utf-8');
        echo $status === 426 ? 'HTTPS is required for administrator authentication and operations.' : ($status === 503 ? 'Preview template unavailable.' : 'Not Found');
        exit;
    }
}
