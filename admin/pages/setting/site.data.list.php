<?php include_once "../../../inc/lib/base.class.php"; ?>
<!DOCTYPE html>
<html lang="ko">
<?php

	$pdo = DB::getInstance();
	$depthnum = 4;
	$pagenum = 3;

	$target = $_REQUEST['target'] ?? '';
	$mainqry = " WHERE a.sitekey = :sitekey";

	$params = ['sitekey' => $NO_SITE_UNIQUE_KEY];
	if ($target) {
		$mainqry .= " AND target = :target";
		$params['target'] = trim($target);
	}

	$page = $_POST['page'] ?? 1; // Current page
	$perpage = $_POST['perpage'] ?? 20; // Rows per page
	$listRowCnt = $perpage;
	$listCurPage = $page;
	$count = ($listCurPage - 1) * $listRowCnt;

	// Query for total record count
	$countQuery = "SELECT COUNT(*) as cnt FROM nb_data a $mainqry";
	$countStmt = $pdo->prepare($countQuery);
	$countStmt->execute($params);
	$totalCnt = $countStmt->fetchColumn();

	// Calculate total pages
	$Page = ceil($totalCnt / $listRowCnt);

	// Query to retrieve data
	$dataQuery = "SELECT a.no, a.sitekey, a.target, a.contents, a.regdate
				  FROM nb_data a $mainqry
				  ORDER BY a.no DESC
				  LIMIT :count, :listRowCnt";
	$dataStmt = $pdo->prepare($dataQuery);
	foreach ($params as $key => $value) {
		$dataStmt->bindValue(":$key", $value);
	}
	$dataStmt->bindValue(':count', $count, PDO::PARAM_INT);
	$dataStmt->bindValue(':listRowCnt', $listRowCnt, PDO::PARAM_INT);
	$dataStmt->execute();

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
            <form method="POST" name="frm" id="frm">
                <section class="no-content">
                    <!-- Page Title -->
                    <div class="no-toolbar">
                        <div class="no-toolbar-container no-flex-stack">
                            <div class="no-page-indicator">
                                <h1 class="no-page-title">사이트 데이터 관리</h1>
                                <div class="no-breadcrumb-container">
                                    <ul class="no-breadcrumb-list">
                                        <li class="no-breadcrumb-item"><span>설정</span></li>
                                        <li class="no-breadcrumb-item"><span>사이트 데이터 관리</span></li>
                                    </ul>
                                </div>
                            </div>
                            <div class="no-items-center">
                                <a href="./site.data.add.php" class="no-btn no-btn--main no-btn--big">데이터 등록</a>
                            </div>
                        </div>
                    </div>

                    <?php
                        $boardQuery = "SELECT no, title, skin, sort_no FROM nb_board_manage WHERE sitekey = :sitekey ORDER BY no ASC";
                        $boardStmt = $pdo->prepare($boardQuery);
                        $boardStmt->execute(['sitekey' => $NO_SITE_UNIQUE_KEY]);
                        $arrBoardList = $boardStmt->fetchAll(PDO::FETCH_ASSOC);
                    ?>
                    <!-- Search -->
                    <div class="no-search no-toolbar-container">
                        <div class="no-card">
                            <div class="no-card-header">
                                <h2 class="no-card-title">게시글 검색</h2>
                            </div>
                            <div class="no-card-body no-admin-column">
                                <div class="no-admin-block wide">
                                    <h3 class="no-admin-title">검색어</h3>
                                    <div class="no-search-select">
                                        <div class="no-search-wrap">
                                            <select name="target" id="target">
                                                <option value="">전체</option>
                                                <?php foreach ($siteDataTarget as $key => $val) : ?>
                                                    <option value="<?= $key ?>" <?= ($target == $key) ? "selected" : "" ?>><?= $val ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                            <div class="no-search-btn">
                                                <button type="button" title="검색" class="no-btn no-btn--main no-btn--search" onClick="doSearchList();">검색</button>
                                            </div>
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
                                <h2 class="no-card-title">게시글 관리</h2>
                            </div>

                            <div class="no-card-body">
                                <div class="no-table-option flex-end">
                                    <div class="no-perpage">
                                        <select name="perpage" id="perpage" onChange="document.frm.submit();">
                                            <option value="20" <?= ($perpage == "20") ? "selected" : "" ?>>20개씩</option>
                                            <option value="50" <?= ($perpage == "50") ? "selected" : "" ?>>50개씩</option>
                                            <option value="100" <?= ($perpage == "100") ? "selected" : "" ?>>100개씩</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="no-table-responsive">
                                    <table class="no-table">
                                        <caption class="no-blind">
                                            번호, 게시판 이름, 공지, 제목, 작성자, 작성일, 조회수, 관리로 구성된 게시글 관리표
                                        </caption>
                                        
                                        <thead>
                                            <tr>
                                                <th scope="col" class="no-width-120 no-min-width-60">번호</th>
                                                <th scope="col" class="no-min-width-150">사용영역</th>
                                                <th scope="col" class="no-min-width-120">등록일</th>
                                                <th scope="col" class="no-min-width-role no-td-center">관리</th>
                                            </tr>
                                        </thead>
                                        
                                        <tbody>
                                            <?php while ($v = $dataStmt->fetch(PDO::FETCH_ASSOC)) : ?>
                                                <tr>
                                                    <td><span><?= $rnumber ?></span></td>
                                                    <td>
                                                        <a href="./site.data.view.php?no=<?= $v['no'] ?>">
                                                            <?= $siteDataTarget[$v['target']] ?? '' ?>
                                                        </a>
                                                    </td>
                                                    <td><span><?= $v['regdate'] ?></span></td>
                                                    <td>
                                                        <div class="no-table-role">
                                                            <span class="no-role-btn"><i class="bx bx-dots-vertical-rounded"></i></span>
                                                            <div class="no-table-action">
                                                                <a href="javascript:doDeleteData(<?= $v['no'] ?>);" class="no-btn no-btn--sm no-btn--delete-outline">삭제</a>
                                                                <a href="./site.data.view.php?no=<?= $v['no'] ?>" class="no-btn no-btn--sm no-btn--normal" onClick="doRegSave();">수정</a>
                                                            </div>
                                                        </div>
                                                    </td>
                                                </tr>
                                                <?php $rnumber--; ?>
                                            <?php endwhile; ?>
                                        </tbody>
                                    </table>

                                    <?php if ($dataStmt->rowCount() == 0) : ?>
                                        <p>등록된 내용이 없습니다.</p>
                                    <?php endif; ?>
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
        <script type="text/javascript" src="./js/setting.process.js?c=<?= $STATIC_ADMIN_JS_MODIFY_DATE ?>"></script>
        <?php include_once "../../inc/admin.footer.php"; ?>
    </div>
</body>
</html>
