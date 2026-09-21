<?php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
$scripts = ['php-lint.php','security-unit.php','proxy-policy.php','security-regression.php','db-integration.php','http-smoke.php','auth-http.php','mfa-flow.php','diagnostic-http.php','migration-release.php']; $failed = 0;
foreach ($scripts as $script) {
    echo "\n=== {$script} ===\n"; passthru(escapeshellarg(PHP_BINARY) . ' -d short_open_tag=1 ' . escapeshellarg(__DIR__ . '/' . $script), $status); if ($status !== 0) $failed++;
}
echo "\n" . ($failed ? "FAILED {$failed}" : 'ALL QA PASSED') . "\n"; exit($failed ? 1 : 0);
