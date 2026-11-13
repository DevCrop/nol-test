<?php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
if (getenv('GATE_ROUTE_MODE') !== 'php') { echo "SKIP PHP router environment required\n"; exit; }
$failed = 0;
passthru(escapeshellarg(PHP_BINARY).' '.escapeshellarg(__DIR__.'/account-authority-policy.php'),$status);
if($status!==0)$failed++;
foreach (['transport-policy.php', 'deployment-preflight.php'] as $deploymentScript) {
    passthru(escapeshellarg(PHP_BINARY).' '.escapeshellarg(__DIR__.'/'.$deploymentScript), $status);
    if ($status !== 0) $failed++;
}
foreach (['php-lint.php','password-policy-boundaries.php','php-router-http.php','active-crud.php','security-unit.php','legacy-font-content.php','pii-legacy-names.php','architecture.php','mfa-mail-template.php'] as $script) {
    echo "\n=== {$script} ===\n";
    passthru(escapeshellarg(PHP_BINARY).' -d short_open_tag=1 '.escapeshellarg(__DIR__.'/'.$script), $status);
    if ($status !== 0) $failed++;
}
if (getenv('APP_ENV')==='development') {
    foreach (['admin-save-regressions.php','admin-pages-http.php','main-banner.php','popup-public-regressions.php','php-router-form-values.php','request-title-output.php','statistics-readonly.php','design-crud-regressions.php','board-crud-regressions.php','board-privacy-regressions.php','log-privacy-view.php','admin-log-ui.php','rental-privacy-mutations.php','account-privacy-actions.php','works-audit-targets.php','download-response-http.php'] as $regression) {
        passthru(escapeshellarg(PHP_BINARY).' '.escapeshellarg(__DIR__.'/'.$regression),$status);
        if($status!==0)$failed++;
    }
    passthru(escapeshellarg(PHP_BINARY).' '.escapeshellarg(__DIR__.'/admin-assets-http.php'),$status);
    if($status!==0)$failed++;
    passthru(escapeshellarg(PHP_BINARY).' '.escapeshellarg(__DIR__.'/captcha-image-http.php'),$status);
    if($status!==0)$failed++;
    passthru(escapeshellarg(PHP_BINARY).' '.escapeshellarg(__DIR__.'/menu-permissions.php'),$status);
    if($status!==0)$failed++;
    passthru(escapeshellarg(PHP_BINARY).' '.escapeshellarg(__DIR__.'/auth-error-redaction.php'),$status);
    if($status!==0)$failed++;
    passthru(escapeshellarg(PHP_BINARY).' '.escapeshellarg(__DIR__.'/admin-transactions.php'),$status);
    if($status!==0)$failed++;
    passthru(escapeshellarg(PHP_BINARY).' '.escapeshellarg(__DIR__.'/temporary-credentials.php'),$status);
    if($status!==0)$failed++;
}
echo $failed ? "FAILED {$failed}\n" : "PHP ROUTER QA PASSED\n";
exit($failed ? 1 : 0);
