<?php
include_once $_SERVER['DOCUMENT_ROOT'].'/inc/lib/base.class.php';
$parkingHallName = isUpdateActive() ? '우리은행홀' : '신한카드홀';
$isAprilFirstUpdateActive = isUpdateActive('2026-04-01');
?>

<!-- dev -->

<?php include_once $STATIC_ROOT.'/inc/layouts/head.php'; ?>

<!-- css, js  -->
<?php 
    include_once $STATIC_ROOT.'/inc/layouts/header.php';
    include_once $STATIC_ROOT.'/inc/shared/sub.visual.php';
    include_once $STATIC_ROOT.'/inc/shared/sub.nav.php';
?>


<!-- contents -->
<main class="no-sub">
    <section class="no-sub-parking no-pd-2xl--y">
        <div class="no-container-xl">

            <div class="f ai-c no-gap-sm" <?=$aos_title?>>
                <i class="fa-solid fa-circle-exclamation no-clr-primary-main no-heading-md"></i>
                <h2 class="no-heading-lg">Notice</h2>
            </div>

            <div class="no-pd-lg--t" <?=$aos_content?>>
                <div class="--box-wrap">
                    <ul>
                        <li class="--box --info-box">
                            <ul class="--box-list-style-disc no-gap-sm">
                                <li>
                                    <h2 class="no-body-lg --fw-semibold">
                                        블루스퀘어 주차장은 상시 만차되어 주차에 어려움이 있습니다. <br>
										지하철 6호선 한강진역과 연결되어 있어 접근성이 용이하오니 대중교통을 적극 이용해주시기 바랍니다.
                                    </h3>
                                </li>
                                <li>
                                    <h2 class="no-body-lg --fw-semibold">
                                        주차장 현장상황에 따라 입차가 제한될 수 있으며, 주차 및 교통난으로 인한 예매 취소 및 변경은 불가합니다.

                                    </h3>
                                </li>
                            </ul>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="no-pd-2xl--t">
                <h3 class="no-heading-lg" <?=$aos_title?>>
                    주차요금
                </h3>
                <div class="no-pd-lg--t" <?=$aos_content?>>
                    <div class="--table">
                        <?php if ($isAprilFirstUpdateActive): ?>
                        <table class="tg">
                            <thead>
                                <tr>
                                    <th class="head">
                                        구분
                                    </th>
                                    <th class="head">
                                        평일
                                    </th>
                                    <th class="head">
                                        주말/공휴일
                                    </th>
                                    <th class="head">
                                        비고
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <th class="sub-head">
                                        일반요금 <br>
                                        (Visitor free)
                                    </th>
                                    <th class="body">
                                        10분당 1,000원 <br>
                                        1,000won for 10Min
                                    </th>
                                    <th class="body">
                                        10분당 1,500원 <br>
                                        1,500won for 10Min
                                    </th>
                                    <th class="body">
                                        20분 이내 출차시 무료 <br>
                                        (20분 초과시 입차시간 기준으로 부과) <br>
                                        일 최대요금 제한 없음
                                    </th>
                                </tr>
                                <tr>
                                    <th class="sub-head">
                                        공연관객 <br>
                                        (Audience)
                                    </th>
                                    <th class="body">
                                        4시간이내 6,000원 <br>
                                        (시간 초과시 일반요금 적용) <br>
                                        6,000won for 4Hours
                                    </th>
                                    <th class="body">
                                        4시간이내 7,500원 <br>
                                        (시간 초과시 일반요금 적용) <br>
                                        7,500won for 4Hours
                                    </th>
                                    <th class="body">
                                        공연티켓 내 QR코드로 할인 적용 가능 <br>
                                        (공연 종료 후 30분 내 출차 시 유인정산 가능)
                                    </th>
                                </tr>
                                <tr>
                                    <th class="sub-head">
                                        입점업체 이용객
                                    </th>
                                    <th class="body">
                                        10분당 1,000원 <br>
                                        1,000won for 10Min
                                    </th>
                                    <th class="body">
                                        10분당 1,500원 <br>
                                        1,500won for 10Min
                                    </th>
                                    <th class="body">
                                        해당업체 문의
                                    </th>
                                </tr>
                                <tr>
                                    <th class="head" colspan="4">
                                        24:00 ~ 07:00 출차 불가
                                    </th>
                                </tr>
                                <tr>
                                    <th class="sub-head">
                                        복지할인 <br>
                                        (장애인&amp;국가유공자) <br>
                                        The disabled, <br>
                                        Men of national merit
                                    </th>
                                    <th class="body">
                                        일반요금의 50% 할인 <br>
                                        50% off the visitor fees
                                    </th>
                                    <th class="body">
                                        일반요금의 50% 할인 <br>
                                        50% off the visitor fees
                                    </th>
                                    <th class="body">
                                        복지카드 및 유공자카드 소지자 탑승 <br>
                                        및 운전 차량에 한하여 적용 <br>
                                        Certificate Holder Only
                                    </th>
                                </tr>
                            </tbody>
                        </table>
<?php else: ?>
                        <table class="tg">
                            <thead>
                                <tr>
                                    <th class="head">
                                        구분
                                    </th>
                                    <th class="head">
                                        요금
                                    </th>
                                    <th class="head">
                                        비고
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <th class="sub-head">
                                        일반요금 <br>
                                        (Visitor fee)
                                    </th>
                                    <th class="body">
                                        10분당 1,000원 <br>
                                        1,000won for 10min
                                    </th>
                                    <th class="body">
                                        20분 이내 출차시 무료 <br>
                                        (20분 초과시 입차시간 기준으로 부과) <br>
                                        <b>일 최대요금 제한 없음</b>
                                    </th>
                                </tr>
                                <tr>
                                    <th class="sub-head">
                                        공연관객 <br>
                                        (Audience)
                                    </th>
                                    <th class="body">
                                        4시간 이내 5,000원 <br>
                                        (시간초과시 일반요금 적용) <br>
                                        5,000won for 4hours
                                    </th>
                                    <th class="body">
                                        공연티켓 내 QR코드로 할인 적용 가능 <br>
                                        (공연 종료 후 30분 내 출차 시 유인정산 가능)
                                    </th>
                                </tr>
                                <tr>
                                    <th class="sub-head">
                                        입점업체 이용객 <br>
                                        (F&amp;B Visitor fee)
                                    </th>
                                    <th class="body">
                                        10분당 1,000원 <br>
                                        1,000won for 10min
                                    </th>
                                    <th class="body">
                                        해당업체 문의
                                    </th>
                                </tr>
                                <tr>
                                    <th class="head" colspan="3">
                                        24:00 ~ 07:00 출차불가
                                    </th>
                                </tr>
                                <tr>
                                    <th class="sub-head">
                                        복지할인 <br>
                                        (The disabled, Men of national Merit)
                                    </th>
                                    <th class="body">
                                        일반요금의 50% 할인 <br>
                                        50% off the visitor fees
                                    </th>
                                    <th class="body">
                                        해당 증빙자료 제시 필요
                                    </th>
                                </tr>
                            </tbody>
                        </table>
<?php endif; ?>

                    </div>
                </div>
            </div>


            <div class="no-pd-2xl--t" >
                <h3 class="no-heading-lg" <?=$aos_title?>>
                    주차 사전정산
                </h3>
                <div class="no-pd-lg--t" <?=$aos_content?>>
                    <div class="--box">
                        <ul class="--box-list-style-disc no-gap-md">
                            <li>
                                <h3>
                                    <i class="fa-solid fa-check" aria-hidden="true"></i>
                                    공연 관람객의 경우 공연 티켓의 QR로 공연장 내 설치된 무인정산기에서 할인 적용 가능 <br>
                                    (편의시설 이용 시 각 해당 업체에 문의해주시기 바랍니다)
                                </h3>
                                <div>
                                    <p>
                                        사전 정산은 입차 20분 이후부터 가능합니다.
                                    </p>
                                    <p>
                                        티켓에 주차할인 QR코드가 없는 경우, <?= htmlspecialchars($parkingHallName, ENT_QUOTES, 'UTF-8') ?> 매표소 또는 물품보관소에서 QR코드 수령하여 정산하시기 바랍니다.
                                    </p>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="no-pd-2xl--t ">
                <h3 class="no-heading-lg" <?=$aos_title?>>
                    유의사항
                </h3>
                <div class="no-pd-lg--t" <?=$aos_content?>>
                    <div class="--box">
                        <ul class="--box-list-style-disc no-gap-md">
                            <li>
                                <h3>
                                    <i class="fa-solid fa-check" aria-hidden="true"></i>
                                    블루스퀘어 주차장은 상시 만차되니 대중교통을 적극 이용하시기 바랍니다.
                                </h3>
                            </li>
                            <li>
                                <h3>
                                    <i class="fa-solid fa-check" aria-hidden="true"></i>
                                    주차 및 교통난으로 인해 당일 관람이 불가하거나 관람을 포기한 경우 예매 취소 및 변경이 불가합니다.
                                </h3>
                            </li>
                            <li>
                                <h3>
                                    <i class="fa-solid fa-check" aria-hidden="true"></i>
                                    블루스퀘어 주차장 만차에 대비하여 인근주차장 정보를 사전에 확인 후 방문해 주시기 바라며, <br>
                                    주차요금은 해당주차장의 요금기준으로 적용됩니다. (관람객
                                    할인 적용 불가)
                                </h3>
                            </li>
                            <li>
                                <h3>
                                    <i class="fa-solid fa-check" aria-hidden="true"></i>
                                    주차장 내 도난 및 제반사고에 관하여 일체의 책임을 지지 않습니다.
                                </h3>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>




            <div class="no-pd-2xl--t ">
                <h3 class="no-heading-lg" <?=$aos_title?>>
                    주차장 운영시간
                </h3>
                <div class="no-pd-lg--t" <?=$aos_content?>> 
                    <div class="--box">
                        <ul class="--box-list-style-disc no-gap-md">
                            <li>
                                <h3>
                                    <i class="fa-solid fa-check" aria-hidden="true"></i>
                                    운영시간 : 07:00~24:00 (24:00 ~ 07:00 출차 불가)

                                </h3>
                                <div>
                                    <p>
                                        공연 종료 후 30분 이내 출차 부탁드립니다.

                                    </p>
                                    <p>
                                        1일 2회 공연인 경우 낮공연을 관람하신 분들은 다음 공연관람 손님을 위해 바로 출차 부탁드립니다.

                                    </p>
                                    <p>
                                        1일 2회 공연인 경우 입출차 동시 진행 시 사고 방지를 위해 낮 공연 종료 10분전 - 종료 30분 이후 까지 입차가 제한됩니다. / 공연
                                        일정에 따른 변동 있음
                                    </p>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="no-pd-2xl--t " >
                <h3 class="no-heading-lg" <?=$aos_title?>>
                    전기차 충전기 이용안내
                </h3>
                <div class="no-pd-lg--t" <?=$aos_content?>>
                    <div class="--box-wrap">
                        <ul>
                            <li class="--box">
                                <div class="--box-info ">
                                    <ul class="">
                                        <li class="">
                                            <div class="--box-info-title no-heading-md no-heading-md">
                                                <h3>충전기 위치</h3>
                                            </div>
                                            <div class="--box-info-desc">
                                                <ul class="f fd-c no-gap-sm">
                                                    <li>
                                                        <p class="no-body-md">
                                                            블루스퀘어 지하3층(B3) 진입로 왼쪽 구역 / 최대 4대 동시 충전 가능
                                                        </p>
                                                    </li>
                                                </ul>
                                            </div>
                                        </li>
                                        <li class="">
                                            <div class="--box-info-title no-heading-md ">
                                                <h3>충전기 종류</h3>
                                            </div>
                                            <div class="--box-info-desc">
                                                <ul class="f fd-c no-gap-sm">
                                                    <li>
                                                        <p class="no-body-md">
                                                            완속 충전기
                                                        </p>
                                                    </li>
                                                </ul>
                                            </div>
                                        </li>
                                        <li class="">
                                            <div class="--box-info-title no-heading-md">
                                                <h3>충전기 결제</h3>
                                            </div>
                                            <div class="--box-info-desc">
                                                <ul class="f fd-c no-gap-sm">
                                                    <li>
                                                        <p class="no-body-md">
                                                            신용카드 / 충전기 내 별도 결제 충전 요금 외 주차 요금 별도
                                                        </p>
                                                    </li>
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
                                                    <h3>환경친화적 자동차 충전소 주차 과태료 안내</h3>
                                                </div>
                                                <ul class="">
                                                    <li>
                                                        <p class="no-body-md">
                                                            아래의 경우 20만원 이하의 과태료가 부과되오니 주의 부탁드립니다.
                                                        </p>
                                                        <div class="no-pd-sm--t">
                                                            <ul class="--list-style-num">
                                                                <li>
                                                                    환경친화적 자동차 이외의 차량 주차 시
                                                                </li>
                                                                <li>
                                                                    환경친화적 자동차의 충전 시작 후 14시간 경과 후 지속 주차 시
                                                                </li>
                                                                <li>
                                                                    충전구역 표시 훼손 및 시설 고의 훼손 시
                                                                </li>
                                                            </ul>
                                                        </div>
                                                    </li>
                                                </ul>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>





            <div class="no-pd-2xl--t ">


                <div class="no-sub-tab-contents__info">
                    <div class="no-sub-tab-contents__title" <?=$aos_title?>>
                        <div class="no-sub-fac-logo">
                            <h2 class="no-heading-lg" >인근 주차장 안내</h2>
                        </div>
                        <div class="f ai-c ju-c">
                            <a href="https://www.sisul.or.kr/open_content/parking/guidance/useable.jsp" target="_blank"
                                class="no-btn-arrow no-btn-arrow__fill--primary ">
                                확인하러 가기
                                <span>
                                    <i class="fa-regular fa-arrow-right" aria-hidden="true"></i>
                                    <i class="fa-regular fa-arrow-right" aria-hidden="true"></i>
                                </span>
                            </a>
                        </div>
                    </div>
                    <div class=" no-body-lg " <?=$aos_content?>>
                        <ul class="grid-col-3 no-gap-lg ">
                            <li class="--card">
                                <div class="--card-txt">
                                    <h3>영업시간</h3>
                                    <div class="--card-info">
                                        <ul>
                                            <li>
                                                <p>24시간 운영</p>
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
                                    <h3>주소</h3>
                                    <div class="--card-info">
                                        <ul>
                                            <li>
                                                <p>
                                                    한강진역 공영주차장 서울시 용산구 이태원로 272
                                                </p>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="--card-icon">
									<i class="fa-regular fa-location-dot"></i>
                                    
                                </div>
                            </li>
                            <li class="--card">
                                <div class="--card-txt">
                                    <h3>요금</h3>
                                    <div class="--card-info">
                                        <ul>
                                            <li>
                                                <p>5분 당 400원</p>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="--card-icon">
                                   <i class="fa-regular fa-money-bill"></i>
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
