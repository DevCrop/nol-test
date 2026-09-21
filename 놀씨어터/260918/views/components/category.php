<?php
// 파라미터 기본값 설정
$board_no = $board_no ?? 0;
$categories = $categories ?? [];
$selectedCategoryNo = $selectedCategoryNo ?? 0;
$searchKeyword = $searchKeyword ?? '';
$extraLinks = $extraLinks ?? []; // 추가 링크 배열: [['url' => '...', 'label' => '...'], ...]
$baseUrl = $baseUrl ?? null; // 기본 URL (지정하지 않으면 현재 URL 사용)

$currentUrl = $baseUrl ?? parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);

// URL 쿼리 파라미터 생성 헬퍼 함수
function buildCategoryUrl($baseUrl, $categoryNo, $searchKeyword)
{
    $params = [];
    if ($categoryNo > 0) {
        $params['category_no'] = $categoryNo;
    }
    if (!empty($searchKeyword)) {
        $params['searchKeyword'] = $searchKeyword;
    }
    if (empty($params)) {
        return $baseUrl;
    }
    return $baseUrl . '?' . http_build_query($params);
}
?>
<!---카테고리 UI 만들기-->
<div class="no-category-wrap">
    <div class="swiper no-category-swiper">
        <ul class="swiper-wrapper no-category-list">
            <li class="swiper-slide no-category-item">
                <a href="<?= e(buildCategoryUrl($currentUrl, 0, $searchKeyword)) ?>"
                    class="no-category-link <?= $selectedCategoryNo == 0 ? 'active' : '' ?>">
                    <span class="no-category-item-text">전체</span>
                </a>
            </li>
            <?php if (!empty($categories)): ?>
            <?php foreach ($categories as $category): ?>
            <li class="swiper-slide no-category-item">
                <a href="<?= e(buildCategoryUrl($currentUrl, $category['no'], $searchKeyword)) ?>"
                    class="no-category-link <?= $selectedCategoryNo == $category['no'] ? 'active' : '' ?>">
                    <span class="no-category-item-text"><?= e($category['name']) ?></span>
                </a>
            </li>
            <?php endforeach; ?>
            <?php endif; ?>
            <?php if (!empty($extraLinks)): ?>
            <?php foreach ($extraLinks as $link): ?>
            <?php
                    // 현재 경로와 extraLink URL 비교 (쿼리스트링 제거)
                    $linkPath = parse_url($link['url'] ?? '', PHP_URL_PATH) ?: $link['url'] ?? '';
                    $currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);
                    $isActive = ($linkPath === $currentPath);
                    ?>
            <li class="swiper-slide no-category-item">
                <a href="<?= e($link['url'] ?? '#') ?>" class="no-category-link <?= $isActive ? 'active' : '' ?>">
                    <span class="no-category-item-text"><?= e($link['label'] ?? '') ?></span>
                </a>
            </li>
            <?php endforeach; ?>
            <?php endif; ?>
        </ul>
    </div>
</div>