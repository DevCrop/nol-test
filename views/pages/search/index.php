<?php section('content') ?>

<div class="no-section-md">
    <section class="no-search-result">
        <div class="no-container-lg">
            <div class="no-search-result__container">
                <!-- 검색 폼 (search.scss 스타일 적용) -->
                <div class="no-search-result__form-wrapper">
                    <form action="<?= route('search') ?>" method="GET" class="no-search__form" id="searchResultForm">
                        <div class="no-search__input-wrapper">
                            <input type="text" name="q" class="no-search__input" id="searchResultInput"
                                placeholder="검색어를 입력해주세요." value="<?= e($_GET['q'] ?? '') ?>" autocomplete="off"
                                aria-label="검색어 입력">
                            <button type="submit" class="no-search__submit" aria-label="검색">
                                <i class="fa-regular fa-magnifying-glass"></i>
                            </button>
                        </div>
                    </form>
                </div>

                <?php
                // 라우트에서 전달받은 데이터
                $searchKeyword = $searchKeyword ?? '';
                $boardResults = $boardResults ?? [];
                $faqResults = $faqResults ?? [];
                $worksResults = $worksResults ?? [];
                $materialResults = $materialResults ?? [];
                $isValidKeyword = $isValidKeyword ?? true;
                $keywordError = $keywordError ?? '';
                $materialCount = array_sum(array_map(fn($category) => count($category['items'] ?? []), $materialResults));
                $hasResults = count($boardResults) > 0 || count($faqResults) > 0 || count($worksResults) > 0 || $materialCount > 0;
                $totalCount = count($boardResults) + count($faqResults) + count($worksResults) + $materialCount;
                ?>

                <!-- 검색 결과 -->
                <?php if ($searchKeyword): ?>
                <?php if (!$isValidKeyword): ?>
                <!-- 키워드 오류 알림 -->
                <div class="no-search-result__alert" id="keywordAlert">
                    <div class="no-search-result__alert-content">
                        <i class="fa-regular fa-circle-exclamation"></i>
                        <span><?= e($keywordError) ?></span>
                    </div>
                </div>
                <?php elseif ($hasResults): ?>
                <!-- 검색 결과 있음 -->
                <div class="no-search-result__content">
                    <div class="no-search-result__count f-heading-5 --bold">
                        <strong><?= e($searchKeyword) ?></strong>에 대한 검색 결과 <strong><?= $totalCount ?></strong>건
                    </div>

                    <?php if (count($boardResults) > 0): ?>
                    <!-- 게시판 검색 결과 -->
                    <div class="no-search-result__section">
                        <div class="no-search-result__section-header">
                            <h3 class="no-search-result__section-title f-heading-4 --bold">게시판</h3>
                        </div>
                        <div class="no-search-result__section-body">
                            <ul class="no-search-result__list">
                                <?php foreach ($boardResults as $result): ?>
                                <li>
                                    <a href="<?= $result['url'] ?>" class="no-search-result__item">
                                        <div class="no-search-result__item-header">
                                            <h3 class="no-search-result__item-title f-heading-5 --bold">
                                                <?= e($result['title']) ?>
                                            </h3>
                                            <span class="--badge"><?= e($result['category']) ?></span>
                                        </div>
                                        <p class="no-search-result__item-desc f-body-2 --regular">
                                            <?= e($result['desc']) ?>
                                        </p>
                                        <div class="no-search-result__item-meta">
                                            <span class="no-search-result__item-topic f-body-3 --regular">
                                                <i class="fa-regular fa-calendar"></i>
                                                <?= e($result['date']) ?>
                                            </span>
                                        </div>
                                    </a>
                                </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                    <?php endif; ?>

                    <?php if (count($materialResults) > 0): ?>
                    <!-- 대관 자료 검색 결과 -->
                    <div class="no-search-result__section">
                        <div class="no-search-result__section-header">
                            <h3 class="no-search-result__section-title f-heading-4 --bold">대관 자료</h3>
                        </div>
                        <div class="no-search-result__section-body">
                            <?php foreach ($materialResults as $category): ?>
                            <div class="no-sub-materials-block">
                                <h4 class="f-body-1 --bold"><?= e($category['category_name'] ?? '') ?></h4>
                                <ul class="no-sub-download-list">
                                    <?php foreach (($category['items'] ?? []) as $item): ?>
                                    <?php
                                                        $filePath = $item['file_attach_1'] ?? '';
                                                        if ($filePath === '') {
                                                            continue;
                                                        }
                                                        if (strpos($filePath, '/uploads/board/') !== 0) {
                                                            $filePath = '/uploads/board/' . ltrim($filePath, '/');
                                                        }
                                                        ?>
                                    <li class="no-sub-download-list-item">
                                        <a href="<?= base_path($filePath) ?>" download>
                                            <h3 class="f-body-1 --medium"><?= e($item['title'] ?? '') ?></h3>
                                            <div class="no-button-download">
                                                <span>다운로드</span>
                                                <i class="fa-regular fa-download"></i>
                                            </div>
                                        </a>
                                    </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <?php if (count($faqResults) > 0): ?>
                    <!-- FAQ 검색 결과 -->
                    <div class="no-search-result__section">
                        <h3 class="no-search-result__section-title f-heading-4 --bold">FAQ</h3>
                        <ul class="no-faq__list">
                            <?php foreach ($faqResults as $item): ?>
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
                    <?php endif; ?>

                    <?php if (count($worksResults) > 0): ?>
                    <!-- 공연 작품 검색 결과 -->
                    <div class="no-search-result__section">
                        <h3 class="no-search-result__section-title f-heading-4 --bold">공연 작품</h3>
                        <ul class="no-sub-whatson-list">
                            <?php foreach ($worksResults as $work): ?>
                            <li class="no-sub-whatson-item">
                                <a href="<?= htmlspecialchars($work['url']) ?>">
                                    <figure class="no-sub-whatson-item-img">
                                        <img src="<?= htmlspecialchars($work['thumb_image']) ?>"
                                            alt="<?= htmlspecialchars($work['title']) ?>">
                                    </figure>
                                    <div class="no-sub-whatson-item-content">
                                        <div class="no-sub-whatson-item-content__info">
                                            <?php if (!empty($work['period'])): ?>
                                            <div class="--badge"><?= htmlspecialchars($work['period']) ?></div>
                                            <?php endif; ?>
                                            <?php if (!empty($work['venue_name'])): ?>
                                            <div class="--badge"><?= htmlspecialchars($work['venue_name']) ?></div>
                                            <?php endif; ?>
                                        </div>
                                        <h3 class="f-heading-5 --bold">
                                            <?= htmlspecialchars($work['title']) ?>
                                        </h3>
                                    </div>
                                </a>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <?php endif; ?>
                </div>
                <?php else: ?>
                <!-- 검색 결과 없음 -->
                <div class="no-search-result__empty">
                    <div class="no-search-result__empty-icon">
                        <i class="fa-regular fa-magnifying-glass"></i>
                    </div>
                    <h3 class="no-search-result__empty-title">검색 결과가 없습니다</h3>
                    <p class="no-search-result__empty-desc">
                        '<strong><?= e($searchKeyword) ?></strong>'에 대한 검색 결과를 찾을 수 없습니다.<br>
                        다른 검색어로 시도해보세요.
                    </p>
                </div>
                <?php endif; ?>
                <?php else: ?>
                <!-- 검색어 없음 -->
                <div class="no-search-result__empty">
                    <div class="no-search-result__empty-icon">
                        <i class="fa-regular fa-magnifying-glass"></i>
                    </div>
                    <h3 class="no-search-result__empty-title">검색어를 입력해주세요</h3>
                    <p class="no-search-result__empty-desc">
                        원하시는 검색어를 입력하시면<br>
                        관련된 내용을 찾아드립니다.
                    </p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </section>
</div>

<?php end_section() ?>

<?php section('portal') ?>
<?= include_view('components.popup'); ?>
<?php end_section() ?>
