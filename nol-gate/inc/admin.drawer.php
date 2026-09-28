<?php

// depthnum/pagenum/pageName 미설정 시 PageContext에서 URL·Role 기반 자동 감지
if (!isset($depthnum)) {
    require_once __DIR__ . '/../lib/PageContext.php';
    $ctx = PageContext::fromRequest($role);
    $depthnum = $ctx->depthnum;
    $pagenum = $ctx->pagenum;
    $pageName = $ctx->pageName;
}

$restrictedMenuKeys = $role->getRestrictedMenuKeys();
$BASE_URL = $NO_ADMIN_PAGES_BASE . '/';

$gnbActive = array_fill(1, 8, "");
$pageActive = [];
$pagenum = $pagenum ?? 0;

// 메뉴 정의 (단일 소스: config/menu.config.php)
$menus = require __DIR__ . '/../config/menu.config.php';

// 메뉴 활성화 설정 함수
function setActive(&$gnbActive, &$pageActive, $depth, $page, $gnbIndex, $key, $subCount = 0)
{
    $gnbActive[$gnbIndex] = "active";

    if ($subCount > 0) {
        $pageActive[$key] = array_fill(0, $subCount, "");
        if ($page > 0 && $page <= $subCount) {
            $pageActive[$key][$page - 1] = "active";
        }
    }
}

// 현재 페이지 정보로 활성화 설정 (외부인+사이트정보는 노출 서브 개수로 active 계산)
if (isset($menus[$depthnum])) {
    $menu = $menus[$depthnum];
    $subCount = isset($menu['subs']) && is_array($menu['subs']) ? count($menu['subs']) : 0;
    if ($menu['key'] === 'siteinfo' && $role->is('external')) {
        $subCount = 3; // 비밀번호 변경, 개인정보처리방침, 페이지별 SEO
    }
    setActive($gnbActive, $pageActive, $depthnum, $pagenum, $depthnum, $menu['key'], $subCount);
}
?>

<aside class="no-sidebar">
    <h1 class="no-sidebar-logo">
        <a href="<?= $NO_ADMIN_PAGES_BASE ?>/board/board.list.php">
            <img src="<?= $NO_IS_SUBDIR ?>/resource/images/admin/logo.png" class="no-logo--default" />
            <img src="<?= $NO_IS_SUBDIR ?>/resource/images/admin/logo-sm.png" class="no-logo--sm" />
        </a>
        <div class="no-sidebar-toggle"><span><i class="bx bx-chevrons-left"></i></span></div>
    </h1>

    <div class="no-sidebar-menu">
        <nav class="no-sidebar-menu__inner">
            <ul class="no-menu-list">
                <?php foreach ($menus as $index => $menu): ?>
                <?php
                    $displayMenu = $menu;
                    if (!$role->canViewMenu($displayMenu['key'])) {
                        continue;
                    }
                    if ($displayMenu['key'] === 'account' && !$role->isSuper()) {
                        continue;
                    }
                    if (!empty($displayMenu['subs']) && $displayMenu['key'] === 'account' && !$role->isSuper()) {
                        $displayMenu['subs'] = array_values(array_filter($displayMenu['subs'], static function ($sub) {
                            return ($sub['url'] ?? '') !== 'account/new.php';
                        }));
                    }
                    if (isset($displayMenu['hidden']) && $displayMenu['hidden']) {
                        continue;
                    }
                    $hasSub = !empty($displayMenu['subs']);
                    ?>
                <li class="no-menu-item <?= $gnbActive[$index] ?? '' ?>">
                    <?php if ($hasSub): ?>
                    <span class="no-menu-link">
                        <span class="no-menu-icon"><i class="fa-solid <?= $displayMenu['icon'] ?>"></i></span>
                        <span class="no-menu-title"><?= $displayMenu['title'] ?></span>
                        <span class="no-menu-arrow"><i class="bx bx-chevron-down"></i></span>
                    </span>
                    <ul class="no-menu-sub">
                        <?php foreach ($displayMenu['subs'] as $i => $sub): ?>
                        <?php
                                    if ($displayMenu['key'] === 'account' && ($sub['url'] ?? '') === 'account/new.php' && !$role->isSuper()) {
                                        continue;
                                    }
                                    $subUrl = $BASE_URL . $sub['url'];
                                    $isActive = $pageActive[$displayMenu['key']][$i] ?? '';
                                    ?>
                        <li class="no-menu-item">
                            <a href="<?= $NO_IS_SUBDIR . $subUrl ?>" class="no-menu-link <?= $isActive ?>">
                                <span class="no-menu-bullet"><span class="no-menu-bullet-dot"></span></span>
                                <span class="no-menu-title"><?= $sub['title'] ?></span>
                            </a>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                    <?php else: ?>
                    <?php $menuUrl = isset($displayMenu['url']) ? $displayMenu['url'] : ''; ?>
                    <?php if ($menuUrl !== '' && strpos($menuUrl, '/') !== 0): ?>
                    <?php $menuUrl = $NO_ADMIN_PAGES_BASE . '/' . ltrim($menuUrl, '/'); ?>
                    <?php endif; ?>
                    <a href="<?= $menuUrl ?>" class="no-menu-link">
                        <span class="no-menu-icon"><i class="fa-solid <?= $displayMenu['icon'] ?>"></i></span>
                        <span class="no-menu-title"><?= $displayMenu['title'] ?></span>
                    </a>
                    <?php endif; ?>
                </li>
                <?php endforeach; ?>
            </ul>
        </nav>
    </div>
</aside>

<script defer>
const activatedMenu = document.querySelector('.no-menu-item.active .no-menu-arrow');
if (activatedMenu) activatedMenu.classList.add('open');
</script>
