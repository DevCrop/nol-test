<?php section('content') ?>

<?php
// 라우트에서 전달받은 데이터 매핑
$canApply = $canApply ?? false;
$isRentalOpen = $isRentalOpen ?? false;
$rentalStartDate = $rentalStartDate ?? null;
$rentalEndDate = $rentalEndDate ?? null;
$rentalStartDateFormatted = $rentalStartDateFormatted ?? null;
$rentalEndDateFormatted = $rentalEndDateFormatted ?? null;
$rentalNotice = $rentalNotice ?? '';
?>

<style>
.rental-closed {}

.rental-closed__card {
    border: 1px solid var(--clr-border-default, rgba(0, 0, 0, 0.16));
    border-radius: clamp(1.2rem, 0.52vw + 1.01rem, 2rem);
    padding: clamp(3.2rem, 1.55vw + 2.62rem, 5.6rem) clamp(2.4rem, 1.04vw + 2.01rem, 4rem);
    text-align: center;
}

.rental-closed__icon {
    width: clamp(5.6rem, 1.55vw + 4.98rem, 8rem);
    height: clamp(5.6rem, 1.55vw + 4.98rem, 8rem);
    margin: 0 auto clamp(2rem, 0.78vw + 1.71rem, 3.2rem);
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    background: var(--clr-primary-50, #ebf4ff);
}

.rental-closed__icon i {
    font-size: clamp(2.4rem, 0.78vw + 2.11rem, 3.6rem);
    color: var(--clr-primary-def, #3549ff);
}

.rental-closed__title {
    font-size: clamp(2rem, 1.04vw + 1.61rem, 3.6rem);
    font-weight: 600;
    line-height: 1.41;
    color: var(--clr-text-title, #000);
    margin-bottom: clamp(1.2rem, 0.26vw + 1.1rem, 1.6rem);
}

.rental-closed__desc {
    font-size: clamp(1.4rem, 0.26vw + 1.3rem, 1.8rem);
    line-height: 1.618;
    color: var(--clr-text-body, rgba(0, 0, 0, 0.8));
}

.rental-closed__desc+.rental-closed__desc {
    margin-top: clamp(0.4rem, 0.07vw + 0.38rem, 0.6rem);
}

.rental-closed__desc strong {
    font-weight: 600;
    color: var(--clr-text-title, #000);
}

.rental-closed__badge {
    display: inline-block;
    margin-top: clamp(1.6rem, 0.52vw + 1.41rem, 2.4rem);
    padding: clamp(0.8rem, 0.13vw + 0.75rem, 1rem) clamp(1.6rem, 0.26vw + 1.5rem, 2rem);
    background: var(--clr-primary-50, #ebf4ff);
    border-radius: clamp(0.6rem, 0.13vw + 0.55rem, 0.8rem);
    font-size: clamp(1.3rem, 0.19vw + 1.23rem, 1.6rem);
    color: var(--clr-primary-def, #3549ff);
    font-weight: 500;
}

.rental-closed__divider {
    border: none;
    border-top: 1px solid var(--clr-border-weak, rgba(0, 0, 0, 0.08));
    margin: clamp(3.2rem, 1.55vw + 2.62rem, 5.6rem) 0;
}

.rental-closed__info {
    border: 1px solid var(--clr-border-default, rgba(0, 0, 0, 0.16));
    border-radius: clamp(1.2rem, 0.52vw + 1.01rem, 2rem);
    padding: clamp(2.4rem, 1.04vw + 2.01rem, 4rem);
}

.rental-closed__info-header {
    display: flex;
    align-items: center;
    gap: clamp(0.8rem, 0.13vw + 0.75rem, 1.2rem);
    margin-bottom: clamp(1.6rem, 0.52vw + 1.41rem, 2.4rem);
}

.rental-closed__info-header i {
    font-size: clamp(1.8rem, 0.26vw + 1.7rem, 2.2rem);
    color: var(--clr-primary-def, #3549ff);
}

.rental-closed__info-header h3 {
    font-size: clamp(1.6rem, 0.52vw + 1.41rem, 2.4rem);
    font-weight: 600;
    color: var(--clr-text-title, #000);
    line-height: 1.41;
}

.rental-closed__info-desc {
    font-size: clamp(1.4rem, 0.26vw + 1.3rem, 1.8rem);
    line-height: 1.618;
    color: var(--clr-text-body, rgba(0, 0, 0, 0.8));
    margin-bottom: clamp(2rem, 0.78vw + 1.71rem, 3.2rem);
}

.rental-closed__info-desc strong {
    font-weight: 600;
    color: var(--clr-text-title, #000);
}

.rental-closed__contacts {
    display: flex;
    flex-direction: column;
    gap: clamp(1.2rem, 0.26vw + 1.1rem, 1.6rem);
}

.rental-closed__contact {
    display: flex;
    align-items: flex-start;
    gap: clamp(1.2rem, 0.26vw + 1.1rem, 1.6rem);
    padding: clamp(1.6rem, 0.52vw + 1.41rem, 2.4rem);
    background: var(--clr-background-notice-default, #fafafa);
    border-radius: clamp(0.8rem, 0.26vw + 0.7rem, 1.2rem);
}

.rental-closed__contact-icon {
    flex-shrink: 0;
    width: clamp(3.6rem, 0.52vw + 3.41rem, 4.4rem);
    height: clamp(3.6rem, 0.52vw + 3.41rem, 4.4rem);
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: clamp(0.6rem, 0.13vw + 0.55rem, 0.8rem);
    background: var(--clr-primary-50, #ebf4ff);
}

.rental-closed__contact-icon i {
    font-size: clamp(1.6rem, 0.26vw + 1.5rem, 2rem);
    color: var(--clr-primary-def, #3549ff);
}

.rental-closed__contact-body {
    flex: 1;
    min-width: 0;
}

.rental-closed__contact-name {
    font-size: clamp(1.4rem, 0.26vw + 1.3rem, 1.8rem);
    font-weight: 600;
    color: var(--clr-text-title, #000);
    line-height: 1.41;
    margin-bottom: clamp(0.4rem, 0.07vw + 0.38rem, 0.6rem);
}

.rental-closed__contact-detail {
    font-size: clamp(1.3rem, 0.19vw + 1.23rem, 1.6rem);
    color: var(--clr-text-body, rgba(0, 0, 0, 0.8));
    line-height: 1.618;
}

.rental-closed__contact-detail a.--tel {
    color: var(--clr-text-body, rgba(0, 0, 0, 0.8));
    text-decoration: none;
}

.rental-closed__contact-detail .--sep {
    margin: 0 0.4em;
    opacity: 0.4;
}

.rental-closed__contact-detail a.--email {
    color: var(--clr-primary-def, #3549ff);
    text-decoration: underline;
    text-underline-offset: 3px;
}

.rental-closed__action {
    text-align: center;
    margin-top: clamp(3.2rem, 1.55vw + 2.62rem, 5.6rem);
}

.rental-closed__btn {
    display: inline-flex;
    align-items: center;
    gap: clamp(0.8rem, 0.13vw + 0.75rem, 1.2rem);
    padding: clamp(1.4rem, 0.26vw + 1.3rem, 1.8rem) clamp(2.8rem, 0.78vw + 2.51rem, 4rem);
    background: var(--clr-background-primary-default, #3549ff);
    color: #fff;
    font-size: clamp(1.4rem, 0.26vw + 1.3rem, 1.8rem);
    font-weight: 600;
    line-height: 1.41;
    border-radius: clamp(0.8rem, 0.26vw + 0.7rem, 1.2rem);
    text-decoration: none;
    transition: background 0.2s ease;
}

.rental-closed__btn:hover {
    background: var(--clr-background-primary-hover, #202fe2);
}

.rental-closed__btn i {
    font-size: 0.9em;
}
</style>

<div class=" no-section-md">
    <?= include_view('components.sub-visual', ['title' => '대관 신청']) ?>

    <?php if (!$canApply): ?>
    <section class="no-sub-apply">
        <div class="no-container-xl">
            <div class="rental-closed">

                <!-- 메인 카드 -->
                <div class="rental-closed__card">
                    <div class="rental-closed__icon">
                        <i class="fa-regular fa-calendar-xmark"></i>
                    </div>

                    <h2 class="rental-closed__title">현재는 대관 접수 기간이 아닙니다</h2>

                    <p class="rental-closed__desc">본 페이지는 <strong>우리카드홀 / 우리투자증권홀</strong> 대관 신청을 위한 페이지 입니다.</p>
                    <p class="rental-closed__desc">향후 <strong>공지사항 게시판</strong>을 통해 대관 공고문을 확인 바랍니다.</p>
                    <p class="rental-closed__desc">그 외 리허설룸은 별도 공고 없이 수시 대관으로 진행됩니다.</p>

                    <?php if ($rentalStartDateFormatted && $rentalEndDateFormatted): ?>
                    <p class="rental-closed__badge">신청 기간 : <?= $rentalStartDateFormatted ?> ~
                        <?= $rentalEndDateFormatted ?></p>
                    <?php endif; ?>
                </div>

                <hr class="rental-closed__divider">

                <!-- 수시 대관 안내 -->
                <div class="rental-closed__info">
                    <p class="rental-closed__info-desc">
                        하단 담당자 정보 및 대관안내 페이지를 참고하시어 문의해주시기 바랍니다.
                    </p>

                    <!-- 담당자 정보 -->
                    <div class="rental-closed__contacts">
                        <div class="rental-closed__contact">
                            <div class="rental-closed__contact-icon">
                                <i class="fa-solid fa-building" aria-hidden="true"></i>
                            </div>
                            <div class="rental-closed__contact-body">
                                <p class="rental-closed__contact-name">우리카드홀 / 우리투자증권홀</p>
                                <p class="rental-closed__contact-detail">
                                    <a href="tel:02-6004-6722" class="--tel">02-6004-6722</a>
                                    <span class="--sep">/</span>
                                    <a href="mailto:kihyun123v@nol-theater.com"
                                        class="--email">kihyun123v@nol-theater.com</a>
                                </p>
                            </div>
                        </div>

                        <div class="rental-closed__contact">
                            <div class="rental-closed__contact-icon">
                                <i class="fa-solid fa-door-open" aria-hidden="true"></i>
                            </div>
                            <div class="rental-closed__contact-body">
                                <p class="rental-closed__contact-name">리허설룸1,2</p>
                                <p class="rental-closed__contact-detail">
                                    <a href="tel:02-6004-6919" class="--tel">02-6004-6919</a>
                                    <span class="--sep">/</span>
                                    <a href="mailto:paran2025@nol-theater.com"
                                        class="--email">paran2025@nol-theater.com</a>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 대관안내 보기 버튼 -->
                <div class="rental-closed__action">
                    <a href="<?= route('rental.procedure') ?>" class="rental-closed__btn">
                        <span>대관안내 보기</span>
                        <i class="fa-regular fa-arrow-right"></i>
                    </a>
                </div>

            </div>
        </div>
    </section>
    <?php else: ?>
    <section class="no-sub-apply">
        <div class="no-container-xl">

            <form action="/module/request.rental.php" method="post" class="no-form-container" id="rental-apply-form"
                enctype="multipart/form-data">
                <div class="no-sub-apply-inner">

                    <div class="no-sub-apply-form">

                        <!-- Notice -->
                        <div class="no-sub-apply-notice">
                            <div class="no-sub-apply-notice-title">
                                <i class="fa-regular fa-circle-exclamation"></i>
                                <h2 class="f-body-2 --bold">NOTICE</h2>
                            </div>
                            <div class="no-sub-apply-notice-content f-body-3 no-sub-apply-notice-content--html">
                                <?php
                                if ($rentalNotice) {
                                    echo strip_tags($rentalNotice, '<p><br><strong><b><em><i><u><s><span><div><h1><h2><h3><h4><h5><h6><ul><ol><li><a><font>');
                                } else {
                                    echo '<p class="no-sub-apply-notice-content__empty">등록된 안내 문구가 없습니다.</p>';
                                }
                                ?>
                            </div>
                        </div>
                        <div class="no-form-container">
                            <!-- 대관구분 -->
                            <div class="no-form-group-row">
                                <div class="no-form-group-radio --full">
                                    <label class="no-form-group__label">대관구분</label>
                                    <div class="no-radio-group">
                                        <label class="no-radio">
                                            <input type="radio" name="venue" value="woori-card" class="no-radio__input"
                                                checked>
                                            <span class="no-radio__visual"></span>
                                            <span>우리카드홀</span>
                                        </label>
                                        <label class="no-radio">
                                            <input type="radio" name="venue" value="woori-securities"
                                                class="no-radio__input">
                                            <span class="no-radio__visual"></span>
                                            <span>우리투자증권홀</span>
                                        </label>
                                    </div>
                                </div>
                                <div class="no-form-group --full">
                                    <label class="no-form-group__label">단체(공연제작사)명</label>
                                    <div class="no-form-control">
                                        <input type="text" name="organization" class="no-form-control__input"
                                            placeholder="단체(공연제작사)명을 입력하세요">
                                    </div>
                                </div>
                            </div>

                            <div class="no-form-group-row">
                                <div class="no-form-group --full">
                                    <label class="no-form-group__label">공연명</label>
                                    <div class="no-form-control">
                                        <input type="text" name="performance_name" class="no-form-control__input"
                                            placeholder="공연명을 입력하세요">
                                    </div>
                                </div>
                                <div class="no-form-group --full">
                                    <label class="no-form-group__label">담당자명</label>
                                    <div class="no-form-control">
                                        <input type="text" name="contact_name" class="no-form-control__input"
                                            placeholder="담당자명을 입력하세요">
                                    </div>
                                </div>
                            </div>

                            <div class="no-form-group-row">
                                <div class="no-form-group --full">
                                    <label class="no-form-group__label">담당자 연락처</label>
                                    <div class="no-form-control">
                                        <input type="tel" name="phone" class="no-form-control__input"
                                            placeholder="담당자 연락처를 입력하세요">
                                    </div>
                                </div>
                                <div class="no-form-group --full">
                                    <label class="no-form-group__label">담당자 이메일</label>
                                    <div class="no-form-control">
                                        <input type="email" name="email" class="no-form-control__input"
                                            placeholder="담당자 이메일을 입력하세요">
                                    </div>
                                </div>
                            </div>



                            <!-- 내용 -->
                            <div class="no-form-group --full">
                                <label class="no-form-group__label">내용</label>
                                <div class="no-form-control --textarea">
                                    <textarea name="content" class="no-form-control__textarea"
                                        placeholder="내용을 입력하세요"></textarea>
                                </div>
                            </div>

                            <!-- 개인정보 수집 및 이용안내 -->
                            <div class="no-form-privacy">
                                <div class="no-form-privacy-head">
                                    <h3 class="no-form-privacy__title">개인정보 수집 및 이용안내</h3>
                                    <div class="no-form-checkbox">
                                        <div class="no-form-checkbox__inner">
                                            <input type="checkbox" id="privacy-agree" name="privacy_agree" checked>
                                            <label for="privacy-agree" class="no-form-checkbox__box">
                                                <i class="fa-solid fa-check"></i>
                                            </label>
                                            <label for="privacy-agree"
                                                class="no-form-checkbox__label f-body-2 --regular">개인정보
                                                수집에 동의합니다
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <div class="no-form-privacy__content" data-lenis-prevent>
                                    <div class="privacy-info">
                                        <p class="privacy-info__title"><strong>[NOL 씨어터] 대관신청 개인정보 수집 및 이용 안내</strong></p>
                                        <p class="privacy-info__desc">NOL 씨어터는 서비스 제공을 위하여 아래와 같이 회원의 개인정보를 수집 및 활용합니다.</p>

                                        <ul class="privacy-info__list">
                                            <li><strong>개인정보 수집 및 이용 목적:</strong> 대관 계약 접수, 대관 계약 진행</li>
                                            <li><strong>수집하는 개인정보 항목:</strong> 성명, 이메일, 연락처</li>
                                            <li><strong>개인정보 보유 및 이용 기간:</strong>
                                                <ul class="privacy-info__sublist">
                                                    <li>대관 미선정작: 대관 심의 종료 후 30일 內 파기</li>
                                                    <li>대관 선정작: 대관 종료 후 회계 정산 종료 시점으로 부터 30일 內 파기. 단, 관계 법령에 따라 보관이 필요한 경우 해당 보유기간 까지 보유</li>
                                                </ul>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <!-- 스팸방지문자 -->
                            <div class="no-form-group --full">
                                <label class="no-form-group__label">스팸방지문자</label>
                                <div class="no-form-captcha">
                                    <div class="no-form-captcha__display">
                                        <div class="no-form-captcha__image-wrap">
                                            <img src="/captcha/default.php" alt="캡차" class="no-form-captcha__image"
                                                id="captcha-image">
                                        </div>
                                        <button type="button" class="no-form-captcha__refresh" id="captcha-refresh"
                                            aria-label="캡차 새로고침">
                                            <i class="fa-solid fa-rotate"></i>
                                        </button>
                                    </div>
                                    <div class="no-form-control">
                                        <input type="text" name="captcha" class="no-form-control__input"
                                            placeholder="스팸방지문자 5글자를 입력해주세요" maxlength="5">
                                    </div>
                                </div>
                            </div>
                            <!-- 첨부파일 -->
                            <div class="no-form-group --full">
                                <label class="no-form-group__label">첨부파일</label>
                                <div class="no-form-file-upload">
                                    <div class="no-form-file-upload__dropzone" id="file-dropzone">
                                        <div class="no-form-file-upload__dropzone-head">
                                            <i class="fa-regular fa-file"></i>
                                            <p class="no-form-file-upload__instruction">클릭하여 파일을 선택해주세요</p>
                                        </div>
                                        <div class="no-form-file-upload__info">
                                            <p>파일당 20mb 이하, 첨부파일은 최대 5개까지 가능합니다.</p>
                                            <p>영상 및 음원 등 고용량 파일은 클라우드 링크 등으로 첨부 바랍니다.</p>
                                            <p>zip, xls, xlsx, pdf, ppt, pptx, word, doc, docx, hwp 확장자만 첨부 가능합니다.</p>
                                        </div>
                                        <input type="file" class="no-form-file-upload__input" id="file-input" multiple
                                            accept=".zip,.xls,.xlsx,.pdf,.ppt,.pptx,.doc,.docx,.hwp">
                                    </div>
                                    <ul class="no-form-file-upload__list" id="file-list"></ul>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="no-sub-apply-submit">
                        <div class="no-sub-apply-submit-inner">
                            <button type="submit" class="no-submit-button">
                                <span class="no-button__text">신청하기</span>
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </section>
    <?php endif; ?>
</div>

<?php end_section() ?>

<?php section('portal') ?>
<?= include_view('components.popup'); ?>
<?php end_section() ?>
