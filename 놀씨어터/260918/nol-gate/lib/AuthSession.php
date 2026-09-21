<?php

require_once __DIR__ . '/../Model/AccountModel.php';
require_once __DIR__ . '/AccountIdleLock.php';

class AuthSession
{
    public static function adminKeys(): array
    {
        return [
            'no_adm_login_no',
            'no_adm_login_uid',
            'no_adm_login_uname',
            'no_adm_login_role_id',
            'no_adm_login_role',
            'no_adm_login_token',
            'no_adm_last_activity',
        ];
    }

    public static function forgetMfa(): void
    {
        unset($_SESSION['mfa_pending']);
    }

    public static function forgetAdmin(): void
    {
        foreach (self::adminKeys() as $key) {
            unset($_SESSION[$key]);
        }
    }

    public static function cancel(): void
    {
        self::forgetMfa();
        self::forgetAdmin();
    }

    public static function rotate(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE && !headers_sent()) {
            session_regenerate_id(true);
        }
    }

    public static function grantAdmin(array $admin, string $roleCode): void
    {
        $_SESSION['no_adm_login_no'] = (int) ($admin['no'] ?? 0);
        $_SESSION['no_adm_login_uid'] = (string) ($admin['uid'] ?? '');
        $_SESSION['no_adm_login_uname'] = (string) ($admin['uname'] ?? '');
        $_SESSION['no_adm_login_role_id'] = (int) ($admin['role_id'] ?? 3);
        $_SESSION['no_adm_login_role'] = $roleCode;
    }

    public static function establish(array $admin, string $roleCode): void
    {
        self::rotate();
        self::grantAdmin($admin, $roleCode);
        $no = (int) ($admin['no'] ?? 0);
        $token = bin2hex(random_bytes(32));
        $_SESSION['no_adm_login_token'] = $token;
        $_SESSION['no_adm_last_activity'] = time();
        if ($no > 0) {
            AccountModel::setLoginToken($no, $token);
            (new AccountIdleLock())->touchLogin($no);
        }
    }

    public static function loginAdmin(array $admin, string $roleCode): void
    {
        $_SESSION = [];
        self::establish($admin, $roleCode);
    }

    public static function assertExclusive(): void
    {
        if (self::isAuthSurface()) {
            return;
        }
        $no = (int) ($_SESSION['no_adm_login_no'] ?? 0);
        if ($no < 1) {
            return;
        }
        $dbToken = AccountModel::getLoginToken($no);
        if ($dbToken === '') {
            return;
        }
        $mine = (string) ($_SESSION['no_adm_login_token'] ?? '');
        if ($mine !== '' && hash_equals($dbToken, $mine)) {
            return;
        }
        self::kick();
    }

    public static function expire(?string $flash = null): void
    {
        $no = (int) ($_SESSION['no_adm_login_no'] ?? 0);
        $token = (string) ($_SESSION['no_adm_login_token'] ?? '');
        if ($no > 0 && $token !== '') {
            AccountModel::clearLoginTokenIfMatch($no, $token);
        }
        self::cancel();
        self::rotate();
        $_SESSION = [];
        if ($flash !== null && $flash !== '') {
            $_SESSION['admin_flash_error'] = $flash;
        }
    }

    public static function destroy(): void
    {
        self::expire(null);

        if (!headers_sent() && ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            $opts = [
                'expires' => time() - 42000,
                'path' => $params['path'] ?: '/',
                'domain' => (string) ($params['domain'] ?? ''),
                'secure' => !empty($params['secure']),
                'httponly' => !empty($params['httponly']),
                'samesite' => (string) (($params['samesite'] ?? '') !== '' ? $params['samesite'] : 'Lax'),
            ];
            setcookie(session_name(), '', $opts);
            setcookie('cookie_session_id', '', $opts);
        }

        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
    }

    public static function isAuthSurface(): bool
    {
        $script = self::scriptName();
        if (preg_match('#/(index|mfa)\.php$#', $script)) {
            return true;
        }
        if (strpos($script, '/lib/login/') !== false || strpos($script, '/captcha/') !== false) {
            return true;
        }
        return false;
    }

    public static function scriptName(): string
    {
        return str_replace('\\', '/', (string) ($_SERVER['SCRIPT_NAME'] ?? ''));
    }

    public static function wantsJson(): bool
    {
        $script = self::scriptName();
        return strpos($script, '/Controller/') !== false
            || strpos($script, '/ajax/') !== false
            || substr($script, -12) === '/process.php'
            || (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower((string) $_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');
    }

    public static function denyLogin(string $message = '로그인이 필요합니다.'): void
    {
        self::expire($message);
        if (self::wantsJson()) {
            if (!headers_sent()) {
                header('Content-Type: application/json; charset=utf-8');
                http_response_code(401);
            }
            echo json_encode([
                'success' => false,
                'message' => $message,
                'result' => 'fail',
                'msg' => $message,
            ]);
            exit;
        }
        $base = (string) ($GLOBALS['NO_ADMIN_BASE'] ?? '');
        $login = $base . '/index.php';
        if (!headers_sent()) {
            header('Location: ' . $login);
            exit;
        }
        unset($_SESSION['admin_flash_error']);
        if (function_exists('alert')) {
            alert($message, $login);
            exit;
        }
        exit;
    }

    private static function kick(): void
    {
        self::denyLogin('다른 곳에서 로그인되어 종료되었습니다.');
    }
}
