<?php include_once "../../../inc/lib/base.class.php"; ?>
<!DOCTYPE html>
<html lang="ko">
<?php

$depthnum = 6;
$pagenum = 1;

try {
    // SQL 쿼리 작성
    $sql = "SELECT * FROM nb_opera";
    $results = DB::query($sql); // DB 클래스의 query 메서드 사용
} catch (Exception $e) {
    echo "Error fetching data: " . blue_safe_error($e);
    $results = [];
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
            <form id="search-form" autocomplete="off">
                <section class="no-content">
                    <div class="no-toolbar">
                        <div class="no-toolbar-container no-flex-stack">
                            <div class="no-page-indicator">
                                <h1 class="no-page-title">오페라글라스 링크 관리</h1>
                                <div class="no-breadcrumb-container">
                                    <ul class="no-breadcrumb-list">
                                        <li class="no-breadcrumb-item"><span>오페라글라스 링크관리</span></li>
                                        <li class="no-breadcrumb-item"><span>오페라글라스 링크</span></li>
                                    </ul>
                                </div>
                            </div>
                            <div class="no-items-center">
                                <a href="./new.php" class="no-btn no-btn--main no-btn--big"> 링크 등록 </a>
                            </div>
                        </div>
                    </div>
					
                    <!-- Contents -->
                    <div class="no-content-container">
                        <div class="no-card">
                            <div class="no-card-header">
                                <h2 class="no-card-title">링크 관리</h2>
                            </div>
                            <div class="no-card-body">
                                <div class="no-table-responsive">
                                    <table class="no-table">
                                        <thead>
                                            <tr>
                                                <th>번호</th>
                                                <th>제목</th>
                                                <th>상태</th>
                                                <th>링크</th>
                                                <th>관리</th>
                                            </tr>
                                        </thead>
										<tbody>
											<?php if (!empty($results)): ?>
												<?php foreach ($results as $row): ?>
													<tr>
														<td><?= htmlspecialchars($row['id']) ?></td>
														<td><?= htmlspecialchars($row['title']) ?></td>
														<td><?= $row['state'] == 1 ? '활성' : '비활성' ?></td>
														<td><?= htmlspecialchars($row['link']) ?></td>
														<td>
															<a href="./edit.php?id=<?= $row['id'] ?>" class="no-btn no-btn--sm no-btn--normal">수정</a>
															<a href="javascript:void(0);" class="no-btn no-btn--sm no-btn--delete-outline" onClick="doManageDelete(<?= htmlspecialchars($row['id']) ?>);">삭제</a>
														</td>
													</tr>
												<?php endforeach; ?>
											<?php else: ?>
												<tr>
													<td colspan="6">등록된 내용이 없습니다.</td>
												</tr>
											<?php endif; ?>
										</tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

					<!-- pagination -->
					<!-- Pagination logic should go here if applicable -->
                </section>
            </form>
        </main>

        <script type="text/javascript" src="./js/opera.process.js?v=<?= date('YmdHis') ?>"></script>
        <?php include_once "../../inc/admin.footer.php"; ?>
    </div>
</body>
</html>
