<?php

use Menu\Menu;

$menu = new Menu('site');
app()->share('menu', $menu);

/**
 * 최상위 섹션 (좌→우 순서)
 * WHAT'S ON | 공연장 소개 | 대관안내 | 고객센터
 */

/* 1) WHAT'S ON (관리자 관리) */
$whatson = $menu->add('WHAT\'S ON', '/whatson', 'whatson', ['orderIndex' => 10]);
$menu->add('WHAT\'S ON', '/whatson', 'whatson', ['parent' => $whatson, 'orderIndex' => 10]);

/* 2) 공연장 소개 */
$venue = $menu->add('공연장 소개', '/venue', 'venue', ['orderIndex' => 20]);
$menu->add('NOL 씨어터 대학로', '/venue/theater', 'venue-theater', ['parent' => $venue, 'orderIndex' => 10]);
$menu->add('공연 시설', '/venue/facilities', 'venue-facilities', ['parent' => $venue, 'orderIndex' => 20]);
$menu->add('편의시설 및 서비스', '/venue/services', 'venue-services', ['parent' => $venue, 'orderIndex' => 30]);
$menu->add('층별 안내', '/venue/floors', 'venue-floors', ['parent' => $venue, 'orderIndex' => 40]);

/* 3) 대관안내 */
$rental = $menu->add('대관안내', '/rental', 'rental', ['orderIndex' => 30]);
$menu->add('대관 절차', '/rental/procedure', 'rental-procedure', ['parent' => $rental, 'orderIndex' => 10]);
$menu->add('대관료', '/rental/fee', 'rental-fee', ['parent' => $rental, 'orderIndex' => 20]);
$menu->add('대관 자료', '/rental/materials', 'rental-materials', ['parent' => $rental, 'orderIndex' => 30]); // 자료 정리 일정 미달로 숨김 처리
$menu->add('대관 신청', '/rental/guide', 'rental-apply', ['parent' => $rental, 'orderIndex' => 40]);

/* 4) 고객센터 */
$customer = $menu->add('고객센터', '/customer', 'customer', ['orderIndex' => 40]);
$menu->add('공지사항', '/customer/notice', 'customer-notice', ['parent' => $customer, 'orderIndex' => 10]);
$menu->add('FAQ', '/customer/faq', 'customer-faq', ['parent' => $customer, 'orderIndex' => 20]);
$menu->add('오시는길', '/customer/directions', 'customer-directions', ['parent' => $customer, 'orderIndex' => 30]);
