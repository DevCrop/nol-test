<?php
include_once "../../../inc/lib/base.class.php";
$db = DB::getInstance();

// 기본 페이지네이션 변수
$perpage = 10;
$listCurPage = isset($_POST['page']) ? (int)$_POST['page'] : (isset($_GET['page']) ? (int)$_GET['page'] : 1);
$pageBlock = 2;
$count = ($listCurPage - 1) * $perpage;

// ========================= WHERE 조건 구성 =========================
$searchKeyword = $_GET['searchKeyword'] ?? '';

$where = "WHERE 1=1";
$params = [];

if (!empty($searchKeyword)) {
    $where .= " AND (title LIKE :searchKeyword OR content LIKE :searchKeyword)";
    $params[':searchKeyword'] = "%{$searchKeyword}%";
}
// ========================= END WHERE =========================

// ========================= 전체 개수 조회 =========================
$totalSql = "
    SELECT COUNT(*) 
    FROM nb_privacy_policy p
    {$where}
";
$totalStmt = $db->prepare($totalSql);
$totalStmt->execute($params);
$totalCount = (int)$totalStmt->fetchColumn();
$Page = ceil($totalCount / $perpage);

// ========================= END 카운트 =========================

// ========================= 실제 데이터 조회 =========================
$sql = "
    SELECT p.id, p.title, p.apply_date, p.created_at, p.updated_at
    FROM nb_privacy_policy p
    {$where}
    ORDER BY p.apply_date DESC, p.id DESC
    LIMIT {$count}, {$perpage}
";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
// ========================= END =========================
?>

<!--=====================HEAD========================= -->
<?php include_once "../../inc/admin.head.php"; ?>

<body data-page="privacy">
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
                                        <li class="no-breadcrumb-item"><span>사이트정보관리</span></li>
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
                                        <div class="no-search-wrap no-ml">
                                            <div class="no-search-input">
                                                <i class="bx bx-search-alt-2"></i>
                                                <input type="text" name="searchKeyword" id="searchKeyword"
                                                    placeholder="제목 또는 내용으로 검색"
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
                                <div class="no-table-option">
                                    <?php if ($role->canDelete()): ?>
                                    <ul class="no-table-check-control">
                                        <li><a href="#" class="no-btn no-btn--sm no-btn--check active "
                                                data-action="selectAll">전체선택</a></li>
                                        <li><a href="#" class="no-btn no-btn--sm no-btn--check"
                                                data-action="deselectAll">선택해제</a></li>
                                        <li><a href="#" class="no-btn no-btn--sm no-btn--check"
                                                data-action="deleteSelected">선택삭제</a></li>
                                    </ul>
                                    <?php endif; ?>
                                    <span>총 <?= $totalCount ?>개</span>
                                </div>

                                <div class="no-table-responsive">
                                    <table class="no-table">
                                        <thead>
                                            <tr>
                                                <?php if ($role->canDelete()): ?>
                                                <th class="no-width-25 no-check">
                                                    <div class="no-checkbox-form">
                                                        <label>
                                                            <input type="checkbox" id="selectAllCheckbox" />
                                                            <span><i class="bx bxs-check-square"></i></span>
                                                        </label>
                                                    </div>
                                                </th>
                                                <?php endif; ?>
                                                <th>제목</th>
                                                <th>적용 날짜</th>
                                                <th>등록일</th>
                                                <th>수정일</th>
                                                <th>관리</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (count($rows) > 0): ?>
                                            <?php foreach ($rows as $row): ?>
                                            <tr>
                                                <?php if ($role->canDelete()) : ?>
                                                <td class="no-check">
                                                    <div class="no-checkbox-form">
                                                        <label>
                                                            <input type="checkbox" class="no-chk"
                                                                value="<?= $row['id'] ?>" />
                                                            <span><i class="bx bxs-check-square"></i></span>
                                                        </label>
                                                    </div>
                                                </td>
                                                <?php endif; ?>
                                                <td><?= htmlspecialchars($row['title']) ?></td>
                                                <td><?= $row['apply_date'] ? date('Y-m-d', strtotime($row['apply_date'])) : '-' ?></td>
                                                <td><?= $row['created_at'] ? date('Y-m-d', strtotime($row['created_at'])) : '-' ?></td>
                                                <td><?= $row['updated_at'] ? date('Y-m-d', strtotime($row['updated_at'])) : '-' ?></td>
                                                <td>
                                                    <div class="no-table-role">
                                                        <span class="no-role-btn"><i
                                                                class="bx bx-dots-vertical-rounded"></i></span>
                                                        <div class="no-table-action">
                                                            <a href="edit.php?id=<?= $row['id'] ?>&page=<?= $listCurPage ?>"
                                                                class="no-btn no-btn--sm no-btn--normal">보기</a>
                                                            <?php if (!$role->isReadOnly()): ?>
                                                            <a href="edit.php?id=<?= $row['id'] ?>&page=<?= $listCurPage ?>"
                                                                class="no-btn no-btn--sm no-btn--normal">수정</a>
                                                            <?php endif; ?>
                                                            <?php if ($role->canDelete()) : ?>
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
                                                <td colspan="<?= $role->canDelete() ? '6' : '5' ?>" style="text-align: center; color: #888;">등록된 <?= $pageName ?>가 없습니다.</td>
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

    <script>
    $(document).ready(function() {
        // 전체 선택/해제
        $('#selectAllCheckbox').on('change', function() {
            $('.no-chk').prop('checked', $(this).prop('checked'));
        });

        // 체크박스 제어
        $('[data-action="selectAll"]').on('click', function(e) {
            e.preventDefault();
            $('.no-chk').prop('checked', true);
            $('#selectAllCheckbox').prop('checked', true);
        });

        $('[data-action="deselectAll"]').on('click', function(e) {
            e.preventDefault();
            $('.no-chk').prop('checked', false);
            $('#selectAllCheckbox').prop('checked', false);
        });

        // 선택 삭제
        $('[data-action="deleteSelected"]').on('click', function(e) {
            e.preventDefault();
            var selectedIds = $('.no-chk:checked').map(function() {
                return $(this).val();
            }).get();

            if (selectedIds.length === 0) {
                alert('삭제할 항목을 선택해주세요.');
                return;
            }

            if (!confirm('선택한 ' + selectedIds.length + '개의 항목을 삭제하시겠습니까?')) {
                return;
            }

            $.ajax({
                url: './process.php',
                type: 'POST',
                data: {
                    mode: 'delete',
                    ids: selectedIds
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        alert(response.message);
                        location.reload();
                    } else {
                        alert(response.message || '삭제 중 오류가 발생했습니다.');
                    }
                },
                error: function() {
                    alert('삭제 중 오류가 발생했습니다.');
                }
            });
        });

        // 개별 삭제
        $('.delete-btn').on('click', function() {
            var id = $(this).data('id');
            if (!confirm('정말 삭제하시겠습니까?')) {
                return;
            }

            $.ajax({
                url: './process.php',
                type: 'POST',
                data: {
                    mode: 'delete',
                    id: id
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        alert(response.message);
                        location.reload();
                    } else {
                        alert(response.message || '삭제 중 오류가 발생했습니다.');
                    }
                },
                error: function() {
                    alert('삭제 중 오류가 발생했습니다.');
                }
            });
        });
    });
    </script>
</body>

</html>


