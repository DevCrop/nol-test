



    <section class="no-pd-2xl--y">
        <div class="no-board-view-works">
            <div class="no-container-xl">
                <div class="cnt">
                    <ul>
                        <!---left--->
                        <li class="left">
                            <div>
                                <img src="/uploads/board/<?=$data['thumb_image']?>" alt="<?=$data['title']?>">
                                <div class="no-board-view-btn">
                                  	<?php if (!empty($data['direct_url'])): ?>
										<a href="<?= htmlspecialchars($data['direct_url']) ?>" target="_blank" class="no-btn no-btn__fill--primary --radius-thin">티켓 예매하기</a>
									<?php endif; ?>
                                  	<?php if (!empty($data['extra9'])): ?>
										<a href="<?= htmlspecialchars($data['extra9']) ?>" class="no-btn no-btn__outline--white --radius-thin"
										   aria-describedby="tooltip" id="button">
											<i class="fa-duotone fa-solid fa-glasses"></i>
											오페라글라스 예약하기
										</a>
									<?php endif; ?>
                                    <div id="tooltip" role="tooltip">
                                        <div class="f fd-c no-gap-md">
                                            <div class="img">
                                                 <img src="<?=IMG_PATH?>/icon/popper_glass.png" alt="">
                                            </div>
                                            <div class="txt f fd-c no-gap-sm">
                                                <h4 class=" no-clr-text-black no-heading-md ">
                                                    오페라글라스란?
                                                </h4>
                                                <p class="no-body-md no-clr-text-black">
                                                    공연장에서 관람을 할 때 사용하는 도구로, <br>
                                                    배율과 구경이 큰 것이 특징입니다. <br>
                                                    좌석에 따라 적합한 오페라글라스를 <br>
                                                    선택하여 사용할 수 있습니다
                                                </p>
                                            </div>
                                        </div>
                                        <div id="arrow" data-popper-arrow></div>
                                    </div>
                                </div>
                        </li>
                        <!--right--->
                        <li class="right">
                            <div class="no-board-view-info">
                                <h2 class="no-heading-lg">공연 정보</h2>
                                <div class="no-pd-lg--t">
                                    <ul class="no-board-view-info__wrap ">
                                        <li>
                                            <h4 class="no-heading-md">장르</h4>
                                            <ul class="">
                                                <li>
                                                    <p class="no-body-md">
                                                        뮤지컬
                                                    </p>
                                                </li>
                                            </ul>
                                        </li>
                                        <li>
                                            <h4 class="no-heading-md">일정</h4>
                                            <ul class="">
                                                <li>
                                                    <p class="no-body-md">
                                                        2024.11.02 - 2024.12.31
                                                    </p>
                                                </li>
                                            </ul>
                                        </li>
                                        <li>
                                            <h4 class="no-heading-md">시간</h4>
                                            <ul class=" time">
                                                <li>
                                                    <p class="no-body-md">
                                                        금 7 : 30PM
                                                    </p>
                                                </li>
                                                <li>
                                                    <p class="no-body-md">
                                                        토 7 : 30PM
                                                    </p>
                                                </li>
                                            </ul>
                                        </li>
                                        <li>
                                            <h4 class="no-heading-md">장소</h4>
                                            <ul class="">
                                                <li>
                                                    <p class="no-body-md">
                                                        블루스퀘어 신한카드홀
                                                    </p>
                                                </li>

                                            </ul>
                                        </li>
                                        <li>
                                            <h4 class="no-heading-md">러닝타임</h4>
                                            <ul class="">
                                                <li>
                                                    <p class="no-body-md">
                                                        75분 / 인터미션 없음
                                                    </p>
                                                </li>

                                            </ul>
                                        </li>
                                        <li>
                                            <h4 class="no-heading-md">티켓가격</h4>
                                            <ul class=" price">
                                                <li>
                                                    <p class="no-body-md">
                                                        <span>VIP석</span>110,000원
                                                    </p>
                                                </li>
                                                <li>
                                                    <p class="no-body-md">
                                                        <span>R석 </span> 99,000원
                                                    </p>
                                                </li>

                                                <li>
                                                    <p class="no-body-md">
                                                        <span>전좌석 </span> 99,000원
                                                    </p>
                                                </li>

                                            </ul>
                                        </li>
                                        <li>
                                            <h4 class="no-heading-md">관람연령</h4>
                                            <ul class="">
                                                <li>
                                                    <p class="no-body-md">
                                                        초등학생 이상 관림가
                                                        8세 이상 관람가, 2017년 포함 이전 출생자
                                                    </p>
                                                </li>
                                            </ul>
                                        </li>
                                        <li>
                                            <h4 class="no-heading-md">문의</h4>
                                            <ul class="">
                                                <li>
                                                    <p class="no-body-md">
                                                        문의
                                                    </p>
                                                </li>
                                            </ul>
                                        </li>
                                        <li>
                                            <h4 class="no-heading-md">비고</h4>
                                            <ul class="">
                                                <li>
                                                    <p class="no-body-md">
                                                        비고
                                                    </p>
                                                </li>
                                            </ul>
                                        </li>
                                    </ul>
                                </div>
                            </div>
							<?php
								// 이미지 태그에서 style 속성을 제거하는 함수
								function removeImageStyles($html) {
									// 정규식을 사용해 <img> 태그의 style 속성을 제거
									return preg_replace('/(<img[^>]+)style=["\'][^"\']*["\']/', '$1', $html);
								}

								// $data['contents'] 처리
								$contents = htmlspecialchars_decode($data['contents']); // HTML 디코딩
								$contentsWithoutStyles = removeImageStyles($contents); // style 속성 제거
							?>

                            <div class="no-board-view-img">
                                <h2 class="no-heading-lg">공연 포스터</h2>
                                <div class="no-pd-lg--t">
                                    <div class="more-info-wrap">
                                        <?= \Security\HtmlSanitizer::clean((string) $contentsWithoutStyles) ?>
                                        <div class="more-info-wrap-bg"></div>
                                    </div>
                                    <div class="more-info-wrap-button">
                                        <button class="no-btn" type="button">
                                            <span class="button-text">전체 포스터 보기</span>
                                            <i class="fa-regular fa-chevron-down"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </section>
