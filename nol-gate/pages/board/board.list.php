<?php
include_once "../../../inc/lib/base.class.php";

$page = $_REQUEST['page'] ?? 1;
$perpage = $_REQUEST['perpage'] ?? 20;
$listCurPage = (int)$page;
$listRowCnt = (int)$perpage;
$pageBlock = $pageBlock ?? 5;
$count = ($listCurPage - 1) * $listRowCnt;

$searchKeyword = $_REQUEST['searchKeyword'] ?? '';
$searchColumn = $_REQUEST['searchColumn'] ?? '';
$sdate = $_REQUEST['sdate'] ?? '';
$edate = $_REQUEST['edate'] ?? '';
$board_no = $_REQUEST['board_no'] ?? null;

$connect = DB::getInstance();
$mainqry = " WHERE a.sitekey = :sitekey ";
$params = [':sitekey' => $NO_SITE_UNIQUE_KEY];
$allowedSearchColumns = [
    'a.title' => 'a.title',
    'a.contents' => 'a.contents',
];
$resolvedSearchColumn = $allowedSearchColumns[$searchColumn] ?? '';

try {
    if ($board_no) {
        $mainqry .= " AND a.board_no = :board_no ";
        $params[':board_no'] = intval($board_no);
    }

    if ($resolvedSearchColumn !== '' && $searchKeyword !== '') {
        $mainqry .= " AND ( REPLACE($resolvedSearchColumn, ' ', '') LIKE :searchKeyword ) ";
        $params[':searchKeyword'] = '%' . trim($searchKeyword) . '%';
    }

    if ($sdate && $edate) {
        $mainqry .= " AND (DATE_FORMAT(a.regdate, '%Y-%m-%d') BETWEEN :sdate AND :edate)";
        $params[':sdate'] = $sdate;
        $params[':edate'] = $edate;
    }

    $query = "SELECT COUNT(*) AS cnt FROM nb_board a $mainqry";
    $stmt = $connect->prepare($query);
    $stmt->execute($params);
    $data = $stmt->fetch(PDO::FETCH_ASSOC);
    $totalCnt = $data['cnt'] ?? 0;
    $Page = ceil($totalCnt / $listRowCnt);

    $cntSql = "
      SELECT board_no, COUNT(*) AS cnt
      FROM nb_board
      WHERE sitekey = :sitekey
      GROUP BY board_no
    ";
    $cntStmt = $connect->prepare($cntSql);
    $cntStmt->execute([':sitekey' => $NO_SITE_UNIQUE_KEY]);
    $boardCounts = [];
    foreach ($cntStmt->fetchAll(PDO::FETCH_ASSOC) as $r) {
        $boardCounts[(int)$r['board_no']] = (int)$r['cnt'];
    }

    $query = "
       SELECT 
            a.no, 
            a.sort_no, 
            a.board_no, 
            a.user_no, 
            a.category_no, 
            a.comment_cnt, 
            a.title, 
            a.contents, 
            a.regdate, 
            a.read_cnt, 
            a.thumb_image, 
            a.is_admin_writed, 
            a.is_notice, 
            a.is_secret, 
            a.secret_pwd, 
            a.write_name, 
            a.isFile, 
            a.file_attach_1, a.file_attach_origin_1, 
            a.file_attach_2, a.file_attach_origin_2, 
            a.file_attach_3, a.file_attach_origin_3, 
            a.file_attach_4, a.file_attach_origin_4, 
            a.file_attach_5, a.file_attach_origin_5, 
            a.extra1, a.extra2, a.extra3, a.extra4, a.extra5, 
            a.extra6, a.extra7, a.extra8, a.extra9, a.extra10, 
            a.extra11, a.extra12, a.extra13, a.extra14, a.extra15, 
            b.title AS board_name
        FROM nb_board a
        LEFT JOIN nb_board_manage b 
            ON a.board_no = b.no
        $mainqry
        ORDER BY a.sort_no ASC
        LIMIT :count, :listRowCnt
    ";
    $stmt = $connect->prepare($query);

    foreach ($params as $key => $value) $stmt->bindValue($key, $value);
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
    ClientFault::abortPage($e);
    exit;
}
?>


<!--=====================HEAD========================= -->
<?php include_once "../../inc/admin.head.php"; ?>

<body data-page="board">
    <div class="no-wrap">

        <!--=====================HEADER========================= -->
        <?php include_once "../../inc/admin.header.php"; ?>

        <!--=====================MAIN========================= -->
        <main class="no-app no-container">

            <!--=====================DRAWER========================= -->
            <?php include_once "../../inc/admin.drawer.php"; ?>

            <!--=====================CONTENTS========================= -->
            <form method="POST" name="frm" id="frm" autocomplete="off">
                <input type="hidden" name="mode" id="mode" value="list">
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
                            <?php if ($role->canCreate()): ?>
                            <div class="no-items-center">
                                <a href="./board.add.php" class="no-btn no-btn--main no-btn--big"> 글등록 </a>
                            </div>
                            <?php endif; ?>
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
                                        <input type="text" name="sdate" id="sdate"
                                            value="<?php echo isset($sdate) ? htmlspecialchars($sdate) : ''; ?>" />
                                        <span></span>
                                        <input type="text" name="edate" id="edate"
                                            value="<?php echo isset($edate) ? htmlspecialchars($edate) : ''; ?>" />
                                    </div>
                                </div>

                                <div class="no-admin-block wide">
                                    <h3 class="no-admin-title">검색어</h3>
                                    <div class="no-search-select">
                                        <select name="searchColumn" id="searchColumn">
                                            <option value="">선택</option>
                                            <option value="a.title"
                                                <?php if (isset($searchColumn) && $searchColumn == "a.title") echo "selected"; ?>>
                                                게시물 제목</option>
                                            <option value="a.contents"
                                                <?php if (isset($searchColumn) && $searchColumn == "a.contents") echo "selected"; ?>>
                                                게시물 내용</option>
                                        </select>
                                        <div class="no-search-wrap no-ml">
                                            <div class="no-search-input">
                                                <i class="bx bx-search-alt-2"></i>
                                                <input name="searchKeyword" id="searchKeyword" type="text"
                                                    title="검색어 입력" placeholder="검색어를 입력해주세요."
                                                    value="<?php echo isset($searchKeyword) ? htmlspecialchars($searchKeyword) : ''; ?>" />
                                            </div>
                                            <div class="no-search-btn">
                                                <button type="button" title="검색"
                                                    class="no-btn no-btn--main no-btn--search"
                                                    onClick="doSearchList();">
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
                                            <a href="javascript:void(0);" class="no-btn no-btn--sm no-btn--check active"
                                                onClick="doCheckUnCheck('no-chk', 'check');">전체선택</a>
                                        </li>
                                        <li>
                                            <a href="javascript:void(0);" class="no-btn no-btn--sm no-btn--check"
                                                onClick="doCheckUnCheck('no-chk', 'uncheck');">선택해제</a>
                                        </li>
                                        <?php if (!$role->isReadOnly()): ?>
                                        <li>
                                            <a href="javascript:void(0);" class="no-btn no-btn--sm no-btn--check"
                                                onClick="doDeleteArray();">선택삭제</a>
                                        </li>
                                        <?php endif; ?>
                                    </ul>
                                    <span>총 <?= $totalCnt ?>개</span>
                                </div>

                                <div class="no-table-responsive">
                                    <table class="no-table">
                                        <thead>
                                            <tr>
                                                <th scope="col" class="no-width-25 no-check">
                                                    <div class="no-checkbox-form">
                                                        <label for="chkAll">
                                                            <input type="checkbox" id="chkAll" class="no-chk"
                                                                onClick="doCheckUnCheckOne(this, 'no-chk');" />
                                                            <span>
                                                                <i class="bx bxs-check-square"></i>
                                                            </span>
                                                        </label>
                                                    </div>
                                                </th>
                                                <th>게시판 이름</th>
                                                <th>공지</th>
                                                <th>제목</th>
                                                <th>순서변경</th>
                                                <th>노출순서</th>
                                                <th>작성일</th>
                                                <th>관리</th>
                                            </tr>
                                        </thead>
                                        <tbody id="sort-list">
                                            <?php foreach ($arrResultSet as $v):
                                            ?>
                                            <tr data-no=<?= $v['no'] ?> data-sort-no=<?= $v['sort_no'] ?>>

                                                <td class="no-check">
                                                    <div class="no-checkbox-form">
                                                        <label for="<?= $v['no'] ?>">
                                                            <input type="checkbox" name="board_file_check_no[]"
                                                                class="no-chk" id="<?= $v['no'] ?>"
                                                                value="<?= $v['no'] ?>" />
                                                            <span>
                                                                <i class="bx bxs-check-square"></i>
                                                            </span>
                                                        </label>
                                                    </div>
                                                </td>

                                                <td><?= htmlspecialchars($v['board_name']) ?></td>
                                                <td><?= $v['is_notice'] == "Y" ? "<span class='no-btn no-btn--notice'> 공지 </span>" : "" ?>
                                                </td>
                                                <td style="max-width: 40rem"><a
                                                        href="./board.view.php?no=<?= $v['no'] ?>&<?= $searchParam ?>"><?= htmlspecialchars($v['title']) ?></a>
                                                </td>

                                                <td class="sort-btn-group">

                                                    <button type="button" class="sort-btn" data-id="<?= $v['no'] ?>"
                                                        data-action="down" data-no="<?= $v['sort_no'] - 1 ?>">
                                                        <i class='bx bx-chevron-up'></i>
                                                    </button>
                                                    <button type="button" class="sort-btn" data-id="<?= $v['no'] ?>"
                                                        data-action="up" data-no="<?= $v['sort_no'] + 1 ?>">
                                                        <i class='bx bx-chevron-down'></i>
                                                    </button>
                                                    <button type="button" class="sort-btn" data-id="<?= $v['no'] ?>"
                                                        data-action="last" data-no="1">
                                                        <i class='bx bx-chevrons-up'></i>
                                                    </button>
                                                    <button type="button" class="sort-btn" data-id="<?= $v['no'] ?>"
                                                        data-action="first" data-no="<?= $totalCnt ?>">
                                                        <i class='bx bx-chevrons-down'></i>
                                                    </button>
                                                </td>

                                                <td><?= $v['sort_no'] ?></td>
                                                <td><?= htmlspecialchars($v['regdate']) ?></td>
                                                <td>
                                                    <div class="no-table-role">
                                                        <span class="no-role-btn">
                                                            <i class="bx bx-dots-vertical-rounded"></i>
                                                        </span>
                                                        <div class="no-table-action">
                                                            <a href="./board.view.php?no=<?= $v['no'] ?>&<?= $searchParam ?>"
                                                                class="no-btn no-btn--sm no-btn--normal">보기</a>
                                                            <?php if (!$role->isReadOnly()): ?>
                                                            <a href="./board.view.php?no=<?= $v['no'] ?>&<?= $searchParam ?>"
                                                                class="no-btn no-btn--sm no-btn--normal">수정</a>
                                                            <a href="javascript:doDelete(<?= $v['no'] ?>);"
                                                                class="no-btn no-btn--sm no-btn--delete-outline">삭제</a>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                    <?php if (!$arrResultSet): ?>
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

        <script src="./js/board.process.js?v=<?= date('YmdHis') ?>"></script>
        <script type="module" src="./js/board.control.js?v=<?= date('YmdHis') ?>" defer></script>
    </div>
    <?php include_once "../../inc/admin.footer.php"; ?>
