<?php

include_once $_SERVER['DOCUMENT_ROOT'].'/inc/lib/base.class.php';

// 데이터베이스 인스턴스 가져오기
$db = DB::getInstance();

try {
    // SQL 쿼리 작성
    $query = "SELECT extra4 FROM nb_board WHERE no = :no";

    // 쿼리 준비
    $stmt = $db->prepare($query);

    // 바인딩
    $stmt->bindValue(':no', 12, PDO::PARAM_INT);

    // 쿼리 실행
    $stmt->execute();

    // 결과 가져오기
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    // JSON으로 반환
    header('Content-Type: application/json');
    if ($result) {
        echo json_encode([
            'extra4' => $result['extra4'],
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    } else {
        // 결과가 없는 경우
        echo json_encode([
            'error' => 'No data found for no = 12',
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }

} catch (PDOException $e) {
    error_log('app/theater.php database error: ' . $e->getMessage());
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode([
        'error' => 'Internal server error.',
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
}

?>
