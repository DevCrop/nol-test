<?php

if (!defined('SESSION_NAME')) {
    require_once dirname(__DIR__, 2) . '/config/env.php';
}

if (session_status() !== PHP_SESSION_NONE) {
    return;
}

if (defined('SESSION_NAME') && SESSION_NAME !== '') {
    session_name(SESSION_NAME);
}

ini_set('session.use_strict_mode', '1');
ini_set('session.use_only_cookies', '1');
ini_set('session.gc_maxlifetime', (string) SESSION_LIFETIME);
require_once dirname(__DIR__, 2) . '/src/Http/TransportSecurity.php';
$temporaryGateHttp = \Http\TransportSecurity::allowsTemporaryHttp($_SERVER, (string) env('GATE_HOST', ''),
    (bool) env('GATE_ALLOW_HTTP', false), (string) env('TRUSTED_PROXY_CIDRS', ''));
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'domain' => '',
    'secure' => !$temporaryGateHttp && (env('APP_ENV', 'production') === 'production' || (defined('SESSION_SECURE') && SESSION_SECURE === true)),
    'httponly' => true,
    'samesite' => (defined('SESSION_SAMESITE') && SESSION_SAMESITE !== '') ? SESSION_SAMESITE : 'Lax',
]);
session_start();
