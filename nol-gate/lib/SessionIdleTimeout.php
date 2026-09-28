<?php

final class SessionIdleTimeout
{
    private const KEY = 'no_adm_last_activity';

    private int $seconds;

    public function __construct(int $seconds)
    {
        $this->seconds = max(1, $seconds);
    }

    public function hasExpired(array $session, ?int $now = null): bool
    {
        $last = (int) ($session[self::KEY] ?? 0);
        return $last > 0 && (($now ?? time()) - $last) >= $this->seconds;
    }

    public function enforce(): void
    {
        if (AuthSession::isAuthSurface() || empty($_SESSION['no_adm_login_uid'])) {
            return;
        }

        $now = time();
        if ($this->hasExpired($_SESSION, $now)) {
            AuthSession::denyLogin('장시간 활동이 없어 자동 로그아웃되었습니다.');
        }
        $path = (string) ($_SERVER['SCRIPT_NAME'] ?? '');
        if (substr($path, -strlen('/ajax/session.activity.php')) !== '/ajax/session.activity.php' || ($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
            $_SESSION[self::KEY] = $now;
        }
    }
}
