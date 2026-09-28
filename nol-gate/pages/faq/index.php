<?php
include_once "../../../inc/lib/base.class.php";

$db = DB::getInstance();

// 기본 페이지네이션 변수
$perpage = 10;
$listCurPage = isset($_POST['page']) ? (int)$_POST['page'] : (isset($_GET['page']) ? (int)$_GET['page'] : 1);
$pageBlock = 2;
$count = ($listCurPage - 1) * $perpage;


// ========================= WHERE 조건 구성 =========================
$active_filter = $_GET['is_active'] ?? '';
$categories = $_GET['categories'] ?? '';
$searchColumn = $_GET['searchColumn'] ?? '';
$searchKeyword = $_GET['searchKeyword'] ?? '';

$where = "WHERE 1=1";
$params = [];


if ($active_filter !== '') {
    $where .= " AND f.is_active = :is_active";
    $params[':is_active'] = (int)$active_filter;
}

if (!empty($categories)) {
    $where .= " AND f.categories = :categories";
    $params[':categories'] = (int)$categories;
}

if (!empty($searchColumn) && !empty($searchKeyword)) {
    $allowedColumns = [
        'question' => 'question',
        'answer' => 'answer',
    ];
    $resolvedSearchColumn = $allowedColumns[$searchColumn] ?? '';
    if ($resolvedSearchColumn !== '') {
        $where .= " AND f.{$resolvedSearchColumn} LIKE :searchKeyword";
        $params[':searchKeyword'] = "%{$searchKeyword}%";
    }
}
// ========================= END WHERE =========================


// ========================= 전체 개수 조회 =========================
$totalSql = "
    SELECT COUNT(*) 
    FROM nb_faqs f
    {$where}
";
$totalStmt = $db->prepare($totalSql);
$totalStmt->execute($params);
$totalCount = (int)$totalStmt->fetchColumn();
$Page = ceil($totalCount / $perpage);

// ========================= END 카운트 =========================


// ========================= 실제 데이터 조회 =========================
$sql = "
    SELECT f.id, f.categories, f.question, f.sort_no, f.is_active, f.updated_at
    FROM nb_faqs f
    {$where}
    ORDER BY f.sort_no ASC
    LIMIT :offset, :perpage
";

$stmt = $db->prepare($sql);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->bindValue(':offset', (int)$count, PDO::PARAM_INT);
$stmt->bindValue(':perpage', (int)$perpage, PDO::PARAM_INT);
$stmt->execute();
$faqRows = $stmt->fetchAll(PDO::FETCH_ASSOC);
// ========================= END =========================
?>


<!--=====================HEAD========================= -->
<?php include_once "../../inc/admin.head.php"; ?>

<body data-page="faq">
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


                                <!-- 카테고리 선택 -->
                                <div class="no-admin-block">
                                    <h3 class="no-admin-title">카테고리</h3>
                                    <div class="no-admin-content">
                                        <select name="categories" id="categories">
                                            <option value="">전체</option>
                                            <?php foreach ($faq_categories as $code => $label): ?>
                                            <option value="<?= $code ?>"
                                                <?= ($categories ?? '') == $code ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($label) ?>
                                            </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>

                                <div class="no-admin-block">
                                    <h3 class="no-admin-title">노출 여부</h3>
                                    <div class="no-admin-content">
                                        <div class="no-radio-form no-list">
                                            <!-- 전체 옵션 수동 추가 -->
                                            <label for="is_active_all">
                                                <div class="no-radio-box">
                                                    <input type="radio" name="is_active" id="is_active_all" value=""
                                                        <?= $active_filter === '' ? 'checked' : '' ?>>
                                                    <span><i class="bx bx-radio-circle-marked"></i></span>
                                                </div>
                                                <span class="no-radio-text">전체</span>
                                            </label>

                                            <!-- $is_active 반복 -->
                                            <?php foreach ($is_active as $key => $label):
                                                $id = "is_active_$key";
                                                $checked = ($active_filter !== '' && $active_filter == $key) ? 'checked' : '';
                                            ?>
                                            <label for="<?= $id ?>">
                                                <div class="no-radio-box">
                                                    <input type="radio" name="is_active" id="<?= $id ?>"
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
                                            <option value="question"
                                                <?= ($searchColumn ?? '') === 'question' ? 'selected' : '' ?>>질문
                                            </option>
                                            <option value="answer"
                                                <?= ($searchColumn ?? '') === 'answer' ? 'selected' : '' ?>>답변</option>
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
                                                <th>카테고리</th>
                                                <th>질문</th>
                                                <th>순서변경</th>
                                                <th>노출</th>
                                                <th>정렬</th>
                                                <th>수정일</th>
                                                <th>관리</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (count($faqRows) > 0): ?>
                                            <?php foreach ($faqRows as $row): ?>
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
                                                <td><?= $faq_categories[$row['categories']] ?? '기타' ?></td>
                                                <td><?= htmlspecialchars($row['question']) ?></td>

                                                <td class="sort-btn-group">
                                                    <!-- sort_no가 클수록 위니까 버튼 누르면 sort_no를 크게/작게 조절 -->
                                                    <button type="button" class="sort-btn" data-id="<?= $row['id'] ?>"
                                                        data-action="up" data-no="<?= $row['sort_no'] + 1 ?>">
                                                        <i class='bx bx-chevron-down'></i>
                                                    </button>
                                                    <button type="button" class="sort-btn" data-id="<?= $row['id'] ?>"
                                                        data-action="down" data-no="<?= $row['sort_no'] - 1 ?>">
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
                                                        class="no-btn <?= $row['is_active'] ? 'no-btn--notice' : 'no-btn--normal' ?>">
                                                        <?= htmlspecialchars($is_active[$row['is_active']] ?? '미정') ?>
                                                    </span>
                                                </td>
                                                <td><?= $row['sort_no'] ?></td>
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
                                                <td colspan="8" style="text-align: center; color: #888;">FAQ가 등록되지
                                                    않았습니다.</td>
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
