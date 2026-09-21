<?php include_once "../../../inc/lib/base.class.php"; ?>
<!DOCTYPE html>
<html lang="ko">
<?php

	$depthnum = 3;
	$pagenum = 1;


	$mainqry = " WHERE a.sitekey = :sitekey ";
	$params = ['sitekey' => $NO_SITE_UNIQUE_KEY];




	$page = $_POST['page'] ?? 1;
	$perpage = $_POST['perpage'] ?? 20;
	$listRowCnt = $perpage;
	$listCurPage = $page;
	$count = ($listCurPage - 1) * $listRowCnt;

	$db = DB::getInstance();
	
	try {
		// Count query
		$countQuery = "SELECT COUNT(*) AS cnt FROM nb_request_manage a $mainqry";
		$stmt = $db->prepare($countQuery);
		$stmt->execute($params);
		$totalCnt = $stmt->fetchColumn();

		// Calculate pagination
		$Page = ceil($totalCnt / $listRowCnt);

		// Main query for data retrieval
		$dataQuery = "SELECT a.* FROM nb_request_manage a $mainqry ORDER BY a.regdate DESC LIMIT $count, $listRowCnt";

		$stmt = $db->prepare($dataQuery);
		$stmt->execute($params);
		$result = $stmt->fetchAll(PDO::FETCH_ASSOC);

		// Board List Query
		$boardListQuery = "SELECT no, title, skin, sort_no FROM nb_board_manage WHERE sitekey = '$NO_SITE_UNIQUE_KEY' ORDER BY no ASC";
		$stmtBoard = $db->prepare($boardListQuery);
		$stmtBoard->execute();
		$arrBoardList = $stmtBoard->fetchAll(PDO::FETCH_ASSOC);

	} catch (PDOException $e) {
		echo "Error: " . blue_safe_error($e);
		exit;
	}

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
                                <h1 class="no-page-title">대관 일정 관리</h1>
                                <div class="no-breadcrumb-container">
                                    <ul class="no-breadcrumb-list">
                                        <li class="no-breadcrumb-item"><span>대관</span></li>
                                        <li class="no-breadcrumb-item"><span>대관 일정 관리</span></li>
                                    </ul>
                                </div>
                            </div>
                            <div class="no-items-center">
                                <a href="./request.schedule.add.php" class="no-btn no-btn--main no-btn--big">일정 생성</a>
                            </div>
                        </div>
                    </div>
        

                    <!-- Contents -->
                    <div class="no-content-container">
                        <div class="no-card">
                            <div class="no-card-header">
                                <h2 class="no-card-title">일정 관리</h2>
                            </div>
                            <div class="no-card-body">
                                <div class="no-table-responsive">
                                    <table class="no-table">
                                        <caption class="no-blind">
                                            번호, 게시판 이름, 공지, 제목, 작성자, 작성일, 조회수, 관리로 구성된 게시글 관리표
                                        </caption>
                                        <thead>
                                            <tr>
                                                <th scope="col">노출</th>
                                                <th scope="col">제목</th>
                                                <th scope="col">개재일</th>
                                                <th scope="col">관리</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                                foreach ($result as $v) {
                                                    $app_view = ($v['r_view'] == "N") ? "숨김" : "노출";
                                                    $rnumber--;
                                            ?>
                                            <tr>
                                                <td><span class="no-btn no-btn--notice"><?= htmlspecialchars($app_view) ?></span></td>

												  <td class="no-td-title">
														<a href="./request.shedule.view.php?no=<?= htmlspecialchars($v['no']) ?>"><?= htmlspecialchars($v['r_title']) ?></a>
													</td>
												 <td>
                                                    <?=htmlspecialchars($v['r_sdate']) . " ~ " . htmlspecialchars($v['r_edate']) ?>
                                                </td>
                                                <td>
                                                    <div class="no-table-role">
                                                        <span class="no-role-btn"><i class="bx bx-dots-vertical-rounded"></i></span>
                                                        <div class="no-table-action">
                                                            <a href="./request.shedule.view.php?no=<?= htmlspecialchars($v['no']) ?>" class="no-btn no-btn--sm no-btn--normal">수정</a>
                                                            <a href="javascript:void(0);" class="no-btn no-btn--sm no-btn--delete-outline" onClick="doManageDelete(<?= htmlspecialchars($v['no']) ?>);">삭제</a>
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
                </section>
            </form>
        </main>

        <!-- Footer -->
        <script type="text/javascript" src="./js/request.process.js?c=<?= htmlspecialchars($STATIC_ADMIN_JS_MODIFY_DATE) ?>"></script>
        <?php include_once "../../inc/admin.footer.php"; ?>
    </div>
</body>
</html>
