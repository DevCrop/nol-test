<?php
require_once dirname(__DIR__) . '/inc/lib/db.php'; // DB 클래스 파일 경로 설정

// ✅ Helper function to sanitize inputs
function sanitize($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

// ✅ Get query parameters (PHP 7.4 호환)
$searchTerm = isset($_GET['search_term']) ? sanitize($_GET['search_term']) : '';
$genre = isset($_GET['genre']) ? sanitize($_GET['genre']) : '';
$place = isset($_GET['place']) ? sanitize($_GET['place']) : '';
$year = isset($_GET['year']) ? sanitize($_GET['year']) : '';
$endDateFilter = isset($_GET['end_date']) ? sanitize($_GET['end_date']) : 'all';
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$listSize = 16; // Default items per page

// ✅ Validate and prepare filters
$filters = [];
$sqlFilters = [];
$currentDate = date('Y-m-d'); // 현재 날짜

// ✅ Add search term filter
if (!empty($searchTerm)) {
    $sqlFilters[] = "(w.title LIKE :searchTerm OR w.contents LIKE :searchTerm)";
    $filters[':searchTerm'] = "%$searchTerm%";
}

// ✅ Add place filter
/*
if ($place !== 'all' && $place !== '') {
    $sqlFilters[] = "w.place = :place";
    $filters[':place'] = $place;
}*/

// ✅ 공연장 필터 추가 (마스터카드홀과 SOL트래블홀 동시 처리)

if ($place !== 'all' && $place !== '') {
    if ($place === '4' || $place === '1') { // 마스터카드홀(1) 또는 SOL트래블홀(4) 선택 시 둘 다 포함
        $sqlFilters[] = "(w.place = '1' OR w.place = '4')";
    } else {
        $sqlFilters[] = "w.place = :place";
        $filters[':place'] = $place;
    }
}



// ✅ Add genre filter
if ($genre !== 'all' && $genre !== '') {
    $sqlFilters[] = "w.genre = :genre";
    $filters[':genre'] = $genre;
}

// ✅ Add year filter
if ($year !== 'all' && $year !== '') {
    $sqlFilters[] = "YEAR(w.start_date) <= :year AND YEAR(w.end_date) >= :year";
    $filters[':year'] = (int)$year;
}

// ✅ Add end_date filter
if ($endDateFilter === 'begin') {
    $sqlFilters[] = "w.end_date >= :currentDate";
    $filters[':currentDate'] = $currentDate;
} elseif ($endDateFilter === 'end') {
    $sqlFilters[] = "w.end_date < :currentDate";
    $filters[':currentDate'] = $currentDate;
}

// ✅ Pagination calculation
$offset = ($page - 1) * $listSize;

// ✅ Prepare SQL query
$sql = "
    SELECT 
        w.* 
    FROM 
        nb_works w
";

if (!empty($sqlFilters)) {
    $sql .= " WHERE " . implode(' AND ', $sqlFilters) . " AND w.is_featured != 1";
} else {
    $sql .= " WHERE w.is_featured != 1";
}

// ✅ ORDER BY 절: end_date 내림차순 -> genre 오름차순
$sql .= " ORDER BY w.end_date DESC, w.genre ASC LIMIT :offset, :listSize";

try {
    $pdo = DB::getInstance();

    // ✅ Prepare the query
    $stmt = $pdo->prepare($sql);

    // ✅ Bind filters
    foreach ($filters as $key => $value) {
        $stmt->bindValue($key, is_int($value) ? (int)$value : $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
    }

    // ✅ Bind pagination parameters
    $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
    $stmt->bindValue(':listSize', (int)$listSize, PDO::PARAM_INT);

    // ✅ Debug SQL preparation (PHP 7.4 안전 버전)
    $debugSql = $sql;
    foreach ($filters as $key => $value) {
        $debugSql = str_replace($key, $pdo->quote($value), $debugSql);
    }
    $debugSql = str_replace(':offset', (int)$offset, $debugSql);
    $debugSql = str_replace(':listSize', (int)$listSize, $debugSql);
    file_put_contents('debug_sql.log', $debugSql . PHP_EOL, FILE_APPEND);

    // ✅ Execute the query
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // ✅ Count total rows for pagination
    $countSql = "SELECT COUNT(*) FROM nb_works w";
    if (!empty($sqlFilters)) {
        $countSql .= " WHERE " . implode(' AND ', $sqlFilters);
    }
    $countStmt = $pdo->prepare($countSql);
    foreach ($filters as $key => $value) {
        $countStmt->bindValue($key, is_int($value) ? (int)$value : $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
    }
    $countStmt->execute();
    $totalRows = $countStmt->fetchColumn();
    $totalPages = ceil($totalRows / $listSize);

    // ✅ `genres` 및 `places`가 미리 정의되지 않았다면 빈 배열로 초기화
    $genres = isset($genres) ? $genres : [];
    $places = isset($places) ? $places : [];

    // ✅ Respond with JSON (전체 아이템 개수 추가)
    echo json_encode([
        'data' => $results,
        'pagination' => [
            'currentPage' => $page,
            'totalPages' => $totalPages,
        ],
        'totalItems' => $totalRows, // 전체 아이템 개수 추가
        'genres' => $genres,
        'places' => $places,
        'debugSql' => $debugSql,
    ]);
} catch (PDOException $e) {
    http_response_code(500);

    // ✅ Respond with error
    echo json_encode([
        'error' => 'Query execution failed',
        'message' => $e->getMessage(),
        'debugSql' => $debugSql ?? '',
    ]);
    exit;
}
?>
