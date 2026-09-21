<?php include_once $_SERVER['DOCUMENT_ROOT'].'/inc/lib/base.class.php'; ?>
<?php include_once $_SERVER['DOCUMENT_ROOT'].'/inc/lib/TimedValues.php'; ?>

<?php

		$pdo = DB::getInstance();

	 try {
        $current_date = date('Y-m-d');

        $query = "SELECT r_sdate, r_edate FROM nb_request_manage WHERE :current_date BETWEEN r_sdate AND r_edate LIMIT 1";

        $stmt = $pdo->prepare($query);
        $stmt->execute(['current_date' => $current_date]);

        $data = $stmt->fetch(PDO::FETCH_ASSOC);
		$is_form_visible = $data ? true : false;
    }  
	catch (Exception $e) {
		$is_form_visible = false; // 에러 발생 시 폼 숨기기
		error_log("Error fetching date range: " . blue_safe_error($e));
	}
?>

<!-- dev -->

<?php include_once $STATIC_ROOT.'/inc/layouts/head.php'; ?>

<!-- css, js  -->
<?php 
    include_once $STATIC_ROOT.'/inc/layouts/header.php';

?>

<!-- contents -->
<main class="no-sub ">

    <?php if ($is_form_visible) :?>

    <section class="no-sub-inquiry-new-none no-pd-2xl--y">
        <div class="no-container-lg">
            <div class="no-sub-inquiry-new-none__inner">
                <div class="no-sub-inquiry-new-none__box ">
                    <div class="title">
                        <h2 class="no-heading-lg " <?=$aos_title?>>
                            본 페이지는 <?= isUpdateActive() ? '우리은행홀' : '신한카드홀' ?><br>
                            대관 신청을 위한 페이지입니다.
                        </h2>
                        <div class="txt no-pd-xl--t " <?=$aos_content?>>
                            <p class="no-body-md --fw-regular">
                                <?= isUpdateActive() ? '우리은행홀' : '신한카드홀' ?> 대관 신청을 희망하실 경우
                                공지사항 &lt;<?= isUpdateActive() ? '우리은행홀' : '신한카드홀' ?> 대관 공고문&gt;을 참고하시어, <br>
                                접수 기간 동안 신청해주시기 바랍니다. (하단 '대관 신청하기' 버튼 클릭)
                            </p>
                            <p class="no-body-md --fw-regular">
                                그 외
                                공간(<?= isUpdateActive() ? ($hallName === 'SOL트래블홀' ? '우리WON뱅킹홀' : $hallName) : $hallName ?>,
                                NEMO, 연습실)은 별도 공고 없이 수시 대관으로 진행됩니다. <br>
                                하단 담당자 정보 및 대관안내 페이지를 참고하시어 문의해 주시기 바랍니다.
                            </p>
                        </div>
                        <div class="cnt no-pd-xl--t " <?=$aos_content?>>
                            <ul class="grid-col-2 no-gap-lg">
                                <li class="--card">
                                    <h4 class="no-heading-md"><?= isUpdateActive() ? '우리은행홀' : '신한카드홀' ?></h4>
                                    <div class="no-pd-md--t no-body-md">
                                        <ul class="f fd-c  no-gap-xs">
                                            <li class="f ai-c no-gap-sm">
                                                <i class="fa-solid fa-phone"></i>
                                                <span>02 - 6399 - 7486</span>
                                            </li>
                                            <li class="f ai-c no-gap-sm">
                                                <i class="fa-solid fa-envelope"></i>
                                                <span>tintin@nol-theater.com</span>
                                            </li>
                                        </ul>
                                    </div>
                                </li>
                                <li class="--card">
                                    <h4 class="no-heading-md">
                                        <?= isUpdateActive() ? ($hallName === 'SOL트래블홀' ? '우리WON뱅킹홀' : $hallName) : $hallName ?>
                                    </h4>
                                    <div class="no-pd-md--t no-body-md">
                                        <ul class="f fd-c  no-gap-xs">
                                            <li class="f ai-c no-gap-sm">
                                                <i class="fa-solid fa-phone"></i>
                                                <span>02 - 6399 - 7515</span>
                                            </li>
                                            <li class="f ai-c no-gap-sm">
                                                <i class="fa-solid fa-envelope"></i>
                                                <span>biglucky@nol-theater.com</span>
                                            </li>
                                        </ul>
                                    </div>
                                </li>
                                <li class="--card">
                                    <h4 class="no-heading-md">NEMO</h4>
                                    <div class="no-pd-md--t no-body-md">
                                        <ul class="f fd-c  no-gap-xs">
                                            <li class="f ai-c no-gap-sm">
                                                <i class="fa-solid fa-phone"></i>
                                                <span>02 - 6399 - 7552</span>
                                            </li>
                                            <li class="f ai-c no-gap-sm">
                                                <i class="fa-solid fa-envelope"></i>
                                                <span>pdh424@nol-theater.com</span>
                                            </li>
                                        </ul>
                                    </div>
                                </li>
                                <li class="--card">
                                    <h4 class="no-heading-md">연습실</h4>
                                    <div class="no-pd-md--t no-body-md">
                                        <ul class="f fd-c  no-gap-xs">
                                            <li class="f ai-c no-gap-sm">
                                                <i class="fa-solid fa-phone"></i>
                                                <span>02 - 6399 - 7552</span>
                                            </li>
                                            <li class="f ai-c no-gap-sm">
                                                <i class="fa-solid fa-envelope"></i>
                                                <span>pdh424@nol-theater.com</span>
                                            </li>
                                        </ul>
                                    </div>
                                </li>
                            </ul>

                        </div>
                        <div class="no-form-action no-pd-xl--t" <?=$aos_content?>>
                            <a href="./inquiry.php" class="no-btn-arrow no-btn-arrow__outline--white  dif">
                                대관 신청하기
                                <span>
                                    <i class="fa-regular fa-arrow-right" aria-hidden="true"></i>
                                    <i class="fa-regular fa-arrow-right" aria-hidden="true"></i>
                                </span>
                            </a>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <?php else: ?>

    <section class="no-sub-inquiry-new-none no-pd-2xl--y">
        <div class="no-container-lg">
            <div class="no-sub-inquiry-new-none__inner">
                <div class="no-sub-inquiry-new-none__box ">
                    <div class="title">
                        <h2 class="no-heading-lg no-section-title">
                            본 페이지는 <?= isUpdateActive() ? '우리은행홀' : '신한카드홀' ?> <br>
                            대관 신청을 위한 페이지입니다.
                        </h2>
                        <div class="txt no-pd-xl--t no-section-content">
                            <p class="no-body-md --fw-regular">
                                현재는 <?= isUpdateActive() ? '우리은행홀' : '신한카드홀' ?> 대관 접수 기간이 아닙니다. <br>
                                향후 공지사항 게시판의 대관 공고문을 확인 바랍니다.
                            </p>
                            <p class="no-body-md --fw-regular">
                                그 외
                                공간(<?= isUpdateActive() ? ($hallName === 'SOL트래블홀' ? '우리WON뱅킹홀' : $hallName) : $hallName ?>,
                                NEMO, 연습실)은 별도 공고 없이 수시 대관으로 진행됩니다. <br>
                                하단 담당자 정보 및 대관안내 페이지를 참고하시어 문의해 주시기 바랍니다.
                            </p>
                        </div>
                        <div class="cnt no-pd-xl--t no-section-content">
                            <ul class="grid-col-2 no-gap-lg">
                                <li class="--card">
                                    <h4 class="no-heading-md"><?= isUpdateActive() ? '우리은행홀' : '신한카드홀' ?></h4>
                                    <div class="no-pd-md--t no-body-md">
                                        <ul class="f fd-c  no-gap-xs">
                                            <li class="f ai-c no-gap-sm">
                                                <i class="fa-solid fa-phone"></i>
                                                <span>02 - 6399 - 7486</span>
                                            </li>
                                            <li class="f ai-c no-gap-sm">
                                                <i class="fa-solid fa-envelope"></i>
                                                <span>tintin@nol-theater.com</span>
                                            </li>
                                        </ul>
                                    </div>
                                </li>
                                <li class="--card">
                                    <h4 class="no-heading-md">
                                        <?= isUpdateActive() ? ($hallName === 'SOL트래블홀' ? '우리WON뱅킹홀' : $hallName) : $hallName ?>
                                    </h4>
                                    <div class="no-pd-md--t no-body-md">
                                        <ul class="f fd-c  no-gap-xs">
                                            <li class="f ai-c no-gap-sm">
                                                <i class="fa-solid fa-phone"></i>
                                                <span>02 - 6399 - 7515</span>
                                            </li>
                                            <li class="f ai-c no-gap-sm">
                                                <i class="fa-solid fa-envelope"></i>
                                                <span>biglucky@nol-theater.com</span>
                                            </li>
                                        </ul>
                                    </div>
                                </li>
                                <li class="--card">
                                    <h4 class="no-heading-md">NEMO</h4>
                                    <div class="no-pd-md--t no-body-md">
                                        <ul class="f fd-c  no-gap-xs">
                                            <li class="f ai-c no-gap-sm">
                                                <i class="fa-solid fa-phone"></i>
                                                <span>02 - 6399 - 7552</span>
                                            </li>
                                            <li class="f ai-c no-gap-sm">
                                                <i class="fa-solid fa-envelope"></i>
                                                <span>pdh424@nol-theater.com</span>
                                            </li>
                                        </ul>
                                    </div>
                                </li>
                                <li class="--card">
                                    <h4 class="no-heading-md">연습실</h4>
                                    <div class="no-pd-md--t no-body-md">
                                        <ul class="f fd-c  no-gap-xs">
                                            <li class="f ai-c no-gap-sm">
                                                <i class="fa-solid fa-phone"></i>
                                                <span>02 - 6399 - 7552</span>
                                            </li>
                                            <li class="f ai-c no-gap-sm">
                                                <i class="fa-solid fa-envelope"></i>
                                                <span>pdh424@nol-theater.com</span>
                                            </li>
                                        </ul>
                                    </div>
                                </li>
                            </ul>

                        </div>
                        <div class="no-form-action no-pd-xl--t no-section-content">
                            <a href="<?=$ROOT?>/pages/rental/process.php"
                                class="no-btn-arrow no-btn-arrow__outline--white  dif">
                                대관안내 보기
                                <span>
                                    <i class="fa-regular fa-arrow-right"></i>
                                    <i class="fa-regular fa-arrow-right"></i>
                                </span>
                            </a>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>



    <?php endif; ?>

</main>


<script>
function updateLordIconColors() {
    const rootStyles = getComputedStyle(document.documentElement);
    const primaryColor = rootStyles.getPropertyValue('--clr-text-invert').trim();
    const secondaryColor = rootStyles.getPropertyValue('--clr-text-primary').trim();

    const lordIcons = document.querySelectorAll('.lord-icon');
    lordIcons.forEach(icon => {
        icon.setAttribute('colors', `primary:${primaryColor},secondary:${secondaryColor}`);
    });
}

updateLordIconColors();

// Observe changes to the "data-theme" attribute on the <html> element
const lordIconObserver = new MutationObserver(mutations => {
    mutations.forEach(mutation => {
        if (mutation.type === 'attributes' && mutation.attributeName === 'data-theme') {
            updateLordIconColors(); // Re-run the function when data-theme changes
        }
    });
});

// Start observing the <html> element
lordIconObserver.observe(document.documentElement, {
    attributes: true
});
</script>







<?php
    include_once $STATIC_ROOT.'/inc/layouts/footer.php';
    ?>