<?php

/** Authentication must precede page queries, not wait for the visual header. */
final class AdminRequest
{
    public static function requireAuthentication(string $adminRoot): void
    {
        if (PHP_SAPI === 'cli') return;
        $file = realpath((string)($_SERVER['SCRIPT_FILENAME'] ?? ''));
        $base = realpath($adminRoot);
        if (!$file || !$base || strpos($file, $base.DIRECTORY_SEPARATOR) !== 0) return;
        $relative = str_replace('\\', '/', substr($file, strlen($base)+1));
        $public = ['index.php','mfa.php','lib/login/login.process.php','lib/login/mfa.email.php',
            'lib/login/mfa.process.php','lib/login/mfa.resend.php','lib/login/logout.php'];
        if (in_array($relative, $public, true) || !empty($_SESSION['no_adm_login_uid'])) return;
        require_once __DIR__.'/AuthSession.php';
        AuthSession::denyLogin();
    }
}
