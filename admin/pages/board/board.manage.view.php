<?php include_once "../../../inc/lib/base.class.php"; ?>
<!DOCTYPE html>
<html lang="ko">
<?php

$connect = DB::getInstance(); // PDO 인스턴스

$depthnum = 1;
$pagenum = 1;

$no = $_REQUEST['no'] ?? null;

try {
    // 데이터 가져오기
    $query = "SELECT * FROM nb_board_manage WHERE no = :no";
    $stmt = $connect->prepare($query);
    $stmt->execute([':no' => $no]);
    $data = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$data) {
        throw new Exception("정보를 찾을 수 없습니다.");
    }
} catch (Exception $e) {
    blue_safe_error($e); echo "<script>alert('요청을 처리할 수 없습니다.');</script>";
    exit;
}

include_once "../../inc/admin.title.php";
include_once "../../inc/admin.css.php";
include_once "../../inc/admin.js.php";
?>
<style>
/* 사용자 정의 스타일 추가 */
</style>
</head>

<body>
<div class="no-wrap">
    <!-- Header -->
    <?php include_once "../../inc/admin.header.php"; ?>

    <!-- Main -->
    <main class="no-app no-container">
        <!-- Drawer -->
        <?php include_once "../../inc/admin.drawer.php"; ?>

        <!-- Contents -->
        <form id="frm" name="frm" method="post" enctype="multipart/form-data">
            <input type="hidden" id="mode" name="mode" value="">
            <input type="hidden" id="no" name="no" value="<?= htmlspecialchars($data['no']) ?>">

            <section class="no-content">
                <!-- Page Title -->
                <div class="no-toolbar">
                    <div class="no-toolbar-container no-flex-stack">
                        <div class="no-page-indicator">
                            <h1 class="no-page-title">게시글 관리</h1>
                            <div class="no-breadcrumb-container">
                                <ul class="no-breadcrumb-list">
                                    <li class="no-breadcrumb-item"><span>게시판</span></li>
                                    <li class="no-breadcrumb-item"><span>게시글 관리</span></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- card-title -->
                <div class="no-toolbar-container">
                    <div class="no-card">
                        <div class="no-card-header no-card-header--detail">
                            <h2 class="no-card-title">게시판 등록</h2>
                        </div>
                        <div class="no-card-body no-admin-column no-admin-column--detail">
                            <div class="no-admin-block">
                                <h3 class="no-admin-title">
                                    <label for="skin">게시판 선택</label>
                                </h3>
                                <div class="no-admin-content">
                                    <select name="skin" id="skin">
                                        <option value="">타입선택</option>
                                        <?php
                                        foreach ($board_type as $key => $val) {
                                            $selected = ($data['skin'] == $key) ? "selected" : "";
                                            echo "<option value=\"{$key}\" {$selected}>{$val}</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                            <!-- admin-block -->

                            <div class="no-admin-block">
                                <h3 class="no-admin-title">
                                    <label for="title">게시판 이름</label>
                                </h3>
                                <div class="no-admin-content">
                                    <input type="text" name="title" id="title" class="no-input--detail" placeholder="게시판 이름을 입력해주세요." value="<?= htmlspecialchars($data['title']) ?>" />
                                </div>
                            </div>
                            <!-- admin-block -->

                            <div class="no-admin-block">
                                <h3 class="no-admin-title">
                                    <label for="top_banner_image">상단 이미지</label>
                                </h3>
                                <div class="no-admin-content">
                                    <?php if (!empty($data['top_banner_image'])) : ?>
                                        <div class="no-banner-image">
                                            <img src="<?= htmlspecialchars($UPLOAD_WDIR_BOARD . '/' . $data['top_banner_image']) ?>" alt="<?= htmlspecialchars($data['title']) . ' 상단 이미지' ?>" />
                                        </div>
                                    <?php endif; ?>
                                    <div class="no-file-control">
                                        <input type="text" class="no-fake-file" id="fakeThumbFileTxt" placeholder="파일을 선택해주세요." readonly disabled />
                                        <div class="no-file-box">
                                            <input type="file" name="top_banner_image" id="top_banner_image" onchange="javascript:document.getElementById('fakeThumbFileTxt').value = this.value" />
                                            <button type="button" class="no-btn no-btn--main">파일찾기</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- admin-block -->

                            <div class="no-admin-block">
                                <h3 class="no-admin-title">
                                    <label for="content">상단 HTML</label>
                                </h3>
                                <div class="no-admin-content">
                                    <div class="no-admin-check">
                                        <textarea name="content" id="content" class="SEditor"><?= htmlspecialchars($data['contents']) ?></textarea>
                                    </div>
                                </div>
                            </div>
                            <!-- admin-block -->

                            <?php
                            // Radio 버튼 그룹을 생성하는 함수
                            function renderRadioGroup($name, $checkedValue, $label) {
                                $checkedYes = ($checkedValue == "Y") ? "checked" : "";
                                $checkedNo = ($checkedValue == "N") ? "checked" : "";
                                echo "
                                <div class=\"no-admin-block\">
                                    <h3 class=\"no-admin-title\"><label for=\"$name\">$label</label></h3>
                                    <div class=\"no-admin-content\">
                                        <div class=\"no-radio-form\">
                                            <label for=\"{$name}_1\">
                                                <div class=\"no-radio-box\">
                                                    <input type=\"radio\" name=\"$name\" id=\"{$name}_1\" value=\"Y\" $checkedYes>
                                                    <span><i class=\"bx bx-radio-circle-marked\"></i></span>
                                                </div>
                                                <span class=\"no-radio-text\">사용</span>
                                            </label>
                                            <label for=\"{$name}_2\">
                                                <div class=\"no-radio-box\">
                                                    <input type=\"radio\" name=\"$name\" id=\"{$name}_2\" value=\"N\" $checkedNo>
                                                    <span><i class=\"bx bx-radio-circle-marked\"></i></span>
                                                </div>
                                                <span class=\"no-radio-text\">미사용</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>";
                            }

                            // 라디오 버튼 렌더링
                            renderRadioGroup("view_yn", $data['view_yn'], "노출");
                            renderRadioGroup("secret_yn", $data['secret_yn'], "비밀글 사용여부");
                            renderRadioGroup("comment_yn", $data['comment_yn'], "댓글 사용여부");
                            renderRadioGroup("category_yn", $data['category_yn'], "카테고리 사용여부");
                            renderRadioGroup("depth_category_yn", $data['depth_category_yn'], "카테고리 분류 사용여부");
                            renderRadioGroup("fileattach_yn", $data['fileattach_yn'], "파일첨부 사용여부");
                            ?>

                            <div class="no-admin-block">
                                <h3 class="no-admin-title">
                                    <label for="fileattach_cnt">파일첨부 갯수</label>
                                </h3>
                                <div class="no-admin-content">
                                    <select name="fileattach_cnt" id="fileattach_cnt" <?= ($data['fileattach_yn'] == "N") ? "disabled" : "" ?>>
                                        <option value="">선택</option>
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <option value="<?= $i ?>" <?= ($data['fileattach_cnt'] == (string)$i) ? "selected" : "" ?>><?= $i ?>개</option>
                                        <?php endfor; ?>
                                    </select>
                                </div>
                            </div>
                            <!-- admin-block -->

                            <!-- 기타 필드들 -->
                            <div class="no-admin-block">
                                <h3 class="no-admin-title">
                                    <label for="list_size">목표 출력 카운트</label>
                                </h3>
                                <div class="no-admin-content">
                                    <input type="text" name="list_size" id="list_size" class="no-input--detail" value="<?= htmlspecialchars($data['list_size']) ?>" placeholder="목록에 노출될 데이터 숫자" />
                                </div>
                            </div>
                            <!-- admin-block -->

                            <?php
                            // 추가 필드 처리
                            for ($i = 1; $i <= $NO_EXTRA_FIELDS_COUNT; $i++) {
                                $extraField = htmlspecialchars($data["extra_match_field{$i}"]);
                                echo "
                                <div class=\"no-admin-block\">
                                    <h3 class=\"no-admin-title\">
                                        <label for=\"extra_match_field{$i}\">추가필드{$i}</label>
                                    </h3>
                                    <div class=\"no-admin-content\">
                                        <input type=\"text\" name=\"extra_match_field{$i}\" id=\"extra_match_field{$i}\" class=\"no-input--detail\" value=\"$extraField\" placeholder=\"추가필드{$i}\" />
                                    </div>
                                </div>";
                            }
                            ?>

                            <div class="no-items-center center">
                                <a href="javascript:void(0);" class="no-btn no-btn--big no-btn--delete-outline" onClick="doDelete(<?= htmlspecialchars($data['no']) ?>);">삭제</a>
                                <a href="./board.manage.list.php" class="no-btn no-btn--big no-btn--normal">목록</a>
                                <a href="javascript:void(0);" class="no-btn no-btn--big no-btn--main" onClick="doEditSave();">수정</a>
                            </div>
                        </div>
                        <!-- card-body -->
                    </div>
                </div>
            </section>
        </form>
    </main>

    <!-- Footer -->
    <script type="text/javascript" src="./js/board.manage.process.js?c=<?= $STATIC_ADMIN_JS_MODIFY_DATE ?>"></script>
    <?php include_once "../../inc/admin.footer.php"; ?>
</div>
</body>
</html>
