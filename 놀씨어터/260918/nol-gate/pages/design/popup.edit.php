<?php
include_once "../../../inc/lib/base.class.php";

try {
    $db = DB::getInstance();

    $id = $_GET['id'] ?? null;
    if (!$id) {
        echo "잘못된 접근입니다.";
        exit;
    }

    $stmt = $db->prepare("SELECT * FROM nb_popups WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $popup = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$popup) {
        echo "해당 팝업을 찾을 수 없습니다.";
        exit;
    }

    $router = app()->router();
    $buildRouteDisplayName = static function (string $routeName, string $uri): string {
        return $routeName !== '' ? $routeName . ' (' . $uri . ')' : $uri;
    };
    $buildRouteGroupLabel = static function (string $groupName): string {
        return $groupName !== '' ? strtoupper($groupName) : 'ROUTE';
    };

    // routes/web.php 파일을 include하여 라우트 등록
    $routesFile = dirname(__DIR__, 3) . '/routes/web.php';
    if (file_exists($routesFile)) {
        include_once $routesFile;
    }

    // 이름이 있는 라우트 목록 가져오기
    $namedRoutes = $router->getNamedRoutes();
    $routeOptions = [];

    // 허용된 라우트만 필터링 (홈과 customer.directions만)
    $allowedRoutes = ['home', 'customer.directions'];

    // 허용된 라우트만 직접 가져오기
    foreach ($allowedRoutes as $routeName) {
        if (!isset($namedRoutes[$routeName])) {
            continue;
        }

        $route = $namedRoutes[$routeName];
        $uri = $route->getUri();
        $method = $route->getMethod();

        // GET 메서드만 표시
        if ($method === 'GET') {
            // 한국어 표시명 생성
            $displayName = $buildRouteDisplayName($routeName, $uri);

            // 그룹명 추출 (첫 번째 점 이전)
            $parts = explode('.', $routeName);
            $groupName = $parts[0] ?? '';
            $groupLabel = $buildRouteGroupLabel($groupName);

            // 라우트 이름과 URI를 함께 저장
            $routeOptions[] = [
                'name' => $routeName,
                'uri' => $uri,
                'display' => $displayName,
                'group' => $groupName,
                'groupLabel' => $groupLabel
            ];
        }
    }

    // 그룹별로 정렬 (그룹명 -> 표시명)
    usort($routeOptions, function ($a, $b) {
        $groupCompare = strcmp($a['groupLabel'], $b['groupLabel']);
        if ($groupCompare !== 0) {
            return $groupCompare;
        }
        return strcmp($a['display'], $b['display']);
    });
} catch (Exception $e) {
    ClientFault::abortPage($e);
    exit;
}

include_once "../../inc/admin.title.php";
include_once "../../inc/admin.css.php";
include_once "../../inc/admin.js.php";
?>


</head>

<body data-page="popup">
    <div class="no-wrap">
        <?php include_once "../../inc/admin.header.php"; ?>

        <main class="no-app no-container">
            <?php include_once "../../inc/admin.drawer.php"; ?>

            <form id="frm" method="post" enctype="multipart/form-data">
                <input type="hidden" name="mode" value="update">
                <input type="hidden" name="id" value="<?= $popup['id'] ?>">
                <input type="hidden" name="sort_no" value="<?= $popup['sort_no'] ?>">

                <section class="no-content">
                    <div class="no-toolbar">
                        <div class="no-toolbar-container no-flex-stack">
                            <div class="no-page-indicator">
                                <h1 class="no-page-title"><?= $pageName ?> 수정</h1>
                                <div class="no-breadcrumb-container">
                                    <ul class="no-breadcrumb-list">
                                        <li class="no-breadcrumb-item"><span><?= $pageName ?></span></li>
                                        <li class="no-breadcrumb-item"><span><?= $pageName ?> 수정</span></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="no-toolbar-container">
                        <div class="no-card">
                            <div class="no-card-header no-card-header--detail">
                                <h2 class="no-card-title"><?= $pageName ?> 수정</h2>
                            </div>
                            <div class="no-card-body no-admin-column no-admin-column--detail">

                                <!-- 팝업 타입 -->
                                <div class="no-admin-block">
                                    <h3 class="no-admin-title"><label for="popup_type">팝업 타입</label></h3>
                                    <div class="no-admin-content">
                                        <select name="popup_type" id="popup_type" required>
                                            <option value="">팝업 타입 선택</option>
                                            <?php
                                            $currentPopupType = (int)($popup['popup_type'] ?? 0);
                                            foreach ($popup_types as $value => $label):
                                                $selected = ($currentPopupType === $value) ? 'selected' : '';
                                            ?>
                                            <option value="<?= $value ?>" <?= $selected ?>>
                                                <?= htmlspecialchars($label) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <div class="no-admin-desc" style="margin-top: 5px; color: #888;">
                                            팝업의 표시 형식을 선택하세요.
                                        </div>
                                    </div>
                                </div>

                                <!-- 팝업 위치 (경로 선택) -->
                                <div class="no-admin-block">
                                    <h3 class="no-admin-title"><label for="popup_path">팝업 위치</label></h3>
                                    <div class="no-admin-content">
                                        <select name="popup_path" id="popup_path"
                                            data-current="<?= htmlspecialchars($popup['popup_path'] ?? '') ?>" required>
                                            <option value="">페이지 경로 선택</option>
                                            <?php
                                            $currentGroup = '';
                                            $currentPath = $popup['popup_path'] ?? '';
                                            foreach ($routeOptions as $route):
                                                // 그룹이 변경되면 optgroup 시작/종료
                                                if ($currentGroup !== $route['groupLabel']):
                                                    if ($currentGroup !== ''):
                                                        echo '</optgroup>';
                                                    endif;
                                                    $currentGroup = $route['groupLabel'];
                                                    echo '<optgroup label="' . htmlspecialchars($route['groupLabel']) . '">';
                                                endif;
                                            ?>
                                            <option value="<?= htmlspecialchars($route['uri']) ?>"
                                                <?= $currentPath === $route['uri'] ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($route['display']) ?>
                                            </option>
                                            <?php endforeach; ?>
                                            <?php if ($currentGroup !== ''): ?>
                                            </optgroup>
                                            <?php endif; ?>
                                        </select>
                                        <div class="no-admin-desc" style="margin-top: 5px; color: #888;">
                                            팝업이 표시될 페이지 경로를 선택하세요.
                                        </div>
                                    </div>
                                </div>

                                <!-- 노출 설정 -->
                                <div class="no-admin-block">
                                    <h3 class="no-admin-title">노출 설정</h3>
                                    <div class="no-admin-content">
                                        <div class="no-radio-form no-list">
                                            <?php foreach ($is_unlimited as $val => $label):
                                                $id = "unlimited_$val";
                                                $checked = ((int)($popup['is_unlimited'] ?? 1) === $val) ? 'checked' : '';
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

                                <!-- 노출 기간 -->
                                <div class="no-admin-block" id="display_period">
                                    <h3 class="no-admin-title">노출 기간</h3>
                                    <div class="no-admin-content no-admin-date">
                                        <input type="text" name="start_at" id="start_at"
                                            value="<?= htmlspecialchars($popup['start_at'] ?? '') ?>" />
                                        <span></span>
                                        <input type="text" name="end_at" id="end_at"
                                            value="<?= htmlspecialchars($popup['end_at'] ?? '') ?>" />
                                    </div>
                                </div>

                                <!-- 제목 -->
                                <div class="no-admin-block">
                                    <h3 class="no-admin-title"><label for="title">제목</label></h3>
                                    <div class="no-admin-content">
                                        <input type="text" id="title" name="title"
                                            value="<?= htmlspecialchars($popup['title'] ?? '') ?>" required>
                                    </div>
                                </div>

                                <!-- 설명 -->
                                <div class="no-admin-block">
                                    <h3 class="no-admin-title"><label for="description">설명</label></h3>
                                    <div class="no-admin-content">
                                        <textarea name="description"
                                            id="description"><?= htmlspecialchars($popup['description'] ?? '') ?></textarea>
                                    </div>
                                </div>

                                <!-- 링크 여부 -->
                                <div class="no-admin-block">
                                    <h3 class="no-admin-title">링크 여부</h3>
                                    <div class="no-admin-content">
                                        <div class="no-radio-form no-list">
                                            <?php foreach ($has_link as $val => $label):
                                                $id = "link_$val";
                                                $checked = ((int)$popup['has_link'] === $val) ? 'checked' : '';
                                            ?>
                                            <label for="<?= $id ?>">
                                                <div class="no-radio-box">
                                                    <input type="radio" name="has_link" id="<?= $id ?>"
                                                        value="<?= $val ?>" <?= $checked ?>>
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
                                            $current_target = isset($popup['is_target']) ? (int)$popup['is_target'] : 1;

                                            foreach ($link_targets as $val => $info):
                                                $id = "link_target_$val";
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
                                            value="<?= htmlspecialchars($popup['link_url'] ?? '') ?>"
                                            placeholder="http:// 또는 https://">
                                    </div>
                                </div>

                                <!-- 팝업 이미지 -->
                                <div class="no-admin-block">
                                    <h3 class="no-admin-title"><label for="popup_image">팝업 이미지</label></h3>
                                    <div class="no-admin-content">
                                        <div class="no-file-control">
                                            <input type="text" class="no-fake-file" id="fakePopupFileTxt"
                                                placeholder="파일을 선택해주세요." readonly disabled
                                                value="<?= htmlspecialchars($popup['popup_image'] ?? '') ?>" />
                                            <div class="no-file-box">
                                                <input type="file" name="popup_image" id="popup_image" accept="image/*"
                                                    onchange="previewImage(this, 'popupPreview', 'fakePopupFileTxt')" />
                                                <button type="button" class="no-btn no-btn--main">파일찾기</button>
                                            </div>
                                        </div>

                                        <?php if (!empty($popup['popup_image'])): ?>
                                        <div class="no-image-preview">
                                            <img id="popupPreview" src="/uploads/popups/<?= $popup['popup_image'] ?>"
                                                alt="팝업 미리보기" style="max-width:150px; margin-top:10px;">
                                        </div>
                                        <?php else: ?>
                                        <div class="no-image-preview">
                                            <img id="popupPreview" src="" alt="팝업 미리보기"
                                                style="display:none; max-width:150px; margin-top:10px;">
                                        </div>
                                        <?php endif; ?>

                                        <span class="no-admin-info"><i class="bx bxs-info-circle"></i>팝업에 사용되는
                                            이미지입니다.</span>
                                    </div>
                                </div>

                                <!-- 정렬 순서 -->
                                <!--
                                <div class="no-admin-block">
                                    <h3 class="no-admin-title"><label for="sort_no">정렬 순서</label></h3>
                                    <div class="no-admin-content">
                                        <input type="number" id="sort_no" name="sort_no"
                                            value="<?= htmlspecialchars($popup['sort_no'] ?? 0) ?>" min="0">
                                    </div>
                                </div>-->

                                <!-- 노출 여부 -->
                                <div class="no-admin-block">
                                    <h3 class="no-admin-title">노출 여부</h3>
                                    <div class="no-admin-content">
                                        <div class="no-radio-form no-list">
                                            <?php foreach ($is_active as $value => $label):
                                                $id = "active_$value";
                                                $checked = ($popup['is_active'] == $value) ? 'checked' : '';
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
                                    <a href="./popup.list.php" class="no-btn no-btn--big no-btn--normal">목록</a>
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

        // 기존 내용 설정
        <?php if (!empty($popup['description'])): ?>
        $('#description').summernote('code',
            <?= json_encode($popup['description'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>);
        <?php endif; ?>

        setTimeout(function() {
            $('.note-editable').each(function() {
                convertEmptyPToDiv(this);
            });
        }, 200);

        // 폼 제출
        $('#frm').on('submit', function(e) {
            e.preventDefault();

            // Summernote 내용을 textarea에 동기화
            $('#description').val($('#description').summernote('code'));

            var formData = new FormData(this);

            $.ajax({
                url: '../../Controller/PopupController.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        alert(response.message);
                        location.href = './popup.list.php';
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
