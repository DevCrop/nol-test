<?php include_once "../../../inc/lib/base.class.php"; ?>
<!DOCTYPE html>
<html lang="ko">
<?php

$depthnum = 1;
$pagenum = 2;

$searchKeyword = $_REQUEST['searchKeyword'] ?? '';
$searchColumn = $_REQUEST['searchColumn'] ?? '';
$searchColumns = ['a.title', 'a.contents'];
$searchColumn = in_array($searchColumn, $searchColumns, true) ? $searchColumn : '';
$sdate = $_REQUEST['sdate'] ?? '';
$edate = $_REQUEST['edate'] ?? '';
$board_no = $_REQUEST['board_no'] ?? null;

$depth1 = $_REQUEST['depth1'] ?? null;
$depth2 = $_REQUEST['depth2'] ?? null;
$depth3 = $_REQUEST['depth3'] ?? null;
$depth4 = $_REQUEST['depth4'] ?? null;

// 데이터베이스 연결
$connect = DB::getInstance();
$mainqry = " WHERE a.sitekey = :sitekey ";
$params = [':sitekey' => $NO_SITE_UNIQUE_KEY];

try {
    // 검색 조건 추가
    if ($board_no) {
        $mainqry .= " AND a.board_no = :board_no ";
        $params[':board_no'] = intval($board_no);
    }
    
    foreach (['depth1', 'depth2', 'depth3', 'depth4'] as $depth) {
        if ($$depth) {
            $depthArr = explode("^", $$depth);
            $mainqry .= " AND a.{$depth} = :{$depth} ";
            $params[":{$depth}"] = intval($depthArr[0]);
        }
    }

    if ($searchColumn && $searchKeyword) {
        $mainqry .= " AND ( REPLACE($searchColumn, ' ', '') LIKE :searchKeyword ) ";
        $params[':searchKeyword'] = '%' . trim($searchKeyword) . '%';
    }

    if ($sdate && $edate) {
        $mainqry .= " AND (DATE_FORMAT(a.regdate, '%Y-%m-%d') BETWEEN :sdate AND :edate)";
        $params[':sdate'] = $sdate;
        $params[':edate'] = $edate;
    }

    $page = $_REQUEST['page'] ?? 1;
    $perpage = (int) ($_REQUEST['perpage'] ?? 20);
    if (!in_array($perpage, [20, 50, 100], true)) $perpage = 20;

    $listRowCnt = (int)$perpage;
    $listCurPage = (int)$page;
    $count = ($listCurPage - 1) * $listRowCnt;

    // 전체 게시물 수 쿼리
    $query = "SELECT COUNT(*) AS cnt FROM nb_board a $mainqry";
    $stmt = $connect->prepare($query);
    $stmt->execute($params);
    $data = $stmt->fetch(PDO::FETCH_ASSOC);
    $totalCnt = $data['cnt'] ?? 0;
    $Page = ceil($totalCnt / $listRowCnt);

    // 데이터 쿼리
    $query = "
        SELECT a.*,
            b.title AS board_name
        FROM nb_board a
        LEFT JOIN nb_board_manage b ON a.board_no = b.no
        $mainqry
        ORDER BY a.is_notice = 'Y' DESC, a.no DESC
        LIMIT :count, :listRowCnt
    ";
    $stmt = $connect->prepare($query);

    // 바인딩 추가
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->bindValue(':count', (int)$count, PDO::PARAM_INT);
    $stmt->bindValue(':listRowCnt', (int)$listRowCnt, PDO::PARAM_INT);
    $stmt->execute();

    $arrResultSet = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $rnumber = $totalCnt - ($listCurPage - 1) * $listRowCnt;
    $searchParam = http_build_query([
        'board_no' => $board_no,
        'page' => $page,
        'sdate' => $sdate,
        'edate' => $edate
    ]);

} catch (Exception $e) {
    error_log('[board.list] query failed');
    echo "요청을 처리할 수 없습니다.";
    exit;
}

include_once "../../inc/admin.title.php";
include_once "../../inc/admin.css.php";
include_once "../../inc/admin.js.php";
?>
<style>
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
            <form method="POST" name="frm" id="frm" autocomplete="off">
                <input type="hidden" name="mode" id="mode" value="list">
                <input type="hidden" id="depth1" value="<?= htmlspecialchars($depth1) ?>">
                <input type="hidden" id="depth2" value="<?= htmlspecialchars($depth2) ?>">
                <input type="hidden" id="depth3" value="<?= htmlspecialchars($depth3) ?>">
                <input type="hidden" id="depth4" value="<?= htmlspecialchars($depth4) ?>">

                <section class="no-content">
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
                            <div class="no-items-center">
                                <a href="./board.add.php" class="no-btn no-btn--main no-btn--big"> 글등록 </a>
                            </div>
                        </div>
                    </div>


					<?php
						// Establishing a PDO instance
						$db = DB::getInstance(); // Assuming DB::getInstance() returns a PDO instance
						
						// Fetching the board list from the database
						$query = "SELECT no, title, skin, sort_no FROM nb_board_manage WHERE sitekey = :sitekey ORDER BY no ASC";
						$stmt = $db->prepare($query);
						$stmt->execute(['sitekey' => $NO_SITE_UNIQUE_KEY]);
						$arrBoardList = $stmt->fetchAll(PDO::FETCH_ASSOC);
					?>

					<!-- Search -->
					<div class="no-search no-toolbar-container">
						<div class="no-card">
							<div class="no-card-header">
								<h2 class="no-card-title">게시글 검색</h2>
							</div>
							<div class="no-card-body no-admin-column">
								<div class="no-admin-block">
									<h3 class="no-admin-title">게시판 선택</h3>
									<div class="no-admin-content">
										<select name="board_no" id="board_no">
											<option value="">게시판 선택</option>
											<?php foreach ($arrBoardList as $v): ?>
												<option value="<?php echo htmlspecialchars($v['no']); ?>" 
													<?php if (isset($board_no) && $board_no == $v['no']) echo "selected"; ?>>
													<?php echo htmlspecialchars($v['title']); ?>
												</option>
											<?php endforeach; ?>
										</select>
									</div>
								</div>
								<div class="no-admin-block">
									<h3 class="no-admin-title">검색 일자</h3>
									<div class="no-admin-content no-admin-date">
										<input type="text" name="sdate" id="sdate" value="<?php echo isset($sdate) ? htmlspecialchars($sdate) : ''; ?>" />
										<span></span>
										<input type="text" name="edate" id="edate" value="<?php echo isset($edate) ? htmlspecialchars($edate) : ''; ?>" />
									</div>
								</div>
								<!-- admin block -->
									<!-- <div class="no-admin-block wide">
										<h3 class="no-admin-title">카테고리 분류</h3>    
										<div class="no-search-select no-admin-catg">
											<select name="depth1" id="category_big" data-catg-type="big" class="no-form-control" onChange="doChangeCategory(this.value, 'mid');">
												<option value="">대분류 선택</option>
											</select>
											<select name="depth2" id="category_mid" data-catg-type="mid" class="no-form-control" onChange="doChangeCategory(this.value, 'sml');">
												<option value="">중분류 선택</option>
											</select>
											<select name="depth3" data-catg-type="sml" class="no-form-control" id="category_sml" onChange="doChangeCategory(this.value, 'itm');">
												<option value="">소분류 선택</option>
											</select>
											<select name="depth4" data-catg-type="itm" id="category_itm" class="no-form-control">
												<option value="">상세분류 선택</option>
											</select>
										</div>
									</div> -->

							<div class="no-admin-block wide">
								<h3 class="no-admin-title">검색어</h3>
								<div class="no-search-select">
									<select name="searchColumn" id="searchColumn">
										<option value="">선택</option>
										<option value="a.title" <?php if (isset($searchColumn) && $searchColumn == "a.title") echo "selected"; ?>>게시물 제목</option>
										<option value="a.contents" <?php if (isset($searchColumn) && $searchColumn == "a.contents") echo "selected"; ?>>게시물 내용</option>
									</select>
									<div class="no-search-wrap no-ml">
										<div class="no-search-input">
											<i class="bx bx-search-alt-2"></i>
											<input name="searchKeyword" id="searchKeyword" type="text" title="검색어 입력" placeholder="검색어를 입력해주세요." 
												   value="<?php echo isset($searchKeyword) ? htmlspecialchars($searchKeyword) : ''; ?>" />
										</div>
										<div class="no-search-btn">
											<button type="button" title="검색" class="no-btn no-btn--main no-btn--search" onClick="doSearchList();">
												검색
											</button>
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
								<div class="no-table-option">
                                    <ul class="no-table-check-control">
                                        <li>
                                            <a href="javascript:void(0);" class="no-btn no-btn--sm no-btn--check active" onClick="doCheckUnCheck('no-chk', 'check');">전체선택</a>
                                        </li>
                                        <li>
                                            <a href="javascript:void(0);" class="no-btn no-btn--sm no-btn--check" onClick="doCheckUnCheck('no-chk', 'uncheck');">선택해제</a>
                                        </li>
                                        <li>
                                            <a href="javascript:void(0);" class="no-btn no-btn--sm no-btn--check" onClick="doDeleteArray();">선택삭제</a>
                                        </li>
                                    </ul>

                                    <div class="no-perpage">
                                        <select name="perpage" id="perpage">
                                            <option value="20" <?php if ($perpage == "20") echo "selected"; ?>>20개씩</option>
                                            <option value="50" <?php if ($perpage == "50") echo "selected"; ?>>50개씩</option>
                                            <option value="100" <?php if ($perpage == "100") echo "selected"; ?>>100개씩</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="no-table-responsive">
                                    <table class="no-table">
                                        <thead>
                                            <tr>
												<th scope="col" class="no-width-25 no-check">
                                                    <div class="no-checkbox-form">
                                                        <label for="chkAll">
                                                            <input type="checkbox" id="chkAll" class="no-chk" onClick="doCheckUnCheckOne(this, 'no-chk');" />
                                                            <span>
                                                                <i class="bx bxs-check-square"></i>
                                                            </span>
                                                        </label>
                                                    </div>
                                                </th>
                                                <th>번호</th>
                                                <th>게시판 이름</th>
                                                <th>공지</th>
                                                <th>노출</th>
                                                <th>제목</th>
                                                <th>작성자</th>
                                                <th>작성일</th>
                                                <th>조회수</th>
                                                <th>관리</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($arrResultSet as $v): ?>
                                                <tr>
													<td class="no-check">
                                                        <div class="no-checkbox-form">
                                                            <label for="<?= $v['no'] ?>">
                                                                <input type="checkbox" name="board_file_check_no[]" class="no-chk" id="<?= $v['no'] ?>" value="<?= $v['no'] ?>" />
                                                                <span>
                                                                    <i class="bx bxs-check-square"></i>
                                                                </span>
                                                            </label>
                                                        </div>
                                                    </td>
                                                    <td><?= $rnumber-- ?></td>
                                                    <td><?= htmlspecialchars_decode($v['board_name']) ?></td>
                                                    <td><?= $v['is_notice'] == "Y" ? "<span class='no-btn no-btn--notice'> 공지 </span>" : "" ?></td>
													<td>
														<span class="no-btn <?= ($v['is_view'] === 'Y') ? 'no-btn--notice' : ' no-btn--check'; ?>">
															<?= ($v['is_view'] === 'Y') ? '노출' : '숨김'; ?>
														</span>
													</td>
                                                    <td><a href="./board.view.php?no=<?= $v['no'] ?>&<?= $searchParam ?>"><?= htmlspecialchars_decode($v['title']) ?></a></td>
                                                    <td><?= htmlspecialchars_decode($v['write_name']) ?></td>
                                                    <td><?= htmlspecialchars_decode($v['regdate']) ?></td>
                                                    <td><?= htmlspecialchars_decode($v['read_cnt']) ?></td>
                                                    <td>
														 <div class="no-table-role">
                                                            <span class="no-role-btn">
                                                                <i class="bx bx-dots-vertical-rounded"></i>
                                                            </span>
                                                            <div class="no-table-action">
                                                                <!--<a href="./board.comment.view.php?no=<?= $v['no'] ?>&<?= $searchParam ?>" class="no-btn no-btn--sm no-btn--normal">댓글</a>-->
                                                                <a href="./board.view.php?no=<?= $v['no'] ?>&<?= $searchParam ?>" class="no-btn no-btn--sm no-btn--normal">수정</a>
                                                                <a href="javascript:doCopy(<?= $v['no'] ?>);" class="no-btn no-btn--sm no-btn--normal">복사</a>
                                                                <a href="javascript:doDelete(<?= $v['no'] ?>);" class="no-btn no-btn--sm no-btn--delete-outline">삭제</a>
                                                            </div>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
									<?php if(!$arrResultSet): ?>
										<!-- 글이 하나라도 등록되지 않았을 때 보여줄 div -->
										<p>등록된 내용이 없습니다.</p>
									<?php endif; ?>
								</div>
                            </div>
                        </div>
                    </div>

					<?php include_once "../../lib/admin.pagination.php"; ?>
                </section>
            </form>
        </main>
        <script type="text/javascript" src="./js/board.process.js?v=<?= date('YmdHis') ?>"></script>
        <?php include_once "../../inc/admin.footer.php"; ?>
    </div>
	 <script>
        $(function() {
            $("#sdate").datepicker();
            $("#edate").datepicker();
        });
    </script>
</body>
</html>
