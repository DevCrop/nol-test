<?php

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "cli only\n");
    exit(1);
}

$root = dirname(__DIR__);
require_once $root . '/config/helpers.php';
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

$faq = file_get_contents($root . '/views/pages/customer/faq.php');
$search = file_get_contents($root . '/views/pages/search/index.php');
$footer = file_get_contents($root . '/views/components/footer.php');
$app = file_get_contents($root . '/nol-gate/resource/js/app.js');
$auth = file_get_contents($root . '/nol-gate/lib/AuthSession.php');
$popup = file_get_contents($root . '/views/components/popup.php');
$htaccess = file_get_contents($root . '/.htaccess');
$compose = file_get_contents($root . '/docker-compose.yml');

expect(strpos($faq, "sanitize_html_fragment(\$item['content']") !== false, 'faq rich html sanitized');
expect(strpos($search, "sanitize_html_fragment(\$item['content']") !== false, 'search faq rich html sanitized');
expect(strpos($footer, "sanitize_embed_html(\$tag['tag_content']") !== false, 'footer embed sanitized');
expect(strpos($app, 'case "inquiry":') !== false && substr_count($app, 'new PrivacyCopyLock(document.body).init();') >= 2, 'copy lock on list and detail');
expect(strpos($auth, "header('Location: ' . \$login);") < strpos($auth, "if (function_exists('alert'))"), 'login redirect precedes fallback alert');
expect(substr_count($popup, "include __DIR__") === 2 && strlen($popup) < 300, 'dead popup implementation removed');
expect(!file_exists($root . '/guide.php'), 'empty public guide removed');
expect(!file_exists($root . '/views/components/faq.php'), 'unused empty faq component removed');
expect(sanitize_html_fragment('<img src="x" onerror="alert(1)"><script>alert(2)</script><a href="javascript:alert(3)">x</a>') === '<img src="x"><a>x</a>', 'rich html attack payload removed');
expect(sanitize_embed_html('<script>alert(1)</script><iframe src="javascript:alert(2)"></iframe>') === '', 'embed attack payload removed');
expect(sanitize_embed_html('<script src="https://trusted.example/tag.js"></script>') === '<script src="https://trusted.example/tag.js"></script>', 'external embed remains supported');
expect(strpos($htaccess, 'fullPage\\.js-2\\.9\\.7/(?:examples|tests)') !== false, 'vendor examples and tests blocked');
expect(strpos($compose, ':-password') === false && strpos($compose, ':-root') === false, 'compose has no default database passwords');

echo $fail === 0 ? "OK {$pass}\n" : "FAIL {$fail}\n";
exit($fail === 0 ? 0 : 1);
