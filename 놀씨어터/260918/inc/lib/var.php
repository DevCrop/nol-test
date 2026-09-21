<?php

// 배너 위치 설정
$arr_banner_loc = [
    'site_main' => '메인 상단이미지',
];


$banner_types = [
    1 => '메인 페이지',
    2 => '상단 배너',
    3 => '하단 배너',
];

$popup_types = [
    1 => '메인 페이지',
    2 => '상단 배너',
    3 => '하단 배너',
];


$admin_roles = [
    1 => ['code' => 'superadmin', 'name' => '최고 관리자'],
    2 => ['code' => 'manager',    'name' => '중간 관리자'],
    3 => ['code' => 'external',   'name' => '외부인'],
];


$link_targets = [
    0 => ['label' => '현재창', 'target' => '_self'],
    1 => ['label' => '새창', 'target' => '_blank'],
];

$has_link = [
    1 => '링크',
    2 => '비링크',
];

$is_unlimited = [
    1 => '무기한 노출',
    2 => '노출 기간 설정'
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

];


// FAQ 카테고리
$faq_categories = [
    1 => '공연',
    2 => '관람',
    3 => '편의',
];

// Works 공연장
$works_venue = [
    1 => '우리카드홀',
    2 => '우리투자증권홀',
    3 => '기타',
];

// Works 진행현황
$works_status = [
    1 => '진행작',
    2 => '예정작',
    3 => '종료작',
];

// Works 장르
$works_genre = [
    1 => '뮤지컬',
    2 => '연극',
    3 => '콘서트',
    4 => '이벤트',
    5 => '기타',
];


// ACTIVE 공통
$is_active = [
    1 => "활성화",
    0 => "비활성화"
];

// 허용된 파일 확장자
$board_file_allow = ['jpg', 'jpeg', 'png', 'gif', 'zip', 'xls', 'xlsx', 'ppt', 'pptx', 'doc', 'docx', 'pdf', 'hwp', 'mp4', 'mov', 'avi', 'txt', 'webp'];


// 사이트 태그 위치
$tag_locations = [
    1 => 'HEAD',
    2 => 'BODY',
    3 => 'FOOTER',
];
