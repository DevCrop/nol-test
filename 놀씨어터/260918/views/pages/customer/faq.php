<?php section('content') ?>

<div class=" no-section-md">
    <?= include_view('components.sub-visual', ['title' => 'FAQ']) ?>


    <?php
    // 라우트에서 전달받은 데이터
    $faqItems = $faqItems ?? [];
    $categories = $categories ?? [];
    $selectedCategoryNo = $selectedCategoryNo ?? 0;
    ?>
    <section class="no-sub-faq">
        <div class="no-container-xl">
            <div class="no-sub-faq-inner">
                <div class="no-sub-ui">
                    <div class="no-sub-ui-inner">
                        <!-- 카테고리 -->
                        <?php if (count($categories) > 0): ?>
                        <aside class="left no-sub-ui-left" <?= AOS_DEFAULT ?>>
                            <div class="no-sub-ui-left-swiper">
                                <div class="swiper no-sub-ui-tabs-swiper">
                                    <ul class="swiper-wrapper no-sub-ui-tabs">
                                        <li
                                            class="swiper-slide no-sub-ui-tab <?= $selectedCategoryNo === 0 ? 'is-active' : '' ?>">
                                            <a href="<?= route('customer.faq') ?>" class="no-sub-ui-tab__btn">
                                                <span class="no-sub-ui-tab__bullet"></span>
                                                <span class="no-sub-ui-tab__label">전체</span>
                                            </a>
                                        </li>
                                        <?php foreach ($categories as $category): ?>
                                        <li
                                            class="swiper-slide no-sub-ui-tab <?= $selectedCategoryNo === $category['no'] ? 'is-active' : '' ?>">
                                            <a href="<?= route('customer.faq', ['category_no' => $category['no']]) ?>"
                                                class="no-sub-ui-tab__btn">
                                                <span class="no-sub-ui-tab__bullet"></span>
                                                <span class="no-sub-ui-tab__label"><?= e($category['name']) ?></span>
                                            </a>
                                        </li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            </div>
                        </aside>
                        <?php endif; ?>

                        <!-- FAQ 리스트 -->
                        <div class="no-sub-ui-right-wrap" <?= AOS_MEDIUM ?>>
                            <div class="right no-sub-ui-right">
                                <ul class="no-faq__list">
                                    <?php foreach ($faqItems as $item): ?>
                                    <?php
                                        $itemTitle = e($item['title'] ?? '');
                                        $itemContent = sanitize_html_fragment($item['content'] ?? '');
                                        $isOpen = $item['isOpen'] ?? false;
                                        $itemClass = $isOpen ? 'no-faq__item --active' : 'no-faq__item';
                                        $ariaExpanded = $isOpen ? 'true' : 'false';
                                    ?>
                                    <li class="<?= $itemClass ?>" <?= isset($item['id']) && $item['id'] > 0 ? 'id="faq-' . $item['id'] . '"' : '' ?>>
                                        <button type="button" class="no-faq__head" aria-expanded="<?= $ariaExpanded ?>">
                                            <div class="no-faq__title">
                                                <h3 class="f-body-1 --bold"><?= $itemTitle ?></h3>
                                            </div>
                                            <div class="no-faq__arrow">
                                                <i class="fa-solid fa-chevron-down"></i>
                                            </div>
                                        </button>
                                        <div class="no-faq__body">
                                            <div class="no-faq__content">
                                                <div class="no-faq__desc">
                                                    <div class="f-body-2 --regular"><?= $itemContent ?></div>
                                                </div>
                                            </div>
                                        </div>
                                    </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<?php end_section() ?>

<?php section('portal') ?>
<?= include_view('components.popup'); ?>
<?php end_section() ?>
