<?php
namespace Security;

final class Csrf
{
    public static function token(): string
    {
        if (empty($_SESSION['blue_csrf'])) $_SESSION['blue_csrf'] = bin2hex(random_bytes(32));
        return (string) $_SESSION['blue_csrf'];
    }

    public static function validateRequest(): void
    {
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') return;
        $path = (string) ($_SERVER['SCRIPT_NAME'] ?? '');
        if (strpos($path, '/admin/') !== 0) return;
        $provided = (string) ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? ($_POST['_csrf'] ?? ''));
        if ($provided !== '' && hash_equals(self::token(), $provided)) return;
        http_response_code(403);
        $json = strpos(strtolower($path), '/ajax/') !== false || substr(strtolower($path), -12) === '/process.php';
        header('Content-Type: ' . ($json ? 'application/json' : 'text/plain') . '; charset=utf-8');
        echo $json ? json_encode(['result' => 'fail', 'message' => '잘못된 요청입니다.']) : '잘못된 요청입니다.';
        exit;
    }

    public static function valid(string $provided): bool
    {
        return $provided !== '' && hash_equals(self::token(), $provided);
    }
}
