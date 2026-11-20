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
        $provided = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? ($_POST['_csrf'] ?? '');
        if (is_string($provided) && self::valid($provided)) return;
        if (strpos(strtolower($path), '/admin/lib/login/') === 0 && BrowserFormResponse::isNavigation()) {
            $cookie = $_COOKIE[session_name()] ?? null;
            $sameSession = is_string($cookie) && hash_equals(session_id(), $cookie);
            if (!$sameSession && is_string($cookie)) {
                // The expired request must not replace the cookie issued by successful MFA.
                header_remove('Set-Cookie');
            }
            $notice = '요청을 확인할 수 없습니다. 화면에서 다시 시도하세요.';
            $pending = $sameSession && !empty($_SESSION['mfa_pending']);
            if ($pending) {
                $_SESSION['mfa_error'] = $notice;
            } elseif ($sameSession && empty($_SESSION['no_adm_login_uid'])) {
                $_SESSION['admin_flash_error'] = $notice;
            }
            header('Location: ' . blue_admin_url($pending ? '/admin/mfa.php' : '/admin/index.php'), true, 303);
            exit;
        }
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
