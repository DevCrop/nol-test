<?php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require dirname(__DIR__)."/nol-gate/lib/MfaEmail.php";
$deadline = (new DateTimeImmutable('2030-01-01 12:34:56', new DateTimeZone('Asia/Seoul')))->getTimestamp();
$mail = MfaEmail::compose('놀씨어터', '123456', $deadline);
$checks = [
    'subject includes platform and Web label' => $mail['subject'] === '[Web발신] 놀씨어터 관리자 로그인 인증번호',
    'plain fallback contains platform and code' => strpos($mail['text'], '플랫폼: 놀씨어터') !== false && strpos($mail['text'], '인증번호: 123456') !== false,
    'HTML contains platform and code' => strpos($mail['html'], '놀씨어터') !== false && strpos($mail['html'], '>123456<') !== false,
    'actual absolute expiry in both alternatives' => strpos($mail['text'], '2030-01-01 12:34:56') !== false && strpos($mail['html'], '2030-01-01 12:34:56') !== false,
    'no external images or scripts' => !preg_match('/<script|<img|<link|<iframe/i', $mail['html']),
    'mobile layout and inline styling' => strpos($mail['html'], 'max-width:520px') !== false && strpos($mail['html'], 'width=device-width') !== false,
    'recipient is not embedded' => strpos($mail['html'], '@') === false,
];
$escaped = MfaEmail::compose('<test & brand>', '654321', $deadline);
$checks['HTML escapes platform'] = strpos($escaped['html'], '&lt;test &amp; brand&gt;') !== false && strpos($escaped['html'], '<test & brand>') === false;
$rejected = false; try { MfaEmail::compose('놀씨어터', '<12345', $deadline); } catch (InvalidArgumentException $e) { $rejected = true; }
$checks['invalid code rejected'] = $rejected;
foreach ($checks as $name => $ok) echo ($ok ? 'PASS ' : 'FAIL ').$name.PHP_EOL;
$fail = count(array_filter($checks, static function($ok){ return !$ok; }));
echo 'pass='.(count($checks)-$fail).' fail='.$fail.PHP_EOL;
if (in_array('--preview', $argv, true)) {
    $dir=__DIR__.'/artifacts'; if (!is_dir($dir)) mkdir($dir,0755,true);
    file_put_contents($dir.'/mfa-email-preview.html', $mail['html']);
}
exit($fail ? 1 : 0);
