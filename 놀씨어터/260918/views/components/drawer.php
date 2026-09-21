<?php

/** @var \Menu\Menu|null $menu */
$menu = app()->get('menu');
$siteinfo = app()->get('siteinfo');

// 현재 경로 가져오기 (쿼리스트링 제거)
$currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?: '/';

?>
<aside id="main-drawer" class="no-drawer" data-lenis-prevent>
    <!-- 공통 닫기 버튼 (drawer/search 공용) -->
    <div class="no-modal__close--container">
        <button type="button" class="no-modal__close" aria-label="닫기">
            <i class="fa-regular fa-xmark"></i>
        </button>
    </div>
    <div class="no-drawer__inner">
        <div class="no-drawer__container no-container-2xl">

            <div class="no-drawer__center">
                <nav class="no-drawer__nav" aria-label="사이트맵 내비게이션">
                    <?php if ($menu): ?>
                    <ul class="no-drawer__gnb">
                        <?php foreach ($menu->rootItems() as $root):
                                if (!$root->isVisible()) continue;
                                $rootHref   = e($root->url());
                                $rootLabel  = e($root->label());
                                $rootTarget = e($root->target()); // _self/_blank
                            ?>
                        <li class="no-drawer__gnb-item">
                            <a href="<?= $rootHref ?>" target="<?= $rootTarget ?>" class="no-drawer__gnb-link">
                                <span
                                    class="no-drawer__gnb-label f-display-3 --bold clr-text-title"><?= $rootLabel ?></span>
                            </a>

                            <?php
                                    $children = $root->children(true); // visibleOnly 정렬 포함
                                    if (!empty($children)): ?>
                            <div class="no-drawer__submenu">
                                <ul class="no-drawer__lnb">
                                    <?php foreach ($children as $child):
                                                    if (!$child->isVisible()) continue;
                                                    $childHref   = e($child->url());
                                                    $childLabel  = e($child->label());
                                                    $childTarget = e($child->target());
                                                ?>
                                    <li class="no-drawer__lnb-item">
                                        <?php
                                                        // 현재 경로와 메뉴 URL 비교 (쿼리스트링 제거)
                                                        $childPath = parse_url($childHref, PHP_URL_PATH) ?: $childHref;
                                                        $isActive = ($childPath === $currentPath);
                                                        ?>
                                        <a href="<?= $childHref ?>" target="<?= $childTarget ?>"
                                            class="no-drawer__lnb-link <?= $isActive ? 'active' : '' ?>">
                                            <span
                                                class="no-drawer__lnb-label f-body-1 --bold clr-text-body"><?= $childLabel ?></span>
                                        </a>
                                    </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                            <?php endif; ?>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                    <?php endif; ?>
                </nav>
            </div>
        </div>
    </div>
</aside>