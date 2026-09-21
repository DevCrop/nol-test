<?php
include_once "../../../inc/lib/base.class.php";
$role->redirectIfReadOnly();

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
 * 총 개수 → 전체 페이지 수
 * ============================= */
$totalStmt  = $db->query("SELECT COUNT(*) FROM nb_site_tags");
$totalCount = (int)$totalStmt->fetchColumn();
$Page       = (int)ceil($totalCount / $perpage);

// 현재 페이지 보정
if ($Page > 0 && $listCurPage > $Page) {
    $listCurPage = $Page;
}
$offset = ($listCurPage - 1) * $perpage;

/* =============================
 * 목록 조회 (LIMIT 바인딩)
 * ============================= */
$sql = "SELECT id, title, tag_content, location, is_active
        FROM nb_site_tags
        ORDER BY id DESC
        LIMIT :offset, :perpage";
$stmt = $db->prepare($sql);
$stmt->bindValue(':offset',  $offset,  PDO::PARAM_INT);
$stmt->bindValue(':perpage', $perpage, PDO::PARAM_INT);
$stmt->execute();
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!--=====================HEAD========================= -->
<?php include_once "../../inc/admin.head.php"; ?>

<body data-page="setting">
    <div class="no-wrap">

        <!--=====================HEADER========================= -->
        <?php include_once "../../inc/admin.header.php"; ?>

        <main class="no-app no-container">

            <!--=====================DRAWER========================= -->
            <?php include_once "../../inc/admin.drawer.php"; ?>

            <form method="POST" name="frm" id="frm" autocomplete="off">
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
                            <?php if ($role->canCreate()): ?>
                            <div class="no-items-center">
                                <a href="./new.php" class="no-btn no-btn--main no-btn--big"> <?= $pageName ?> 생성 </a>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="no-content-container">
                        <div class="no-card">
                            <div class="no-card-header">
                                <h2 class="no-card-title"><?= $pageName ?> 리스트</h2>
                            </div>

                            <div class="no-card-body">
                                <div class="no-table-option">
                                    <ul class="no-table-check-control">
                                        <li><a href="#" class="no-btn no-btn--sm no-btn--check active "
                                                data-action="selectAll">전체선택</a>
                                        </li>
                                        <li><a href="#" class="no-btn no-btn--sm" data-action="deselectAll">선택해제</a>
                                        </li>
                                        <?php if ($role->canDelete()): ?>
                                        <li><a href="#" class="no-btn no-btn--sm" data-action="deleteSelected">선택삭제</a>
                                        </li>
                                        <?php endif; ?>
                                    </ul>
                                </div>

                                <div class="no-table-responsive">
                                    <table class="no-table">
                                        <thead>
                                            <tr>
                                                <th class="no-width-25 no-check">
                                                    <div class="no-checkbox-form">
                                                        <label>
                                                            <input type="checkbox" id="selectAllCheckbox" />
                                                            <span><i class="bx bxs-check-square"></i></span>
                                                        </label>
                                                    </div>
                                                </th>
                                                <th>번호</th>
                                                <th>제목</th>
                                                <th>위치</th>
                                                <th>사용 여부</th>
                                                <th>관리</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (!empty($rows)): ?>
                                                <?php foreach ($rows as $row): ?>
                                                    <tr>
                                                        <td class="no-check">
                                                            <div class="no-checkbox-form">
                                                                <label>
                                                                    <input type="checkbox" class="no-chk"
                                                                        value="<?= $row['id'] ?>">
                                                                    <span><i class="bx bxs-check-square"></i></span>
                                                                </label>
                                                            </div>
                                                        </td>
                                                        <td><?= $row['id'] ?></td>
                                                        <td><?= $row['title'] ?></td>
                                                        <td>
                                                            <?php
                                                            require_once __DIR__ . '/../../../inc/lib/var.php';
                                                            $location = (int)($row['location'] ?? 1);
                                                            echo htmlspecialchars($tag_locations[$location] ?? 'HEAD');
                                                            ?>
                                                        </td>
                                                        <td>
                                                            <span
                                                                class="no-badge <?= $row['is_active'] == 1 ? 'no-badge--success' : 'no-badge--gray' ?>">
                                                                <?= $row['is_active'] == 1 ? '사용' : '미사용' ?>
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <div class="no-table-role">
                                                                <span class="no-role-btn"><i
                                                                        class="bx bx-dots-vertical-rounded"></i></span>
                                                                <div class="no-table-action">
                                                                    <?php if (!$role->isReadOnly()): ?>
                                                                    <a href="edit.php?id=<?= $row['id'] ?>&page=<?= $listCurPage ?>"
                                                                        class="no-btn no-btn--sm no-btn--normal">수정</a>
                                                                    <?php endif; ?>
                                                                    <?php if ($role->canDelete()): ?>
                                                                    <button type="button"
                                                                        class="no-btn no-btn--sm no-btn--delete-outline delete-btn"
                                                                        data-id="<?= $row['id'] ?>">
                                                                        삭제
                                                                    </button>
                                                                    <?php endif; ?>
                                                                </div>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <tr>
                                                    <td colspan="6">등록된 외부 태그가 없습니다.</td>
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