<?php
include_once "../../../inc/lib/base.class.php";

$connect = DB::getInstance();

// 🟢 추가된 부분
$search_word = $_REQUEST['search_word'] ?? '';
$searchColumn = $_REQUEST['searchColumn'] ?? '';
$category = $_REQUEST['category'] ?? '';
$skin = $_REQUEST['skin'] ?? '';

$mainqry = " WHERE a.sitekey = :sitekey";
$params = [':sitekey' => $NO_SITE_UNIQUE_KEY];
$allowedSearchColumns = [
    'a.title' => 'a.title',
    'a.contents' => 'a.contents',
];
$resolvedSearchColumn = $allowedSearchColumns[$searchColumn] ?? '';

// 🔧 검색 조건 처리
if ($search_word !== '' && $resolvedSearchColumn !== '') {
    $mainqry .= " AND (REPLACE($resolvedSearchColumn, ' ', '') LIKE :search_word)";
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

// 🟢 pageBlock 기본값 설정
$pageBlock = isset($pageBlock) ? (int)$pageBlock : 5;

/* 출력될 rows */
$listRowCnt = (int)$perpage;
$listCurPage = max(1, (int)$page);
$count = ($listCurPage - 1) * $listRowCnt;

try {
    // 🔍 전체 개수
    $query = "SELECT COUNT(*) AS cnt FROM nb_board_manage a $mainqry";
    $stmt = $connect->prepare($query);
    $stmt->execute($params);
    $data = $stmt->fetch(PDO::FETCH_ASSOC);
    $totalCnt = $data['cnt'] ?? 0;

    $Page = ceil($totalCnt / $listRowCnt);

    // 🔍 목록 가져오기
    $query = "SELECT a.no, a.title, a.skin, a.regdate, a.top_banner_image, a.contents, a.view_yn, a.secret_yn, 
                      a.sort_no, a.list_size, a.fileattach_yn, a.fileattach_cnt, a.comment_yn 
               FROM nb_board_manage a 
               $mainqry 
               ORDER BY a.no DESC 
               LIMIT :offset, :limitCnt";

    $stmt = $connect->prepare($query);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->bindValue(':offset', (int)$count, PDO::PARAM_INT);
    $stmt->bindValue(':limitCnt', (int)$listRowCnt, PDO::PARAM_INT);
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $rnumber = $totalCnt - ($listCurPage - 1) * $listRowCnt;
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
        <?php include_once "../../inc/admin.header.php"; ?>

        <main class="no-app no-container">
            <?php include_once "../../inc/admin.drawer.php"; ?>

            <form method="POST" name="frm" id="frm" autocomplete="off">
                <section class="no-content">
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

                    <!-- 🔍 검색 영역 -->
                    <div class="no-search no-toolbar-container">
                        <div class="no-card">
                            <div class="no-card-header">
                                <h2 class="no-card-title">게시판 검색</h2>
                            </div>
                            <div class="no-card-body no-admin-column">
                                <div class="no-admin-block wide">
                                    <h3 class="no-admin-title">검색어</h3>
                                    <div class="no-search-select">
                                        <select name="searchColumn" id="searchColumn">
                                            <option value="">선택</option>
                                            <option value="a.title"
                                                <?= ($searchColumn == "a.title") ? "selected" : "" ?>>게시물 제목</option>
                                            <option value="a.contents"
                                                <?= ($searchColumn == "a.contents") ? "selected" : "" ?>>게시물 내용</option>
                                        </select>
                                        <div class="no-search-wrap no-ml">
                                            <div class="no-search-input">
                                                <i class="bx bx-search-alt-2"></i>
                                                <input name="search_word" id="searchKeyword" type="text" title="검색어 입력"
                                                    placeholder="검색어를 입력해주세요."
                                                    value="<?= htmlspecialchars($search_word) ?>" />
                                            </div>
                                            <div class="no-search-btn">
                                                <button type="submit" title="검색"
                                                    class="no-btn no-btn--main no-btn--search">검색</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 🔍 결과 목록 -->
                    <div class="no-content-container">
                        <div class="no-card">
                            <div class="no-card-header">
                                <h2 class="no-card-title">게시글 관리</h2>
                            </div>
                            <div class="no-card-body">
                                <div class="no-table-responsive">
                                    <table class="no-table">
                                        <thead>
                                            <tr>
                                                <th>번호</th>
                                                <th>게시판 이름</th>
                                                <th>관리</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($result as $v): ?>
                                            <tr>
                                                <td><?= $rnumber ?></td>
                                                <td>
                                                    <?php if (!$role->isReadOnly()): ?>
                                                    <a href="./board.role.view.php?no=<?= $v['no'] ?>"><?= htmlspecialchars($v['title']) ?></a>
                                                    <?php else: ?>
                                                    <?= htmlspecialchars($v['title']) ?>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if (!$role->isReadOnly()): ?>
                                                    <div class="no-table-role">
                                                        <a href="./board.role.view.php?no=<?= $v['no'] ?>"
                                                            class="no-btn no-btn--sm no-btn--normal">권한설정</a>
                                                    </div>
                                                    <?php endif; ?>
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

                    <?php include_once "../../lib/admin.pagination.php"; ?>
                </section>
            </form>
        </main>

        <?php include_once "../../inc/admin.footer.php"; ?>
    </div>
</body>

</html>
