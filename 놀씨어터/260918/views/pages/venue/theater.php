<?php section('content') ?>

<div class=" no-section-md">
    <?= include_view('components.sub-visual', ['title' => '공연장 소개']) ?>


    <section class="no-sub-theater">
        <div class="no-container-xl">
            <div class="no-sub-theater-inner">
                <div class="no-sub-theater-intro">
                    <!--
                    <div class="no-sub-theater-banner" <?= AOS_MEDIUM ?>>
                        <img src="<?= base_path('/resource/images/sub/sub_theater_banner.jpg') ?>" alt="공연장 소개">
                    </div>-->
                    <div class="no-sub-theater-txt">
                        <div class="no-sub-theater-nol-inner">

                            <div class="no-sub-theater-txt__title" <?= AOS_TITLE ?>>
                                <h2 class="f-heading-1 --bold">NOL 씨어터 대학로</h2>
                            </div>
                            <div class="no-sub-theater-txt__desc" <?= AOS_DEFAULT ?>>
                                <h3 class="f-body-1 --bold">
                                    2026년 새롭게 문을 여는 본 공연장은 935석 규모의 대극장과 <br>
                                    490석 규모의 중극장으로 구성된 대학로 최대 규모의 공연장입니다.
                                </h3>
                                <p class="f-body-2 --regular">
                                    대학로 유일의 대형 공연장으로서, 수준 높은 무대 인프라와 전문 운영 시스템을 바탕으로 연극 및 뮤지컬 공연 장르에 최적화된 환경을 제공합니다.
                                </p>
                                <p class="f-body-2 --regular">
                                    <b>한국의 브로드웨이</b>로 불리는 대학로 공연시장의 중심에 위치해 뛰어난 접근성과 입지를 자랑하며,
                                    한국 공연산업의 성장과 새로운 흐름을 주도하며 공연문화의 내일을 함께 그려갑니다.
                                    대학로 문화예술의 새로운 중심, NOL 씨어터 대학로입니다.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="no-sub-theater-nol ">
                    <div class="no-sub-theater-nol-inner">
                        <div class="no-sub-theater-nol-txt">
                            <div class="no-sub-theater-nol-txt__title" <?= AOS_TITLE ?>>
                                <span class="--bold">
                                    운영사
                                </span>
                                <h2 class="f-heading-1 --bold">NOL theater</h2>
                            </div>
                            <div class="no-sub-theater-nol-txt__desc" <?= AOS_DEFAULT ?>>
                                <p class="f-body-1 --bold">
                                    놀씨어터는 공연 투자 및 티켓 유통 영역에서 공연 시장의 성장을 이끌어 온 놀유니버스가
                                    미래 공연 산업 발전에 기여하기 위해 설립한 국내 유일의 공연장 운영 법인입니다.
                                </p>
                                <p class="f-body-2 --regular">
                                    놀씨어터는 뮤지컬과 콘서트 공연장으로 사랑받고 있는 서울 블루스퀘어와 NOL 씨어터 대학로, NOL 씨어터 합정, 코엑스아티움 및 부산 소향씨어터를
                                    운영하며
                                    우리나라 공연계의 균형 잡힌 발전에 기여하고 있습니다.

                                </p>
                                <p class="f-body-2 --regular">
                                    앞으로도 제작사에게는 작품의 퀄리티가 향상될 수 있는 최적의 제작 환경을, 관객 여러분들께는 감동 가득한 공연과 쾌적한 관람 환경을 제공할 것을
                                    약속드리며, 전국 각지의 공연장 운영을 통해 우리나라 공연계의 발전을 견인해 나가겠습니다.

                                </p>
                            </div>
                        </div>
                        <!--
                        <div class="no-sub-theater-nol__rolling">
                            <div class="no-sub-theater-nol__rolling-inner">
                                <div class="no-sub-theater-nol__rolling-list no-sub-theater-nol__rolling-list--left">
                                    <?php for ($i = 1; $i <= 3; $i++): ?>
                                    <div class="no-sub-theater-nol__rolling-item">
                                        <figure class="no-sub-theater-nol__rolling-img">
                                            <img src="<?= base_path('/resource/images/sub/sub_theater_rolling_img_' . $i . '.png') ?>"
                                                alt="NOL theater">
                                        </figure>
                                    </div>
                                    <?php endfor; ?>
                                </div>
                                <div class="no-sub-theater-nol__rolling-list no-sub-theater-nol__rolling-list--right">
                                    <?php for ($i = 4; $i <= 6; $i++): ?>
                                    <div class="no-sub-theater-nol__rolling-item">
                                        <figure class="no-sub-theater-nol__rolling-img">
                                            <img src="<?= base_path('/resource/images/sub/sub_theater_rolling_img_' . $i . '.png') ?>"
                                                alt="NOL theater">
                                        </figure>
                                    </div>
                                    <?php endfor; ?>
                                </div>
                            </div>
                        </div>-->
                        <div class="no-sub-theater-nol-banner">
                            <img src="<?= base_path('/resource/images/sub/nol-banner.png') ?>" alt="NOL 씨어터 대학로 배너">
                        </div>
                    </div>
                </div>

                <!-- 브랜드 가이드 섹션 -->
                <div class="no-sub-theater-brand">
                    <div class="no-sub-theater-brand__block">
                        <div class="no-sub-theater-brand__content" <?= AOS_TITLE ?>>
                            <h2 class="f-heading-1 --bold">NOL 씨어터 대학로</h2>
                            <a href="<?= base_path('/resource/file/NOL_BI.zip') ?>" class="no-button-download" download>
                                <span>BI 가이드 다운로드</span>
                                <i class="fa-regular fa-download" aria-hidden="true"></i>
                            </a>
                        </div>
                        <div class="no-sub-theater-brand__visual" <?= AOS_DEFAULT ?>>

                            <div class="no-sub-theater-brand__logo ">

                                <div class="no-sub-theater-brand__logo-row">
                                    <figure class="no-sub-theater-brand__logo-img">
                                        <img src="<?= base_path('/resource/images/logo/BI_img_2.png') ?>"
                                            alt="NOL 씨어터 대학로 로고">
                                    </figure>
                                </div>
                                <div class="no-sub-theater-brand__logo-row">
                                    <figure class="no-sub-theater-brand__logo-img">
                                        <img src="<?= base_path('/resource/images/logo/BI_img_1.png') ?>"
                                            alt="NOL 씨어터 대학로 로고">
                                    </figure>
                                </div>

                            </div>
                        </div>
                    </div>

                    <div class="no-sub-theater-brand__block">
                        <div class="no-sub-theater-brand__content" <?= AOS_TITLE ?>>
                            <h2 class="f-heading-1 --bold">NOL 씨어터</h2>
                            <a href="<?= base_path('/resource/file/NOL_CI.zip') ?>" class="no-button-download" download>
                                <span>CI 가이드 다운로드</span>
                                <i class="fa-regular fa-download" aria-hidden="true"></i>
                            </a>
                        </div>
                        <div class="no-sub-theater-brand__visual" <?= AOS_DEFAULT ?>>

                            <div class="no-sub-theater-brand__logo">
                                <div class="no-sub-theater-brand__logo-row">
                                    <figure class="no-sub-theater-brand__logo-img">
                                        <img src="<?= base_path('/resource/images/logo/CI_img_1.png') ?>"
                                            alt="NOL 씨어터 대학로 로고">
                                    </figure>
                                </div>

                            </div>
                            <div class="no-sub-theater-brand__colors">
                                <div class="no-sub-theater-brand__color-item no-sub-theater-brand__color-item--main">
                                    <div class="no-sub-theater-brand__color-info">
                                        <span class="no-sub-theater-brand__color-label f-heading-4 --bold">MAIN
                                            COLOR</span>
                                    </div>
                                    <div class="no-sub-theater-brand__color-values">
                                        <span class="f-body-2 --regular">R 53 / G 73 / B 255</span>
                                        <span class="f-body-2 --regular">C 79 M 71 Y 0 K 0</span>
                                    </div>
                                </div>
                            </div>
                        </div>
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