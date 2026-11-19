<?php

if (!function_exists('no_render_internal_error')) {
    function no_render_internal_error(string $message = 'Internal Server Error', int $statusCode = 500): void
    {
        if (PHP_SAPI !== 'cli') {
            if (!headers_sent()) {
                http_response_code($statusCode);
                header('Content-Type: text/plain; charset=UTF-8');
            }
        }

        echo $message;
        exit;
    }
}

if (!function_exists('no_handle_unhandled_exception')) {
    function no_handle_unhandled_exception(Throwable $e): void
    {
        error_log('[unhandled] ' . $e->getMessage());
        no_render_internal_error('Internal Server Error', http_response_code() === 503 ? 503 : 500);
    }
}

if (!function_exists('no_handle_fatal_error')) {
    function no_handle_fatal_error(): void
    {
        if (function_exists('env') && in_array(strtolower((string) env('APP_ENV', 'production')), ['development', 'local', 'dev'], true)) {
            return;
        }
        $error = error_get_last();
        if ($error === null) {
            return;
        }

        $fatalTypes = [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR];
        if (!in_array($error['type'] ?? 0, $fatalTypes, true)) {
            return;
        }

        error_log('[fatal] ' . ($error['message'] ?? 'Unknown fatal error'));
        no_render_internal_error();
    }
}

// Use safe response defaults before loading application classes.
ini_set('display_errors', '0');
ini_set('display_startup_errors', '0');
ini_set('log_errors', '1');
error_reporting(E_ALL & ~(E_NOTICE | E_USER_NOTICE | E_WARNING | E_COMPILE_WARNING | E_CORE_WARNING | E_USER_WARNING | E_DEPRECATED | E_USER_DEPRECATED));

if (PHP_SAPI !== 'cli') {
    set_exception_handler('no_handle_unhandled_exception');
    register_shutdown_function('no_handle_fatal_error');
}

require_once dirname(__DIR__, 2) . '/config/env.php';
if (in_array(strtolower((string) env('APP_ENV', 'production')), ['development', 'local', 'dev'], true)) {
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    if (PHP_SAPI !== 'cli') {
        restore_exception_handler();
    }
}
