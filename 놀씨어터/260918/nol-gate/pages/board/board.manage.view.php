<?php
include_once "../../../inc/lib/base.class.php";

$connect = DB::getInstance(); // PDO 인스턴스

//GET
$no = $_REQUEST['no'] ?? null;

try {
    $query = "SELECT * FROM nb_board_manage WHERE no = :no";
    $stmt = $connect->prepare($query);
    $stmt->execute([':no' => $no]);
    $data = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$data) {
        throw new Exception("정보를 찾을 수 없습니다.");
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
                                        <input type="text" name="title" id="title" class="no-input--detail"
                                            placeholder="게시판 이름을 입력해주세요."
                                            value="<?= htmlspecialchars($data['title']) ?>" />
                                    </div>
                                </div>

                                <div class="no-admin-block">
                                    <h3 class="no-admin-title"><label for="view_yn">노출</label></h3>
                                    <div class="no-admin-content">
                                        <div class="no-radio-form">
                                            <label for="view_yn_1">
                                                <div class="no-radio-box">
                                                    <input type="radio" name="view_yn" id="view_yn_1" value="Y"
                                                        <?= $data['view_yn'] === "Y" ? "checked" : "" ?>>
                                                    <span><i class="bx bx-radio-circle-marked"></i></span>
                                                </div>
                                                <span class="no-radio-text">사용</span>
                                            </label>
                                            <label for="view_yn_2">
                                                <div class="no-radio-box">
                                                    <input type="radio" name="view_yn" id="view_yn_2" value="N"
                                                        <?= $data['view_yn'] === "N" ? "checked" : "" ?>>
                                                    <span><i class="bx bx-radio-circle-marked"></i></span>
                                                </div>
                                                <span class="no-radio-text">미사용</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <div class="no-admin-block">
                                    <h3 class="no-admin-title"><label for="secret_yn">비밀글 사용여부</label></h3>
                                    <div class="no-admin-content">
                                        <div class="no-radio-form">
                                            <label for="secret_yn_1">
                                                <div class="no-radio-box">
                                                    <input type="radio" name="secret_yn" id="secret_yn_1" value="Y"
                                                        <?= $data['secret_yn'] === "Y" ? "checked" : "" ?>>
                                                    <span><i class="bx bx-radio-circle-marked"></i></span>
                                                </div>
                                                <span class="no-radio-text">사용</span>
                                            </label>
                                            <label for="secret_yn_2">
                                                <div class="no-radio-box">
                                                    <input type="radio" name="secret_yn" id="secret_yn_2" value="N"
                                                        <?= $data['secret_yn'] === "N" ? "checked" : "" ?>>
                                                    <span><i class="bx bx-radio-circle-marked"></i></span>
                                                </div>
                                                <span class="no-radio-text">미사용</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <div class="no-admin-block">
                                    <h3 class="no-admin-title"><label for="comment_yn">댓글 사용여부</label></h3>
                                    <div class="no-admin-content">
                                        <div class="no-radio-form">
                                            <label for="comment_yn_1">
                                                <div class="no-radio-box">
                                                    <input type="radio" name="comment_yn" id="comment_yn_1" value="Y"
                                                        <?= $data['comment_yn'] === "Y" ? "checked" : "" ?>>
                                                    <span><i class="bx bx-radio-circle-marked"></i></span>
                                                </div>
                                                <span class="no-radio-text">사용</span>
                                            </label>
                                            <label for="comment_yn_2">
                                                <div class="no-radio-box">
                                                    <input type="radio" name="comment_yn" id="comment_yn_2" value="N"
                                                        <?= $data['comment_yn'] === "N" ? "checked" : "" ?>>
                                                    <span><i class="bx bx-radio-circle-marked"></i></span>
                                                </div>
                                                <span class="no-radio-text">미사용</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <div class="no-admin-block">
                                    <h3 class="no-admin-title"><label for="category_yn">카테고리 사용여부</label></h3>
                                    <div class="no-admin-content">
                                        <div class="no-radio-form">
                                            <label for="category_yn_1">
                                                <div class="no-radio-box">
                                                    <input type="radio" name="category_yn" id="category_yn_1" value="Y"
                                                        <?= $data['category_yn'] === "Y" ? "checked" : "" ?>>
                                                    <span><i class="bx bx-radio-circle-marked"></i></span>
                                                </div>
                                                <span class="no-radio-text">사용</span>
                                            </label>
                                            <label for="category_yn_2">
                                                <div class="no-radio-box">
                                                    <input type="radio" name="category_yn" id="category_yn_2" value="N"
                                                        <?= $data['category_yn'] === "N" ? "checked" : "" ?>>
                                                    <span><i class="bx bx-radio-circle-marked"></i></span>
                                                </div>
                                                <span class="no-radio-text">미사용</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <!-- admin-block -->

                                <!-- 기타 필드들 -->
                                <div class="no-admin-block">
                                    <h3 class="no-admin-title">
                                        <label for="list_size">목표 출력 카운트</label>
                                    </h3>
                                    <div class="no-admin-content">
                                        <input type="text" name="list_size" id="list_size" class="no-input--detail"
                                            value="<?= htmlspecialchars($data['list_size']) ?>"
                                            placeholder="목록에 노출될 데이터 숫자" />
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
                                    <?php if (!$role->isReadOnly()): ?>
                                    <a href="javascript:void(0);" class="no-btn no-btn--big no-btn--delete-outline"
                                        onClick="doDelete(<?= htmlspecialchars($data['no']) ?>);">삭제</a>
                                    <?php endif; ?>
                                    <a href="./board.manage.list.php" class="no-btn no-btn--big no-btn--normal">목록</a>
                                    <?php if (!$role->isReadOnly()): ?>
                                    <a href="javascript:void(0);" class="no-btn no-btn--big no-btn--main"
                                        onClick="doEditSave();">수정</a>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <!-- card-body -->
                        </div>
                    </div>
                </section>
            </form>
        </main>

        <!-- Footer -->
        <script type="text/javascript" src="./js/board.manage.process.js?c=<?= $STATIC_ADMIN_JS_MODIFY_DATE ?>">
        </script>
        <?php include_once "../../inc/admin.footer.php"; ?>
    </div>
</body>

</html>