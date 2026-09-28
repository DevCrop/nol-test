<?php

if (!function_exists('env')) {
    function env(string $key, $default = null)
    {
        $value = $_ENV[$key] ?? getenv($key);
        if ($value === false || $value === null || $value === '') {
            return $default;
        }

        $lower = strtolower((string) $value);
        if ($lower === 'true') {
            return true;
        }
        if ($lower === 'false') {
            return false;
        }
        if ($lower === 'null') {
            return null;
        }

        return $value;
    }
}

if (!function_exists('no_load_dotenv')) {
    function no_load_dotenv(string $path): void
    {
        if (!is_file($path) || !is_readable($path)) {
            return;
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES);
        if ($lines === false) {
            return;
        }

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || $line[0] === '#' || strpos($line, '=') === false) {
                continue;
            }

            [$key, $value] = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            $value = trim($value, "\"'");

            if ($key === '' || $value === '') {
                continue;
            }

            $existing = getenv($key);
            if ($existing !== false && $existing !== '') {
                continue;
            }

            $_ENV[$key] = $value;
            putenv($key . '=' . $value);
        }
    }
}

no_load_dotenv(dirname(__DIR__) . DIRECTORY_SEPARATOR . '.env');

if (!defined('PUBLIC_HOST')) {
    define('PUBLIC_HOST', (string) env('PUBLIC_HOST', 'noltheater-daehakro.com'));
}
if (!defined('GATE_HOST')) {
    define('GATE_HOST', (string) env('GATE_HOST', ''));
}
if (!defined('GATE_ENFORCE')) {
    define('GATE_ENFORCE', (bool) env('GATE_ENFORCE', false));
}
if (!defined('ADMIN_DIR')) {
    define('ADMIN_DIR', (string) env('ADMIN_DIR', 'nol-gate'));
}
if (!defined('CORS_ORIGINS')) {
    define('CORS_ORIGINS', (string) env('CORS_ORIGINS', ''));
}
if (!defined('SESSION_NAME')) {
    define('SESSION_NAME', (string) env('SESSION_NAME', 'NOLGATESESSID'));
}
if (!defined('SESSION_LIFETIME')) {
    define('SESSION_LIFETIME', (int) env('SESSION_LIFETIME', 1800));
}
if (!defined('SESSION_DOMAIN')) {
    define('SESSION_DOMAIN', (string) env('SESSION_DOMAIN', ''));
}
if (!defined('SESSION_SECURE')) {
    define('SESSION_SECURE', (bool) env('SESSION_SECURE', false));
}
if (!defined('SESSION_SAMESITE')) {
    define('SESSION_SAMESITE', (string) env('SESSION_SAMESITE', 'Lax'));
}

if (!defined('SMTP_HOST')) {
    define('SMTP_HOST', (string) env('SMTP_HOST', 'smtp.gmail.com'));
}
if (!defined('SMTP_PORT')) {
    define('SMTP_PORT', (int) env('SMTP_PORT', 587));
}
if (!defined('SMTP_USER')) {
    define('SMTP_USER', (string) env('SMTP_USER', ''));
}
if (!defined('SMTP_PASS')) {
    define('SMTP_PASS', (string) env('SMTP_PASS', ''));
}
if (!defined('SMTP_FROM')) {
    define('SMTP_FROM', (string) env('SMTP_FROM', ''));
}
if (!defined('SMTP_FROM_NAME')) {
    define('SMTP_FROM_NAME', (string) env('SMTP_FROM_NAME', 'NOL Theater'));
}

if (!defined('MFA_TO')) {
    define('MFA_TO', (string) env('MFA_TO', ''));
}
if (!defined('MFA_ENABLED')) {
    define('MFA_ENABLED', (bool) env('MFA_ENABLED', false));
}
if (!defined('MFA_CODE_LENGTH')) {
    define('MFA_CODE_LENGTH', (int) env('MFA_CODE_LENGTH', 6));
}
if (!defined('MFA_TTL_SECONDS')) {
    define('MFA_TTL_SECONDS', (int) env('MFA_TTL_SECONDS', 900));
}
if (!defined('MFA_MAX_ATTEMPTS')) {
    define('MFA_MAX_ATTEMPTS', (int) env('MFA_MAX_ATTEMPTS', 5));
}
if (!defined('ACCOUNT_IDLE_DAYS')) {
    define('ACCOUNT_IDLE_DAYS', (int) env('ACCOUNT_IDLE_DAYS', 90));
}
if (!defined('PASSWORD_MAX_DAYS')) {
    define('PASSWORD_MAX_DAYS', (int) env('PASSWORD_MAX_DAYS', 90));
}
if (!defined('GATE_ALLOW_IPS')) {
    define('GATE_ALLOW_IPS', (string) env('GATE_ALLOW_IPS', ''));
}

if (ADMIN_DIR !== '') {
    putenv('NO_ADMIN_DIR=' . ADMIN_DIR);
    $_ENV['NO_ADMIN_DIR'] = ADMIN_DIR;
}

require_once dirname(__DIR__) . '/inc/lib/Gate.php';
require_once dirname(__DIR__) . '/inc/lib/GateAllowlist.php';
require_once dirname(__DIR__) . '/inc/lib/Cors.php';
require_once dirname(__DIR__) . '/inc/lib/ClientFault.php';
GateAllowlist::enforce();
