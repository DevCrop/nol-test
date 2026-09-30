<?php
if (PHP_SAPI !== 'cli' || !is_file('/.dockerenv')) exit(1);
require dirname(__DIR__) . '/nol-gate/lib/SmtpMailer.php';
$hosted = dirname(__DIR__) . '/storage/mfa-smtp.log';
$before = is_file($hosted) ? hash_file('sha256', $hosted) : null;
$path = tempnam(sys_get_temp_dir(), 'nol-smtp-qa-');
if ($path === false) exit(1);
$old = ini_get('error_log'); $failed = 0;
try {
    ini_set('error_log', $path);
    $method = new ReflectionMethod('SmtpMailer', 'debug');
    $method->setAccessible(true);
    $method->invoke(null, 'fail', 'SMTP server refused recipient synthetic@example.com');
    $text = file_get_contents($path);
    $checks = [
        'SMTP server diagnostic reaches external server log' => strpos($text, 'SMTP server refused recipient') !== false,
        'SMTP diagnostic redacts recipient email' => strpos($text, 'synthetic@example.com') === false && strpos($text, '[email]') !== false,
        'SMTP diagnostic does not modify hosted log file' => (is_file($hosted) ? hash_file('sha256', $hosted) : null) === $before,
    ];
    foreach ($checks as $name => $ok) { echo ($ok ? 'PASS ' : 'FAIL ') . $name . PHP_EOL; if (!$ok) $failed++; }
} finally {
    ini_set('error_log', $old);
    unlink($path);
}
exit($failed ? 1 : 0);
