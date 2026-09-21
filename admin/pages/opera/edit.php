<?php include_once "../../../inc/lib/base.class.php"; ?>
<!DOCTYPE html>
<html lang="ko">
<?php

$depthnum = 1;
$pagenum = 1;

// 요청된 id 확인
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) {
    die("유효하지 않은 ID입니다.");
}

// 데이터베이스에서 해당 id의 데이터 가져오기
try {
    $sql = "SELECT * FROM nb_opera WHERE id = :id";
    $result = DB::query($sql, [':id' => $id]);

    if (empty($result)) {
        die("데이터를 찾을 수 없습니다.");
    }

    // 조회된 데이터
    $operaData = $result[0];
} catch (Exception $e) {
    die("데이터를 가져오는 중 오류가 발생했습니다: " . blue_safe_error($e));
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
            <form id="frm" name="frm" method="post" enctype="multipart/form-data">
                <input type="hidden" id="mode" name="mode" value="edit">
                <input type="hidden" id="id" name="id" value="<?= htmlspecialchars($operaData['id']) ?>">
                <section class="no-content">
                    <!-- Page Title -->
                    <div class="no-toolbar">
                        <div class="no-toolbar-container no-flex-stack">
                            <div class="no-page-indicator">
                                <h1 class="no-page-title">오페라글라스 링크 수정</h1>
                                <div class="no-breadcrumb-container">
                                    <ul class="no-breadcrumb-list">
                                        <li class="no-breadcrumb-item"><span>오페라글라스 링크</span></li>
                                        <li class="no-breadcrumb-item"><span>오페라글라스 링크 수정</span></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- card-title -->
                    <div class="no-toolbar-container">
                        <div class="no-card">
                            <div class="no-card-header no-card-header--detail">
                                <h2 class="no-card-title">링크 수정</h2>
                            </div>
                            <div class="no-card-body no-admin-column no-admin-column--detail">

                                <!-- admin-block -->

								<div class="no-admin-block">
									<h3 class="no-admin-title"><label for="state">상태 관리</label></h3>
									<div class="no-admin-content">
										<div class="no-radio-form">
											<?php
											$states = [1 => '활성', 0 => '비활성'];
											foreach ($states as $value => $label): ?>
												<label for="state_<?= $value ?>">
													<div class="no-radio-box">
														<input type="radio" name="state" id="state_<?= $value ?>" value="<?= $value ?>" <?= $operaData['state'] == $value ? 'checked' : '' ?> />
														<span><i class="bx bx-radio-circle-marked"></i></span>
													</div>
													<span class="no-radio-text"><?= $label ?></span>
												</label>
											<?php endforeach; ?>
										</div>
									</div>
								</div>

                                <div class="no-admin-block">
                                    <h3 class="no-admin-title"><label for="title">타이틀</label></h3>
                                    <div class="no-admin-content">
                                        <input type="text" name="title" id="title" class="no-input--detail" placeholder="타이틀을 입력해주세요." value="<?= htmlspecialchars($operaData['title']) ?>" />
                                    </div>
                                </div>

                                <div class="no-admin-block">
                                    <h3 class="no-admin-title"><label for="link">링크</label></h3>
                                    <div class="no-admin-content">
                                        <input type="text" name="link" id="link" class="no-input--detail" placeholder="링크를 입력해주세요." value="<?= htmlspecialchars($operaData['link']) ?>" />
                                    </div>
                                </div>

                                <!-- admin-block -->

                                <div class="no-items-center center">
                                    <a href="./index.php" class="no-btn no-btn--big no-btn--normal">목록</a>
                                    <a href="javascript:void(0);" class="no-btn no-btn--big no-btn--main" onClick="doManageEdit();">수정</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </form>
        </main>

        <!-- Footer -->
        <script type="text/javascript" src="./js/opera.process.js?c=<?= htmlspecialchars($STATIC_ADMIN_JS_MODIFY_DATE) ?>"></script>
        <?php include_once "../../inc/admin.footer.php"; ?>
    </div>
</body>
</html>
