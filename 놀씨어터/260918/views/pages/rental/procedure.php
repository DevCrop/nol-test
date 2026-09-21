<?php section('content') ?>

<div class=" no-section-md">
    <?= include_view('components.sub-visual', ['title' => '대관 절차']) ?>

    <section class="no-sub-procedure">
        <div class="no-sub-procedure-process">
            <div class="no-container-xl">
                <div class="no-sub-procedure-layout">
                    <div class="no-sub-procedure-title" <?= AOS_TITLE ?>>
                        <h2 class="f-heading-1 --bold">대관 프로세스</h2>
                    </div>
                    <div class="no-sub-procedure-content-wrapper" <?= AOS_DEFAULT ?>>
                        <div class="no-sub-procedure-content">
                            <ul class="no-sub-procedure-list">
                                <div class="no-sub-procedure-line-placeholder"></div>
                                <div class="no-sub-procedure-line"></div>
                                <?php
                                $steps = [
                                    [
                                        'title' => '대관공고',
                                        'desc'  => '대관 가능 일정 및 조건 등이 홈페이지를 통해 공지됩니다.'
                                    ],
                                    [
                                        'title' => '대관신청',
                                        'desc'  => '접수기간에 맞춰 대관 신청을 접수합니다.'
                                    ],
                                    [
                                        'title' => '대관심의',
                                        'desc'  => '제출된 신청 내용을 바탕으로 내부 심의가 진행됩니다.'
                                    ],
                                    [
                                        'title' => '승인·불가 통보',
                                        'desc'  => '대관 심의 결과는 개별 안내를 통해 전달됩니다.'
                                    ],
                                    [
                                        'title' => '대관계약',
                                        'desc'  => '승인된 건에 한하여 대관 계약 절차가 진행됩니다.'
                                    ],
                                    [
                                        'title' => '대관료 납부',
                                        'desc'  => '계약 체결 후 지정 기한 내 대관료를 납부합니다.'
                                    ],
                                    [
                                        'title' => '공연장 홍보물 게시',
                                        'desc'  => '공연장 홈페이지 및 채널을 통해 관련 정보를 안내합니다.'
                                    ],
                                    [
                                        'title' => '스태프 회의',
                                        'desc'  => '운영을 위한 관계자 및 스태프 사전 협의를 진행합니다.'
                                    ],
                                    [
                                        'title' => '공연 진행',
                                        'desc'  => '사전 준비를 바탕으로 공연이 진행됩니다.'
                                    ],
                                ];
                                ?>

                                <?php foreach ($steps as $index => $step): ?>
                                <li class="no-sub-procedure-item">
                                    <div class="no-sub-procedure-item-icon">
                                        <span
                                            class="no-sub-procedure-item-number f-body-1 --fm-en --bold"><?= $index + 1 ?></span>
                                    </div>
                                    <div class="no-sub-procedure-item-content">
                                        <h2 class="f-heading-5 --semibold">
                                            <?= e($step['title']) ?>
                                        </h2>
                                        <p class="f-body-2 --medium">
                                            <?= e($step['desc']) ?>
                                        </p>
                                    </div>
                                </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <div class="no-sub-procedure-info">
            <div class="no-container-xl">
                <div class="no-sub-procedure-info-inner">
                    <div class="no-sub-procedure-layout">
                        <div class="no-sub-procedure-title" <?= AOS_TITLE ?>>
                            <h2 class="f-heading-1 --bold">대관정보</h2>
                        </div>
                        <div class="no-sub-procedure-content-wrapper" <?= AOS_DEFAULT ?>>
                            <div class="no-sub-table">
                                <table class="no-sub-table__self">
                                    <tbody>
                                        <tr>
                                            <th>대관공고</th>
                                            <td>
                                                <div class="no-sub-table__list">
                                                    <div class="no-sub-table__item">
                                                        <strong>공연장</strong>
                                                        <div class="no-sub-table__item-content">
                                                            <p>홈페이지 정기 대관 공고를 통한 접수</p>
                                                        </div>
                                                    </div>
                                                    <div class="no-sub-table__item">
                                                        <strong>리허설룸</strong>
                                                        <div class="no-sub-table__item-content">
                                                            <p>별도의 공고 없이 수시 대관 접수</p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>신청방법</th>
                                            <td>
                                                <div class="no-sub-table__list">
                                                    <div class="no-sub-table__item">
                                                        <strong>공연장</strong>
                                                        <div class="no-sub-table__item-content">
                                                            <p>공고 기간 내 대관안내를 통해 신청</p>
                                                        </div>
                                                    </div>
                                                    <div class="no-sub-table__item">
                                                        <strong>리허설룸</strong>
                                                        <div class="no-sub-table__item-content">
                                                            <p>
                                                                담당자 개별 문의 후, 대관신청서 E-mail 접수
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>대관심의 및 통보</th>
                                            <td>
                                                <div class="no-sub-table__desc">
                                                    <p>
                                                        심의 승인 여부 및 제출 필요 서류 개별 통보 (메일, 문자 또는 유선)
                                                    </p>
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
        <div class="no-sub-procedure-inquiry ">
            <div class="no-container-xl">
                <div class="no-sub-procedure-info-inner">
                    <div class="no-sub-procedure-layout">
                        <div class="no-sub-procedure-title" <?= AOS_TITLE ?>>
                            <h2 class="f-heading-1 --bold">대관문의</h2>
                        </div>
                        <div class="no-sub-procedure-content-wrapper" <?= AOS_DEFAULT ?>>
                            <div class="no-sub-table">
                                <table class="no-sub-table__self">
                                    <tbody>
                                        <?php
                                        $inquiries = [
                                            [
                                                'title' => '공연장 ',
                                                'phone' => '02-6004-6722',
                                                'email' => 'kihyun123v@nol-theater.com'
                                            ],
                                            [
                                                'title' => '리허설룸 ',
                                                'phone' => '02-6004-6919',
                                                'email' => 'paran2025@nol-theater.com'
                                            ],

                                        ];
                                        ?>
                                        <?php foreach ($inquiries as $inquiry): ?>
                                        <tr>
                                            <th><?= e($inquiry['title']) ?></th>
                                            <td>
                                                <?php if (!empty($inquiry['phone']) || !empty($inquiry['email'])): ?>
                                                <div class="no-sub-table__list">
                                                    <?php if (!empty($inquiry['phone'])): ?>
                                                    <div class="no-sub-table__item--icon">
                                                        <a href="tel:<?= e($inquiry['phone']) ?>">
                                                            <i class="fa-solid fa-phone" aria-hidden="true"></i>
                                                            <div class="no-sub-table__item-content">
                                                                <p><?= e($inquiry['phone']) ?></p>
                                                            </div>
                                                        </a>
                                                    </div>
                                                    <?php endif; ?>
                                                    <?php if (!empty($inquiry['email'])): ?>
                                                    <div class="no-sub-table__item--icon">
                                                        <a href="mailto:<?= e($inquiry['email']) ?>">
                                                            <i class="fa-regular fa-at" aria-hidden="true"></i>
                                                            <div class="no-sub-table__item-content">
                                                                <p><?= e($inquiry['email']) ?></p>
                                                            </div>
                                                        </a>
                                                    </div>
                                                    <?php endif; ?>
                                                </div>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
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