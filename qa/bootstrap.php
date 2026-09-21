<?php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
$root = dirname(__DIR__);
require_once $root . '/inc/lib/security.bootstrap.php';
spl_autoload_register(static function (string $class) use ($root): void {
    $path = $root . '/inc/lib/classes/' . str_replace('\\', '/', $class) . '.php';
    if (is_file($path)) require_once $path;
});
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
$GLOBALS['qa_pass'] = 0; $GLOBALS['qa_fail'] = 0;
function qa_expect($ok, string $name): void
{
    if ($ok) { $GLOBALS['qa_pass']++; echo "PASS {$name}\n"; }
    else { $GLOBALS['qa_fail']++; echo "FAIL {$name}\n"; }
}
function qa_done(): void
{
    $pass = $GLOBALS['qa_pass']; $fail = $GLOBALS['qa_fail']; echo $fail ? "FAIL {$fail} / PASS {$pass}\n" : "OK {$pass}\n"; exit($fail ? 1 : 0);
}

