<?php
include_once "../../../inc/lib/base.class.php";

try {
    $db = DB::getInstance();

    // ID로 해당 배너 불러오기
    $id = $_GET['id'] ?? null;

    if (!$id) {
        echo "잘못된 접근입니다.";
        exit;
    }

    $stmt = $db->prepare("SELECT * FROM nb_banners WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $banner = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$banner) {
        echo "해당 배너를 찾을 수 없습니다.";
        exit;
    }
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
                <input type="hidden" name="mode" value="update">
                <input type="hidden" name="id" value="<?= $banner['id'] ?>">
                <input type="hidden" name="sort_no" value="<?= $banner['sort_no'] ?>">
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
                                <h2 class="no-card-title"><?= $pageName ?> 등록</h2>
                            </div>
                            <div class="no-card-body no-admin-column no-admin-column--detail">

                                <!-- 배너 타입 -->
                                <div class="no-admin-block">
                                    <h3 class="no-admin-title"><label for="banner_type">배너 위치</label></h3>
                                    <div class="no-admin-content">
                                        <select name="banner_type" id="banner_type" required>
                                            <option value="">선택</option>
                                            <?php foreach ($banner_types as $key => $label): ?>
                                                <option value="<?= $key ?>"
                                                    <?= $banner['banner_type'] == $key ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($label) ?>
                                                </option>
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
                                                <option value="<?= $key ?>"
                                                    <?= isset($banner['hall_id']) && $banner['hall_id'] == $key ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($label) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>

                                <!-- 노출 설정 -->
                                <div class="no-admin-block">
                                    <h3 class="no-admin-title">노출 설정</h3>
                                    <div class="no-admin-content">
                                        <div class="no-radio-form no-list">
                                            <?php foreach ($is_unlimited as $val => $label):
                                                $id = "unlimited_$val";
                                                $checked = ((int)($banner['is_unlimited'] ?? 1) === $val) ? 'checked' : '';
                                            ?>
                                                <label for="<?= $id ?>">
                                                    <div class="no-radio-box">
                                                        <input type="radio" name="is_unlimited" id="<?= $id ?>"
                                                            value="<?= $val ?>" <?= $checked ?>>
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
                                            value="<?= htmlspecialchars($banner['start_at'] ?? '') ?>" />
                                        <span></span>
                                        <input type="text" name="end_at" id="end_at"
                                            value="<?= htmlspecialchars($banner['end_at'] ?? '') ?>" />
                                    </div>
                                </div>

                                <!-- 등록기간 (실제 노출 제어) -->
                                <div class="no-admin-block" id="display_period">
                                    <h3 class="no-admin-title">등록기간</h3>
                                    <div class="no-admin-content no-admin-date">
                                        <input type="text" name="display_start_at" id="display_start_at"
                                            value="<?= htmlspecialchars($banner['display_start_at'] ?? '') ?>" />
                                        <span></span>
                                        <input type="text" name="display_end_at" id="display_end_at"
                                            value="<?= htmlspecialchars($banner['display_end_at'] ?? '') ?>" />
                                    </div>
                                </div>




                                <!-- 제목 -->
                                <div class="no-admin-block">
                                    <h3 class="no-admin-title"><label for="title">제목</label></h3>
                                    <div class="no-admin-content">
                                        <input type="text" id="title" name="title"
                                            value="<?= htmlspecialchars($banner['title'] ?? '') ?>" required>

                                    </div>
                                </div>



                                <!-- 링크 여부 -->
                                <div class="no-admin-block">
                                    <h3 class="no-admin-title">링크 여부</h3>
                                    <div class="no-admin-content">
                                        <div class="no-radio-form no-list">
                                            <?php foreach ($has_link as $value => $label):
                                                $id = "link_$value";
                                                $checked = ($banner['has_link'] ?? 1) == $value ? 'checked' : '';
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
                                            <?php foreach ($link_targets as $val => $target):
                                                $id = "target_$val";
                                                $checked = ((int)($banner['is_target'] ?? 0) === $val) ? 'checked' : '';
                                            ?>
                                                <label for="<?= $id ?>">
                                                    <div class="no-radio-box">
                                                        <input type="radio" name="is_target" id="<?= $id ?>"
                                                            value="<?= $val ?>" <?= $checked ?>>
                                                        <span><i class="bx bx-radio-circle-marked"></i></span>
                                                    </div>
                                                    <span
                                                        class="no-radio-text"><?= htmlspecialchars($target['label']) ?></span>
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
                                            value="<?= htmlspecialchars($banner['link_url'] ?? '') ?>"
                                            placeholder="http:// 또는 https://">

                                    </div>
                                </div>

                                <!-- 배너 이미지 (데스크탑) -->
                                <div class="no-admin-block">
                                    <h3 class="no-admin-title"><label for="banner_image">배너 이미지 (데스크탑)</label></h3>
                                    <div class="no-admin-content">
                                        <div class="no-file-control">
                                            <input type="text" class="no-fake-file" id="fakeBannerFileTxt"
                                                placeholder="파일을 선택해주세요." readonly disabled
                                                value="<?= htmlspecialchars($banner['banner_image'] ?? '') ?>" />
                                            <div class="no-file-box">
                                                <input type="file" name="banner_image" id="banner_image"
                                                    accept="image/*"
                                                    onchange="previewBannerFile(this, 'bannerPreview', 'fakeBannerFileTxt')" />
                                                <button type="button" class="no-btn no-btn--main">파일찾기</button>
                                            </div>
                                        </div>

                                        <?php if (!empty($banner['banner_image'])): ?>
                                            <div class="no-image-preview" id="bannerPreviewContainer">
                                                <img id="bannerPreview" src="/uploads/banners/<?= $banner['banner_image'] ?>"
                                                    alt="배너 미리보기" style="max-width:150px; margin-top:10px;">
                                            </div>
                                        <?php else: ?>
                                            <div class="no-image-preview" id="bannerPreviewContainer">
                                                <img id="bannerPreview" src="" alt="배너 미리보기"
                                                    style="display:none; max-width:150px; margin-top:10px;">
                                            </div>
                                        <?php endif; ?>

                                        <span class="no-admin-info"><i class="bx bxs-info-circle"></i>데스크탑 화면에 표시될 이미지입니다. (jpg, png, gif, webp)</span>
                                    </div>
                                </div>

                                <!-- 배너 이미지 (모바일) -->
                                <div class="no-admin-block">
                                    <h3 class="no-admin-title"><label for="banner_image_mobile">배너 이미지 (모바일)</label></h3>
                                    <div class="no-admin-content">
                                        <div class="no-file-control">
                                            <input type="text" class="no-fake-file" id="fakeBannerMobileFileTxt"
                                                placeholder="파일을 선택해주세요." readonly disabled
                                                value="<?= htmlspecialchars($banner['banner_image_mobile'] ?? '') ?>" />
                                            <div class="no-file-box">
                                                <input type="file" name="banner_image_mobile" id="banner_image_mobile"
                                                    accept="image/*"
                                                    onchange="previewBannerFile(this, 'bannerMobilePreview', 'fakeBannerMobileFileTxt')" />
                                                <button type="button" class="no-btn no-btn--main">파일찾기</button>
                                            </div>
                                        </div>

                                        <?php if (!empty($banner['banner_image_mobile'])): ?>
                                            <div class="no-image-preview" id="bannerMobilePreviewContainer">
                                                <img id="bannerMobilePreview" src="/uploads/banners/<?= $banner['banner_image_mobile'] ?>"
                                                    alt="배너 모바일 미리보기" style="max-width:150px; margin-top:10px;">
                                            </div>
                                        <?php else: ?>
                                            <div class="no-image-preview" id="bannerMobilePreviewContainer">
                                                <img id="bannerMobilePreview" src="" alt="배너 모바일 미리보기"
                                                    style="display:none; max-width:150px; margin-top:10px;">
                                            </div>
                                        <?php endif; ?>

                                        <span class="no-admin-info"><i class="bx bxs-info-circle"></i>모바일 화면에 표시될 이미지입니다. (jpg, png, gif, webp) - 선택사항</span>
                                    </div>
                                </div>

                                <!-- 정렬 순서 -->
                                <!--
                                <div class="no-admin-block">
                                    <h3 class="no-admin-title"><label for="sort_no">정렬 순서</label></h3>
                                    <div class="no-admin-content">
                                        <input type="number" id="sort_no" name="sort_no"
                                            value="<?= htmlspecialchars($banner['sort_no'] ?? 0) ?>" min="0">
                                    </div>
                                </div>-->

                                <!-- 설명글 -->
                                <div class="no-admin-block">
                                    <h3 class="no-admin-title"><label for="description">설명</label></h3>
                                    <div class="no-admin-content">
                                        <textarea name="description" id="description"><?= htmlspecialchars($banner['description'] ?? '') ?></textarea>
                                    </div>
                                </div>


                                <!-- 노출 여부 -->
                                <div class="no-admin-block">
                                    <h3 class="no-admin-title">노출 여부</h3>
                                    <div class="no-admin-content">
                                        <div class="no-radio-form no-list">
                                            <?php foreach ($is_active as $value => $label):
                                                $id = "active_$value";
                                                $checked = ($banner['is_active'] == $value) ? 'checked' : '';
                                            ?>
                                                <label for="<?= $id ?>">
                                                    <div class="no-radio-box">
                                                        <input type="radio" name="is_active" id="<?= $id ?>"
                                                            value="<?= $value ?>" <?= $checked ?>>
                                                        <span><i class="bx bx-radio-circle-marked"></i></span>
                                                    </div>
                                                    <span class="no-radio-text"><?= htmlspecialchars($label) ?></span>
                                                </label>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                </div>

                                <!-- 버튼 -->
                                <div class="no-items-center center">
                                    <a href="./banner.list.php" class="no-btn no-btn--big no-btn--normal">목록</a>
                                    <?php if (!$role->isReadOnly()) : ?>
                                        <button type="submit" class="no-btn no-btn--big no-btn--main"
                                            id="editBtn">수정</button>
                                    <?php endif; ?>
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
            // Summernote 초기화 (BannerController에서도 처리하지만, 기존 내용 설정을 위해 여기서도 처리)
            if ($('#description').length > 0 && !$('#description').summernote('code')) {
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

                // 기존 내용 설정
                <?php if (!empty($banner['description'])): ?>
                    $('#description').summernote('code', <?= json_encode($banner['description'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>);
                <?php endif; ?>

                setTimeout(function() {
                    $('.note-editable').each(function() {
                        convertEmptyPToDiv(this);
                    });
                }, 200);
            }

            // 폼 제출: BannerController로 AJAX 전송 (데스크탑/모바일 이미지가 올바르게 /uploads/banners 로 저장되도록)
            $('#frm').on('submit', function(e) {
                e.preventDefault();
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
                            alert(response.message || '수정에 실패했습니다.');
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