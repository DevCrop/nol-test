<?php section('content') ?>

<div class=" no-section-md">
    <?= include_view('components.sub-visual', ['title' => '편의시설 및 서비스']) ?>

    <section class="no-sub-ui">
        <div class="no-container-xl">
            <div class="no-sub-ui-inner">
                <aside class="left no-sub-ui-left" <?= AOS_DEFAULT ?>>
                    <div class="no-sub-ui-left-swiper">
                        <div class="swiper no-sub-ui-tabs-swiper">
                            <ul class="swiper-wrapper no-sub-ui-tabs">
                                <!-- F & B 탭 (나중에 오픈 예정) -->
                                <!-- <li class="swiper-slide no-sub-ui-tab" data-target="woori-card">
                                    <button type="button" class="no-sub-ui-tab__btn">
                                        <span class="no-sub-ui-tab__bullet"></span>
                                        <span class="no-sub-ui-tab__label">F & B</span>
                                    </button>
                                </li> -->
                                <li class="swiper-slide no-sub-ui-tab is-active" data-target="storage">
                                    <button type="button" class="no-sub-ui-tab__btn">
                                        <span class="no-sub-ui-tab__bullet"></span>
                                        <span class="no-sub-ui-tab__label">물품보관소</span>
                                    </button>
                                </li>
                            </ul>
                        </div>
                    </div>
                </aside>

                <div class="no-sub-ui-right-wrap" <?= AOS_MEDIUM ?>>
                    <div class="right no-sub-ui-right">

                        <!-- F & B 패널 (나중에 오픈 예정) -->
                        <div class="no-sub-ui-panel" data-panel="woori-card" style="display: none;">
                            <div class="no-sub-ui-panel-inner">
                                <div class="no-sub-ui-panel-block">
                                    <div class="no-sub-ui-txt">
                                        <div class="no-sub-ui-txt-header">
                                            <div class="no-sub-ui-title">
                                                <h2 class="f-heading-4 --bold">F & B</h2>
                                            </div>
                                            <?= include_view('components.button', [
                                                'text' => '자세히 보기',
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
                                                공연 관람 전후 편안하게 즐길 수 있는 다양한 음식과 음료를 제공합니다.
                                                공연장 내 F&B 시설에서 간단한 간식부터 다양한 음료까지 만나보실 수 있으며,
                                                공연 전후 여유로운 시간을 보내실 수 있습니다.
                                            </p>
                                        </div>
                                    </div>

                                    <div class="--info-box">
                                        <ul>
                                            <li>
                                                <span>
                                                    위치
                                                </span>
                                                <p>
                                                    <span>
                                                        우리카드홀 로비, 우리투자증권홀 로비
                                                    </span>
                                                </p>
                                            </li>
                                            <li>
                                                <span>
                                                    운영시간
                                                </span>
                                                <p>
                                                    <span>
                                                        공연일 로비 개방 시 ~ 공연 종료 후 30분
                                                    </span>
                                                </p>
                                            </li>
                                            <li>
                                                <span>
                                                    제공 메뉴
                                                </span>
                                                <p>
                                                    <span>
                                                        커피, 차, 음료, 간식류 등
                                                    </span>
                                                </p>
                                            </li>
                                        </ul>
                                    </div>
                                    <div class="no-sub-ui-swiper-wrapper">
                                        <div class="swiper no-sub-ui-swiper">
                                            <ul class="swiper-wrapper">
                                                <?php for ($i = 0; $i < 10; $i++): ?>
                                                    <li class="swiper-slide">
                                                        <figure>
                                                            <img src="<?= base_path('/resource/images/placeholder/img_placeholder.png') ?>"
                                                                alt="">
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
                            </div>
                        </div>

                        <div class="no-sub-ui-panel is-active" data-panel="storage">
                            <div class="no-sub-ui-panel-inner">
                                <div class="no-sub-ui-panel-block">
                                    <div class="no-sub-ui-txt">
                                        <div class="no-sub-ui-title">
                                            <h2 class="f-heading-4 --bold">물품보관소</h2>
                                        </div>
                                        <div class="no-sub-ui-desc">
                                            <p class="--regular clr-text-body">
                                                보다 편안한 공연 관람을 위해 물품보관을 하실 수 있습니다.
                                            </p>
                                        </div>
                                    </div>
                                    <div class="no-sub-table">
                                        <table class="no-sub-table__self">
                                            <tbody>
                                                <tr>
                                                    <th>위치</th>
                                                    <td>
                                                        <div class="no-sub-table__list">
                                                            <div class="no-sub-table__item">
                                                                <strong>우리카드홀</strong>
                                                                <div class="no-sub-table__item-content">
                                                                    <p>객석1, 2층(B2층, B3층) 로비, 건물 B1층 로비</p>
                                                                </div>
                                                            </div>
                                                            <div class="no-sub-table__item">
                                                                <strong>우리투자증권홀</strong>
                                                                <div class="no-sub-table__item-content">
                                                                    <p>객석 1층(2층) 로비</p>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th>운영시간</th>
                                                    <td>
                                                        <div class="no-sub-table__desc">
                                                            <p>
                                                                로비 개방 시 ~ 공연 종료 후 20분
                                                            </p>
                                                        </div>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th>보관방법</th>
                                                    <td>
                                                        <div class="no-sub-table__list">
                                                            <div class="no-sub-table__info">
                                                                <p><strong>공연 관람객 기준 2시간</strong></p>
                                                                <ul>
                                                                    <li>소형: 1,000원</li>
                                                                    <li>중형: 2,000원</li>
                                                                    <li>대형: 3,000원</li>
                                                                </ul>
                                                            </div>
                                                            <div class="no-sub-table__desc">
                                                                <p>
                                                                    추가 요금: 2시간당 동일 요금 <br>
                                                                    결제: 신용카드 결제만 가능
                                                                </p>
                                                            </div>
                                                        </div>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th>주의사항</th>
                                                    <td>
                                                        <div class="no-sub-table__list">

                                                            <div class="no-sub-table__desc">
                                                                <p>
                                                                    각 홀 별 당일 관람객에 한하여 보관 가능하며, 이후에는 추가 요금이 발생합니다. <br>
                                                                    보관함 수량이 한정적이므로 현장 상황에 따라 물품보관이 어려울 수 있습니다. <br>
                                                                    고가의 물품(현금, 유가 증권, 귀금속 등), 귀중품은 개인소지 해 주시고, 파손 및 분실 시
                                                                    책임지지 않습니다.
                                                                </p>
                                                            </div>
                                                            <div class="no-sub-table__info">
                                                                <p><strong>물품보관 주의사항</strong></p>
                                                                <ul>
                                                                    <li>장기 미수령 물품(5일 이상)은 별도 보관 처리됩니다.</li>
                                                                    <li>30일 이후 임의 폐기됩니다.</li>
                                                                </ul>
                                                            </div>
                                                        </div>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
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