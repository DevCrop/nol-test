<?php
if (!function_exists('blue_safe_error')) {
    function blue_safe_error(\Throwable $error): string
    {
        error_log(get_class($error) . ': ' . $error->getMessage());
        return '요청을 처리할 수 없습니다.';
    }
}

if (!function_exists('blue_env')) {
    function blue_env(string $key, $default = null)
    {
        $value = $_ENV[$key] ?? getenv($key);
        if ($value === false || $value === null || $value === '') return $default;
        $lower = strtolower((string) $value);
        if ($lower === 'true') return true;
        if ($lower === 'false') return false;
        if ($lower === 'null') return null;
        return $value;
    }
}

if (!function_exists('blue_load_env')) {
    function blue_load_env(string $file): void
    {
        if (!is_readable($file)) return;
        foreach ((array) file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
            $line = trim($line);
            if ($line === '' || $line[0] === '#' || strpos($line, '=') === false) continue;
            [$key, $value] = explode('=', $line, 2);
            $key = trim($key);
            $value = trim(trim($value), "\"'");
            if ($key === '' || getenv($key) !== false) continue;
            $_ENV[$key] = $value;
            putenv($key . '=' . $value);
        }
    }
}

blue_load_env(dirname(__DIR__, 2) . '/.env');

if (!defined('APP_ENV')) define('APP_ENV', (string) blue_env('APP_ENV', 'production'));
if (!defined('PUBLIC_HOST')) define('PUBLIC_HOST', (string) blue_env('PUBLIC_HOST', 'www.bluesquare.kr'));
if (!defined('GATE_HOST')) define('GATE_HOST', (string) blue_env('GATE_HOST', ''));
if (!defined('GATE_ENFORCE')) define('GATE_ENFORCE', (bool) blue_env('GATE_ENFORCE', false));
if (!defined('GATE_ALLOW_IPS')) define('GATE_ALLOW_IPS', (string) blue_env('GATE_ALLOW_IPS', ''));
if (!defined('TRUSTED_PROXY_CIDRS')) define('TRUSTED_PROXY_CIDRS', (string) blue_env('TRUSTED_PROXY_CIDRS', ''));
if (!defined('SESSION_LIFETIME')) define('SESSION_LIFETIME', max(60, (int) blue_env('SESSION_LIFETIME', 1800)));
if (!defined('MFA_ENABLED')) define('MFA_ENABLED', (bool) blue_env('MFA_ENABLED', false));
if (!defined('MFA_TTL_SECONDS')) define('MFA_TTL_SECONDS', max(60, (int) blue_env('MFA_TTL_SECONDS', 900)));
if (!defined('MFA_MAX_ATTEMPTS')) define('MFA_MAX_ATTEMPTS', max(1, (int) blue_env('MFA_MAX_ATTEMPTS', 5)));
if (!defined('MFA_RESEND_SECONDS')) define('MFA_RESEND_SECONDS', max(1, (int) blue_env('MFA_RESEND_SECONDS', 30)));
if (!defined('LOGIN_MAX_ATTEMPTS')) define('LOGIN_MAX_ATTEMPTS', max(1, (int) blue_env('LOGIN_MAX_ATTEMPTS', 5)));
if (!defined('LOGIN_LOCK_SECONDS')) define('LOGIN_LOCK_SECONDS', max(60, (int) blue_env('LOGIN_LOCK_SECONDS', 900)));
if (!defined('PASSWORD_MAX_DAYS')) define('PASSWORD_MAX_DAYS', max(1, (int) blue_env('PASSWORD_MAX_DAYS', 90)));
if (!defined('ACCOUNT_IDLE_DAYS')) define('ACCOUNT_IDLE_DAYS', max(1, (int) blue_env('ACCOUNT_IDLE_DAYS', 90)));

$isProduction = APP_ENV === 'production';
ini_set('display_errors', $isProduction ? '0' : '1');
ini_set('display_startup_errors', $isProduction ? '0' : '1');
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) {
    session_name((string) blue_env('SESSION_NAME', 'BLUESQGATESESSID'));
    session_set_cookie_params([
        'lifetime' => 0, 'path' => '/', 'domain' => '',
        'secure' => $isProduction || (bool) blue_env('SESSION_SECURE', false),
        'httponly' => true, 'samesite' => (string) blue_env('SESSION_SAMESITE', 'Lax'),
    ]);
    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    ini_set('session.gc_maxlifetime', (string) SESSION_LIFETIME);
}
