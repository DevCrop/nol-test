<footer class="no-footer no-pd-xl--y">

    <div class="no-container-2xl">
        <div class="no-footer__inner ">
            <div class="no-footer__top no-pd-lg--b">
                <h1 class="no-footer__logo">
                    <a href="#" class="--toggle-images">
                        <img src="<?=IMG_PATH?>/logo/Logo_C_White.png" alt="" class="--dark-img">
                        <img src="<?=IMG_PATH?>/logo/Logo_C_Black.png" alt="" class="--light-img">
                        <span class="--blind">공연의 모든 것 - 블루스퀘어</span>
                    </a>
                </h1>
                <div class="no-footer__sponsor">
                    <h5 class="no-body-lg">Sponsor</h5>
                    <ul>
                      <?php if (isUpdateActive()): ?>
							<li>
								<a href="https://www.wooribank.com/" target="_blank">
									<div class="--toggle-images">
										<img src="<?=IMG_PATH?>/logo/woori_bank_logo_dark.png" class="--light-img" alt="">
										<img src="<?=IMG_PATH?>/logo/woori_bank_logo_white.png" class="--dark-img" alt="">
									</div>
								</a>
							</li>
						<?php else: ?>
							<li>
								<a href="https://www.shinhancard.com/pconts/html/main.html" target="_blank">
									<div class="--toggle-images">
										<img src="<?=IMG_PATH?>/logo/sponsor_img_1_light.png" class="--light-img" alt="">
										<img src="<?=IMG_PATH?>/logo/sponsor_img_1_dark.png" class="--dark-img" alt="">
									</div>
								</a>
							</li>
							<li>
								<a href="https://www.mastercard.co.kr/ko-kr.html" target="_blank">
									<div class="--toggle-images">
										<img src="<?=IMG_PATH?>/logo/master_card_logo_dark.png" class="--light-img" alt="">
										<img src="<?=IMG_PATH?>/logo/master_card_logo_white.png" class="--dark-img" alt="">
									</div>
								</a>
							</li>
						<?php endif; ?>
                    </ul>
                </div>


            </div>
            <div class="no-footer__bottom no-pd-md--t">
                <div class="no-footer__info">
                    <ul>
                        <!--
						<li>
                            <b>사업자등록번호</b>
                            <p><?=$SITEINFO_FOOTER_SSN?></p>
                        </li>-->
                        <li>
                            <b>주소</b>
                            <p><?=$SITEINFO_FOOTER_ADDRESS?></p>
                        </li>
                        <li>
                            <b>고객센터</b>
                            <p><?=$SITEINFO_FOOTER_PHONE?></p>
                        </li>
                        <li>
                            <b>E-Mail</b>
                            <p><?=$SITEINFO_FOOTER_EMAIL?></p>
                        </li>
                        <!--
                        <li>
                            <b>운영시간</b>
                            <p><?=$SITEINFO_FOOTER_FAX?></p>
                        </li>-->
                    </ul>
                </div>
                <div class="no-footer-wrap">
                    <ul class="no-footer__privacy">
                        <li class="--pos-r">
                            <button type="button" class="f ai-c no-gap-sm jc-sb">
                                개인정보처리방침
                                <i class="fa-regular fa-chevron-down"></i>
                            </button>
                            <ul class="no-footer__privacy-list">
								<?php if (date('Ymd') >= '20260212'): ?>
									<li>
										<a href="<?=$ROOT?>/pages/legal/legal_260212.php" class="f ai-c no-gap-sm jc-sb ">
											2026.02.12
											<i class="fa-regular fa-arrow-right" aria-hidden="true"></i>
										</a>
									</li>
								<?php endif; ?>
								<li>
                                    <a href="<?=$ROOT?>/pages/legal/legal_250801.php" class="f ai-c no-gap-sm jc-sb ">
                                        2025.08.01
                                        <i class="fa-regular fa-arrow-right"></i>
                                    </a>
                                </li>
                                <li>
                                    <a href="<?=$ROOT?>/pages/legal/legal_250217.php" class="f ai-c no-gap-sm jc-sb ">
                                        2025.02.17
                                        <i class="fa-regular fa-arrow-right"></i>
                                    </a>
                                </li>
                                <li>
                                    <a href="<?=$ROOT?>/pages/legal/legal_181001.php" class="f ai-c no-gap-sm jc-sb ">
                                        2018.10.01
                                        <i class="fa-regular fa-arrow-right"></i>
                                    </a>
                                </li>
                            </ul>
                        </li>
                        <li>
                            <a href="<?=$ROOT?>/pages/board/board.list.php?board_no=13"
                                class="f al-c no-gap-sm jc-sb ai-c ">
                                <p>
                                    기업공고
                                </p>
                                <i class="fa-regular fa-arrow-right"></i>
                            </a>
                        </li>
                        <li>
                            <a href=" https://www.kbei.org/y-siren" target="_blank"
                                class="f al-c no-gap-sm jc-sb ai-c ">
                                <p>
                                    윤리경영 / 제보
                                </p>
                                <i class="fa-regular fa-arrow-right"></i>
                            </a>
                        </li>
                    </ul>
                    <div class="no-footer__address ">
                        <p>Copyright ⓒ NOL theater All Rights Reserved.</p>
                    </div>
                </div>

            </div>
        </div>
    </div>


</footer>

<div class="no-top-btn">
    <a href="<?=$ROOT?>/pages/cs/parking.php">
        <div>
            <span>주차 안내</span>
        </div>
        <i class="fa-solid fa-car"></i>
    </a>
    <button type="button" class="active">
        <div class="arrow">
            <i class="fa-regular fa-arrow-up-long" aria-hidden="true"></i>
            <i class="fa-regular fa-arrow-up-long" aria-hidden="true"></i>
        </div>
    </button>

</div>

<div id="modal" data-lenis-prevent="">
    <!-- 신한 모달 -->
    <div class="no-modal-shinhan">
        <ul class="modal-wrap ">
            <!-- 북파크라운지 모달 -->
            <li class="modal-item " data-modal-id="book">
                <div>
                    <div class="img">
                        <div class="swiper no-modal-swiper">
                            <ul class="swiper-wrapper">
                                <?php for ($i = 1; $i < 4 ; $i++) :?>
                                <li class="swiper-slide">
                                    <figure>
                                        <img src="<?=IMG_PATH?>/sub/fnb_book_img_<?=$i?>.jpg" alt="">
                                    </figure>
                                </li>
                                <?php endfor; ?>
                            </ul>
                            <div class="swiper-pagination no-modal-swiper-pagination"></div>
                        </div>
                    </div>
                    <div class="txt">
                        <div class="title no-pd-lg--b">
                            <h4 class="no-heading-lg --fw-bold">북파크라운지</h4>
                            <h5 class="no-body-lg no-pd-sm--t">블루스퀘어에서 만나는 아늑한 복합 문화 공간</h5>
                            <p class="">
                                3,000여 권의 큐레이션 도서와 감각적인 카페 메뉴가 어우러진 특별한 공간입니다. <br>
                                편안하고 따뜻한 분위기 속에서
                                독서와 휴식을 동시에 즐길 수 있는 북파크 라운지는 일상의 작은 여유를 선사합니다.
                            </p>
                        </div>
                        <div class="info no-pd-lg--t">
                            <ul class="grid-col-3 no-gap-lg ">
                                <li class="--card">
                                    <div class="--card-txt">
                                        <h3>위치</h3>
                                        <div class="--card-info">
                                            <ul>
                                                <li>
                                                    <p><?= isUpdateActive() ? '우리은행홀' : '신한카드홀' ?> 3층</p>
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
                                        <h3>영업시간</h3>
                                        <div class="--card-info">
                                            <ul>
                                                <li>
                                                    <p>
                                                        화요일 - 일요일 <br>
                                                        11 : 30 - 21 : 00
                                                    </p>
                                                </li>

                                                <li>
                                                    <p>
                                                        <b class="no-clr-text-primary">
                                                            매주 월요일 휴무 <br>
                                                            주말 및 공휴일 라스트오더 19시
                                                        </b>
                                                    </p>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                    <div class="--card-icon">
                                        <i class="fa-regular fa-timer"></i>
                                    </div>
                                </li>
                                <li class="--card">
                                    <div class="--card-txt">
                                        <h3>문의</h3>
                                        <div class="--card-info">
                                            <ul>
                                                <li>
                                                    <p>0507 - 1312 - 0539</p>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                    <div class="--card-icon">
                                        <i class="fa-solid fa-phone"></i>
                                    </div>
                                </li>
                            </ul>
                        </div>
                        <div class="link no-pd-xl--t">
                            <div class="f ai-c jc-c">
                                <a href="https://map.naver.com/p/entry/place/1174718905?lng=127.0024193&lat=37.5409711&placePath=%2Fhome&searchType=place&c=15.00,0,0,0,dh"
                                    target="_blank" class="no-btn-arrow no-btn-arrow__fill--primary  ">
                                    자세히 보기
                                    <span>
                                        <i class="fa-regular fa-arrow-right" aria-hidden="true"></i>
                                        <i class="fa-regular fa-arrow-right" aria-hidden="true"></i>
                                    </span>
                                </a>
                            </div>
                        </div>
                    </div>


                </div>
            </li>
            <!-- 카페필로스 모달 -->
            <li class="modal-item " data-modal-id="cafe">
                <div>
                    <div class="img">
                        <div class="swiper no-modal-swiper">
                            <ul class="swiper-wrapper">
                                <?php for ($i = 1; $i < 5 ; $i++) :?>
                                <li class="swiper-slide">
                                    <figure>
                                        <img src="<?=IMG_PATH?>/sub/fnb_philos_img<?=$i?>.jpg" alt="">
                                    </figure>
                                </li>
                                <?php endfor; ?>
                            </ul>
                            <div class="swiper-pagination no-modal-swiper-pagination"></div>
                        </div>
                    </div>
                    <div class="txt">
                        <div class="title no-pd-lg--b">
                            <h4 class="no-heading-lg ">카페 필로스 & 베이커리</h4>
                            <h5 class="no-body-lg no-pd-sm--t">이색적인 원두와 갓 구운 베이커리가 어우러지는 특별한 공간</h5>
                            <p class="">
                                독특한 풍미의 원두로 완성된 카페 메뉴와 신선하게 구워낸 베이커리를 만날 수 있는 곳입니다. <br>
                                남산의 정취를 느끼며 공연과 함께 여유를 즐길 수 있는 테라스
                                카페로, 감각적인 맛과 분위기를 모두 경험해보세요.
                            </p>
                        </div>
                        <div class="info no-pd-lg--t">
                            <ul class="grid-col-3 no-gap-lg ">
                                <li class="--card">
                                    <div class="--card-txt">
                                        <h3>위치</h3>
                                        <div class="--card-info">
                                            <ul>
                                                <li>
                                                    <p><?= isUpdateActive() ? '우리은행홀' : '신한카드홀' ?> 2층</p>
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
                                        <h3>영업시간</h3>
                                        <div class="--card-info">
                                            <ul>
                                                <li>
                                                    <p>
                                                        화요일 - 일요일 <br>
                                                        12 : 00 - 20 : 00
                                                    </p>
                                                </li>

                                                <li>
                                                    <p>
                                                        <b class="no-clr-text-primary">매주 월요일 휴무</b>
                                                    </p>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                    <div class="--card-icon">
                                        <i class="fa-regular fa-timer"></i>
                                    </div>
                                </li>
                                <li class="--card">
                                    <div class="--card-txt">
                                        <h3>문의</h3>
                                        <div class="--card-info">
                                            <ul>
                                                <li>
                                                    <p>070 - 7724 - 0698</p>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                    <div class="--card-icon">
                                        <i class="fa-solid fa-phone"></i>
                                    </div>
                                </li>
                            </ul>
                        </div>
                        <div class="link no-pd-xl--t">
                            <div class="f ai-c jc-c">
                                <a href="https://map.naver.com/p/search/%EC%B9%B4%ED%8E%98%20%ED%95%84%EB%A1%9C%EC%8A%A4/place/960061966?placePath=?entry=pll&from=nx&fromNxList=true&searchType=place&c=15.00,0,0,0,dh"
                                    target="_blank" class="no-btn-arrow no-btn-arrow__fill--primary  ">
                                    자세히 보기
                                    <span>
                                        <i class="fa-regular fa-arrow-right" aria-hidden="true"></i>
                                        <i class="fa-regular fa-arrow-right" aria-hidden="true"></i>
                                    </span>
                                </a>
                            </div>
                        </div>
                    </div>


                </div>
            </li>
            <!-- 오페라글라스 모달 -->
            <li class="modal-item " data-modal-id="opera">
                <div class="no-pd-xl--t">
                    <!--
                    <div class="img">
                        <div class="swiper no-modal-swiper">
                            <ul class="swiper-wrapper">
                                <?php for ($i = 1; $i < 3 ; $i++) :?>
                                <li class="swiper-slide">
                                    <figure>
                                        <img src="<?=IMG_PATH?>/sub/oprea_glass_img_<?=$i?>.jpg" alt="">
                                    </figure>
                                </li>
                                <?php endfor; ?>
                            </ul>
                            <div class="swiper-pagination no-modal-swiper-pagination"></div>
                        </div>
                    </div>-->

                    <div class="txt">
                        <div class="--box-wrap">
                            <ul>
                                <li class="--box">
                                    <div class="--box-info ">
                                        <ul class="">
                                            <li class="">
                                                <div class="--box-info-title no-heading-md no-heading-md">
                                                    <h3>대여방법</h3>
                                                </div>
                                                <div class="--box-info-desc">
                                                    <ul class="f fd-c no-gap-sm">
                                                        <li>
                                                            <p class="no-body-md">
                                                                NOL티켓 사이트를 통한 사전 예약 후 현장 수령
                                                                시, 실물 티켓, 오페라글라스 예약 내역서 확인 및 예약자 신분증 보관 후
                                                                이용
                                                            </p>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </li>
                                            <li class="">
                                                <div class="--box-info-title no-heading-md ">
                                                    <h3>이용금액</h3>
                                                </div>
                                                <div class="--box-info-desc">
                                                    <ul class="f fd-c no-gap-sm">
                                                        <li>
                                                            <p class="no-body-md">
                                                                5,000원
                                                            </p>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </li>
                                            <li class="">
                                                <div class="--box-info-title no-heading-md">
                                                    <h3>수령위치</h3>
                                                </div>
                                                <div class="--box-info-desc">
                                                    <ul class="f fd-c no-gap-sm">
                                                        <li>
                                                            <p class="no-body-md">
                                                                <?= isUpdateActive() ? '우리은행홀' : '신한카드홀' ?> 객석 3층(1F)
                                                                오페라글라스 대여 부스
                                                            </p>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </li>
                                            <li class="">
                                                <div class="--box-info-title no-heading-md">
                                                    <h3>수령 가능시간</h3>
                                                </div>
                                                <div class="--box-info-desc">
                                                    <ul class="f fd-c no-gap-sm">
                                                        <li>
                                                            <p class="no-body-md">
                                                                공연 시작 1시간 전 ~ 공연 시작 10분 전
                                                            </p>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </li>
                                            <?php
											// 데이터베이스에서 예약 링크 데이터 가져오기
											try {
												$sql = "SELECT title, link FROM nb_opera WHERE state = 1"; // 활성화된 상태(state = 1)의 데이터만 가져오기
												$results = DB::query($sql);

												if (!$results) {
													$results = []; // 데이터가 없을 경우 빈 배열 설정
												} else {
													$results = array_reverse($results); // 배열 순서를 역순으로 변경
												}
											} catch (Exception $e) {
												echo "데이터를 가져오는 중 오류가 발생했습니다: " . blue_safe_error($e);
												$results = [];
											}
											?>
                                            <li class="">
                                                <div class="--box-info-title no-heading-md">
                                                    <h3>예약하기</h3>
                                                </div>
                                                <div class="--box-info-desc-link">
                                                    <ul class="">
                                                        <?php foreach ($results as $row): ?>
                                                        <li class="">
                                                            <a href="<?= htmlspecialchars($row['link']) ?>"
                                                                target="_blank"
                                                                class="no-btn-arrow no-btn-arrow__outline--white dif">
                                                                <?= htmlspecialchars($row['title']) ?>
                                                                <span>
                                                                    <i class="fa-regular fa-arrow-right"
                                                                        aria-hidden="true"></i>
                                                                    <i class="fa-regular fa-arrow-right"
                                                                        aria-hidden="true"></i>
                                                                </span>
                                                            </a>
                                                        </li>
                                                        <?php endforeach; ?>
                                                    </ul>
                                                </div>
                                            </li>
                                        </ul>
                                    </div>

                                    <div class="--box-notice">
                                        <div class="--box-info">
                                            <ul class="">
                                                <li class="">
                                                    <div class="--box-info-title no-heading-md">
                                                        <h3>주의사항</h3>
                                                    </div>
                                                    <div class="--box-info-desc --box-notice-desc">
                                                        <ul class="no-body-md">
                                                            <li>
                                                                <p>
                                                                    현장 대여 수량은 공연시작 2시간 전부터 오페라글라스 대여 부스에서
                                                                    확인하실 수
                                                                    있습니다.
                                                                </p>
                                                            </li>
                                                            <li>
                                                                <p>
                                                                    당일 공연 관람자에 한해 본 서비스를 이용할 수 있으며, <br>
                                                                    예약자 신분증 실물 및 증빙서류(실물 티켓 및 오페라글라스 예약 내역서) 미지참 시 수령
                                                                    불가합니다.
                                                                </p>
                                                            </li>
                                                            <li>
                                                                <p>
                                                                    사전예약은 공연 당일 3시간 전까지 가능합니다.
                                                                </p>
                                                            </li>
                                                            <li>
                                                                <p>
                                                                    파손 및 분실의 경우 고객 부담금이 발생합니다.
                                                                </p>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </li>
            <!-- 객석 모달 -->
            <li class="modal-item " data-modal-id="seat">
                <div>
                    <div class="img">
                        <div class="swiper no-modal-swiper">
                            <ul class="swiper-wrapper">
                                <?php for ($i = 1; $i < 4 ; $i++) :?>
                                <li class="swiper-slide">
                                    <figure>
                                        <img src="<?=IMG_PATH?>/sub/shinhan_card_hall_img_<?=$i?>.jpg" alt="">
                                    </figure>
                                </li>
                                <?php endfor; ?>
                            </ul>
                            <div class="swiper-pagination no-modal-swiper-pagination"></div>
                        </div>
                    </div>
                    <div class="txt">
                        <div class="title no-pd-lg--b">
                            <div class="logo-img">
                                <div class="--toggle-images">
                                    <img src="/resource/images/logo/<?= isUpdateActive() ? 'blue_square_wooribank_hall_logo_white.png' : 'blue_square_shinhan_hall_logo_white.png' ?>"
                                        class="--light-img" alt="">
                                    <img src="/resource/images/logo/<?= isUpdateActive() ? 'blue_square_wooribank_hall_logo_black.png' : 'blue_square_shinhan_hall_logo_black.png' ?>"
                                        class="--dark-img" alt="">
                                </div>
                            </div>
                            <h5 class="no-body-lg no-pd-sm--t"><?= isUpdateActive() ? '우리은행홀' : '신한카드홀' ?>은 국내 최대 규모의
                                뮤지컬 전용 대극장으로, 총 객석
                                3층(1,766석)으로 이루어져 있습니다.</h5>
                            <p class="">
                                객석 1층은 무대 끝 선과 객석 맨 뒷열의 거리가 약 27m로 어느 자리에서든 가깝게 무대를 바라볼 수 있으며, <br>
                                객석 2층은 1열 좌석에서 무대까지의 거리가 18.5m로 국내 최단 거리를 자랑합니다. <br>
                                또한 <?= isUpdateActive() ? '우리은행홀' : '신한카드홀' ?> 좌석은 교차 형식으로 배치되어 관객 여러분의 편안한 관람을 경험할 수
                                있도록 설계되어 있습니다. <br>
                                가장 편안하고 쾌적한 공연 관람이 되도록, 항상 배려하는 마음으로 서비스 연구에 매진하는 <br>
                                <?= isUpdateActive() ? '우리은행홀' : '신한카드홀' ?>에서 뮤지컬의 뜨거운 열기와 무한한 감동을 가져가시기 바랍니다.
                            </p>
                        </div>
                        <div class="info no-pd-lg--t">
                            <ul class="grid-col-3 no-gap-lg ">
                                <li class="--card">
                                    <div class="--card-txt">
                                        <h3>용도</h3>
                                        <div class="--card-info">
                                            <ul>
                                                <li>
                                                    <p>뮤지컬 전용 공연장</p>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </li>
                                <li class="--card">
                                    <div class="--card-txt">
                                        <h3>좌석</h3>
                                        <div class="--card-info">
                                            <ul>
                                                <li>
                                                    <p>
                                                        총 좌석수
                                                        <span class="no-clr-text-primary --fw-bold">1,766석</span>
                                                    </p>
                                                </li>
                                                <li>
                                                    <p>
                                                        1층
                                                        <span class="no-clr-text-primary --fw-bold">1,066석</span>
                                                    </p>
                                                </li>
                                                <li>
                                                    <p>
                                                        2층
                                                        <span class="no-clr-text-primary --fw-bold">430석</span>
                                                    </p>
                                                </li>
                                                <li>
                                                    <p>
                                                        3층
                                                        <span class="no-clr-text-primary --fw-bold">270석</span>
                                                    </p>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </li>
                                <li class="--card">
                                    <div class="--card-txt">
                                        <h3>부대시설</h3>
                                        <div class="--card-info">
                                            <ul>
                                                <li>
                                                    <p>
                                                        VIP 분장실, 의상실, 세탁실,<br>
                                                        그룹 분장실, 소품실 등
                                                    </p>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </li>
                            </ul>
                        </div>
                        <div class="link no-pd-xl--t">
                            <div class="f ai-c jc-c">
                                <a href="<?=$ROOT?>/pages/intro/fac.php"
                                    class="no-btn-arrow no-btn-arrow__fill--primary  ">
                                    자세히 보기
                                    <span>
                                        <i class="fa-regular fa-arrow-right" aria-hidden="true"></i>
                                        <i class="fa-regular fa-arrow-right" aria-hidden="true"></i>
                                    </span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </li>
            <!-- 스테이지 B 모달 -->
            <li class="modal-item " data-modal-id="stageB">
                <div>
                    <div class="img">
                        <div class="swiper no-modal-swiper">
                            <ul class="swiper-wrapper">
                                <?php for ($i = 1; $i < 4 ; $i++) :?>
                                <li class="swiper-slide">
                                    <figure>
                                        <img src="<?=IMG_PATH?>/sub/fnb_stage_b_img_<?=$i?>.jpg" alt="">
                                    </figure>
                                </li>
                                <?php endfor; ?>
                            </ul>
                            <div class="swiper-pagination no-modal-swiper-pagination"></div>
                        </div>
                    </div>
                    <div class="txt">
                        <div class="title no-pd-lg--b">
                            <h4 class="no-heading-lg ">스테이지B</h4>
                            <h5 class="no-body-lg no-pd-sm--t">외식과 문화가 공존하는 예술의 공간</h5>
                            <p class="no-body-base">
                                다양한 고품질 식재료로 완성된 트렌디한 메뉴를 선보이는 이탈리안 비스트로입니다.
                                천연 조미료와 산지에서 공수한 신선한 제철 재료를 사용하여 맛과 건강을 모두 충족시키는 브런치와 다이닝을 제공합니다.
                                미식의 예술과 문화적 풍요로움이 조화를 이루는 특별한 경험을 만나보세요.
                            </p>
                        </div>
                        <div class="info no-pd-lg--t">
                            <ul class="grid-col-3 no-gap-lg ">
                                <li class="--card">
                                    <div class="--card-txt">
                                        <h3>위치</h3>
                                        <div class="--card-info">
                                            <ul>
                                                <li>
                                                    <p><?= isUpdateActive() ? '우리은행홀' : '신한카드홀' ?> 1층(객석 3층)</p>
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
                                        <h3>영업시간</h3>
                                        <div class="--card-info">
                                            <ul>
                                                <li>
                                                    <p>
                                                        화요일 - 일요일 <br>
                                                        12 : 00 - 20 : 00
                                                    </p>
                                                </li>
                                                <li>
                                                    <p>
                                                        <b class="no-clr-text-primary">매주 월요일 휴무</b>
                                                    </p>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                    <div class="--card-icon">
                                        <i class="fa-regular fa-timer"></i>
                                    </div>
                                </li>
                                <li class="--card">
                                    <div class="--card-txt">
                                        <h3>문의</h3>
                                        <div class="--card-info">
                                            <ul>
                                                <li>
                                                    <p>02 - 6399 - 7545</p>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                    <div class="--card-icon">
                                        <i class="fa-solid fa-phone"></i>
                                    </div>
                                </li>
                            </ul>
                        </div>
                        <div class="link no-pd-xl--t">
                            <div class="f ai-c jc-c">
                                <a href="https://map.naver.com/p/entry/place/132236039?lng=127.0022708&lat=37.5408327&placePath=%2Fbooking%3Fentry%3Dplt&entry=plt&searchType=place&c=15.00,0,0,0,dh"
                                    target="_blank" class="no-btn-arrow no-btn-arrow__fill--primary  ">
                                    자세히 보기
                                    <span>
                                        <i class="fa-regular fa-arrow-right" aria-hidden="true"></i>
                                        <i class="fa-regular fa-arrow-right" aria-hidden="true"></i>
                                    </span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </li>
            <!-- 솔로스 키친 모달 -->
            <li class="modal-item " data-modal-id="solo">
                <div>
                    <div class="img">
                        <div class="swiper no-modal-swiper">
                            <ul class="swiper-wrapper">
                                <?php for ($i = 1; $i < 4 ; $i++) :?>
                                <li class="swiper-slide">
                                    <figure>
                                        <img src="<?=IMG_PATH?>/sub/fnb_solos_img_<?=$i?>.jpg" alt="">
                                    </figure>
                                </li>
                                <?php endfor; ?>
                            </ul>
                            <div class="swiper-pagination no-modal-swiper-pagination"></div>
                        </div>
                    </div>
                    <div class="txt">
                        <div class="title no-pd-lg--b">
                            <h4 class="no-heading-lg ">솔로스 키친</h4>
                            <h5 class="no-body-lg no-pd-sm--t">간편하면서도 풍성한 한 끼를 위한 특별한 공간</h5>
                            <p class="">
                                푸드트럭의 재밌는 컨셉으로 간편하고 맛있는 한 끼를 제공하는 한식 Fast Food! <br>
                                공연을 즐기는 싱글슈머들의 다이닝 공간입니다.
                            </p>
                        </div>
                        <div class="info no-pd-lg--t">
                            <ul class="grid-col-3 no-gap-lg ">
                                <li class="--card">
                                    <div class="--card-txt">
                                        <h3>위치</h3>
                                        <div class="--card-info">
                                            <ul>
                                                <li>
                                                    <p><?= isUpdateActive() ? '우리은행홀' : '신한카드홀' ?> 1층 / 객석 3층</p>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                    <div class="--card-icon">
                                        <i class="fa-solid fa-location-dot" aria-hidden="true"></i>
                                    </div>
                                </li>
                                <li class="--card">
                                    <div class="--card-txt">
                                        <h3>영업시간</h3>
                                        <div class="--card-info">
                                            <ul>
                                                <?php if (isUpdateActive()): ?>
                                                <li>
                                                    <p>
                                                        화요일 - 금요일<br>
                                                        12 : 00 - 20 : 00
                                                    </p>
                                                </li>
                                                <li>
                                                    <p>
                                                        토요일 – 일요일<br>
                                                        12 : 00 - 19 : 00
                                                    </p>
                                                </li>
                                                <?php else: ?>
                                                <li>
                                                    <p>
                                                        화요일 - 일요일<br>
                                                        12 : 00 - 20 : 00
                                                    </p>
                                                </li>
                                                <?php endif; ?>
                                                <li>
                                                    <p>
                                                        <b class="no-clr-text-primary">매주 월요일 휴무</b>
                                                    </p>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                    <div class="--card-icon">
                                        <i class="fa-regular fa-timer" aria-hidden="true"></i>
                                    </div>
                                </li>
                                <li class="--card">
                                    <div class="--card-txt">
                                        <h3>문의</h3>
                                        <div class="--card-info">
                                            <ul>
                                                <li>
                                                    <p>02 - 6399 - 7544</p>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                    <div class="--card-icon">
                                        <i class="fa-solid fa-phone" aria-hidden="true"></i>
                                    </div>
                                </li>
                            </ul>
                        </div>
                        <div class="link no-pd-xl--t">
                            <div class="f ai-c jc-c">
                                <a href="https://map.naver.com/p/entry/place/267265992?lng=127.0029308&lat=37.5402956&placePath=%2Fhome&entry=plt&searchType=place&c=15.00,0,0,0,dh"
                                    target="_blank" class="no-btn-arrow no-btn-arrow__fill--primary  ">
                                    자세히 보기
                                    <span>
                                        <i class="fa-regular fa-arrow-right" aria-hidden="true"></i>
                                        <i class="fa-regular fa-arrow-right" aria-hidden="true"></i>
                                    </span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </li>
            <!-- 물품보관소 모달 -->
            <li class="modal-item " data-modal-id="storage">
                <div class="no-pd-xl--t">
                    <!--
                    <div class="img">
                        <div class="swiper no-modal-swiper">
                            <ul class="swiper-wrapper">
                                <li class="swiper-slide">
                                    <figure>
                                        <img src="<?=IMG_PATH?>/sub/storage_img_1.jpg" alt="">
                                    </figure>
                                </li>
                            </ul>
                            <div class="swiper-pagination no-modal-swiper-pagination"></div>
                        </div>
                    </div>-->

                    <div class="txt">
                        <div class="title no-pd-lg--b">
                            <div class="--box-wrap">
                                <ul>
                                    <li class="--box">
                                        <div class="--box-info ">
                                            <ul class="">
                                                <li class="">
                                                    <div class="--box-info-title no-heading-md">
                                                        <h3>위치</h3>
                                                    </div>
                                                    <div class="--box-info-desc">
                                                        <ul class="f fd-c no-gap-xs">
                                                            <li>
                                                                <ul class="">
                                                                    <li>
                                                                        <span class="no-body-md">
                                                                            <?= isUpdateActive() ? '우리은행홀' : '신한카드홀' ?>
                                                                        </span>
                                                                    </li>

                                                                    <li>
                                                                        <p class="no-body-base">
                                                                            객석 1층(B1층) 물품보관소 및 외부 공간
                                                                        </p>
                                                                    </li>
                                                                </ul>
                                                            </li>
                                                            <li>
                                                                <ul class="">
                                                                    <li>
                                                                        <span class="no-body-md">
                                                                            <?= isUpdateActive() ? ($hallName === 'SOL트래블홀' ? '우리WON뱅킹홀' : $hallName) : $hallName ?>
                                                                        </span>
                                                                    </li>

                                                                    <li>
                                                                        <p class="no-body-base">
                                                                            객석 1,2층(B1,B3층) 로비
                                                                        </p>
                                                                    </li>
                                                                </ul>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                </li>
                                            </ul>
                                        </div>
                                        <div class="">
                                            <div class="--box-notice">
                                                <div class="--box-info">
                                                    <ul class="">
                                                        <li class="new">
                                                            <div class="--box-info-title no-heading-md">
                                                                <h3>운영시간</h3>
                                                            </div>
                                                            <div class="--box-info-desc --box-notice-desc">
                                                                <ul class="no-body-md">
                                                                    <li>
                                                                        <p class="">
                                                                            로비 개방 시 ~ 공연 종료 후 20분(공연 별 예매페이지 참고)
                                                                        </p>
                                                                    </li>
                                                                </ul>
                                                            </div>
                                                        </li>
                                                        <li class="new">
                                                            <div class="--box-info-title no-heading-md">
                                                                <h3>보관방법</h3>
                                                            </div>
                                                            <div class="--box-info-desc --box-notice-desc">
                                                                <ul class="no-body-md">
                                                                    <li>
                                                                        <p class="">
                                                                            공연관람객 2시간 무료 (추가 요금 : 1시간당 소형 1,000원 / 중형
                                                                            2,000원 / 대형 3,000원 신용카드 결제)
                                                                        </p>
                                                                    </li>
                                                                </ul>
                                                            </div>
                                                        </li>
                                                        <li class="">
                                                            <div class="--box-info-title no-heading-md">
                                                                <h3>주의사항</h3>
                                                            </div>
                                                            <div class="--box-info-desc --box-notice-desc">
                                                                <ul class="no-body-md">
                                                                    <li>
                                                                        <p class="">
                                                                            공연 관람객에 한하여 2시간 무료 이용이 가능합니다.
                                                                        </p>
                                                                    </li>
                                                                    <li>
                                                                        <p>
                                                                            장기 방치(5일 이상) 된 물품은 업체 측에서 수거하여 별도 보관합니다.(30일
                                                                            경과 시 임의 처분)
                                                                        </p>
                                                                    </li>
                                                                    <li>
                                                                        <p>
                                                                            고가의 물품(현금, 유가 증권, 귀금속 등), 귀중품은 개인 소지해
                                                                            주시고, 파손 및 분실 시 책임지지 않습니다.
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
                </div>
            </li>
        </ul>
    </div>
    <!-- 마스터카드 모달 -->
    <div class="no-modal-master">
        <ul class="modal-wrap ">
            <!-- 객석 모달 -->
            <li class="modal-item " data-modal-id="seat">
                <div>
                    <div class="img">
                        <div class="swiper no-modal-swiper">
                            <ul class="swiper-wrapper">
                                <?php 
                                $maxImage = isUpdateActive() ? 4 : 3;
                                for ($i = 1; $i < $maxImage ; $i++) :?>
                                <li class="swiper-slide">
                                    <figure>
                                        <img src="<?=IMG_PATH?>/sub/master_card_hall_img_<?=$i?>.jpg" alt="">
                                    </figure>
                                </li>
                                <?php endfor; ?>
                            </ul>
                            <div class="swiper-pagination no-modal-swiper-pagination"></div>
                        </div>
                    </div>
                    <div class="txt">
                        <div class="title no-pd-lg--b">
                            <div class="logo-img">
                                <div class="--toggle-images">
                                    <img src="/resource/images/logo/<?= isUpdateActive() ? 'blue_square_woori_won_banking_hall_logo_white.png' : $logoPrefix . '_white.png' ?>" class="--light-img"
                                        alt="">
                                    <img src="/resource/images/logo/<?= isUpdateActive() ? 'blue_square_woori_won_banking_hall_logo_black.png' : $logoPrefix . '_black.png' ?>" class="--dark-img"
                                        alt="">
                                </div>
                            </div>
                            <h5 class="no-body-lg no-pd-sm--t">
                                <?= isUpdateActive() ? '우리WON뱅킹홀' : $hallName ?>은 총 1,379석(스탠딩 <?= isUpdateActive() ? '2,400' : '2,800' ?>석) 규모의 진정한 라이브를 <br>
                                만들고 즐길 수 있도록 태어난 공연장입니다.</h5>
                            <p class="no-body-base">
                                세계 최고의 D&B 음향 시스템과 좌석 변형이 가능한 객석 시스템을 갖추고 있습니다.
                                라이브 콘서트와 쇼케이스, 기업행사, 팬미팅 등 다양한 장르의 콘텐츠를 공연할 수 있는
                                최적의 시설을 갖추고 있으며
                                무대와 관객이 하나되는 강렬한 경험을 만끽하실 수 있습니다.
                            </p>
                        </div>
                        <div class="info no-pd-lg--t">
                            <ul class="grid-col-3 no-gap-lg ">
                                <li class="--card">
                                    <div class="--card-txt">
                                        <h3>용도</h3>
                                        <div class="--card-info">
                                            <ul>
                                                <li>
                                                    <p>콘서트, 쇼케이스, 팬미팅, 컨퍼런스, 기업행사 등</p>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </li>
                                <li class="--card">
                                    <div class="--card-txt">
                                        <h3>좌석</h3>
                                        <div class="--card-info">
                                            <ul>
                                                <li>
                                                    <p>
                                                        그라운드형
                                                        <span class="no-clr-text-primary">1,379석</span>
                                                    </p>
                                                </li>
                                                <li>
                                                    <p>
                                                        스탠딩형
                                                        <span class="no-clr-text-primary">약 <?= isUpdateActive() ? '2,400' : '2,800' ?>석</span>
                                                    </p>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </li>
                                <li class="--card">
                                    <div class="--card-txt">
                                        <h3>부대시설</h3>
                                        <div class="--card-info">
                                            <ul>
                                                <li>
                                                    <p>
                                                        VIP분장실, 그룹 분장실, 스탭 대기실, 스탭식당 등
                                                    </p>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </li>
                            </ul>
                        </div>
                        <div class="link no-pd-xl--t">
                            <div class="f ai-c jc-c">
                                <a href="<?=$ROOT?>/pages/intro/fac.php?tab=master-card"
                                    class="no-btn-arrow no-btn-arrow__fill--primary  ">
                                    자세히 보기
                                    <span>
                                        <i class="fa-regular fa-arrow-right" aria-hidden="true"></i>
                                        <i class="fa-regular fa-arrow-right" aria-hidden="true"></i>
                                    </span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </li>
            <!-- 물품보관소 모달 -->
            <li class="modal-item " data-modal-id="storage">
                <div class="no-pd-xl--t">
                    <!---
                    <div class="img">
                        <div class="swiper no-modal-swiper">
                            <ul class="swiper-wrapper">
                                <li class="swiper-slide">
                                    <figure>
                                        <img src="<?=IMG_PATH?>/sub/storage_img_1.jpg" alt="">
                                    </figure>
                                </li>
                            </ul>
                            <div class="swiper-pagination no-modal-swiper-pagination"></div>
                        </div>
                    </div>-->

                    <div class="txt">
                        <div class="title no-pd-lg--b">
                            <div class="--box-wrap">
                                <ul>
                                    <li class="--box">
                                        <div class="--box-info ">
                                            <ul class="">
                                                <li class="">
                                                    <div class="--box-info-title no-heading-md">
                                                        <h3>위치</h3>
                                                    </div>
                                                    <div class="--box-info-desc">
                                                        <ul class="f fd-c no-gap-xs">
                                                            <li>
                                                                <ul class="">
                                                                    <li>
                                                                        <span class="no-body-md">
                                                                            <?= isUpdateActive() ? '우리은행홀' : '신한카드홀' ?>
                                                                        </span>
                                                                    </li>

                                                                    <li>
                                                                        <p class="no-body-base">
                                                                            객석 1층(B1층) 물품보관소 및 외부 공간
                                                                        </p>
                                                                    </li>
                                                                </ul>
                                                            </li>
                                                            <li>
                                                                <ul class="">
                                                                    <li>
                                                                        <span class="no-body-md">
                                                                            <?= isUpdateActive() ? ($hallName === 'SOL트래블홀' ? '우리WON뱅킹홀' : $hallName) : $hallName ?>
                                                                        </span>
                                                                    </li>

                                                                    <li>
                                                                        <p class="no-body-base">
                                                                            객석 1,2층(B1,B3층) 로비
                                                                        </p>
                                                                    </li>
                                                                </ul>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                </li>


                                            </ul>
                                        </div>
                                        <div class="">
                                            <div class="--box-notice">
                                                <div class="--box-info">
                                                    <ul class="">
                                                        <li class="new">
                                                            <div class="--box-info-title no-heading-md">
                                                                <h3>운영시간</h3>
                                                            </div>
                                                            <div class="--box-info-desc --box-notice-desc">
                                                                <ul class="no-body-md">
                                                                    <li>
                                                                        <p class="">
                                                                            로비 개방 시 ~ 공연 종료 후 20분(공연 별 예매페이지 참고)
                                                                        </p>
                                                                    </li>
                                                                </ul>
                                                            </div>
                                                        </li>
                                                        <li class="new">
                                                            <div class="--box-info-title no-heading-md">
                                                                <h3>보관방법</h3>
                                                            </div>
                                                            <div class="--box-info-desc --box-notice-desc">
                                                                <ul class="no-body-md">
                                                                    <li>
                                                                        <p class="">
                                                                            공연관람객 2시간 무료 (추가 요금 : 1시간당 소형 1,000원 / 중형
                                                                            2,000원 / 대형 3,000원 신용카드 결제)
                                                                        </p>
                                                                    </li>
                                                                </ul>
                                                            </div>
                                                        </li>
                                                        <li class="">
                                                            <div class="--box-info-title no-heading-md">
                                                                <h3>주의사항</h3>
                                                            </div>
                                                            <div class="--box-info-desc --box-notice-desc">
                                                                <ul class="no-body-md">
                                                                    <li>
                                                                        <p class="">
                                                                            공연 관람객에 한하여 2시간 무료 이용이 가능합니다.
                                                                        </p>
                                                                    </li>
                                                                    <li>
                                                                        <p>
                                                                            장기 방치(5일 이상) 된 물품은 업체 측에서 수거하여 별도 보관합니다.(30일
                                                                            경과 시 임의 처분)
                                                                        </p>
                                                                    </li>
                                                                    <li>
                                                                        <p>
                                                                            고가의 물품(현금, 유가 증권, 귀금속 등), 귀중품은 개인 소지해
                                                                            주시고, 파손 및 분실 시 책임지지 않습니다.
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
                </div>
            </li>
        </ul>
    </div>
    <!-- 개인정보처리방침 모달 -->
    <div class="no-modal-privacy">
        <ul class="modal-wrap">
            <!-- 개인정보처리방침 모달 -->
            <li class="modal-item" data-modal-id="privacy">
                <div>
                    <div class="txt">
                        <div class="">
                            <h4 class="no-heading-lg --fw-bold">[NOL 씨어터] 대관신청 개인정보 수집 및 이용 안내</h4>

                            <p class="no-body-lg no-pd-xl--t">
                                [NOL 씨어터] 대관신청 개인정보 수집 및 이용 안내NOL 씨어터는 서비스 제공을 위하여 아래와 같이 회원의 개인정보를 수집 및 활용합니다.
                            </p>

                            <div class="no-pd-sm--t">
                                <div class="--table ">
                                    <table class="tg">
                                        <thead>
                                            <tr>
                                                <th class="head">
                                                    개인정보 수집 및 이용 목적
                                                </th>
                                                <th class="head">
                                                    수집하는 개인정보 항목
                                                </th>
                                                <th class="head">
                                                    개인정보 보유 및 이용 기간
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody class="">
                                            <tr>
                                                <td class="body">
                                                    <p class="no-body-sm">
                                                        대관 계약 접수, 대관 계약 진행
                                                    </p>
                                                </td>
                                                <td class="body">
                                                    <p class="no-body-sm">
                                                        성명, 이메일, 연락처
                                                    </p>
                                                </td>
                                                <td class="body">
                                                    <p class="no-body-sm">
                                                        대관 미선정작 <br>
                                                        대관 심의 종료 후 30일 內 파기
                                                    </p>
                                                    <p class="no-body-sm no-pd-md--t">
                                                        대관 선정작 <br>
														대관 종료 후 회계 정산 종료 시점으로 부터 30일 內 파기. <br>
														단, 관계 법령에 따라 보관이 필요한 경우 해당 보유기간 까지 보유
                                                    </p>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </li>
        </ul>
    </div>
    <article class="modal-close-btn-container">
        <button class="modal-close-btn ">
            <i class="fa-solid fa-xmark"></i>
        </button>
    </article>
</div>



</body>

</html>
