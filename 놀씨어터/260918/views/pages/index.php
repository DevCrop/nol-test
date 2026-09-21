<?php section('content') ?>

<section class="no-main-hero">
    <div class="no-main-hero-container">
        <div class="no-main-hero-inner">
            <div class="no-main-hero-swiper">
                <ul class="swiper-wrapper">
                    <?php
                    // 라우트에서 전달받은 banners 데이터
                    $banners = $banners ?? [];

                    // 배너 이미지 경로 처리 함수
                    if (!function_exists('processBannerImagePath')) {
                        function processBannerImagePath($image)
                        {
                            if (empty($image)) {
                                return base_path('/resource/images/main/main_hero.jpg');
                            }
                            // http로 시작하는 외부 URL이거나 /로 시작하는 절대 경로는 그대로 사용
                            if (strpos($image, 'http') === 0 || strpos($image, '/') === 0) {
                                return $image;
                            }
                            // 상대 경로면 /uploads/banners/ 경로로 처리
                            return '/uploads/banners/' . $image;
                        }
                    }
                    ?>
                    <?php if (count($banners) > 0): ?>
                    <?php foreach ($banners as $banner): ?>
                    <li class="swiper-slide">
                        <?php $safeBannerUrl = safe_url($banner['link_url'] ?? '', ['http', 'https'], true); ?>
                        <?php if (!empty($banner['has_link']) && $safeBannerUrl !== ''): ?>
                        <a href="<?= e($safeBannerUrl) ?>"
                            target="<?= e(($banner['target'] ?? '_self')) ?>">
                            <?php else: ?>
                            <a href="#">
                                <?php endif; ?>
                                <figure class="no-main-hero-img">
                                    <?php
                                            $bannerImageDesktop = $banner['banner_image'] ?? '';
                                            $bannerImageMobile = $banner['banner_image_mobile'] ?? '';

                                            $desktopPath = processBannerImagePath($bannerImageDesktop);
                                            $mobilePath = !empty($bannerImageMobile) ? processBannerImagePath($bannerImageMobile) : $desktopPath;
                                            ?>
                                    <picture>
                                        <?php if (!empty($bannerImageMobile)): ?>
                                        <source media="(max-width: 768px)"
                                            srcset="<?= htmlspecialchars($mobilePath) ?>">
                                        <?php endif; ?>
                                        <img src="<?= htmlspecialchars($desktopPath) ?>"
                                            alt="<?= htmlspecialchars($banner['title']) ?>" loading="eager"
                                            decoding="async" fetchpriority="high">
                                    </picture>
                                </figure>
                                <?php if (!empty($banner['title']) || !empty($banner['hall_name']) || !empty($banner['period'])): ?>
                                <div class="no-main-hero-content">
                                    <div class="no-main-hero-txt">
                                        <?php if (!empty($banner['title'])): ?>
                                        <h2 class="f-heading-1 --bold">
                                            <?= htmlspecialchars($banner['title']) ?>
                                        </h2>
                                        <?php endif; ?>
                                        <?php if (!empty($banner['hall_name']) || !empty($banner['period'])): ?>
                                        <p class="f-body-2 --medium">
                                            <?php if (!empty($banner['hall_name'])): ?>
                                            <span><?= htmlspecialchars($banner['hall_name']) ?></span>
                                            <?php endif; ?>
                                            <?php if (!empty($banner['hall_name']) && !empty($banner['period'])): ?>
                                            <span class="--divider"></span>
                                            <?php endif; ?>
                                            <?php if (!empty($banner['period'])): ?>
                                            <span><?= htmlspecialchars($banner['period']) ?></span>
                                            <?php endif; ?>
                                        </p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <?php endif; ?>
                            </a>
                    </li>
                    <?php endforeach; ?>
                    <?php else: ?>
                    <li class="swiper-slide">
                        <a href="#">
                            <figure class="no-main-hero-img">
                                <img src="<?= base_path('/resource/images/main/main_hero.jpg') ?>" alt=""
                                    loading="eager" decoding="async" fetchpriority="high">
                            </figure>
                        </a>
                    </li>
                    <?php endif; ?>
                </ul>

            </div>
            <div class="no-main-hero-swiper-pagination">
                <div class="no-main-hero-swiper-progress">
                    <div class="no-main-hero-swiper-progress__bar" id="heroProgressBar"></div>
                </div>
                <div class="no-main-hero-swiper-pagination-wrap">
                    <span class="no-main-hero-swiper-pagination__current">1</span>
                    <span class="no-main-hero-swiper-pagination__separator">/</span>
                    <span
                        class="no-main-hero-swiper-pagination__total"><?= count($banners) > 0 ? count($banners) : 1 ?></span>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="no-main-whatson no-section-md">
    <div class="no-container-2xl">
        <span class="f-display-0 --bold no-main-whatson-deco">
            WHAT'S ON
        </span>
        <div class="no-main-whatson-inner">
            <div class="no-main-whatson-left" <?= AOS_DEFAULT ?>>
                <div class="no-section__head">
                    <h2 class="f-display-1 --bold --fm-en">WHAT'S ON</h2>
                    <?= include_view('components.button', [
                        'text' => 'View More',
                        'href' => '/whatson',
                        'bgColor' => 'var(--clr-primary-def)',
                        'border' => 'none',
                        'icon' => 'fa-regular fa-arrow-right',
                        'iconBg' => 'var(--clr-ui-white)',
                        'iconColor' => 'var(--clr-primary-def)',
                    ]) ?>
                </div>
                <div class="no-main-whatson-pagination">
                    <button type="button" class="no-main-whatson-pagination__prev" aria-label="이전" disabled>
                        <i class="fa-regular fa-chevron-left"></i>
                    </button>
                    <button type="button" class="no-main-whatson-pagination__next" aria-label="다음">
                        <i class="fa-regular fa-chevron-right"></i>
                    </button>
                    <div class="no-main-whatson-pagination__indicator">
                        <span class="no-main-whatson-pagination__current">01</span>
                        <span class="no-main-whatson-pagination__separator">/</span>
                        <span
                            class="no-main-whatson-pagination__total"><?= str_pad(count($works ?? []), 2, '0', STR_PAD_LEFT) ?></span>
                    </div>
                </div>
            </div>
            <div class="no-main-whatson-right" <?= AOS_MEDIUM ?>>
                <div class="swiper no-main-whatson-swiper">
                    <ul class="swiper-wrapper">
                        <?php
                        // 라우트에서 전달받은 works 데이터
                        $works = $works ?? [];
                        ?>
                        <?php if (count($works) > 0): ?>
                        <?php foreach ($works as $index => $work): ?>
                        <li class="swiper-slide">
                            <a href="<?= route('whatson.view') ?>?id=<?= $work['id'] ?>">
                                <figure class="no-main-whatson-img">
                                    <img src="<?= htmlspecialchars($work['thumb_image']) ?>"
                                        alt="<?= htmlspecialchars($work['title']) ?>"
                                        loading="<?= $index === 0 ? 'eager' : 'lazy' ?>" decoding="async"
                                        <?= $index === 0 ? 'fetchpriority="high"' : '' ?>>
                                </figure>
                                <div class="no-main-whatson-contents">
                                    <div class="no-main-whatson-contents__info">
                                        <?php if (!empty($work['venue_name'])): ?>
                                        <span
                                            class="f-body-2 --regular"><?= htmlspecialchars($work['venue_name']) ?></span>
                                        <?php endif; ?>
                                        <?php if (!empty($work['venue_name']) && !empty($work['period'])): ?>
                                        <?php endif; ?>
                                        <?php if (!empty($work['period'])): ?>
                                        <span class="f-body-2 --regular"><?= htmlspecialchars($work['period']) ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <h2 class="f-heading-5 --bold">
                                        <?= htmlspecialchars($work['title']) ?>
                                    </h2>
                                </div>
                            </a>
                        </li>
                        <?php endforeach; ?>
                        <?php else: ?>
                        <li class="swiper-slide">
                            <div class="no-main-whatson-contents">
                                <h2 class="f-heading-5 --bold">등록된 공연이 없습니다.</h2>
                            </div>
                        </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>

        </div>
    </div>

</section>

<section class="no-main-visit-guide no-section-md">
    <div class="no-container-2xl">
        <div class="no-section__head" <?= AOS_TITLE ?>>
            <h2 class="f-display-1 --bold --fm-en --tac">VISIT GUIDE</h2>
        </div>
        <div class="--cnt">
            <ul class="no-main-visit-guide__row">
                <li class="no-main-visit-guide__item" <?= AOS_DEFAULT ?>>
                    <a href="/venue/facilities?tab=woori-card" class="no-main-visit-guide__link">
                        <div class="no-main-visit-guide__bg">
                            <div class="no-theme-change">
                                <img src="<?= base_path('/resource/images/main/main_service_hover_bg_color.png') ?>"
                                    alt="" class="dark" loading="lazy" decoding="async">
                                <img src="<?= base_path('/resource/images/main/main_service_hover_bg_white.png') ?>"
                                    alt="" class="light" loading="lazy" decoding="async">
                            </div>
                        </div>
                        <div class="no-main-visit-guide__text">
                            <span class="f-body-3 --fm-en">Woori Card Hall Seating Plan</span>
                            <span class="f-heading-5 --bold">우리카드홀 좌석배치도</span>
                        </div>
                        <div class="no-main-visit-guide__icon">
                            <div class="no-theme-change">
                                <img src="<?= base_path('/resource/images/icon/main_visit_guide_icon_1.png') ?>"
                                    alt="우리카드홀 좌석배치도" class="dark" loading="lazy" decoding="async">
                                <img src="<?= base_path('/resource/images/icon/main_visit_guide_icon_1_color.png') ?>"
                                    alt="우리카드홀 좌석배치도" class="light" loading="lazy" decoding="async">
                            </div>
                        </div>
                    </a>
                </li>
                <li class="no-main-visit-guide__item" <?= AOS_DEFAULT ?>>
                    <a href="/venue/facilities?tab=woori-securities" class="no-main-visit-guide__link">
                        <div class="no-main-visit-guide__bg">
                            <div class="no-theme-change">
                                <img src="<?= base_path('/resource/images/main/main_service_hover_bg_color.png') ?>"
                                    alt="" class="dark" loading="lazy" decoding="async">
                                <img src="<?= base_path('/resource/images/main/main_service_hover_bg_white.png') ?>"
                                    alt="" class="light" loading="lazy" decoding="async">
                            </div>
                        </div>
                        <div class="no-main-visit-guide__text">
                            <span class="f-body-3 --fm-en">Woori Investment Securities Hall Seating
                                Plan</span>
                            <span class="f-heading-5 --bold">우리투자증권홀 좌석배치도</span>
                        </div>
                        <div class="no-main-visit-guide__icon">
                            <div class="no-theme-change">
                                <img src="<?= base_path('/resource/images/icon/main_visit_guide_icon_2.png') ?>"
                                    alt="우리투자증권홀 좌석배치도" class="dark" loading="lazy" decoding="async">
                                <img src="<?= base_path('/resource/images/icon/main_visit_guide_icon_2_color.png') ?>"
                                    alt="우리투자증권홀 좌석배치도" class="light" loading="lazy" decoding="async">
                            </div>
                        </div>
                    </a>
                </li>
                <li class="no-main-visit-guide__item" <?= AOS_DEFAULT ?>>
                    <a href="/venue/floors" class="no-main-visit-guide__link">
                        <div class="no-main-visit-guide__bg">
                            <div class="no-theme-change">
                                <img src="<?= base_path('/resource/images/main/main_service_hover_bg_color.png') ?>"
                                    alt="" class="dark" loading="lazy" decoding="async">
                                <img src="<?= base_path('/resource/images/main/main_service_hover_bg_white.png') ?>"
                                    alt="" class="light" loading="lazy" decoding="async">
                            </div>
                        </div>
                        <div class="no-main-visit-guide__text">
                            <span class="f-body-3 --fm-en">Floor Guide</span>
                            <span class="f-heading-5 --bold">층별 안내</span>
                        </div>
                        <div class="no-main-visit-guide__icon">
                            <div class="no-theme-change">
                                <img src="<?= base_path('/resource/images/icon/main_visit_guide_icon_3.png') ?>"
                                    alt="층별 안내" class="dark" loading="lazy" decoding="async">
                                <img src="<?= base_path('/resource/images/icon/main_visit_guide_icon_3_color.png') ?>"
                                    alt="층별 안내" class="light" loading="lazy" decoding="async">
                            </div>
                        </div>
                    </a>
                </li>
                <li class="no-main-visit-guide__item" <?= AOS_DEFAULT ?>>
                    <a href="/venue/services" class="no-main-visit-guide__link">
                        <div class="no-main-visit-guide__bg">
                            <div class="no-theme-change">
                                <img src="<?= base_path('/resource/images/main/main_service_hover_bg_color.png') ?>"
                                    alt="" class="dark" loading="lazy" decoding="async">
                                <img src="<?= base_path('/resource/images/main/main_service_hover_bg_white.png') ?>"
                                    alt="" class="light" loading="lazy" decoding="async">
                            </div>
                        </div>
                        <div class="no-main-visit-guide__text">
                            <span class="f-body-3 --fm-en">Facilities & Services</span>
                            <span class="f-heading-5 --bold">편의시설 및 서비스</span>
                        </div>
                        <div class="no-main-visit-guide__icon">
                            <div class="no-theme-change">
                                <img src="<?= base_path('/resource/images/main/main_visit_guide_icon_8.png') ?>"
                                    alt="광고·캠페인" class="dark" loading="lazy" decoding="async">
                                <img src="<?= base_path('/resource/images/main/main_visit_guide_icon_8_color.png') ?>"
                                    alt="광고·캠페인" class="light" loading="lazy" decoding="async">
                            </div>
                        </div>

                    </a>
                </li>

                <li class="no-main-visit-guide__item" <?= AOS_DEFAULT ?>>
                    <a href="/customer/faq" class="no-main-visit-guide__link">
                        <div class="no-main-visit-guide__bg">
                            <div class="no-theme-change">
                                <img src="<?= base_path('/resource/images/main/main_service_hover_bg_color.png') ?>"
                                    alt="" class="dark" loading="lazy" decoding="async">
                                <img src="<?= base_path('/resource/images/main/main_service_hover_bg_white.png') ?>"
                                    alt="" class="light" loading="lazy" decoding="async">
                            </div>
                        </div>
                        <div class="no-main-visit-guide__text">
                            <span class="f-body-3 --fm-en">Frequently Asked Questions</span>
                            <span class="f-heading-5 --bold">FAQ</span>
                        </div>
                        <div class="no-main-visit-guide__icon">
                            <div class="no-theme-change">
                                <img src="<?= base_path('/resource/images/icon/main_visit_guide_icon_4.png') ?>"
                                    alt="FAQ" class="dark" loading="lazy" decoding="async">
                                <img src="<?= base_path('/resource/images/icon/main_visit_guide_icon_4_color.png') ?>"
                                    alt="FAQ" class="light" loading="lazy" decoding="async">
                            </div>
                        </div>
                    </a>
                </li>

                <li class="no-main-visit-guide__item" <?= AOS_DEFAULT ?>>
                    <a href="/customer/directions" class="no-main-visit-guide__link">
                        <div class="no-main-visit-guide__bg">
                            <div class="no-theme-change">
                                <img src="<?= base_path('/resource/images/main/main_service_hover_bg_color.png') ?>"
                                    alt="" class="dark" loading="lazy" decoding="async">
                                <img src="<?= base_path('/resource/images/main/main_service_hover_bg_white.png') ?>"
                                    alt="" class="light" loading="lazy" decoding="async">
                            </div>
                        </div>
                        <div class="no-main-visit-guide__text">
                            <span class="f-body-3 --fm-en">Contact Us</span>
                            <span class="f-heading-5 --bold">오시는 길</span>
                        </div>
                        <div class="no-main-visit-guide__icon">
                            <div class="no-theme-change">
                                <img src="<?= base_path('/resource/images/icon/main_visit_guide_icon_5.png') ?>"
                                    alt="오시는 길" class="dark" loading="lazy" decoding="async">
                                <img src="<?= base_path('/resource/images/icon/main_visit_guide_icon_5_color.png') ?>"
                                    alt="오시는 길" class="light" loading="lazy" decoding="async">
                            </div>
                        </div>
                    </a>
                </li>

            </ul>
            <!--
            <ul class="no-main-visit-guide__row no-main-visit-guide__row--center">

                <li class="no-main-visit-guide__item" <?= AOS_DEFAULT ?>>
                    <a href="<?= route('venue.services') ?>" class="no-main-visit-guide__link">
                        <div class="no-main-visit-guide__bg">
                            <div class="no-theme-change">
                                <img src="<?= base_path('/resource/images/main/main_service_hover_bg_color.png') ?>"
                                    alt="" class="dark" loading="lazy" decoding="async">
                                <img src="<?= base_path('/resource/images/main/main_service_hover_bg_white.png') ?>"
                                    alt="" class="light" loading="lazy" decoding="async">
                            </div>
                        </div>
                        <div class="no-main-visit-guide__text">
                            <span class="f-body-3 --fm-en">Food & Beverage</span>
                            <span class="f-heading-5 --bold">F&B</span>
                        </div>
                        <div class="no-main-visit-guide__icon">
                            <div class="no-theme-change">
                                <img src="<?= base_path('/resource/images/icon/main_visit_guide_icon_6.png') ?>"
                                    alt="F&B" class="dark">
                                <img src="<?= base_path('/resource/images/icon/main_visit_guide_icon_6_color.png') ?>"
                                    alt="F&B" class="light">
                            </div>
                        </div>
                    </a>
                </li>

            </ul>-->
        </div>
    </div>
</section>

<section class="no-main-notice no-section-md">
    <div class="no-container-2xl">
        <div class="no-main-notice-inner">
            <div class="no-main-notice-left" <?= AOS_TITLE ?>>
                <div class="no-section__head">
                    <div class="no-section__head--inner">
                        <h2 class="f-display-1 --bold --fm-en">Notice</h2>
                        <p class="f-body-2 --regular">중요한 정보와 안내 사항을 지금바로 확인해보세요.</p>
                    </div>
                    <?= include_view('components.button', [
                        'text' => 'View More',
                        'href' => '/customer/notice',
                        'bgColor' => 'var(--clr-primary-def)',
                        'border' => 'none',
                        'icon' => 'fa-regular fa-arrow-right',
                        'iconBg' => 'var(--clr-ui-white)',
                        'iconColor' => 'var(--clr-primary-def)',
                    ]) ?>
                </div>
            </div>
            <div class="no-main-notice-right">
                <ul class="no-main-notice__list">
                    <?php
                    // 라우트에서 전달받은 공지사항 데이터
                    $notices = $notices ?? [];
                    ?>
                    <?php if (count($notices) > 0): ?>
                    <?php foreach ($notices as $notice): ?>
                    <li class="no-main-notice__item" <?= AOS_DEFAULT ?>>
                        <a href="<?= route('customer.notice-view', ['no' => $notice['no']]) ?>">
                            <div class="no-main-notice__item--info">
                                <div class="no-main-notice__item-heading">
                                    <div class="no-main-notice__item--state">
                                        <?php if (!empty($notice['category_name'])): ?>
                                        <div class="--badge">
                                            <?= htmlspecialchars($notice['category_name']) ?>
                                        </div>
                                        <?php endif; ?>
                                        <?php if ($notice['is_notice'] === 'Y'): ?>
                                        <div class="--notice">
                                            <i class="fa-solid fa-megaphone"></i>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="no-main-notice__item--title">
                                        <h3 class="f-heading-4 --bold">
                                            <?= htmlspecialchars($notice['title']) ?>
                                        </h3>
                                    </div>
                                </div>
                                <div class="no-main-notice__item--date">
                                    <span
                                        class="f-body-1 --regular"><?= date('Y.m.d', strtotime($notice['regdate'])) ?></span>
                                </div>
                            </div>
                            <div class="--arrow">
                                <i class="fa-regular fa-arrow-right"></i>
                            </div>
                        </a>
                    </li>
                    <?php endforeach; ?>
                    <?php else: ?>
                    <li class="no-main-notice__item" <?= AOS_DEFAULT ?>>
                        <div class="no-main-notice__item--info">
                            <div class="no-main-notice__item-heading">
                                <div class="no-main-notice__item--title">
                                    <h3 class="f-heading-4 --bold">등록된 공지사항이 없습니다.</h3>
                                </div>
                            </div>
                        </div>
                    </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </div>
</section>

<?= include_view('components.marquee') ?>
<?php end_section() ?>

<?php section('portal') ?>
<?= include_view('components.popup'); ?>
<?php end_section() ?>
