<?php
declare(strict_types=1);

session_start();
ini_set('display_errors', '0');

require __DIR__ . '/base.class.php';
require ROOT.'/vendor/autoload.php';

use Gregwar\Captcha\CaptchaBuilder;
use Gregwar\Captcha\PhraseBuilder;

// 폰트 경로 (필요 시 후보 탐색으로 확장 가능)
$font = ROOT.'/resource/fonts/DejaVuSans/DejaVuSans.ttf';

$phraseBuilder = new PhraseBuilder(5, '0123456789');
$builder = new CaptchaBuilder(null, $phraseBuilder);

// 색상/라인 난수 설정
$bgR = random_int(230, 255); $bgG = random_int(230, 255); $bgB = random_int(230, 255);
$fgR = random_int( 30,  80); $fgG = random_int( 30,  80); $fgB = random_int( 30,  80);

$builder
  ->setBackgroundColor($bgR, $bgG, $bgB)
  ->setTextColor($fgR, $fgG, $fgB)
  ->setMaxBehindLines(random_int(2, 5))
  ->setMaxFrontLines(random_int(1, 3));

try {
    // 폰트 사전 체크
    $fontArg = is_file($font) ? $font : null;
    if ($fontArg && function_exists('imagettfbbox')) {
        if (@imagettfbbox(20, 0, $fontArg, 'TEST') === false) {
            error_log('captcha.image: imagettfbbox failed with font '.$fontArg);
            $fontArg = null;
        }
    }

    // ★ 먼저 빌드!
    $builder->build(150, 48, $fontArg);

    // 빌드된 GD 리소스에 후처리(노이즈/스트라이프/곡선)
    $im = $builder->getGd();
    $w  = imagesx($im); $h = imagesy($im);

    // 점 노이즈
    $dotCount = (int) (($w * $h) * 0.03);
    for ($i = 0; $i < $dotCount; $i++) {
        $x = random_int(0, $w - 1);
        $y = random_int(0, $h - 1);
        $c = (random_int(0,1) === 1) ? random_int(180, 240) : random_int(40, 100);
        $col = imagecolorallocate($im, $c, $c, $c);
        imagesetpixel($im, $x, $y, $col);
    }

    // 얇은 사선 스트라이프
    imagesetthickness($im, 1);
    for ($x = -$h; $x < $w; $x += random_int(10, 16)) {
        $y1 = 0; $y2 = $h;
        $x1 = $x; $x2 = $x + $h;
        $cc = imagecolorallocatealpha($im, 200, 200, 200, 85);
        imageline($im, $x1, $y1, $x2, $y2, $cc);
    }

    // Sine 곡선 1~2개
    $curveN = random_int(1, 2);
    for ($k = 0; $k < $curveN; $k++) {
        $amp = random_int((int)($h*0.12), (int)($h*0.22));
        $freq = (M_PI * random_int(2, 4)) / $w;
        $y0 = random_int((int)($h*0.25), (int)($h*0.75));
        $cc = imagecolorallocatealpha($im, random_int(60,120), random_int(60,120), random_int(60,120), 70);
        imagesetthickness($im, 1);
        $px = 0; $py = $y0 + (int)($amp * sin($freq * 0));
        for ($x = 1; $x < $w; $x++) {
            $y = $y0 + (int)($amp * sin($freq * $x + $k));
            imageline($im, $px, $py, $x, $y, $cc);
            $px = $x; $py = $y;
        }
    }

    // 세션 저장
    $_SESSION['captcha_phrase'] = $builder->getPhrase();

    // 출력
    if (ob_get_length()) { ob_clean(); }
    header_remove('X-Powered-By');
    header('Cache-Control: no-cache, no-store, must-revalidate');
    header('Pragma: no-cache');
    header('Expires: 0');
    header('Content-Type: image/jpeg');

    imagejpeg($im, null, 90);
    imagedestroy($im);
    exit;

} catch (Throwable $e) {
    error_log('captcha.image error: '.$e->getMessage());
    http_response_code(500);
    header('Content-Type: text/plain; charset=utf-8');
    echo 'captcha error';
    exit;
}
