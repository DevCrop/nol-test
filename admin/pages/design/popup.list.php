<?php include_once "../../../inc/lib/base.class.php"; ?>
<!DOCTYPE html>
<html lang="ko">
<?php

	$db = DB::getInstance(); // Get PDO instance

    $depthnum = 2;
    $pagenum = 2;

    $p_title = $_REQUEST['p_title'] ?? '';
    $p_view = $_REQUEST['p_view'] ?? '';
    $page = $_POST['page'] ?? 1;
    $perpage = $_POST['perpage'] ?? 20;

    $mainqry = "WHERE a.sitekey = :sitekey";
    $params = ['sitekey' => $NO_SITE_UNIQUE_KEY];

    if ($p_title) {
        $mainqry .= " AND REPLACE(a.p_title, ' ', '') LIKE :p_title";
        $params['p_title'] = '%' . trim($p_title) . '%';
    }

    if ($p_view) {
        $mainqry .= " AND p_view = :p_view";
        $params['p_view'] = trim($p_view);
    }

    /* Rows per page and current page calculation */
    $listRowCnt = $perpage;
    $listCurPage = $page;
    $count = ($listCurPage - 1) * $listRowCnt;

    /* Get total count */
    $query = "SELECT COUNT(*) as cnt FROM nb_popup a $mainqry";
    $stmt = $db->prepare($query);
    $stmt->execute($params);
    $data = $stmt->fetch(PDO::FETCH_ASSOC);
    $totalCnt = $data['cnt'] ?? 0;

    /* Total pages calculation */
    $Page = ceil($totalCnt / $listRowCnt);

    /* Fetch results with pagination */
    $query = "SELECT a.no, a.p_title, a.p_img, a.p_target, a.p_link, a.p_view, a.p_left, a.p_top, a.p_idx, 
                     a.p_sdate, a.p_edate, a.p_rdate, a.p_none_limit, a.p_loc
              FROM nb_popup a 
              $mainqry 
              ORDER BY a.no DESC
              LIMIT :count, :listRowCnt";

    $stmt = $db->prepare($query);
    $stmt->bindValue(':count', $count, PDO::PARAM_INT);
    $stmt->bindValue(':listRowCnt', $listRowCnt, PDO::PARAM_INT);

    foreach ($params as $key => $value) {
        $stmt->bindValue(":$key", $value);
    }

    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    /* Row number calculation */
    $rnumber = $totalCnt - ($listCurPage - 1) * $listRowCnt;


	include_once "../../inc/admin.title.php";
	include_once "../../inc/admin.css.php";
	include_once "../../inc/admin.js.php";
?>
</head>
<body>
<div class="no-wrap">
        <!-- Header -->
		<?php
		include_once "../../inc/admin.header.php";
		?>

        <!-- Main -->
        <main class="no-app no-container">
            <!-- Drawer -->
            <?php
                include_once "../../inc/admin.drawer.php";
            ?>

            <!-- Contents -->
            <form method="POST" name="frm" id="frm">
            <section class="no-content">
                    <!-- Page Title -->
                    <div class="no-toolbar">
                        <div class="no-toolbar-container no-flex-stack">
                            <div class="no-page-indicator">
                                <h1 class="no-page-title">배너 관리</h1>
                                <div class="no-breadcrumb-container">
                                    <ul class="no-breadcrumb-list">
                                        <li class="no-breadcrumb-item">
                                            <span>디자인 관리</span>
                                        </li>
                                        <li class="no-breadcrumb-item">
                                            <span>배너 목록</span>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <!-- page indicator -->

                            <div class="no-items-center">
                                <a
                                    href="./popup.add.php"
                                    class="no-btn no-btn--main no-btn--big"
                                >
                                    팝업등록
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Search -->
                    <div class="no-search no-toolbar-container">
                        <div class="no-card">
                            <div class="no-card-header">
                                <h2 class="no-card-title">팝업 검색</h2>
                            </div>
                            <div class="no-card-body no-admin-column">
                                <div class="no-admin-block">
                                    <h3 class="no-admin-title">노출 여부</h3>
                                    <div class="no-admin-content">
                                        <div class="no-radio-form no-list">
                                            <label for="input1">
                                                <div class="no-radio-box">
                                                    <input
                                                        type="radio"
                                                        name="p_view"
                                                        id="input1"
                                                        value=""
                                                        <?php if($p_view == "") echo "checked";?>
                                                    />
                                                    <span>
                                                        <i
                                                            class="bx bx-radio-circle-marked"
                                                        ></i>
                                                    </span>
                                                </div>
                                                <span class="no-radio-text"
                                                    >전체</span
                                                >
                                            </label>

                                            <label for="input2">
                                                <div class="no-radio-box">
                                                    <input
                                                        type="radio"
                                                        name="p_view"
                                                        id="input2"
                                                        value="Y"
                                                        <?php if($p_view == "Y") echo "checked";?>
                                                    />
                                                    <span>
                                                        <i
                                                            class="bx bx-radio-circle-marked"
                                                        ></i>
                                                    </span>
                                                </div>
                                                <span class="no-radio-text"
                                                    >노출</span
                                                >
                                            </label>

                                            <label for="input3">
                                                <div class="no-radio-box">
                                                    <input
                                                        type="radio"
                                                        name="p_view"
                                                        id="input3"
                                                        value="N"
                                                        <?php if($p_view == "N") echo "checked";?>
                                                    />
                                                    <span>
                                                        <i
                                                            class="bx bx-radio-circle-marked"
                                                        ></i>
                                                    </span>
                                                </div>
                                                <span class="no-radio-text"
                                                    >숨김</span
                                                >
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div class="no-admin-block">
                                    <h3 class="no-admin-title">검색어</h3>
                                    <div class="no-search-wrap">
                                        <div class="no-search-input">
                                            <i class="bx bx-search-alt-2"></i>
                                            <input
                                                type="text"
                                                name="p_title"
                                                id="p_title"
                                                title="검색어 입력"
                                                placeholder="검색어를 입력해주세요."
                                            />
                                        </div>
                                        <div class="no-search-btn">
                                            <button
                                                type="button"
                                                title="검색"
                                                class="no-btn no-btn--main no-btn--search"
                                                onClick="doSearchList();"
                                            >
                                                검색
                                            </button>
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
                                            번호, 게시판 이름, 공지, 제목,
                                            작성자, 작성일, 조회수, 관리로
                                            구성된 게시글 관리표
                                        </caption>
                                        <thead>
                                            <tr>
                                                <th
                                                    scope="col"
                                                    class="no-width-120 no-min-width-60"
                                                >
                                                    번호
                                                </th>
                                                <th
                                                    scope="col"
                                                    class="no-width-100 no-min-width-70"
                                                >
                                                    노출
                                                </th>
                                                <th
                                                    scope="col"
                                                    class="no-width-120 no-min-width-60"
                                                >
                                                    순위
                                                </th>
                                                <th
                                                    scope="col"
                                                    class="no-min-width-120"
                                                >
                                                    팝업
                                                </th>

                                                <th
                                                    scope="col"
                                                    class="no-min-width-150"
                                                >
                                                    제목
                                                </th>
                                                <th
                                                    scope="col"
                                                    class="no-min-width-150"
                                                >
                                                    개재일
                                                </th>
                                                <th
                                                    scope="col"
                                                    class="no-min-width-150"
                                                >
                                                    링크
                                                </th>

                                                <th
                                                    scope="col"
                                                    class="no-min-width-100"
                                                >
                                                    링크형태
                                                </th>
                                                <th
                                                    scope="col"
                                                    class="no-min-width-role no-td-center"
                                                >
                                                    관리
                                                </th>
                                            </tr>
                                            <!-- col 9 -->
                                        </thead>
                                        <tbody>
										<?php
											foreach ($results as $v) {
												$app_view = ($v['p_view'] === "N") ? "노출안함" : "노출";
												$app_src = $UPLOAD_WDIR_POPUP . "/" . $v['p_img'];
												$app_popup = "";

												if ($v['p_img']) {
													$app_popup = "<img src='$app_src' width=100 alt='Popup Image'>";
												}
												?>
												<tr>
													<td>
														<span> <?= $rnumber ?> </span>
													</td>
													<td>
														<span class="no-btn no-btn--notice">
															<?= $app_view ?>
														</span>
													</td>
													<td>
														<span> <?= $v['p_idx'] ?> </span>
													</td>
													<td class="no-td-image">
														<div class="no-td-image-box">
															<img src="<?= $app_src ?>" alt="<?= htmlspecialchars($v['p_title'], ENT_QUOTES, 'UTF-8') ?>" />
														</div>
													</td>
													<td class="no-td-title">
														<a href="./popup.view.php?no=<?= $v['no'] ?>">
															<?= htmlspecialchars($v['p_title'], ENT_QUOTES, 'UTF-8') ?>
														</a>
													</td>
													<td>
														<span>
															<?= ($v['p_none_limit'] === 'Y') ? '무기한' : $v['p_sdate'] . " ~ " . $v['p_edate'] ?>
														</span>
													</td>
													<td>
														<a href="<?= htmlspecialchars($v['p_link'], ENT_QUOTES, 'UTF-8') ?>" target="_blank"><?= htmlspecialchars($v['p_link'], ENT_QUOTES, 'UTF-8') ?></a>
													</td>
													<td>
														<?= htmlspecialchars($v['p_target'], ENT_QUOTES, 'UTF-8') ?>
													</td>
													<td>
														<div class="no-table-role">
															<span class="no-role-btn">
																<i class="bx bx-dots-vertical-rounded"></i>
															</span>
															<div class="no-table-action">
																<a href="./popup.view.php?no=<?= $v['no'] ?>" class="no-btn no-btn--sm no-btn--normal">수정</a>
																<a href="javascript:void(0);" class="no-btn no-btn--sm no-btn--delete-outline" onClick="doDelete(<?= $v['no'] ?>);">삭제</a>
															</div>
														</div>
													</td>
												</tr>
												<?php
												$rnumber--;
											}
										?>
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
        <script type="text/javascript" src="./js/popup.process.js?c=<?=$STATIC_ADMIN_JS_MODIFY_DATE?>"></script>
        <?php
            include_once "../../inc/admin.footer.php";
        ?>
    </div>
</body>
</html> 
