<?php

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "cli only\n");
    exit(1);
}

$root = dirname(__DIR__);
require_once $root . '/inc/lib/Gate.php';
require_once $root . '/inc/lib/GateAllowlist.php';

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

expect(GateAllowlist::rulesFrom('') === [], 'empty string no rules');
expect(GateAllowlist::rulesFrom(' 1.2.3.4 , 10.0.0.0/8,nope') === ['1.2.3.4', '10.0.0.0/8'], 'parse ipv4 and cidr skip junk');
expect(GateAllowlist::allows('8.8.8.8', []) === true, 'empty rules allow');
expect(GateAllowlist::allows('1.2.3.4', ['1.2.3.4']) === true, 'exact ip');
expect(GateAllowlist::allows('1.2.3.5', ['1.2.3.4']) === false, 'exact miss');
expect(GateAllowlist::allows('10.1.2.3', ['10.0.0.0/8']) === true, 'cidr /8');
expect(GateAllowlist::allows('11.0.0.1', ['10.0.0.0/8']) === false, 'cidr miss');
expect(GateAllowlist::allows('192.168.1.50', ['192.168.1.0/24']) === true, 'cidr /24');
expect(GateAllowlist::allows('192.168.2.50', ['192.168.1.0/24']) === false, 'cidr /24 miss');
expect(GateAllowlist::allows('203.0.113.9', ['203.0.113.9/32']) === true, 'cidr /32');
expect(GateAllowlist::allows('', ['1.2.3.4']) === false, 'blank ip denied when rules set');
$configured = GateAllowlist::rulesFrom('1.237.79.3,39.123.86.210,192.168.10.0/24');
expect(GateAllowlist::allows('39.123.86.210', $configured) === true, 'configured office ip allowed');
expect(GateAllowlist::allows('203.0.113.10', $configured) === false, 'unregistered ip denied');

echo $fail === 0 ? "OK {$pass}\n" : "FAIL {$fail}\n";
exit($fail === 0 ? 0 : 1);
