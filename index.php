<?php
require_once __DIR__ . '/inc/lib/PhpGateRouter.php';
$phpGateTarget = (new PhpGateRouter(__DIR__, 'nol-gate'))->start();
if ($phpGateTarget !== null) {
    require_once __DIR__ . '/inc/lib/base.class.php';
    require $phpGateTarget;
    exit;
}


include_once __DIR__.'/inc/lib/base.class.php';

try {
    $response = $app->handleRequest();
    echo $response->send();
} catch (Throwable $e) {
    error_log('[index] ' . $e->getMessage());
    no_render_internal_error();
}
