<?php section('content') ?>

<div class=" no-section-md">
    <?= include_view('components.sub-visual', ['title' => '오시는 길']) ?>

    <section class="no-sub-txt-ui">
        <div class="no-container-xl">
            <div class="no-sub-txt-ui-inner">
                <div class="no-sub-procedure-layout">
                    <div class="no-sub-procedure-title" <?= AOS_TITLE ?>>
                        <h2 class="f-heading-1 --bold">Information</h2>
                    </div>
                    <div class="no-sub-right-content" <?= AOS_DEFAULT ?>>
                        <div class="no-sub-directions-map">
                            <iframe
                                src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3161.819028845209!2d127.00183557715911!3d37.58287892318867!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x357ca32cef91194d%3A0xb2b840e99a044a68!2z7ISc7Jq47Yq567OE7IucIOyiheuhnOq1rCDrj5nsiK3quLggMTAw!5e0!3m2!1sko!2skr!4v1767864642734!5m2!1sko!2skr"
                                style="border:0;" allowfullscreen="" loading="lazy"
                                referrerpolicy="no-referrer-when-downgrade">
                            </iframe>
                        </div>
                        <!-- 정보 섹션들 -->
                        <div class="no-sub-directions-info">
                            <!-- 주소 -->
                            <div class="no-sub-directions-info-item" <?= AOS_DEFAULT ?>>
                                <div class="no-sub-directions-info-label">
                                    <span class="f-body-1 --bold">주소</span>
                                </div>
                                <div class="no-sub-directions-info-value">
                                    <span class="f-body-1 --medium">서울특별시 종로구 동숭길 100</span>
                                </div>
                            </div>

                            <!-- 지도 -->
                            <div class="no-sub-directions-info-item" <?= AOS_DEFAULT ?>>
                                <div class="no-sub-directions-info-label">
                                    <span class="f-body-1 --bold">지도</span>
                                </div>
                                <div class="no-sub-directions-info-value">
                                    <div class="no-sub-directions-map-buttons">
                                        <a href="https://kko.to/jK4THMAf8i" target="_blank" rel="noopener noreferrer" class="no-button-arrow">
                                            <span class="f-body-2 --regular">카카오 지도</span>
                                            <i class="fa-regular fa-arrow-up-right"></i>
                                        </a>
                                        <a href="https://naver.me/xI17HaIi" target="_blank" rel="noopener noreferrer" class="no-button-arrow">
                                            <span class="f-body-2 --regular">네이버 지도</span>
                                            <i class="fa-regular fa-arrow-up-right"></i>
                                        </a>
                                        <a href="https://maps.app.goo.gl/zWFnUiuVLywggQzr8" target="_blank" rel="noopener noreferrer" class="no-button-arrow">
                                            <span class="f-body-2 --regular">구글 지도</span>
                                            <i class="fa-regular fa-arrow-up-right"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <!-- 지하철 -->
                            <div class="no-sub-directions-info-item" <?= AOS_DEFAULT ?>>
                                <div class="no-sub-directions-info-label">
                                    <span class="f-body-1 --bold">지하철 이용 시</span>
                                </div>
                                <div class="no-sub-directions-info-value">
                                    <div class="no-sub-directions-transport">
                                        <div class="no-sub-directions-transport-item">
                                            <span class="no-sub-directions-bullet --subway"></span>
                                            <span class="f-body-1 --medium">4호선 혜화역 1번 출구에서 도보 약 4분 소요</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- 버스 -->
                            <div class="no-sub-directions-info-item" <?= AOS_DEFAULT ?>>
                                <div class="no-sub-directions-info-label">
                                    <span class="f-body-1 --bold">버스 이용 시</span>
                                </div>
                                <div class="no-sub-directions-info-value">
                                    <div class="no-sub-directions-transport">
                                        <div class="no-sub-directions-transport-item">
                                            <span class="f-body-1 --medium">혜화역2번출구.마로니에공원(01220) 도보 약 5분 소요</span>
                                        </div>
                                        <div class="no-sub-directions-bus-list">
                                            <div class="no-sub-directions-bus-group">
                                                <div class="no-sub-directions-bus-item">
                                                    <span class="no-sub-directions-bullet --bus-main"></span>
                                                    <span class="f-body-2 --regular">간선</span>
                                                    <span class="f-body-2 --regular">100, 102, 104, 107, 109, 140, 143,
                                                        150, 160, 162, 273, 301, 710</span>
                                                </div>
                                            </div>
                                            <div class="no-sub-directions-bus-group">
                                                <div class="no-sub-directions-bus-item">
                                                    <span class="no-sub-directions-bullet --bus-branch"></span>
                                                    <span class="f-body-2 --regular">지선</span>
                                                    <span class="f-body-2 --regular">2112, 8101</span>
                                                </div>
                                            </div>
                                            <div class="no-sub-directions-bus-group">
                                                <div class="no-sub-directions-bus-item">
                                                    <span class="no-sub-directions-bullet --bus-village"></span>
                                                    <span class="f-body-2 --regular">마을</span>
                                                    <span class="f-body-2 --regular">종로07, 종로08</span>
                                                </div>
                                            </div>
                                            <div class="no-sub-directions-bus-group">
                                                <div class="no-sub-directions-bus-item">
                                                    <span class="no-sub-directions-bullet --bus-express"></span>
                                                    <span class="f-body-2 --regular">직행</span>
                                                    <span class="f-body-2 --regular">1101, 1102, 7101</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="no-sub-procedure-layout">
                    <div class="no-sub-procedure-title">
                        <h2 class="f-heading-1 --bold">유의사항</h2>
                    </div>
                    <div class="no-sub-procedure-content-wrapper">



                        <!-- Notice -->
                        <div class="no-sub-apply-notice">
                            <div class="no-sub-apply-notice-title">
                                <i class="fa-regular fa-circle-exclamation f-heading-5 "></i>
                                <h2 class="f-heading-5 --bold">NOL 씨어터 대학로는 주차가 불가합니다.
                                </h2>
                            </div>
                            <div class="no-sub-apply-notice-content  --regular">
                                <p>
										※ NOL 씨어터 대학로는 주차가 불가합니다.
                                        공연장 주변 교통이 혼잡하오니 가급적 대중교통을 이용해주시기 바라며, <br>
                                    <b>
                                        불가피하게 차량을 이용하실 경우 인근 주차장 정보를 사전 확인 후 방문하시기 바랍니다. <br>
										주차 및 교통난으로 관람을 포기하신 경우, 예매 취소, 변경, 환불은 불가합니다. <br>
                                    </b>
                                    휠체어 이용 관객께서는 극장 B1층 출입구 앞 주차구역(1대)에 주차하실 수 있습니다.
                                </p>
                            </div>
                        </div>

						<!--
                        <div class="no-sub-ui-panel-block">
                            <div>
                                <h2 class="f-heading-4 --bold ">한국방송통신대학교 주차장
                                </h2>
                            </div>
                            <div class="--info-box">
                                <ul>
                                    <li>
                                        <span>
                                            영업시간
                                        </span>
                                        <p>
                                            <span>
                                                연중무휴 24시간 운영
                                            </span>
                                        </p>
                                    </li>
                                    <li>
                                        <span>
                                            주소
                                        </span>
                                        <p>
                                            <span>
                                                서울 종로구 대학로 86

                                            </span>
                                        </p>
                                    </li>
                                    <li>
                                        <span>
                                            요금
                                        </span>
                                        <p>
                                            <span>
                                                5분 당 400원, 추가 5분 당400원
                                            </span>
                                        </p>
                                    </li>
                                </ul>
                            </div>
                        </div>
                        <div class="no-sub-ui-panel-block">
                            <div>
                                <h2 class="f-heading-4 --bold ">홍익대학교 대학로캠퍼스 주차장
                                </h2>
                            </div>
                            <div class="--info-box">
                                <ul>
                                    <li>
                                        <span>
                                            영업시간
                                        </span>
                                        <p>
                                            <span>
                                                연중무휴 24시간 운영
                                            </span>
                                        </p>
                                    </li>
                                    <li>
                                        <span>
                                            주소
                                        </span>
                                        <p>
                                            <span>
                                                서울 종로구 대학로 57
                                            </span>
                                        </p>
                                    </li>
                                    <li>
                                        <span>
                                            요금
                                        </span>
                                        <p>
                                            <span>
                                                기본 30분 2,000원, 추가 10분 당 1,000원
                                            </span>
                                        </p>
                                    </li>
                                </ul>
                            </div>
                            <div class="--tar">
                                <span class="--warning ">
                                    ※ 상기 주차장의 주차요금은 기관 사정에 따라 변경될 수 있습니다.
                                </span>
                            </div>
                        </div>-->

                    </div>
                </div>



            </div>
        </div>
    </section>
</div>

<?php end_section() ?>

<?php section('portal') ?>
<?= include_view('components.popup'); ?>
<?php end_section() ?>