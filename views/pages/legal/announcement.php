<?php section('content') ?>

<div class=" no-section-md">
    <?= include_view('components.sub-visual', ['title' => '기업공고', 'showBreadcrumb' => false]) ?>

    <section class="no-sub-notice">
        <div class="no-container-xl">
            <div class="no-sub-notice-inner">
                <div class="no-sub-ui">
                    <div class="no-sub-ui-inner">
                        <?php
                        // 라우트에서 전달받은 데이터
                        $boardRows = $boardRows ?? [];
                        $categories = $categories ?? [];
                        $selectedCategoryNo = $selectedCategoryNo ?? 0;
                        $page = $page ?? 1;
                        $lastPage = $lastPage ?? 1;
                        ?>
                        <!-- 카테고리 -->
                        <?php if (count($categories) > 0): ?>
                        <aside class="left no-sub-ui-left" <?= AOS_DEFAULT ?>>
                            <div class="no-sub-ui-left-swiper">
                                <div class="swiper no-sub-ui-tabs-swiper">
                                    <ul class="swiper-wrapper no-sub-ui-tabs">
                                        <li
                                            class="swiper-slide no-sub-ui-tab <?= $selectedCategoryNo === 0 ? 'is-active' : '' ?>">
                                            <a href="<?= route('legal.announcement') ?>" class="no-sub-ui-tab__btn">
                                                <span class="no-sub-ui-tab__bullet"></span>
                                                <span class="no-sub-ui-tab__label">전체</span>
                                            </a>
                                        </li>
                                        <?php foreach ($categories as $category): ?>
                                        <li
                                            class="swiper-slide no-sub-ui-tab <?= $selectedCategoryNo === $category['no'] ? 'is-active' : '' ?>">
                                            <a href="<?= route('legal.announcement', ['category_no' => $category['no']]) ?>"
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
                        <!-- 리스트 -->
                        <div class="no-sub-ui-right-wrap">
                            <div class="right no-sub-ui-right">
                                <div class="no-sub-notice-list">
                                    <div class="no-sub-notice-head">
                                        <div class="no-sub-notice-head__category">
                                            <span class="f-body-2 --bold">공지</span>
                                        </div>
                                        <div class="no-sub-notice-head__title">
                                            <span class="f-body-2 --bold">제목</span>
                                        </div>
                                        <div class="no-sub-notice-head__views">
                                            <span class="f-body-2 --bold">조회수</span>
                                        </div>
                                        <div class="no-sub-notice-head__date">
                                            <span class="f-body-2 --bold">날짜</span>
                                        </div>
                                    </div>
                                    <ul class="no-sub-notice-list__items">
                                        <?php if (count($boardRows) > 0): ?>
                                        <?php foreach ($boardRows as $row): ?>
                                        <li class="no-sub-notice-item">
                                            <a
                                                href="<?= route('legal.announcement-view', ['no' => $row['no'], 'category_no' => $selectedCategoryNo]) ?>">
                                                <div class="no-sub-notice-item__category">
                                                    <?php if ($row['is_notice'] === 'Y'): ?>
                                                    <span class="--notice">
                                                        <i class="fa-solid fa-megaphone"></i>
                                                    </span>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="no-sub-notice-item__title">
                                                    <h3 class="f-body-2 --bold"><?= htmlspecialchars($row['title']) ?>
                                                    </h3>
                                                </div>
                                                <div class="no-sub-notice-item__views">
                                                    <span
                                                        class="f-body-3 --regular"><?= number_format($row['read_cnt']) ?></span>
                                                </div>
                                                <div class="no-sub-notice-item__date">
                                                    <span
                                                        class="f-body-3 --regular"><?= date('Y.m.d', strtotime($row['regdate'])) ?></span>
                                                </div>
                                            </a>
                                        </li>
                                        <?php endforeach; ?>
                                        <?php else: ?>
                                        <li class="no-sub-notice-item" style="text-align: center; padding: 2rem;">
                                            <span class="f-body-2 --regular">등록된 게시글이 없습니다.</span>
                                        </li>
                                        <?php endif; ?>
                                    </ul>
                                </div>

                                <!-- 페이지네이션 -->
                                <?php if ($lastPage > 0): ?>
                                <div class="no-sub-notice-pagination">
                                    <?= include_view('components.pagination', [
                                            'totalPages' => $lastPage,
                                            'currentPage' => $page,
                                            'selectedCategoryNo' => $selectedCategoryNo,
                                            'searchKeyword' => '',
                                        ]) ?>
                                </div>
                                <?php endif; ?>
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