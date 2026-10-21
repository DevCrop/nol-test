<?php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
// PHP PATH_INFO mode has an independent no-rewrite HTTP acceptance suite.
if (getenv('GATE_ROUTE_MODE') === 'php') {
    passthru(escapeshellarg(PHP_BINARY).' '.escapeshellarg(__DIR__.'/run-php-router.php'), $status);
    exit($status);
}
$failed=0;
foreach(glob(__DIR__.'/*.php') as $file) {
    if(in_array(basename($file), ['run-all.php', 'run-php-router.php', 'browser-fixture.php'], true)) continue;
    echo "\n=== ".basename($file)." ===\n";
    passthru(escapeshellarg(PHP_BINARY).' -d short_open_tag=1 '.escapeshellarg($file),$status);
    if($status!==0) $failed++;
}
echo $failed ? "FAILED {$failed}\n" : "ALL QA PASSED\n";
exit($failed ? 1 : 0);
