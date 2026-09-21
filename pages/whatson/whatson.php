<?php include_once $_SERVER['DOCUMENT_ROOT'].'/inc/lib/base.class.php'; ?>

<!-- dev -->

<?php include_once $STATIC_ROOT.'/inc/layouts/head.php'; ?>



<!-- css, js  -->
<?php 
    include_once $STATIC_ROOT.'/inc/layouts/header.php';
    include_once $STATIC_ROOT.'/inc/shared/sub.visual.works.php';
?>


<main class="no-sub no-board">

    <form id='works' name='works' method="GET" autocomplete="off">
        <div class="no-pd-xl--y">
            <section class="">
                <div class="no-skin-gallery">
                    <div class="no-container-xl">
                        <div class="no-form-head">
                            <div class="no-form-head-top">
                                <div class="no-form-search__type_B">
                                    <input type="text" name="search_term" id="search_term" placeholder="검색어를 입력해주세요">
                                    <i class="fa-solid fa-magnifying-glass"></i>
                                </div>
                            </div>
                            <div class="no-form-head-bottom">
                                <div class="f fd-c no-gap-md">
                                    <div class="f ai-c no-gap-xl">
                                        <div class="no-form-group ">
                                            <h4 class="no-body-md">공연장</h4>
                                            <div class="no-form-radio ">
                                                <ul class="" id="no-theater-hook">
                                                    <li>
                                                        <label for="place_all" class="no-filing-btn">
                                                            <input type="radio" name="place" id="place_all" value="all"
                                                                checked>
                                                            <span>
                                                                <p>전체</p>
                                                            </span>
                                                            <div class="no-filing-btn-circle"></div>
                                                        </label>
                                                    </li>
                                                    <li>
                                                        <label for="place_0" class="no-filing-btn">
                                                            <input type="radio" name="place" id="place_0" value="0">
                                                            <span>
                                                                <p><?= isUpdateActive() ? '우리은행홀' : '신한카드홀' ?></p>
                                                            </span>
                                                            <div class="no-filing-btn-circle"></div>
                                                        </label>
                                                    </li>
                                                    <?php
													$currentDate = date('Y-m-d'); // 현재 날짜 (형식: 2025-04-01)
													$targetDate = '2025-04-01'; // 4월 1일 이후 제거

													if ($currentDate < $targetDate): // 4월 1일 이전에만 출력
													?>
                                                    <li>
                                                        <label for="place_1" class="no-filing-btn">
                                                            <input type="radio" name="place" id="place_1" value="1">
                                                            <span>
                                                                <p>마스터카드홀</p>
                                                            </span>
                                                            <div class="no-filing-btn-circle"></div>
                                                        </label>
                                                    </li>
                                                    <?php endif; ?>
                                                    <li>
                                                        <label for="place_4" class="no-filing-btn">
                                                            <input type="radio" name="place" id="place_4" value="4">
                                                            <span>
                                                                <p><?= isUpdateActive() ? '우리WON뱅킹홀' : 'SOL트래블홀' ?></p>
                                                            </span>
                                                            <div class="no-filing-btn-circle"></div>
                                                        </label>
                                                    </li>
                                                    <li>
                                                        <label for="place_2" class="no-filing-btn">
                                                            <input type="radio" name="place" id="place_2" value="2">
                                                            <span>
                                                                <p>NEMO</p>
                                                            </span>
                                                            <div class="no-filing-btn-circle"></div>
                                                        </label>
                                                    </li>
                                                    <li>
                                                        <label for="place_3" class="no-filing-btn">
                                                            <input type="radio" name="place" id="place_3" value="3">
                                                            <span>
                                                                <p>기타</p>
                                                            </span>
                                                            <div class="no-filing-btn-circle"></div>
                                                        </label>
                                                    </li>

                                                </ul>
                                            </div>
                                        </div>

                                        <!---name은 end_date  value는 0 1 을 api로 만들기.--->
                                        <div class="no-form-group">
                                            <h4 class="no-body-md ">진행 현황</h4>
                                            <div class="no-form-radio " id="no-catg-hook">
                                                <ul class="">
                                                    <li>
                                                        <label for="end_date_all" class="no-filing-btn">
                                                            <input type="radio" name="end_date" id="end_date_all"
                                                                value="all" checked>
                                                            <span>
                                                                <p>전체</p>
                                                            </span>
                                                            <div class="no-filing-btn-circle"></div>
                                                        </label>
                                                    </li>

                                                    <li>

                                                        <label for="end_date_begin" class="no-filing-btn">
                                                            <input type="radio" name="end_date" id="end_date_begin"
                                                                value="begin">
                                                            <span>
                                                                <p>진행 · 예정작</p>
                                                            </span>
                                                            <div class="no-filing-btn-circle"></div>
                                                        </label>

                                                    </li>
                                                    <li>

                                                        <label for="end_date_end" class="no-filing-btn">
                                                            <input type="radio" name="end_date" id="end_date_end"
                                                                value="end">
                                                            <span>
                                                                <p>종료작</p>
                                                            </span>
                                                            <div class="no-filing-btn-circle"></div>
                                                        </label>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="f ai-c no-gap-xl">
                                        <div class="no-form-group ">
                                            <h4 class="no-body-md">장르</h4>
                                            <div class="no-form-radio ">
                                                <ul class="" id="no-genre-hook">
                                                    <li>
                                                        <label for="genre_all" class="no-filing-btn">
                                                            <input type="radio" name="genre" id="genre_all" value="all"
                                                                checked>
                                                            <span>
                                                                <p>전체</p>
                                                            </span>
                                                            <div class="no-filing-btn-circle"></div>
                                                        </label>
                                                    </li>
                                                    <li>
                                                        <label for="genre_0" class="no-filing-btn">
                                                            <input type="radio" name="genre" id="genre_0" value="0">
                                                            <span>
                                                                <p>뮤지컬</p>
                                                            </span>
                                                            <div class="no-filing-btn-circle"></div>
                                                        </label>
                                                    </li>
                                                    <li>
                                                        <label for="genre_1" class="no-filing-btn">
                                                            <input type="radio" name="genre" id="genre_1" value="1">
                                                            <span>
                                                                <p>콘서트</p>
                                                            </span>
                                                            <div class="no-filing-btn-circle"></div>
                                                        </label>
                                                    </li>
                                                    <li>
                                                        <label for="genre_2" class="no-filing-btn">
                                                            <input type="radio" name="genre" id="genre_2" value="2">
                                                            <span>
                                                                <p>이벤트</p>
                                                            </span>
                                                            <div class="no-filing-btn-circle"></div>
                                                        </label>
                                                    </li>
                                                    <li>
                                                        <label for="genre_3" class="no-filing-btn">
                                                            <input type="radio" name="genre" id="genre_3" value="3">
                                                            <span>
                                                                <p>기타</p>
                                                            </span>
                                                            <div class="no-filing-btn-circle"></div>
                                                        </label>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>

                                        <div class="no-form-group">
                                            <h4 class=" no-body-md">연도 선택</h4>
                                            <div class="no-date-wrap">
                                                <div class="no-date-box">
                                                    <button class="no-date-btn" type="button">
                                                        <div>
                                                            <i class="fa-regular fa-calendar" aria-hidden="true"></i>
                                                            <span class="no-form-date-text">전체</span>
                                                        </div>
                                                        <i class="fa-regular fa-chevron-down"></i>
                                                    </button>
                                                    <ul class="no-date-list" data-lenis-prevent>
                                                        <li>
                                                            <label for="date_all">
                                                                <input type="radio" id="date_all" name="year"
                                                                    value="all">
                                                                <span>전체</span>
                                                            </label>
                                                        </li>
                                                        <li>
                                                            <label for="date_2025">
                                                                <input type="radio" id="date_2025" name="year"
                                                                    value="2025">
                                                                <span>2025</span>
                                                            </label>
                                                        </li>
                                                        <li>
                                                            <label for="date_2024">
                                                                <input type="radio" id="date_2024" name="year"
                                                                    value="2024">
                                                                <span>2024</span>
                                                            </label>
                                                        </li>
                                                        <li>
                                                            <label for="date_2023">
                                                                <input type="radio" id="date_2023" name="year"
                                                                    value="2023">
                                                                <span>2023</span>
                                                            </label>
                                                        </li>
                                                        <li>
                                                            <label for="date_2022">
                                                                <input type="radio" id="date_2022" name="year"
                                                                    value="2022">
                                                                <span>2022</span>
                                                            </label>
                                                        </li>
                                                        <li>
                                                            <label for="date_2021">
                                                                <input type="radio" id="date_2021" name="year"
                                                                    value="2021">
                                                                <span>2021</span>
                                                            </label>
                                                        </li>
                                                        <li>
                                                            <label for="date_2020">
                                                                <input type="radio" id="date_2020" name="year"
                                                                    value="2020">
                                                                <span>2020</span>
                                                            </label>
                                                        </li>
                                                        <li>
                                                            <label for="date_2019">
                                                                <input type="radio" id="date_2019" name="year"
                                                                    value="2019">
                                                                <span>2019</span>
                                                            </label>
                                                        </li>
                                                        <li>
                                                            <label for="date_2018">
                                                                <input type="radio" id="date_2018" name="year"
                                                                    value="2018">
                                                                <span>2018</span>
                                                            </label>
                                                        </li>
                                                        <li>
                                                            <label for="date_2017">
                                                                <input type="radio" id="date_2017" name="year"
                                                                    value="2017">
                                                                <span>2017</span>
                                                            </label>
                                                        </li>
                                                        <li>
                                                            <label for="date_2016">
                                                                <input type="radio" id="date_2016" name="year"
                                                                    value="2016">
                                                                <span>2016</span>
                                                            </label>
                                                        </li>
                                                        <li>
                                                            <label for="date_2015">
                                                                <input type="radio" id="date_2015" name="year"
                                                                    value="2015">
                                                                <span>2015</span>
                                                            </label>
                                                        </li>
                                                        <li>
                                                            <label for="date_2014">
                                                                <input type="radio" id="date_2014" name="year"
                                                                    value="2014">
                                                                <span>2014</span>
                                                            </label>
                                                        </li>
                                                        <li>
                                                            <label for="date_2013">
                                                                <input type="radio" id="date_2013" name="year"
                                                                    value="2013">
                                                                <span>2013</span>
                                                            </label>
                                                        </li>
                                                        <li>
                                                            <label for="date_2012">
                                                                <input type="radio" id="date_2012" name="year"
                                                                    value="2012">
                                                                <span>2012</span>
                                                            </label>
                                                        </li>
                                                        <li>
                                                            <label for="date_2011">
                                                                <input type="radio" id="date_2011" name="year"
                                                                    value="2011">
                                                                <span>2011</span>
                                                            </label>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                        <div class="cnt no-pd-xl--t">
                            <ul class="grid-col-4 no-skin-gallery-wrap visible-smooth " id="works-hook">
                            </ul>
                            <div id="no-item-hook"></div>
                        </div>
                        <div class="loader-container" id="loader">
                            <svg version="1.1" id="loader-1" xmlns="http://www.w3.org/2000/svg"
                                xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" width="40px" height="40px"
                                viewBox="0 0 40 40" enable-background="new 0 0 40 40" xml:space="preserve">
                                <path opacity="0.2" fill="#000"
                                    d="M20.201,5.169c-8.254,0-14.946,6.692-14.946,14.946c0,8.255,6.692,14.946,14.946,14.946
								s14.946-6.691,14.946-14.946C35.146,11.861,28.455,5.169,20.201,5.169z M20.201,31.749c-6.425,0-11.634-5.208-11.634-11.634
								c0-6.425,5.209-11.634,11.634-11.634c6.425,0,11.633,5.209,11.633,11.634C31.834,26.541,26.626,31.749,20.201,31.749z"></path>
                                <path fill="#000" d="M26.013,10.047l1.654-2.866c-2.198-1.272-4.743-2.012-7.466-2.012h0v3.312h0
								C22.32,8.481,24.301,9.057,26.013,10.047z">
                                    <animateTransform attributeType="xml" attributeName="transform" type="rotate"
                                        from="0 20 20" to="360 20 20" dur="1.4s" repeatCount="indefinite">
                                    </animateTransform>
                                </path>
                            </svg>
                        </div>
                        <div class="no-filter-btn">
                            <button type="button">
                                <div>
                                    <lord-icon src="https://cdn.lordicon.com/eskivvxw.json" stroke="bold" trigger="loop"
                                        colors="primary:#ffffff,secondary:#ffffff">
                                    </lord-icon>
                                    <span class="no-form-filter-text">필터</span>
                                </div>
                            </button>
                        </div>
                    </div>
                </div>
            </section>

            <div class="no-pd-xl--t">
                <nav class="no-pagination">
                    <a href="#" class="no-pagination__arrow no-pagination__first">
                        <i class="fa-solid fa-chevrons-left"></i>
                    </a>
                    <a href="#" class="no-pagination__arrow no-pagination__prev">
                        <i class="fa-light fa-chevron-left"></i>
                    </a>

                    <div class="no-pagination__num" id="pagination-pages">
                        <!-- 페이지 번호가 여기에 동적으로 추가됩니다 -->
                    </div>

                    <a href="#" class="no-pagination__arrow no-pagination__next">
                        <i class="fa-light fa-chevron-right"></i>
                    </a>
                    <a href="#" class="no-pagination__arrow no-pagination__last">
                        <i class="fa-regular fa-chevrons-right"></i>
                    </a>
                </nav>
            </div>
        </div>



        <!-- 모바일용 모달 입력 요소 -->
        <div class="no-filter-modal mobile-form" data-lenis-prevent="">
            <div class="no-filter-modal-container">
                <div class="no-filter-modal-inner">
                    <h2>검색 필터</h2>
                    <div class="no-filter-modal-col">
                        <div class="no-filter-modal-group">
                            <h4>연도 선택</h4>
                            <ul class="no-date-list-mb" data-lenis-prevent>
                                <li>
                                    <label for="date_all_mb" class="no-filing-btn ">
                                        <input type="radio" name="year" id="date_all_mb" value="all">
                                        <span>
                                            <p>전체</p>
                                        </span>
                                        <div class="no-filing-btn-circle"></div>
                                    </label>
                                </li>
                                <li>
                                    <label for="date_2025_mb" class="no-filing-btn ">
                                        <input type="radio" name="year" id="date_2025_mb" value="2025">
                                        <span>
                                            <p>2025</p>
                                        </span>
                                        <div class="no-filing-btn-circle"></div>
                                    </label>
                                </li>
                                <li>
                                    <label for="date_2024_mb" class="no-filing-btn">
                                        <input type="radio" name="year" id="date_2024_mb" value="2024">
                                        <span>
                                            <p>2024</p>
                                        </span>
                                        <div class="no-filing-btn-circle"></div>
                                    </label>
                                </li>
                                <li>
                                    <label for="date_2023_mb" class="no-filing-btn">
                                        <input type="radio" name="year" id="date_2023_mb" value="2023">
                                        <span>
                                            <p>2023</p>
                                        </span>
                                        <div class="no-filing-btn-circle"></div>
                                    </label>
                                </li>
                                <li>
                                    <label for="date_2022_mb" class="no-filing-btn">
                                        <input type="radio" name="year" id="date_2022_mb" value="2022">
                                        <span>
                                            <p>2022</p>
                                        </span>
                                        <div class="no-filing-btn-circle"></div>
                                    </label>
                                </li>
                                <li>
                                    <label for="date_2021_mb" class="no-filing-btn">
                                        <input type="radio" name="year" id="date_2021_mb" value="2021">
                                        <span>
                                            <p>2021</p>
                                        </span>
                                        <div class="no-filing-btn-circle"></div>
                                    </label>
                                </li>
                                <li>
                                    <label for="date_2020_mb" class="no-filing-btn">
                                        <input type="radio" name="year" id="date_2020_mb" value="2020">
                                        <span>
                                            <p>2020</p>
                                        </span>
                                        <div class="no-filing-btn-circle"></div>
                                    </label>
                                </li>
                                <li>
                                    <label for="date_2019_mb" class="no-filing-btn">
                                        <input type="radio" name="year" id="date_2019_mb" value="2019">
                                        <span>
                                            <p>2019</p>
                                        </span>
                                        <div class="no-filing-btn-circle"></div>
                                    </label>
                                </li>
                                <li>
                                    <label for="date_2018_mb" class="no-filing-btn">
                                        <input type="radio" name="year" id="date_2018_mb" value="2018">
                                        <span>
                                            <p>2018</p>
                                        </span>
                                        <div class="no-filing-btn-circle"></div>
                                    </label>
                                </li>
                                <li>
                                    <label for="date_2017_mb" class="no-filing-btn">
                                        <input type="radio" name="year" id="date_2017_mb" value="2017">
                                        <span>
                                            <p>2017</p>
                                        </span>
                                        <div class="no-filing-btn-circle"></div>
                                    </label>
                                </li>
                                <li>
                                    <label for="date_2016_mb" class="no-filing-btn">
                                        <input type="radio" name="year" id="date_2016_mb" value="2016">
                                        <span>
                                            <p>2016</p>
                                        </span>
                                        <div class="no-filing-btn-circle"></div>
                                    </label>
                                </li>
                                <li>
                                    <label for="date_2015_mb" class="no-filing-btn">
                                        <input type="radio" name="year" id="date_2015_mb" value="2015">
                                        <span>
                                            <p>2015</p>
                                        </span>
                                        <div class="no-filing-btn-circle"></div>
                                    </label>
                                </li>
                                <li>
                                    <label for="date_2014_mb" class="no-filing-btn">
                                        <input type="radio" name="year" id="date_2014_mb" value="2014">
                                        <span>
                                            <p>2014</p>
                                        </span>
                                        <div class="no-filing-btn-circle"></div>
                                    </label>
                                </li>
                                <li>
                                    <label for="date_2013_mb" class="no-filing-btn">
                                        <input type="radio" name="year" id="date_2013_mb" value="2013">
                                        <span>
                                            <p>2013</p>
                                        </span>
                                        <div class="no-filing-btn-circle"></div>
                                    </label>
                                </li>
                                <li>
                                    <label for="date_2012_mb" class="no-filing-btn">
                                        <input type="radio" name="year" id="date_2012_mb" value="2012">
                                        <span>
                                            <p>2012</p>
                                        </span>
                                        <div class="no-filing-btn-circle"></div>
                                    </label>
                                </li>
                                <li>
                                    <label for="date_2011_mb" class="no-filing-btn">
                                        <input type="radio" name="year" id="date_2011_mb" value="2011">
                                        <span>
                                            <p>2011</p>
                                        </span>
                                        <div class="no-filing-btn-circle"></div>
                                    </label>
                                </li>
                            </ul>
                        </div>
                        <div class="no-filter-modal-group">
                            <h4>진행 현황</h4>
                            <div class="no-form-radio" id="no-catg-hook_mb">
                                <ul>
                                    <li>
                                        <label for="end_date_all_mb">
                                            <input type="radio" name="end_date" id="end_date_all_mb" value="all">
                                            <span>
                                                <div class="no-form-radio-circle"></div>
                                                <p>전체</p>
                                            </span>
                                        </label>
                                    </li>
                                    <li>
                                        <label for="end_date_begin_mb">
                                            <input type="radio" name="end_date" id="end_date_begin_mb" value="begin">
                                            <span>
                                                <div class="no-form-radio-circle"></div>
                                                <p>진행 · 예정작</p>
                                            </span>
                                        </label>
                                    </li>
                                    <li>
                                        <label for="end_date_end_mb">
                                            <input type="radio" name="end_date" id="end_date_end_mb" value="end">
                                            <span>
                                                <div class="no-form-radio-circle"></div>
                                                <p>종료작</p>
                                            </span>
                                        </label>
                                    </li>
                                </ul>
                            </div>
                        </div>
                        <div class="no-filter-modal-group">
                            <h4 class="no-body-md">공연장</h4>
                            <div class="no-form-radio">
                                <ul id="no-theater-hook">
                                    <li>
                                        <label for="place_all_mb">
                                            <input type="radio" name="place" id="place_all_mb" value="all">
                                            <span>
                                                <div class="no-form-radio-circle"></div>
                                                <p>전체</p>
                                            </span>
                                        </label>
                                    </li>
                                    <li>
                                        <label for="place_0_mb">
                                            <input type="radio" name="place" id="place_0_mb" value="0">
                                            <span>
                                                <div class="no-form-radio-circle"></div>
                                                <p><?= isUpdateActive() ? '우리은행홀' : '신한카드홀' ?></p>
                                            </span>
                                        </label>
                                    </li>

                                    <?php
										$currentDate = date('Y-m-d'); // 현재 날짜
										$targetDate = '2025-04-01'; // 4월 1일 기준

										if ($currentDate < $targetDate): // 4월 1일 이전에만 출력
										?>
                                    <li>
                                        <label for="place_1_mb">
                                            <input type="radio" name="place" id="place_1_mb" value="1">
                                            <span>
                                                <div class="no-form-radio-circle"></div>
                                                <p>마스터카드홀</p>
                                            </span>
                                        </label>
                                    </li>
                                    <?php endif; ?>


                                    <li>
                                        <label for="place_4_mb">
                                            <input type="radio" name="place" id="place_4_mb" value="4">
                                            <span>
                                                <div class="no-form-radio-circle"></div>
                                                <p><?= isUpdateActive() ? '우리WON뱅킹홀' : 'SOL트래블홀' ?></p>
                                            </span>
                                        </label>
                                    </li>
                                    <li>
                                        <label for="place_2_mb">
                                            <input type="radio" name="place" id="place_2_mb" value="2">
                                            <span>
                                                <div class="no-form-radio-circle"></div>
                                                <p>NEMO</p>
                                            </span>
                                        </label>
                                    </li>
                                    <li>
                                        <label for="place_3_mb">
                                            <input type="radio" name="place" id="place_3_mb" value="3">
                                            <span>
                                                <div class="no-form-radio-circle"></div>
                                                <p>기타</p>
                                            </span>
                                        </label>
                                    </li>

                                </ul>
                            </div>
                        </div>
                        <div class="no-filter-modal-group">
                            <h4 class="no-body-md">장르</h4>
                            <div class="no-form-radio">
                                <ul id="no-genre-hook">
                                    <li>
                                        <label for="genre_all_mb">
                                            <input type="radio" name="genre" id="genre_all_mb" value="all">
                                            <span>
                                                <div class="no-form-radio-circle"></div>
                                                <p>전체</p>
                                            </span>
                                        </label>
                                    </li>
                                    <li>
                                        <label for="genre_0_mb">
                                            <input type="radio" name="genre" id="genre_0_mb" value="뮤지컬">
                                            <span>
                                                <div class="no-form-radio-circle"></div>
                                                <p>뮤지컬</p>
                                            </span>
                                        </label>
                                    </li>
                                    <li>
                                        <label for="extra_1_2_mb">
                                            <input type="radio" name="genre" id="extra_1_2_mb" value="콘서트">
                                            <span>
                                                <div class="no-form-radio-circle"></div>
                                                <p>콘서트</p>
                                            </span>
                                        </label>
                                    </li>
                                    <li>
                                        <label for="extra_1_3_mb">
                                            <input type="radio" name="genre" id="extra_1_3_mb" value="이벤트">
                                            <span>
                                                <div class="no-form-radio-circle"></div>
                                                <p>이벤트</p>
                                            </span>
                                        </label>
                                    </li>
                                    <li>
                                        <label for="extra_1_4_mb">
                                            <input type="radio" name="genre" id="extra_1_4_mb" value="기타">
                                            <span>
                                                <div class="no-form-radio-circle"></div>
                                                <p>기타</p>
                                            </span>
                                        </label>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="no-filter-modal-close">
                        <button class="no-filter-modal-close-btn" type="button">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>
                </div>
                <div class="no-filter-modal-btn">
                    <!--
						<div class="no-filter-modal-reset-btn">
							<button type="button">
								<i class="fa-regular fa-trash"></i>
								Reset
								<span class="no-filter-modal-state">1</span>
							</button>
						</div>-->
                    <div class="no-filter-modal-submit-btn">
                        <button type="button">적용하기</button>
                    </div>
                </div>
            </div>
        </div>

    </form>





</main>

<script type="text/javascript" src="/resource/js/api2.js?v=<?=$STATIC_FRONT_JS_MODIFY_DATE?>"></script>

<?php
    include_once $STATIC_ROOT.'/inc/layouts/footer.php';
    ?>