<!DOCTYPE html>
<html lang="ko">
<?php

header("Cache-Control: no-cache, must-revalidate"); // HTTP 1.1
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // 과거 날짜로 설정하여 캐시 무효화
header("Pragma: no-cache"); // HTTP 1.0



include_once "../../../inc/lib/base.class.php";

$depthnum = 6;
$pagenum = 1;


$page = $_GET['page'] ?? 1; // 페이지 기본값을 1로 설정
$perpage = $_GET['perpage'] ?? 20; // 페이지당 항목 수 기본값을 20으로 설정

// 페이지 유효성 확인
$page = max(1, (int)$page);
$perpage = max(1, (int)$perpage);


$max_year_data = DB::query("SELECT MAX(YEAR(end_date)) as max_year FROM nb_works");
$min_year_data = DB::query("SELECT MIN(YEAR(end_date)) as min_year FROM nb_works");

$max_year = $max_year_data[0]['max_year'] ?? date('Y'); 
$min_year = $min_year_data[0]['min_year'] ?? $max_year;


$filters = ['search', 'genre', 'is_featured', 'year', 'place'];
$filterValues = [];
$conditions = [];
$params = [];

foreach ($filters as $k) {
    if (isset($_GET[$k]) && !empty($_GET[$k])) {
        $filterValues[$k] = trim($_GET[$k]);
    }
}

if (isset($filterValues['search'])) {
    $conditions[] = "title LIKE :search";
    $params[':search'] = '%' . $filterValues['search'] . '%';
}

if (isset($_GET['year']) && !empty($_GET['year'])) {
    $filterValues['year'] = trim($_GET['year']);
    $conditions[] = "YEAR(end_date) = :year";
    $params[':year'] = $filterValues['year'];
}

if (isset($_GET['place']) && !empty($_GET['place'])) {
    $filterValues['place'] = trim($_GET['place']);
    $conditions[] = "place = :place";
    $params[':place'] = $filterValues['place'];
}


if (isset($_GET['genre']) && $_GET['genre'] !== '') { 
    $filterValues['genre'] = trim($_GET['genre']);
    $conditions[] = "genre = :genre";
    $params[':genre'] = $filterValues['genre'];
}

// 총 게시글 수 계산
$totalSql = "SELECT COUNT(*) as total FROM nb_works";
if (!empty($conditions)) {
    $totalSql .= " WHERE " . implode(' AND ', $conditions);
}
$totalCount = DB::query($totalSql, $params)[0]['total'] ?? 0;

// 페이지네이션 데이터 계산
$totalPages = (int)ceil($totalCount / $perpage);
$hasPrev = $page > 1;
$hasNext = $page < $totalPages;

// 현재 페이지 블록 계산 (1~5, 6~10 등)
$blockSize = 5;
$currentBlock = (int)ceil($page / $blockSize);
$startPage = ($currentBlock - 1) * $blockSize + 1;
$endPage = min($startPage + $blockSize - 1, $totalPages);

// OFFSET 및 LIMIT 설정
$offset = ($page - 1) * $perpage;

// 결과 조회 쿼리
$resultSql = "SELECT * FROM nb_works";
if (!empty($conditions)) {
    $resultSql .= " WHERE " . implode(' AND ', $conditions);
}
$resultSql .= " ORDER BY is_featured DESC, created_at DESC LIMIT $perpage OFFSET $offset";

// 쿼리 실행
$result = DB::query($resultSql, $params) ?? [];

// 현재 쿼리스트링 생성
$queryString = http_build_query(array_merge($_GET, ['page' => 1]));
// 페이지네이션 HTML 생성
$html = '<div class="no-pagination"><ul class="no-page-list">';


// 처음으로 가기 버튼
if ($page > 1) {
    $html .= '<li class="no-page-item"><a href="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?' . $queryString . '" class="no-page-link" onClick="goListMove(1, \'' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '\');"><i class="bx bx-chevrons-left"></i></a></li>';
} else {
    $html .= '<li class="no-page-item"><a href="javascript:void(0);" class="no-page-link"><i class="bx bx-chevrons-left"></i></a></li>';
}

// 이전 페이지 버튼
if ($hasPrev) {
    $prevPage = $page - 1;
    $queryString = http_build_query(array_merge($_GET, ['page' => $prevPage]));
    $html .= '<li class="no-page-item"><a href="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?' . $queryString . '" class="no-page-link" onClick="goListMove(' . $prevPage . ', \'' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '\');"><i class="bx bx-chevron-left"></i></a></li>';
} else {
    $html .= '<li class="no-page-item"><a href="javascript:void(0);" class="no-page-link"><i class="bx bx-chevron-left"></i></a></li>';
}

// 페이지 번호 버튼
for ($x = $startPage; $x <= $endPage; $x++) {
    $queryString = http_build_query(array_merge($_GET, ['page' => $x]));
    if ($x == $page) {
        $html .= '<li class="no-page-item"><a href="javascript:void(0);" class="no-page-link active">' . $x . '</a></li>';
    } else {
        $html .= '<li class="no-page-item"><a href="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?' . $queryString . '" onClick="goListMove(' . $x . ', \'' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '\');" class="no-page-link">' . $x . '</a></li>';
    }
}

// 다음 페이지 버튼
if ($hasNext) {
    $nextPage = $page + 1;
    $queryString = http_build_query(array_merge($_GET, ['page' => $nextPage]));
    $html .= '<li class="no-page-item"><a href="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?' . $queryString . '" class="no-page-link" onClick="goListMove(' . $nextPage . ', \'' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '\');"><i class="bx bx-chevron-right"></i></a></li>';
} else {
    $html .= '<li class="no-page-item"><a href="javascript:void(0);" class="no-page-link"><i class="bx bx-chevron-right"></i></a></li>';
}

// 마지막으로 가기 버튼
if ($page < $totalPages) {
    $queryString = http_build_query(array_merge($_GET, ['page' => $totalPages]));
    $html .= '<li class="no-page-item"><a href="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?' . $queryString . '" class="no-page-link" onClick="goListMove(' . $totalPages . ', \'' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '\');"><i class="bx bx-chevrons-right"></i></a></li>';
} else {
    $html .= '<li class="no-page-item"><a href="javascript:void(0);" class="no-page-link"><i class="bx bx-chevrons-right"></i></a></li>';
}

$html .= '</ul></div>';

$pagingItemCount = $totalCount - $offset; 

// 결과 출력
$data = [
    'items' => $result, // 현재 페이지의 항목
    'pagination' => [
        'current_page' => $page,
        'per_page' => $perpage,
        'total_count' => $totalCount,
        'total_pages' => $totalPages,
        'has_prev' => $hasPrev,
        'has_next' => $hasNext,
    ],
    'html' => $html, // 페이지네이션 HTML
];

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
            <form  id="search-form" autocomplete="off">

                <section class="no-content">
                    <div class="no-toolbar">
                        <div class="no-toolbar-container no-flex-stack">
                            <div class="no-page-indicator">
                                <h1 class="no-page-title">공연 관리</h1>
                                <div class="no-breadcrumb-container">
                                    <ul class="no-breadcrumb-list">
                                        <li class="no-breadcrumb-item"><span>What's ON</span></li>
                                        <li class="no-breadcrumb-item"><span>공연 게시글 관리</span></li>
                                    </ul>
                                </div>
                            </div>
                            <div class="no-items-center">
                                <a href="./new.php" class="no-btn no-btn--main no-btn--big"> 공연 등록 </a>
                            </div>
                        </div>
                    </div>
					

					<!-- Search -->
					<div class="no-search no-toolbar-container">
						<div class="no-card">
							<div class="no-card-header">
								<h2 class="no-card-title">공연 검색</h2>
							</div>
							<div class="no-card-body no-admin-column">
								<!-- 검색어 -->
								<div class="no-admin-block wide">
									<h3 class="no-admin-title">검색어</h3>
									<div class="no-search-wrap">
										<div class="no-search-input">
											<i class="bx bx-search-alt-2"></i>
											<input 
												type="text" 
												name="search" 
												id="search" 
												title="검색어 입력" 
												placeholder="검색어를 입력해주세요." 
												value="<?php echo isset($filterValues['search']) ? htmlspecialchars($filterValues['search']) : ''; ?>" 
											/>
										</div>
										<div style="margin-left:1rem">
											<button 
												type="submit" 
												class="no-btn no-btn--main no-btn--search" 
												title="검색" 
												onClick="doSearchList();">
												검색
											</button>
										</div>
									</div>
								</div>
								<!-- 장르 선택 -->
								<div class="no-admin-filters">
									<div class="no-admin-block">
										<h3 class="no-admin-title">장르 선택</h3>
										<div class="no-admin-content">
											<select name="genre" id="genre">
												<option value="">전체</option>
												<?php foreach ($genres as $key => $value): ?>
													<option 
														value="<?php echo $key; ?>" 
														<?php echo (isset($filterValues['genre']) && (int)$filterValues['genre'] == (int)$key) ? 'selected' : ''; ?>>
														<?php echo htmlspecialchars($value); ?>
													</option>
												<?php endforeach; ?>
											</select>
										</div>
									</div>
									<!-- 배너 선택 -->

									<div class="no-admin-block">
										<h3 class="no-admin-title">년도 선택</h3>
										<div class="no-admin-content">
											<select name="year" id="year">
												<option value="">전체</option>
												<?php for ($y = $max_year; $y >= $min_year; $y--): ?>
													<option value="<?= $y ?>" <?= (isset($filterValues['year']) && $filterValues['year'] == $y) ? 'selected' : '' ?>>
														<?= $y ?>
													</option>
												<?php endfor; ?>
											</select>
										</div>
									</div>


									<div class="no-admin-block">
										<h3 class="no-admin-title">공연장소 선택</h3>
										<div class="no-admin-content">
											<select name="place" id="place">
												<option value="">전체</option>
												<?php foreach ($places as $key => $value): ?>
													<option value="<?= $key ?>" <?= (isset($filterValues['place']) && $filterValues['place'] == $key) ? 'selected' : '' ?>>
														<?= htmlspecialchars($value) ?>
													</option>
												<?php endforeach; ?>
											</select>
										</div>
									</div>


									<div class="no-admin-block">
										<h3 class="no-admin-title">배너 선택</h3>
										<div class="no-admin-content">
											<select name="is_featured" id="is_featured">
												<option value="">전체</option>
												<?php foreach ($is_banner as $key => $value): ?>
													<option 
														value="<?php echo $key; ?>" 
														<?php echo (isset($filterValues['is_featured']) && $filterValues['is_featured'] == $key) ? 'selected' : ''; ?>>
														<?php echo htmlspecialchars($value); ?>
													</option>
												<?php endforeach; ?>
											</select>
										</div>
									</div>
									<!-- 검색 버튼 -->
								</div>
							</div>
						</div>
					</div>

					
                    <!-- Contents -->
                    <div class="no-content-container">
                        <div class="no-card">
                            <div class="no-card-header">
                                <h2 class="no-card-title">공연 관리</h2>
                            </div>
                            <div class="no-card-body">
								<div class="no-table-option">
                                    <!-- <ul class="no-table-check-control">
                                        <li>
                                            <button type="button" class="no-btn no-btn--sm no-btn--check active" id="check-all-btn">전체선택</button>
                                        </li>
                                        <li>
                                            <button type="button" class="no-btn no-btn--sm no-btn--check" id="uncheck-all-btn">선택해제</button>
                                        </li>
                                        <li>
                                            <button type="button" class="no-btn no-btn--sm no-btn--check" id="check-delete-btn">선택삭제</button>
                                        </li>
                                    </ul> -->

                                    <div class="no-perpage">
                                        <select name="perpage" id="perpage">
                                            <option value="20" <?php if ($perpage == "20") echo "selected"; ?>>20개씩</option>
                                            <option value="50" <?php if ($perpage == "50") echo "selected"; ?>>50개씩</option>
                                            <option value="100" <?php if ($perpage == "100") echo "selected"; ?>>100개씩</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="no-table-responsive">
                                    <table class="no-table">
                                        <thead>
                                            <tr>
												<!-- <th scope="col" class="no-width-25 no-check">
												                                                    <div class="no-checkbox-form">
												                                                        <label for="chkAll">
												                                                            <input type="checkbox" id="chkAll" class="no-chk" />
												                                                            <span>
												                                                                <i class="bx bxs-check-square"></i>
												                                                            </span>
												                                                        </label>
												                                                    </div>
												                                                </th> -->
                                                <th>번호</th>
                                                <th>배너선택</th>
                                                <th>장르</th>
                                                <th>공연 장소</th>
												<th>썸네일</th>
                                                <th>제목</th>
                                                <th>작성일</th>
                                                <th>관리</th>
                                            </tr>
                                        </thead>
										<tbody>
											<?php foreach ($data['items'] as $v): 
											$chkId = 'work-chk-'.$v['id'];
											$currentQueryString = http_build_query(array_merge($_GET, ['id' => $v['id']]));
											?>
												<tr>
													<!-- <td class="no-check">
														<div class="no-checkbox-form">
															<label for="<?=$chkId?>">
																<input type="checkbox" class="no-chk no-chk-item" id="<?=$chkId?>" value="<?= $v['id'] ?>">
																<span>
																	<i class="bx bxs-check-square"></i>
																</span>
															</label>
														</div>
													</td> -->
													<td><?= $pagingItemCount-- ?></td>
													<td>
														<?php if ($v['is_featured'] == 1): ?>
															<span class='no-btn no-btn--notice'>대표</span>
														<?php else: ?>
															-
														<?php endif; ?>
													</td>
													<td><?= isset($genres[$v['genre']]) ? $genres[$v['genre']] : "-" ?></td>
													<td>
														<?= getPlaceDisplayName($v['place'], $v['start_date'] ?? null) ?>
													</td>
													<td>
														<?php if($v['thumbnail_image']) : ?>
														<img src="/uploads/works/<?=$v['thumbnail_image']?>" alt="" width="100">
														<?php endif; ?>
													</td>
													<td>
														<a href="./edit.php?<?= $currentQueryString ?>"><?= $v['title'] ?></a>
													</td>
													<td><?=$v['created_at']?></td>
													<td>
														<div class="no-table-role">
															<span class="no-role-btn">
																<i class="bx bx-dots-vertical-rounded"></i>
															</span>
															<div class="no-table-action">
																 <a href="./edit.php?<?= $currentQueryString ?>" class="no-btn no-btn--sm no-btn--normal">수정</a>
																<button type="button" class="no-btn no-btn--md no-btn--delete-outline" id="delete-btn" data-id="<?=$v['id']?>">삭제</button>
															</div>
														</div>
													</td>
												</tr>
											<?php endforeach; ?>
										</tbody>
                                    </table>
									<?php if(!$data): ?>
										<p>등록된 내용이 없습니다.</p>
									<?php endif; ?>
								</div>
                            </div>
                        </div>
                    </div>

					<!-- pagination --> 
					<?= $data['html'] ?>

                </section>
            </form>
        </main>

        <script type="text/javascript" src="./js/works.process.js?v=<?= date('YmdHis') ?>"></script>
        <?php include_once "../../inc/admin.footer.php"; ?>
    </div>

	<style>
		.no-admin-filters{
			display: grid; 
			grid-template-columns: repeat(4, 1fr); 
			width: 100%;
			gap: 1.2rem;
		}
		.no-admin-filters > div{
			flex: 1; 
			width: 100%; 
		}

		.no-admin-column .no-admin-block{
			width: 100%;
		}

		.no-admin-content select {
			    border: 1px solid var(--border-color);
				border-radius: 0.4rem;
				height: 4.5rem;
				padding: 0.6rem 1.2rem;
				max-width: 100%;
				width: 100%;
		}

		@media (max-width: 1024px){ 
			.no-admin-filters{
				grid-template-columns: repeat(2, 1fr); 
			}
		}

		@media (max-width: 768px){ 
			.no-admin-filters{
				grid-template-columns: 1fr; 
				gap: 0.8rem; 
			}
		}
	</style>
</body>
</html>
