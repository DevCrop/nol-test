<?php

// 이미지 초기화 (120 x 30 픽셀)
$image = imagecreatetruecolor(120, 30);
if (!$image) {
    die('이미지를 생성할 수 없습니다.');
}

// 배경색 설정 및 색상 할당
$background = imagecolorallocate($image, 11, 32, 63);
if ($background === false) {
    imagedestroy($image);
    die('배경색을 설정할 수 없습니다.');
}
imagefill($image, 0, 0, $background);

$linecolor = imagecolorallocate($image, 255, 255, 255);
$textcolor = imagecolorallocate($image, 255, 255, 255);

session_start();

// 캔버스에 랜덤 숫자 추가
$digit = '';
for ($x = 15; $x <= 95; $x += 20) {
    $num = rand(0, 9);
    $digit .= $num;
    $fontSize = rand(3, 5);
    $yPosition = rand(2, 14);
    imagechar($image, $fontSize, $x, $yPosition, (string)$num, $textcolor);
}

// 세션 변수에 숫자 기록
$_SESSION['captcha_secure_quick'] = $digit; 


// 이미지 출력 및 정리
header('Content-Type: image/png');
imagepng($image);
imagedestroy($image);

?>
