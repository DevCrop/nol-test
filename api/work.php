<?php

require_once dirname(__DIR__) . '/inc/lib/db.php'; // DB 클래스 파일 경로 설정

// Helper function to sanitize inputs
function sanitize($input)
{
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

// Auto-update category based on w_edate
try {
    $pdo = DB::getInstance();
    $currentDate = date('Y-m-d');

    $updateSql = "
        UPDATE nb_board
        SET category_no = 22
        WHERE category_no = 21 AND w_edate < :currentDate
    ";
    $updateStmt = $pdo->prepare($updateSql);
    $updateStmt->bindValue(':currentDate', $currentDate);
    $updateStmt->execute();
} catch (PDOException $e) {
    file_put_contents('error.log', 'Update Error: ' . $e->getMessage() . PHP_EOL, FILE_APPEND);
}

// Get query parameters
$searchTerm = sanitize($_GET['search_term'] ?? '');
$category = sanitize($_GET['category_no'] ?? '');
$year = sanitize($_GET['year'] ?? ''); // Expecting a year like '2024'
$extra4 = sanitize($_GET['extra4'] ?? '');
$extra1 = sanitize($_GET['extra1'] ?? '');
$page = intval($_GET['page'] ?? 1);
$listSize = 12; // Default items per page

// Validate and prepare filters
$filters = [];
$sqlFilters = [];

// Add fixed board_no = 12 filter
$sqlFilters[] = "b.board_no = 12";

if (!empty($searchTerm)) {
    $sqlFilters[] = "(b.title LIKE :searchTerm OR b.contents LIKE :searchTerm)";
    $filters[':searchTerm'] = '%' . $searchTerm . '%';
}

if (!empty($category) && $category !== 'all') {
    $sqlFilters[] = "b.category_no = :category";
    $filters[':category'] = $category;
}

if (!empty($year)) {
    $sqlFilters[] = "YEAR(b.w_sdate) = :year";
    $filters[':year'] = $year; // Directly use the year value
}

if (!empty($extra4) && $extra4 !== 'all') {
    $sqlFilters[] = "b.extra4 = :extra4";
    $filters[':extra4'] = $extra4;
}

if (!empty($extra1) && $extra1 !== 'all') {
    $sqlFilters[] = "b.extra1 = :extra1";
    $filters[':extra1'] = $extra1;
}

// Pagination calculation
$offset = ($page - 1) * $listSize;

// Prepare SQL query
$sql = "
    SELECT 
        b.*, 
        c.name AS category_name
    FROM 
        nb_board b
    LEFT JOIN 
        nb_board_category c 
    ON 
        b.category_no = c.no
";

if (!empty($sqlFilters)) {
    $sql .= " WHERE " . implode(' AND ', $sqlFilters);
} else {
    $sql .= " WHERE 1 = 1"; // 기본 조건
}
$sql .= " ORDER BY b.regdate DESC LIMIT :offset, :listSize";

try {
    // Prepare the query
    $stmt = $pdo->prepare($sql);

    // Bind filters
    foreach ($filters as $key => $value) {
        $stmt->bindValue($key, $value);
    }

    // Bind pagination parameters
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->bindValue(':listSize', $listSize, PDO::PARAM_INT);

    // Debug: Build the final SQL query for debugging
    $debugSql = $sql;
    foreach ($filters as $key => $value) {
        $debugSql = str_replace($key, "'" . addslashes($value) . "'", $debugSql);
    }
    $debugSql = str_replace(':offset', $offset, $debugSql);
    $debugSql = str_replace(':listSize', $listSize, $debugSql);

    // Log the debug SQL to a file
    file_put_contents('debug_sql.log', $debugSql . PHP_EOL, FILE_APPEND);

    // Execute the query
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Count total rows for pagination
    $countSql = "
        SELECT COUNT(*) 
        FROM nb_board b
        LEFT JOIN nb_board_category c ON b.category_no = c.no
    ";
    if (!empty($sqlFilters)) {
        $countSql .= " WHERE " . implode(' AND ', $sqlFilters);
    }
    $countStmt = $pdo->prepare($countSql);
    foreach ($filters as $key => $value) {
        $countStmt->bindValue($key, $value);
    }
    $countStmt->execute();
    $totalRows = $countStmt->fetchColumn();
    $totalPages = ceil($totalRows / $listSize);

    // Respond with JSON
    echo json_encode([
        'data' => $results,
        'pagination' => [
            'currentPage' => $page,
            'totalPages' => $totalPages,
        ],
        'query' => $debugSql
    ]);
} catch (PDOException $e) {
    http_response_code(500);

    // Respond with error and debug query
    echo json_encode([
        'error' => 'Query execution failed',
        'message' => $e->getMessage(),
        'query' => $debugSql ?? ''
    ]);
    exit;
}
