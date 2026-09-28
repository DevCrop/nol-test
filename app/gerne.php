<?php

// 데이터베이스 클래스 포함
include_once $_SERVER['DOCUMENT_ROOT'].'/inc/lib/base.class.php';

// 데이터베이스 인스턴스 가져오기
$db = DB::getInstance();

try {
    // SQL 쿼리 실행
    $query = "SELECT * FROM nb_board WHERE board_no = 12 AND sitekey = 'BLUESQ' AND is_view = 'Y' AND COALESCE(is_secret, 'N') <> 'Y'";
    $stmt = $db->prepare($query);
    $stmt->execute();

    // 결과 가져오기
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($rows as &$row) unset($row['secret_pwd']);
    unset($row);

    // JSON 응답 반환
    header('Content-Type: application/json');
    echo json_encode(array(
        'rows' => $rows,
    ), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
} 

catch (PDOException $e) {
    error_log('app/gerne.php database error: ' . $e->getMessage());
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode(array(
        'error' => 'Internal server error.',
    ));
}

?>

