<?php
include_once "../../../inc/lib/base.class.php";

$db = DB::getInstance();

// 페이지네이션 설정
$perpage = 10;
$listCurPage = isset($_POST['page']) ? (int)$_POST['page'] : (isset($_GET['page']) ? (int)$_GET['page'] : 1);
$pageBlock = 2;
$count = ($listCurPage - 1) * $perpage;

// GET 파라미터 처리
$active_filter = $_GET['is_active']    ?? '';
$searchKeyword = $_GET['searchKeyword'] ?? '';
$start_at      = $_GET['start_at']     ?? '';
$end_at        = $_GET['end_at']       ?? '';

// WHERE 조건 구성
$where = "WHERE 1=1";
$params = [];

if ($active_filter !== '') {
    $where .= " AND p.is_active = :is_active";
    $params[':is_active'] = (int)$active_filter;
}

if (!empty($start_at)) {
    $where .= " AND (p.end_at IS NULL OR p.end_at >= :start_at)";
    $params[':start_at'] = $start_at;
}

if (!empty($end_at)) {
    $where .= " AND (p.start_at IS NULL OR p.start_at <= :end_at)";
    $params[':end_at'] = $end_at;
}

if (!empty($searchKeyword)) {
    $where .= " AND p.title LIKE :searchKeyword";
    $params[':searchKeyword'] = '%' . $searchKeyword . '%';
}

// 전체 개수 조회
$totalSql = "
    SELECT COUNT(*) 
    FROM nb_popups p
    $where
";
$totalStmt = $db->prepare($totalSql);
$totalStmt->execute($params);
$totalCount = (int)$totalStmt->fetchColumn();
$Page = ceil($totalCount / $perpage);

// 실제 데이터 조회
$sql = "
    SELECT p.*
    FROM nb_popups p
    $where
    ORDER BY p.sort_no ASC, p.id DESC
    LIMIT {$count}, {$perpage}
";
$stmt = $db->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>



<?php include_once "../../inc/admin.head.php"; ?>

<body data-page="popup">
    <div class="no-wrap">
        <?php include_once "../../inc/admin.header.php"; ?>
        <main class="no-app no-container">
            <?php include_once "../../inc/admin.drawer.php"; ?>
            <?php $breadcrumbs = [$pageName]; ?>

            <form method="GET" name="frm" id="frm" autocomplete="off">
                <input type="hidden" name="mode" id="mode" value="list">

                <section class="no-content">
                    <div class="no-toolbar">
                        <div class="no-toolbar-container no-flex-stack">
                            <div class="no-page-indicator">
                                <h1 class="no-page-title"><?= $pageName ?></h1>
                                <div class="no-breadcrumb-container">
                                    <ul class="no-breadcrumb-list">
                                        <?php foreach ($breadcrumbs as $crumb): ?>
                                        <?php if (trim($crumb) !== ''): ?>
                                        <li class="no-breadcrumb-item"><span><?= htmlspecialchars($crumb) ?></span></li>
                                        <?php endif; ?>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            </div>
                            <?php if ($role->canCreate()): ?>
                            <div class="no-items-center">
                                <a href="./popup.new.php" class="no-btn no-btn--main no-btn--big"> <?= $pageName ?> 생성
                                </a>
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

                                <!-- 노출 기간 -->
                                <div class="no-admin-block">
                                    <h3 class="no-admin-title">노출 기간</h3>
                                    <div class="no-admin-content no-admin-date">
                                        <input type="text" name="start_at" id="start_at"
                                            value="<?= isset($start_at) ? htmlspecialchars($start_at) : '' ?>" />
                                        <span></span>
                                        <input type="text" name="end_at" id="end_at"
                                            value="<?= isset($end_at) ? htmlspecialchars($end_at) : '' ?>" />
                                    </div>
                                </div>


                                <!-- 검색어 -->
                                <div class="no-admin-block wide">
                                    <h3 class="no-admin-title">팝업명</h3>
                                    <div class="no-search-wrap ">
                                        <div class="no-search-input">
                                            <i class="bx bx-search-alt-2"></i>
                                            <input type="text" name="searchKeyword" id="searchKeyword"
                                                placeholder="팝업명을 입력하세요"
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


                    <!-- 리스트 -->
                    <div class="no-content-container">
                        <div class="no-card">
                            <div class="no-card-header">
                                <h2 class="no-card-title"><?= $pageName ?> 리스트</h2>
                            </div>

                            <div class="no-card-body">

                                <div class="no-table-option">
                                    <?php if($role->canDelete()) : ?>
                                    <ul class="no-table-check-control">
                                        <ul class="no-table-check-control">
                                            <li><a href="#" class="no-btn no-btn--sm no-btn--check active "
                                                    data-action="selectAll">전체선택</a>
                                            </li>
                                            <li><a href="#" class="no-btn no-btn--sm" data-action="deselectAll">선택해제</a>
                                            </li>
                                            <li><a href="#" class="no-btn no-btn--sm"
                                                    data-action="deleteSelected">선택삭제</a></li>
                                        </ul>
                                    </ul>
                                    <?php endif;?>
                                    <span>총 <?=$totalCount?>개</span>
                                </div>


                                <div class="no-table-responsive">

                                    <table class="no-table">
                                        <thead>
                                            <tr>
                                                <?php if($role->canDelete()) : ?>
                                                <th class="no-width-25 no-check">
                                                    <div class="no-checkbox-form">
                                                        <label>
                                                            <input type="checkbox" id="selectAllCheckbox" />
                                                            <span><i class="bx bxs-check-square"></i></span>
                                                        </label>
                                                    </div>
                                                </th>
                                                <?php endif;?>
                                                <th>팝업명</th>
                                                <th>썸네일</th>
                                                <th>팝업 위치</th>
                                                <th>노출 기간</th>
                                                <th>순서변경</th>
                                                <th>정렬</th>
                                                <th>노출 여부</th>
                                                <th>관리</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (count($rows) > 0): ?>
                                            <?php foreach ($rows as $row): ?>
                                            <tr>
                                                <?php if($role->canDelete()) :?>
                                                <td class="no-check">
                                                    <div class="no-checkbox-form">
                                                        <label>
                                                            <input type="checkbox" class="no-chk"
                                                                value="<?= $row['id'] ?>">
                                                            <span><i class="bx bxs-check-square"></i></span>
                                                        </label>
                                                    </div>
                                                </td>
                                                <?php endif ;?>
                                                <td><?= htmlspecialchars($row['title']) ?></td>

                                                <!-- 썸네일 이미지 -->
                                                <td>
                                                    <?php if (!empty($row['popup_image'])): ?>
                                                    <img src="/uploads/popups/<?= $row['popup_image'] ?>" alt="썸네일"
                                                        style="max-width: 60px;">
                                                    <?php else: ?>
                                                    <span style="color: #aaa;">-</span>
                                                    <?php endif; ?>
                                                </td>

                                                <!-- 팝업 위치 -->
                                                <td><?= htmlspecialchars($row['popup_path'] ?? '-') ?>
                                                </td>

                                                <!-- 시작일~종료일 -->
                                                <td><?= htmlspecialchars($row['start_at']) ?> ~
                                                    <?= htmlspecialchars($row['end_at']) ?></td>


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
                                                <td><?= $row['sort_no'] ?></td>
                                                <!-- 노출 여부 -->
                                                <td>
                                                    <span
                                                        class="no-btn <?= $row['is_active'] ? 'no-btn--notice' : 'no-btn--normal' ?>">
                                                        <?= htmlspecialchars($is_active[$row['is_active']] ?? '미정') ?>
                                                    </span>
                                                </td>

                                                <!-- 관리 -->
                                                <td>
                                                    <div class="no-table-role">
                                                        <span class="no-role-btn"><i
                                                                class="bx bx-dots-vertical-rounded"></i></span>
                                                        <div class="no-table-action">
                                                            <a href="popup.edit.php?id=<?= $row['id'] ?>"
                                                                class="no-btn no-btn--sm no-btn--normal">보기</a>
                                                            <?php if (!$role->isReadOnly()): ?>
                                                            <a href="popup.edit.php?id=<?= $row['id'] ?>"
                                                                class="no-btn no-btn--sm no-btn--normal">수정</a>
                                                            <?php endif; ?>
                                                            <?php if($role->canDelete() ) : ?>
                                                            <button type="button"
                                                                class="no-btn no-btn--sm no-btn--delete-outline delete-btn"
                                                                data-id="<?= $row['id'] ?>">삭제</button>
                                                            <?php endif;?>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                            <?php else: ?>
                                            <tr>
                                                <td colspan="9" style="text-align: center; color: #888;">등록된 팝업이 없습니다.
                                                </td>
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