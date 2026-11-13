<?php
/** Pure CLI regression: no DB, environment, HTTP, mail or session access. */
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require_once dirname(__DIR__) . '/inc/lib/classes/Security/AccountValidator.php';
require_once dirname(__DIR__) . '/inc/lib/classes/Security/TemporaryPassword.php';

$cases = [
    ['reject four characters encoded as eight bytes', 'A1한글', false],
    ['reject seven ASCII characters', 'Abc123!', false],
    ['reject seven Unicode characters', 'A1가나다라마', false],
    ['accept eight ASCII characters', 'Abcd123!', true],
    ['accept eight Unicode characters', 'A1가나다라마바', true],
    ['reject invalid UTF-8 continuation byte', "Abcd12!\x80", false],
    ['reject invalid UTF-8 truncated sequence', "Abcd12!\xE3\x81", false],
    ['reject invalid UTF-8 overlong sequence', "Abcd12!\xC0\xAF", false],
    ['reject invalid UTF-8 surrogate', "Abcd12!\xED\xA0\x80", false],
    ['reject embedded NUL', "Abcd12!\0x", false],
    ['accept exactly 72 ASCII bytes', 'A1!' . str_repeat('a', 69), true],
    ['reject 73 ASCII bytes', 'A1!' . str_repeat('a', 70), false],
    ['accept exactly 72 Unicode bytes', 'A1!' . str_repeat('가', 23), true],
    ['reject 73 Unicode bytes', 'A1!a' . str_repeat('가', 23), false],
    ['reject fewer than three character groups', 'abcd1234', false],
    ['reject mismatched confirmation', 'Abcd123!', false, 'Abcd124!'],
    ['reject case-insensitive account ID inclusion', 'Admin123!', false, 'Admin123!', 'ADMIN'],
    ['accept existing generated temporary password', \Security\TemporaryPassword::generate(), true],
];

$failures = 0;
foreach ($cases as $case) {
    [$label, $password, $expected] = $case;
    $accepted = true;
    try {
        \Security\AccountValidator::password($password, $case[3] ?? $password, $case[4] ?? '');
    } catch (\RuntimeException $error) {
        $accepted = false;
    }
    $passed = $accepted === $expected;
    if (!$passed) $failures++;
    echo ($passed ? 'PASS ' : 'FAIL ') . $label . PHP_EOL;
}
echo count($cases) . ' cases; ' . $failures . ' fail' . PHP_EOL;
exit($failures ? 1 : 0);
