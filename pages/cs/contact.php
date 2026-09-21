<?php include_once $_SERVER['DOCUMENT_ROOT'].'/inc/lib/base.class.php'; ?>

<!-- dev -->

<?php include_once $STATIC_ROOT.'/inc/layouts/head.php'; ?>

<!-- css, js  -->
<?php 
    include_once $STATIC_ROOT.'/inc/layouts/header.php';
    include_once $STATIC_ROOT.'/inc/shared/sub.visual.php';
    include_once $STATIC_ROOT.'/inc/shared/sub.nav.php';
?>

<script charset="UTF-8" class="daum_roughmap_loader_script"
    src="https://ssl.daumcdn.net/dmaps/map_js_init/roughmapLoader.js"></script>



<!-- contents -->
<main class="no-sub">
    <section class="no-sub-contact no-pd-2xl--y">
        <div class="no-container-xl">
            <h2 class="no-heading-lg " <?=$aos_title?>>서울특별시 용산구 이태원로 294 블루스퀘어</h2>

            <div class="no-pd-lg--t">
                <div class="cnt">
                    <div id="map" data-lenis-prevent <?=$aos_content?>>
                        <div id="daumRoughmapContainer1735813312499"
                            class="root_daum_roughmap root_daum_roughmap_landing"></div>
                    </div>
                    <ul class="no-pd-lg--t">
                        <li class="no-pd-lg--y ">
                            <ul>
                                <li class="grid-col-4-8 ai-c no-gap-lg" <?=$aos_content?>>
                                    <h3 class="no-heading-md">지도</h3>
                                    <ul class="f ai-c no-gap-sm f-w">
                                        <li>
                                            <a href="https://kko.kakao.com/1CpMV_T2tQ" target="_blank"
                                                class="no-btn-arrow no-btn-arrow__outline--white  ">
                                                카카오 지도로 확인
                                                <span>
                                                    <i class="fa-regular fa-arrow-right" aria-hidden="true"></i>
                                                    <i class="fa-regular fa-arrow-right" aria-hidden="true"></i>
                                                </span>
                                            </a>
                                        </li>
                                        <li>
                                            <a href="https://naver.me/x9BNag5b" target="_blank"
                                                class="no-btn-arrow no-btn-arrow__outline--white  ">
                                                네이버 지도로 확인
                                                <span>
                                                    <i class="fa-regular fa-arrow-right" aria-hidden="true"></i>
                                                    <i class="fa-regular fa-arrow-right" aria-hidden="true"></i>
                                                </span>
                                            </a>
                                        </li>
                                        <li>
                                            <a href="https://maps.app.goo.gl/H7Gx1ryaa78SS8kAA"
                                                class="no-btn-arrow no-btn-arrow__outline--white  " target="_blank"> 구글
                                                지도로 확인
                                                <span>
                                                    <i class="fa-regular fa-arrow-right" aria-hidden="true"></i>
                                                    <i class="fa-regular fa-arrow-right" aria-hidden="true"></i>
                                                </span>
                                            </a>
                                        </li>
                                    </ul>
                                </li>
                            </ul>
                        </li>
                        <li class="no-pd-lg--y" <?=$aos_content?>>
                            <ul>
                                <li class="grid-col-4-8 ai-c no-gap-lg">
                                    <h3 class="no-heading-md --fw-semibold">지하철 이용 시</h3>
                                    <div class="no-body-base">
                                        <div class="--no-public-tranportation">
                                            <span class="--transporation-color --metro-line-6"></span>
                                            <p>
                                                6호선 한강진역 2번 출구와 3번 출구 사이 지하철역 연결통로 이용
                                            </p>
                                        </div>
                                    </div>
                                </li>
                            </ul>
                        </li>

                        <li class="no-pd-lg--y " <?=$aos_content?>>
                            <ul>
                                <li class="grid-col-4-8 ai-c no-gap-lg">
                                    <h3 class="no-heading-md">버스 이용 시</h3>
                                    <div class="">
                                        <ul class="f fd-c no-gap-lg">
                                            <li class="f fd-c no-gap-xs">
                                                <h5 class="no-body-md --fw-semibold">한강진역 하차 - 1번 출구 방면</h5>
                                                <div class="no-body-base">
                                                    <div class="f fd-c no-gap-xs">
                                                        <div class="--no-public-tranportation">
                                                            <span class="--transporation-color --bus-city"></span>
                                                            <p>
                                                                110A, 405, 400
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </li>
                                            <li class="f fd-c no-gap-xs">
                                                <h5 class="no-body-md --fw-semibold">한강진역 하차 - 2번 출구 방면</h5>
                                                <div class="no-body-base">
                                                    <div class="f fd-c no-gap-xs">
                                                        <div class="--no-public-tranportation">
                                                            <span class="--transporation-color --bus-city"></span>
                                                            <p>
                                                                110B, 405, 400
                                                            </p>
                                                        </div>
                                                        <div class="--no-public-tranportation">
                                                            <span class="--transporation-color --bus-metropolitan"></span>
                                                            <p>
                                                                03
                                                            </p>
                                                        </div>
                                                        <div class="--no-public-tranportation">
                                                            <span
                                                                class="--transporation-color --bus-airport "></span>
                                                            <p>
                                                                6030
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </li>
                                            <li class="f fd-c no-gap-xs">
                                                <h5 class="no-body-md --fw-semibold">서울시 중부 기술교육원 / 블루스퀘어 하차</h5>
                                                <div class="no-body-base">
                                                    <div class="f fd-c no-gap-xs">
                                                        <div class="--no-public-tranportation">
                                                            <span class="--transporation-color --bus-city"></span>
                                                            <p>
                                                                110B, 142, 144, 402, 407, 420, N13
                                                            </p>
                                                        </div>
                                                        <div class="--no-public-tranportation">
                                                            <span class="--transporation-color --bus-seoul"></span>
                                                            <p>
                                                                3011, 6211
                                                            </p>
                                                        </div>
                                                        <div class="--no-public-tranportation">
                                                            <span
                                                                class="--transporation-color --bus-airport "></span>
                                                            <p>
                                                                6030
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </li>
                                            <li>
                                                <div class="--info">
                                                    <div class="f no-gap-xs ">
                                                        <p>
                                                            강북방면에서 남산1호터널(고가도로)를 대중 교통편으로 이용하시는 분들은
                                                            순천향대학병원(구 단국대학교) 정류장에서 하차하셔서 한강진역 방향으로 10분정도 도보로 이용하셔야
                                                            합니다.
                                                        </p>
                                                    </div>
                                                </div>
                                            </li>
                                        </ul>

                                    </div>
                                </li>
                            </ul>
                        </li>
                        <li class="no-pd-lg--y" <?=$aos_content?>>
                            <ul>
                                <li class="grid-col-4-8 ai-c no-gap-lg">
                                    <h3 class="no-heading-md">자가용 이용 시</h3>
                                    <div class="">
                                        <ul class="f fd-c no-gap-lg">
                                            <li class="f fd-c no-gap-xs">
                                                <h5 class="no-body-md --fw-semibold">강남 방면에서 오시는 경우</h5>
                                                <div class="no-body-base">
                                                    <div class="f f-w no-gap-xs ai-c">
                                                        <p>한남대교 진입하여 직진</p>
                                                        <i class="fa-regular fa-arrow-right-long"></i>
                                                        <p>북한남 삼거리에서 좌회전 </p>
                                                        <i class="fa-regular fa-arrow-right-long"></i>
                                                        <p>첫번째 신호등에서 좌회전하여 한강진역 2,3번 출구 사이 우측 지하주차장 입구로 진입 </p>
                                                    </div>
                                                </div>
                                            </li>
                                            <li class="f fd-c no-gap-xs">
                                                <h5 class="no-body-md --fw-semibold">장충동 방면에서 오실 경우</h5>
                                                <div class="no-body-base">
                                                    <div class="f f-w no-gap-xs ai-c">
                                                        <p>한남대로 진입하여 직진</p>
                                                        <i class="fa-regular fa-arrow-right-long"></i>
                                                        <p>북한남 삼거리 지나 이태원로 진입</p>
                                                        <i class="fa-regular fa-arrow-right-long"></i>
                                                        <p>좌회전하여 한강진역 2,3번 출구 사이 우측 지하주차장 입구로 진입</p>
                                                    </div>
                                                </div>
                                            </li>
                                            <li class="f fd-c no-gap-xs">
                                                <h5 class="no-body-md --fw-semibold">이태원동 방면에서 오실 경우</h5>
                                                <div class="no-body-base">
                                                    <div class="f f-w no-gap-xs ai-c">
                                                        <p>이태원로 진입하여 북한남 삼거리 방향으로 직진</p>
                                                        <i class="fa-regular fa-arrow-right-long"></i>
                                                        <p>한강진역 2,3번 출구 사이 우측 지하주차장 입구로 진입</p>
                                                    </div>
                                                </div>
                                            </li>

                                        </ul>

                                    </div>
                                </li>
                            </ul>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

</main>


<!-- 3. 실행 스크립트 -->
<script charset="UTF-8">
new daum.roughmap.Lander({
    "timestamp": "1735813312499",
    "key": "2mohe",
}).render();
</script>


<?php
    include_once $STATIC_ROOT.'/inc/layouts/footer.php';
    ?>