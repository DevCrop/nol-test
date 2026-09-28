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
        $no = (int) ($admin['no'] ?? 0);
        $current = AccountModel::authenticationRow($no);
        if (!$current || ($current['active_status'] ?? '') !== 'Y' || !empty($current['idle_locked_at'])
            || (string) $current['sitekey'] !== (string) ($GLOBALS['NO_SITE_UNIQUE_KEY'] ?? '')
            || (isset($admin['upwd']) && !hash_equals((string) $current['upwd'], (string) $admin['upwd']))
            || (isset($admin['email']) && (string) $current['email'] !== (string) $admin['email'])
            || (isset($admin['role_id']) && (int) $current['role_id'] !== (int) $admin['role_id'])) {
            throw new RuntimeException('계정 정보가 변경되었습니다. 다시 로그인하세요.');
        }
        $token = bin2hex(random_bytes(32));
        $stmt = DB::getInstance()->prepare("UPDATE nb_admin SET login_token=:token, last_login_at=NOW()
            WHERE no=:no AND sitekey=:site AND upwd=:password AND role_id=:role AND email=:email
              AND active_status='Y' AND idle_locked_at IS NULL");
        $stmt->execute(['token'=>$token,'no'=>$no,'site'=>$current['sitekey'],'password'=>$current['upwd'],'role'=>$current['role_id'],'email'=>$current['email']]);
        if ($stmt->rowCount() !== 1) throw new RuntimeException('계정 정보가 변경되었습니다. 다시 로그인하세요.');
        self::rotate();
        global $admin_roles;
        self::grantAdmin($current, $admin_roles[(int) $current['role_id']]['code'] ?? $roleCode);
        $_SESSION['no_adm_login_token'] = $token;
        $_SESSION['no_adm_last_activity'] = time();
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
        try { $row = AccountModel::authenticationRow($no); }
        catch (Throwable $e) { self::denyLogin('인증 정보를 확인할 수 없습니다. 다시 로그인하세요.'); }
        $dbToken = (string) ($row['login_token'] ?? '');
        $mine = (string) ($_SESSION['no_adm_login_token'] ?? '');
        if ($row && ($row['active_status'] ?? '') === 'Y'
            && empty($row['idle_locked_at'])
            && (string) ($row['sitekey'] ?? '') === (string) ($GLOBALS['NO_SITE_UNIQUE_KEY'] ?? '')
            && (int) $row['role_id'] === (int) ($_SESSION['no_adm_login_role_id'] ?? 0)
            && $dbToken !== '' && $mine !== '' && hash_equals($dbToken, $mine)) {
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
        $base = rtrim((string) ($GLOBALS['NO_ADMIN_BASE'] ?? ''), '/');
        if (in_array($script, [$base . '/index.php', $base . '/mfa.php'], true)) {
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
