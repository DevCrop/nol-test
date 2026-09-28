<?php
include_once "../../../inc/lib/base.class.php";

try {
    $db = DB::getInstance();
} catch (Exception $e) {
    ClientFault::abortPage($e);
    exit;
}

$id = $_GET['id'] ?? null;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$data = null;

if ($id) {
    $stmt = $db->prepare("SELECT * FROM nb_privacy_policy WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $data = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$data) {
        echo "<script>alert('존재하지 않는 개인정보처리방침입니다.'); history.back();</script>";
        exit;
    }
} else {
    echo "<script>alert('잘못된 접근입니다.'); history.back();</script>";
    exit;
}

include_once "../../inc/admin.title.php";
include_once "../../inc/admin.css.php";
include_once "../../inc/admin.js.php";
?>

<style>
/* 개인정보처리방침 수정 화면 서머노트: 글머리기호/표 가독성 개선 */
.note-editor .note-editable ul,
.note-editor .note-editable ol {
    padding-left: 1.4em;
    /* 글머리 기호와 본문 간 여백 */
}

.note-editor .note-editable li {
    letter-spacing: 0.03em;
    /* 글머리 기호 사용 시 자간 약간 넓게 */
}

/* 표 안 텍스트 자간도 살짝 여유 있게 */
.note-editor .note-editable table th,
.note-editor .note-editable table td {
    letter-spacing: 0.02em;
}
</style>

</head>

<body data-page="privacy">
    <div class="no-wrap">
        <?php include_once "../../inc/admin.header.php"; ?>

        <main class="no-app no-container">
            <?php include_once "../../inc/admin.drawer.php"; ?>

            <form id="frm" method="post" enctype="multipart/form-data">
                <input type="hidden" name="mode" value="update">
                <input type="hidden" name="id" value="<?= $_GET['id'] ?>">
                <input type="hidden" name="page" value="<?= $page ?>">

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

                                <!-- 제목 -->
                                <div class="no-admin-block">
                                    <h3 class="no-admin-title"><label for="title">제목</label></h3>
                                    <div class="no-admin-content">
                                        <input type="text" id="title" name="title" required
                                            value="<?= htmlspecialchars($data['title']) ?>" placeholder="제목을 입력해주세요.">
                                    </div>
                                </div>

                                <!-- 내용 -->
                                <?php $contentValue = htmlspecialchars_decode($data['content'] ?? ''); ?>
                                <div class="no-admin-block">
                                    <h3 class="no-admin-title"><label for="content">내용</label></h3>
                                    <div class="no-admin-content">
                                        <textarea id="content" name="content"><?= $contentValue ?></textarea>
                                    </div>
                                </div>

                                <!-- 적용 날짜 -->
                                <div class="no-admin-block">
                                    <h3 class="no-admin-title"><label for="apply_date">적용 날짜</label></h3>
                                    <div class="no-admin-content">
                                        <input type="text" id="apply_date" name="apply_date" required
                                            value="<?= $data['apply_date'] ? date('Y-m-d', strtotime($data['apply_date'])) : '' ?>"
                                            placeholder="적용 날짜를 선택해주세요.">
                                    </div>
                                </div>

                                <!-- 버튼 -->
                                <div class="no-items-center center">
                                    <a href="./index.php?page=<?= $page ?>"
                                        class="no-btn no-btn--big no-btn--normal">목록</a>
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
    // Word(한글/Office)에서 복붙한 내용 정리 헬퍼
    function cleanWordHtml(html) {
        if (!html) return html;

        var container = document.createElement('div');
        container.innerHTML = html;

        // 1) 주석 제거
        var walker = document.createTreeWalker(container, NodeFilter.SHOW_COMMENT, null, false);
        var comments = [];
        while (walker.nextNode()) {
            comments.push(walker.currentNode);
        }
        comments.forEach(function(node) {
            if (node.parentNode) node.parentNode.removeChild(node);
        });

        // 2) 스타일/클래스 정리
        //    - 워드에서 가져온 표/목록의 색상과 테두리는 살려 둔다
        var allowedStyleProps = [
            'text-align',
            'font-weight',
            'font-style',
            'text-decoration',
            'border',
            'border-collapse',
            'vertical-align',
            'background',
            'background-color',
            'color',
            'list-style-type'
        ];

        var all = container.getElementsByTagName('*');
        for (var i = 0; i < all.length; i++) {
            var el = all[i];

            // Mso* 클래스 제거
            if (el.className && /(^|\s)Mso/.test(el.className)) {
                el.removeAttribute('class');
            }

            // style 정리
            if (el.hasAttribute('style')) {
                var style = el.getAttribute('style') || '';
                var parts = style.split(';');
                var kept = [];

                parts.forEach(function(part) {
                    var idx = part.indexOf(':');
                    if (idx === -1) return;
                    var name = part.slice(0, idx).trim().toLowerCase();
                    var value = part.slice(idx + 1).trim();

                    // mso-*, 폰트 패밀리 관련은 제거
                    if (name.indexOf('mso-') === 0) return;
                    if (name === 'font-family' || name === 'mso-fareast-font-family') return;

                    // 허용 목록 + border-* 계열(각 셀 테두리) 허용
                    if (allowedStyleProps.indexOf(name) !== -1 || name.indexOf('border-') === 0) {
                        kept.push(name + ': ' + value);
                    }
                });

                if (kept.length > 0) {
                    el.setAttribute('style', kept.join('; '));
                } else {
                    el.removeAttribute('style');
                }
            }
        }

        // 3) 빈 블록 태그 정리 — 연속 빈 블록은 <p><br></p> 하나로 통합
        var html2 = container.innerHTML;

        // 빈 heading(h1~h6) 태그는 무조건 제거 (빈 제목은 의미 없음)
        html2 = html2.replace(/<(h[1-6])>(\s*(<br\s*\/?>|&nbsp;|\u00a0)\s*)*<\/\1>/gi, '');

        // 빈 p/div 패턴을 임시 마커로 변환
        html2 = html2.replace(/<(p|div)>(\s*(<br\s*\/?>|&nbsp;|\u00a0)\s*)*<\/\1>/gi, '<!--EMPTY_LINE-->');

        // 연속된 마커를 하나로 통합
        html2 = html2.replace(/(<!--EMPTY_LINE-->\s*){2,}/g, '<!--EMPTY_LINE-->');

        // 마커를 <div><br></div> 하나로 치환
        html2 = html2.replace(/<!--EMPTY_LINE-->/g, '<div><br></div>');

        // 맨 앞/뒤의 빈 줄 제거
        html2 = html2.replace(/^(\s*<div><br><\/div>\s*)+/, '');
        html2 = html2.replace(/(\s*<div><br><\/div>\s*)+$/, '');

        return html2.trim();
    }

    $(document).ready(function() {
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

        // 적용 날짜 Datepicker 초기화
        $('#apply_date').datepicker();

        // Summernote 초기화
        $('#content').summernote({
            height: 400,
            lang: 'ko-KR',
            styleTags: ['h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'p'],
            toolbar: [
                ['style', ['style']],
                ['font', ['bold', 'italic', 'underline']],
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
                    var code = $('#content').summernote('code');
                    var cleaned = cleanWordHtml(code);
                    if (code !== cleaned) {
                        $('#content').summernote('code', cleaned);
                    }
                    setTimeout(function() {
                        var $editable = $(self).next('.note-editor').find('.note-editable');
                        if ($editable.length) convertEmptyPToDiv($editable[0]);
                    }, 100);
                },
                onPaste: function(e) {
                    var self = this;
                    setTimeout(function() {
                        var code = $(self).summernote('code');
                        var cleaned = cleanWordHtml(code);
                        if (code !== cleaned) {
                            $(self).summernote('code', cleaned);
                        }
                        var $editable = $(self).next('.note-editor').find('.note-editable');
                        if ($editable.length) convertEmptyPToDiv($editable[0]);
                    }, 100);
                }
            }
        });

        // 폼 제출
        $('#frm').on('submit', function(e) {
            e.preventDefault();

            // Summernote 내용 가져오기 + Word 스타일 정리
            var content = $('#content').summernote('code');
            content = cleanWordHtml(content);

            $.ajax({
                url: './process.php',
                type: 'POST',
                data: {
                    mode: 'update',
                    id: $('input[name="id"]').val(),
                    title: $('#title').val(),
                    content: content,
                    apply_date: $('#apply_date').val()
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        alert(response.message);
                        location.href = './index.php?page=' + <?= $page ?>;
                    } else {
                        alert(response.message || '수정 중 오류가 발생했습니다.');
                    }
                },
                error: function() {
                    alert('수정 중 오류가 발생했습니다.');
                }
            });
        });
    });
    </script>
</body>

</html>