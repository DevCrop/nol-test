<?php include_once $_SERVER['DOCUMENT_ROOT'].'/inc/lib/base.class.php'; ?>

<!-- dev -->

<?php include_once $STATIC_ROOT.'/inc/layouts/head.php'; ?>

<!-- css, js  -->
<?php 
    include_once $STATIC_ROOT.'/inc/layouts/header.php';
    include_once $STATIC_ROOT.'/inc/shared/sub.visual.php';
    include_once $STATIC_ROOT.'/inc/shared/sub.nav.php';
?>


<!-- contents -->
<main class="no-board  ">
    <section class="no-sub-list no-pd-2xl--y">
        <div class="no-container-xl">
            <div class="f ai-c jc-sb">
                <!---category-->
                <div class="no-sub-category">
                    <div class="no-sub-category-slider">
                        <ul class="swiper-wrapper">
                            <li class="swiper-slide">
                                <a href="#" class=" no-btn active no-btn__fill">
                                    전체
                                </a>
                            </li>
                            <li class="swiper-slide">
                                <a href="#" class=" no-btn no-btn__fill">
                                    공연
                                </a>
                            </li>
                            <li class="swiper-slide">
                                <a href="#" class=" no-btn no-btn__fill">
                                    공고
                                </a>
                            </li>
                            <li class="swiper-slide">
                                <a href="#" class=" no-btn active no-btn__fill">
                                    채용
                                </a>
                            </li>
                            <li class="swiper-slide">
                                <a href="#" class=" no-btn ">
                                    기타
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
                <!---search---->
                <div class="">
                    <div class="no-form-search">
                        <input type="text" name="search_term" id="search_term" placeholder="검색어를 입력해주세요.">
                        <button type="submit" class="no-form-search-icon">
                            <i class="fa-light fa-magnifying-glass"></i>
                        </button>
                    </div>
                </div>
            </div>
            <div class="no-pd-lg--t">
                <div class="no-skin-list">
                    <div class="--table">
                        <table class="no-skin-list-table">
                            <colgroup>
                                <col style="width: 9%;">
                                <col style="width: 9%">
                                <col style="width: 60%;">
                                <col style="width: 10%;">
                                <col style="width: 10%;">
                            </colgroup>
                            <thead>
                                <tr class="no-body-lg --fw-semibold">
                                    <th class="head">번호</th>
                                    <th class="head">카테고리</th>
                                    <th class="--tal head">제목</th>
                                    <th class="head">조회수</th>
                                    <th class="head">날짜</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php for ($i = 0; $i < 12; $i++) : ?>
                                <tr>
                                    <td class="body --mobile-full no-notice-num">
                                        <span class="">5</span>
                                        <!-- <span class="no-notice-megaphone">
                                            <i class="fa-solid fa-megaphone"></i>
                                        </span> -->
                                    </td>
                                    <td class="body">
                                        <span class=" category">카테고리</span>
                                    </td>
                                    <td class="--t-start  body">
                                        <a href="" class="no-clr-text-title no-body-lg --fw-semibold">
                                            <strong>
                                                LG전자 ESG 대학생 아카데미 11기 모집 최종 합격자 결과 발표 안내
                                            </strong>
                                        </a>
                                    </td>
                                    <td class=" body">
                                        <span class="" data-label="조회수">
                                            1500
                                        </span>
                                    </td>
                                    <td class="body">
                                        <span class="" data-label="등록일">
                                            2025.02.21
                                        </span>
                                    </td>
                                </tr>
                                <?php endfor ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
            <div class="no-pd-xl--t">
                <nav class="no-pagination">

                    <a href="#" class="no-pagination__arrow">
                        <i class="fa-light fa-chevron-left"></i>
                    </a>

                    <div class="no-pagination__num">
                        <a href="#" class="no-pagination__link">
                            <span>1</span>
                        </a>
                        <a href="#" class="no-pagination__link --active">
                            <span>2</span>
                        </a>
                        <a href="#" class="no-pagination__link">
                            <span>3</span>
                        </a>
                        <a href="#" class="no-pagination__link">
                            <span>4</span>
                        </a>
                        <a href="#" class="no-pagination__link">
                            <span>5</span>
                        </a>
                    </div>

                    <a href="#" class="no-pagination__arrow">
                        <i class="fa-light fa-chevron-right"></i>
                    </a>

                </nav>
            </div>

        </div>
    </section>

</main>

<?php
    include_once $STATIC_ROOT.'/inc/layouts/footer.php';
    ?>