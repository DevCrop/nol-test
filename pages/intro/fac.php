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
    <section class="no-sub-fac no-pd-2xl--y">
        <!---tab-->
        <div class="no-sub-tab --of-h " <?=$aos_title?>>
            <div class="no-container-xl">
                <div class="no-sub-tab-slider">
                    <ul class="swiper-wrapper">
                        <li class="swiper-slide">
                            <button type="button" class="no-btn active no-btn__fill" data-id="shinhan-card">
                                <?= isUpdateActive() ? '우리은행홀' : '신한카드홀' ?>
                            </button>
                        </li>
                        <li class="swiper-slide">
                            <button type="button" class="no-btn no-btn__fill" data-id="master-card">
                                <?= isUpdateActive() ? '우리WON뱅킹홀' : $hallName ?>
                            </button>
                        </li>
                        <li class="swiper-slide">
                            <button type="button" class="no-btn no-btn__fill" data-id="nemo">
                                NEMO
                            </button>
                        </li>
                        <li class="swiper-slide">
                            <button type="button" class="no-btn no-btn__fill" data-id="practice">
                                연습실
                            </button>
                        </li>
                        <!--
                        <li class="swiper-slide">
                            <button type="button" class="no-btn no-btn__fill" data-id="event">
                                이벤트홀
                            </button>
                        </li>-->
                    </ul>
                </div>
            </div>
        </div>
        <div class="no-sub-fac-contents no-sub-tab-contents " <?=$aos_content?>>
            <ul>
                <!----content-1--->
                <li class="shinhan-card" id="shinhan-card">
                    <div class="no-container-xl">
                        <div class="cnt no-pd-xl--t">
                            <div class="no-sub-tab-contents__info no-position-padding ">
                                <div class="no-sub-tab-contents__title">
                                    <div class="no-sub-fac-logo">
                                        <div class="--toggle-images">
                                            <img src="/resource/images/logo/<?= isUpdateActive() ? 'blue_square_wooribank_hall_logo_white.png' : 'blue_square_shinhan_hall_logo_white.png' ?>"
                                                class="--light-img" alt="">
                                            <img src="/resource/images/logo/<?= isUpdateActive() ? 'blue_square_wooribank_hall_logo_black.png' : 'blue_square_shinhan_hall_logo_black.png' ?>"
                                                class="--dark-img" alt="">
                                        </div>
                                    </div>
                                    <a href="<?=$ROOT?>/pages/rental/process.php"
                                        class="no-btn-arrow no-btn-arrow__fill--primary  ">
                                        대관안내 바로가기
                                        <span>
                                            <i class="fa-regular fa-arrow-right"></i>
                                            <i class="fa-regular fa-arrow-right"></i>
                                        </span>
                                    </a>
                                </div>
                                <div class="no-sub-tab-contents__desc no-body-md">
                                    <ul>
                                        <li>
                                            <p>
                                                <?= isUpdateActive() ? '우리은행홀' : '신한카드홀' ?>은 국내 최대 규모의 뮤지컬 전용 대극장으로, 총
                                                객석 3층(1,766석)으로 이루어져 있습니다.
                                            </p>
                                        </li>
                                        <li>
                                            <p>
                                                객석 1층은 무대 끝 선과 객석 맨 뒷열의 거리가 약 27m로 어느 자리에서든 가깝게 무대를 바라볼 수 있으며,
                                                객석 2층은 1열 좌석에서 무대까지의 거리가 18.5m로 국내 최단 거리를 자랑합니다.

                                                또한 <?= isUpdateActive() ? '우리은행홀' : '신한카드홀' ?> 좌석은 교차 형식으로 배치되어
                                                관객 여러분의 편안한 관람을 경험할 수 있도록 설계되어 있습니다.
                                            </p>
                                        </li>
                                        <li>
                                            <p>

                                                가장 편안하고 쾌적한 공연 관람이 되도록, 항상 배려하는 마음으로 서비스 연구에 매진하는
                                                <?= isUpdateActive() ? '우리은행홀' : '신한카드홀' ?>에서
                                                뮤지컬의 뜨거운 열기와 무한한 감동을 가져가시기 바랍니다.
                                            </p>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <div class="no-pd-lg--t">
                                <div class="no-sub-tab-table">
                                    <ul>
                                        <li>
                                            <div class="no-sub-tab-table__title">
                                                <h4 class="no-heading-md">용도</h4>
                                            </div>
                                            <div class="no-sub-tab-table__info no-body-md">
                                                <ul>
                                                    <li>
                                                        <p>뮤지컬 전용 공연장</p>
                                                    </li>
                                                </ul>
                                            </div>
                                        </li>
                                        <li>
                                            <div class="no-sub-tab-table__title">
                                                <h4 class="no-heading-md">좌석</h4>
                                            </div>
                                            <div class="no-sub-tab-table__info no-body-md">
                                                <ul>
                                                    <li>
                                                        <p>
                                                            총 좌석수
                                                            <span class="no-clr-text-primary">1,766석</span>
                                                        </p>
                                                    </li>
                                                    <li>
                                                        <p>
                                                            1층
                                                            <span class="no-clr-text-primary">1,066석</span>
                                                        </p>
                                                    </li>
                                                    <li>
                                                        <p>
                                                            2층
                                                            <span class="no-clr-text-primary">430석</span>
                                                        </p>
                                                    </li>
                                                    <li>
                                                        <p>
                                                            3층
                                                            <span class="no-clr-text-primary">270석</span>
                                                        </p>
                                                    </li>
                                                </ul>
                                            </div>
                                        </li>
                                        <li>
                                            <div class="no-sub-tab-table__title">
                                                <h4 class="no-heading-md">부대시설</h4>
                                            </div>
                                            <div class="no-sub-tab-table__info no-body-md">
                                                <ul>
                                                    <li>
                                                        <p>
                                                            VIP 분장실, 의상실, 세탁실, 그룹 분장실, 소품실 등

                                                        </p>
                                                    </li>
                                                </ul>
                                            </div>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <div class="no-pd-lg--t">
                                <div class="swiper no-sub-tab-contents-slider">
                                    <ul class="swiper-wrapper">
                                        <?php for ($i = 1; $i < 4 ; $i++) :?>
                                        <li class="swiper-slide">
                                            <figure class="">
                                                <img src="<?=IMG_PATH?>/sub/shinhan_card_hall_img_<?=$i?>.jpg" alt="">
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
                            <div class="no-pd-2xl--t">
                                <div class="no-sub-tab-chart">
                                    <div class="swiper no-sub-tab-chart-slider">
                                        <ul class="swiper-wrapper">
                                            <li class="swiper-slide">
                                                <div class="no-sub-tab-chart-info">
                                                    <div class="no-sub-tab-chart-title">
                                                        <h2 class="no-heading-lg">
                                                            좌석 배치도 /
                                                            <span class="fm-en ">1F</span>
                                                        </h2>
                                                    </div>
                                                    <div class="no-sub-tab-table no-sub-tab-chart-table">
                                                        <ul>
                                                            <li>
                                                                <div class="no-sub-tab-table__title no-heading-md">
                                                                    <h4>객석 1층 좌석 수</h4>
                                                                </div>
                                                                <div class="no-sub-tab-table__info">
                                                                    <ul>
                                                                        <li class="f fd-c no-gap-sm">
                                                                            <p class="no-body-md">1,066석
                                                                            </p>
                                                                            <div class="f ai-c no-gap-sm ">
                                                                                <p class="no-body-md">
                                                                                    오케스트라 피트석
                                                                                    <span
                                                                                        class="no-clr-text-orchestra">76석</span>
                                                                                </p>
                                                                                <p class="no-body-md">
                                                                                    휠체어석
                                                                                    <span
                                                                                        class="no-clr-text-wheelchair">18석</span>
                                                                                </p>
                                                                                <p class="no-body-md">
                                                                                    모자동반석
                                                                                    <span
                                                                                        class="no-clr-text-family">14석</span>
                                                                                </p>
                                                                            </div>
                                                                        </li>

                                                                    </ul>
                                                                </div>
                                                            </li>

                                                        </ul>
                                                    </div>
                                                </div>
                                                <div class="no-pd-lg--t">
                                                    <figure class="no-container-lg">
                                                        <div class="--toggle-images">
                                                            <img src="<?=IMG_PATH?>/sub/shinhan_card_hall_chart_1_dark.png"
                                                                alt="" class="--light-img">
                                                            <img src="<?=IMG_PATH?>/sub/shinhan_card_hall_chart_1_light.png"
                                                                alt="" class="--dark-img">
                                                        </div>
                                                    </figure>
                                                </div>
                                            </li>
                                            <li class="swiper-slide">
                                                <div class="no-sub-tab-chart-info">
                                                    <div class="no-sub-tab-chart-title">
                                                        <h2 class="no-heading-lg">
                                                            좌석 배치도 /
                                                            <span class="fm-en ">2F</span>
                                                        </h2>
                                                    </div>
                                                    <div class="no-sub-tab-table no-sub-tab-chart-table">
                                                        <ul>
                                                            <li>
                                                                <div class="no-sub-tab-table__title no-heading-md">
                                                                    <h4>객석 2층 좌석 수</h4>
                                                                </div>
                                                                <div class="no-sub-tab-table__info">
                                                                    <ul>
                                                                        <li>
                                                                            <p class="no-body-md">430석</p>
                                                                        </li>

                                                                    </ul>
                                                                </div>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                </div>
                                                <div class="no-pd-lg--t">
                                                    <figure class="no-container-lg">
                                                        <div class="--toggle-images">
                                                            <img src="<?=IMG_PATH?>/sub/shinhan_card_hall_chart_2_dark.png"
                                                                alt="" class="--light-img">
                                                            <img src="<?=IMG_PATH?>/sub/shinhan_card_hall_chart_2_light.png"
                                                                alt="" class="--dark-img">
                                                        </div>
                                                    </figure>
                                                </div>
                                            </li>
                                            <li class="swiper-slide">
                                                <div class="no-sub-tab-chart-info">
                                                    <div class="no-sub-tab-chart-title">
                                                        <h2 class="no-heading-lg">
                                                            좌석 배치도 /
                                                            <span class="fm-en ">3F</span>
                                                        </h2>
                                                    </div>
                                                    <div class="no-sub-tab-table no-sub-tab-chart-table">
                                                        <ul>
                                                            <li>
                                                                <div class="no-sub-tab-table__title no-heading-md">
                                                                    <h4>객석 3층 좌석 수</h4>
                                                                </div>
                                                                <div class="no-sub-tab-table__info no-body-md">
                                                                    <ul>
                                                                        <li>
                                                                            <p class="no-heading-sm">270석</p>
                                                                        </li>
                                                                    </ul>
                                                                </div>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                </div>
                                                <div class="no-pd-lg--t">
                                                    <figure class="no-container-lg">
                                                        <div class="--toggle-images">
                                                            <img src="<?=IMG_PATH?>/sub/shinhan_card_hall_chart_3_dark.png"
                                                                alt="" class="--light-img">
                                                            <img src="<?=IMG_PATH?>/sub/shinhan_card_hall_chart_3_light.png"
                                                                alt="" class="--dark-img">
                                                        </div>
                                                    </figure>
                                                </div>
                                            </li>
                                        </ul>
                                        <button type="button" class="swiper-button-next swiper-button --tf-inherit">
                                            <i class="fa-light fa-arrow-right-long"></i>
                                        </button>
                                        <button type="button" class="swiper-button-prev swiper-button --tf-inherit">
                                            <i class="fa-light fa-arrow-left-long"></i>
                                        </button>
                                    </div>
                                    <div class="no-pd-lg--t">
                                        <div class="swiper no-sub-tab-chart-slider-thum">
                                            <ul class="swiper-wrapper">
                                                <li class="swiper-slide">
                                                    <button type="button">
                                                        <figure class="--toggle-images">
                                                            <img src="<?=IMG_PATH?>/sub/shinhan_card_hall_chart_1_dark.png"
                                                                alt="" class="--light-img">
                                                            <img src="<?=IMG_PATH?>/sub/shinhan_card_hall_chart_1_light.png"
                                                                alt="" class="--dark-img">
                                                        </figure>
                                                    </button>
                                                </li>
                                                <li class="swiper-slide">
                                                    <button type="button">
                                                        <figure class="--toggle-images">
                                                            <img src="<?=IMG_PATH?>/sub/shinhan_card_hall_chart_2_dark.png"
                                                                alt="" class="--light-img">
                                                            <img src="<?=IMG_PATH?>/sub/shinhan_card_hall_chart_2_light.png"
                                                                alt="" class="--dark-img">
                                                        </figure>
                                                    </button>
                                                </li>
                                                <li class="swiper-slide">
                                                    <button type="button">
                                                        <figure class="--toggle-images">
                                                            <img src="<?=IMG_PATH?>/sub/shinhan_card_hall_chart_3_dark.png"
                                                                alt="" class="--light-img">
                                                            <img src="<?=IMG_PATH?>/sub/shinhan_card_hall_chart_3_light.png"
                                                                alt="" class="--dark-img">
                                                        </figure>
                                                    </button>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </li>
                <!----content-2--->
                <li class="master-card" id="master-card">
                    <div class="no-container-xl">
                        <div class="cnt no-pd-xl--t">
                            <div class="no-sub-tab-contents__info no-position-padding">
                                <div class="no-sub-tab-contents__title">
                                    <div class="no-sub-fac-logo">
                                        <div class="--toggle-images">
                                            <img src="/resource/images/logo/<?= isUpdateActive() ? 'blue_square_woori_won_banking_hall_logo_white.png' : $logoPrefix . '_white.png' ?>"
                                                class="--light-img" alt="">
                                            <img src="/resource/images/logo/<?= isUpdateActive() ? 'blue_square_woori_won_banking_hall_logo_black.png' : $logoPrefix . '_black.png' ?>"
                                                class="--dark-img" alt="">
                                        </div>

                                    </div>
                                    <a href="<?=$ROOT?>/pages/rental/process.php"
                                        class="no-btn-arrow no-btn-arrow__fill--primary  ">
                                        대관안내 바로가기
                                        <span>
                                            <i class="fa-regular fa-arrow-right"></i>
                                            <i class="fa-regular fa-arrow-right"></i>
                                        </span>
                                    </a>
                                </div>
                                <div class="no-sub-tab-contents__desc no-body-md">
                                    <ul>
                                        <li>
                                            <p>
                                                <?= isUpdateActive() ? '우리WON뱅킹홀' : $hallName ?>은 총 1,379석(스탠딩
                                                <?= isUpdateActive() ? '2,400' : '2,800' ?>석) 규모의 진정한 라이브를 만들고 즐길 수 있도록
                                                태어난
                                                공연장으로,
                                            </p>
                                        </li>
                                        <li>
                                            <p>
                                                세계 최고의 D&B 음향 시스템과 좌석 변형이 가능한 객석 시스템을 갖추고 있습니다.
                                            </p>
                                        </li>
                                        <li>
                                            <p>
                                                라이브 콘서트와 쇼케이스, 기업행사, 팬미팅 등 다양한 장르의 콘텐츠를 공연할 수 있는
                                                최적의 시설을 갖추고 있으며 무대와 관객이 하나되는 강렬한 경험을 만끽하실 수 있습니다.
                                            </p>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <div class="no-pd-lg--t">
                                <div class="no-sub-tab-table">
                                    <ul>
                                        <li>
                                            <div class="no-sub-tab-table__title">
                                                <h4 class="no-heading-md">용도</h4>
                                            </div>
                                            <div class="no-sub-tab-table__info no-body-md">
                                                <ul>
                                                    <li>
                                                        <p>콘서트, 쇼케이스, 팬미팅, 컨퍼런스, 기업행사 등</p>
                                                    </li>
                                                </ul>
                                            </div>
                                        </li>
                                        <li>
                                            <div class="no-sub-tab-table__title">
                                                <h4 class="no-heading-md">좌석</h4>
                                            </div>
                                            <div class="no-sub-tab-table__info no-body-md">
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
                                                            <span class="no-clr-text-primary">약
                                                                <?= isUpdateActive() ? '2,400' : '2,800' ?>명 수용</span>
                                                        </p>
                                                    </li>
                                                </ul>
                                            </div>
                                        </li>
                                        <li>
                                            <div class="no-sub-tab-table__title">
                                                <h4 class="no-heading-md">부대시설</h4>
                                            </div>
                                            <div class="no-sub-tab-table__info no-body-md">
                                                <ul>
                                                    <li>
                                                        <p>
                                                            VIP분장실, 그룹 분장실, 스탭 대기실, 스탭식당 등
                                                        </p>
                                                    </li>
                                                </ul>
                                            </div>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <div class="no-pd-lg--t">
                                <div class="swiper no-sub-tab-contents-slider">
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
                                    <div class="swiper-button-next swiper-button">
                                        <i class="fa-light fa-arrow-right-long"></i>
                                    </div>
                                    <div class="swiper-button-prev swiper-button">
                                        <i class="fa-light fa-arrow-left-long"></i>
                                    </div>
                                </div>
                            </div>

                            <div class="no-pd-2xl--t">
                                <!---tab-1---->
                                <div class="no-pd-xl--b">
                                    <ul class="no-nest-tab-wrap">
                                        <li>
                                            <button type="button" class="no-nest-btn active">그라운드형</button>
                                        </li>
                                        <li>
                                            <button type="button" class="no-nest-btn">스탠딩형</button>
                                        </li>
                                    </ul>
                                </div>
                                <div class="no-nest-tab-contents">
                                    <ul>
                                        <!----nest content-1--->
                                        <li class="active">
                                            <div class="no-sub-tab-chart">
                                                <div class="swiper no-sub-tab-chart-slider-ground">
                                                    <ul class="swiper-wrapper">
                                                        <li class="swiper-slide">
                                                            <div class="no-sub-tab-chart-info">
                                                                <div class="no-sub-tab-chart-title">
                                                                    <h2 class="no-heading-lg">
                                                                        좌석 배치도 /
                                                                        <span class="fm-en ">1F</span>
                                                                    </h2>
                                                                </div>
                                                                <div class="no-sub-tab-table no-sub-tab-chart-table">
                                                                    <ul>
                                                                        <li>
                                                                            <div
                                                                                class="no-sub-tab-table__title no-heading-md">
                                                                                <h4>객석 1층 좌석 수</h4>
                                                                            </div>
                                                                            <div class="no-sub-tab-table__info">
                                                                                <ul>
                                                                                    <li>
                                                                                        <p class=" no-body-md">
                                                                                            998석
                                                                                        </p>
                                                                                    </li>
                                                                                </ul>
                                                                            </div>
                                                                        </li>
                                                                    </ul>
                                                                </div>
                                                            </div>
                                                            <div class="no-pd-lg--t">
                                                                <figure class="no-container-lg">
                                                                    <div class="--toggle-images">
                                                                        <img src="<?=IMG_PATH?>/sub/master_chart_img_1_1_dark.png"
                                                                            alt="" class="--light-img">
                                                                        <img src="<?=IMG_PATH?>/sub/master_chart_img_1_1_light.png"
                                                                            alt="" class="--dark-img">
                                                                    </div>
                                                                </figure>
                                                            </div>
                                                        </li>
                                                        <li class="swiper-slide">
                                                            <div class="no-sub-tab-chart-info">
                                                                <div class="no-sub-tab-chart-title">
                                                                    <h2 class="no-heading-lg">
                                                                        좌석 배치도 /
                                                                        <span class="fm-en ">2F</span>
                                                                    </h2>
                                                                </div>
                                                                <div class="no-sub-tab-table no-sub-tab-chart-table">
                                                                    <ul>
                                                                        <li>
                                                                            <div
                                                                                class="no-sub-tab-table__title no-heading-md">
                                                                                <h4>객석 2층 좌석 수</h4>
                                                                            </div>
                                                                            <div class="no-sub-tab-table__info">
                                                                                <ul>
                                                                                    <li>
                                                                                        <p class="no-body-md">381석 (휠체어
                                                                                            10석 포함)
                                                                                        </p>
                                                                                    </li>
                                                                                </ul>
                                                                            </div>
                                                                        </li>
                                                                    </ul>
                                                                </div>
                                                            </div>
                                                            <div class="no-pd-lg--t">
                                                                <figure class="no-container-lg">
                                                                    <div class="--toggle-images">
                                                                        <img src="<?=IMG_PATH?>/sub/master_chart_img_2_2_dark_re.png"
                                                                            alt="" class="--light-img">
                                                                        <img src="<?=IMG_PATH?>/sub/master_chart_img_2_2_light_re.png"
                                                                            alt="" class="--dark-img">
                                                                    </div>
                                                                </figure>
                                                            </div>
                                                        </li>

                                                    </ul>
                                                    <div class="swiper-button-next swiper-button">
                                                        <i class="fa-light fa-arrow-right-long"></i>
                                                    </div>
                                                    <div class="swiper-button-prev swiper-button">
                                                        <i class="fa-light fa-arrow-left-long"></i>
                                                    </div>
                                                </div>
                                                <div class="no-pd-lg--t">
                                                    <div
                                                        class="swiper no-sub-tab-chart-slider-thum-ground no-sub-tab-chart-slider-thum">
                                                        <ul class="swiper-wrapper">
                                                            <li class="swiper-slide">
                                                                <button type="button">
                                                                    <figure class="--toggle-images">
                                                                        <img src="<?=IMG_PATH?>/sub/master_chart_img_1_1_dark.png"
                                                                            alt="" class="--light-img">
                                                                        <img src="<?=IMG_PATH?>/sub/master_chart_img_1_1_light.png"
                                                                            alt="" class="--dark-img">
                                                                    </figure>
                                                                </button>
                                                            </li>
                                                            <li class="swiper-slide">
                                                                <button type="button">
                                                                    <figure class="--toggle-images">
                                                                        <img src="<?=IMG_PATH?>/sub/master_chart_img_2_2_dark_re.png"
                                                                            alt="" class="--light-img">
                                                                        <img src="<?=IMG_PATH?>/sub/master_chart_img_2_2_light_re.png"
                                                                            alt="" class="--dark-img">
                                                                    </figure>
                                                                </button>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                </div>
                                            </div>
                                        </li>
                                        <!----nest content-2--->
                                        <li class="">
                                            <div class="no-sub-tab-chart">
                                                <div class="swiper no-sub-tab-chart-slider-stand">
                                                    <ul class="swiper-wrapper">
                                                        <li class="swiper-slide">
                                                            <div class="no-sub-tab-chart-info">
                                                                <div class="no-sub-tab-chart-title">
                                                                    <h2 class="no-heading-lg">
                                                                        좌석 배치도 /
                                                                        <span class="fm-en ">1F</span>
                                                                    </h2>
                                                                </div>
                                                                <div class="no-sub-tab-table no-sub-tab-chart-table">
                                                                    <ul>
                                                                        <li>
                                                                            <div
                                                                                class="no-sub-tab-table__title no-heading-md">
                                                                                <h4>객석 1층 좌석 수</h4>
                                                                            </div>
                                                                            <div class="no-sub-tab-table__info">
                                                                                <ul>
                                                                                    <li>
                                                                                        <p class="no-body-md">
                                                                                            최대
                                                                                            2,400명 수용 (2,000명 권장) </p>
                                                                                    </li>
                                                                                </ul>
                                                                            </div>
                                                                        </li>
                                                                    </ul>
                                                                </div>
                                                            </div>
                                                            <div class="no-pd-lg--t">
                                                                <figure class="no-container-lg">
                                                                    <div class="--toggle-images">
                                                                        <img src="<?=IMG_PATH?>/sub/master_chart_img_2_1_dark.png"
                                                                            alt="" class="--light-img">
                                                                        <img src="<?=IMG_PATH?>/sub/master_chart_img_2_1_light.png"
                                                                            alt="" class="--dark-img">
                                                                    </div>
                                                                </figure>
                                                            </div>
                                                        </li>
                                                        <li class="swiper-slide">
                                                            <div class="no-sub-tab-chart-info">
                                                                <div class="no-sub-tab-chart-title">
                                                                    <h2 class="no-heading-lg">
                                                                        좌석 배치도 /
                                                                        <span class="fm-en ">2F</span>
                                                                    </h2>
                                                                </div>
                                                                <div class="no-sub-tab-table no-sub-tab-chart-table">
                                                                    <ul>
                                                                        <li>
                                                                            <div
                                                                                class="no-sub-tab-table__title no-heading-md">
                                                                                <h4>객석 2층 좌석 수</h4>
                                                                            </div>
                                                                            <div class="no-sub-tab-table__info">
                                                                                <ul>
                                                                                    <li>
                                                                                        <p class="no-body-md">381석
                                                                                            (휠체어 10석 포함)</p>
                                                                                    </li>
                                                                                </ul>
                                                                            </div>
                                                                        </li>

                                                                    </ul>
                                                                </div>
                                                            </div>
                                                            <div class="no-pd-lg--t">
                                                                <figure class="no-container-lg">
                                                                    <div class="--toggle-images">
                                                                        <img src="<?=IMG_PATH?>/sub/master_chart_img_2_2_dark_re.png"
                                                                            alt="" class="--light-img">
                                                                        <img src="<?=IMG_PATH?>/sub/master_chart_img_2_2_light_re.png"
                                                                            alt="" class="--dark-img">
                                                                    </div>
                                                                </figure>
                                                            </div>
                                                        </li>

                                                    </ul>
                                                    <div class="swiper-button-next swiper-button">
                                                        <i class="fa-light fa-arrow-right-long"></i>
                                                    </div>
                                                    <div class="swiper-button-prev swiper-button">
                                                        <i class="fa-light fa-arrow-left-long"></i>
                                                    </div>
                                                </div>
                                                <div class="no-pd-lg--t">
                                                    <div
                                                        class="swiper no-sub-tab-chart-slider-thum-stand no-sub-tab-chart-slider-thum">
                                                        <ul class="swiper-wrapper">
                                                            <li class="swiper-slide">
                                                                <button type="button">
                                                                    <figure class="--toggle-images">
                                                                        <img src="<?=IMG_PATH?>/sub/master_chart_img_2_1_dark.png"
                                                                            alt="" class="--light-img">
                                                                        <img src="<?=IMG_PATH?>/sub/master_chart_img_2_1_light.png"
                                                                            alt="" class="--dark-img">
                                                                    </figure>
                                                                </button>
                                                            </li>
                                                            <li class="swiper-slide">
                                                                <button type="button">
                                                                    <figure class="--toggle-images">
                                                                        <img src="<?=IMG_PATH?>/sub/master_chart_img_2_2_dark_re.png"
                                                                            alt="" class="--light-img">
                                                                        <img src="<?=IMG_PATH?>/sub/master_chart_img_2_2_light_re.png"
                                                                            alt="" class="--dark-img">
                                                                    </figure>
                                                                </button>
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
                <!----content-3--->
                <li class="nemo" id="nemo">
                    <div class="no-container-xl">
                        <div class="cnt no-pd-xl--t">
                            <div class="no-sub-tab-contents__info no-position-padding">
                                <div class="no-sub-tab-contents__title">
                                    <div class="no-sub-fac-logo">
                                        <h2 class="no-heading-lg">복합문화공간 NEMO</h2>
                                    </div>
                                    <a href="<?=$ROOT?>/pages/rental/process.php"
                                        class="no-btn-arrow no-btn-arrow__fill--primary  ">
                                        대관안내 바로가기
                                        <span>
                                            <i class="fa-regular fa-arrow-right"></i>
                                            <i class="fa-regular fa-arrow-right"></i>
                                        </span>
                                    </a>
                                </div>
                                <div class="no-sub-tab-contents__desc no-body-md">
                                    <ul>
                                        <li>
                                            <p>
                                                NEMO는 해상 수송용 컨테이너 박스 18개를 각각 연결, 결합하여 만든 복합문화공간입니다.
                                            </p>
                                        </li>
                                        <li>
                                            <p>
                                                <b>New Emotional Movemen + New Multi-Culture Object</b>의 조합어로 탄생한 NEMO는
                                                파인아트는 물론 그래픽 디자인, 영상, 건축 등의 예술 분야 전시를 비롯해
                                                아트페어, 심포지움, 세미나 등의 다양한 문화 행사가 진행됩니다.
                                            </p>
                                        </li>
                                        <li>
                                            <p>
                                                무한한 잠재력과 가능성을 가진 NEMO에서 새로운 일상을 향유하시기 바랍니다.
                                            </p>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <div class="no-pd-lg--t">
                                <div class="no-sub-tab-table">
                                    <ul>
                                        <li>
                                            <div class="no-sub-tab-table__title">
                                                <h4 class="no-heading-md">용도</h4>
                                            </div>
                                            <div class="no-sub-tab-table__info no-body-md">
                                                <ul>
                                                    <li>
                                                        <p>팝업, 전시, 아트페어, 강의, 세미나 등</p>
                                                    </li>
                                                </ul>
                                            </div>
                                        </li>
                                        <li>
                                            <div class="no-sub-tab-table__title">
                                                <h4 class="no-heading-md">부대시설</h4>
                                            </div>
                                            <div class="no-sub-tab-table__info no-body-md">
                                                <ul>
                                                    <li>
                                                        <p>
                                                            실내 BGM용 음향장비, LED 전광판, 전시용 레일 조명 등
                                                        </p>
                                                    </li>
                                                </ul>
                                            </div>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <div class="no-pd-lg--t">
                                <div class="swiper no-sub-tab-contents-slider">
                                    <ul class="swiper-wrapper">
                                        <?php for ($i = 1; $i < 5 ; $i++) :?>
                                        <li class="swiper-slide">
                                            <figure>
                                                <img src="<?=IMG_PATH?>/sub/nemo_img_<?=$i?>.jpg" alt="">
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
                <!----content-4--->
                <li class="practice" id="practice">
                    <div class="no-container-xl">
                        <div class="cnt no-pd-xl--t">
                            <div class="no-sub-tab-contents__info no-position-padding">
                                <div class="no-sub-tab-contents__title">
                                    <div class="no-sub-fac-logo">
                                        <h2 class="no-heading-lg">연습실</h2>
                                    </div>
                                    <a href="<?=$ROOT?>/pages/rental/process.php"
                                        class="no-btn-arrow no-btn-arrow__fill--primary  ">
                                        대관안내 바로가기
                                        <span>
                                            <i class="fa-regular fa-arrow-right"></i>
                                            <i class="fa-regular fa-arrow-right"></i>
                                        </span>
                                    </a>
                                </div>
                                <div class="no-sub-tab-contents__desc no-body-md">
                                    <ul>
                                        <li>
                                            <p>
                                                문화예술인들의 쾌적한 연습과 다양한 창작활동이 가능한 다목적 연습 공간입니다.
                                            </p>
                                        </li>
                                        <li>
                                            <p>
                                                블루스퀘어 연습실은 창작의 시작점이자 가능성을 펼치는 무대입니다. <br>쾌적한 환경에서 문화예술인들이 자유롭게 꿈을 실현할 수
                                                있도록
                                                설계되었습니다.
                                            </p>
                                        </li>
                                        <li>
                                            <p>
                                                이곳에서 당신의 열정과 창의력을 마음껏 펼쳐보세요.
                                            </p>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <div class="no-pd-lg--t">
                                <div class="no-sub-tab-table">
                                    <ul>
                                        <li>
                                            <div class="no-sub-tab-table__title no-heading-md">
                                                <h4>B1F 연습실</h4>
                                            </div>
                                            <div class="no-sub-tab-table__info">
                                                <ul>
                                                    <li>
                                                        <p class="no-body-md">
                                                            공연예술작품 연습, 리허설, 각종 오디션 등
                                                        </p>
                                                    </li>
                                                </ul>
                                            </div>
                                        </li>

                                    </ul>
                                </div>
                            </div>
                            <div class="no-pd-xl--t">
                                <h4 class="no-heading-lg">B1F 연습실</h4>
                                <div class="no-pd-lg--t">
                                    <div class="swiper no-sub-tab-contents-slider-2 no-sub-tab-contents-slider">
                                        <ul class="swiper-wrapper">
                                            <?php for ($i = 1; $i < 4 ; $i++) :?>
                                            <li class="swiper-slide">
                                                <figure>
                                                    <img src="<?=IMG_PATH?>/sub/practice_b1_img_<?=$i?>.jpg" alt="">
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
                    </div>
                </li>
            </ul>
        </div>

    </section>
</main>



<?php
    include_once $STATIC_ROOT.'/inc/layouts/footer.php';
    ?>
