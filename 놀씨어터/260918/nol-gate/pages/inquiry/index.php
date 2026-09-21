<?php
include_once "../../../inc/lib/base.class.php";
require_once dirname(__DIR__, 2) . "/lib/PiiMask.php";
$db = DB::getInstance();

/* =============================
 * 페이지네이션 기본값
 * ============================= */
$perpage     = 10; // 페이지당 개수
$listCurPage = isset($_POST['page']) ? (int)$_POST['page'] : (isset($_GET['page']) ? (int)$_GET['page'] : 1);
if ($listCurPage < 1) $listCurPage = 1;
$pageBlock   = 2;   // 좌우 페이지 번호 개수
$offset      = ($listCurPage - 1) * $perpage;

/* =============================
 * GET 필터 파라미터
 * ============================= */
$searchColumn   = isset($_GET['searchColumn'])   ? trim($_GET['searchColumn'])   : '';
$searchKeyword  = isset($_GET['searchKeyword'])  ? trim($_GET['searchKeyword'])  : '';

/* =============================
 * WHERE 절 구성
 * ============================= */
$where  = "WHERE 1=1";
$params = [];

if ($searchColumn !== '' && $searchKeyword !== '') {
    $allowedColumns = [
        'performance_name' => 'performance_name',
        'company' => 'company',
        'name' => 'name',
    ];
    $resolvedSearchColumn = $allowedColumns[$searchColumn] ?? '';
    if ($resolvedSearchColumn !== '') {
        $where .= " AND r.`{$resolvedSearchColumn}` LIKE :searchKeyword";
        $params[':searchKeyword'] = "%{$searchKeyword}%";
    }
}

/* =============================
 * 총 개수 → 전체 페이지 수
 * ============================= */
$countSql = "
    SELECT COUNT(*)
    FROM nb_request r
    {$where}
";
$countStmt = $db->prepare($countSql);
$countStmt->execute($params);
$totalCount = (int)$countStmt->fetchColumn();

$Page = (int)ceil($totalCount / $perpage);
if ($Page > 0 && $listCurPage > $Page) {
    $listCurPage = $Page;
    $offset = ($listCurPage - 1) * $perpage;
}

/* =============================
 * 리스트 조회 (LIMIT)
 * ============================= */
$sql = "
    SELECT *
    FROM nb_request r
    {$where}
    ORDER BY r.regdate DESC
    LIMIT :offset, :perpage
";
$stmt = $db->prepare($sql);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
$stmt->bindValue(':perpage', (int)$perpage, PDO::PARAM_INT);
$stmt->execute();
$requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>


<!--=====================HEAD========================= -->
<?php include_once "../../inc/admin.head.php"; ?>

<body data-page="inquiry">
    <div class="no-wrap">

        <!--=====================HEADER========================= -->
        <?php include_once "../../inc/admin.header.php"; ?>

        <main class="no-app no-container">

            <!--=====================DRAWER========================= -->
            <?php include_once "../../inc/admin.drawer.php"; ?>

            <form method="GET" name="frm" id="frm" autocomplete="off">
                <input type="hidden" name="mode" id="mode" value="list">

                <section class="no-content">
                    <div class="no-toolbar">
                        <div class="no-toolbar-container no-flex-stack">
                            <div class="no-page-indicator">
                                <h1 class="no-page-title"><?= $pageName ?> 관리</h1>
                                <div class="no-breadcrumb-container">
                                    <ul class="no-breadcrumb-list">
                                        <li class="no-breadcrumb-item"><span>환경설정</span></li>
                                        <li class="no-breadcrumb-item"><span><?= $pageName ?> 관리</span></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 검색 조건 -->
                    <div class="no-search no-toolbar-container">
                        <div class="no-card">
                            <div class="no-card-header">
                                <h2 class="no-card-title"><?= $pageName ?> 검색</h2>
                            </div>
                            <div class="no-card-body no-admin-column">


                                <!-- 검색어 -->
                                <div class="no-admin-block wide">
                                    <h3 class="no-admin-title">검색어</h3>
                                    <div class="no-search-select">
                                        <select name="searchColumn" id="searchColumn">
                                            <option value="">선택</option>
                                            <option value="performance_name"
                                                <?= ($searchColumn ?? '') === 'performance_name' ? 'selected' : '' ?>>공연명</option>
                                            <option value="company"
                                                <?= ($searchColumn ?? '') === 'company' ? 'selected' : '' ?>>단체명</option>
                                            <option value="name"
                                                <?= ($searchColumn ?? '') === 'name' ? 'selected' : '' ?>>담당자명</option>
                                        </select>
                                        <div class="no-search-wrap no-ml">
                                            <div class="no-search-input">
                                                <i class="bx bx-search-alt-2"></i>
                                                <input type="text" name="searchKeyword" id="searchKeyword"
                                                    placeholder="검색어 입력"
                                                    value="<?= htmlspecialchars($searchKeyword ?? '') ?>">
                                            </div>
                                            <div class="no-search-btn">
                                                <button type="submit" class="no-btn no-btn--main no-btn--search">
                                                    검색
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>


                    <div class="no-content-container">
                        <div class="no-card">
                            <div class="no-card-header">
                                <h2 class="no-card-title"><?= $pageName ?> 리스트</h2>
                            </div>

                            <div class="no-card-body">

                                <div class="no-table-responsive">
                                    <table class="no-table">
                                        <thead>
                                            <tr>
                                                <th>번호</th>
                                                <th>공연명</th>
                                                <th>단체명</th>
                                                <th>담당자명</th>
                                                <th>신청 일자</th>
                                                <th>관리</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (count($requests) > 0): ?>
                                                <?php foreach ($requests as $request): ?>
                                                    <tr data-request-no="<?= (int)$request['no'] ?>">
                                                        <td><?= $request['no'] ?></td>
                                                        <td><?= htmlspecialchars($request['performance_name'] ?? '') ?></td>
                                                        <td><?= htmlspecialchars($request['company'] ?? '') ?></td>
                                                        <td><?= htmlspecialchars(PiiMask::name((string)($request['name'] ?? ''))) ?></td>
                                                        <td><?= htmlspecialchars($request['regdate'] ?? '') ?></td>
                                                        <td>
                                                            <div class="no-table-role">
                                                                <span class="no-role-btn"><i
                                                                        class="bx bx-dots-vertical-rounded"></i></span>
                                                                <div class="no-table-action">
                                                                    <a href="view.php?no=<?= $request['no'] ?>"
                                                                        class="no-btn no-btn--sm no-btn--normal">자세히 보기</a>
                                                                    <?php if ($role->canDelete()): ?>
                                                                    <button type="button"
                                                                        class="no-btn no-btn--sm no-btn--delete-outline no-inquiry-delete-btn"
                                                                        data-no="<?= (int)$request['no'] ?>"
                                                                        aria-label="삭제">삭제</button>
                                                                    <?php endif; ?>
                                                                </div>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <tr>
                                                    <td colspan="6" style="text-align:center;">대관 신청 내역이 없습니다.</td>
                                                </tr>
                                            <?php endif; ?>
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
    </div>

    <?php include_once "../../inc/admin.footer.php"; ?>

    <?php if ($role->canDelete()): ?>
    <script>
    (function() {
        document.querySelectorAll('.no-inquiry-delete-btn').forEach(function(btn) {
            btn.addEventListener('click', function() {
                var no = this.getAttribute('data-no');
                if (!no) return;
                if (!confirm('이 신청 데이터를 삭제하시겠습니까?\n삭제된 데이터는 복구할 수 없습니다.')) return;

                var tr = this.closest('tr');
                var xhr = new XMLHttpRequest();
                xhr.open('POST', 'ajax/inquiry.delete.php');
                xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
                xhr.onreadystatechange = function() {
                    if (xhr.readyState === 4) {
                        var res = null;
                        try { res = JSON.parse(xhr.responseText); } catch (e) {}
                        if (res && res.result === 'success') {
                            if (tr && tr.parentNode) tr.remove();
                            if (typeof alert === 'function') alert(res.msg || '삭제되었습니다.');
                        } else {
                            if (typeof alert === 'function') alert(res && res.msg ? res.msg : '삭제 처리에 실패했습니다.');
                        }
                    }
                };
                xhr.send('mode=delete&no=' + encodeURIComponent(no));
            });
        });
    })();
    </script>
    <?php endif; ?>
