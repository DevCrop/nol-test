<?php include_once "../../../inc/lib/base.class.php"; ?>
<!DOCTYPE html>
<html lang="ko">
<?php

    $depthnum = 2;
    $pagenum = 1;

    // Initialize variables
    $search_word = $_REQUEST['search_word'] ?? '';
    $_loc = $_REQUEST['_loc'] ?? '';
    $_view = $_REQUEST['_view'] ?? '';
    $page = $_POST['page'] ?? 1;
    $perpage = $_POST['perpage'] ?? 20;

    // Setup PDO
    $pdo = DB::getInstance();

    // Build main query
    $mainqry = " WHERE a.sitekey = :sitekey";
    $params = [':sitekey' => $NO_SITE_UNIQUE_KEY];

    if ($search_word) {
        $mainqry .= " AND (REPLACE(a.b_title, ' ', '') LIKE :search_word)";
        $params[':search_word'] = '%' . trim($search_word) . '%';
    }

    if ($_loc) {
        $mainqry .= " AND a.b_loc = :loc";
        $params[':loc'] = trim($_loc);
    }

    if ($_view) {
        $mainqry .= " AND a.b_view = :view";
        $params[':view'] = trim($_view);
    }

    // Pagination setup
    $listRowCnt = $perpage;
    $listCurPage = $page ?: 1;
    $count = ($listCurPage - 1) * $listRowCnt;

    // Get total count
    $query = "SELECT COUNT(*) AS cnt FROM nb_banner a $mainqry";
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $totalCnt = $stmt->fetchColumn() ?? 0;

    $Page = ceil($totalCnt / $listRowCnt);

    // Get banner data
    $query = "SELECT a.*
				  FROM nb_banner a
				  $mainqry
				  ORDER BY a.no DESC
				  LIMIT $count, $listRowCnt";
	$stmt = $pdo->prepare($query);

	// Bind parameters
	foreach ($params as $key => &$val) {
		$stmt->bindParam($key, $val);
	}
	// Bind count and listRowCnt as integers for LIMIT
	$stmt->execute();

    $banners = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $rnumber = $totalCnt - ($listCurPage - 1) * $listRowCnt;

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
            <form method="POST" name="frm" id="frm" autocomplete="off">
                <section class="no-content">
                    <!-- Page Title -->
                    <div class="no-toolbar">
                        <div class="no-toolbar-container no-flex-stack">
                            <div class="no-page-indicator">
                                <h1 class="no-page-title">배너 관리</h1>
                                <div class="no-breadcrumb-container">
                                    <ul class="no-breadcrumb-list">
                                        <li class="no-breadcrumb-item"><span>디자인 관리</span></li>
                                        <li class="no-breadcrumb-item"><span>배너 목록</span></li>
                                    </ul>
                                </div>
                            </div>
                            <div class="no-items-center">
                                <a href="./banner.add.php" class="no-btn no-btn--main no-btn--big">배너등록</a>
                            </div>
                        </div>
                    </div>

                    <!-- Search -->
                    <div class="no-search no-toolbar-container">
                        <div class="no-card">
                            <div class="no-card-header">
                                <h2 class="no-card-title">배너 검색</h2>
                            </div>
                            <div class="no-card-body no-admin-column">
                                <div class="no-admin-block">
                                    <h3 class="no-admin-title">배너 구분</h3>
                                    <div class="no-admin-content">
                                        <select name="_loc" id="_loc">
                                            <option value="">선택</option>
                                            <?php
                                                foreach ($arr_banner_loc as $key => $val) {
                                                    $selected = ($_loc == $key) ? 'selected' : '';
                                                    echo "<option value='" . htmlspecialchars($key) . "' $selected>" . htmlspecialchars($val) . "</option>";
                                                }
                                            ?>
                                        </select>
                                    </div>
                                </div>

                                <div class="no-admin-block">
                                    <h3 class="no-admin-title">노출 여부</h3>
                                    <div class="no-admin-content">
                                        <div class="no-radio-form no-list">
                                            <label for="input1">
                                                <div class="no-radio-box">
                                                    <input type="radio" name="b_view" id="input1" value="" <?= empty($_view) ? 'checked' : ''; ?> />
                                                    <span><i class="bx bx-radio-circle-marked"></i></span>
                                                </div>
                                                <span class="no-radio-text">전체</span>
                                            </label>
                                            <label for="input2">
                                                <div class="no-radio-box">
                                                    <input type="radio" name="b_view" id="input2" value="Y" <?= ($_view == "Y") ? 'checked' : ''; ?> />
                                                    <span><i class="bx bx-radio-circle-marked"></i></span>
                                                </div>
                                                <span class="no-radio-text">노출</span>
                                            </label>
                                            <label for="input3">
                                                <div class="no-radio-box">
                                                    <input type="radio" name="b_view" id="input3" value="N" <?= ($_view == "N") ? 'checked' : ''; ?> />
                                                    <span><i class="bx bx-radio-circle-marked"></i></span>
                                                </div>
                                                <span class="no-radio-text">숨김</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <div class="no-admin-block">
                                    <h3 class="no-admin-title">검색어</h3>
                                    <div class="no-search-wrap">
                                        <div class="no-search-input">
                                            <i class="bx bx-search-alt-2"></i>
                                            <input type="text" name="search_word" id="name" title="검색어 입력" placeholder="검색어를 입력해주세요." value="<?= htmlspecialchars($search_word) ?>" />
                                        </div>
                                        <div class="no-search-btn">
                                            <button type="button" title="검색" class="no-btn no-btn--main no-btn--search" onClick="doSearchList();">검색</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Contents -->
                    <div class="no-content-container">
                        <div class="no-card">
                            <div class="no-card-header">
                                <h2 class="no-card-title">배너 관리</h2>
                            </div>
                            <div class="no-card-body">
                                <div class="no-table-responsive">
                                    <table class="no-table">
                                        <caption class="no-blind">
                                            번호, 게시판 이름, 공지, 제목, 작성자, 작성일, 조회수, 관리로 구성된 게시글 관리표
                                        </caption>
                                        <thead>
                                            <tr>
                                                <th scope="col">번호</th>
                                                <th scope="col">노출</th>
                                                <th scope="col">순위</th>
                                                <th scope="col">팝업</th>
                                                <th scope="col">제목</th>
                                                <th scope="col">공연날짜</th>
                                                <th scope="col">링크</th>
                                                <th scope="col">링크형태</th>
                                                <th scope="col">관리</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                                foreach ($banners as $v) {
                                                    $app_view = ($v['b_view'] == "N") ? "숨김" : "노출";
                                                    $rnumber--;
                                            ?>
                                            <tr>
                                                <td><?= htmlspecialchars($rnumber) ?></td>
                                                <td><span class="no-btn no-btn--notice"><?= htmlspecialchars($app_view) ?></span></td>
                                                <td><span><?= htmlspecialchars($v['b_idx']) ?></span></td>
                                                <td class="no-td-image">
                                                    <div class="no-td-image-box">
                                                        <img src="../../../uploads/banner/<?= htmlspecialchars($v['b_img']) ?>" alt="<?= htmlspecialchars($v['b_title']) ?>" />
                                                    </div>
                                                </td>
                                                <td class="no-td-title">
                                                    <a href="./banner.view.php?no=<?= htmlspecialchars($v['no']) ?>"><?= htmlspecialchars($v['b_title']) ?></a>
                                                </td>
                                                <td>
                                                    <?= ($v['b_none_limit'] == 'Y' ? '무기한' : htmlspecialchars($v['b_sdate']) . " ~ " . htmlspecialchars($v['b_edate'])) ?>
                                                </td>
                                                <td>
                                                    <?php if ($v['b_target'] != '_none' && isset($v['b_link'])): ?>
                                                        <a href="<?= htmlspecialchars($v['b_link']) ?>" target="_blank"><?= htmlspecialchars($v['b_link']) ?></a>
                                                    <?php else: ?>
                                                        링크없음
                                                    <?php endif; ?>
                                                </td>
                                                <td><?= htmlspecialchars(($v['b_target'] != '_none' && isset($v['b_link'])) ? $_targetArr[$v['b_target']] : '링크없음') ?></td>
                                                <td>
                                                    <div class="no-table-role">
                                                        <span class="no-role-btn"><i class="bx bx-dots-vertical-rounded"></i></span>
                                                        <div class="no-table-action">
                                                            <a href="./banner.view.php?no=<?= htmlspecialchars($v['no']) ?>" class="no-btn no-btn--sm no-btn--normal">수정</a>
                                                            <a href="javascript:void(0);" class="no-btn no-btn--sm no-btn--delete-outline" onClick="doDelete(<?= htmlspecialchars($v['no']) ?>);">삭제</a>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                            <?php } ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Pagination -->
                    <?php include_once "../../lib/admin.pagination.php"; ?>
                </section>
            </form>
        </main>

        <!-- Footer -->
        <script type="text/javascript" src="./js/banner.process.js?c=<?= htmlspecialchars($STATIC_ADMIN_JS_MODIFY_DATE) ?>"></script>
        <?php include_once "../../inc/admin.footer.php"; ?>
    </div>
</body>
</html>
