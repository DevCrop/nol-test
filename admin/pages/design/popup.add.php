<?php include_once "../../../inc/lib/base.class.php"; ?>
<!DOCTYPE html>
<html lang="ko">
<?php

    $depthnum = 2;
    $pagenum = 2;

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
            <form id="frm" name="frm" method="post" enctype="multipart/form-data">
                <input type="hidden" id="mode" name="mode" value="">
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
                            <!-- page indicator -->
                        </div>
                    </div>

                    <!-- card-title -->
                    <div class="no-toolbar-container">
                        <div class="no-card">
                            <div class="no-card-header no-card-header--detail">
                                <h2 class="no-card-title">배너 등록</h2>
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
                                                    <input
                                                        type="radio"
                                                        name="p_view"
                                                        id="input1"
                                                        value="Y"
                                                        checked
                                                    />
                                                    <span>
                                                        <i class="bx bx-radio-circle-marked"></i>
                                                    </span>
                                                </div>
                                                <span class="no-radio-text">노출</span>
                                            </label>

                                            <label for="input2">
                                                <div class="no-radio-box">
                                                    <input
                                                        type="radio"
                                                        name="p_view"
                                                        id="input2"
                                                        value="N"
                                                    />
                                                    <span>
                                                        <i class="bx bx-radio-circle-marked"></i>
                                                    </span>
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
															checked
															onClick="doAttrChange('p_date', 'disabled', true);"
														/>
														<span>
															<i class="bx bx-radio-circle-marked"></i>
														</span>
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
															onClick="doAttrChange('p_date', 'disabled', false);"
														/>
														<span>
															<i class="bx bx-radio-circle-marked"></i>
														</span>
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
													disabled
												/>
												<span></span>
												<input
													type="text"
													name="p_edate"
													id="p_edate"
													class="p_date"
													disabled 
												/>
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
                                        <div class="no-file-control">
                                            <input
                                                type="text"
                                                class="no-fake-file"
                                                id="fake_p_img"
                                                placeholder="파일을 선택해주세요."
                                                readonly
                                                disabled
                                            />
                                            <div class="no-file-box">
                                                <input
                                                    type="file"
                                                    name="p_img"
                                                    id="p_img"
                                                    onchange="document.getElementById('fake_p_img').value = this.value"
                                                />
                                                <button
                                                    type="button"
                                                    class="no-btn no-btn--main"
                                                >
                                                    파일찾기
                                                </button>
                                            </div>
                                        </div>
                                        <!-- file control -->
                                    </div>
                                </div>
                                <!-- admin-block -->

                                <div class="no-admin-block">
                                    <h3 class="no-admin-title">
                                        <label for="p_idx">노출순위</label>
                                    </h3>
                                    <div class="no-admin-content">
                                        <input
                                            type="text"
                                            name="p_idx"
                                            id="p_idx"
                                            class="no-input--detail"
                                            placeholder="0"
                                        />
                                        <span class="no-admin-info">
                                            <i class="bx bxs-info-circle"></i>
                                            노출순위가 높을수록 우선 노출됩니다.
                                        </span>
                                        <span class="no-admin-info">
                                            <i class="bx bxs-info-circle"></i>
                                            미입력시 자동으로 부여됩니다.
                                        </span>
                                    </div>
                                </div>
                                <!-- admin-block -->

                                <div class="no-admin-block">
                                    <h3 class="no-admin-title">
                                        <label for="p_title">타이틀</label>
                                    </h3>
                                    <div class="no-admin-content">
                                        <input
                                            type="text"
                                            name="p_title"
                                            id="p_title"
                                            class="no-input--detail"
                                            placeholder="타이틀을 입력해주세요."
                                        />
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
                                                    <input
                                                        type="radio"
                                                        name="p_target"
                                                        id="input5"
                                                        value=""
                                                        checked
                                                        onClick="doHideDiv('no_linkaddress');"
                                                    />
                                                    <span>
                                                        <i class="bx bx-radio-circle-marked"></i>
                                                    </span>
                                                </div>
                                                <span class="no-radio-text">링크없음</span>
                                            </label>

                                            <label for="input6">
                                                <div class="no-radio-box">
                                                    <input
                                                        type="radio"
                                                        name="p_target"
                                                        id="input6"
                                                        value="_self"
                                                        onClick="doOpenDiv('no_link_target_box');"
                                                    />
                                                    <span>
                                                        <i class="bx bx-radio-circle-marked"></i>
                                                    </span>
                                                </div>
                                                <span class="no-radio-text">같은창</span>
                                            </label>

                                            <label for="input7">
                                                <div class="no-radio-box">
                                                    <input
                                                        type="radio"
                                                        name="p_target"
                                                        id="input7"
                                                        value="_blank"
                                                        onClick="doOpenDiv('no_link_target_box');"
                                                    />
                                                    <span>
                                                        <i class="bx bx-radio-circle-marked"></i>
                                                    </span>
                                                </div>
                                                <span class="no-radio-text">새창</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <!-- admin-block -->

                                <div id="no_link_target_box" class="no-admin-block no_linkaddress" style="display: none;">
                                    <h3 class="no-admin-title">
                                        <label for="b_link">링크주소</label>
                                    </h3>
                                    <div class="no-admin-content">
                                        <input
                                            type="text"
                                            name="p_link"
                                            id="p_link"
                                            class="no-input--detail"
                                            placeholder="링크주소를 입력해주세요."
                                        />
                                    </div>
                                </div>
                                <!-- admin-block -->


                                <div class="no-items-center center">
                                    <a href="./popup.list.php" class="no-btn no-btn--big no-btn--normal">목록</a>
                                    <a href="javascript:void(0);" class="no-btn no-btn--big no-btn--main" onClick="doRegSave();">수정</a>
                                </div>
                            </div>
                            <!-- card-body -->
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
