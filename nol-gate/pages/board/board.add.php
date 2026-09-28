<?php
include_once "../../../inc/lib/base.class.php";
$role->redirectIfReadOnly();

try {
    $db = DB::getInstance(); // PDO 인스턴스
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
            <?php
            try {
                $query = "SELECT no, title, skin, sort_no FROM nb_board_manage ORDER BY no ASC";
                $stmt = $db->query($query); // PDO 인스턴스 사용
                $arrBoardList = $stmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (Exception $e) {
                ClientFault::abortPage($e);
                exit;
            }
            ?>

            <form id="frm" name="frm" method="post" enctype="multipart/form-data">
                <input type="hidden" id="mode" name="mode" value="">

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
                                <h2 class="no-card-title">게시글 등록</h2>
                            </div>

                            <div class="no-card-body no-admin-column no-admin-column--detail">
                                <div class="no-admin-block">
                                    <h3 class="no-admin-title">
                                        <label for="board_no"> 게시판 선택 </label>
                                    </h3>
                                    <div class="no-admin-content">
                                        <select name="board_no" id="board_no">
                                            <option value="">게시판 선택</option>
                                            <?php foreach ($arrBoardList as $board): ?>
                                            <option value="<?= htmlspecialchars($board['no']) ?>"
                                                <?= isset($board_no) && $board_no == $board['no'] ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($board['title']) ?>
                                            </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <span class="no-admin-info">
                                            <i class="bx bxs-info-circle"></i> 글을 등록하시려는 게시판을 선택하세요
                                        </span>
                                    </div>
                                </div>

                                <div class="no-admin-block no_table_category" style="display:none;">
                                    <h3 class="no-admin-title">
                                        <label for="category_no">
                                            게시판 카테고리
                                        </label>
                                    </h3>
                                    <div class="no-admin-content">
                                        <select name="category_no" id="category_no">
                                            <option value="">
                                                카테고리 선택
                                            </option>
                                        </select>
                                        <span class="no-admin-info">
                                            <i class="bx bxs-info-circle"></i>
                                            원하는 카테고리를 선택하세요
                                        </span>
                                    </div>
                                </div>
                                <!-- admin-block -->

                                <div class="no-admin-block no_table_category_depth no-admin-field"
                                    style="display:none;">
                                    <h3 class="no-admin-title">
                                        <label for="category_no">
                                            게시판 카테고리 분류
                                        </label>
                                    </h3>
                                    <div class="no-admin-content">


                                        <div class="no-catg-wrapper">
                                            <select name="depth1" id="category_big" class="no-form-control"
                                                onChange="doChangeCategory(this.value, 'mid');">
                                                <option value="">
                                                    대분류 선택
                                                </option>
                                            </select>

                                            <select name="depth2" id="category_mid" class="no-form-control"
                                                onChange="doChangeCategory(this.value, 'sml');">
                                                <option value="">
                                                    중분류 선택
                                                </option>
                                            </select>

                                            <select name="depth3" id="category_sml" class="no-form-control"
                                                onChange="doChangeCategory(this.value, 'itm');">
                                                <option value="">
                                                    소분류 선택
                                                </option>
                                            </select>

                                            <select name="depth4" id="category_itm" class="no-form-control">
                                                <option value="">
                                                    상세분류 선택
                                                </option>
                                            </select>
                                        </div>



                                        <span class="no-admin-info">
                                            <i class="bx bxs-info-circle"></i>
                                            원하는 카테고리를 선택하세요
                                        </span>
                                    </div>
                                </div>
                                <!-- admin-block -->

                                <!-- Additional Fields -->
                                <div class="no-admin-block ">
                                    <h3 class="no-admin-title"><label for="title">제목</label></h3>
                                    <div class="no-admin-content">
                                        <input type="text" name="title" id="title" class="no-input--detail"
                                            placeholder="제목을 입력해주세요." />
                                    </div>
                                </div>

                                <div class="no-admin-block">
                                    <h3 class="no-admin-title"><label for="write_name">작성자</label></h3>
                                    <div class="no-admin-content">
                                        <input type="text" name="write_name" id="write_name"
                                            value="<?= htmlspecialchars($NO_ADM_NAME) ?>" placeholder="사이트관리자"
                                            class="no-input--detail" />
                                    </div>
                                </div>


                                <div class="no-admin-block">
                                    <h3 class="no-admin-title"><label for="direct_url">연관 링크</label></h3>
                                    <div class="no-admin-content">
                                        <input type="text" name="direct_url" id="direct_url"
                                            value="<?= isset($data['direct_url']) ? htmlspecialchars($data['direct_url']) : '' ?>"
                                            placeholder="링크 URL" class="no-input--detail" />

                                    </div>
                                </div>

                                <div class="no-admin-block no-admin-pos">
                                    <h3 class="no-admin-title"><span>공지</span></h3>
                                    <div class="no-admin-content">
                                        <label for="is_notice" class="no-items-center">
                                            <div class="no-checkbox-form">
                                                <input type="checkbox" name="is_notice" id="is_notice"
                                                    class="no-input--detail" value="Y" />
                                                <span><i class="bx bxs-check-square"></i></span>
                                            </div>
                                            <span class="no-admin-info no-mt">공지사항으로 등록하면 게시판 상단에 고정됩니다.</span>
                                        </label>
                                    </div>
                                </div>

                                <div class="no-admin-block">
                                    <h3 class="no-admin-title"><label for="title">썸네일 파일</label></h3>
                                    <div class="no-admin-content">
                                        <div class="no-file-control">
                                            <input type="text" class="no-fake-file" id="fakeThumbFileTxt"
                                                placeholder="파일을 선택해주세요." readonly disabled />
                                            <div class="no-file-box">
                                                <input type="file" name="thumb_image" id="thumb_image"
                                                    onchange="document.getElementById('fakeThumbFileTxt').value = this.value" />
                                                <button type="button" class="no-btn no-btn--main">파일찾기</button>
                                            </div>
                                        </div>
                                        <span class="no-admin-info"><i class="bx bxs-info-circle"></i> 갤러리 게시판은 썸네일 파일을
                                            필수 등록해야 합니다.</span>
                                    </div>
                                </div>

                                <div class="no-admin-block">
                                    <h3 class="no-admin-title"><label for="title">파일첨부</label></h3>
                                    <div class="no-admin-content">
                                        <div class="no-file-wrap">
                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <div class="no-file-control">
                                                <input type="text" class="no-fake-file" id="fakeFileTxt<?= $i ?>"
                                                    placeholder="파일을 선택해주세요." readonly disabled />
                                                <div class="no-file-box">
                                                    <input type="file" name="addFile<?= $i ?>"
                                                        accept=".jpg,.jpeg,.png,.gif,.webp,.zip,.xls,.xlsx,.ppt,.pptx,.doc,.docx,.pdf,.hwp,.mp4,.mov,.avi,.txt"
                                                        onchange="document.getElementById('fakeFileTxt<?= $i ?>').value = this.value" />
                                                    <button type="button" class="no-btn no-btn--main">파일찾기</button>
                                                </div>
                                            </div>
                                            <?php endfor; ?>
                                        </div>
                                        <span class="no-admin-info"><i class="bx bxs-info-circle"></i> zip, xls, xlsx,
                                            pdf, ppt, pptx, doc, docx, hwp 파일만 등록 가능합니다.</span>
                                    </div>
                                </div>

                                <div class="no-admin-block">
                                    <h3 class="no-admin-title"><label for="contents">내용</label></h3>
                                    <div class="no-admin-content">
                                        <textarea name="contents" id="contents"></textarea>
                                    </div>
                                </div>

                                <div class="no-items-center center">
                                    <a href="./board.list.php" class="no-btn no-btn--big no-btn--normal">목록</a>
                                    <a href="javascript:void(0);" class="no-btn no-btn--big no-btn--main"
                                        onClick="doRegSave();">저장</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </form>
        </main>

        <!-- Footer -->
        <script type="text/javascript"
            src="./js/board.process.js?c=<?= htmlspecialchars($STATIC_ADMIN_JS_MODIFY_DATE) ?>"></script>
        <script type="text/javascript"
            src="./js/board.category.depth.js?c=<?= htmlspecialchars($STATIC_ADMIN_JS_MODIFY_DATE) ?>"></script>
        <?php include_once "../../inc/admin.footer.php"; ?>

    </div>
</body>

</html>
