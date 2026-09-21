<?php
header('Content-Type: application/json; charset=utf-8');

include_once "../inc/lib/base.class.php";

$db = DB::getInstance();
$id = (int)($_GET['id'] ?? 0);

try {
    if (!$id) {
        throw new Exception('ID가 필요합니다.');
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
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => '공연 정보를 찾을 수 없습니다.'
        ], JSON_UNESCAPED_UNICODE);
        exit;
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

    echo json_encode([
        'success' => true,
        'data' => $work
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => ClientFault::message($e),
    ], JSON_UNESCAPED_UNICODE);
}