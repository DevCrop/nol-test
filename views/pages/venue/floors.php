<?php section('content') ?>

<div class=" no-section-md">
    <?= include_view('components.sub-visual', ['title' => '층별 안내']) ?>

    <section class="no-sub-floors">
        <div class="no-container-xl">
            <div class="no-sub-floors-inner">
                <?php
                $floors = [
                    [
                        'floor' => '5F',
                        'facilities' => [
                            [
                                'icon_type' => 'culture',
                                'title' => '리허설룸2',
                                'desc' => '',
                            ],
                        ],
                    ],
                    [
                        'floor' => '4F',
                        'facilities' => [
                            [
                                'icon_type' => 'culture',
                                'title' => '리허설룸1',
                                'desc' => '',
                            ],
                        ],
                    ],
                    [
                        'floor' => '3F',
                        'facilities' => [
                            [
                                'icon_type' => 'culture',
                                'title' => '우리투자증권홀(객석 2F)',
                                'desc' => '',
                            ],
                        ],
                    ],
                    [
                        'floor' => '2F',
                        'facilities' => [
                            [
                                'icon_type' => 'culture',
                                'title' => '우리투자증권홀(객석 1F)',
                                'desc' => '',
                            ],
                        ],
                    ],
                    [
                        'floor' => '1F',
                        'facilities' => [
                            [
                                'icon_type' => 'culture',
                                'title' => '공연장 주출입구, 매표소',
                                'desc' => '',
                            ],
                            [
                                'icon_type' => 'convenience',
                                'title' => '편의시설',
                                'desc' => 'F&B',
                                'hidden' => true, // 미노출
                            ],
                        ],
                    ],
                    [
                        'floor' => 'B1F',
                        'facilities' => [
                            [
                                'icon_type' => 'exit',
                                'title' => '공연장 출입구',
                            ],
                            [
                                'icon_type' => 'convenience',
                                'title' => '편의시설',
                                'desc' => 'F&B',
                                'hidden' => true, // 미노출
                            ],
                        ],
                    ],
                    [
                        'floor' => 'B2F',
                        'facilities' => [
                            [
                                'icon_type' => 'culture',
                                'title' => '우리카드홀(객석 2F)',
                                'desc' => '',
                            ],
                        ],
                    ],
                    [
                        'floor' => 'B3F',
                        'facilities' => [
                            [
                                'icon_type' => 'culture',
                                'title' => '우리카드홀(객석 1F)',
                                'desc' => '',
                            ],
                        ],
                    ],
                ];
                ?>
                <ul class="no-sub-floors-list">
                    <?php foreach ($floors as $floorData): ?>
                        <li class="no-sub-floors-item" <?= AOS_DEFAULT ?>>
                            <h2 class="f-heading-1 --bold --fm-en"><?= e($floorData['floor']) ?></h2>
                            <div class="no-sub-floors-item-content">
                                <ul class="no-sub-floors-item-content-list">
                                    <?php foreach ($floorData['facilities'] as $facility): ?>
                                        <?php if (!isset($facility['hidden']) || !$facility['hidden']): ?>
                                            <li class="no-sub-floors-item-content-item">
                                                <div class="txt">
                                                    <h3 class="f-heading-4 --bold">
                                                        <?= e($facility['title']) ?>
                                                    </h3>
                                                    <?php if (isset($facility['desc'])): ?>
                                                        <!--<div class="--divider"></div>-->
                                                        <div class="desc">
                                                            <p class="f-heading-4 --semibold">
                                                                <?= e($facility['desc']) ?>
                                                            </p>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            </li>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </section>
</div>

<?php end_section() ?>

<?php section('portal') ?>
<?= include_view('components.popup'); ?>
<?php end_section() ?>