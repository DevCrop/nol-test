<?php

require_once __DIR__ . '/env.php';

define('ROOT_PATH', dirname(__DIR__));
define('BASE_PATH', '');
define('IMG_PATH', '/resource/images');

define('ENV_PRODUCTION', 'production');
define('ENV_DEVELOPMENT', 'development');

define('FORCE_LOCALE_PREFIX', false);

define('LOCALES', [
    'ko' => 'ko-KR',
    'en' => 'en-US',
]);

define('DEFAULT_LOCALE', 'ko');

$appEnv = strtolower((string) env('APP_ENV', 'production'));
define('ENV', in_array($appEnv, ['development', 'local', 'dev'], true) ? ENV_DEVELOPMENT : ENV_PRODUCTION);

if (env('DB_HOST')) {
    define('DB_HOST', (string) env('DB_HOST'));
    define('DB_PORT', (int) env('DB_PORT', 3306));
    define('DB_NAME', (string) env('DB_NAME'));
    define('DB_USER', (string) env('DB_USER'));
    define('DB_PASS', (string) env('DB_PASS', ''));
    define('DB_CHARSET', (string) env('DB_CHARSET', 'utf8mb4'));
}
