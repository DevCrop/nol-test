<?php
namespace Security;

final class AuthSession
{
    public static function enforce(): void
    {
        if (self::authSurface()) return;
        if (empty($_SESSION['no_adm_login_uid'])) self::deny('로그인이 필요합니다.');
        $last = (int) ($_SESSION['no_adm_last_activity'] ?? 0);
        if (!$last || time() - $last >= SESSION_LIFETIME) self::deny('장시간 활동이 없어 자동 로그아웃되었습니다.');
        $row = AdminAccount::findByNo((int) $_SESSION['no_adm_login_no']);
        $mine = (string) ($_SESSION['no_adm_login_token'] ?? '');
        if (!$row || ($row['active_status'] ?? 'N') !== 'Y' || $mine === '' || !hash_equals((string) ($row['login_token'] ?? ''), $mine)) self::deny('다른 곳에서 로그인되어 종료되었습니다.');
        if (!empty($row['idle_locked_at'])) self::deny('장기 미접속으로 잠긴 계정입니다.');
        $path = (string) ($_SERVER['SCRIPT_NAME'] ?? '');
        if ($path !== '/admin/lib/session/ping.php' || ($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') $_SESSION['no_adm_last_activity'] = time();
        $_SESSION['no_adm_password_change_required'] = self::passwordDue($row);
        if (self::passwordDue($row) && !in_array($path, ['/admin/pages/account/password.php', '/admin/pages/account/ajax/password.process.php', '/admin/lib/session/ping.php'], true)) {
            if (self::wantsJson()) {
                http_response_code(403); header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['result'=>'fail', 'message'=>'비밀번호를 변경해야 합니다.', 'redirect'=>'/admin/pages/account/password.php']); exit;
            }
            header('Location: /admin/pages/account/password.php'); exit;
        }
    }

    public static function logout(): void
    {
        $no = (int) ($_SESSION['no_adm_login_no'] ?? 0); $token = (string) ($_SESSION['no_adm_login_token'] ?? '');
        if ($no && $token !== '') {
            $stmt = \DB::getInstance()->prepare('UPDATE nb_admin SET login_token = NULL WHERE no = ? AND login_token = ?');
            $stmt->execute([$no, $token]);
        }
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', time() - 3600, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
        }
        session_destroy();
    }

    public static function deny(string $message): void
    {
        $no = (int) ($_SESSION['no_adm_login_no'] ?? 0);
        $token = (string) ($_SESSION['no_adm_login_token'] ?? '');
        if ($no && $token !== '') \DB::getInstance()->prepare('UPDATE nb_admin SET login_token = NULL WHERE no = ? AND login_token = ?')->execute([$no, $token]);
        $_SESSION = []; session_regenerate_id(true);
        $_SESSION['admin_flash_error'] = $message;
        $path = strtolower((string) ($_SERVER['SCRIPT_NAME'] ?? ''));
        if (self::wantsJson()) {
            http_response_code(401); header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['result' => 'fail', 'message' => $message, 'msg' => $message]); exit;
        }
        header('Location: /admin/index.php'); exit;
    }

    public static function wantsJson(): bool
    {
        $path = strtolower((string) ($_SERVER['SCRIPT_NAME'] ?? ''));
        return strpos($path, '/ajax/') !== false || substr($path, -12) === '/process.php' || $path === '/admin/lib/session/ping.php' || strtolower((string) ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '')) === 'xmlhttprequest';
    }

    public static function passwordDue(array $row): bool
    {
        $changed = strtotime((string) ($row['password_changed_at'] ?? ''));
        return !empty($row['password_must_change']) || !$changed || time() >= $changed + PASSWORD_MAX_DAYS * 86400;
    }

    private static function authSurface(): bool
    {
        $path = strtolower((string) ($_SERVER['SCRIPT_NAME'] ?? ''));
        return $path === '/admin/index.php' || $path === '/admin/mfa.php' || strpos($path, '/admin/lib/login/') === 0;
    }
}
