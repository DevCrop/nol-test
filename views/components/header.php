<?php

/** @var \Menu\Menu $menu */
$menu = app()->get('menu');

?>
<header id="main-header" class="no-header">
    <div class="no-header__inner">
        <div class="no-header__container no-container-2xl">
            <div class="no-header__wrap">
                <a href="/" class="no-header__logo">
                    <div class="no-theme-change">
                        <img src="<?= base_path('/resource/images/logo/logo_color.png') ?>" alt="NOL 씨어터 대학로 공연장"
                            class="light" style="display: none;">
                        <img src="<?= base_path('/resource/images/logo/logo_white.png') ?>" alt="NOL 씨어터 대학로 공연장"
                            class="dark" style="display: block;">
                    </div>
                </a>
                <div class="--divider">
                </div>
                <nav class="no-header__nav">
                    <?php if ($menu): ?>
                    <ul class="no-header__gnb">
                        <?php foreach ($menu->rootItems() as $root):
                                if (!$root->isVisible()) continue;
                            ?>
                        <li class="no-header__gnb-item">
                            <a href="<?= e($root->url()) ?>" class="no-header__gnb-link">
                                <span
                                    class="no-header__gnb-label f-body-2 --semibold clr-text-title"><?= e($root->label()) ?></span>
                            </a>

                            <?php
                                    $children = $root->children(true);
                                    if (!empty($children)): ?>
                            <div class="no-header__submenu">
                                <ul class="no-header__lnb">
                                    <?php foreach ($children as $child): ?>
                                    <li class="no-header__lnb-item">
                                        <a href="<?= e($child->url()) ?>" class="no-header__lnb-link">
                                            <span
                                                class="no-header__lnb-label f-body-3 --regular clr-text-body"><?= e($child->label()) ?></span>
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
            <div class="no-header-right">

                <div class="no-header__action">
                    <div class="no-header__icons">
                        <button type="button" id="theme-toggle" class="no-header__theme-toggle" aria-label="다크 모드로 전환">
                            <span class="no-header__theme-toggle-icon no-header__theme-toggle-icon--sun">
                                <i class="fa-regular fa-sun-bright"></i>
                            </span>
                            <span class="no-header__theme-toggle-handle"></span>
                            <span class="no-header__theme-toggle-icon no-header__theme-toggle-icon--moon">
                                <i class="fa-solid fa-moon"></i>
                            </span>
                        </button>

                        <button type="button" class="no-header__icon-btn no-header__search-toggle" aria-label="검색">
                            <i class="fa-regular fa-magnifying-glass"></i>
                        </button>

                        <button type="button" class="no-header__icon-btn no-header__icon-btn--sitemap no-header__toggle"
                            aria-label="사이트맵">
                            <i class="fa-regular fa-bars"></i>
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </div>
    </div>
</header>