<?php

/** @var \Menu\Menu $menu */
$menu = app()->get('menu');
/** @var \Menu\MenuItem|null $menuItem */
$menuItem = app()->get('active_menu_item');

// notice-view 페이지인 경우 customer-notice 메뉴 아이템 찾기
// announcement-view 페이지인 경우 legal.announcement 메뉴 아이템 찾기
if (!$menuItem && $menu) {
    $currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?: '/';
    if (strpos($currentPath, '/customer/notice/view') === 0) {
        $menuItem = $menu->findByUrl('/customer/notice');
    } elseif (strpos($currentPath, '/legal/announcement/view') === 0) {
        // 기업공고는 메뉴에 없으므로 직접 처리
        $rootMenu = null;
        $subMenu = null;
    }
}

$rootMenu = null;
$subMenu = null;
$rootSiblings = [];
$subSiblings = [];

if ($menuItem && $menu) {
    try {
        // 현재 메뉴의 부모
        $parent = $menuItem->parent();

        // 대메뉴: 부모가 있으면 부모, 없으면 자기 자신
        $rootMenu = $parent ? $parent : $menuItem;

        // 중메뉴: 부모가 있으면 현재 메뉴, 없으면 없음
        $subMenu = $parent ? $menuItem : null;

        // 대메뉴 형제들 (드롭다운 리스트용)
        $rootSiblings = $rootMenu ? $rootMenu->siblingsIncludingSelf(true) : [];

        // 중메뉴 형제들 (드롭다운 리스트용)
        $subSiblings = $subMenu ? $subMenu->siblingsIncludingSelf(true) : [];
    } catch (Exception $e) {
        $rootMenu = null;
        $subMenu = null;
        $rootSiblings = [];
        $subSiblings = [];
    }
}
?>

<div class="breadcrumb" <?= AOS_DEFAULT ?>>
    <nav class="breadcrumb__nav">
        <ul class="breadcrumb__list">
            <!-- 홈 아이콘 -->
            <li class="home">
                <a href="/">
                    <i class="fa-solid fa-house" aria-hidden="true"></i>
                </a>
            </li>

            <?php if ($rootMenu && !empty($rootSiblings)): ?>
            <li class="breadcrumb-has-menu">
                <button type="button" class="breadcrumb-toggle">
                    <?= e($rootMenu->label()) ?>
                    <i class="fa-solid fa-caret-down" aria-hidden="true"></i>
                </button>
                <ul class="breadcrumb-menu" data-lenis-prevent>
                    <?php foreach ($rootSiblings as $sibling): ?>
                    <li class="<?= $sibling === $rootMenu ? 'active' : '' ?>">
                        <a href="<?= e($sibling->url()) ?>" <?= $sibling->isBlank() ? 'target="_blank"' : '' ?>>
                            <?= e($sibling->label()) ?>
                        </a>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </li>
            <?php endif; ?>

            <?php if ($subMenu && !empty($subSiblings)): ?>
            <li class="breadcrumb-has-menu">
                <button type="button" class="breadcrumb-toggle">
                    <?= e($subMenu->label()) ?>
                    <i class="fa-solid fa-caret-down" aria-hidden="true"></i>
                </button>
                <ul class="breadcrumb-menu" data-lenis-prevent>
                    <?php foreach ($subSiblings as $sibling): ?>
                    <li class="<?= $sibling === $subMenu ? 'active' : '' ?>">
                        <a href="<?= e($sibling->url()) ?>" <?= $sibling->isBlank() ? 'target="_blank"' : '' ?>>
                            <?= e($sibling->label()) ?>
                        </a>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </li>
            <?php endif; ?>
        </ul>
    </nav>
</div>