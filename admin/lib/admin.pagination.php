<?php
if (!isset($totalCnt) || !isset($listCurPage) || !isset($listRowCnt)) {
    return;
}

$totalPages = ceil($totalCnt / $listRowCnt);
$hasPrev = $listCurPage > 1;
$hasNext = $listCurPage < $totalPages;

// 현재 페이지 블록 계산 (1~5, 6~10 등)
$blockSize = 5;
$currentBlock = (int)ceil($listCurPage / $blockSize);
$startPage = ($currentBlock - 1) * $blockSize + 1;
$endPage = min($startPage + $blockSize - 1, $totalPages);

// 현재 쿼리스트링 생성
$queryParams = $_GET;
unset($queryParams['page']); // 기존 page 제거
$queryString = http_build_query($queryParams);

$html = '<div class="no-pagination"><ul class="no-page-list">';

// 처음으로 가기 버튼
if ($hasPrev) {
    $html .= '<li class="no-page-item"><a href="?' . $queryString . '&page=1" class="no-page-link" onClick="goListMove(1);"><i class="bx bx-chevrons-left"></i></a></li>';
} else {
    $html .= '<li class="no-page-item"><a href="javascript:void(0);" class="no-page-link"><i class="bx bx-chevrons-left"></i></a></li>';
}

// 이전 페이지 버튼
if ($hasPrev) {
    $prevPage = $listCurPage - 1;
    $html .= '<li class="no-page-item"><a href="?' . $queryString . '&page=' . $prevPage . '" class="no-page-link" onClick="goListMove(' . $prevPage . ');"><i class="bx bx-chevron-left"></i></a></li>';
} else {
    $html .= '<li class="no-page-item"><a href="javascript:void(0);" class="no-page-link"><i class="bx bx-chevron-left"></i></a></li>';
}

// 페이지 번호 버튼
for ($x = $startPage; $x <= $endPage; $x++) {
    if ($x == $listCurPage) {
        $html .= '<li class="no-page-item"><a href="javascript:void(0);" class="no-page-link active">' . $x . '</a></li>';
    } else {
        $html .= '<li class="no-page-item"><a href="?' . $queryString . '&page=' . $x . '" onClick="goListMove(' . $x . ');" class="no-page-link">' . $x . '</a></li>';
    }
}

// 다음 페이지 버튼
if ($hasNext) {
    $nextPage = $listCurPage + 1;
    $html .= '<li class="no-page-item"><a href="?' . $queryString . '&page=' . $nextPage . '" class="no-page-link" onClick="goListMove(' . $nextPage . ');"><i class="bx bx-chevron-right"></i></a></li>';
} else {
    $html .= '<li class="no-page-item"><a href="javascript:void(0);" class="no-page-link"><i class="bx bx-chevron-right"></i></a></li>';
}

// 마지막 페이지 버튼
if ($hasNext) {
    $html .= '<li class="no-page-item"><a href="?' . $queryString . '&page=' . $totalPages . '" class="no-page-link" onClick="goListMove(' . $totalPages . ');"><i class="bx bx-chevrons-right"></i></a></li>';
} else {
    $html .= '<li class="no-page-item"><a href="javascript:void(0);" class="no-page-link"><i class="bx bx-chevrons-right"></i></a></li>';
}

$html .= '</ul></div>';

echo $html;
?>
