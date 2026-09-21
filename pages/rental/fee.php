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
    <section class="no-sub-fee no-pd-2xl--y ">
        <!--tab--->
        <div class="no-sub-tab" <?=$aos_title?>>
            <div class="no-container-xl">
                <div class="no-sub-tab-slider">
                    <ul class="swiper-wrapper">
                        <li class="swiper-slide">
                            <button type="button" class=" no-btn__fill active no-btn" data-id="shinhan-card">
                                <?= isUpdateActive() ? '우리은행홀' : '신한카드홀' ?>
                            </button>
                        </li>
                        <li class="swiper-slide">
                            <button type="button" class="  no-btn__fill no-btn" data-id="master-card">
                                <?= isUpdateActive() ? ($hallName === 'SOL트래블홀' ? '우리WON뱅킹홀' : $hallName) : $hallName ?>
                            </button>
                        </li>
                        <li class="swiper-slide">
                            <button type="button" class="  no-btn__fill no-btn" data-id="nemo">
                                NEMO
                            </button>
                        </li>
                        <li class="swiper-slide">
                            <button type="button" class="  no-btn__fill no-btn" data-id="practice">
                                연습실
                            </button>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="no-container-xl">

            <div class="no-pd-xl--t ">
                <div class=" no-sub-tab-contents">
                    <ul <?=$aos_content?>>
                        <!----content-1--->
                        <li class="" id="shinhan-card">
                            <div class="--table">
                                <table class="tg">
                                    <thead>
                                        <tr>
                                            <th class="head">
                                                공연대관
                                            </th>
                                            <th class="head">
                                                준비대관
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td class="body">
                                                <p>
                                                    회당 10,800,000원
                                                </p>
                                            </td>
                                            <td class="body">
                                                <p>
                                                    구간별 8,800,000원
                                                </p>
                                            </td>

                                        </tr>
                                    </tbody>
                                </table>

                            </div>
                            <div class="no-pd-sm--t">
                                <div class="--table-info">
                                    <i class="fa-solid fa-circle-exclamation"></i>
                                    <?= isUpdateActive() ? 'VAT별도 / 기본구간: 09:00~22:00' : 'VAT 별도' ?>
                                </div>
                            </div>

                            <div class="no-pd-2xl--t">
                                <h3 class="no-heading-lg">
                                    공연대관 안내사항
                                </h3>
                                <div class="no-pd-md--t">
                                    <div class="--box">
                                        <ul class="--box-list-style-disc ">
                                            <li>
                                                <h3>
                                                    <i class="fa-solid fa-check"></i>
                                                    주 1일 OFF DAY에는 대관료를 부과하지 않으며, 1주 1회 OFF 기준임
                                                </h3>
                                                <div>
                                                    <p>
                                                        추가 OFF일에는 공연대관료로 부과
                                                    </p>
                                                </div>
                                            </li>
                                            <li>
                                                <h3>
                                                    <i class="fa-solid fa-check"></i>
                                                    1일 1회 공연대관 시간

                                                </h3>
                                                <div>
                                                    <p>
                                                        공연 시작 시간 3시간 30분 전부터 7시간 / 점심, 저녁시간 1시간 제외

                                                    </p>
                                                    <p>
                                                        1회 공연 대관시간은 150분을 넘지 못하며, 1일 총 대관시간은 12시간을 초과할 수 없음
                                                    </p>
                                                </div>
                                            </li>
                                            <li>
                                                <h3>
                                                    <i class="fa-solid fa-check"></i>
                                                    기본 대관료에 포함되는 내용
                                                </h3>
                                                <div>
                                                    <p>
                                                        공연 대관 시간 냉,난방료 / 공연 한시간 전부터 종료시까지


                                                    </p>
                                                    <p>
                                                        1명의 하우스 매니저, 15명의 하우스 어셔 인력

                                                    </p>
                                                    <p>
                                                        무대 기계시설, 기본음향, 조명시설 / 음향, 조명 사용비용 별도

                                                    </p>
                                                    <p>
                                                        준비대관, 공연대관시 사용되는 전기, 수도세 일체
                                                    </p>
                                                    <p>
                                                        대관기간내에 사용하는 모든 분장실, 대기실, 휴게실 및 세탁실
                                                    </p>
                                                </div>
                                            </li>
                                            <li>
                                                <h3>
                                                    <i class="fa-solid fa-check"></i>
                                                    시연회 및 프레스콜 등의 추가 행사 진행 시 시간당 별도 비용 부과
                                                </h3>
                                            </li>

                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div class="no-pd-2xl--t">
                                <h3 class="no-heading-lg">
                                    준비대관 안내사항
                                </h3>
                                <div class="no-pd-md--t">
                                    <div class="--box">
                                        <ul class="--box-list-style-disc ">
                                            <li>
                                                <h3>
                                                    <i class="fa-solid fa-check"></i>
                                                    냉난방 및 온수 사용료 별도
                                                </h3>

                                            </li>
                                            <li>
                                                <h3>
                                                    <i class="fa-solid fa-check"></i>
                                                    기본구간 준비대관


                                                </h3>
                                                <div>
                                                    <p>
                                                        09:00 ~ 18:00 (8시간) * 점심시간 1시간은 대관가능시간에서 제외

                                                    </p>

                                                </div>
                                            </li>
                                            <li>
                                                <h3>
                                                    <i class="fa-solid fa-check"></i>
                                                    심야구간 기본대관

                                                </h3>
                                                <div>
                                                    <p>
                                                        19:00 ~ 22:00 (3시간)
                                                    </p>

                                                </div>
                                            </li>
                                            <li>
                                                <h3>
                                                    <i class="fa-solid fa-check"></i>
                                                    철야구간

                                                </h3>
                                                <div>
                                                    <p> 22시 이후부터 익일 9시까지 / 시간당 2,000,000원</p>
                                                </div>
                                            </li>
                                            <li>
                                                <h3>
                                                    <i class="fa-solid fa-check"></i>
                                                    준비대관일은 최대 12일을 넘지 못하며 12일이 넘는 경우 1일 공연 대관료로 계산하여 추가함
                                                </h3>

                                            </li>
                                            <li>
                                                <h3>
                                                    <i class="fa-solid fa-check"></i>
                                                    준비대관 기간에도 1주 1일 OFF가 있어야 하며 비용을 부과하지 않음
                                                </h3>


                                            </li>

                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <div class="no-pd-2xl--t">
                                <h3 class="no-heading-lg">
                                    주의사항
                                </h3>
                                <div class="no-pd-md--t">
                                    <div class="--box">
                                        <?php if (isUpdateActive()): ?>
                                        <ul class="--box-list-style-disc ">
                                            <li>
                                                <h3><i class="fa-solid fa-check"></i>브레이크타임에는 대관 진행이 제한됩니다.</h3>
                                                <div>
                                                    <p>브레이크타임: 12:00~13:00, 18:00~19:00 / 시간 조정 및 추가 대관 협의 가능</p>
                                                </div>
                                            </li>
                                            <li>
                                                <h3><i class="fa-solid fa-check"></i>대관료 완납 후 홍보 및 티켓 판매가 가능합니다.</h3>
                                            </li>
                                            <li>
                                                <h3><i class="fa-solid fa-check"></i>대관 신청 전, 대관규약(다운로드링크)을 확인바랍니다.</h3>
                                                <div>
                                                    <p>
                                                        <a href="<?=$ROOT?>/pages/board/board.list.php?board_no=10"
                                                            class="dib --link-line">대관규약 다운로드</a>
                                                    </p>
                                                </div>
                                            </li>
                                        </ul>
                                        <?php else: ?>
                                        <ul class="--box-list-style-disc ">
                                            <li>
                                                <h3> <i class="fa-solid fa-check"></i>
                                                    위 금액의 공연대관료 및 준비대관료에는 장비대여료, 부대시설사용료는 포함되어 있지 않음
                                                </h3>
                                                <div>
                                                    <p> 장비대여료 및 부대시설사용료는 블루스퀘어 <a
                                                            href="<?=$ROOT?>/pages/board/board.list.php?board_no=10"
                                                            class="dib --link-line">대관규약</a>에 의거 대관계약
                                                        시 추가산정</p>
                                                </div>
                                            </li>
                                        </ul>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>


                        </li>
                        <!----content 2----->
                        <li class="" id="master-card">
                            <h3 class="no-heading-lg">
                                공연 대관료
                            </h3>
                            <div class="no-pd-md--t">
                                <div class="--table">
                                    <table class="tg">
                                        <thead>
                                            <tr>
                                                <th class="head">
                                                    구분
                                                </th>
                                                <?php if (isUpdateActive()): ?>
                                                <th class="head">
                                                    대관일
                                                </th>
                                                <th class="head">
                                                    대관료 1일당
                                                </th>
                                                <?php else: ?>
                                                <th class="head">
                                                    공연기간
                                                </th>
                                                <th class="head">
                                                    회당 대관료 <br>
                                                    (VAT 별도)
                                                </th>
                                                <?php endif; ?>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (isUpdateActive()): ?>
                                            <tr>
                                                <td class="body" rowspan="2">
                                                    <p>
                                                        콘서트
                                                    </p>
                                                </td>
                                                <td class="body">
                                                    주중 (월~목)
                                                </td>
                                                <td class="body"> 6,800,000원</td>
                                            </tr>
                                            <tr>
                                                <td class="body">
                                                    주말 (금~일/공휴일)
                                                </td>
                                                <td class="body"> 8,000,000원</td>
                                            </tr>

                                            <tr>
                                                <td class="body" rowspan="2">
                                                    <p>
                                                        방송 / 행사
                                                    </p>
                                                </td>
                                                <td class="body">
                                                    주중 (월~목)
                                                </td>
                                                <td class="body"> 7,700,000원</td>
                                            </tr>
                                            <tr>

                                                <td class="body">
                                                    주말 (금~일/공휴일)
                                                </td>
                                                <td class="body"> 9,200,000원</td>
                                            </tr>

                                            <tr>
                                                <td class="body" rowspan="3">
                                                    <p>
                                                        쇼케이스
                                                    </p>
                                                </td>
                                                <td class="body">
                                                    주중 (월~수)
                                                </td>
                                                <td class="body"> 7,400,000원</td>
                                            </tr>
                                            <tr>
                                                <td class="body">
                                                    주중 (목)
                                                </td>
                                                <td class="body"> 7,700,000원</td>
                                            </tr>
                                            <tr>

                                                <td class="body">
                                                    주말 (금~일/공휴일)
                                                </td>
                                                <td class="body"> 9,200,000원</td>
                                            </tr>
                                            <?php else: ?>
                                            <tr>
                                                <td class="body" rowspan="2">
                                                    <p>
                                                        콘서트
                                                    </p>
                                                </td>
                                                <td class="body">
                                                    주중 (월~목)
                                                </td>
                                                <td class="body"> 6,500,000원</td>
                                            </tr>
                                            <tr>
                                                <td class="body">
                                                    주말 (금~일/공휴일)
                                                </td>
                                                <td class="body"> 7,700,000원</td>
                                            </tr>

                                            <tr>
                                                <td class="body" rowspan="2">
                                                    <p>
                                                        방송 / 행사
                                                    </p>
                                                </td>
                                                <td class="body">
                                                    주중 (월~목)
                                                </td>
                                                <td class="body"> 7,400,000원</td>
                                            </tr>
                                            <tr>

                                                <td class="body">
                                                    주말 (금~일/공휴일)
                                                </td>
                                                <td class="body"> 8,900,000원</td>
                                            </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="no-pd-sm--t">
                                <div class="--table-info">
                                    <i class="fa-solid fa-circle-exclamation"></i>
                                    <?= isUpdateActive() ? 'vat별도 / 기본구간 : 09 : 00 ~ 23 : 00' : 'VAT 별도' ?>
                                </div>
                            </div>
                            <div class="no-pd-2xl--t">
                                <h3 class="no-heading-lg">
                                    준비 대관료
                                </h3>
                                <div class="no-pd-md--t">
                                    <div class="--table">
                                        <table class="tg">
                                            <thead>
                                                <?php if (isUpdateActive()): ?>
                                                <tr>
                                                    <th class="head">
                                                        구분
                                                    </th>
                                                    <th class="head">
                                                        대관일
                                                    </th>
                                                    <th class="head">
                                                        대관료(1일당)
                                                    </th>
                                                </tr>
                                                <?php else: ?>
                                                <tr>
                                                    <th class="head">
                                                        구분
                                                    </th>
                                                    <th class="head">
                                                        공연기간
                                                    </th>
                                                    <th class="head">
                                                        기본 구간<br>
                                                        (09 : 00 ~ 22 : 00)
                                                    </th>
                                                    <th class="head">
                                                        철야 구간<br>
                                                        (시간 당 / VAT 별도)
                                                    </th>
                                                </tr>
                                                <?php endif; ?>
                                            </thead>
                                            <tbody>
                                                <?php if (isUpdateActive()): ?>
                                                <tr>
                                                    <td class="body" rowspan="2">
                                                        <p>
                                                            콘서트
                                                        </p>
                                                    </td>
                                                    <td class="body">
                                                        주중<br>
                                                        (월~목)
                                                    </td>
                                                    <td class="body"> 7,800,000원</td>
                                                </tr>
                                                <tr>
                                                    <td class="body">
                                                        주말<br>
                                                        (금~일/공휴일)
                                                    </td>
                                                    <td class="body"> 9,200,000원</td>
                                                </tr>

                                                <tr>
                                                    <td class="body" rowspan="2">
                                                        <p>
                                                            방송 / 행사
                                                        </p>
                                                    </td>
                                                    <td class="body">
                                                        주중<br>
                                                        (월~목)
                                                    </td>
                                                    <td class="body"> 8,000,000원</td>
                                                </tr>
                                                <tr>
                                                    <td class="body">
                                                        주말<br>
                                                        (금~일/공휴일)
                                                    </td>
                                                    <td class="body"> 9,500,000원</td>
                                                </tr>

                                                <tr>
                                                    <td class="body" rowspan="3">
                                                        <p>
                                                            쇼케이스
                                                        </p>
                                                    </td>
                                                    <td class="body">
                                                        주중<br>
                                                        (월~수)
                                                    </td>
                                                    <td class="body"> 7,700,000원</td>
                                                </tr>
                                                <tr>
                                                    <td class="body">
                                                        주중<br>
                                                        (목)
                                                    </td>
                                                    <td class="body"> 8,000,000원</td>
                                                </tr>
                                                <tr>
                                                    <td class="body">
                                                        주말<br>
                                                        (금~일/공휴일)
                                                    </td>
                                                    <td class="body"> 9,500,000원</td>
                                                </tr>
                                                <?php else: ?>
                                                <tr>
                                                    <td class="body" rowspan="2">
                                                        <p>
                                                            콘서트
                                                        </p>
                                                    </td>
                                                    <td class="body">
                                                        주중 (월~목)
                                                    </td>
                                                    <td class="body"> 7,500,000원</td>
                                                    <td class="body">
                                                        1,000,000원
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="body">
                                                        주말 (금~일/공휴일)
                                                    </td>
                                                    <td class="body"> 8,800,000원</td>
                                                    <td class="body">
                                                        1,000,000원
                                                    </td>
                                                </tr>

                                                <tr>
                                                    <td class="body" rowspan="2">
                                                        <p>
                                                            방송 / 행사
                                                        </p>
                                                    </td>
                                                    <td class="body">
                                                        주중 (월~목)
                                                    </td>
                                                    <td class="body"> 7,700,000원</td>
                                                    <td class="body">
                                                        1,000,000원
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="body">
                                                        주말 (금~일/공휴일)
                                                    </td>
                                                    <td class="body"> 9,200,000원</td>
                                                    <td class="body">
                                                        1,000,000원
                                                    </td>
                                                </tr>


                                                <?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <div class="no-pd-sm--t">
                                    <div class="--table-info">
                                        <i class="fa-solid fa-circle-exclamation"></i>
                                        <?= isUpdateActive() ? 'VAT별도 / 기본구간: 09:00~22:00' : 'VAT 별도' ?>
                                    </div>
                                </div>
                            </div>

                            <div class="no-pd-2xl--t">
                                <h3 class="no-heading-lg">
                                    공연대관 안내사항
                                </h3>
                                <div class="no-pd-md--t">
                                    <div class="--box">
                                        <?php if (isUpdateActive()): ?>
                                        <ul class="--box-list-style-disc  ">
                                            <li>
                                                <h3>
                                                    <i class="fa-solid fa-check"></i>
                                                    공연 1회 150분 기준, 1일 2회 공연의 경우 50% 할증 적용
                                                </h3>
                                            </li>
                                            <li>
                                                <h3>
                                                    <i class="fa-solid fa-check"></i>
                                                    쇼케이스는 2회 공연을 기본으로 하되, 대관료 포함 사항은 1회 한 제공
                                                </h3>
                                            </li>
                                            <li>
                                                <h3>
                                                    <i class="fa-solid fa-check"></i>
                                                    대관료 포함 사항
                                                </h3>
                                                <div>
                                                    <p>공연 대관일 냉·난방 사용료: 공연 1시간 전부터 종료시까지</p>
                                                    <p>분장실, 대기실, 로비, 전기, 수도 등 사용료</p>
                                                    <p>공연장 보유 객석 사용료</p>
                                                    <p>하우스 매니저 1인 및 하우스 어셔 4인</p>
                                                </div>
                                            </li>
                                            <li>
                                                <h3>
                                                    <i class="fa-solid fa-check"></i>
                                                    대관료 불포함 사항
                                                </h3>
                                                <div>
                                                    <p>청소비(회당): 600,000원</p>
                                                    <p>추가 대관(시간당): 1,000,000원</p>
                                                    <p>추가 냉·난방 사용료 및 기타 부대시설 사용료: 추후 정산</p>
                                                </div>
                                            </li>
                                        </ul>
                                        <?php else: ?>
                                        <ul class="--box-list-style-disc  ">
                                            <li>
                                                <h3>
                                                    <i class="fa-solid fa-check"></i>
                                                    공연 대관: 09:00~23:00 / 준비 대관: 09:00~22:00
                                                </h3>
                                                <div>
                                                    <p>
                                                        식사시간인 12:00~13:00, 18:00~19:00은 대관 가능 시간에서 제외
                                                    </p>
                                                </div>
                                            </li>
                                            <li>
                                                <h3>
                                                    <i class="fa-solid fa-check"></i>
                                                    공연 1회는 최대 150분을 기준으로 하며, 1일 2회 공연의 경우 50% 할증 적용

                                                </h3>
                                            </li>
                                            <li>
                                                <h3>
                                                    <i class="fa-solid fa-check"></i>
                                                    기본 대관료에 포함되는 내용

                                                </h3>
                                                <div>
                                                    <p>
                                                        공연 대관 시간 냉,난방료 (공연 한시간 전부터 종료시까지) / 준비대관 시 냉난방 및 온수 사용료 별도

                                                    </p>
                                                    <p>
                                                        1명의 하우스 매니저, 4명의 하우스 어셔 인력

                                                    </p>
                                                    <p> 대관기간 내 사용하는 전기, 수도세 일체 / 분장실, 대기실, 휴게실 및 세탁실 사용료 일체</p>
                                                </div>
                                            </li>

                                            <li>
                                                <h3>
                                                    <i class="fa-solid fa-check"></i>
                                                    그라운드형 객석 운영 시 설치/철거 등 대관자 직접 운영

                                                </h3>
                                            </li>
                                            <li>
                                                <h3>
                                                    <i class="fa-solid fa-check"></i>
                                                    회당 청소비(50만원) 별도 부과

                                                </h3>

                                            </li>

                                        </ul>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <div class="no-pd-2xl--t">
                                <h3 class="no-heading-lg">
                                    주의사항
                                </h3>
                                <div class="no-pd-md--t">
                                    <div class="--box">
                                        <?php if (isUpdateActive()): ?>
                                        <ul class="--box-list-style-disc ">
                                            <li>
                                                <h3><i class="fa-solid fa-check"></i>브레이크타임에는 대관 진행이 제한됩니다.</h3>
                                                <div>
                                                    <p>브레이크타임: 12:00~13:00, 18:00~19:00 / 시간 조정 및 추가 대관 협의 가능</p>
                                                </div>
                                            </li>
                                            <li>
                                                <h3><i class="fa-solid fa-check"></i>대관료 완납 후 홍보 및 티켓 판매가 가능합니다.</h3>
                                            </li>
                                            <li>
                                                <h3><i class="fa-solid fa-check"></i>대관 신청 전, 대관규약(다운로드링크)을 확인바랍니다.</h3>
                                                <div>
                                                    <p>
                                                        <a href="<?=$ROOT?>/pages/board/board.list.php?board_no=10"
                                                            class="dib --link-line">대관규약 다운로드</a>
                                                    </p>
                                                </div>
                                            </li>
                                        </ul>
                                        <?php else: ?>
                                        <ul class="--box-list-style-disc ">
                                            <li>
                                                <h3> <i class="fa-solid fa-check"></i>
                                                    위 금액의 공연대관료 및 준비대관료에는 장비대여료, 부대시설사용료는 포함되어 있지 않음
                                                </h3>
                                                <div>
                                                    <p> 장비대여료 및 부대시설사용료는 블루스퀘어 <a
                                                            href="<?=$ROOT?>/pages/board/board.list.php?board_no=10"
                                                            class="dib --link-line">대관규약</a>에 의거 대관계약
                                                        시 추가산정</p>
                                                </div>
                                            </li>
                                        </ul>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>


                        </li>
                        <!----content 3----->
                        <li id="nemo">
                            <div class="--table">
                                <table class="tg">
                                    <thead>
                                        <tr>
                                            <th class="head">
                                                구분
                                            </th>
                                            <th class="head">
                                                기준
                                            </th>
                                            <th class="head">
                                                대관료
                                            </th>
                                            <th class="head">
                                                단위
                                            </th>
                                            <th class="head">
                                                비고
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <th class="sub-head">
                                                행사 및 전시
                                            </th>
                                            <th class="body">
                                                10 : 00 ~ 22 : 00
                                            </th>
                                            <th class="body">
                                                6,000,000원
                                            </th>
                                            <th class="body">
                                                일
                                            </th>
                                            <th class="body">
                                                준비, 철수시간 포함
                                            </th>
                                        </tr>
                                        <tr>
                                            <th class="sub-head">
                                                추가대관료
                                            </th>
                                            <th class="body">
                                                1 시간
                                            </th>
                                            <th class="body">
                                                1,000,000원
                                            </th>
                                            <th class="body">
                                                시간당
                                            </th>
                                            <th class="body">
                                                -
                                            </th>
                                        </tr>
                                    </tbody>
                                    <thead>
                                        <tr>
                                            <td colspan="2" class="head">
                                                <p>
                                                    순수예술 및 전시대관
                                                </p>
                                            </td>
                                            <td colspan="3" class="head">
                                                <p>
                                                    별도 협의
                                                </p>
                                            </td>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td class="body">
                                                이행보증예치금
                                            </td>
                                            <td class="body"> -</td>
                                            <td class="body">3,000,000원</td>
                                            <td class="body" colspan="2">원상복구 후 반납</td>
                                        </tr>
                                    </tbody>
                                    <thead>
                                        <tr>
                                            <td class="head" colspan="5">
                                                추가 옵션
                                            </td>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td class="body">
                                                너른마루
                                            </td>
                                            <td class="body"> -</td>
                                            <td class="body">1,200,000원</td>
                                            <td class="body">셔터 앞 공간</td>
                                            <td class="body" rowspan="2">-</td>
                                        </tr>
                                    </tbody>
                                    <tbody>
                                        <tr>
                                            <td class="body">
                                                2층 테라스
                                            </td>
                                            <td class="body"> -</td>
                                            <td class="body">500,000원</td>
                                            <td class="body">케이터링 가능</td>
                                            <td class="body"></td>
                                        </tr>
                                    </tbody>
                                </table>

                            </div>
                            <div class="no-pd-sm--t">
                                <div class="--table-info">
                                    <i class="fa-solid fa-circle-exclamation"></i>
                                    VAT 별도
                                </div>
                                <div class="--table-info">
                                    ※ 장기 대관의 경우 대관료 별도 협의
                                </div>
                            </div>
                            <div class="no-pd-2xl--t">
                                <h3 class="no-heading-lg">
                                    대관 안내사항
                                </h3>
                                <div class="no-pd-md--t">
                                    <div class="--box">
                                        <ul class="--box-list-style-disc ">
                                            <li>
                                                <h3>
                                                    <i class="fa-solid fa-check"></i>
                                                    기본대관료 포함사항


                                                </h3>
                                                <div>
                                                    <p> 냉난방비 및 전기세 (기계장비 설치 시 별도 협의)</p>
                                                </div>
                                            </li>

                                            <li>
                                                <h3>
                                                    <i class="fa-solid fa-check"></i>
                                                    이행보증예치금


                                                </h3>
                                                <div>
                                                    <p>
                                                        철수 및 원상복구 확인 후 반환 <br>
                                                        ※ 복구 비용 발생 시 차감 후 반환
                                                    </p>

                                                </div>
                                            </li>

                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <div class="no-pd-2xl--t">
                                <h3 class="no-heading-lg">
                                    주의사항
                                </h3>
                                <div class="no-pd-md--t">
                                    <div class="--box">
                                        <ul class="--box-list-style-disc ">
                                            <li>
                                                <h3> <i class="fa-solid fa-check"></i>
                                                    위 기본대관료에는 부대시설사용료가 포함되어 있지 않음
                                                </h3>
                                                <div>
                                                    <p> 부대시설사용료는 블루스퀘어 <a
                                                            href="<?=$ROOT?>/pages/board/board.list.php?board_no=10"
                                                            class="dib --link-line">대관규약</a>에 의거 대관계약시 추가산정</p>
                                                </div>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                        </li>
                        <li id="practice">
                            <h3 class="no-heading-lg">
                                연습실 - B1F
                            </h3>
                            <div class="no-pd-md--t">
                                <div class="--table">
                                    <table class="tg">
                                        <thead>
                                            <tr>
                                                <th class="head">
                                                    사용기간
                                                </th>
                                                <th class="head">
                                                    대관료
                                                </th>
                                                <th class="head">
                                                    사용기준
                                                </th>
                                                <th class="head" colspan="2">
                                                    비고
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <th class="sub-head" rowspan="3">
                                                    1회
                                                </th>
                                                <th class="body" rowspan="3">
                                                    220,000원
                                                </th>
                                                <th class="body" rowspan="3">
                                                    3 시간 기준<br>
                                                    오전, 오후, 야간 구분
                                                </th>
                                                <th class="sub-head">
                                                    오전
                                                </th class="body">
                                                <th class="body">09 : 00 - 12 : 00</th>
                                            </tr>
                                            <tr>
                                                <th class="sub-head">
                                                    오후
                                                </th class="body">
                                                <th class="body">14 : 00 - 17 : 00</th>
                                            </tr>
                                            <tr>
                                                <th class="sub-head">
                                                    야간
                                                </th class="body">
                                                <th class="body">19 : 00 - 22 : 00</th>
                                            </tr>
                                        </tbody>
                                        <tbody>
                                            <tr>
                                                <th class="sub-head">
                                                    1일
                                                </th>
                                                <th class="body">
                                                    450,000원
                                                </th>
                                                <th class="body">
                                                    오전, 오후, 야간
                                                </th>
                                                <th class="body" colspan="2">
                                                    09 : 00 - 22 : 00
                                                </th>
                                            </tr>
                                        </tbody>

                                    </table>

                                </div>
                            </div>
                            <div class="no-pd-sm--t">
                                <div class="--table-info">
                                    <i class="fa-solid fa-circle-exclamation"></i>
                                    VAT 별도
                                </div>
                            </div>
                            <div class="no-pd-2xl--t">
                                <h3 class="no-heading-lg">
                                    주의사항
                                </h3>
                                <div class="no-pd-md--t">
                                    <div class="--box">
                                        <ul class="--box-list-style-disc ">
                                            <li>
                                                <h3> <i class="fa-solid fa-check"></i>
                                                    위 기본대관료에는 부대시설사용료가 포함되어 있지 않음
                                                </h3>
                                                <div>
                                                    <p> 부대시설사용료는 블루스퀘어 <a
                                                            href="<?=$ROOT?>/pages/board/board.list.php?board_no=10"
                                                            class="dib --link-line">대관규약</a>에 의거 대관계약시 추가산정</p>
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
    </section>
</main>



<?php
    include_once $STATIC_ROOT.'/inc/layouts/footer.php';
    ?>
