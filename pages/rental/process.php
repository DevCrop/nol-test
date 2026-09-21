<?php include_once $_SERVER['DOCUMENT_ROOT'].'/inc/lib/base.class.php'; ?>
<?php include_once $_SERVER['DOCUMENT_ROOT'].'/inc/lib/TimedValues.php'; ?>
<!-- dev -->

<?php include_once $STATIC_ROOT.'/inc/layouts/head.php'; ?>

<!-- css, js  -->
<?php 
    include_once $STATIC_ROOT.'/inc/layouts/header.php';
    include_once $STATIC_ROOT.'/inc/shared/sub.visual.php';
    include_once $STATIC_ROOT.'/inc/shared/sub.nav.php';
?>


<!-- contents -->
<main class="no-sub ">
    <section class="no-sub-process no-pd-2xl--y">
        <div class="no-container-xl">
            <div class="">
                <h2 class="no-heading-lg " <?=$aos_title?>>대관 Process</h2>
                <div class="cnt no-pd-lg--t --process ">
                    <ul class="grid-col-5 ">
                        <li class="active">
                            <span>대관공고</span>
                            <div class="--ripple-num-box">
                                <div class="--ripple-num">
                                    <div class="num">1</div>
                                    <div class="ripple"></div>
                                    <div class="ripple"></div>
                                </div>
                                <div class="--ripple-num-line">
                                    <span></span>
                                </div>
                            </div>
                        </li>
                        <li>
                            <span>대관신청</span>
                            <div class="--ripple-num-box">
                                <div class="--ripple-num">
                                    <div class="num">2</div>
                                    <div class="ripple"></div>
                                    <div class="ripple"></div>
                                </div>
                                <div class="--ripple-num-line">
                                    <span></span>
                                </div>
                            </div>
                        </li>
                        <li>
                            <span>대관심의</span>
                            <div class="--ripple-num-box">
                                <div class="--ripple-num">
                                    <div class="num">3</div>
                                    <div class="ripple"></div>
                                    <div class="ripple"></div>
                                </div>
                                <div class="--ripple-num-line">
                                    <span></span>
                                </div>
                            </div>
                        </li>
                        <li>
                            <span>승인 · 불가 통보</span>
                            <div class="--ripple-num-box">
                                <div class="--ripple-num">
                                    <div class="num">4</div>
                                    <div class="ripple"></div>
                                    <div class="ripple"></div>
                                </div>
                                <div class="--ripple-num-line">
                                    <span></span>
                                </div>
                            </div>
                        </li>
                        <li>
                            <span>대관계약</span>
                            <div class="--ripple-num-box">
                                <div class="--ripple-num">
                                    <div class="num">5</div>
                                    <div class="ripple"></div>
                                    <div class="ripple"></div>
                                </div>
                                <div class="--ripple-num-line">
                                    <span></span>
                                </div>
                            </div>
                        </li>
                        <li class="">
                            <span>계약금 납부</span>
                            <div class="--ripple-num-box">
                                <div class="--ripple-num">
                                    <div class="num">6</div>
                                    <div class="ripple"></div>
                                    <div class="ripple"></div>
                                </div>
                                <div class="--ripple-num-line">
                                    <span></span>
                                </div>
                            </div>
                        </li>
                        <li>
                            <span>공연장 홍보물 게시</span>
                            <div class="--ripple-num-box">
                                <div class="--ripple-num">
                                    <div class="num">7</div>
                                    <div class="ripple"></div>
                                    <div class="ripple"></div>
                                </div>
                                <div class="--ripple-num-line">
                                    <span></span>
                                </div>
                            </div>
                        </li>
                        <li>
                            <span>입장권 검인</span>
                            <div class="--ripple-num-box">
                                <div class="--ripple-num">
                                    <div class="num">8</div>
                                    <div class="ripple"></div>
                                    <div class="ripple"></div>
                                </div>
                                <div class="--ripple-num-line">
                                    <span></span>
                                </div>
                            </div>
                        </li>
                        <li>
                            <span>스태프 회의</span>
                            <div class="--ripple-num-box">
                                <div class="--ripple-num">
                                    <div class="num">9</div>
                                    <div class="ripple"></div>
                                    <div class="ripple"></div>
                                </div>
                                <div class="--ripple-num-line">
                                    <span></span>
                                </div>
                            </div>
                        </li>
                        <li>
                            <span>공연 진행</span>
                            <div class="--ripple-num-box">
                                <div class="--ripple-num">
                                    <div class="num">10</div>
                                    <div class="ripple"></div>
                                    <div class="ripple"></div>
                                </div>
                                <div class="--ripple-num-line">
                                    <span></span>
                                </div>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="no-pd-2xl--t">
                <h2 class="no-heading-lg" <?=$aos_title?>>대관 정보</h2>
                <div class="cnt no-pd-lg--t" <?=$aos_content?>>
                    <div class="--box-wrap">
                        <ul>
                            <li class="--box">
                                <div class="--box-info ">
                                    <ul class="">
                                        <li class="">
                                            <div class="--box-info-title no-heading-md">
                                                <h3>대관 공고</h3>
                                            </div>
                                            <div class="--box-info-desc">
                                                <ul class="f fd-c no-gap-xs">
                                                    <li>
                                                        <ul class="">
                                                            <li>
                                                                <span class="no-body-md">
                                                                    <?= isUpdateActive() ? '블루스퀘어 우리은행홀' : '신한카드홀' ?>
                                                                </span>
                                                            </li>

                                                            <li>
                                                                <p class="no-body-base">
                                                                    홈페이지 대관 공고를 통한 접수
                                                                </p>
                                                            </li>
                                                        </ul>
                                                    </li>
                                                </ul>
                                                <ul class="f fd-c no-gap-xs">
                                                    <li>
                                                        <ul class="">
                                                            <li>
                                                                <span class="no-body-md">
                                                                    그 외
                                                                </span>
                                                            </li>

                                                            <li>
                                                                <p class="no-body-base">
                                                                    <?= isUpdateActive() ? '블루스퀘어 우리은행홀' : '신한카드홀' ?>을 제외한 기타
                                                                    공간은 별도의 공고 없이 수시 대관 접수
                                                                </p>
                                                            </li>
                                                        </ul>
                                                    </li>
                                                </ul>

                                            </div>
                                        </li>
                                        <li class="">
                                            <div class="--box-info-title no-heading-md">
                                                <h3>신청방법</h3>
                                            </div>
                                            <div class="--box-info-desc">
                                                <ul class="f fd-c no-gap-xs">
                                                    <li>
                                                        <ul class="">
                                                            <li>
                                                                <span class="no-body-md">
                                                                    <?= isUpdateActive() ? '블루스퀘어 우리은행홀' : '신한카드홀' ?>
                                                                </span>
                                                            </li>
                                                            <li>
                                                                <p class="no-body-base">
                                                                    공고 기간 中 홈페이지 대관안내 - 대관신청 페이지를 통해 신청
                                                                </p>
                                                            </li>
                                                        </ul>
                                                    </li>
                                                </ul>
                                                <ul class="f fd-c no-gap-xs">
                                                    <li>
                                                        <ul class="">
                                                            <li>
                                                                <span class="no-body-md">
                                                                    그 외
                                                                </span>
                                                            </li>
                                                            <li>
                                                                <p class="no-body-base">
                                                                    각 홀별 담당자 개별 문의 후, 대관신청서 E-mail 접수 / 대관신청서 양식 대관안내 -
                                                                    대관신청 페이지 참고
                                                                </p>
                                                            </li>
                                                        </ul>
                                                    </li>
                                                </ul>

                                            </div>
                                        </li>
                                    </ul>
                                </div>

                                <div class="no-pd-lg--t">
                                    <div class="--box-notice">
                                        <div class="--box-info">
                                            <ul class="">
                                                <li class="">
                                                    <div class="--box-info-title no-heading-md">
                                                        <h3>대관심의 및 통보</h3>
                                                    </div>
                                                    <div class="--box-info-desc --box-notice-desc">
                                                        <ul class="">
                                                            <li>
                                                                <p class="no-body-md">
                                                                    심의 결과 승인 여부 및 제출 필요 서류 개별 통보 (메일, 문자 또는 유선 통보)
                                                                </p>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="no-pd-2xl--t">
                <div class="grid-col-4-8 no-gap-lg">
                    <h2 class="no-heading-lg" <?=$aos_title?>>대관 문의</h2>
                    <div class="cnt" <?=$aos_content?>>
                        <ul class="grid-col-2 no-gap-lg">
                            <li class="--card">
                                <h4 class="no-heading-md"><?= isUpdateActive() ? '블루스퀘어 우리은행홀' : '신한카드홀' ?></h4>
                                <div class="no-pd-md--t no-body-md">
                                    <ul class="f fd-c  no-gap-xs">
                                        <li class="f ai-c no-gap-sm">
                                            <i class="fa-solid fa-phone"></i>
                                            <span>02 - 6399 - 7486</span>
                                        </li>
                                        <li class="f ai-c no-gap-sm">
                                            <i class="fa-solid fa-envelope"></i>
                                            <span>tintin@nol-theater.com</span>
                                        </li>
                                    </ul>
                                </div>
                            </li>
                            <li class="--card">
                                <h4 class="no-heading-md">
                                    <?= isUpdateActive() ? ($hallName === 'SOL트래블홀' ? '우리WON뱅킹홀' : $hallName) : $hallName ?>
                                </h4>
                                <div class="no-pd-md--t no-body-md">
                                    <ul class="f fd-c  no-gap-xs">
                                        <li class="f ai-c no-gap-sm">
                                            <i class="fa-solid fa-phone"></i>
                                            <span>02 - 6399 - 7515</span>
                                        </li>
                                        <li class="f ai-c no-gap-sm">
                                            <i class="fa-solid fa-envelope"></i>
                                            <span>biglucky@nol-theater.com</span>
                                        </li>
                                    </ul>
                                </div>
                            </li>
                            <li class="--card">
                                <h4 class="no-heading-md">NEMO</h4>
                                <div class="no-pd-md--t no-body-md">
                                    <ul class="f fd-c  no-gap-xs">
                                        <li class="f ai-c no-gap-sm">
                                            <i class="fa-solid fa-phone"></i>
                                            <span>02 - 6399 - 7552</span>
                                        </li>
                                        <li class="f ai-c no-gap-sm">
                                            <i class="fa-solid fa-envelope"></i>
                                            <span>pdh424@nol-theater.com</span>
                                        </li>
                                    </ul>
                                </div>
                            </li>
                            <li class="--card">
                                <h4 class="no-heading-md">연습실</h4>
                                <div class="no-pd-md--t no-body-md">
                                    <ul class="f fd-c  no-gap-xs">
                                        <li class="f ai-c no-gap-sm">
                                            <i class="fa-solid fa-phone"></i>
                                            <span>02 - 6399 - 7552</span>
                                        </li>
                                        <li class="f ai-c no-gap-sm">
                                            <i class="fa-solid fa-envelope"></i>
                                            <span>pdh424@nol-theater.com</span>
                                        </li>
                                    </ul>
                                </div>
                            </li>
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
