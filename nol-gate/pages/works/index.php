<?php
include_once "../../../inc/lib/base.class.php";

$db = DB::getInstance();

$works_venue = $works_venue ?? [
    1 => '우리카드홀',
    2 => '우리투자증권홀',
    3 => '기타',
];
$works_status = $works_status ?? [
    1 => '진행·예정작',
    2 => '종료작',
];
$works_genre = $works_genre ?? [
    1 => '뮤지컬',
    2 => '연극',
    3 => '콘서트',
    4 => '이벤트',
    5 => '기타',
];
$is_active = $is_active ?? [
    1 => '활성화',
    0 => '비활성화',
];

// 기본 페이지네이션 변수
$perpage = 10;
$listCurPage = isset($_POST['page']) ? (int)$_POST['page'] : (isset($_GET['page']) ? (int)$_GET['page'] : 1);
$pageBlock = 2;
$count = ($listCurPage - 1) * $perpage;

// ========================= WHERE 조건 구성 =========================
$is_published_filter = $_GET['is_published'] ?? '';
$venue = $_GET['venue'] ?? '';
$genre = $_GET['genre'] ?? '';
$year = $_GET['year'] ?? '';
$searchColumn = $_GET['searchColumn'] ?? '';
$searchKeyword = $_GET['searchKeyword'] ?? '';

$where = "WHERE 1=1";
$params = [];

if ($is_published_filter !== '') {
    $where .= " AND w.is_published = :is_published";
    $params[':is_published'] = (int)$is_published_filter;
}

if (!empty($venue)) {
    $where .= " AND w.venue = :venue";
    $params[':venue'] = (int)$venue;
}

if (!empty($genre)) {
    $where .= " AND w.genre = :genre";
    $params[':genre'] = (int)$genre;
}

if (!empty($year)) {
    $where .= " AND YEAR(w.start_date) = :year";
    $params[':year'] = (int)$year;
}

if (!empty($searchColumn) && !empty($searchKeyword)) {
    $allowedColumns = [
        'title' => 'title',
        'subtitle' => 'subtitle',
        'note' => 'note',
        'content_html' => 'content_html',
    ];
    $resolvedSearchColumn = $allowedColumns[$searchColumn] ?? '';
    if ($resolvedSearchColumn !== '') {
        if ($resolvedSearchColumn === 'content_html') {
            $where .= " AND (w.title LIKE :searchKeyword OR w.subtitle LIKE :searchKeyword OR w.note LIKE :searchKeyword OR w.content_html LIKE :searchKeyword)";
        } else {
            $where .= " AND w.{$resolvedSearchColumn} LIKE :searchKeyword";
        }
        $params[':searchKeyword'] = "%{$searchKeyword}%";
    }
}
// ========================= END WHERE =========================

// ========================= 전체 개수 조회 =========================
$totalSql = "
    SELECT COUNT(*) 
    FROM nb_works w
    {$where}
";
$totalStmt = $db->prepare($totalSql);
$totalStmt->execute($params);
$totalCount = (int)$totalStmt->fetchColumn();
$Page = ceil($totalCount / $perpage);

// ========================= END 카운트 =========================

// ========================= 실제 데이터 조회 =========================
$sql = "
    SELECT w.id, w.title, w.subtitle, w.venue, w.genre, w.start_date, w.end_date, 
           w.is_published, w.sort_order, w.created_at, w.updated_at
    FROM nb_works w
    {$where}
    ORDER BY w.sort_order ASC, w.id DESC
    LIMIT :offset, :perpage
";

$stmt = $db->prepare($sql);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->bindValue(':offset', (int)$count, PDO::PARAM_INT);
$stmt->bindValue(':perpage', (int)$perpage, PDO::PARAM_INT);
$stmt->execute();
$worksRows = $stmt->fetchAll(PDO::FETCH_ASSOC);
// ========================= END =========================
?>

<!--=====================HEAD========================= -->
<?php include_once "../../inc/admin.head.php"; ?>

<body data-page="works">
    <div class="no-wrap">

        <!--=====================HEADER========================= -->
        <?php include_once "../../inc/admin.header.php"; ?>

        <main class="no-app no-container">

            <!--=====================DRAWER========================= -->
            <?php include_once "../../inc/admin.drawer.php"; ?>
            <?php $pageName = "WHAT'S ON"; ?>

            <form method="GET" name="frm" id="frm" autocomplete="off">
                <input type="hidden" name="mode" id="mode" value="list">

                <section class="no-content">
                    <div class="no-toolbar">
                        <div class="no-toolbar-container no-flex-stack">
                            <div class="no-page-indicator">
                                <h1 class="no-page-title"><?= $pageName ?></h1>
                                <div class="no-breadcrumb-container">
                                    <ul class="no-breadcrumb-list">
                                        <li class="no-breadcrumb-item"><span><?= $pageName ?></span></li>
                                        <li class="no-breadcrumb-item"><span><?= $pageName ?></span></li>
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

                                <!-- 공연장 선택 -->
                                <div class="no-admin-block">
                                    <h3 class="no-admin-title">공연장</h3>
                                    <div class="no-admin-content">
                                        <select name="venue" id="venue">
                                            <option value="">전체</option>
                                            <?php foreach ($works_venue as $code => $label): ?>
                                            <option value="<?= $code ?>"
                                                <?= ($venue ?? '') == $code ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($label) ?>
                                            </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>

                                <!-- 장르 선택 -->
                                <div class="no-admin-block">
                                    <h3 class="no-admin-title">장르</h3>
                                    <div class="no-admin-content">
                                        <select name="genre" id="genre">
                                            <option value="">전체</option>
                                            <?php foreach ($works_genre as $code => $label): ?>
                                            <option value="<?= $code ?>"
                                                <?= ($genre ?? '') == $code ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($label) ?>
                                            </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>

                                <!-- 연도 선택 -->
                                <div class="no-admin-block">
                                    <h3 class="no-admin-title">연도</h3>
                                    <div class="no-admin-content">
                                        <select name="year" id="year">
                                            <option value="">전체</option>
                                            <?php for ($y = date('Y'); $y >= 2007; $y--): ?>
                                            <option value="<?= $y ?>" <?= ($year ?? '') == $y ? 'selected' : '' ?>>
                                                <?= $y ?>
                                            </option>
                                            <?php endfor; ?>
                                        </select>
                                    </div>
                                </div>

                                <!-- 노출 여부 -->
                                <div class="no-admin-block">
                                    <h3 class="no-admin-title">노출 여부</h3>
                                    <div class="no-admin-content">
                                        <div class="no-radio-form no-list">
                                            <label for="is_published_all">
                                                <div class="no-radio-box">
                                                    <input type="radio" name="is_published" id="is_published_all" value=""
                                                        <?= $is_published_filter === '' ? 'checked' : '' ?>>
                                                    <span><i class="bx bx-radio-circle-marked"></i></span>
                                                </div>
                                                <span class="no-radio-text">전체</span>
                                            </label>
                                            <?php foreach ($is_active as $key => $label):
                                                $id = "is_published_$key";
                                                $checked = ($is_published_filter !== '' && $is_published_filter == $key) ? 'checked' : '';
                                            ?>
                                            <label for="<?= $id ?>">
                                                <div class="no-radio-box">
                                                    <input type="radio" name="is_published" id="<?= $id ?>"
                                                        value="<?= $key ?>" <?= $checked ?>>
                                                    <span><i class="bx bx-radio-circle-marked"></i></span>
                                                </div>
                                                <span class="no-radio-text"><?= htmlspecialchars($label) ?></span>
                                            </label>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                </div>

                                <!-- 검색어 -->
                                <div class="no-admin-block wide">
                                    <h3 class="no-admin-title">검색어</h3>
                                    <div class="no-search-select">
                                        <select name="searchColumn" id="searchColumn">
                                            <option value="">선택</option>
                                            <option value="title"
                                                <?= ($searchColumn ?? '') === 'title' ? 'selected' : '' ?>>제목
                                            </option>
                                            <option value="subtitle"
                                                <?= ($searchColumn ?? '') === 'subtitle' ? 'selected' : '' ?>>소제목
                                            </option>
                                            <option value="content_html"
                                                <?= ($searchColumn ?? '') === 'content_html' ? 'selected' : '' ?>>내용
                                            </option>
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
                                                <th>공연장</th>
                                                <th>장르</th>
                                                <th>공연기간</th>
                                                <th>진행현황</th>
                                                <th>순서변경</th>
                                                <th>노출</th>
                                                <th>정렬</th>
                                                <th>수정일</th>
                                                <th>관리</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (count($worksRows) > 0): ?>
                                            <?php foreach ($worksRows as $row): ?>
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
                                                <td>
                                                    <strong><?= htmlspecialchars($row['title']) ?></strong>
                                                    <?php if (!empty($row['subtitle'])): ?>
                                                    <br><small style="color: #888;"><?= htmlspecialchars($row['subtitle']) ?></small>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?= $works_venue[$row['venue']] ?? '-' ?></td>
                                                <td><?= $works_genre[$row['genre']] ?? '-' ?></td>
                                                <td>
                                                    <?php if ($row['start_date'] && $row['end_date']): ?>
                                                    <?= date('Y.m.d', strtotime($row['start_date'])) ?> ~ 
                                                    <?= date('Y.m.d', strtotime($row['end_date'])) ?>
                                                    <?php else: ?>
                                                    -
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php
                                                    $today = date('Y-m-d');
                                                    if (!empty($row['end_date']) && $row['end_date'] < $today) {
                                                        $statusCode = 3;
                                                        $statusClass = 'no-btn--normal';
                                                    } elseif (!empty($row['start_date']) && $row['start_date'] > $today) {
                                                        $statusCode = 2;
                                                        $statusClass = 'no-btn--main';
                                                    } else {
                                                        $statusCode = 1;
                                                        $statusClass = 'no-btn--notice';
                                                    }
                                                    $statusLabel = $works_status[$statusCode] ?? '-';
                                                    ?>
                                                    <span class="no-btn <?= $statusClass ?>">
                                                        <?= htmlspecialchars($statusLabel) ?>
                                                    </span>
                                                </td>
                                                <td class="sort-btn-group">
                                                    <button type="button" class="sort-btn" data-id="<?= $row['id'] ?>"
                                                        data-action="up" data-no="<?= $row['sort_order'] + 1 ?>">
                                                        <i class='bx bx-chevron-down'></i>
                                                    </button>
                                                    <button type="button" class="sort-btn" data-id="<?= $row['id'] ?>"
                                                        data-action="down" data-no="<?= $row['sort_order'] - 1 ?>">
                                                        <i class='bx bx-chevron-up'></i>
                                                    </button>
                                                    <button type="button" class="sort-btn" data-id="<?= $row['id'] ?>"
                                                        data-action="first" data-no="<?= $totalCount ?>">
                                                        <i class='bx bx-chevrons-down'></i>
                                                    </button>
                                                    <button type="button" class="sort-btn" data-id="<?= $row['id'] ?>"
                                                        data-action="last" data-no="1">
                                                        <i class='bx bx-chevrons-up'></i>
                                                    </button>
                                                </td>
                                                <td>
                                                    <span
                                                        class="no-btn <?= $row['is_published'] ? 'no-btn--notice' : 'no-btn--normal' ?>">
                                                        <?= htmlspecialchars($is_active[$row['is_published']] ?? '미정') ?>
                                                    </span>
                                                </td>
                                                <td><?= $row['sort_order'] ?></td>
                                                <td><?= substr($row['updated_at'], 0, 10) ?></td>
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
                                                <td colspan="11" style="text-align: center; color: #888;">등록된 공연이 없습니다.</td>
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
        // jQuery UI Selectmenu 초기화
        $('#venue, #genre, #year, #searchColumn').selectmenu();

        function getSelectedIds() {
            return $('.no-chk:checked').map(function() {
                return $(this).val();
            }).get();
        }

        $('#selectAllCheckbox').on('change', function() {
            $('.no-chk').prop('checked', $(this).prop('checked'));
        });

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

        $('.no-chk').on('change', function() {
            var totalCount = $('.no-chk').length;
            var checkedCount = $('.no-chk:checked').length;
            $('#selectAllCheckbox').prop('checked', totalCount > 0 && totalCount === checkedCount);
        });

        $('[data-action="deleteSelected"]').on('click', function(e) {
            e.preventDefault();

            var selectedIds = getSelectedIds();
            if (selectedIds.length === 0) {
                alert('Select items to delete.');
                return;
            }

            if (!confirm('Delete selected items?')) {
                return;
            }

            $.ajax({
                url: './process.php',
                type: 'POST',
                dataType: 'json',
                data: {
                    mode: 'delete_array',
                    ids: JSON.stringify(selectedIds)
                },
                success: function(response) {
                    if (response && response.success) {
                        alert(response.message || 'Deleted.');
                        location.reload();
                        return;
                    }

                    alert((response && response.message) || 'Delete failed.');
                },
                error: function() {
                    alert('Delete failed.');
                }
            });
        });

        $('.delete-btn').on('click', function() {
            var id = $(this).data('id');
            if (!id) {
                return;
            }

            if (!confirm('Delete this item?')) {
                return;
            }

            $.ajax({
                url: './process.php',
                type: 'POST',
                dataType: 'json',
                data: {
                    mode: 'delete',
                    id: id
                },
                success: function(response) {
                    if (response && response.success) {
                        alert(response.message || 'Deleted.');
                        location.reload();
                        return;
                    }

                    alert((response && response.message) || 'Delete failed.');
                },
                error: function() {
                    alert('Delete failed.');
                }
            });
        });

        $('.sort-btn').on('click', function() {
            var id = $(this).data('id');
            var targetNo = parseInt($(this).data('no'), 10);

            if (!id || isNaN(targetNo) || targetNo <= 0) {
                alert('Invalid sort value.');
                return;
            }

            $.ajax({
                url: './process.php',
                type: 'POST',
                dataType: 'json',
                data: {
                    mode: 'sort',
                    id: id,
                    new_no: targetNo
                },
                success: function(response) {
                    if (response && response.success) {
                        location.reload();
                        return;
                    }

                    alert((response && response.message) || 'Sort update failed.');
                },
                error: function() {
                    alert('Sort update failed.');
                }
            });
        });
    });
    </script>

