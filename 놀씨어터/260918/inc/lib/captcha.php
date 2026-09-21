<?php

session_start();
header('Content-Type: image/gif');

$captcha = '';

// 패턴 설정 및 5자리 문자 생성
$pattern = '123456789123456789';
for ($i = 0, $len = strlen($pattern) - 1; $i < 5; $i++) {
    $captcha .= $pattern[rand(0, $len)];
}

$_SESSION['captcha_secure'] = $captcha;

// 이미지 생성
$img = imagecreatetruecolor(80, 20);
if (!$img) {
    die('이미지를 생성할 수 없습니다.');
}

// 배경색, 글자색, 라인색 설정
$bgColor = imagecolorallocate($img, 0, 66, 37); // 배경색
$textColor = imagecolorallocate($img, 255, 255, 255); // 글자색
$lineColor = imagecolorallocate($img, 0, 78, 162); // 라인색

// 이미지 테두리 및 문자열 추가
imagefilledrectangle($img, 0, 0, 80, 20, $bgColor);
imagestring($img, 5, 5, 2, $captcha, $textColor);

// 이미지 출력 및 정리
imagegif($img);
imagedestroy($img);

?>
