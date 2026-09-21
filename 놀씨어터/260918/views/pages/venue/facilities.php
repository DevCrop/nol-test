<?php section('content') ?>
<?php
// 이미지 캐시 버스팅을 위한 오늘 날짜
$cacheDate = date('Ymd');
?>

<div class=" no-section-md">
    <?= include_view('components.sub-visual', ['title' => '공연 시설']) ?>

    <section class="no-sub-ui">
        <div class="no-container-xl">
            <div class="no-sub-ui-inner">
                <aside class="left no-sub-ui-left" <?= AOS_DEFAULT ?>>
                    <div class="no-sub-ui-left-swiper">
                        <div class="swiper no-sub-ui-tabs-swiper">
                            <ul class="swiper-wrapper no-sub-ui-tabs">
                                <li class="swiper-slide no-sub-ui-tab is-active" data-target="woori-card">
                                    <button type="button" class="no-sub-ui-tab__btn">
                                        <span class="no-sub-ui-tab__bullet"></span>
                                        <span class="no-sub-ui-tab__label">우리카드홀</span>
                                    </button>
                                </li>
                                <li class="swiper-slide no-sub-ui-tab" data-target="woori-securities">
                                    <button type="button" class="no-sub-ui-tab__btn">
                                        <span class="no-sub-ui-tab__bullet"></span>
                                        <span class="no-sub-ui-tab__label">우리투자증권홀</span>
                                    </button>
                                </li>
                                <li class="swiper-slide no-sub-ui-tab" data-target="practice">
                                    <button type="button" class="no-sub-ui-tab__btn">
                                        <span class="no-sub-ui-tab__bullet"></span>
                                        <span class="no-sub-ui-tab__label">리허설룸 </span>
                                    </button>
                                </li>
                            </ul>
                        </div>
                    </div>
                </aside>
                <div class="no-sub-ui-right-wrap" <?= AOS_MEDIUM ?>>
                    <div class="right no-sub-ui-right">
                        <div class="no-sub-ui-panel is-active" data-panel="woori-card">
                            <div class="no-sub-ui-panel-inner">
                                <div class="no-sub-ui-panel-block">
                                    <div class="no-sub-ui-txt">
                                        <div class="no-sub-ui-txt-header">
                                            <div class="no-sub-ui-title">
                                                <h2 class="f-heading-4 --bold">우리카드홀</h2>
                                            </div>
                                            <?= include_view('components.button', [
                                                'text' => '대관안내 바로가기',
                                                'href' => route('rental.procedure'),
                                                'bgColor' => 'var(--clr-primary-def)',
                                                'border' => 'none',
                                                'icon' => 'fa-regular fa-arrow-right',
                                                'iconBg' => 'var(--clr-ui-white)',
                                                'iconColor' => 'var(--clr-primary-def)',
                                            ]) ?>
                                        </div>
                                        <div class="no-sub-ui-desc">
                                            <p class="--regular clr-text-body">
                                                935석 규모의 우리카드홀은 공연 경험 전반을 새롭게 재구성한 프로시니엄 무대 공연장입니다. 작품의 디테일과 감정선을 섬세하게
                                                전달하며,
                                                관객이
                                                공연에
                                                온전히 몰입할 수 있는 환경을 구현했습니다. 관객 동선과 시야를 고려한 좌석 설계로 어느 위치에서도 무대의 흐름과 에너지를
                                                생생하게
                                                감상할 수
                                                있으며,
                                                장시간 관람에도 부담 없는 쾌적함을 제공합니다. 뮤지컬을 비롯한 다양한 대규모 공연에 최적화된 이 공간은, 무대와 객석의 거리를
                                                좁혀
                                                예술가의
                                                호흡과
                                                감동이 직접 전달되는 새로운 공연 경험을 선사합니다.
                                            </p>
                                        </div>
                                    </div>

                                    <div class="--info-box">
                                        <ul>
                                            <li>
                                                <span>
                                                    용도
                                                </span>
                                                <p>
                                                    <span>
                                                        연극, 뮤지컬 전용 공연장
                                                    </span>
                                                </p>
                                            </li>
                                            <li>
                                                <span>
                                                    좌석
                                                </span>
                                                <p>
                                                    <span>
                                                        총 좌석 수 935석 / 1층 618석 / 2층 317석
                                                    </span>
                                                </p>
                                            </li>
                                            <li>
                                                <span>
                                                    부대시설
                                                </span>
                                                <p>
                                                    <span>
                                                        단체분장실, 개인분장실, 세탁실 등
                                                    </span>
                                                </p>
                                            </li>
                                        </ul>
                                    </div>
                                    <div class="no-sub-ui-swiper-wrapper">
                                        <div class="swiper no-sub-ui-swiper">
                                            <ul class="swiper-wrapper">
                                                <?php for ($i = 1; $i <= 4; $i++): ?>
                                                <li class="swiper-slide">
                                                    <figure>
                                                        <img src="<?= base_path('/resource/images/sub/woori_hall_img_' . $i . '.png?v=' . $cacheDate) ?>"
                                                            alt="우리카드홀 이미지 <?= $i ?>"
                                                            loading="<?= $i === 1 ? 'eager' : 'lazy' ?>"
                                                            decoding="async"
                                                            <?= $i === 1 ? 'fetchpriority="high"' : '' ?>>
                                                    </figure>
                                                </li>
                                                <?php endfor; ?>
                                            </ul>
                                        </div>
                                        <div class="no-sub-ui-swiper-nav">
                                            <button type="button" class="no-sub-ui-swiper-nav__prev" aria-label="이전">
                                                <i class="fa-regular fa-chevron-left"></i>
                                            </button>
                                            <button type="button" class="no-sub-ui-swiper-nav__next" aria-label="다음">
                                                <i class="fa-regular fa-chevron-right"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="no-sub-ui-panel-block">
                                    <div class="no-sub-ui-seat">
                                        <div class="no-sub-ui-seat__title">
                                            <h3 class="f-heading-4 --bold">좌석배치도</h3>
                                        </div>
                                        <ul class="no-sub-ui-seat__list">
                                            <li class="no-sub-ui-seat__item">
                                                <span class="no-sub-ui-seat__label f-body-1 --medium">객석 1F 좌석 수</span>
                                                <span class="no-sub-ui-seat__value f-body-1 --regular">618석 (오케스트라 피트석
                                                    17석 /
                                                    휠체어석 10석 포함)</span>
                                            </li>
                                            <li class="no-sub-ui-seat__item">
                                                <span class="no-sub-ui-seat__label f-body-1 --medium">객석 2F 좌석 수</span>
                                                <span class="no-sub-ui-seat__value f-body-1 --regular">317석</span>
                                            </li>
                                        </ul>
                                    </div>
                                    <div class="no-sub-ui-seat-img">
                                        <figure>
                                            <div class="no-theme-change">
                                                <img src="<?= base_path('/resource/images/sub/card_chart_img_white.png?v=' . $cacheDate) ?>"
                                                    alt="우리카드홀 좌석배치도" class="dark" loading="lazy" decoding="async">
                                                <img src="<?= base_path('/resource/images/sub/card_chart_img_color.png?v=' . $cacheDate) ?>"
                                                    alt="우리카드홀 좌석배치도" class="light" loading="lazy" decoding="async">
                                            </div>
                                        </figure>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="no-sub-ui-panel" data-panel="woori-securities">
                            <div class="no-sub-ui-panel-inner">
                                <div class="no-sub-ui-panel-block">
                                    <div class="no-sub-ui-txt">
                                        <div class="no-sub-ui-txt-header">
                                            <div class="no-sub-ui-title">
                                                <h2 class="f-heading-4 --bold">우리투자증권홀</h2>
                                            </div>
                                            <?= include_view('components.button', [
                                                'text' => '대관안내 바로가기',
                                                'href' => route('rental.procedure'),
                                                'bgColor' => 'var(--clr-primary-def)',
                                                'border' => 'none',
                                                'icon' => 'fa-regular fa-arrow-right',
                                                'iconBg' => 'var(--clr-ui-white)',
                                                'iconColor' => 'var(--clr-primary-def)',
                                            ]) ?>
                                        </div>
                                        <div class="no-sub-ui-desc">
                                            <p class="--regular clr-text-body">
                                                490석 규모의 우리투자증권홀은 관객과 예술가의 거리를 최소화한 친밀한 공연 공간입니다.
                                                무대와 객석의 긴밀한 관계를 바탕으로, 배우의 움직임과 감정의 결까지 섬세하게 전달합니다. 객석 단차를 강화해 시야 간섭을
                                                최소화하고,
                                                정교한 음향·조명 시스템을 통해 작품의 밀도와 표현력을 극대화했습니다. 연극과 창작 공연 등 중·소규모 작품에 최적화된 이
                                                공간은,
                                                창의적인 예술 실험과 깊이 있는 관객 경험을 동시에 완성합니다.
                                            </p>
                                        </div>
                                    </div>

                                    <div class="--info-box">
                                        <ul>
                                            <li>
                                                <span>
                                                    용도
                                                </span>
                                                <p>
                                                    <span>
                                                        연극, 뮤지컬 전용 공연장
                                                    </span>
                                                </p>
                                            </li>
                                            <li>
                                                <span>
                                                    좌석
                                                </span>
                                                <p>
                                                    <span>
                                                        총 좌석 수 490석 / 1층 337석 / 2층 153석
                                                    </span>
                                                </p>
                                            </li>
                                            <li>
                                                <span>
                                                    부대시설
                                                </span>
                                                <p>
                                                    <span>
                                                        단체분장실, 개인분장실, 세탁실 등
                                                    </span>
                                                </p>
                                            </li>
                                        </ul>
                                    </div>
                                    <div class="no-sub-ui-swiper-wrapper">
                                        <div class="swiper no-sub-ui-swiper">
                                            <ul class="swiper-wrapper">
                                                <?php for ($i = 1; $i <= 3; $i++): ?>
                                                <li class="swiper-slide">
                                                    <figure>
                                                        <img src="<?= base_path('/resource/images/sub/woori_finance_img_' . $i . '.png?v=' . $cacheDate) ?>"
                                                            alt="우리투자증권홀 이미지 <?= $i ?>"
                                                            loading="<?= $i === 1 ? 'eager' : 'lazy' ?>"
                                                            decoding="async"
                                                            <?= $i === 1 ? 'fetchpriority="high"' : '' ?>>
                                                    </figure>
                                                </li>
                                                <?php endfor; ?>
                                            </ul>
                                        </div>
                                        <div class="no-sub-ui-swiper-nav">
                                            <button type="button" class="no-sub-ui-swiper-nav__prev" aria-label="이전">
                                                <i class="fa-regular fa-chevron-left"></i>
                                            </button>
                                            <button type="button" class="no-sub-ui-swiper-nav__next" aria-label="다음">
                                                <i class="fa-regular fa-chevron-right"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="no-sub-ui-panel-block">
                                    <div class="no-sub-ui-seat">
                                        <div class="no-sub-ui-seat__title">
                                            <h3 class="f-heading-4 --bold">좌석배치도</h3>
                                        </div>
                                        <ul class="no-sub-ui-seat__list">
                                            <li class="no-sub-ui-seat__item">
                                                <span class="no-sub-ui-seat__label f-body-1 --medium">객석 1F 좌석 수</span>
                                                <span class="no-sub-ui-seat__value f-body-1 --regular">337석 (휠체어석 5석
                                                    포함)</span>
                                            </li>
                                            <li class="no-sub-ui-seat__item">
                                                <span class="no-sub-ui-seat__label f-body-1 --medium">객석 2F 좌석 수</span>
                                                <span class="no-sub-ui-seat__value f-body-1 --regular">153석</span>
                                            </li>
                                        </ul>
                                    </div>
                                    <div class="no-sub-ui-seat-img">
                                        <figure>
                                            <div class="no-theme-change">
                                                <img src="<?= base_path('/resource/images/sub/finance_chart_img_white.png?v=' . $cacheDate) ?>"
                                                    alt="우리투자증권홀 좌석배치도" class="dark" loading="lazy" decoding="async">
                                                <img src="<?= base_path('/resource/images/sub/finance_chart_img_color.png?v=' . $cacheDate) ?>"
                                                    alt="우리투자증권홀 좌석배치도" class="light" loading="lazy" decoding="async">
                                            </div>
                                        </figure>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="no-sub-ui-panel" data-panel="practice">
                            <div class="no-sub-ui-panel-inner">
                                <div class="no-sub-ui-panel-block">
                                    <div class="no-sub-ui-txt">
                                        <div class="no-sub-ui-txt-header">
                                            <div class="no-sub-ui-title">
                                                <h2 class="f-heading-4 --bold">리허설룸</h2>
                                            </div>
                                            <?= include_view('components.button', [
                                                'text' => '대관안내 바로가기',
                                                'href' => route('rental.procedure'),
                                                'bgColor' => 'var(--clr-primary-def)',
                                                'border' => 'none',
                                                'icon' => 'fa-regular fa-arrow-right',
                                                'iconBg' => 'var(--clr-ui-white)',
                                                'iconColor' => 'var(--clr-primary-def)',
                                            ]) ?>
                                        </div>
                                        <div class="no-sub-ui-desc">
                                            <p class="--regular clr-text-body">
                                                리허설룸1(12*10m)과 리허설룸2(11*9m)로 구성된 연습실은 공연 제작 전반에 활용되는 연습 공간입니다.
                                                뮤지컬과 연극 리허설을 비롯해 리딩과 워크숍 등 작품의 초기 단계 작업이 가능하도록 구성되었습니다.
                                                공연 환경을 고려한 공간 설계와 안정적인 시설을 통해, 창작자가 작업에 집중할 수 있는 환경을 제공합니다.
                                            </p>
                                        </div>
                                    </div>



                                    <div class="no-sub-ui-swiper-block">

                                        <div class="--info-box">
                                            <ul>
                                                <li>
                                                    <span>
                                                        4F 리허설룸1
                                                    </span>
                                                    <p>
                                                        <span>
                                                            연극, 뮤지컬 등 각종 연습, 오디션 등
                                                        </span>
                                                    </p>
                                                </li>
                                            </ul>
                                        </div>
                                        <div class="no-sub-ui-swiper-wrapper">
                                            <div class="swiper no-sub-ui-swiper">
                                                <ul class="swiper-wrapper">
                                                    <?php
                                                    $practiceImages = [
                                                        'practice_big_img_01',
                                                        'practice_big_img_02',

                                                    ];
                                                    $index = 0;
                                                    foreach ($practiceImages as $img):
                                                        $index++;
                                                    ?>
                                                    <li class="swiper-slide">
                                                        <figure>
                                                            <img src="<?= base_path('/resource/images/sub/' . $img . '.png?v=' . $cacheDate) ?>"
                                                                alt="4F 리허설룸1 이미지 <?= $index ?>"
                                                                loading="<?= $index === 1 ? 'eager' : 'lazy' ?>"
                                                                decoding="async"
                                                                <?= $index === 1 ? 'fetchpriority="high"' : '' ?>>
                                                        </figure>
                                                    </li>
                                                    <?php endforeach; ?>
                                                </ul>
                                            </div>
                                            <div class="no-sub-ui-swiper-nav">
                                                <button type="button" class="no-sub-ui-swiper-nav__prev"
                                                    aria-label="이전">
                                                    <i class="fa-regular fa-chevron-left"></i>
                                                </button>
                                                <button type="button" class="no-sub-ui-swiper-nav__next"
                                                    aria-label="다음">
                                                    <i class="fa-regular fa-chevron-right"></i>
                                                </button>
                                            </div>
                                        </div>

                                    </div>
                                    <div class="no-sub-ui-swiper-block">

                                        <div class="--info-box">
                                            <ul>
                                                <li>
                                                    <span>
                                                        5F 리허설룸2
                                                    </span>
                                                    <p>
                                                        <span>
                                                            연극, 뮤지컬 등 각종 연습, 오디션 등
                                                        </span>
                                                    </p>
                                                </li>
                                            </ul>
                                        </div>
                                        <div class="no-sub-ui-swiper-wrapper">
                                            <div class="swiper no-sub-ui-swiper">
                                                <ul class="swiper-wrapper">
                                                    <?php
                                                    $practiceImages = [
                                                        'practice_small_img_1',
                                                        'practice_small_img_2',

                                                    ];
                                                    $index = 0;
                                                    foreach ($practiceImages as $img):
                                                        $index++;
                                                    ?>
                                                    <li class="swiper-slide">
                                                        <figure>
                                                            <img src="<?= base_path('/resource/images/sub/' . $img . '.png?v=' . $cacheDate) ?>"
                                                                alt="5F 리허설룸2 이미지 <?= $index ?>" loading="lazy"
                                                                decoding="async">
                                                        </figure>
                                                    </li>
                                                    <?php endforeach; ?>
                                                </ul>
                                            </div>
                                            <div class="no-sub-ui-swiper-nav">
                                                <button type="button" class="no-sub-ui-swiper-nav__prev"
                                                    aria-label="이전">
                                                    <i class="fa-regular fa-chevron-left"></i>
                                                </button>
                                                <button type="button" class="no-sub-ui-swiper-nav__next"
                                                    aria-label="다음">
                                                    <i class="fa-regular fa-chevron-right"></i>
                                                </button>
                                            </div>
                                        </div>
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