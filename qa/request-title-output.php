<?php
require __DIR__ . '/bootstrap.php';
$source = file_get_contents(dirname(__DIR__) . '/admin/pages/request/request.list.php');
preg_match_all('/<\?=\s*(.*?)\s*\?>/s', $source, $expressions);
$title = null;
foreach ($expressions[1] as $expression) {
    if (strpos($expression, "$" . "v['performance_name']") !== false) $title = $expression;
}
if ($title === null) throw new RuntimeException('request title expression missing');
$render = static function (string $value) use ($title): string {
    $v = ['performance_name' => $value];
    ob_start();
    eval('echo ' . $title . ';');
    return ob_get_clean();
};
foreach (['<img src=x onerror=alert(1)>', '</a><svg/onload=alert(1)>', '<script>alert(1)</script>'] as $value) {
    $output = $render($value);
    qa_expect(strpos($output, '<') === false && html_entity_decode($output, ENT_QUOTES | ENT_HTML5, 'UTF-8') === $value, 'raw stored markup displayed as text: ' . substr($value, 0, 12));
}
$normal = '공연 "A&B" <봄>';
$encoded = htmlspecialchars($normal, ENT_QUOTES, 'UTF-8');
qa_expect(html_entity_decode($render($normal), ENT_QUOTES | ENT_HTML5, 'UTF-8') === $normal, 'raw Korean title preserves punctuation');
qa_expect($render($encoded) === $encoded, 'existing encoded title is not double encoded');
qa_expect($render('&#x3C;script&#x3E;test&#x3C;/script&#x3E;') === '&#x3C;script&#x3E;test&#x3C;/script&#x3E;', 'encoded tag remains inert text');
qa_done();
