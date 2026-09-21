<?php

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "cli only\n");
    exit(1);
}

$root = dirname(__DIR__);
require_once $root . '/nol-gate/lib/PiiMask.php';

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

expect(PiiMask::name('홍길동') === '홍*동', 'name 3');
expect(PiiMask::name('김이') === '김*', 'name 2');
expect(PiiMask::name('가') === '*', 'name 1');
expect(PiiMask::phone('010-1234-5678') === '010-****-5678', 'phone 11');
expect(PiiMask::phone('0101234567') === '010-***-4567', 'phone 10');
expect(PiiMask::email('abcdef@example.com') === 'abc***@example.com', 'email 4+ keep 3');
expect(PiiMask::email('abc@example.com') === 'ab*@example.com', 'email 3 keep 2');
expect(PiiMask::email('ab@example.com') === 'a*@example.com', 'email 2 keep 1');
expect(PiiMask::email('a@example.com') === '*@example.com', 'email 1 mask all');

echo $fail === 0 ? "OK {$pass}\n" : "FAIL {$fail}\n";
exit($fail === 0 ? 0 : 1);
