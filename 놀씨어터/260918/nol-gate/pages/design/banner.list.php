<?php
include_once "../../../inc/lib/base.class.php";

$db = DB::getInstance();

// 페이지네이션 설정
$perpage = 10;
$listCurPage = isset($_POST['page']) ? (int)$_POST['page'] : (isset($_GET['page']) ? (int)$_GET['page'] : 1);
$pageBlock = 2;
$count = ($listCurPage - 1) * $perpage; // LIMIT offset

// GET 파라미터 처리
$banner_type       = $_GET['banner_type'] ?? '';
$active_filter     = $_GET['is_active'] ?? '';
$searchKeyword     = $_GET['searchKeyword'] ?? '';
$display_start_at  = $_GET['display_start_at'] ?? '';
$display_end_at    = $_GET['display_end_at'] ?? '';

// WHERE 조건 구성
$where  = "WHERE 1=1";
$params = [];

if (!empty($banner_type)) {
    $where .= " AND b.banner_type = :banner_type";
    $params[':banner_type'] = $banner_type;
}

if ($active_filter !== '') {
    $where .= " AND b.is_active = :is_active";
    $params[':is_active'] = (int)$active_filter;
}

if (!empty($display_start_at)) {
    $where .= " AND (b.display_end_at IS NULL OR b.display_end_at >= :display_start_at)";
    $params[':display_start_at'] = $display_start_at;
}

if (!empty($display_end_at)) {
    $where .= " AND (b.display_start_at IS NULL OR b.display_start_at <= :display_end_at)";
    $params[':display_end_at'] = $display_end_at;
}

if (!empty($searchKeyword)) {
    $where .= " AND b.title LIKE :searchKeyword";
    $params[':searchKeyword'] = "%{$searchKeyword}%";
}

// 전체 개수 조회
$totalSql = "
    SELECT COUNT(*) 
    FROM nb_banners b
    $where
";
$totalStmt = $db->prepare($totalSql);
$totalStmt->execute($params);
$totalCount = (int)$totalStmt->fetchColumn();
$Page = ceil($totalCount / $perpage);

// 실제 데이터 조회
$sql = "
    SELECT b.*
    FROM nb_banners b
    $where
    ORDER BY b.sort_no ASC, b.id DESC
    LIMIT {$count}, {$perpage}
";
$stmt = $db->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>



<?php include_once "../../inc/admin.head.php"; ?>

<body data-page="banner">
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
                                <a href="./banner.new.php" class="no-btn no-btn--main no-btn--big"> <?= $pageName ?> 생성
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


                                <!-- 배너 위치 -->
                                <div class="no-admin-block">
                                    <h3 class="no-admin-title">배너 위치</h3>
                                    <div class="no-admin-content">
                                        <select name="banner_type" id="banner_type">
                                            <option value="">전체</option>
                                            <?php foreach ($banner_types as $code => $label): ?>
                                                <option value="<?= $code ?>"
                                                    <?= ($banner_type == $code) ? 'selected' : '' ?>>
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

                                <!-- 등록기간 -->
                                <div class="no-admin-block">
                                    <h3 class="no-admin-title">등록기간</h3>
                                    <div class="no-admin-content no-admin-date">
                                        <input type="text" name="display_start_at" id="display_start_at"
                                            value="<?= isset($display_start_at) ? htmlspecialchars($display_start_at) : '' ?>" />
                                        <span></span>
                                        <input type="text" name="display_end_at" id="display_end_at"
                                            value="<?= isset($display_end_at) ? htmlspecialchars($display_end_at) : '' ?>" />
                                    </div>
                                </div>
                                <!-- 검색어 -->
                                <div class="no-admin-block wide">
                                    <h3 class="no-admin-title">배너명</h3>
                                    <div class="no-search-wrap ">
                                        <div class="no-search-input">
                                            <i class="bx bx-search-alt-2"></i>
                                            <input type="text" name="searchKeyword" id="searchKeyword"
                                                placeholder="배너명을 입력하세요"
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
                                    <ul class="no-table-check-control">
                                        <li>
                                            <a href="#" class="no-btn no-btn--sm no-btn--check active "
                                                data-action="selectAll">전체선택</a>
                                        </li>
                                        <li><a href="#" class="no-btn no-btn--sm" data-action="deselectAll">선택해제</a>
                                        </li>
                                        <?php if ($role->canDelete()): ?>
                                        <li><a href="#" class="no-btn no-btn--sm" data-action="deleteSelected">선택삭제</a>
                                        </li>
                                        <?php endif; ?>
                                    </ul>
                                    <span>총 <?= $totalCount ?>개</span>
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
                                                <th>배너명</th>
                                                <th>썸네일</th>
                                                <th>배너 타입</th>
                                                <th>공연일자</th>
                                                <th>등록기간</th>
                                                <th>정렬</th>
                                                <th>순서변경</th>
                                                <th>노출 여부</th>
                                                <th>관리</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (count($rows) > 0): ?>
                                                <?php $no = $count + 1; ?>
                                                <?php foreach ($rows as $row):  ?>
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

                                                        <td><?= htmlspecialchars($row['title']) ?></td>

                                                        <!-- 썸네일 이미지 -->
                                                        <td>
                                                            <?php if (!empty($row['banner_image'])): ?>
                                                                <img src="/uploads/banners/<?= $row['banner_image'] ?>" alt="썸네일"
                                                                    style="max-width: 60px;">
                                                            <?php else: ?>
                                                                <span style="color: #aaa;">-</span>
                                                            <?php endif; ?>
                                                        </td>

                                                        <!-- 배너 타입 -->
                                                        <td><?= htmlspecialchars($banner_types[$row['banner_type']] ?? '-') ?>
                                                        </td>

                                                        <!-- 공연일자 -->
                                                        <td><?= htmlspecialchars($row['start_at'] ?? '') ?> ~
                                                            <?= htmlspecialchars($row['end_at'] ?? '') ?></td>

                                                        <!-- 등록기간 -->
                                                        <td>
                                                            <?php if ((int)($row['is_unlimited'] ?? 0) === 1): ?>
                                                                <span class="no-btn no-btn--notice">무기한</span>
                                                            <?php elseif (!empty($row['display_start_at']) && !empty($row['display_end_at'])): ?>
                                                                <?= htmlspecialchars($row['display_start_at']) ?> ~
                                                                <?= htmlspecialchars($row['display_end_at']) ?>
                                                            <?php else: ?>
                                                                <span style="color: #aaa;">-</span>
                                                            <?php endif; ?>
                                                        </td>


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

                                                        <td><?= $no ?></td>
                                                        <!-- 노출 여부 -->
                                                        <td>
                                                            <span
                                                                class="no-btn <?= $row['is_active'] ? 'no-btn--notice' : 'no-btn--normal' ?>">
                                                                <?= htmlspecialchars($is_active[$row['is_active']] ?? '미정') ?>
                                                            </span>
                                                        </td>


                                                        <td>
                                                            <div class="no-table-role">
                                                                <span class="no-role-btn"><i
                                                                        class="bx bx-dots-vertical-rounded"></i></span>
                                                                <div class="no-table-action">
                                                                    <a href="banner.edit.php?id=<?= $row['id'] ?>"
                                                                        class="no-btn no-btn--sm no-btn--normal">보기</a>
                                                                    <?php if (!$role->isReadOnly()): ?>
                                                                    <a href="banner.edit.php?id=<?= $row['id'] ?>"
                                                                        class="no-btn no-btn--sm no-btn--normal">수정</a>
                                                                    <?php endif; ?>
                                                                    <?php if ($role->canDelete()): ?>
                                                                    <button type="button"
                                                                        class="no-btn no-btn--sm no-btn--delete-outline delete-btn"
                                                                        data-id="<?= $row['id'] ?>">삭제</button>
                                                                    <?php endif; ?>
                                                                </div>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                    <?php $no++; ?>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <tr>
                                                    <td colspan="10" style="text-align: center; color: #888;">등록된 배너가 없습니다.
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