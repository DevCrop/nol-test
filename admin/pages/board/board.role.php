<?php include_once "../../../inc/lib/base.class.php"; ?>
<!DOCTYPE html>
<html>
<?php

    $depthnum = 1;
    $pagenum = 3;

    $connect = DB::getInstance();

    $search_word = $_REQUEST['search_word'] ?? '';
    $category = $_REQUEST['category'] ?? '';
    $skin = $_REQUEST['skin'] ?? '';

    $mainqry = " WHERE a.sitekey = :sitekey";
    $params = [':sitekey' => $NO_SITE_UNIQUE_KEY];

    if ($search_word) {
        $mainqry .= " AND (REPLACE(a.title, ' ', '') LIKE :search_word)";
        $params[':search_word'] = '%' . trim($search_word) . '%';
    }

    if ($category) {
        $mainqry .= " AND category = :category";
        $params[':category'] = trim($category);
    }

    if ($skin) {
        $mainqry .= " AND skin = :skin";
        $params[':skin'] = trim($skin);
    }

    $page = $_POST['page'] ?? 1;
    $perpage = $_POST['perpage'] ?? 50;

    /* 출력될 rows */
    $listRowCnt = (int)$perpage;
    $listCurPage = max(1, (int)$page);
    $count = ($listCurPage - 1) * $listRowCnt;

    try {
        $query = "SELECT COUNT(*) AS cnt FROM nb_board_manage a $mainqry";
        $stmt = $connect->prepare($query);
        $stmt->execute($params);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        $totalCnt = $data['cnt'] ?? 0;

        $Page = ceil($totalCnt / $listRowCnt);

        // Note: Using direct variable substitution for LIMIT
        $query = "SELECT a.no, a.title, a.skin, a.regdate, a.top_banner_image, a.contents, a.view_yn, a.secret_yn, 
                  a.sort_no, a.list_size, a.fileattach_yn, a.fileattach_cnt, a.comment_yn 
                  FROM nb_board_manage a 
                  $mainqry 
                  ORDER BY a.no DESC 
                  LIMIT $count, $listRowCnt";
        
        $stmt = $connect->prepare($query);
        $stmt->execute($params);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $rnumber = $totalCnt - ($listCurPage - 1) * $listRowCnt;
    } catch (Exception $e) {
        echo "데이터베이스 오류가 발생하였습니다: " . blue_safe_error($e);
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
            <form method="POST" name="frm" id="frm" autocomplete="off">
                <section class="no-content">
                    <!-- Page Title -->
                    <div class="no-toolbar">
                        <div class="no-toolbar-container no-flex-stack">
                            <div class="no-page-indicator">
                                <h1 class="no-page-title">게시판 권한</h1>
                                <div class="no-breadcrumb-container">
                                    <ul class="no-breadcrumb-list">
                                        <li class="no-breadcrumb-item"><span>게시판</span></li>
                                        <li class="no-breadcrumb-item"><span>게시판 권한</span></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Search -->
                    <div class="no-search no-toolbar-container">
                        <div class="no-card">
                            <div class="no-card-header"><h2 class="no-card-title">게시판 검색</h2></div>
                            <div class="no-card-body no-admin-column">
                                <div class="no-admin-block wide">
                                    <h3 class="no-admin-title">검색어</h3>
                                    <div class="no-search-select">
                                        <select name="searchColumn" id="searchColumn">
                                            <option value="">선택</option>
                                            <option value="a.title" <?php if ($searchColumn == "a.title") echo "selected"; ?>>게시물 제목</option>
                                            <option value="a.contents" <?php if ($searchColumn == "a.contents") echo "selected"; ?>>게시물 내용</option>
                                        </select>
                                        <div class="no-search-wrap no-ml">
                                            <div class="no-search-input">
                                                <i class="bx bx-search-alt-2"></i>
                                                <input name="searchKeyword" id="searchKeyword" type="text" title="검색어 입력" placeholder="검색어를 입력해주세요." value="<?= $searchKeyword ?>" />
                                            </div>
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
                            <div class="no-card-header"><h2 class="no-card-title">게시글 관리</h2></div>
                            <div class="no-card-body">
                                <div class="no-table-responsive">
                                    <table class="no-table">
                                        <caption class="no-blind">번호, 게시판 이름, 공지, 제목, 작성자, 작성일, 조회수, 관리로 구성된 게시글 관리표</caption>
                                        <thead>
                                            <tr>
                                                <th scope="col" class="no-width-120 no-min-width-60">번호</th>
                                                <th scope="col" class="no-min-width-150">게시판 이름</th>
                                                <th scope="col" class="no-min-width-role no-td-center no-min-width-120">관리</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($result as $v): ?>
                                                <tr>
                                                    <td><span><?= $rnumber ?></span></td>
                                                    <td class="no-td-title"><a href="./board.role.view.php?no=<?= $v['no'] ?>"><?= $v['title'] ?></a></td>
                                                    <td>
                                                        <div class="no-table-role">
                                                            <span class="no-role-btn"><i class="bx bx-dots-vertical-rounded"></i></span>
                                                            <div class="no-table-action">
                                                                <a href="./board.role.view.php?no=<?= $v['no'] ?>" class="no-btn no-btn--sm no-btn--normal">권한설정</a>
                                                            </div>
                                                        </div>
                                                    </td>
                                                </tr>
                                                <?php $rnumber--; ?>
                                            <?php endforeach; ?>
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
        <?php include_once "../../inc/admin.footer.php"; ?>
    </div>
</body>
</html>
