<?php

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "cli only\n");
    exit(1);
}

$root = dirname(__DIR__);
require_once $root . '/nol-gate/lib/SessionIdleTimeout.php';

$fail = 0;
$pass = 0;
function expect($ok, string $name): void
{
    global $fail, $pass;
    if ($ok) {
        $pass++;
        echo "PASS  {$name}\n";
        return;
    }
    $fail++;
    echo "FAIL  {$name}\n";
}

$timeout = new SessionIdleTimeout(1800);
expect($timeout->hasExpired([], 2000) === false, 'missing timestamp is not expired');
expect($timeout->hasExpired(['no_adm_last_activity' => 1000], 2799) === false, 'active before boundary');
expect($timeout->hasExpired(['no_adm_last_activity' => 1000], 2800) === true, 'expires at boundary');
expect($timeout->hasExpired(['no_adm_last_activity' => 1000], 4000) === true, 'expires after boundary');

echo $fail === 0 ? "OK {$pass}\n" : "FAIL {$fail}\n";
exit($fail === 0 ? 0 : 1);
