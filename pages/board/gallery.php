<?php include_once $_SERVER['DOCUMENT_ROOT'].'/inc/lib/base.class.php'; ?>

<!-- dev -->

<?php include_once $STATIC_ROOT.'/inc/layouts/head.php'; ?>

<!-- css, js  -->
<?php 
    include_once $STATIC_ROOT.'/inc/layouts/header.php';
    include_once $STATIC_ROOT.'/inc/shared/sub.visual.works.php';
?>

<div class="no-form-search-container no-container-lg">
    <div class="no-form-group">
        <div class="no-form-search">
            <input type="text" name="search_term" id="search_term" placeholder="검색어를 입력해주세요.">
            <button type="submit" class="no-form-search-icon">
                <i class="fa-light fa-magnifying-glass"></i>
            </button>
        </div>
    </div>
</div>

<!-- contents -->


<main class="no-board">
    <section class="no-pd-2xl--y">
        <div class="no-skin-gallery">
            <div class="no-container-2xl">

                <div class="no-form-container">
                    <div class="no-form-head">
                        <div class="no-form-row">
                            <div class="no-form-group">
                                <div class="no-form-radio no-form-radio-line">
                                    <label for="upcoming">
                                        <input type="radio" name="project_status" id="upcoming" value="M" checked>
                                        <span>
                                            진행 · 예정작
                                        </span>
                                    </label>
                                    <label for="event">
                                        <input type="radio" name="project_status" id="event" value="F">
                                        <span>

                                            이벤트
                                        </span>
                                    </label>
                                    <label for="end">
                                        <input type="radio" name="project_status" id="end" value="F">
                                        <span>
                                            종료작
                                        </span>
                                    </label>
                                    <div class="no-form-radio-line-rect"></div>
                                </div>
                            </div>
                            <div class="no-form-group">
                                <div class="no-form-date-box">
                                    <button class="no-form-date" type="button">
                                        <div>
                                            <i class="fa-regular fa-calendar"></i>
                                            <span class="no-form-date-text">연도별</span>
                                        </div>
                                        <i class="fa-regular fa-chevron-down"></i>
                                    </button>
                                    <ul class="date-list" data-lenis-prevent></ul>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="no-form-row">
                        <div class="no-form-group f ai-c no-gap-md">
                            <h4 class="no-heading-sm">공연장</h4>
                            <div class="no-form-radio no-form-radio-circle">
                                <label for="all">
                                    <input type="radio" name="venue" id="all" value="all" checked>
                                    <span>
                                        <div class="no-form-radio__box">
                                            <i class="fa-regular fa-check"></i>
                                        </div>
                                        <p>전체</p>
                                    </span>
                                </label>
                                <label for="shinhan_card_hall">
                                    <input type="radio" name="venue" id="shinhan_card_hall" value="shinhan_card_hall">
                                    <span>
                                        <div class="no-form-radio__box">
                                            <i class="fa-regular fa-check"></i>
                                        </div>
                                        <p>신한카드홀</p>
                                    </span>
                                </label>
                                <label for="master_card_hall">
                                    <input type="radio" name="venue" id="master_card_hall" value="master_card_hall">
                                    <span>
                                        <div class="no-form-radio__box">
                                            <i class="fa-regular fa-check"></i>
                                        </div>
                                        <p>마스터카드홀</p>
                                    </span>
                                </label>
                                <label for="nemo">
                                    <input type="radio" name="venue" id="nemo" value="nemo">
                                    <span>
                                        <div class="no-form-radio__box">
                                            <i class="fa-regular fa-check"></i>
                                        </div>
                                        <p>NEMO</p>
                                    </span>
                                </label>
                                <label for="event">
                                    <input type="radio" name="venue" id="event" value="event">
                                    <span>
                                        <div class="no-form-radio__box">
                                            <i class="fa-regular fa-check"></i>
                                        </div>
                                        <p>이벤트</p>
                                    </span>
                                </label>
                            </div>
                        </div>
                        <div class="no-form-group f ai-c no-gap-md">
                            <h4 class="no-heading-sm">장르</h4>
                            <div class="no-form-radio no-form-radio-circle">
                                <label for="all-genres">
                                    <input type="radio" name="genre" id="all-genres" value="all" checked>
                                    <span>
                                        <div class="no-form-radio__box">
                                            <i class="fa-regular fa-check"></i>
                                        </div>
                                        전체
                                    </span>
                                </label>
                                <label for="concert">
                                    <input type="radio" name="genre" id="concert" value="concert">
                                    <span>
                                        <div class="no-form-radio__box">
                                            <i class="fa-regular fa-check"></i>
                                        </div>
                                        콘서트
                                    </span>
                                </label>
                                <label for="musical">
                                    <input type="radio" name="genre" id="musical" value="musical">
                                    <span>
                                        <div class="no-form-radio__box">
                                            <i class="fa-regular fa-check"></i>
                                        </div>
                                        뮤지컬
                                    </span>
                                </label>
                                <label for="fan_meeting">
                                    <input type="radio" name="genre" id="fan_meeting" value="fan_meeting">
                                    <span>
                                        <div class="no-form-radio__box">
                                            <i class="fa-regular fa-check"></i>
                                        </div>
                                        팬미팅
                                    </span>
                                </label>
                                <label for="performance">
                                    <input type="radio" name="genre" id="performance" value="performance">
                                    <span>
                                        <div class="no-form-radio__box">
                                            <i class="fa-regular fa-check"></i>
                                        </div>
                                        공연
                                    </span>
                                </label>
                            </div>
                        </div>
                    </div>

                </div>
                <div class="cnt no-pd-xl--t">
                    <ul class="grid-col-4 no-gap-lg no-skin-gallery-wrap">
                        <?php for ($i = 1 ; $i < 12; $i++) :?>
                        <li class="no-skin-gallery-item">
                            <a href="#">
                                <figure>
                                    <img src="<?=IMG_PATH?>/works/gallery_img_1.png" alt="">
                                </figure>
                                <div class="no-skin-gallery-item__txt no-pd-sm--t">
                                    <span>콘서트</span>
                                    <h3 class="no-heading-sm --fw-bold">장기하 단독 공연 [하기장기하] (12월 8일 일요일)</h3>
                                    <div class="f ai-c no-gap-3xs">
                                        <div class="tag">2024.10.01 - 10.01</div>
                                        <div class="tag">신한카드몰</div>
                                    </div>
                                </div>
                            </a>
                        </li>
                        <?php endfor ;?>
                    </ul>
                </div>
            </div>
        </div>
    </section>
</main>



<?php
    include_once $STATIC_ROOT.'/inc/layouts/footer.php';
    ?>