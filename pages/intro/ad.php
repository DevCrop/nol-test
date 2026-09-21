<?php include_once $_SERVER['DOCUMENT_ROOT'].'/inc/lib/base.class.php'; ?>

<!-- dev -->

<?php include_once $STATIC_ROOT.'/inc/layouts/head.php'; ?>

<!-- css, js  -->
<?php 
    include_once $STATIC_ROOT.'/inc/layouts/header.php';
    include_once $STATIC_ROOT.'/inc/shared/sub.visual.ad.php';
?>


<!-- contents -->
<main class="no-sub ">
    <section class="no-sub-fac no-pd-2xl--y">
        <div class="no-sub-fac-contents no-sub-tab-contents " <?=$aos_content?>>
            <ul>
                <!----content-1--->
                <li class="fnb" id="fnb">
                    <div class="no-container-xl">
                        <!--cnt---->
                        <div class="cnt  ">
                            <div class="no-pd-lg--b">
                                <div class="no-sub-tab-contents__info no-position-padding ">
                                    <div class="no-sub-tab-contents__title">
                                        <div class="no-sub-fac-logo">
                                            <h2 class="no-heading-lg">전광판 및 기타 광고</h2>
                                        </div>
										<a href="mailto:kihyun123v@interparktriple.com"
										class="no-btn-arrow no-btn-arrow__fill--primary" target="_blank">
										문의하기
										<span>
											<i class="fa-regular fa-arrow-right"></i>
											<i class="fa-regular fa-arrow-right"></i>
										</span>
										</a>
                                    </div>
                                    <div class="no-sub-tab-contents__desc">
                                        <ul>
                                            <li>
                                                <p class="no-body-md">
                                                    공연장 내ᆞ외부에 위치한 광고매체로 공연장 방문객 뿐 아니라 
													일반 시민에게까지 효과적으로 광고 노출이 가능합니다. 
													자세한 내용은 아래 담당자 정보를 참고하시어 문의 바랍니다.
                                                </p>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div class="no-pd-lg--t --card-wrap">
                                <ul class="grid-col-3 no-gap-lg ">
                                    <li class="--card">
                                        <div class="--card-txt">
                                            <h3>위치</h3>
                                            <div class="--card-info">
                                                <ul>
                                                    <li>
                                                        <p>지하철 6호선(한강진역) 연결통로 <br>
														우리은행홀 로비 외</p>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                        <div class="--card-icon">
                                            <i class="fa-solid fa-location-dot"></i>
                                        </div>
                                    </li>
                                    <li class="--card">
                                        <div class="--card-txt">
                                            <h3>광고매체</h3>
                                            <div class="--card-info">
                                                <ul>
                                                    <li>
                                                        <p>
                                                           자이언트 LED / 대형 LED&LCD 외
                                                        </p>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                        <div class="--card-icon">
                                           <i class="fa-solid fa-desktop"></i>
                                        </div>
                                    </li>
                                    <li class="--card">
                                        <div class="--card-txt">
                                            <h3>문의</h3>
                                            <div class="--card-info">
                                                <ul>
                                                    <li>
                                                        <p>kihyun123v@nol-theater.com</p>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                        <div class="--card-icon">
                                           <i class="fa-solid fa-envelope"></i>
                                        </div>
                                    </li>
                                </ul>
                            </div>
                           <div class="no-pd-lg--t">
								<div class="swiper no-sub-tab-contents-slider fnb-swiper">
									<ul class="swiper-wrapper">
										<?php 
										$timestamp = time(); // 현재 시간(Unix Timestamp) 생성
										for ($i = 1; $i < 4; $i++) : 
										?>
										<li class="swiper-slide">
											<figure>
												<img src="<?= IMG_PATH ?>/sub/ad_img_<?= $i ?>_re.jpg?time=<?= $timestamp ?>" alt="">
											</figure>
										</li>
										<?php endfor; ?>
									</ul>
									<div class="swiper-button-next swiper-button">
										<i class="fa-light fa-arrow-right-long"></i>
									</div>
									<div class="swiper-button-prev swiper-button">
										<i class="fa-light fa-arrow-left-long"></i>
									</div>
								</div>
							</div>
                        </div>
                    </div>
                </li>
            </ul>
        </div>
    </section>
</main>



<?php
    include_once $STATIC_ROOT.'/inc/layouts/footer.php';
    ?>