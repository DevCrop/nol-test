<?php section('content') ?>

<div class=" no-section-md">
    <?= include_view('components.sub-visual', ['title' => 'WHAT\'S ON']) ?>
    <section class="no-sub-whatson">
        <div class="no-container-xl">
            <!-- 모바일 필터 열기 버튼 -->
            <button type="button" class="no-sub-whatson-filter-toggle" aria-label="필터 열기">
                <i class="fa-regular fa-filter"></i>
                <span>필터</span>
            </button>
            <div class="no-sub-whatson-inner">
                <aside class="no-sub-whatson-filter" id="whatson-filter">
                    <div class="no-sub-whatson-filter__header">
                        <h2 class="no-sub-whatson-filter__title">필터</h2>
                        <button type="button" class="no-sub-whatson-filter__close" aria-label="필터 닫기">
                            <i class="fa-regular fa-xmark"></i>
                        </button>
                    </div>
                    <form class="no-filter-form" data-lenis-prevent="">
                        <!-- 공연장 -->
                        <div class="no-filter-group">
                            <h3 class="no-filter-title">공연장</h3>
                            <div class="no-filter-options">
                                <label class="no-radio">
                                    <input type="radio" name="venue" value="all" class="no-radio__input"
                                        <?= ($filters['venue'] ?? '') === '' || ($filters['venue'] ?? '') === 'all' ? 'checked' : '' ?>>
                                    <span class="no-radio__visual"></span>
                                    <span class="no-radio__label">전체</span>
                                </label>
                                <label class="no-radio">
                                    <input type="radio" name="venue" value="woori-card" class="no-radio__input"
                                        <?= ($filters['venue'] ?? '') === 'woori-card' ? 'checked' : '' ?>>
                                    <span class="no-radio__visual"></span>
                                    <span class="no-radio__label">우리카드홀</span>
                                </label>
                                <label class="no-radio">
                                    <input type="radio" name="venue" value="woori-securities" class="no-radio__input"
                                        <?= ($filters['venue'] ?? '') === 'woori-securities' ? 'checked' : '' ?>>
                                    <span class="no-radio__visual"></span>
                                    <span class="no-radio__label">우리투자증권홀</span>
                                </label>
                                <label class="no-radio">
                                    <input type="radio" name="venue" value="other" class="no-radio__input"
                                        <?= ($filters['venue'] ?? '') === 'other' ? 'checked' : '' ?>>
                                    <span class="no-radio__visual"></span>
                                    <span class="no-radio__label">기타</span>
                                </label>
                            </div>
                        </div>

                        <!-- 진행현황 -->
                        <div class="no-filter-group">
                            <h3 class="no-filter-title">진행현황</h3>
                            <div class="no-filter-options">
                                <label class="no-radio">
                                    <input type="radio" name="status" value="all" class="no-radio__input"
                                        <?= ($filters['status'] ?? '') === '' || ($filters['status'] ?? '') === 'all' ? 'checked' : '' ?>>
                                    <span class="no-radio__visual"></span>
                                    <span class="no-radio__label">전체</span>
                                </label>
                                <label class="no-radio">
                                    <input type="radio" name="status" value="ongoing" class="no-radio__input"
                                        <?= ($filters['status'] ?? '') === 'ongoing' ? 'checked' : '' ?>>
                                    <span class="no-radio__visual"></span>
                                    <span class="no-radio__label">진행작</span>
                                </label>
                                <label class="no-radio">
                                    <input type="radio" name="status" value="upcoming" class="no-radio__input"
                                        <?= ($filters['status'] ?? '') === 'upcoming' ? 'checked' : '' ?>>
                                    <span class="no-radio__visual"></span>
                                    <span class="no-radio__label">예정작</span>
                                </label>
                                <label class="no-radio">
                                    <input type="radio" name="status" value="completed" class="no-radio__input"
                                        <?= ($filters['status'] ?? '') === 'completed' ? 'checked' : '' ?>>
                                    <span class="no-radio__visual"></span>
                                    <span class="no-radio__label">종료작</span>
                                </label>
                            </div>
                        </div>

                        <!-- 장르 -->
                        <div class="no-filter-group">
                            <h3 class="no-filter-title">장르</h3>
                            <div class="no-filter-options">
                                <label class="no-radio">
                                    <input type="radio" name="genre" value="all" class="no-radio__input"
                                        <?= ($filters['genre'] ?? '') === '' || ($filters['genre'] ?? '') === 'all' ? 'checked' : '' ?>>
                                    <span class="no-radio__visual"></span>
                                    <span class="no-radio__label">전체</span>
                                </label>
                                <label class="no-radio">
                                    <input type="radio" name="genre" value="musical" class="no-radio__input"
                                        <?= ($filters['genre'] ?? '') === 'musical' ? 'checked' : '' ?>>
                                    <span class="no-radio__visual"></span>
                                    <span class="no-radio__label">뮤지컬</span>
                                </label>
                                <label class="no-radio">
                                    <input type="radio" name="genre" value="play" class="no-radio__input"
                                        <?= ($filters['genre'] ?? '') === 'play' ? 'checked' : '' ?>>
                                    <span class="no-radio__visual"></span>
                                    <span class="no-radio__label">연극</span>
                                </label>
                                <label class="no-radio">
                                    <input type="radio" name="genre" value="concert" class="no-radio__input"
                                        <?= ($filters['genre'] ?? '') === 'concert' ? 'checked' : '' ?>>
                                    <span class="no-radio__visual"></span>
                                    <span class="no-radio__label">콘서트</span>
                                </label>
                                <label class="no-radio">
                                    <input type="radio" name="genre" value="event" class="no-radio__input"
                                        <?= ($filters['genre'] ?? '') === 'event' ? 'checked' : '' ?>>
                                    <span class="no-radio__visual"></span>
                                    <span class="no-radio__label">이벤트</span>
                                </label>
                                <label class="no-radio">
                                    <input type="radio" name="genre" value="other" class="no-radio__input"
                                        <?= ($filters['genre'] ?? '') === 'other' ? 'checked' : '' ?>>
                                    <span class="no-radio__visual"></span>
                                    <span class="no-radio__label">기타</span>
                                </label>
                            </div>
                        </div>

                        <!-- 연도/월 -->
                        <div class="no-filter-group" id="yearMonthFilterContainer">
                            <h3 class="no-filter-title">연도/월</h3>
                            <div class="no-filter-date-input-wrapper">
                                <input type="text" class="no-filter-date-input" id="yearMonthInput" placeholder="전체"
                                    readonly autocomplete="off" inputmode="none">
                                <button type="button" class="no-filter-date-input__clear" id="yearMonthClear"
                                    style="display: none;">
                                    <i class="fa-regular fa-xmark"></i>
                                </button>
                            </div>

                            <!-- 연도/월 선택 모달 -->
                            <div class="no-filter-date-picker" id="yearMonthPicker">
                                <div class="no-filter-date-picker__inner">
                                    <!-- 왼쪽: 연도 선택 -->
                                    <div class="no-filter-date-picker__year-panel">
                                        <h4 class="no-filter-date-picker__panel-title">연도 선택</h4>
                                        <div class="no-filter-date-picker__year-list" data-lenis-prevent>
                                            <div class="no-filter-date-picker__year-item" data-year="all">
                                                전체
                                            </div>
                                            <div class="no-filter-date-picker__year-item" data-year="2026">
                                                2026
                                            </div>
                                        </div>
                                    </div>

                                    <!-- 오른쪽: 월 선택 -->
                                    <div class="no-filter-date-picker__month-panel" id="monthPanel"
                                        style="display: none;">
                                        <h4 class="no-filter-date-picker__panel-title" id="monthPanelTitle">월 선택</h4>
                                        <div class="no-filter-date-picker__month-grid">
                                            <button type="button" class="no-filter-date-picker__month-btn"
                                                data-month="all">
                                                전체
                                            </button>
                                            <?php for ($month = 1; $month <= 12; $month++): ?>
                                            <button type="button" class="no-filter-date-picker__month-btn"
                                                data-month="<?= $month ?>">
                                                <?= $month ?>월
                                            </button>
                                            <?php endfor; ?>
                                        </div>
                                    </div>
                                </div>

                                <!-- 확인 버튼 -->
                                <div class="no-filter-date-picker__actions">
                                    <button type="button" class="no-filter-date-picker__confirm" id="yearMonthConfirm">
                                        확인
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </aside>
                <div class="no-sub-whatson-filter-backdrop"></div>
                <div class="no-sub-whatson-content" id="hook" <?= AOS_MEDIUM ?>>
                    <?php
                    // 라우트에서 전달받은 데이터
                    $works = $works ?? [];
                    $total = $total ?? 0;
                    $page = $page ?? 1;
                    $lastPage = $lastPage ?? 1;
                    $filters = $filters ?? [];
                    ?>
                    <!-- 검색 및 카운트 -->
                    <div class="no-form-search no-sub-whatson-search">
                        <div class="no-form-search__form" id="whatsonSearchForm">
                            <div class="no-form-control">
                                <input type="text" name="q" class="no-form-control__input --search"
                                    id="whatsonSearchInput" placeholder="검색어를 입력해주세요." autocomplete="off"
                                    aria-label="검색어 입력">
                                <button type="button" class="no-form-search__button" aria-label="검색"
                                    id="whatsonSearchBtn">
                                    <i class="fa-regular fa-magnifying-glass"></i>
                                </button>
                            </div>
                            <button type="button" class="no-form-search__reset" aria-label="초기화"
                                id="whatsonSearchReset">
                                <i class="fa-regular fa-arrow-rotate-left"></i>
                            </button>
                        </div>
                        <div class="no-form-search__count f-heading-5 --bold" id="whatsonCount">
                            총 <strong>0</strong>건
                        </div>

                    </div>
                    <div class="no-sub-whatson-list-wrapper">
                        <div id="hook">
                            <ul class="no-sub-whatson-list" id="whatsonList">
                                <!-- 데이터는 JavaScript로 렌더링됩니다 -->
                            </ul>
                        </div>
                    </div>

                    <!-- 페이지네이션 -->
                    <div id="whatsonPagination"></div>
                </div>
            </div>
        </div>
    </section>
</div>

<?php end_section() ?>

<?php section('portal') ?>
<?= include_view('components.popup'); ?>
<?php end_section() ?>

<?php section('scripts') ?>
<?php end_section() ?>