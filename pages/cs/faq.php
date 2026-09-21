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
<main class="no-board">
    <section class="no-sub-faq no-pd-2xl--y">
        <div class="no-container-xl">
            <!---category-->
            <div class="no-sub-category">
                <div class="no-sub-category-slider">
                    <ul class="swiper-wrapper">
                        <li class="swiper-slide">
                            <a href="#" class=" no-btn active">
                                전체
                            </a>
                        </li>
                        <li class="swiper-slide">
                            <a href="#" class=" no-btn ">
                                공연
                            </a>
                        </li>
                        <li class="swiper-slide">
                            <a href="#" class=" no-btn ">
                                공고
                            </a>
                        </li>
                        <li class="swiper-slide">
                            <a href="#" class=" no-btn active ">
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

            <div class="no-pd-lg--t">
                <div class="no-skin-faq">
                    <div class="no-skin-faq-container">
                        <ul class="no-skin-faq-list">
                            <?php for ($i = 0; $i < 14; $i++): ?>
                            <li class="no-skin-faq-item" data-faq-item>
                                <header class="no-skin-faq-head">
                                    <button type="button">
                                        <div class="no-skin-faq-item__title">
                                            <div class="no-skin-faq-item__icon">
                                                <span>Q</span>
                                            </div>
                                            <h3 class="no-body-xl --fw-semibold --t-start">
                                                대학생이라면 누구나 지원할 수 있나요?
                                            </h3>
                                        </div>
                                        <div class="no-skin-faq-item__arrow">
                                            <span></span>
                                            <span></span>
                                        </div>
                                    </button>
                                </header>
                                <section class="no-skin-faq-body">
                                    <div>
                                        <div class="no-skin-faq-item__icon --dark">
                                            <span>A</span>
                                        </div>
                                        <div class="no-skin-faq-body__content --fw-regular">
                                            네 재학생, 휴학생 상관없이 대학생이라면 누구나 지원 가능합니다! <br>
                                            졸업유예 혹은 추가학기등의 정규학기가 아닐지라도 신분이 대학생이라면 누구나 지원이 가능합니다.
                                        </div>
                                    </div>
                                </section>
                            </li>
                            <?php endfor; ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>

</main>

<?php
    include_once $STATIC_ROOT.'/inc/layouts/footer.php';
    ?>