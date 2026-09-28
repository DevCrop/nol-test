<?php
// 파라미터 기본값 설정
$totalPages = $totalPages ?? 1;
$currentPage = $currentPage ?? 1;
$selectedCategoryNo = $selectedCategoryNo ?? 0;
$searchKeyword = $searchKeyword ?? '';
$side = 2; // 좌우 표시할 개수
?>
<div class="no-pagination">
    <!-- 맨 처음 -->
    <a href="javascript:void(0);" class="no-pagination__link no-pagination__link--arrow --first"
        <?php if ($currentPage === 1 || $totalPages === 1): ?> style="pointer-events: none; opacity: 0.5;"
        <?php else: ?> onClick="goListMove(1)" <?php endif; ?>>
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <polyline points="11 17 6 12 11 7"></polyline>
            <polyline points="18 17 13 12 18 7"></polyline>
        </svg>
    </a>

    <!-- 이전 -->
    <a href="javascript:void(0);" class="no-pagination__link no-pagination__link--arrow --prev"
        <?php if ($currentPage === 1 || $totalPages === 1): ?> style="pointer-events: none; opacity: 0.5;"
        <?php else: ?> onClick="goListMove(<?= max(1, $currentPage - 1) ?>)" <?php endif; ?>>
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <polyline points="15 18 9 12 15 6"></polyline>
        </svg>
    </a>

    <div class="no-pagination__numbers">
        <?php
        $start = max(1, $currentPage - $side);
        $end = min($totalPages, $currentPage + $side);

        // 시작 부분 처리
        if ($start > 1) {
            echo '<a href="javascript:void(0);" class="no-pagination__link no-pagination__link--num" onClick="goListMove(1)">1</a>';
            if ($start > 2) {
                echo '<span class="no-pagination__dots">...</span>';
            }
        }

        // 중앙 페이지 출력
        for ($i = $start; $i <= $end; $i++) {
            $activeClass = ($i === $currentPage) ? ' is-active' : '';
            echo '<a href="javascript:void(0);" class="no-pagination__link no-pagination__link--num' . $activeClass . '" onClick="goListMove(' . $i . ')">' . $i . '</a>';
        }

        // 끝 부분 처리
        if ($end < $totalPages) {
            if ($end < $totalPages - 1) {
                echo '<span class="no-pagination__dots">...</span>';
            }
            echo '<a href="javascript:void(0);" class="no-pagination__link no-pagination__link--num" onClick="goListMove(' . $totalPages . ')">' . $totalPages . '</a>';
        }
        ?>
    </div>

    <!-- 다음 -->
    <a href="javascript:void(0);" class="no-pagination__link no-pagination__link--arrow --next"
        <?php if ($currentPage === $totalPages || $totalPages === 1): ?> style="pointer-events: none; opacity: 0.5;"
        <?php else: ?> onClick="goListMove(<?= min($totalPages, $currentPage + 1) ?>)" <?php endif; ?>>
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <polyline points="9 18 15 12 9 6"></polyline>
        </svg>
    </a>

    <!-- 맨 끝 -->
    <a href="javascript:void(0);" class="no-pagination__link no-pagination__link--arrow --last"
        <?php if ($currentPage === $totalPages || $totalPages === 1): ?> style="pointer-events: none; opacity: 0.5;"
        <?php else: ?> onClick="goListMove(<?= $totalPages ?>)" <?php endif; ?>>
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <polyline points="13 17 18 12 13 7"></polyline>
            <polyline points="6 17 11 12 6 7"></polyline>
        </svg>
    </a>
</div>

<script>
function goListMove(page) {
    const urlParams = new URLSearchParams(window.location.search);
    urlParams.set('page', page);
    window.location.href = window.location.pathname + '?' + urlParams.toString();
}
</script>