<?php

include_once $_SERVER['DOCUMENT_ROOT'] . '/inc/lib/base.class.php';

header('Content-Type: application/json');

function respondJson(int $statusCode, array $payload): void
{
    http_response_code($statusCode);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

function normalizeIntegerList($value): array
{
    if ($value === null || $value === '') {
        return [];
    }

    $items = is_array($value) ? $value : explode(',', (string) $value);
    $normalized = [];

    foreach ($items as $item) {
        $item = trim((string) $item);
        if ($item === '') {
            continue;
        }

        if (!ctype_digit($item)) {
            respondJson(400, ['error' => 'Invalid category_no parameter.']);
        }

        $normalized[] = (int) $item;
    }

    return array_values(array_unique($normalized));
}

$boardNoRaw = $_GET['board_no'] ?? null;
$regdateRaw = $_GET['regdate'] ?? null;
$categoryNos = normalizeIntegerList($_GET['category_no'] ?? null);

if ($boardNoRaw === null || $boardNoRaw === '' || filter_var($boardNoRaw, FILTER_VALIDATE_INT) === false) {
    respondJson(400, ['error' => 'Invalid board_no parameter.']);
}

$boardNo = (int) $boardNoRaw;

$regdate = null;
if ($regdateRaw !== null && $regdateRaw !== '') {
    $regdateRaw = trim((string) $regdateRaw);
    $regdateObject = DateTime::createFromFormat('Y-m-d H:i:s', $regdateRaw)
        ?: DateTime::createFromFormat('Y-m-d', $regdateRaw);

    if (!$regdateObject || $regdateObject->format($regdateObject->format('H:i:s') === '00:00:00' ? 'Y-m-d' : 'Y-m-d H:i:s') !== $regdateRaw) {
        respondJson(400, ['error' => 'Invalid regdate parameter.']);
    }

    $regdate = $regdateObject->format($regdateObject->format('H:i:s') === '00:00:00' ? 'Y-m-d' : 'Y-m-d H:i:s');
}

try {
    $db = DB::getInstance();

    $query = "SELECT
                  nb.*,
                  nbc.name AS category_name
              FROM
                  nb_board AS nb
              LEFT JOIN
                  nb_board_category AS nbc
              ON
                  nb.category_no = nbc.no
              WHERE
                  nb.board_no = :board_no AND nb.sitekey = 'BLUESQ'
                  AND nb.is_view = 'Y' AND COALESCE(nb.is_secret, 'N') <> 'Y'";

    $params = [
        ':board_no' => $boardNo,
    ];
    $categoryPlaceholders = [];

    foreach ($categoryNos as $index => $categoryNo) {
        $placeholder = ':category_no_' . $index;
        $params[$placeholder] = $categoryNo;
        $categoryPlaceholders[] = $placeholder;
    }

    if (!empty($categoryPlaceholders)) {
        $query .= " AND nb.category_no IN (" . implode(', ', $categoryPlaceholders) . ")";
    }

    if ($regdate !== null) {
        $query .= " AND nb.regdate < :regdate";
        $params[':regdate'] = $regdate;
    }

    $query .= " ORDER BY nb.regdate DESC LIMIT 100";

    $stmt = $db->prepare($query);

    foreach ($params as $placeholder => $value) {
        $paramType = is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR;
        $stmt->bindValue($placeholder, $value, $paramType);
    }

    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'rows' => $rows,
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
} catch (PDOException $e) {
    error_log('app/api.php database error: ' . $e->getMessage());
    respondJson(500, ['error' => 'Internal server error.']);
}

?>
