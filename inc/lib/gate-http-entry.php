<?php

// Add this entry before the existing public bootstrap; leave public application code intact.
require_once __DIR__ . '/GateHttpPreview.php';
$gatePreviewRoot = dirname(__DIR__, 2);
if (GateHttpPreview::enabled($gatePreviewRoot)) {
    require_once __DIR__ . '/LegacyGateHttpRouter.php';
    // enabled() already validated the configured host; support development gate.local as well.
    $gatePreviewHost = strtolower(preg_replace('/:\d+$/', '', (string) ($_SERVER['HTTP_HOST'] ?? '')));
    LegacyGateHttpRouter::dispatch($gatePreviewRoot, $gatePreviewHost);
    // A configured preview must never fall through into public or legacy authentication.
    http_response_code(503);
    header('Content-Type: text/plain; charset=utf-8');
    exit('Gate preview configuration mismatch.');
}
unset($gatePreviewRoot);
