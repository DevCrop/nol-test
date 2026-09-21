<?php
include_once "../../../inc/lib/base.class.php";
$role->redirectIfReadOnly();

try {
    $db = DB::getInstance();
} catch (Exception $e) {
    ClientFault::abortPage($e);
    exit;
}

// 최대 sort_order 조회
$maxSortStmt = $db->query("SELECT COALESCE(MAX(sort_order), 0) + 1 as next_sort FROM nb_works");
$nextSort = $maxSortStmt->fetchColumn();

include_once "../../inc/admin.title.php";
include_once "../../inc/admin.css.php";
include_once "../../inc/admin.js.php";
?>

</head>

<body data-page="works">
    <div class="no-wrap">
        <?php include_once "../../inc/admin.header.php"; ?>

        <main class="no-app no-container">
            <?php include_once "../../inc/admin.drawer.php"; ?>

            <form id="frm" method="post" enctype="multipart/form-data" action="./process.php">
                <input type="hidden" name="mode" value="insert">
                <input type="hidden" name="sort_order" value="<?= $nextSort ?>">

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

                                <!-- 제목 -->
                                <div class="no-admin-block">
                                    <h3 class="no-admin-title"><label for="title">제목 *</label></h3>
                                    <div class="no-admin-content">
                                        <input type="text" id="title" name="title" placeholder="제목을 입력해주세요." required>
                                    </div>
                                </div>

                                <!-- 소제목 -->
                                <div class="no-admin-block">
                                    <h3 class="no-admin-title"><label for="subtitle">소제목</label></h3>
                                    <div class="no-admin-content">
                                        <input type="text" id="subtitle" name="subtitle" placeholder="소제목을 입력해주세요.">
                                    </div>
                                </div>

                                <!-- 장르 -->
                                <div class="no-admin-block">
                                    <h3 class="no-admin-title"><label for="genre">장르</label></h3>
                                    <div class="no-admin-content">
                                        <select name="genre" id="genre">
                                            <option value="">선택</option>
                                            <?php foreach ($works_genre as $key => $label): ?>
                                            <option value="<?= $key ?>"><?= htmlspecialchars($label) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>

                                <!-- 공연장 -->
                                <div class="no-admin-block">
                                    <h3 class="no-admin-title"><label for="venue">공연장</label></h3>
                                    <div class="no-admin-content">
                                        <select name="venue" id="venue">
                                            <option value="">선택</option>
                                            <?php foreach ($works_venue as $key => $label): ?>
                                            <option value="<?= $key ?>"><?= htmlspecialchars($label) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>

                                <!-- 공연기간 -->
                                <div class="no-admin-block">
                                    <h3 class="no-admin-title">공연기간</h3>
                                    <div class="no-admin-content no-admin-date">
                                        <input type="text" name="start_date" id="start_date" placeholder="시작일">
                                        <span>~</span>
                                        <input type="text" name="end_date" id="end_date" placeholder="종료일">
                                    </div>
                                </div>

                                <!-- 공연시간 정보 -->
                                <div class="no-admin-block">
                                    <h3 class="no-admin-title"><label for="running_time">러닝타임</label></h3>
                                    <div class="no-admin-content">
                                        <input type="text" id="running_time" name="running_time"
                                            placeholder="예: 150분 (인터미션 20분 포함)">
                                    </div>
                                </div>

                                <!-- 관람연령 -->
                                <div class="no-admin-block">
                                    <h3 class="no-admin-title"><label for="age_rating">관람연령</label></h3>
                                    <div class="no-admin-content">
                                        <input type="text" id="age_rating" name="age_rating" placeholder="예: 만 7세 이상">
                                    </div>
                                </div>

                                <!-- 문의 -->
                                <div class="no-admin-block">
                                    <h3 class="no-admin-title"><label for="inquiry">문의</label></h3>
                                    <div class="no-admin-content">
                                        <input type="text" id="inquiry" name="inquiry" placeholder="문의처를 입력해주세요.">
                                    </div>
                                </div>

                                <!-- 티켓 예매 링크 -->
                                <div class="no-admin-block">
                                    <h3 class="no-admin-title"><label for="ticket_url">티켓 예매 링크</label></h3>
                                    <div class="no-admin-content">
                                        <input type="url" id="ticket_url" name="ticket_url" placeholder="https://">
                                    </div>
                                </div>

                                <!-- 비고 -->
                                <div class="no-admin-block">
                                    <h3 class="no-admin-title"><label for="note">비고</label></h3>
                                    <div class="no-admin-content">
                                        <textarea id="note" name="note" rows="3" placeholder="비고를 입력해주세요."></textarea>
                                    </div>
                                </div>

                                <!-- 좌석별 가격 -->
                                <div class="no-admin-block">
                                    <h3 class="no-admin-title"><label for="seat_prices">좌석별 가격</label></h3>
                                    <div class="no-admin-content">
                                        <textarea id="seat_prices" name="seat_prices" rows="5"
                                            placeholder="좌석별 가격 정보를 입력해주세요. (JSON 형식 또는 텍스트)"></textarea>
                                    </div>
                                </div>

                                <!-- 썸네일 포스터 -->
                                <div class="no-admin-block">
                                    <h3 class="no-admin-title"><label for="thumb_image">썸네일 포스터</label></h3>
                                    <div class="no-admin-content">
                                        <div class="no-file-control">
                                            <input type="text" class="no-fake-file" id="fakeThumbFileTxt"
                                                placeholder="파일을 선택해주세요." readonly disabled />
                                            <div class="no-file-box">
                                                <input type="file" name="thumb_image" id="thumb_image" accept="image/*"
                                                    onchange="document.getElementById('fakeThumbFileTxt').value = this.files[0] ? this.files[0].name : ''; handleThumbImagePreview(this);" />
                                                <button type="button" class="no-btn no-btn--main">파일찾기</button>
                                            </div>
                                        </div>
                                        <div id="thumb_image_preview" style="margin-top: 10px;"></div>
                                    </div>
                                </div>

                                <!-- 포스터 이미지 (긴 포스터) -->
                                <div class="no-admin-block">
                                    <h3 class="no-admin-title"><label for="poster_long_html">포스터 이미지 (긴 포스터)</label>
                                    </h3>
                                    <div class="no-admin-content">
                                        <textarea id="poster_long_html" name="poster_long_html"></textarea>
                                    </div>

                                </div>

                                <!-- 내용 입력란 -->
                                <div class="no-admin-block">
                                    <h3 class="no-admin-title"><label for="content_html">내용</label></h3>
                                    <div class="no-admin-content">
                                        <textarea id="content_html" name="content_html"></textarea>
                                    </div>

                                </div>

                                <!-- 노출 여부 -->
                                <div class="no-admin-block">
                                    <h3 class="no-admin-title">노출 여부</h3>
                                    <div class="no-admin-content">
                                        <div class="no-radio-form no-list">
                                            <?php foreach ($is_active as $value => $label):
                                                $current_value = 1;
                                            ?>
                                            <?php
                                                $id = 'active_' . $value;
                                                $checked = ($current_value == $value) ? 'checked' : '';
                                                ?>
                                            <label for="<?= $id ?>">
                                                <div class="no-radio-box">
                                                    <input type="radio" name="is_published" id="<?= $id ?>"
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
                                    <a href="./index.php" class="no-btn no-btn--big no-btn--normal">목록</a>
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
        // jQuery UI Selectmenu 초기화
        $('#genre, #venue').selectmenu();

        // jQuery UI Datepicker 설정
        $.datepicker.setDefaults({
            dateFormat: "yy-mm-dd",
            prevText: "이전 달",
            nextText: "다음 달",
            monthNames: [
                "1월", "2월", "3월", "4월", "5월", "6월",
                "7월", "8월", "9월", "10월", "11월", "12월"
            ],
            monthNamesShort: [
                "1월", "2월", "3월", "4월", "5월", "6월",
                "7월", "8월", "9월", "10월", "11월", "12월"
            ],
            dayNames: ["일", "월", "화", "수", "목", "금", "토"],
            dayNamesShort: ["일", "월", "화", "수", "목", "금", "토"],
            dayNamesMin: ["일", "월", "화", "수", "목", "금", "토"],
            showMonthAfterYear: true,
            yearSuffix: "년",
        });

        // 공연기간 Datepicker 초기화
        $('#start_date').datepicker();
        $('#end_date').datepicker({
            onSelect: function(selectedDate) {
                // 종료일이 시작일보다 이전이면 시작일로 설정
                var startDate = $('#start_date').datepicker('getDate');
                if (startDate && selectedDate < startDate) {
                    $(this).datepicker('setDate', startDate);
                }
            }
        });

        // 시작일 변경 시 종료일 최소값 설정
        $('#start_date').on('change', function() {
            var startDate = $(this).datepicker('getDate');
            if (startDate) {
                $('#end_date').datepicker('option', 'minDate', startDate);
            }
        });

        // 썸네일 이미지 미리보기 함수
        window.handleThumbImagePreview = function(input) {
            var file = input.files[0];
            if (file) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    $('#thumb_image_preview').html('<img src="' + e.target.result +
                        '" style="max-width: 200px; max-height: 200px; margin-top: 10px;">');
                };
                reader.readAsDataURL(file);
            } else {
                $('#thumb_image_preview').html('');
            }
        };

        // Summernote 초기화 (이미지 업로드 콜백 포함)
        $('#poster_long_html, #content_html, #note, #seat_prices').summernote({
            height: 300,
            lang: 'ko-KR',
            toolbar: [
                ['style', ['style']],
                ['font', ['bold', 'italic', 'underline', 'clear']],
                ['fontname', ['fontname']],
                ['color', ['color']],
                ['para', ['ul', 'ol', 'paragraph']],
                ['table', ['table']],
                ['insert', ['link', 'picture', 'video']],
                ['view', ['fullscreen', 'codeview', 'help']]
            ],
            callbacks: {
                onInit: function() {
                    var self = this;
                    setTimeout(function() {
                        var $editable = $(self).next('.note-editor').find('.note-editable');
                        if ($editable.length) convertEmptyPToDiv($editable[0]);
                    }, 100);
                },
                onImageUpload: function(files) {
                    var editor = $(this);
                    for (var i = 0; i < files.length; i++) {
                        uploadSummernoteImage(files[i], editor);
                    }
                }
            }
        });

        // Summernote 이미지 업로드 함수
        function uploadSummernoteImage(file, editor) {
            var formData = new FormData();
            formData.append('file', file);
            formData.append('_method', 'post');

            var ext = file.name.split('.').pop().toLowerCase();
            formData.append('extension', ext);

            $.ajax({
                url: './ajax/upload.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',
                success: function(response) {
                    if (response.success && response.filename) {
                        editor.summernote('insertImage', response.filename);
                    } else {
                        alert('이미지 업로드에 실패했습니다.');
                    }
                },
                error: function() {
                    alert('이미지 업로드 중 오류가 발생했습니다.');
                }
            });
        }

        // 폼 제출
        $('#frm').on('submit', function(e) {
            e.preventDefault();

            // Summernote 내용을 textarea에 동기화
            $('#poster_long_html').val($('#poster_long_html').summernote('code'));
            $('#content_html').val($('#content_html').summernote('code'));
            $('#note').val($('#note').summernote('code'));
            $('#seat_prices').val($('#seat_prices').summernote('code'));

            var formData = new FormData(this);

            $.ajax({
                url: './process.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        alert(response.message);
                        location.href = './index.php';
                    } else {
                        alert(response.message || '오류가 발생했습니다.');
                    }
                },
                error: function() {
                    alert('서버 오류가 발생했습니다.');
                }
            });
        });
    });
    </script>

</body>

</html>