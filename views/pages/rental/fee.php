<?php section('content') ?>

<div class=" no-section-md">
    <?= include_view('components.sub-visual', ['title' => '대관료']) ?>

    <section class="no-sub-ui">
        <div class="no-container-xl">
            <div class="no-sub-ui-inner">
                <aside class="left no-sub-ui-left">
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
                                        <span class="no-sub-ui-tab__label">리허설룸</span>
                                    </button>
                                </li>
                            </ul>
                        </div>
                    </div>
                </aside>

                <div class="no-sub-ui-right-wrap">
                    <div class="right no-sub-ui-right">
                        <div class="no-sub-ui-panel is-active" data-panel="woori-card">
                            <div class="no-sub-ui-panel-inner">

                                <?php
                                $guideItems = [
                                    [
                                        'title' => '주 1일 OFF DAY에는 대관료를 부과하지 않으며, 1주 1회 OFF 기준임',
                                        'points' => [
                                            '추가 OFF일에는 공연대관료로 부과',
                                        ],
                                    ],
                                    [
                                        'title' => '1일 1회 공연대관 시간',
                                        'points' => [
                                            '공연 시작 시간 3시간 30분 전부터 7시간 / 점심, 저녁시간 1시간 제외',
                                            '1회 공연 대관시간은 150분을 넘지 못하며, 1일 총 대관시간은 12시간을 초과할 수 없음',
											'이 외 시간 사용 시, 추가 대관료 부과 (별도 협의)',
                                        ],
                                    ],
                                    [
                                        'title' => '기본 대관료에 포함되는 내용',
                                        'points' => [
                                            '공연 대관 시간 냉,난방료 / 공연 한시간 전부터 종료시까지',
                                            '1명의 하우스 매니저, 8명의 하우스 어셔 인력',
                                            '무대 기계시설, 기본음향, 조명시설 / 음향, 조명 사용비용 별도',
                                            '준비대관, 공연대관 시 사용되는 전기, 수도세 일체',
                                            '대관기간내에 사용하는 모든 분장실, 대기실, 휴게실 및 세탁실',
                                        ],
                                    ],
                                    [
                                        'title' => '시연회 및 프레스콜 등의 추가 행사 진행 시 시간당 별도 비용 부과',
                                        'points' => [],
                                    ],
                                ];

                                $preparationGuideItems = [
                                    [
                                        'title' => '냉난방 및 온수 사용료 별도',
                                        'points' => [],
                                    ],
                                    [
                                        'title' => '기본구간 준비대관',
                                        'points' => [
                                            '09:00 ~ 18:00 (8시간)',
                                            '* 점심시간 1시간은 대관가능시간에서 제외',
                                        ],
                                    ],
                                    [
                                        'title' => '심야구간 기본대관',
                                        'points' => [
                                            '19:00 ~ 22:00 (3시간)',
                                        ],
                                    ],
                                    [
                                        'title' => '철야구간',
                                        'points' => [
                                            '22시 이후부터 익일 9시까지 / 시간당 1,500,000원',
                                        ],
                                    ],
                                    [
                                        'title' => '준비대관일은 최대 12일을 넘지 못하며 12일이 넘는 경우 1일 공연 대관료로 계산하여 추가함',
                                        'points' => [],
                                    ],
                                    [
                                        'title' => '준비대관 기간에도 1주 1일 OFF가 있어야 하며 비용을 부과하지 않음',
                                        'points' => [],
                                    ],
                                ];

                                $concertGuideItems = [
                                    [
                                        'title' => '공연 대관: 09:00~23:00 / 준비 대관: 09:00~22:00',
                                        'points' => [
                                            '대관 시 12~13시, 18~19시의 식사시간은 대관가능시간에서 제외',
                                        ],
                                    ],
                                    [
                                        'title' => '공연 1회는 최대 150분을 기준으로 하며, 1일 2회 공연의 경우 50% 할증 적용',
                                        'points' => [],
                                    ],
                                    [
                                        'title' => '준비대관 시 냉난방 및 온수 사용료 별도',
                                        'points' => [],
                                    ],
                                    [
                                        'title' => '기본 대관료에 포함되는 내용',
                                        'points' => [
                                            '공연 대관 시간 냉,난방료 / 공연 한시간 전부터 종료시까지',
                                            '1명의 하우스 매니저, 4명의 하우스 어셔 인력',
                                            '무대 기계시설, 기본음향, 조명시설 / 음향, 조명 사용비용 별도',
                                            '대관기간내에 사용하는 모든 분장실, 대기실, 휴게실 및 세탁실',
                                        ],
                                    ],
                                ];

                                $precautionsItems = [
                                    [
                                        'title' => '위 금액의 공연대관료 및 준비대관료에는 장비대여료, 부대시설사용료는 포함되어 있지 않음',
                                        'points' => [],
                                    ],
                                    [
                                        'title' => '장비대여료 및 부대시설사용료는 NOL 씨어터 대관규약에 의거 대관계약 시 추가 산정',
                                        'points' => [],
                                    ],
                                ];
                                ?>

                                <div style="display:flex; flex-direction:column; gap:4.8rem;">
                                    <div class="no-sub-ui-panel-block">
                                        <h3 class="f-heading-3 --bold" style="margin-bottom:2.4rem;">연극/뮤지컬</h3>
                                        <div class="no-sub-table">
                                            <table class="no-sub-table__self">
                                                <thead>
                                                    <tr>
                                                        <th>준비대관</th>
                                                        <th>공연대관 (1일 1회)</th>
                                                        <th>공연대관 (1일 2회)</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr>
                                                        <td>구간별 4,000,000원</td>
                                                        <td>1회 5,000,000원</td>
                                                        <td>1회 7,500,000원</td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                        <div class="--cnt">
                                            <div class="no-guide-wrap">
                                                <?= include_view('components.guide', [
                                                    'title' => '공연대관 안내사항',
                                                    'items' => $guideItems,
                                                ]) ?>
                                                <?= include_view('components.guide', [
                                                    'title' => '준비대관 안내사항',
                                                    'items' => $preparationGuideItems,
                                                ]) ?>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="no-sub-ui-panel-block">
                                        <h3 class="f-heading-3 --bold" style="margin-bottom:2.4rem;">콘서트/행사/방송</h3>
                                        <div class="no-sub-table">
                                            <table class="no-sub-table__self">
                                                <thead>
                                                    <tr>
                                                        <th colspan="2">구분</th>
                                                        <th>공연대관</th>
                                                        <th>준비대관</th>
                                                        <th>철야대관(1h)</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr>
                                                        <td rowspan="2">콘서트</td>
                                                        <td>주중(월~목)</td>
                                                        <td>6,100,000원</td>
                                                        <td>7,000,000원</td>
                                                        <td>1,000,000원</td>
                                                    </tr>
                                                    <tr>
                                                        <td>주말(금~일)</td>
                                                        <td>7,200,000원</td>
                                                        <td>8,300,000원</td>
                                                        <td>1,000,000원</td>
                                                    </tr>
                                                    <tr>
                                                        <td rowspan="2">방송/행사</td>
                                                        <td>주중(월~목)</td>
                                                        <td>6,900,000원</td>
                                                        <td>7,200,000원</td>
                                                        <td>1,000,000원</td>
                                                    </tr>
                                                    <tr>
                                                        <td>주말(금~일)</td>
                                                        <td>8,400,000원</td>
                                                        <td>8,700,000원</td>
                                                        <td>1,000,000원</td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                        <div class="--cnt">
                                            <div class="no-guide-wrap">
                                                <?= include_view('components.guide', [
                                                    'title' => '공연/준비대관 안내사항',
                                                    'items' => $concertGuideItems,
                                                ]) ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="--cnt">
                                    <div class="no-guide-wrap">
                                        <?= include_view('components.guide', [
                                            'title' => '주의사항',
                                            'items' => $precautionsItems,
                                            'class' => 'no-guide--precautions',
                                        ]) ?>
                                    </div>
                                </div>

                            </div>
                        </div>
                        <div class="no-sub-ui-panel" data-panel="woori-securities">
                            <div class="no-sub-ui-panel-inner">
                                <div class="no-sub-table">
                                    <table class="no-sub-table__self">
                                        <thead>
                                            <tr>
                                                <th>준비대관</th>
                                                <th>공연대관 (1일 1회)</th>
                                                <th>공연대관 (1일 2회)</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>1일 3,000,000원</td>
                                                <td>1일 3,000,000원</td>
                                                <td>1일 4,500,000원</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>

                                <?php
                                $securitiesGuideItems = [
                                    [
                                        'title' => '주 1일 OFF DAY에는 대관료를 부과하지 않으며, 1주 1회 OFF 기준임',
                                        'points' => [
                                            '추가 OFF일에는 공연대관료로 부과',
                                        ],
                                    ],
                                    [
                                        'title' => '1일 1회 공연대관 시간',
                                        'points' => [
                                            '공연 시작 시간 3시간 30분 전부터 7시간 / 점심, 저녁시간 1시간 제외',
                                            '1회 공연 대관시간은 150분을 넘지 못하며, 1일 총 대관시간은 12시간을 초과할 수 없음',
											'이 외 시간 사용 시 ,추가 대관료 부과 (별도 협의)',
                                        ],
                                    ],
                                    [
                                        'title' => '기본 대관료에 포함되는 내용',
                                        'points' => [
                                            '공연 대관 시간 냉,난방료 / 공연 한시간 전부터 종료시까지',
                                            '1명의 하우스 매니저, 4명의 하우스 어셔 인력',
                                            '무대 기계시설, 기본음향, 조명시설 / 음향, 조명 사용비용 별도',
                                            '준비대관, 공연대관 시 사용되는 전기, 수도세 일체',
                                            '대관기간내에 사용하는 모든 분장실, 대기실, 휴게실 및 세탁실',
                                        ],
                                    ],
                                    [
                                        'title' => '시연회 및 프레스콜 등의 추가 행사 진행 시 시간당 별도 비용 부과',
                                        'points' => [],
                                    ],
                                ];

                                $securitiesPreparationGuideItems = [
                                    [
                                        'title' => '냉난방 및 온수 사용료 별도',
                                        'points' => [],
                                    ],
                                    [
                                        'title' => '기본구간 준비대관',
                                        'points' => [
                                            '09:00 ~ 18:00 (8시간)',
                                            '* 점심시간 1시간은 대관가능시간에서 제외',
                                        ],
                                    ],
                                    [
                                        'title' => '심야구간 기본대관',
                                        'points' => [
                                            '19:00 ~ 22:00 (3시간)',
                                        ],
                                    ],
                                    [
                                        'title' => '철야구간',
                                        'points' => [
                                            '22시 이후부터 익일 9시까지 / 시간당 1,000,000원',
                                        ],
                                    ],
                                    [
                                        'title' => '준비대관일은 최대 12일을 넘지 못하며 12일이 넘는 경우 1일 공연 대관료로 계산하여 추가함',
                                        'points' => [],
                                    ],
                                    [
                                        'title' => '준비대관 기간에도 1주 1일 OFF가 있어야 하며 비용을 부과하지 않음',
                                        'points' => [],
                                    ],
                                ];

                                $securitiesPrecautionsItems = [
                                    [
                                        'title' => '위 금액의 공연대관료 및 준비대관료에는 장비대여료, 부대시설사용료는 포함되어 있지 않음',
                                        'points' => [],
                                    ],
                                    [
                                        'title' => '장비대여료 및 부대시설사용료는 NOL 씨어터 대관규약에 의거 대관계약 시 추가 산정',
                                        'points' => [],
                                    ],
                                ];
                                ?>

                                <div class="--cnt">
                                    <div class="no-guide-wrap">
                                        <?= include_view('components.guide', [
                                            'title' => '공연대관 안내사항',
                                            'items' => $securitiesGuideItems,
                                        ]) ?>

                                        <?= include_view('components.guide', [
                                            'title' => '준비대관 안내사항',
                                            'items' => $securitiesPreparationGuideItems,
                                        ]) ?>

                                        <?= include_view('components.guide', [
                                            'title' => '주의사항',
                                            'items' => $securitiesPrecautionsItems,
                                            'class' => 'no-guide--precautions',
                                        ]) ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="no-sub-ui-panel" data-panel="practice">
                            <div class="no-sub-ui-panel-inner">


                                <div class="no-sub-table">
                                    <table class="no-sub-table__self">
                                        <thead>
                                            <tr>
                                                <th>리허설룸(4F)</th>
                                                <th>리허설룸(5F)</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>1일 150,000원</td>
                                                <td>1일 84,000원</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>

                                <?php
                                $practicePrecautionsItems = [
                                    [
                                        'title' => '위 금액에는 장비대여료, 부대시설사용료는 포함되어 있지 않음',
                                        'points' => [],
                                    ],
                                    [
                                        'title' => '장비대여료 및 부대시설사용료는 NOL 씨어터 대관규약에 의거 대관계약 시 추가 산정',
                                        'points' => [],
                                    ],
                                ];
                                ?>

                                <div class="--cnt">
                                    <div class="no-guide-wrap">
                                        <?= include_view('components.guide', [
                                            'title' => '주의사항',
                                            'items' => $practicePrecautionsItems,
                                            'class' => 'no-guide--precautions',
                                        ]) ?>
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