<?php
header('Content-Type: application/json; charset=utf-8');

include_once "../inc/lib/base.class.php";

$db = DB::getInstance();

// 필터 및 검색 파라미터
$venue = $_GET['venue'] ?? '';
$status = $_GET['status'] ?? '';
$genre = $_GET['genre'] ?? '';
$year = $_GET['year'] ?? '';
$months = $_GET['months'] ?? '';
$search = trim($_GET['q'] ?? '');
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 12;

try {
    // WHERE 조건 구성
    $where = ["is_published = 1"];
    $params = [];

    // 공연장 필터 (1=우리카드홀, 2=우리투자증권홀, 3=기타)
    if (!empty($venue) && $venue !== 'all') {
        $venueMap = [
            'woori-card' => 1,
            'woori-securities' => 2,
            'other' => 3
        ];
        if (isset($venueMap[$venue])) {
            $where[] = "venue = :venue";
            $params[':venue'] = $venueMap[$venue];
        }
    }

    // 장르 필터 (1=뮤지컬, 2=연극, 3=콘서트, 4=이벤트, 5=기타)
    if (!empty($genre) && $genre !== 'all') {
        $genreMap = [
            'musical' => 1,
            'play' => 2,
            'concert' => 3,
            'event' => 4,
            'other' => 5
        ];
        if (isset($genreMap[$genre])) {
            $where[] = "genre = :genre";
            $params[':genre'] = $genreMap[$genre];
        }
    }

    // 진행현황 필터
    if (!empty($status) && $status !== 'all') {
        $today = date('Y-m-d');
        if ($status === 'ongoing') {
            // 진행작: 시작했고 아직 종료 안 된 공연
            $where[] = "(start_date <= :today AND (end_date >= :today2 OR end_date IS NULL))";
            $params[':today'] = $today;
            $params[':today2'] = $today;
        } elseif ($status === 'upcoming') {
            // 예정작: 아직 시작 안 한 공연
            $where[] = "start_date > :today";
            $params[':today'] = $today;
        } elseif ($status === 'completed') {
            // 종료작: 종료일이 오늘 이전
            $where[] = "end_date < :today";
            $params[':today'] = $today;
        }
    }

    // 연도 + 월 필터
    if (!empty($year) && $year !== 'all' && is_numeric($year)) {
        $monthArray = [];
        if (!empty($months) && $months !== 'all') {
            $monthArray = explode(',', $months);
            $monthArray = array_filter($monthArray, fn($m) => is_numeric($m) && $m >= 1 && $m <= 12 && $m != 'all');
        }

        if (empty($monthArray)) {
            // 월이 선택되지 않았거나 '전체'인 경우: 해당 연도 전체 기간
            $yearStart = "{$year}-01-01";
            $yearEnd = "{$year}-12-31";
            $where[] = "(start_date <= :year_end AND (end_date >= :year_start OR end_date IS NULL))";
            $params[':year_start'] = $yearStart;
            $params[':year_end'] = $yearEnd;
        } else {
            // 선택된 월들에 대한 필터링
            $monthConditions = [];
            foreach ($monthArray as $idx => $month) {
                $month = (int)$month;
                // 해당 월의 시작일과 종료일 계산
                $monthStart = sprintf("%04d-%02d-01", $year, $month);
                $daysInMonth = date('t', strtotime($monthStart));
                $monthEnd = sprintf("%04d-%02d-%02d", $year, $month, $daysInMonth);

                $startParam = ":month_start_{$idx}";
                $endParam = ":month_end_{$idx}";

                // 공연 기간이 해당 월과 겹치는 조건
                // start_date <= 월말 AND (end_date >= 월초 OR end_date IS NULL)
                $monthConditions[] = "(start_date <= {$endParam} AND (end_date >= {$startParam} OR end_date IS NULL))";
                $params[$startParam] = $monthStart;
                $params[$endParam] = $monthEnd;
            }

            if (!empty($monthConditions)) {
                $where[] = "(" . implode(" OR ", $monthConditions) . ")";
            }
        }
    }

    // 검색어 (제목, 소제목, 내용)
    if (!empty($search)) {
        $where[] = "(title LIKE :search_title OR subtitle LIKE :search_subtitle OR content_html LIKE :search_content)";
        $params[':search_title'] = "%{$search}%";
        $params[':search_subtitle'] = "%{$search}%";
        $params[':search_content'] = "%{$search}%";
    }

    $whereClause = implode(' AND ', $where);

    // 전체 개수
    $countSql = "SELECT COUNT(*) as total FROM nb_works WHERE {$whereClause}";
    $countStmt = $db->prepare($countSql);
    $countStmt->execute($params);
    $totalCount = (int)$countStmt->fetchColumn();

    // 페이지네이션 계산
    $lastPage = max(1, ceil($totalCount / $perPage));
    $page = min($page, $lastPage);
    $offset = ($page - 1) * $perPage;

    // 데이터 조회
    $today_sort = date('Y-m-d');
    $sql = "
        SELECT
            id, title, subtitle, venue, genre,
            start_date, end_date, thumb_image, poster_long_html,
            running_time, age_rating, inquiry, note, ticket_url
        FROM nb_works
        WHERE {$whereClause}
        ORDER BY
            CASE
                WHEN start_date <= '{$today_sort}' AND (end_date >= '{$today_sort}' OR end_date IS NULL) THEN 0
                WHEN start_date > '{$today_sort}' THEN 1
                ELSE 2
            END ASC,
            CASE
                WHEN start_date <= '{$today_sort}' AND (end_date >= '{$today_sort}' OR end_date IS NULL) THEN start_date
                WHEN start_date > '{$today_sort}' THEN start_date
                ELSE NULL
            END ASC,
            CASE WHEN end_date < '{$today_sort}' THEN start_date ELSE NULL END DESC
        LIMIT {$offset}, {$perPage}
    ";

    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $works = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 공연장/장르 매핑
    $venueMap = [
        1 => '우리카드홀',
        2 => '우리투자증권홀',
        3 => '기타'
    ];

    $genreMap = [
        1 => '뮤지컬',
        2 => '연극',
        3 => '콘서트',
        4 => '이벤트',
        5 => '기타'
    ];

    // 데이터 가공
    foreach ($works as &$work) {
        $work['venue_name'] = $venueMap[$work['venue']] ?? '';
        $work['genre_name'] = $genreMap[$work['genre']] ?? '';

        // 날짜 포맷
        if ($work['start_date']) {
            $work['start_date_formatted'] = date('Y.m.d', strtotime($work['start_date']));
        }
        if ($work['end_date']) {
            $work['end_date_formatted'] = date('Y.m.d', strtotime($work['end_date']));
        }

        // 썸네일 이미지 처리
        if (empty($work['thumb_image'])) {
            $work['thumb_image'] = '/resource/images/works/poster_img_1.png';
        }
    }

    $result = [
        'success' => true,
        'data' => $works,
        'pagination' => [
            'currentPage' => $page,
            'lastPage' => $lastPage,
            'total' => $totalCount,
            'perPage' => $perPage
        ]
    ];

    // 디버깅용 로그
    error_log('get_works.php 응답: total=' . $totalCount . ', works_count=' . count($works) . ', page=' . $page);

    echo json_encode($result, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
} catch (Exception $e) {
    http_response_code(500);
    error_log('get_works.php 오류: ' . $e->getMessage());

    echo json_encode([
        'success' => false,
        'message' => ClientFault::message($e),
    ], JSON_UNESCAPED_UNICODE);
}
