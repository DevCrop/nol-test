<?php

// 배너 위치 설정
$arr_banner_loc = [
    'site_main' => '메인 상단이미지',
];

// 사이트 데이터 타겟 설정
$siteDataTarget = [
    'subtitle' => '임시제목',
];


$states = [
    0 => '현재 공연',
    1 => '차기 공연',
];


$genres = [
    0 => '뮤지컬',
    1 => '콘서트',
    2 => '이벤트',
    3 => '기타',
];


$places = [
    0 => "신한카드홀",
    1 => "마스터카드홀",
    2 => "NEMO",
    3 => "기타",
    4 => "SOL트래블홀"
];

if (function_exists('isUpdateActive') && isUpdateActive()) {
    $places[0] = '우리은행홀';
    $places[4] = '우리WON뱅킹홀';
}

/**
 * 공연 시작일(start_date) 기준으로 공연장 표시명 반환
 * place=4 → 2026-03-01부터 우리WON뱅킹홀
 * place=0 → 2026-03-01부터 우리은행홀
 */
function shouldUseUpdatedPlaceName($placeKey, $referenceDate = null) {
    $key = (string)$placeKey;
    $switchDates = [
        '0' => '2026-03-01',
        '4' => '2026-03-01',
    ];

    if (!isset($switchDates[$key]) || empty($referenceDate)) {
        return false;
    }

    $referenceTimestamp = strtotime($referenceDate);
    if ($referenceTimestamp === false) {
        return false;
    }

    return $referenceTimestamp >= strtotime($switchDates[$key]);
}

function getPlaceDisplayName($placeKey, $referenceDate = null) {
    global $places;

    $key = (string)$placeKey;
    $legacyPlaceNames = [
        '0' => '신한카드홀',
        '4' => 'SOL트래블홀',
    ];
    $updatedPlaceNames = [
        '0' => '우리은행홀',
        '4' => '우리WON뱅킹홀',
    ];

    if (isset($updatedPlaceNames[$key])) {
        return shouldUseUpdatedPlaceName($key, $referenceDate)
            ? $updatedPlaceNames[$key]
            : $legacyPlaceNames[$key];
    }

    return isset($places[$placeKey]) ? $places[$placeKey] : '-';
}

$is_banner = [
    0 => "배너 미선택",
    1 => "배너 선택",
];


$workStatus = [
	0 => '종료작',
	1 => '진행 · 예정작',
];


// 창 열기 방식 설정
$_targetArr = [
    '_blank' => '새창',
    '_self' => '같은창',
];

// 게시판 타입 설정
$board_type = [
    'bbs' => '게시판',
    'gal' => '갤러리',
	
    // 홍보센터
    'new' => '뉴스',
    'not' => '공지사항',
    'com' => '기업공고',

	'faq' => 'FAQ',
	'wok' => 'whats on',
	'doc' => '대관자료',
];

// 허용된 파일 확장자
$board_file_allow = ['jpg', 'jpeg', 'png', 'gif', 'zip', 'xls', 'xlsx', 'ppt', 'pptx', 'doc', 'docx', 'pdf', 'hwp', 'mp4', 'mov', 'avi', 'txt', 'webp'];
$employment_file_allow = ['zip', 'xls', 'xlsx', 'ppt', 'pptx', 'doc', 'docx', 'pdf', 'hwp'];
$admission_file_allow = ['jpg', 'jpeg', 'png', 'gif'];




?>
