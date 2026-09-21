<?php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
$failed=0;
foreach(glob(__DIR__.'/*.php') as $file) {
    if(basename($file)==='run-all.php') continue;
    echo "\n=== ".basename($file)." ===\n";
    passthru(escapeshellarg(PHP_BINARY).' -d short_open_tag=1 '.escapeshellarg($file),$status);
    if($status!==0) $failed++;
}
echo $failed ? "FAILED {$failed}\n" : "ALL QA PASSED\n";
exit($failed ? 1 : 0);
