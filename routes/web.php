<?php

use Database\DB;
use Http\Request;
use Http\Response;

// 사이트 유니크 키 (base.class.php에서 정의되지만, 라우트에서도 사용)
if (!isset($NO_SITE_UNIQUE_KEY)) {
    $NO_SITE_UNIQUE_KEY = "NOLTHE";
}

/** @var \Routing\Router $router */
$router = app()->router();

/* =========================
 * 공용/홈/정책
 * ========================= */
$router->get('/', function () {
    $db = DB::getInstance();
    global $NO_SITE_UNIQUE_KEY;

    // 공지사항 최신 4개 조회 (board_no = 37)
    $noticeBoardNo = 37;
    $noticeStmt = $db->prepare("
        SELECT a.no, a.title, a.regdate, a.is_notice, a.category_no,
               c.name as category_name
        FROM nb_board a
        LEFT JOIN nb_board_category c ON a.category_no = c.no
        WHERE a.sitekey = :sitekey AND a.board_no = :board_no
        ORDER BY a.is_notice DESC, a.sort_no ASC, a.regdate DESC
        LIMIT 4
    ");
    $noticeStmt->execute([
        ':sitekey' => $NO_SITE_UNIQUE_KEY,
        ':board_no' => $noticeBoardNo
    ]);
    $noticeRows = $noticeStmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

    // 뷰용 매핑
    $notices = array_map(function ($row) {
        return [
            'no' => (int)$row['no'],
            'title' => (string)$row['title'],
            'regdate' => (string)$row['regdate'],
            'is_notice' => (string)$row['is_notice'],
            'category_no' => (int)($row['category_no'] ?? 0),
            'category_name' => (string)($row['category_name'] ?? ''),
        ];
    }, $noticeRows);

    // WHAT'S ON — 노출(is_published) 활성화 + 기간이 지나지 않은 공연
    $worksStmt = $db->prepare("
        SELECT 
            id, title, subtitle, venue, genre, 
            start_date, end_date, thumb_image
        FROM nb_works 
        WHERE is_published = 1
          AND (end_date >= :today OR end_date IS NULL)
        ORDER BY sort_order ASC, id DESC
    ");
    $worksStmt->execute([':today' => date('Y-m-d')]);
    $worksRows = $worksStmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

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

    // 뷰용 매핑
    $works = array_map(function ($row) use ($venueMap, $genreMap) {
        $venueName = $venueMap[$row['venue']] ?? '';
        $genreName = $genreMap[$row['genre']] ?? '';

        $startDate = $row['start_date'] ? date('Y.m.d', strtotime($row['start_date'])) : '';
        $endDate = $row['end_date'] ? date('Y.m.d', strtotime($row['end_date'])) : '';
        $period = ($startDate && $endDate) ? "{$startDate} ~ {$endDate}" : '';

        $thumbImage = $row['thumb_image'] ?: '/resource/images/works/poster_img_1.png';

        return [
            'id' => (int)$row['id'],
            'title' => (string)$row['title'],
            'subtitle' => (string)($row['subtitle'] ?? ''),
            'venue' => (int)($row['venue'] ?? 0),
            'venue_name' => $venueName,
            'genre' => (int)($row['genre'] ?? 0),
            'genre_name' => $genreName,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'period' => $period,
            'thumb_image' => $thumbImage,
        ];
    }, $worksRows);

    // 메인 히어로 배너 조회 (banner_type = 1: 메인 페이지)
    // 노출 여부 + 등록기간(is_unlimited 또는 display_start_at~display_end_at) 필터
    $bannerStmt = $db->prepare("
        SELECT 
            id, title, description, banner_image, banner_image_mobile, has_link, link_url, 
            is_target, duration, start_at, end_at, is_unlimited, hall_id
        FROM nb_banners
        WHERE banner_type = 1
          AND is_active = 1
          AND (
              is_unlimited = 1
              OR (
                  display_start_at IS NOT NULL AND display_end_at IS NOT NULL
                  AND CURDATE() >= DATE(display_start_at)
                  AND CURDATE() <= DATE(display_end_at)
              )
          )
        ORDER BY sort_no ASC, id DESC
    ");
    $bannerStmt->execute();
    $bannerRows = $bannerStmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

    // 홀 이름 매핑
    $hallMap = [
        1 => '우리카드홀',
        2 => '우리투자증권홀',
        3 => '기타'
    ];

    // 뷰용 매핑
    $banners = array_map(function ($row) use ($hallMap) {
        $hasLink = (int)($row['has_link'] ?? 2) === 1;
        $linkUrl = $hasLink ? ($row['link_url'] ?? '#') : '#';
        $target = (int)($row['is_target'] ?? 1) === 1 ? '_blank' : '_self';

        // 홀 이름 매핑
        $hallId = (int)($row['hall_id'] ?? 0);
        $hallName = $hallMap[$hallId] ?? '';

        // 기간 포맷팅
        $period = '';
        if (!empty($row['start_at']) && !empty($row['end_at'])) {
            $startDate = date('Y.m.d', strtotime($row['start_at']));
            $endDate = date('Y.m.d', strtotime($row['end_at']));
            $period = "{$startDate} ~ {$endDate}";
        } elseif (!empty($row['start_at'])) {
            $startDate = date('Y.m.d', strtotime($row['start_at']));
            $period = $startDate;
        }

        return [
            'id' => (int)$row['id'],
            'title' => (string)($row['title'] ?? ''),
            'description' => (string)($row['description'] ?? ''),
            'banner_image' => $row['banner_image'] ?? '',
            'banner_image_mobile' => $row['banner_image_mobile'] ?? '',
            'has_link' => $hasLink,
            'link_url' => $linkUrl,
            'target' => $target,
            'duration' => (int)($row['duration'] ?? 6),
            'hall_id' => $hallId,
            'hall_name' => $hallName,
            'period' => $period,
        ];
    }, $bannerRows);

    return render('pages.index', [
        'notices' => $notices,
        'works' => $works,
        'banners' => $banners,
    ]);
})->name('home');

/* =======================================================
 * 1) WHAT'S ON  (/whatson)
 * ======================================================= */
$router->group(['prefix' => '/whatson'], function ($r) {
    // What's ON 목록 (스크립트 기반 렌더링)
    $r->get('', function (Request $req, Response $res) {
        return render('pages.whatson.index');
    })->name('whatson.index');

    // What's ON 상세 (PHP 정적 렌더링)
    $r->get('/view', function (Request $req, Response $res) {
        $db = DB::getInstance();
        $id = (int)($req->query('id', 0));

        if (!$id) {
            return $res->redirect(route('whatson.index'), 302);
        }

        // 데이터 조회
        $sql = "
            SELECT 
                id, title, subtitle, venue, genre, 
                start_date, end_date, running_time, age_rating,
                inquiry, note, content_html, seat_prices,
                poster_long_html, ticket_url, thumb_image
            FROM nb_works 
            WHERE id = :id AND is_published = 1
        ";

        $stmt = $db->prepare($sql);
        $stmt->execute([':id' => $id]);
        $work = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$work) {
            return $res->redirect(route('whatson.index'), 302);
        }

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

        $work['venue_name'] = $venueMap[$work['venue']] ?? '';
        $work['genre_name'] = $genreMap[$work['genre']] ?? '';

        // 날짜 포맷
        if ($work['start_date'] && $work['end_date']) {
            $work['period'] = date('Y.m.d', strtotime($work['start_date'])) . ' – ' . date('Y.m.d', strtotime($work['end_date']));
        }

        // 썸네일 이미지 처리
        if (empty($work['thumb_image'])) {
            $work['thumb_image'] = '/resource/images/works/poster_img_1.png';
        }

        return render('pages.whatson.view', [
            'work' => $work
        ]);
    })->name('whatson.view');
});

/* =======================================================
 * 2) 공연장 소개  (/venue)
 * ======================================================= */
$router->group(['prefix' => '/venue'], function ($r) {
    $r->get('',              fn() => to_route('venue.theater'));
    $r->get('/theater',      fn() => render('pages.venue.theater'))->name('venue.theater');              // NOL 씨어터 대학로
    $r->get('/facilities',   fn() => render('pages.venue.facilities'))->name('venue.facilities');         // 공연 시설
    $r->get('/services',     fn() => render('pages.venue.services'))->name('venue.services');             // 편의시설 및 서비스
    $r->get('/floors',       fn() => render('pages.venue.floors'))->name('venue.floors');                 // 층별 안내
});

/* =======================================================
 * 3) 대관안내  (/rental)
 * ======================================================= */
$router->group(['prefix' => '/rental'], function ($r) {
    $r->get('',              fn() => to_route('rental.procedure'));
    $r->get('/procedure',    fn() => render('pages.rental.procedure'))->name('rental.procedure');          // 대관 절차
    $r->get('/fee',          fn() => render('pages.rental.fee'))->name('rental.fee');                     // 대관료
    // 대관 자료 (board_no = 39)
    $r->get('/materials', function (Request $req, Response $res) {
        $db = DB::getInstance();
        global $NO_SITE_UNIQUE_KEY;

        $materialsBoardNo = 39;

        // 카테고리 목록 조회
        $catStmt = $db->prepare("
            SELECT no, name, sort_no
            FROM nb_board_category
            WHERE sitekey = :sitekey AND board_no = :board_no
            ORDER BY sort_no ASC, no ASC
        ");
        $catStmt->execute([':sitekey' => $NO_SITE_UNIQUE_KEY, ':board_no' => $materialsBoardNo]);
        $categories = $catStmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        // 카테고리별 데이터 조회
        $materialsByCategory = [];
        foreach ($categories as $category) {
            $stmt = $db->prepare("
                SELECT a.no, a.title, a.file_attach_1, a.file_attach_origin_1, a.sort_no, a.category_no,
                       c.name as category_name
                FROM nb_board a
                LEFT JOIN nb_board_category c ON a.category_no = c.no
                WHERE a.sitekey = :sitekey AND a.board_no = :board_no AND a.category_no = :category_no
                ORDER BY a.sort_no ASC, a.regdate DESC
            ");
            $stmt->execute([
                ':sitekey' => $NO_SITE_UNIQUE_KEY,
                ':board_no' => $materialsBoardNo,
                ':category_no' => $category['no']
            ]);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

            $materialsByCategory[$category['no']] = [
                'category_name' => $category['name'],
                'category_no' => (int)$category['no'],
                'items' => array_map(function ($row) {
                    return [
                        'no' => (int)$row['no'],
                        'title' => (string)$row['title'],
                        'file_attach_1' => (string)($row['file_attach_1'] ?? ''),
                        'file_attach_origin_1' => (string)($row['file_attach_origin_1'] ?? ''),
                        'sort_no' => (int)($row['sort_no'] ?? 0),
                    ];
                }, $rows)
            ];
        }

        return render('pages.rental.materials', [
            'materialsByCategory' => $materialsByCategory,
            'categories' => $categories
        ]);
    })->name('rental.materials');         // 대관 자료

    // 대관 신청 안내 브릿지 (메뉴에서 먼저 진입)
    $r->get('/guide', function (Request $req, Response $res) {
        $db = DB::getInstance();
        global $NO_SITE_UNIQUE_KEY;
        $rentalNotice = '';
        $rentalIsOpen = 0;
        $rentalStartDate = '';
        $rentalEndDate = '';
        $rentalStartDateFormatted = null;
        $rentalEndDateFormatted = null;
        try {
            $checkStmt = $db->query("SHOW COLUMNS FROM nb_siteinfo LIKE 'rental_is_open'");
            if ($checkStmt->rowCount() > 0) {
                $stmt = $db->prepare("SELECT rental_is_open, rental_start_date, rental_end_date, rental_notice FROM nb_siteinfo WHERE sitekey = :sitekey LIMIT 1");
                $stmt->execute([':sitekey' => $NO_SITE_UNIQUE_KEY]);
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($row) {
                    $rentalIsOpen = isset($row['rental_is_open']) ? (int)$row['rental_is_open'] : 0;
                    $rentalStartDate = isset($row['rental_start_date']) ? trim($row['rental_start_date']) : '';
                    $rentalEndDate = isset($row['rental_end_date']) ? trim($row['rental_end_date']) : '';
                    $rentalNotice = isset($row['rental_notice']) ? trim($row['rental_notice']) : '';
                }
            }
        } catch (Exception $e) {}
        $canApply = false;
        if ($rentalIsOpen === 1) {
            if (!empty($rentalStartDate) && !empty($rentalEndDate)) {
                $today = date('Y-m-d');
                try {
                    $startDateTime = new DateTime($rentalStartDate);
                    $endDateTime = new DateTime($rentalEndDate);
                    $todayDateTime = new DateTime($today);
                    $canApply = ($todayDateTime >= $startDateTime && $todayDateTime <= $endDateTime);
                } catch (Exception $e) {}
            } else {
                $canApply = true;
            }
        }
        if (!empty($rentalStartDate) && !empty($rentalEndDate)) {
            $rentalStartDateFormatted = date('Y년 m월 d일', strtotime($rentalStartDate));
            $rentalEndDateFormatted = date('Y년 m월 d일', strtotime($rentalEndDate));
        }
        return render('pages.rental.guide', [
            'canApply' => $canApply,
            'rentalNotice' => $rentalNotice,
            'rentalStartDateFormatted' => $rentalStartDateFormatted,
            'rentalEndDateFormatted' => $rentalEndDateFormatted,
        ]);
    })->name('rental.guide');

    // 대관 신청 (접수 폼)
    $r->get('/apply', function (Request $req, Response $res) {
        $db = DB::getInstance();
        global $NO_SITE_UNIQUE_KEY;

        // 대관 신청 가능 여부 체크를 위한 데이터 조회
        try {
            // 먼저 컬럼 존재 여부 확인
            $checkStmt = $db->query("SHOW COLUMNS FROM nb_siteinfo LIKE 'rental_is_open'");
            $hasRentalColumns = $checkStmt->rowCount() > 0;

            if ($hasRentalColumns) {
                $cols = 'rental_is_open, rental_start_date, rental_end_date, rental_notice';
                try {
                    $cc = $db->query("SHOW COLUMNS FROM nb_siteinfo LIKE 'rental_apply_notice'");
                    if ($cc->rowCount() > 0) {
                        $cols .= ', rental_apply_notice';
                    }
                } catch (Exception $e) {}
                $stmt = $db->prepare("SELECT {$cols} FROM nb_siteinfo WHERE sitekey = :sitekey LIMIT 1");
                $stmt->execute([':sitekey' => $NO_SITE_UNIQUE_KEY]);
                $siteinfo = $stmt->fetch(PDO::FETCH_ASSOC);
                if (!$siteinfo) {
                    $siteinfo = [];
                }

                $rentalIsOpen = isset($siteinfo['rental_is_open']) ? (int)$siteinfo['rental_is_open'] : 0;
                $rentalStartDate = isset($siteinfo['rental_start_date']) ? trim($siteinfo['rental_start_date']) : '';
                $rentalEndDate = isset($siteinfo['rental_end_date']) ? trim($siteinfo['rental_end_date']) : '';
                $rentalNotice = isset($siteinfo['rental_apply_notice']) && trim($siteinfo['rental_apply_notice']) !== ''
                    ? trim($siteinfo['rental_apply_notice'])
                    : (isset($siteinfo['rental_notice']) ? trim($siteinfo['rental_notice']) : '');
            } else {
                $rentalIsOpen = 0;
                $rentalStartDate = '';
                $rentalEndDate = '';
                $rentalNotice = '';
            }
        } catch (Exception $e) {
            // 에러 발생 시 기본값
            $rentalIsOpen = 0;
            $rentalStartDate = '';
            $rentalEndDate = '';
            $rentalNotice = '';
        }

        // 대관 신청 가능 여부 계산
        $canApply = false;
        $isRentalOpen = $rentalIsOpen === 1;

        if ($isRentalOpen) {
            if (!empty($rentalStartDate) && !empty($rentalEndDate)) {
                $today = date('Y-m-d');
                try {
                    $startDateTime = new DateTime($rentalStartDate);
                    $endDateTime = new DateTime($rentalEndDate);
                    $todayDateTime = new DateTime($today);
                    $canApply = ($todayDateTime >= $startDateTime && $todayDateTime <= $endDateTime);
                } catch (Exception $e) {
                    $canApply = false;
                }
            } else {
                // 날짜가 설정되어 있지 않으면 항상 신청 가능
                $canApply = true;
            }
        }

        // 날짜 포맷팅 (뷰에서 사용)
        $rentalStartDateFormatted = null;
        $rentalEndDateFormatted = null;
        if (!empty($rentalStartDate) && !empty($rentalEndDate)) {
            $rentalStartDateFormatted = date('Y년 m월 d일', strtotime($rentalStartDate));
            $rentalEndDateFormatted = date('Y년 m월 d일', strtotime($rentalEndDate));
        }

        return render('pages.rental.apply', [
            'canApply' => $canApply,
            'isRentalOpen' => $isRentalOpen,
            'rentalStartDate' => !empty($rentalStartDate) ? $rentalStartDate : null,
            'rentalEndDate' => !empty($rentalEndDate) ? $rentalEndDate : null,
            'rentalStartDateFormatted' => $rentalStartDateFormatted,
            'rentalEndDateFormatted' => $rentalEndDateFormatted,
            'rentalNotice' => $rentalNotice ?? '',
        ]);
    })->name('rental.apply');
});

/* =======================================================
 * 4) 고객센터  (/customer)
 * ======================================================= */
$router->group(['prefix' => '/customer'], function ($r) {
    $r->get('',              fn() => to_route('customer.notice'));

    // 공지사항 (board_no = 37)
    $noticeBoardNo = 37;
    $noticePerPage = 10;

    // 공지사항 목록
    $r->get('/notice', function (Request $req, Response $res) use ($noticeBoardNo, $noticePerPage) {
        $db = DB::getInstance();
        global $NO_SITE_UNIQUE_KEY;

        // 쿼리스트링
        $page = max(1, (int)($req->query('page', 1)));
        $selectedCategoryNo = (int)($req->query('category_no', 0)); // 0이면 전체
        $offset = ($page - 1) * $noticePerPage;

        // 카테고리 목록 조회
        $catStmt = $db->prepare("
            SELECT no, name, sort_no
            FROM nb_board_category
            WHERE sitekey = :sitekey AND board_no = :board_no
            ORDER BY sort_no ASC, no ASC
        ");
        $catStmt->execute([':sitekey' => $NO_SITE_UNIQUE_KEY, ':board_no' => $noticeBoardNo]);
        $categories = $catStmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        // 카테고리 배열 구성
        $categoryList = [];
        foreach ($categories as $cat) {
            $categoryList[] = [
                'no' => (int)$cat['no'],
                'name' => $cat['name'],
                'is_active' => ($selectedCategoryNo > 0 && (int)$cat['no'] === $selectedCategoryNo)
            ];
        }

        // 총 개수 조회
        $countSql = "
            SELECT COUNT(*) AS cnt
            FROM nb_board
            WHERE sitekey = :sitekey AND board_no = :board_no
        ";
        if ($selectedCategoryNo > 0) {
            $countSql .= " AND category_no = :category_no";
        }
        $countStmt = $db->prepare($countSql);
        $countParams = [
            ':sitekey' => $NO_SITE_UNIQUE_KEY,
            ':board_no' => $noticeBoardNo,
        ];
        if ($selectedCategoryNo > 0) {
            $countParams[':category_no'] = $selectedCategoryNo;
        }
        $countStmt->execute($countParams);
        $total = (int)$countStmt->fetchColumn();
        $lastPage = (int)ceil($total / $noticePerPage);

        // 목록 조회
        $listSql = "
            SELECT a.no, a.title, a.regdate, a.read_cnt, a.is_notice, a.category_no,
                   a.write_name, a.thumb_image, a.sort_no,
                   c.name as category_name
            FROM nb_board a
            LEFT JOIN nb_board_category c ON a.category_no = c.no
            WHERE a.sitekey = :sitekey AND a.board_no = :board_no
        ";
        if ($selectedCategoryNo > 0) {
            $listSql .= " AND a.category_no = :category_no";
        }
        $listSql .= "
            ORDER BY a.is_notice DESC, a.sort_no ASC, a.regdate DESC
            LIMIT :limit OFFSET :offset
        ";

        $listStmt = $db->prepare($listSql);
        $listStmt->bindValue(':sitekey', $NO_SITE_UNIQUE_KEY, PDO::PARAM_STR);
        $listStmt->bindValue(':board_no', $noticeBoardNo, PDO::PARAM_INT);
        if ($selectedCategoryNo > 0) {
            $listStmt->bindValue(':category_no', $selectedCategoryNo, PDO::PARAM_INT);
        }
        $listStmt->bindValue(':limit', $noticePerPage, PDO::PARAM_INT);
        $listStmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $listStmt->execute();

        $rows = $listStmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        // 뷰용 매핑
        $boardRows = array_map(function ($row) {
            return [
                'no' => (int)$row['no'],
                'title' => (string)$row['title'],
                'regdate' => (string)$row['regdate'],
                'read_cnt' => (int)$row['read_cnt'],
                'is_notice' => (string)$row['is_notice'],
                'category_no' => (int)($row['category_no'] ?? 0),
                'category_name' => (string)($row['category_name'] ?? ''),
                'write_name' => (string)($row['write_name'] ?? ''),
                'thumb_image' => (string)($row['thumb_image'] ?? ''),
                'sort_no' => (int)($row['sort_no'] ?? 0),
            ];
        }, $rows);

        return render('pages.customer.notice', [
            'boardRows' => $boardRows,
            'categories' => $categoryList,
            'selectedCategoryNo' => $selectedCategoryNo,
            'page' => $page,
            'perPage' => $noticePerPage,
            'total' => $total,
            'lastPage' => $lastPage,
        ]);
    })->name('customer.notice');

    // 공지사항 상세
    $r->get('/notice/view/{no}', function (Request $req, Response $res, string $no) use ($noticeBoardNo) {
        $db = DB::getInstance();
        global $NO_SITE_UNIQUE_KEY;

        $categoryNo = (int)($req->query('category_no', 0));

        // 카테고리 목록 조회
        $catStmt = $db->prepare("
            SELECT no, name, sort_no
            FROM nb_board_category
            WHERE sitekey = :sitekey AND board_no = :board_no
            ORDER BY sort_no ASC, no ASC
        ");
        $catStmt->execute([':sitekey' => $NO_SITE_UNIQUE_KEY, ':board_no' => $noticeBoardNo]);
        $categories = $catStmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        // 카테고리 배열 구성
        $categoryList = [];
        foreach ($categories as $cat) {
            $categoryList[] = [
                'no' => (int)$cat['no'],
                'name' => $cat['name'],
                'is_active' => ($categoryNo > 0 && (int)$cat['no'] === $categoryNo)
            ];
        }

        // 게시글 조회
        $stmt = $db->prepare("
            SELECT a.no, a.title, a.contents, a.regdate, a.read_cnt, a.category_no,
                   a.write_name, a.thumb_image, a.isFile,
                   a.file_attach_1, a.file_attach_origin_1,
                   a.file_attach_2, a.file_attach_origin_2,
                   a.file_attach_3, a.file_attach_origin_3,
                   a.file_attach_4, a.file_attach_origin_4,
                   a.file_attach_5, a.file_attach_origin_5,
                   a.direct_url, a.is_secret,
                   c.name as category_name
            FROM nb_board a
            LEFT JOIN nb_board_category c ON a.category_no = c.no
            WHERE a.sitekey = :sitekey AND a.board_no = :board_no AND a.no = :no
            LIMIT 1
        ");
        $stmt->execute([
            ':sitekey' => $NO_SITE_UNIQUE_KEY,
            ':board_no' => $noticeBoardNo,
            ':no' => (int)$no
        ]);
        $noticeData = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$noticeData) {
            return $res->redirect(route('customer.notice', [
                'category_no' => $categoryNo ?: null,
            ]), 302);
        }

        // 조회수 증가
        $updateStmt = $db->prepare("UPDATE nb_board SET read_cnt = read_cnt + 1 WHERE no = :no");
        $updateStmt->execute([':no' => (int)$no]);

        // 이전글/다음글 조회
        $navWhere = "sitekey = :sitekey AND board_no = :board_no";
        $navParams = [
            ':sitekey' => $NO_SITE_UNIQUE_KEY,
            ':board_no' => $noticeBoardNo
        ];

        if ($categoryNo > 0) {
            $navWhere .= " AND category_no = :category_no";
            $navParams[':category_no'] = $categoryNo;
        }

        // 이전글
        $prevStmt = $db->prepare("
            SELECT no, title
            FROM nb_board
            WHERE {$navWhere} AND no < :no
            ORDER BY is_notice DESC, sort_no ASC, regdate DESC, no DESC
            LIMIT 1
        ");
        foreach ($navParams as $key => $value) {
            $prevStmt->bindValue($key, $value);
        }
        $prevStmt->bindValue(':no', (int)$no, PDO::PARAM_INT);
        $prevStmt->execute();
        $prevRow = $prevStmt->fetch(PDO::FETCH_ASSOC) ?: null;

        $prevPost = null;
        if ($prevRow) {
            $prevPost = [
                'title' => $prevRow['title'],
                'url' => route('customer.notice-view', ['no' => $prevRow['no'], 'category_no' => $categoryNo ?: null])
            ];
        }

        // 다음글
        $nextStmt = $db->prepare("
            SELECT no, title
            FROM nb_board
            WHERE {$navWhere} AND no > :no
            ORDER BY is_notice DESC, sort_no ASC, regdate ASC, no ASC
            LIMIT 1
        ");
        foreach ($navParams as $key => $value) {
            $nextStmt->bindValue($key, $value);
        }
        $nextStmt->bindValue(':no', (int)$no, PDO::PARAM_INT);
        $nextStmt->execute();
        $nextRow = $nextStmt->fetch(PDO::FETCH_ASSOC) ?: null;

        $nextPost = null;
        if ($nextRow) {
            $nextPost = [
                'title' => $nextRow['title'],
                'url' => route('customer.notice-view', ['no' => $nextRow['no'], 'category_no' => $categoryNo ?: null])
            ];
        }

        return render('pages.customer.notice-view', [
            'noticeData' => $noticeData,
            'categoryNo' => $categoryNo,
            'categories' => $categoryList,
            'prevPost' => $prevPost,
            'nextPost' => $nextPost,
        ]);
    })->name('customer.notice-view');

    // FAQ
    $r->get('/faq', function (Request $req, Response $res) {
        $db = DB::getInstance();

        // 선택된 카테고리 (기본값: 전체)
        $selectedCategoryNo = (int)($req->query('category_no', 0));

        // FAQ 카테고리 정의
        $faqCategories = [
            1 => '공연',
            2 => '관람',
            3 => '편의',
        ];

        // 카테고리 배열 구성
        $categoryList = [];
        foreach ($faqCategories as $code => $name) {
            $categoryList[] = [
                'no' => $code,
                'name' => $name,
                'is_active' => ($selectedCategoryNo > 0 && $code === $selectedCategoryNo)
            ];
        }

        // WHERE 조건 구성
        $where = "WHERE is_active = 1";
        $params = [];

        if ($selectedCategoryNo > 0) {
            $where .= " AND categories = :categories";
            $params[':categories'] = $selectedCategoryNo;
        }

        // FAQ 데이터 조회 (sort_no로 정렬)
        $sql = "
            SELECT id, categories, question, answer, sort_no
            FROM nb_faqs
            {$where}
            ORDER BY sort_no ASC, id ASC
        ";
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $faqRows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        // FAQ 데이터를 컴포넌트 형식으로 변환
        $displayItems = [];
        foreach ($faqRows as $row) {
            $displayItems[] = [
                'title' => $row['question'],
                'content' => $row['answer'], // HTML 포함 가능
                'isOpen' => false,
            ];
        }

        return render('pages.customer.faq', [
            'faqItems' => $displayItems,
            'categories' => $categoryList,
            'selectedCategoryNo' => $selectedCategoryNo,
        ]);
    })->name('customer.faq');
    $r->get('/directions',   fn() => render('pages.customer.directions'))->name('customer.directions');    // 오시는길
});

$router->group(['prefix' => '/info'], function ($r) {
    $r->get('/usage', fn() => render('pages.info.usage'))->name('info.usage');                           // 이용 안내
    $r->get('/patient-rights', fn() => render('pages.info.patient-rights'))->name('info.patient-rights'); // 환자 권리 장전
    $r->get('/noncovered', fn() => render('pages.info.noncovered'))->name('info.noncovered');             // 비급여수가
    $r->get('/certificates-fee', fn() => render('pages.info.certificates'))->name('info.certificates');   // 제증명수수료
});

$router->group(['prefix' => '/legal'], function ($r) {
    // 개인정보처리방침
    $r->get('/privacy', function (Request $req, Response $res) {
        $db = DB::getInstance();

        // 선택된 ID (쿼리스트링에서 가져오기, 없으면 최신)
        $selectedId = (int)($req->query('id', 0));

        // 모든 개인정보처리방침 조회 (적용 날짜 내림차순)
        $stmt = $db->prepare("
            SELECT id, title, content, apply_date, created_at, updated_at
            FROM nb_privacy_policy
            ORDER BY apply_date DESC, id DESC
        ");
        $stmt->execute();
        $allPolicies = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        // 선택된 정책 (없으면 최신)
        $selectedPolicy = null;
        if ($selectedId > 0) {
            foreach ($allPolicies as $policy) {
                if ((int)$policy['id'] === $selectedId) {
                    $selectedPolicy = $policy;
                    break;
                }
            }
        }

        // 선택된 정책이 없으면 최신 정책 사용
        if (!$selectedPolicy && !empty($allPolicies)) {
            $selectedPolicy = $allPolicies[0];
            $selectedId = (int)$selectedPolicy['id'];
        }

        // 탭용 데이터 (날짜 포맷)
        $tabs = array_map(function ($policy) use ($selectedId) {
            $applyDate = $policy['apply_date'] ? date('Y.m.d', strtotime($policy['apply_date'])) : '';
            return [
                'id' => (int)$policy['id'],
                'date' => $applyDate,
                'is_active' => (int)$policy['id'] === $selectedId,
            ];
        }, $allPolicies);

        return render('pages.legal.privacy', [
            'policies' => $allPolicies,
            'tabs' => $tabs,
            'selectedPolicy' => $selectedPolicy,
            'selectedId' => $selectedId,
        ]);
    })->name('legal.privacy');

    // 기업공고 (board_no = 38)
    $announcementBoardNo = 38;
    $announcementPerPage = 10;

    // 기업공고 목록
    $r->get('/announcement', function (Request $req, Response $res) use ($announcementBoardNo, $announcementPerPage) {
        $db = DB::getInstance();
        global $NO_SITE_UNIQUE_KEY;

        // 쿼리스트링
        $page = max(1, (int)($req->query('page', 1)));
        $selectedCategoryNo = (int)($req->query('category_no', 0)); // 0이면 전체
        $offset = ($page - 1) * $announcementPerPage;

        // 카테고리 목록 조회
        $catStmt = $db->prepare("
            SELECT no, name, sort_no
            FROM nb_board_category
            WHERE sitekey = :sitekey AND board_no = :board_no
            ORDER BY sort_no ASC, no ASC
        ");
        $catStmt->execute([':sitekey' => $NO_SITE_UNIQUE_KEY, ':board_no' => $announcementBoardNo]);
        $categories = $catStmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        // 카테고리 배열 구성
        $categoryList = [];
        foreach ($categories as $cat) {
            $categoryList[] = [
                'no' => (int)$cat['no'],
                'name' => $cat['name'],
                'is_active' => ($selectedCategoryNo > 0 && (int)$cat['no'] === $selectedCategoryNo)
            ];
        }

        // 총 개수 조회
        $countSql = "
            SELECT COUNT(*) AS cnt
            FROM nb_board
            WHERE sitekey = :sitekey AND board_no = :board_no
        ";
        if ($selectedCategoryNo > 0) {
            $countSql .= " AND category_no = :category_no";
        }
        $countStmt = $db->prepare($countSql);
        $countParams = [
            ':sitekey' => $NO_SITE_UNIQUE_KEY,
            ':board_no' => $announcementBoardNo,
        ];
        if ($selectedCategoryNo > 0) {
            $countParams[':category_no'] = $selectedCategoryNo;
        }
        $countStmt->execute($countParams);
        $total = (int)$countStmt->fetchColumn();
        $lastPage = (int)ceil($total / $announcementPerPage);

        // 목록 조회
        $listSql = "
            SELECT a.no, a.title, a.regdate, a.read_cnt, a.is_notice, a.category_no,
                   a.write_name, a.thumb_image, a.sort_no,
                   c.name as category_name
            FROM nb_board a
            LEFT JOIN nb_board_category c ON a.category_no = c.no
            WHERE a.sitekey = :sitekey AND a.board_no = :board_no
        ";
        if ($selectedCategoryNo > 0) {
            $listSql .= " AND a.category_no = :category_no";
        }
        $listSql .= "
            ORDER BY a.is_notice DESC, a.sort_no ASC, a.regdate DESC
            LIMIT :limit OFFSET :offset
        ";

        $listStmt = $db->prepare($listSql);
        $listStmt->bindValue(':sitekey', $NO_SITE_UNIQUE_KEY, PDO::PARAM_STR);
        $listStmt->bindValue(':board_no', $announcementBoardNo, PDO::PARAM_INT);
        if ($selectedCategoryNo > 0) {
            $listStmt->bindValue(':category_no', $selectedCategoryNo, PDO::PARAM_INT);
        }
        $listStmt->bindValue(':limit', $announcementPerPage, PDO::PARAM_INT);
        $listStmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $listStmt->execute();

        $rows = $listStmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        // 뷰용 매핑
        $boardRows = array_map(function ($row) {
            return [
                'no' => (int)$row['no'],
                'title' => (string)$row['title'],
                'regdate' => (string)$row['regdate'],
                'read_cnt' => (int)$row['read_cnt'],
                'is_notice' => (string)$row['is_notice'],
                'category_no' => (int)($row['category_no'] ?? 0),
                'category_name' => (string)($row['category_name'] ?? ''),
                'write_name' => (string)($row['write_name'] ?? ''),
                'thumb_image' => (string)($row['thumb_image'] ?? ''),
                'sort_no' => (int)($row['sort_no'] ?? 0),
            ];
        }, $rows);

        return render('pages.legal.announcement', [
            'boardRows' => $boardRows,
            'categories' => $categoryList,
            'selectedCategoryNo' => $selectedCategoryNo,
            'page' => $page,
            'perPage' => $announcementPerPage,
            'total' => $total,
            'lastPage' => $lastPage,
        ]);
    })->name('legal.announcement');

    // 기업공고 상세
    $r->get('/announcement/view/{no}', function (Request $req, Response $res, string $no) use ($announcementBoardNo) {
        $db = DB::getInstance();
        global $NO_SITE_UNIQUE_KEY;

        $categoryNo = (int)($req->query('category_no', 0));

        // 카테고리 목록 조회
        $catStmt = $db->prepare("
            SELECT no, name, sort_no
            FROM nb_board_category
            WHERE sitekey = :sitekey AND board_no = :board_no
            ORDER BY sort_no ASC, no ASC
        ");
        $catStmt->execute([':sitekey' => $NO_SITE_UNIQUE_KEY, ':board_no' => $announcementBoardNo]);
        $categories = $catStmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        // 카테고리 배열 구성
        $categoryList = [];
        foreach ($categories as $cat) {
            $categoryList[] = [
                'no' => (int)$cat['no'],
                'name' => $cat['name'],
                'is_active' => ($categoryNo > 0 && (int)$cat['no'] === $categoryNo)
            ];
        }

        // 게시글 조회
        $stmt = $db->prepare("
            SELECT a.no, a.title, a.contents, a.regdate, a.read_cnt, a.category_no,
                   a.write_name, a.thumb_image, a.isFile,
                   a.file_attach_1, a.file_attach_origin_1,
                   a.file_attach_2, a.file_attach_origin_2,
                   a.file_attach_3, a.file_attach_origin_3,
                   a.file_attach_4, a.file_attach_origin_4,
                   a.file_attach_5, a.file_attach_origin_5,
                   a.direct_url, a.is_secret,
                   c.name as category_name
            FROM nb_board a
            LEFT JOIN nb_board_category c ON a.category_no = c.no
            WHERE a.sitekey = :sitekey AND a.board_no = :board_no AND a.no = :no
            LIMIT 1
        ");
        $stmt->execute([
            ':sitekey' => $NO_SITE_UNIQUE_KEY,
            ':board_no' => $announcementBoardNo,
            ':no' => (int)$no
        ]);
        $announcementData = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$announcementData) {
            return $res->redirect(route('legal.announcement', [
                'category_no' => $categoryNo ?: null,
            ]), 302);
        }

        // 조회수 증가
        $updateStmt = $db->prepare("UPDATE nb_board SET read_cnt = read_cnt + 1 WHERE no = :no");
        $updateStmt->execute([':no' => (int)$no]);

        // 이전글/다음글 조회
        $navWhere = "sitekey = :sitekey AND board_no = :board_no";
        $navParams = [
            ':sitekey' => $NO_SITE_UNIQUE_KEY,
            ':board_no' => $announcementBoardNo
        ];

        if ($categoryNo > 0) {
            $navWhere .= " AND category_no = :category_no";
            $navParams[':category_no'] = $categoryNo;
        }

        // 이전글
        $prevStmt = $db->prepare("
            SELECT no, title
            FROM nb_board
            WHERE {$navWhere} AND no < :no
            ORDER BY is_notice DESC, sort_no ASC, regdate DESC, no DESC
            LIMIT 1
        ");
        foreach ($navParams as $key => $value) {
            $prevStmt->bindValue($key, $value);
        }
        $prevStmt->bindValue(':no', (int)$no, PDO::PARAM_INT);
        $prevStmt->execute();
        $prevRow = $prevStmt->fetch(PDO::FETCH_ASSOC) ?: null;

        $prevPost = null;
        if ($prevRow) {
            $prevPost = [
                'title' => $prevRow['title'],
                'url' => route('legal.announcement-view', ['no' => $prevRow['no'], 'category_no' => $categoryNo ?: null])
            ];
        }

        // 다음글
        $nextStmt = $db->prepare("
            SELECT no, title
            FROM nb_board
            WHERE {$navWhere} AND no > :no
            ORDER BY is_notice DESC, sort_no ASC, regdate ASC, no ASC
            LIMIT 1
        ");
        foreach ($navParams as $key => $value) {
            $nextStmt->bindValue($key, $value);
        }
        $nextStmt->bindValue(':no', (int)$no, PDO::PARAM_INT);
        $nextStmt->execute();
        $nextRow = $nextStmt->fetch(PDO::FETCH_ASSOC) ?: null;

        $nextPost = null;
        if ($nextRow) {
            $nextPost = [
                'title' => $nextRow['title'],
                'url' => route('legal.announcement-view', ['no' => $nextRow['no'], 'category_no' => $categoryNo ?: null])
            ];
        }

        return render('pages.legal.announcement-view', [
            'announcementData' => $announcementData,
            'categoryNo' => $categoryNo,
            'categories' => $categoryList,
            'prevPost' => $prevPost,
            'nextPost' => $nextPost,
        ]);
    })->name('legal.announcement-view');
});

/* =======================================================
 * Search (검색)
 * ======================================================= */
$router->get('/search', function (Request $req, Response $res) {
    try {
        $db = DB::getInstance();
        global $NO_SITE_UNIQUE_KEY;

        $searchKeyword = $req->query('q', '');
        // URL 인코딩된 검색어 디코딩
        $searchKeyword = urldecode($searchKeyword);
        $searchKeyword = trim($searchKeyword);
        $results = [];
        $isValidKeyword = true;
        $keywordError = '';
        $faqItems = [];
        $worksItems = [];
        $faqRows = [];
        $worksRows = [];
        // 대관자료(board_no=39)는 현재 숨김 처리되어 검색 결과에 포함하지 않음
        // (나중에 다시 사용할 수 있도록 로직은 아래에서 주석으로 보관)
        $materialResultsByCategory = [];

        $buildSearchPattern = function ($keyword) {
            return '%' . $keyword . '%';
        };

        $sanitizeText = function ($value) {
            $value = strip_tags((string)$value);
            $value = html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
            $value = preg_replace('/<[^>]*>/', '', $value);
            $value = preg_replace('/\s+/', ' ', $value);
            return trim($value);
        };

        $truncateText = function ($value, int $limit = 150) use ($sanitizeText) {
            $cleaned = $sanitizeText($value);
            if (mb_strlen($cleaned) > $limit) {
                return mb_substr($cleaned, 0, $limit) . '...';
            }
            return $cleaned;
        };

        $executeSearch = function (PDO $db, string $sql, array $params, string $context) {
            try {
                $stmt = $db->prepare($sql);
                $stmt->execute($params);
                return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
            } catch (Exception $e) {
                error_log("Search error ({$context}): " . $e->getMessage());
                return [];
            }
        };

        // 검색어 유효성 검사
        if ($searchKeyword) {
            $searchKeywordWithoutSpaces = preg_replace('/\s+/', '', $searchKeyword);
            if (mb_strlen($searchKeywordWithoutSpaces) < 2) {
                $isValidKeyword = false;
                $keywordError = '검색어는 최소 2자 이상 입력해주세요.';
            }

            if ($isValidKeyword) {
                $searchPattern = $buildSearchPattern($searchKeyword);
                error_log("Search pattern: " . $searchPattern);
                error_log("Site key: " . ($NO_SITE_UNIQUE_KEY ?? 'NOT SET'));

                // nb_board 검색 (title, contents)
                $boardSql = "
                    SELECT 
                        a.no, 
                        a.title, 
                        a.contents, 
                        a.regdate,
                    a.board_no,
                    a.sitekey,
                    a.category_no,
                    a.file_attach_1,
                    a.file_attach_origin_1,
                    c.name as category_name
                    FROM nb_board a
                    LEFT JOIN nb_board_category c ON a.category_no = c.no
                    WHERE a.sitekey = :sitekey
                      AND (
                          a.title LIKE :search1 
                          OR a.contents LIKE :search2
                      )
                    ORDER BY a.regdate DESC
                    LIMIT 50
                ";
                $boardRows = $executeSearch($db, $boardSql, [
                    ':sitekey' => $NO_SITE_UNIQUE_KEY,
                    ':search1' => $searchPattern,
                    ':search2' => $searchPattern
                ], 'nb_board');

                if (empty($boardRows)) {
                    $debugSql = "
                        SELECT no, title, sitekey 
                        FROM nb_board 
                        WHERE title LIKE :search 
                        LIMIT 5
                    ";
                    $debugRows = $executeSearch($db, $debugSql, [':search' => $searchPattern], 'nb_board-debug');
                    if (!empty($debugRows)) {
                        error_log("Search debug: Found " . count($debugRows) . " rows with different sitekey. Current sitekey: " . $NO_SITE_UNIQUE_KEY);
                    }
                }

                // nb_faqs 검색 (question, answer) - 최적화된 쿼리
                $faqSql = "
                    SELECT 
                        id,
                        question as title,
                        answer as contents,
                        categories,
                        sort_no
                    FROM nb_faqs
                    WHERE is_active = 1
                      AND (
                          question LIKE :search1 
                          OR answer LIKE :search2
                      )
                    ORDER BY 
                        CASE 
                            WHEN question LIKE :search3 THEN 1
                            WHEN answer LIKE :search4 THEN 2
                            ELSE 3
                        END,
                        sort_no ASC, 
                        id ASC
                    LIMIT 50
                ";
                $faqRows = $executeSearch($db, $faqSql, [
                    ':search1' => $searchPattern,
                    ':search2' => $searchPattern,
                    ':search3' => $searchPattern,
                    ':search4' => $searchPattern
                ], 'nb_faqs');

                foreach ($boardRows as $row) {
                    $boardNo = isset($row['board_no']) ? (int)$row['board_no'] : 0;
                    $categoryName = !empty($row['category_name']) ? $row['category_name'] : '게시판';

                    // board_no=39(대관자료)는 검색 결과에서 제외
                    // 나중에 다시 필요하면 아래 주석 블록을 활성화하고, continue를 제거하세요.
                    /*
                    if ($boardNo === 39) {
                        $fileAttach = trim((string)($row['file_attach_1'] ?? ''));
                        if ($fileAttach !== '') {
                            $categoryKey = (int)($row['category_no'] ?? 0);
                            $materialResultsByCategory[$categoryKey]['category_name'] = $categoryName;
                            $materialResultsByCategory[$categoryKey]['items'][] = [
                                'title' => (string)$row['title'],
                                'file_attach_1' => $fileAttach,
                                'file_attach_origin_1' => (string)($row['file_attach_origin_1'] ?? ''),
                                'regdate' => $row['regdate'] ?? ''
                            ];
                        }
                        continue;
                    }
                    */
                    if ($boardNo === 39) {
                        continue;
                    }

                    if ($boardNo === 37) {
                        $categoryName = '공지사항';
                    } elseif ($boardNo === 38) {
                        $categoryName = '기업공고';
                    }

                    $url = '#';
                    if ($boardNo === 37 && isset($row['no'])) {
                        $url = route('customer.notice-view', ['no' => $row['no']]);
                    } elseif ($boardNo === 38 && isset($row['no'])) {
                        $url = route('legal.announcement-view', ['no' => $row['no']]);
                    }

                    $content = $truncateText($row['contents'] ?? '');
                    $title = $sanitizeText($row['title'] ?? '');

                    $results[] = [
                        'title' => $title,
                        'category' => $categoryName,
                        'desc' => $content,
                        'url' => $url,
                        'date' => isset($row['regdate']) && $row['regdate'] ? date('Y.m.d', strtotime($row['regdate'])) : '',
                        'type' => 'board'
                    ];
                }

                // nb_works 검색 (title, subtitle, venue, content_html) - 최적화된 쿼리
                $venueMap = [
                    1 => '우리카드홀',
                    2 => '우리투자증권홀',
                    3 => '기타'
                ];

                $worksSql = "
                    SELECT 
                        id,
                        title,
                        subtitle,
                        venue,
                        start_date,
                        end_date,
                        thumb_image,
                        content_html
                    FROM nb_works
                    WHERE is_published = 1
                      AND (
                          title LIKE :search1 
                          OR subtitle LIKE :search2
                          OR content_html LIKE :search3
                      )
                    ORDER BY 
                        CASE 
                            WHEN title LIKE :search4 THEN 1
                            WHEN subtitle LIKE :search5 THEN 2
                            WHEN content_html LIKE :search6 THEN 3
                            ELSE 4
                        END,
                        sort_order ASC, 
                        id DESC
                    LIMIT 50
                ";
                $worksRows = $executeSearch($db, $worksSql, [
                    ':search1' => $searchPattern,
                    ':search2' => $searchPattern,
                    ':search3' => $searchPattern,
                    ':search4' => $searchPattern,
                    ':search5' => $searchPattern,
                    ':search6' => $searchPattern
                ], 'nb_works');

                $faqItems = [];
                foreach ($faqRows as $row) {
                    $faqTitle = $sanitizeText($row['title'] ?? '');
                    $faqAnswer = $row['contents'] ?? '';

                    $faqItems[] = [
                        'title' => $faqTitle,
                        'content' => $faqAnswer,
                        'isOpen' => false,
                        'id' => isset($row['id']) ? (int)$row['id'] : 0
                    ];
                }

                $worksItems = [];
                foreach ($worksRows as $row) {
                    $venueId = isset($row['venue']) ? (int)$row['venue'] : 0;
                    $venueName = $venueMap[$venueId] ?? '';

                    $startDate = $row['start_date'] ? date('Y.m.d', strtotime($row['start_date'])) : '';
                    $endDate = $row['end_date'] ? date('Y.m.d', strtotime($row['end_date'])) : '';
                    $period = ($startDate && $endDate) ? "{$startDate} ~ {$endDate}" : '';

                    $thumbImage = $row['thumb_image'] ?: '/resource/images/works/poster_img_1.png';
                    if (strpos($thumbImage, 'http') !== 0 && strpos($thumbImage, '/') !== 0) {
                        $thumbImage = base_path($thumbImage);
                    }

                    $worksItems[] = [
                        'id' => (int)$row['id'],
                        'title' => (string)($row['title'] ?? ''),
                        'subtitle' => (string)($row['subtitle'] ?? ''),
                        'venue_name' => $venueName,
                        'period' => $period,
                        'thumb_image' => $thumbImage,
                        'url' => route('whatson.view') . '?id=' . (int)$row['id']
                    ];
                }
            }
        }

        // 결과를 타입별로 분리
        $boardResults = array_filter($results, fn($r) => ($r['type'] ?? '') === 'board');
        $faqResults = $faqItems ?? [];
        $worksResults = $worksItems ?? [];
        // $materialResults = array_values(array_filter($materialResultsByCategory, fn($category) => !empty($category['items'] ?? [])));
        $materialResults = [];

        return render('pages.search.index', [
            'searchKeyword' => $searchKeyword,
            'boardResults' => array_values($boardResults), // 인덱스 재정렬
            'faqResults' => $faqResults,
            'worksResults' => $worksResults,
            'materialResults' => $materialResults,
            'isValidKeyword' => $isValidKeyword,
            'keywordError' => $keywordError,
        ]);
    } catch (Exception $e) {
        // 에러 로깅 (서버에서 디버깅용)
        error_log("Search route error: " . $e->getMessage());
        error_log("Search route stack trace: " . $e->getTraceAsString());
        error_log("Search keyword: " . ($req->query('q', '') ?? ''));

        // 에러 발생 시 빈 결과 반환
        return render('pages.search.index', [
            'searchKeyword' => $req->query('q', ''),
            'boardResults' => [],
            'faqResults' => [],
            'worksResults' => [],
            'materialResults' => [],
            'isValidKeyword' => false,
            'keywordError' => '검색 중 오류가 발생했습니다.',
        ]);
    }
})->name('search');
