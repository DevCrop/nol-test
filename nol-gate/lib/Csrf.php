<?php

final class Csrf
{
    public static function token(): string
    {
        if (empty($_SESSION['_csrf']) || !is_string($_SESSION['_csrf'])) {
            $_SESSION['_csrf'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['_csrf'];
    }

    public static function field(): string
    {
        return '<input type="hidden" name="_csrf" value="'.htmlspecialchars(self::token(), ENT_QUOTES, 'UTF-8').'">';
    }

    public static function guard(string $adminRoot): void
    {
        if (PHP_SAPI === 'cli') return;
        $script = realpath((string)($_SERVER['SCRIPT_FILENAME'] ?? ''));
        $base = realpath($adminRoot);
        if (!$script || !$base || strpos($script, $base.DIRECTORY_SEPARATOR) !== 0) return;
        $method = strtoupper((string)($_SERVER['REQUEST_METHOD'] ?? 'GET'));
        $postOnly = ['login.process.php', 'mfa.email.php', 'mfa.process.php', 'mfa.resend.php', 'logout.php'];
        if (in_array(basename($script), $postOnly, true) && $method !== 'POST') {
            self::deny(405);
        }
        if (in_array($method, ['GET', 'HEAD', 'OPTIONS'], true)) return;
        $provided = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? $_POST['_csrf'] ?? '';
        $expected = $_SESSION['_csrf'] ?? '';
        if (!is_string($provided) || !is_string($expected) || $expected === '' || !hash_equals($expected, $provided)) {
            self::deny(403);
        }
    }

    private static function deny(int $status): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        if ($status === 405) header('Allow: POST');
        echo json_encode(['success'=>false, 'message'=>'요청을 확인할 수 없습니다. 화면을 새로고침한 뒤 다시 시도하세요.']);
        exit;
    }
}
