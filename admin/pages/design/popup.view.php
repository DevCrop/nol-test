<?php include_once "../../../inc/lib/base.class.php"; ?>
<!DOCTYPE html>
<html lang="ko">
<?php

    $depthnum = 2;
    $pagenum = 2;

    $no = $_REQUEST['no'];

    // Fetching popup data
    $db = DB::getInstance();
    $query = "SELECT a.no, a.p_title, a.p_img, a.p_target, a.p_link, a.p_view, a.p_left, a.p_top, a.p_idx, 
              a.p_sdate, a.p_edate, a.p_rdate, a.p_none_limit, a.p_loc
              FROM nb_popup a
              WHERE a.no = :no";
    $stmt = $db->prepare($query);
    $stmt->execute(['no' => $no]);
    $data = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$data) {
        echo "<script>alert('정보를 찾을 수 없습니다');</script>";
        exit;
    }

    include_once "../../inc/admin.title.php";
    include_once "../../inc/admin.css.php";
    include_once "../../inc/admin.js.php";
?>
</head>

<body>
    <div class="no-wrap">
        <!-- Header -->
        <?php
            include_once "../../inc/admin.header.php";
        ?>

        <!-- Main -->
        <main class="no-app no-container">
            <!-- Drawer -->
            <?php
                include_once "../../inc/admin.drawer.php";
            ?>

            <!-- Contents -->
            <form id="frm" name="frm" method="POST" enctype="multipart/form-data">
                <input type="hidden" id="mode" name="mode" value="">
                <input type="hidden" id="no" name="no" value="<?= htmlspecialchars($data['no']) ?>">
                
                <section class="no-content">
                    <!-- Page Title -->
                    <div class="no-toolbar">
                        <div class="no-toolbar-container no-flex-stack">
                            <div class="no-page-indicator">
                                <h1 class="no-page-title">배너 관리</h1>
                                <div class="no-breadcrumb-container">
                                    <ul class="no-breadcrumb-list">
                                        <li class="no-breadcrumb-item">
                                            <span>디자인</span>
                                        </li>
                                        <li class="no-breadcrumb-item">
                                            <span>배너 관리</span>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- card-title -->
                    <div class="no-toolbar-container">
                        <div class="no-card">
                            <div class="no-card-header no-card-header--detail">
                                <h2 class="no-card-title">팝업 등록</h2>
                            </div>
                            <div class="no-card-body no-admin-column no-admin-column--detail">
                                
                                <div class="no-admin-block">
                                    <h3 class="no-admin-title">
                                        <label for="title">팝업노출</label>
                                    </h3>
                                    <div class="no-admin-content">
                                        <div class="no-radio-form">
                                            <label for="input1">
                                                <div class="no-radio-box">
                                                    <input type="radio" name="p_view" id="input1" value="Y" <?= $data['p_view'] === "Y" ? "checked" : "" ?>>
                                                    <span><i class="bx bx-radio-circle-marked"></i></span>
                                                </div>
                                                <span class="no-radio-text">노출</span>
                                            </label>

                                            <label for="input2">
                                                <div class="no-radio-box">
                                                    <input type="radio" name="p_view" id="input2" value="N" <?= $data['p_view'] === "N" ? "checked" : "" ?>>
                                                    <span><i class="bx bx-radio-circle-marked"></i></span>
                                                </div>
                                                <span class="no-radio-text">숨김</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>

                             <!-- admin-block -->
								<div class="no-admin-block">
									<h3 class="no-admin-title">
										<span>기한설정</span>
									</h3>
									<div class="no-admin-content">
										<div class="no-radio-form no-radio-flex">
											<div class="no-radio-link">
												<label for="input3">
													<div class="no-radio-box">
														<input
															type="radio"
															name="p_none_limit"
															id="input3"
															value="Y"
															<?= $data['p_none_limit'] === "Y" ? "checked" : "" ?>
															onClick="doAttrChange('p_date', 'disabled', true);"
														>
														<span><i class="bx bx-radio-circle-marked"></i></span>
													</div>
													<span class="no-radio-text">무기한</span>
												</label>

												<label for="input4">
													<div class="no-radio-box">
														<input
															type="radio"
															name="p_none_limit"
															id="input4"
															value="N"
															<?= $data['p_none_limit'] === "N" ? "checked" : "" ?>
															onClick="doAttrChange('p_date', 'disabled', false);"
														>
														<span><i class="bx bx-radio-circle-marked"></i></span>
													</div>
													<span class="no-radio-text">기한지정</span>
												</label>
											</div>

											<div class="no-admin-content no-admin-date no-pd no-flex-row">
												<input
													type="text"
													name="p_sdate"
													id="p_sdate"
													class="p_date"
													value="<?= htmlspecialchars($data['p_sdate']) ?>"
													<?= $data['p_none_limit'] === "Y" ? "disabled" : "" ?>
												>
												<span></span>
												<input
													type="text"
													name="p_edate"
													id="p_edate"
													class="p_date"
													value="<?= htmlspecialchars($data['p_edate']) ?>"
													<?= $data['p_none_limit'] === "Y" ? "disabled" : "" ?>
												>
											</div>
										</div>
									</div>
								</div>


                                <!-- admin-block -->
                                <div class="no-admin-block">
                                    <h3 class="no-admin-title">
                                        <label for="title">팝업 이미지</label>
                                    </h3>
                                    <div class="no-admin-content">
                                        <?php if ($data['p_img']): ?>
                                            <div class="no-banner-image">
                                                <img src="<?= htmlspecialchars($UPLOAD_WDIR_POPUP . "/" . $data['p_img']) ?>" alt="<?= htmlspecialchars($data['p_title']) ?>">
                                            </div>
                                        <?php endif; ?>
                                        
                                        <div class="no-file-control">
                                            <input type="text" class="no-fake-file" id="fake_p_img" placeholder="파일을 선택해주세요." readonly disabled>
                                            <div class="no-file-box">
                                                <input type="file" name="p_img" id="p_img" onchange="document.getElementById('fake_p_img').value = this.value">
                                                <button type="button" class="no-btn no-btn--main">파일찾기</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- admin-block -->
                                <div class="no-admin-block">
                                    <h3 class="no-admin-title">
                                        <label for="p_idx">노출순위</label>
                                    </h3>
                                    <div class="no-admin-content">
                                        <input type="text" name="p_idx" id="p_idx" value="<?= htmlspecialchars($data['p_idx']) ?>" class="no-input--detail" placeholder="0">
                                        <span class="no-admin-info"><i class="bx bxs-info-circle"></i> 노출순위가 높을수록 우선 노출됩니다.</span>
                                        <span class="no-admin-info"><i class="bx bxs-info-circle"></i> 미입력시 자동으로 부여됩니다.</span>
                                    </div>
                                </div>

                                <!-- admin-block -->
                                <div class="no-admin-block">
                                    <h3 class="no-admin-title">
                                        <label for="p_title">타이틀</label>
                                    </h3>
                                    <div class="no-admin-content">
                                        <input type="text" name="p_title" id="p_title" value="<?= htmlspecialchars($data['p_title']) ?>" class="no-input--detail" placeholder="타이틀을 입력해주세요.">
                                    </div>
                                </div>

                                <!-- admin-block -->
                                <div class="no-admin-block">
                                    <h3 class="no-admin-title">
                                        <span>링크타겟</span>
                                    </h3>
                                    <div class="no-admin-content">
                                        <div class="no-radio-form">
                                            <label for="input5">
                                                <div class="no-radio-box">
                                                    <input type="radio" name="p_target" id="input5" value="_none" <?= $data['p_target'] === "_none" ? "checked" : "" ?> onClick="doHideDiv('no_linkaddress');">
                                                    <span><i class="bx bx-radio-circle-marked"></i></span>
                                                </div>
                                                <span class="no-radio-text">링크없음</span>
                                            </label>

                                            <label for="input6">
                                                <div class="no-radio-box">
                                                    <input type="radio" name="p_target" id="input6" value="_self" <?= $data['p_target'] === "_self" ? "checked" : "" ?> onClick="doOpenDiv('no_link_target_box');">
                                                    <span><i class="bx bx-radio-circle-marked"></i></span>
                                                </div>
                                                <span class="no-radio-text">같은창</span>
                                            </label>

                                            <label for="input7">
                                                <div class="no-radio-box">
                                                    <input type="radio" name="p_target" id="input7" value="_blank" <?= $data['p_target'] === "_blank" ? "checked" : "" ?> onClick="doOpenDiv('no_link_target_box');">
                                                    <span><i class="bx bx-radio-circle-marked"></i></span>
                                                </div>
                                                <span class="no-radio-text">새창</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <!-- admin-block -->
                                <div id="no_link_target_box" class="no-admin-block no_linkaddress" style="<?= $data['p_loc'] === "_none" ? "display:none" : "" ?>">
                                    <h3 class="no-admin-title">
                                        <label for="p_link">링크주소</label>
                                    </h3>
                                    <div class="no-admin-content">
                                        <input type="text" name="p_link" id="p_link" class="no-input--detail" value="<?= htmlspecialchars($data['p_link']) ?>" placeholder="링크주소를 입력해주세요.">
                                    </div>
                                </div>

                                <!-- admin-block -->
								<!--
                                <div class="no-admin-block">
                                    <h3 class="no-admin-title">
                                        <span>노출 위치</span>
                                    </h3>
                                    <div class="no-admin-content">
                                        <div class="no-radio-form">
                                            <label for="input10">
                                                <div class="no-radio-box">
                                                    <input type="radio" name="p_loc" id="input10" value="P" <?= $data['p_loc'] === "P" ? "checked" : "" ?> onClick="doOpenDiv('no_loc_target_box');">
                                                    <span><i class="bx bx-radio-circle-marked"></i></span>
                                                </div>
                                                <span class="no-radio-text">PC 노출</span>
                                            </label>

                                            <label for="input11">
                                                <div class="no-radio-box">
                                                    <input type="radio" name="p_loc" id="input11" value="M" <?= $data['p_loc'] === "M" ? "checked" : "" ?> onClick="doHideDiv('no_pc_pos');">
                                                    <span><i class="bx bx-radio-circle-marked"></i></span>
                                                </div>
                                                <span class="no-radio-text">Mobile 노출</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <div id="no_loc_target_box" class="no-admin-block no_pc_pos" style="<?= $data['p_loc'] === "P" ? "" : "display:none;" ?>">
                                    <h3 class="no-admin-title">
                                        <span>PC 노출위치</span>
                                    </h3>
                                    <div class="no-admin-content">
                                        <div class="no-input-pos">
                                            <span>왼쪽으로부터 거리 (px)</span>
                                            <input type="text" name="p_left" id="p_left" class="no-input--detail" placeholder="0" value="<?= htmlspecialchars($data['p_left']) ?>">
                                        </div>
                                        <div class="no-input-pos">
                                            <span>위쪽으로부터 거리 (px)</span>
                                            <input type="text" name="p_top" id="p_top" class="no-input--detail" placeholder="0" value="<?= htmlspecialchars($data['p_top']) ?>">
                                        </div>
                                    </div>
                                </div>-->

                                <div class="no-items-center center">
                                    <a href="javascript:void(0);" class="no-btn no-btn--big no-btn--delete-outline" onClick="doDelete(<?= htmlspecialchars($data['no']) ?>);">삭제</a>
                                    <a href="./popup.list.php" class="no-btn no-btn--big no-btn--normal">목록</a>
                                    <a href="javascript:void(0);" class="no-btn no-btn--big no-btn--main" onClick="doEditSave();">수정</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </form>
        </main>

        <!-- Footer -->
        <script type="text/javascript" src="./js/popup.process.js?c=<?= htmlspecialchars($STATIC_ADMIN_JS_MODIFY_DATE) ?>"></script>
        <?php
            include_once "../../inc/admin.footer.php";
        ?>
    </div>
</body>
</html>
