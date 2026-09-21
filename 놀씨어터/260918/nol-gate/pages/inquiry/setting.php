<?php
include_once "../../../inc/lib/base.class.php";
$role->redirectIfReadOnly();

// 현재 설정값 가져오기
$db = DB::getInstance();
$rental_apply_notice = '';
$rental_notice = '';
try {
    $stmt = $db->prepare("SELECT rental_is_open, rental_start_date, rental_end_date, rental_notice, rental_apply_notice FROM nb_siteinfo WHERE sitekey = :sitekey LIMIT 1");
    $stmt->execute([':sitekey' => $NO_SITE_UNIQUE_KEY]);
    $rentalSetting = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
    $stmt = $db->prepare("SELECT rental_is_open, rental_start_date, rental_end_date, rental_notice FROM nb_siteinfo WHERE sitekey = :sitekey LIMIT 1");
    $stmt->execute([':sitekey' => $NO_SITE_UNIQUE_KEY]);
    $rentalSetting = $stmt->fetch(PDO::FETCH_ASSOC);
}
if (!$rentalSetting) {
    $rentalSetting = [];
}

$rental_is_open = isset($rentalSetting['rental_is_open']) ? (int)$rentalSetting['rental_is_open'] : 0;
$rental_start_date = $rentalSetting['rental_start_date'] ?? '';
$rental_end_date = $rentalSetting['rental_end_date'] ?? '';
$rental_notice = $rentalSetting['rental_notice'] ?? '';
$rental_apply_notice = $rentalSetting['rental_apply_notice'] ?? '';

include_once "../../inc/admin.title.php";
include_once "../../inc/admin.css.php";
include_once "../../inc/admin.js.php";
?>
</head>

<body>
    <div class="no-wrap">
        <!-- Header -->
        <?php
        include_once "../../inc/admin.header.php";
        ?>

        <!-- Main -->
        <main class="no-app no-container">
            <!-- Drawer -->
            <?php
            include_once "../../inc/admin.drawer.php";
            ?>

            <!-- Contents -->
            <form id="frm" name="frm" method="post" enctype="multipart/form-data" autocomplete="off">
                <input type="hidden" id="mode" name="mode" value="">
                <section class="no-content">
                    <!-- Page Title -->
                    <div class="no-toolbar">
                        <div class="no-toolbar-container no-flex-stack">
                            <div class="no-page-indicator">
                                <h1 class="no-page-title">대관 신청 설정</h1>
                                <div class="no-breadcrumb-container">
                                    <ul class="no-breadcrumb-list">
                                        <li class="no-breadcrumb-item">
                                            <span>대관 신청 관리</span>
                                        </li>
                                        <li class="no-breadcrumb-item">
                                            <span>대관 신청 설정</span>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <!-- page indicator -->
                        </div>
                    </div>

                    <!-- card-title -->
                    <div class="no-toolbar-container">
                        <div class="no-card">
                            <div class="no-card-header no-card-header--detail">
                                <h2 class="no-card-title">대관 신청 관리</h2>
                                <div class="no-admin-header">
                                    <span class="no-admin-info">
                                        <i class="bx bxs-info-circle"></i>
                                        대관 신청 폼의 열기/닫기 및 신청 기간을 설정합니다.
                                    </span>
                                </div>
                            </div>
                            <div class="no-card-body no-admin-column no-admin-column--detail">
                                <div class="no-admin-block">
                                    <h3 class="no-admin-title">
                                        <label>대관 신청 상태</label>
                                    </h3>
                                    <div class="no-admin-content">
                                        <div class="no-radio-form no-list">
                                            <label for="rental_open">
                                                <div class="no-radio-box">
                                                    <input type="radio" name="rental_is_open" id="rental_open" value="1"
                                                        <?= $rental_is_open == 1 ? 'checked' : '' ?>>
                                                    <span><i class="bx bx-radio-circle-marked"></i></span>
                                                </div>
                                                <span class="no-radio-text">열기</span>
                                            </label>
                                            <label for="rental_close">
                                                <div class="no-radio-box">
                                                    <input type="radio" name="rental_is_open" id="rental_close"
                                                        value="0" <?= $rental_is_open == 0 ? 'checked' : '' ?>>
                                                    <span><i class="bx bx-radio-circle-marked"></i></span>
                                                </div>
                                                <span class="no-radio-text">닫기</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <!-- admin-block -->

                                <div class="no-admin-block" id="rental-period-block"
                                    style="<?= $rental_is_open == 0 ? 'display: none;' : 'display: flex;' ?>">
                                    <h3 class="no-admin-title">대관 신청 기간</h3>
                                    <div class="no-admin-content no-admin-date">
                                        <input type="text" name="rental_start_date" id="start_at"
                                            value="<?= $rental_start_date ?>" />
                                        <span></span>
                                        <input type="text" name="rental_end_date" id="end_at"
                                            value="<?= $rental_end_date ?>" />
                                    </div>

                                </div>
                                <!-- admin-block -->

                                <div class="no-admin-block">
                                    <h3 class="no-admin-title">
                                        <label for="rental_notice">대관 신청 안내 문구</label>
                                    </h3>
                                    <div class="no-admin-content">
                                        <textarea name="rental_notice" id="rental_notice" class="no-input--detail no-summernote-rental-notice"
                                            placeholder="대관 신청 안내 문구를 입력하세요" rows="5" style="resize: vertical; min-height: 160px;"><?= str_replace('</textarea>', '&lt;/textarea&gt;', $rental_notice) ?></textarea>
                                        <span class="no-admin-info">
                                            <i class="bx bxs-info-circle"></i>
                                            대관 신청 가이드 페이지(브릿지)에 노출됩니다. 폰트 크기, 색상, 굵게/기울임 등 서식을 적용할 수 있습니다.
                                        </span>
                                    </div>
                                </div>
                                <!-- admin-block -->

                                <div class="no-admin-block">
                                    <h3 class="no-admin-title"><label for="rental_apply_notice">접수페이지 NOTICE</label></h3>
                                    <div class="no-admin-content">
                                        <textarea name="rental_apply_notice" id="rental_apply_notice" class="no-input--detail no-summernote-rental-apply-notice"
                                            placeholder="대관 신청 접수 폼 상단 NOTICE 영역 문구 (폰트/색상 등 서식 가능)" rows="5" style="min-height: 160px;"><?= str_replace('</textarea>', '&lt;/textarea&gt;', $rental_apply_notice) ?></textarea>
                                        <span class="no-admin-info"><i class="bx bxs-info-circle"></i> 대관 신청하기 버튼 클릭 후 접수 폼 상단에 노출됩니다.</span>
                                    </div>
                                </div>

                                <div class="no-items-center center" style="margin-top: 2rem;">
                                    <a href="javascript:void(0);" class="no-btn no-btn--big no-btn--main"
                                        onClick="doRentalSettingSave();">
                                        저장
                                    </a>
                                </div>
                                <!-- admin-block -->
                            </div>
                        </div>
                        <!-- card -->
                    </div>
                </section>
            </form>
        </main>

        <!-- Footer -->
        <script>
        (function() {
            function initRentalNoticeSummernote() {
                if (typeof $ === 'undefined' || typeof $.fn.summernote === 'undefined') return;
                var $ta = $('#rental_notice');
                if (!$ta.length || $ta.data('summernote')) return;
                $ta.summernote({
                    height: 220,
                    lang: 'ko-KR',
                    styleTags: ['p', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6'],
                    toolbar: [
                        ['style', ['style']],
                        ['font', ['bold', 'italic', 'underline', 'strikethrough']],
                        ['fontname', ['fontname']],
                        ['fontsize', ['fontsize']],
                        ['color', ['color']],
                        ['para', ['ul', 'ol', 'paragraph']],
                        ['insert', ['link']],
                        ['view', ['fullscreen', 'codeview', 'help']]
                    ],
                    fontSizes: ['8', '9', '10', '11', '12', '14', '16', '18', '20', '24', '36']
                });
            }
            function initApplyNoticeSummernote() {
                if (typeof $ === 'undefined' || typeof $.fn.summernote === 'undefined') return;
                var $ta = $('#rental_apply_notice');
                if (!$ta.length || $ta.data('summernote')) return;
                $ta.summernote({
                    height: 200,
                    lang: 'ko-KR',
                    toolbar: [
                        ['style', ['style']],
                        ['font', ['bold', 'italic', 'underline']],
                        ['fontsize', ['fontsize']],
                        ['color', ['color']],
                        ['para', ['ul', 'ol', 'paragraph']],
                        ['view', ['codeview', 'help']]
                    ]
                });
            }
            function init() {
                initRentalNoticeSummernote();
                initApplyNoticeSummernote();
            }
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', init);
            } else {
                init();
            }
        })();
        </script>
        <script type="text/javascript" src="./js/inquiry.setting.process.js?c=<?= $STATIC_ADMIN_JS_MODIFY_DATE ?>">
        </script>
        <script>
        // 라디오 버튼 변경 시 기간 설정 블록 표시/숨김
        document.addEventListener('DOMContentLoaded', function() {
            const rentalOpen = document.getElementById('rental_open');
            const rentalClose = document.getElementById('rental_close');
            const periodBlock = document.getElementById('rental-period-block');

            function togglePeriodBlocks() {
                if (rentalOpen.checked) {
                    periodBlock.style.display = 'flex';
                } else {
                    periodBlock.style.display = 'none';
                }
            }

            rentalOpen.addEventListener('change', togglePeriodBlocks);
            rentalClose.addEventListener('change', togglePeriodBlocks);
        });
        </script>
        <?php
        include_once "../../inc/admin.footer.php";
        ?>
    </div>
</body>

</html>