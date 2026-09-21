<?php
require_once __DIR__ . '/security.bootstrap.php';
if (session_status() !== PHP_SESSION_ACTIVE) session_start();

// 이미지 초기화 (120 x 30 픽셀)
$image = imagecreatetruecolor(120, 30);
imagealphablending($image, false);
imagesavealpha($image, true);

// 투명 배경 설정
$transparent = imagecolorallocatealpha($image, 0, 0, 0, 127);
imagefill($image, 0, 0, $transparent);

// 텍스트 및 선 색상 설정
$textcolor = imagecolorallocate($image, 255, 255, 255);

// 랜덤한 숫자 생성 (5자리)
$captcha_code = '';
for ($x = 15; $x <= 95; $x += 20) {
    $num = rand(0, 9);
    $captcha_code .= $num;
    $fontSize = rand(3, 5);
    $yPosition = rand(5, 14);
    imagechar($image, $fontSize, $x, $yPosition, (string)$num, $textcolor);
}

// 세션에 새로운 캡차 코드 저장
$_SESSION['captcha_secure'] = $captcha_code;

// 이미지 출력
header('Content-Type: image/png');
imagepng($image);
imagedestroy($image);
?>
