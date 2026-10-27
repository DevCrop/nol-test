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
foreach (['php-lint.php','php-router-http.php','active-crud.php','security-unit.php','architecture.php','mfa-mail-template.php'] as $script) {
    echo "\n=== {$script} ===\n";
    passthru(escapeshellarg(PHP_BINARY).' -d short_open_tag=1 '.escapeshellarg(__DIR__.'/'.$script), $status);
    if ($status !== 0) $failed++;
}
if (getenv('APP_ENV')==='development') {
    passthru(escapeshellarg(PHP_BINARY).' '.escapeshellarg(__DIR__.'/auth-error-redaction.php'),$status);
    if($status!==0)$failed++;
    passthru(escapeshellarg(PHP_BINARY).' '.escapeshellarg(__DIR__.'/admin-transactions.php'),$status);
    if($status!==0)$failed++;
    passthru(escapeshellarg(PHP_BINARY).' '.escapeshellarg(__DIR__.'/temporary-credentials.php'),$status);
    if($status!==0)$failed++;
}
echo $failed ? "FAILED {$failed}\n" : "PHP ROUTER QA PASSED\n";
exit($failed ? 1 : 0);
