<?php
/**
 * 관리자 메뉴 설정 (단일 소스)
 * - admin.drawer.php: 메뉴 렌더링
 * - PageContext: URL 기반 depthnum/pagenum/pageName 자동 감지
 *
 * 탭 추가 시: depthnum에 subs 항목 추가
 * 새 페이지 추가 시: PageContext::getRouteOverrides()에 경로 등록
 */
return [
    1 => [
        'key'   => 'board',
        'title' => '게시판',
        'icon'  => 'fa-list',
        'subs'  => [
            ['title' => '게시글 관리', 'url' => 'board/board.list.php'],
        ],
    ],
    2 => [
        'key'   => 'design',
        'title' => '배너 관리',
        'icon'  => 'fa-paint-roller',
        'subs'  => [
            ['title' => '메인 배너', 'url' => 'design/banner.list.php'],
            ['title' => '팝업 배너', 'url' => 'design/popup.list.php'],
        ],
    ],
    3 => [
        'key'   => 'faq',
        'title' => 'FAQ 관리',
        'icon'  => 'fa-clipboard-question',
        'url'   => 'faq/index.php',
        'subs'  => [],
    ],
    4 => [
        'key'   => 'inquiry',
        'title' => '대관 신청 관리',
        'icon'  => 'fa-envelope-open-text',
        'subs'  => [
            ['title' => '대관 신청 관리', 'url' => 'inquiry/index.php'],
            ['title' => '대관 신청 설정', 'url' => 'inquiry/setting.php'],
        ],
    ],
    5 => [
        'key'   => 'log',
        'title' => '접속 통계',
        'icon'  => 'fa-chart-simple',
        'subs'  => [
            ['title' => '일별', 'url' => 'log/log.day.php'],
            ['title' => '시간별', 'url' => 'log/log.time.php'],
            ['title' => '월별 ', 'url' => 'log/log.month.php'],
            ['title' => '연별', 'url' => 'log/log.year.php'],
        ],
    ],
    6 => [
        'key'   => 'works',
        'title' => "WHAT'S ON",
        'icon'  => 'fa-briefcase',
        'url'   => 'works/index.php',
        'subs'  => [],
    ],
    7 => [
        'key'   => 'siteinfo',
        'title' => '사이트정보관리',
        'icon'  => 'fa-gear',
        'subs'  => [
            ['title' => '사이트 정보', 'url' => 'setting/index.php'],
            ['title' => '비밀번호 변경', 'url' => 'setting/pwd.php'],
            ['title' => '페이지별 SEO', 'url' => 'setting/seo.php'],
            ['title' => '개인정보처리방침 관리', 'url' => 'privacy/index.php'],
        ],
    ],
    8 => [
        'key'   => 'account',
        'title' => '계정 및 권한 관리',
        'icon'  => 'fa-user-shield',
        'subs'  => [
            ['title' => '계정 관리', 'url' => 'account/index.php'],
            ['title' => '계정 생성', 'url' => 'account/new.php'],
            ['title' => '작업 이력', 'url' => 'account/audit.php'],
            ['title' => '개인정보 접속기록', 'url' => 'account/access.php'],
        ],
    ],
];
