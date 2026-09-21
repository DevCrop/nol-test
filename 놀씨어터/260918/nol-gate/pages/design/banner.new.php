<?php
include_once "../../../inc/lib/base.class.php";
$role->redirectIfReadOnly();

try {
    $db = DB::getInstance(); 
} catch (Exception $e) {
    ClientFault::abortPage($e);
    exit;
}

include_once "../../inc/admin.title.php";
include_once "../../inc/admin.css.php";
include_once "../../inc/admin.js.php";
?>

</head>

<body data-page="banner">
    <div class="no-wrap">
        <?php include_once "../../inc/admin.header.php"; ?>

        <main class="no-app no-container">
            <?php include_once "../../inc/admin.drawer.php"; ?>

            <form id="frm" method="post" enctype="multipart/form-data">
                <input type="hidden" name="mode" value="insert">

                <section class="no-content">

                    <div class="no-toolbar">
                        <div class="no-toolbar-container no-flex-stack">
                            <div class="no-page-indicator">
                                <h1 class="no-page-title"><?= $pageName ?> 등록</h1>
                                <div class="no-breadcrumb-container">
                                    <ul class="no-breadcrumb-list">
                                        <li class="no-breadcrumb-item"><span><?= $pageName ?></span></li>
                                        <li class="no-breadcrumb-item"><span><?= $pageName ?> 등록</span></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="no-toolbar-container">
                        <div class="no-card">
                            <div class="no-card-header no-card-header--detail">
                                <h2 class="no-card-title"><?=$pageName?> 등록</h2>
                            </div>
                            <div class="no-card-body no-admin-column no-admin-column--detail">

                                <!-- 배너 타입 -->
                                <div class="no-admin-block">
                                    <h3 class="no-admin-title"><label for="banner_type">배너 위치</label></h3>
                                    <div class="no-admin-content">
                                        <select name="banner_type" id="banner_type" required>
                                            <option value="">선택</option>
                                            <?php foreach ($banner_types as $key => $label): ?>
                                            <option value="<?= $key ?>"><?= htmlspecialchars($label) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>

                                <!-- 홀 선택 -->
                                <div class="no-admin-block">
                                    <h3 class="no-admin-title"><label for="hall_id">홀 이름</label></h3>
                                    <div class="no-admin-content">
                                        <select name="hall_id" id="hall_id">
                                            <option value="">선택</option>
                                            <?php foreach ($works_venue as $key => $label): ?>
                                            <option value="<?= $key ?>"><?= htmlspecialchars($label) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>

                                <!-- 무기한 여부 -->
                                <div class="no-admin-block">
                                    <h3 class="no-admin-title">노출 설정</h3>
                                    <div class="no-admin-content">
                                        <div class="no-radio-form no-list">
                                            <?php foreach ($is_unlimited as $value => $label): 
                                                $id = "unlimited_$value";
                                                $checked = ($value == 1) ? 'checked' : ''; 
                                            ?>
                                            <label for="<?= $id ?>">
                                                <div class="no-radio-box">
                                                    <input type="radio" name="is_unlimited" id="<?= $id ?>"
                                                        value="<?= $value ?>" <?= $checked ?>>
                                                    <span><i class="bx bx-radio-circle-marked"></i></span>
                                                </div>
                                                <span class="no-radio-text"><?= htmlspecialchars($label) ?></span>
                                            </label>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                </div>


                                <!-- 공연일자 (텍스트 표시용) -->
                                <div class="no-admin-block">
                                    <h3 class="no-admin-title">공연일자</h3>
                                    <div class="no-admin-content no-admin-date">
                                        <input type="text" name="start_at" id="start_at"
                                            value="<?php echo isset($start_at) ? htmlspecialchars($start_at) : ''; ?>" />
                                        <span></span>
                                        <input type="text" name="end_at" id="end_at"
                                            value="<?php echo isset($end_at) ? htmlspecialchars($end_at) : ''; ?>" />
                                    </div>
                                </div>

                                <!-- 등록기간 (실제 노출 제어) -->
                                <div class="no-admin-block" id="display_period">
                                    <h3 class="no-admin-title">등록기간</h3>
                                    <div class="no-admin-content no-admin-date">
                                        <input type="text" name="display_start_at" id="display_start_at"
                                            value="<?php echo isset($display_start_at) ? htmlspecialchars($display_start_at) : ''; ?>" />
                                        <span></span>
                                        <input type="text" name="display_end_at" id="display_end_at"
                                            value="<?php echo isset($display_end_at) ? htmlspecialchars($display_end_at) : ''; ?>" />
                                    </div>
                                </div>


                                <!-- 제목 -->
                                <div class="no-admin-block">
                                    <h3 class="no-admin-title"><label for="title">제목</label></h3>
                                    <div class="no-admin-content">
                                        <input type="text" id="title" name="title" required>
                                    </div>
                                </div>

                                <!-- 설명글 -->
                                <div class="no-admin-block">
                                    <h3 class="no-admin-title"><label for="description">설명</label></h3>
                                    <div class="no-admin-content">
                                        <textarea name="description" id="description"></textarea>
                                    </div>
                                </div>

                                <!-- 링크 여부 -->
                                <div class="no-admin-block">
                                    <h3 class="no-admin-title">링크 여부</h3>
                                    <div class="no-admin-content">
                                        <div class="no-radio-form no-list">
                                            <?php foreach ($has_link as $value => $label): 
                                                $id = "link_$value";
                                                $checked = ($value == 2) ? 'checked' : '';
                                            ?>
                                            <label for="<?= $id ?>">
                                                <div class="no-radio-box">
                                                    <input type="radio" name="has_link" id="<?= $id ?>"
                                                        value="<?= $value ?>" <?= $checked ?>>
                                                    <span><i class="bx bx-radio-circle-marked"></i></span>
                                                </div>
                                                <span class="no-radio-text"><?= htmlspecialchars($label) ?></span>
                                            </label>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                </div>

                                <!-- 새창 여부 -->
                                <div class="no-admin-block" id="link_target_block">
                                    <h3 class="no-admin-title">새창 여부</h3>
                                    <div class="no-admin-content">
                                        <div class="no-radio-form no-list">
                                            <?php
                                                $current_target = isset($banner['is_target']) ? (int)$banner['is_target'] : 1;
                                                foreach ($link_targets as $val => $info):
                                                    $id = "target_$val";
                                                    $checked = ($current_target === $val) ? 'checked' : '';
                                            ?>
                                            <label for="<?= $id ?>">
                                                <div class="no-radio-box">
                                                    <input type="radio" name="is_target" id="<?= $id ?>"
                                                        value="<?= $val ?>" <?= $checked ?>>
                                                    <span><i class="bx bx-radio-circle-marked"></i></span>
                                                </div>
                                                <span
                                                    class="no-radio-text"><?= htmlspecialchars($info['label']) ?></span>
                                            </label>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                </div>



                                <!-- 링크 URL -->
                                <div class="no-admin-block" id="link_url_block">
                                    <h3 class="no-admin-title"><label for="link_url">링크 URL</label></h3>
                                    <div class="no-admin-content">
                                        <input type="url" id="link_url" name="link_url"
                                            placeholder="http:// 또는 https://">
                                    </div>
                                </div>

                                <!-- 배너 이미지 (데스크탑) -->
                                <div class="no-admin-block">
                                    <h3 class="no-admin-title"><label for="banner_image">배너 이미지 (데스크탑)</label></h3>
                                    <div class="no-admin-content">
                                        <div class="no-file-control">
                                            <input type="text" class="no-fake-file" id="fakeBannerFileTxt"
                                                placeholder="파일을 선택해주세요." readonly disabled />
                                            <div class="no-file-box">
                                                <input type="file" name="banner_image" id="banner_image"
                                                    onchange="previewBannerFile(this, 'bannerPreview', 'fakeBannerFileTxt')"
                                                    accept="image/*" />
                                                <button type="button" class="no-btn no-btn--main">파일찾기</button>
                                            </div>
                                        </div>

                                        <div class="no-image-preview" id="bannerPreviewContainer">
                                            <img id="bannerPreview" src="" alt="배너 미리보기"
                                                style="display:none; max-width:150px; margin-top:10px;">
                                        </div>

                                        <span class="no-admin-info"><i class="bx bxs-info-circle"></i>데스크탑 화면에 표시될 이미지입니다. (jpg, png, gif, webp)</span>
                                    </div>
                                </div>

                                <!-- 배너 이미지 (모바일) -->
                                <div class="no-admin-block">
                                    <h3 class="no-admin-title"><label for="banner_image_mobile">배너 이미지 (모바일)</label></h3>
                                    <div class="no-admin-content">
                                        <div class="no-file-control">
                                            <input type="text" class="no-fake-file" id="fakeBannerMobileFileTxt"
                                                placeholder="파일을 선택해주세요." readonly disabled />
                                            <div class="no-file-box">
                                                <input type="file" name="banner_image_mobile" id="banner_image_mobile"
                                                    onchange="previewBannerFile(this, 'bannerMobilePreview', 'fakeBannerMobileFileTxt')"
                                                    accept="image/*" />
                                                <button type="button" class="no-btn no-btn--main">파일찾기</button>
                                            </div>
                                        </div>

                                        <div class="no-image-preview" id="bannerMobilePreviewContainer">
                                            <img id="bannerMobilePreview" src="" alt="배너 모바일 미리보기"
                                                style="display:none; max-width:150px; margin-top:10px;">
                                        </div>

                                        <span class="no-admin-info"><i class="bx bxs-info-circle"></i>모바일 화면에 표시될 이미지입니다. (jpg, png, gif, webp) - 선택사항</span>
                                    </div>
                                </div>


                                <!-- 정렬 순서 -->
                                <!--
                                <div class="no-admin-block">
                                    <h3 class="no-admin-title"><label for="sort_no">정렬 순서</label></h3>
                                    <div class="no-admin-content">
                                        <input type="number" id="sort_no" name="sort_no" value="0" min="0">
                                    </div>
                                </div>-->







                                <!-- 버튼 -->
                                <div class="no-items-center center">
                                    <a href="./banner.list.php" class="no-btn no-btn--big no-btn--normal">목록</a>
                                    <button type="submit" class="no-btn no-btn--big no-btn--main"
                                        id="submitBtn">저장</button>
                                </div>

                            </div>

                        </div>
                    </div>

                </section>
            </form>

        </main>
        <?php include_once "../../inc/admin.footer.php"; ?>
    </div>

    <script>
        $(document).ready(function() {
            // Summernote 초기화
            $('#description').summernote({
                height: 300,
                lang: 'ko-KR',
                toolbar: [
                    ['style', ['style']],
                    ['font', ['bold', 'italic', 'underline', 'clear']],
                    ['fontname', ['fontname']],
                    ['color', ['color']],
                    ['para', ['ul', 'ol', 'paragraph']],
                    ['table', ['table']],
                    ['insert', ['link']],
                    ['view', ['fullscreen', 'codeview', 'help']]
                ],
                callbacks: {
                    onInit: function() {
                        var self = this;
                        setTimeout(function() {
                            var $editable = $(self).next('.note-editor').find('.note-editable');
                            if ($editable.length) convertEmptyPToDiv($editable[0]);
                        }, 100);
                    }
                }
            });

            // 폼 제출
            $('#frm').on('submit', function(e) {
                e.preventDefault();
                
                // Summernote 내용을 textarea에 동기화
                $('#description').val($('#description').summernote('code'));
                
                var formData = new FormData(this);
                
                $.ajax({
                    url: '../../Controller/BannerController.php',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            alert(response.message);
                            location.href = './banner.list.php';
                        } else {
                            alert(response.message || '등록에 실패했습니다.');
                        }
                    },
                    error: function() {
                        alert('처리 중 오류가 발생했습니다.');
                    }
                });
            });
        });
    </script>

</body>

</html>