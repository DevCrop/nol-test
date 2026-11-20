<?php

/** Authentication must precede page queries, not wait for the visual header. */
final class AdminRequest
{
    public static function requireAuthentication(string $adminRoot): void
    {
        $relative = self::relativePath($adminRoot);
        if ($relative === null) return;
        $public = ['index.php','mfa.php','lib/login/login.process.php','lib/login/mfa.email.php',
            'lib/login/mfa.process.php','lib/login/mfa.resend.php','lib/login/logout.php'];
        if (in_array($relative, $public, true) || !empty($_SESSION['no_adm_login_uid'])) return;
        require_once __DIR__.'/AuthSession.php';
        AuthSession::denyLogin();
    }

    public static function prepareDocumentResponse(string $adminRoot, string $backUrl): void
    {
        if (!BrowserFormResponse::isNavigation()) return;
        $relative = self::relativePath($adminRoot);
        if ($relative === null) return;
        $isRead = in_array($_SERVER['REQUEST_METHOD'] ?? 'GET', ['GET', 'HEAD', 'OPTIONS'], true);
        if (!$isRead || preg_match('#(?:^Controller/|(?:^|/)ajax/|(?:^|/)process\.php$|^lib/login/)#', $relative)) {
            BrowserFormResponse::handleJson($backUrl);
        }
    }

    private static function relativePath(string $adminRoot): ?string
    {
        if (PHP_SAPI === 'cli') return null;
        $file = realpath((string) ($_SERVER['SCRIPT_FILENAME'] ?? ''));
        $base = realpath($adminRoot);
        if (!$file || !$base || strpos($file, $base . DIRECTORY_SEPARATOR) !== 0) return null;
        return str_replace('\\', '/', substr($file, strlen($base) + 1));
    }
}
