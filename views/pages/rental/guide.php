<?php section('content') ?>

<?php
$canApply = $canApply ?? false;
$rentalNotice = $rentalNotice ?? '';
$rentalStartDateFormatted = $rentalStartDateFormatted ?? null;
$rentalEndDateFormatted = $rentalEndDateFormatted ?? null;
?>

<div class="no-section-md">
    <?= include_view('components.sub-visual', ['title' => '대관 신청']) ?>

    <?php if (!$canApply): ?>
    <!-- 대관 신청 기간이 아닐 때: rental-closed 안내 -->
    <section class="no-sub-apply rental-guide__section">
        <div class="no-container-xl">
            <div class="rental-closed">
                <div class="rental-closed__card">
                    <div class="rental-closed__icon">
                        <i class="fa-regular fa-calendar-xmark" aria-hidden="true"></i>
                    </div>
                    <h2 class="rental-closed__title f-heading-2">현재는 대관 접수 기간이 아닙니다</h2>
                    <p class="rental-closed__desc f-body-2">본 페이지는 <strong>우리카드홀 / 우리투자증권홀</strong> 대관 신청을 위한 페이지 입니다.</p>
                    <p class="rental-closed__desc f-body-2">향후 <strong>공지사항 게시판</strong>을 통해 대관 공고문을 확인 바랍니다.</p>
                    <p class="rental-closed__desc f-body-2">그 외 리허설룸은 별도 공고 없이 수시 대관으로 진행됩니다.</p>
                    <?php if ($rentalStartDateFormatted && $rentalEndDateFormatted): ?>
                    <p class="rental-closed__badge f-body-3">신청 기간 : <?= htmlspecialchars($rentalStartDateFormatted) ?> ~ <?= htmlspecialchars($rentalEndDateFormatted) ?></p>
                    <?php endif; ?>
                </div>
                <hr class="rental-closed__divider">
                <div class="rental-closed__info">
                    <p class="rental-closed__info-desc f-body-2">하단 담당자 정보 및 대관안내 페이지를 참고하시어 문의해주시기 바랍니다.</p>
                    <div class="rental-closed__contacts">
                        <div class="rental-closed__contact">
                            <p class="rental-closed__contact-name f-heading-4">우리카드홀 / 우리투자증권홀</p>
                            <ul class="rental-closed__contact-list">
                                <li class="rental-closed__contact-item f ai-c no-gap-sm f-body-3">
                                    <i class="fa-solid fa-phone" aria-hidden="true"></i>
                                    <a href="tel:02-6004-6722" class="--tel">02-6004-6722</a>
                                </li>
                                <li class="rental-closed__contact-item f ai-c no-gap-sm f-body-3">
                                    <i class="fa-solid fa-envelope" aria-hidden="true"></i>
                                    <a href="mailto:kihyun123v@nol-theater.com" class="--email">kihyun123v@nol-theater.com</a>
                                </li>
                            </ul>
                        </div>
                        <div class="rental-closed__contact">
                            <p class="rental-closed__contact-name f-heading-4">리허설룸1,2</p>
                            <ul class="rental-closed__contact-list">
                                <li class="rental-closed__contact-item f ai-c no-gap-sm f-body-3">
                                    <i class="fa-solid fa-phone" aria-hidden="true"></i>
                                    <a href="tel:02-6004-6919" class="--tel">02-6004-6919</a>
                                </li>
                                <li class="rental-closed__contact-item f ai-c no-gap-sm f-body-3">
                                    <i class="fa-solid fa-envelope" aria-hidden="true"></i>
                                    <a href="mailto:paran2025@nol-theater.com" class="--email">paran2025@nol-theater.com</a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="rental-closed__action">
                    <a href="<?= route('rental.procedure') ?>" class="rental-closed__btn f-body-1">
                        <span>대관안내 보기</span>
                        <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
                    </a>
                </div>
            </div>
        </div>
    </section>
    <?php else: ?>
    <!-- 대관 신청 기간일 때: 브릿지 안내 + 대관 신청하기 버튼 -->
    <section class="no-sub-apply rental-guide__section">
        <div class="no-container-xl">
            <div class="rental-guide__card">
                <?php if (trim(strip_tags($rentalNotice ?? '')) !== ''): ?>
                <div class="rental-guide__notice rental-guide__notice--html f-body-2">
                    <?php echo strip_tags($rentalNotice, '<p><br><strong><b><em><i><u><s><span><div><h1><h2><h3><h4><h5><h6><ul><ol><li><a><font>'); ?>
                </div>
                <?php endif; ?>
                <div class="rental-guide__contacts">
                    <div class="rental-guide__contact">
                        <p class="rental-guide__contact-name f-heading-4">우리카드홀 / 우리투자증권홀</p>
                        <p class="rental-guide__contact-row f-body-3">
                            <i class="fa-solid fa-phone" aria-hidden="true"></i>
                            <a href="tel:02-6004-6722">02-6004-6722</a>
                        </p>
                        <p class="rental-guide__contact-row f-body-3">
                            <i class="fa-solid fa-envelope" aria-hidden="true"></i>
                            <a href="mailto:kihyun123v@nol-theater.com">kihyun123v@nol-theater.com</a>
                        </p>
                    </div>
                    <div class="rental-guide__contact">
                        <p class="rental-guide__contact-name f-heading-4">리허설룸1,2</p>
                        <p class="rental-guide__contact-row f-body-3">
                            <i class="fa-solid fa-phone" aria-hidden="true"></i>
                            <a href="tel:02-6004-6919">02-6004-6919</a>
                        </p>
                        <p class="rental-guide__contact-row f-body-3">
                            <i class="fa-solid fa-envelope" aria-hidden="true"></i>
                            <a href="mailto:paran2025@nol-theater.com">paran2025@nol-theater.com</a>
                        </p>
                    </div>
                </div>
                <div class="rental-guide__action">
                    <a href="<?= route('rental.apply') ?>" class="rental-guide__btn f-body-1">
                        <span>대관 신청하기</span>
                        <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
                    </a>
                </div>
            </div>
        </div>
    </section>
    <?php endif; ?>
</div>

<?php end_section() ?>

<?php section('portal') ?>
<?= include_view('components.popup'); ?>
<?php end_section() ?>
