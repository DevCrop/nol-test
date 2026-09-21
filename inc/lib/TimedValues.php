<?php
date_default_timezone_set('Asia/Seoul');

$now = new DateTime();
$changeTime = new DateTime('2025-04-01 09:00:00');

// 공연장 이름
$hallName = ($now >= $changeTime) ? 'SOL트래블홀' : '마스터카드홀';

// BI 파일 경로
$fileName = ($now >= $changeTime) ? '2602_BLUE_SQAURE_BI.zip' : '2602_BLUE_SQAURE_BI.zip';

// 로고 이미지 파일명
$logoPrefix = ($now >= $changeTime) ? 'blue_square_sol_travel_hall_logo' : 'blue_square_mastercard_hall_logo';
