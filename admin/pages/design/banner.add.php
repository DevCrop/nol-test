<?php include_once "../../../inc/lib/base.class.php"; ?>
<!DOCTYPE html>
<html lang="ko">
<?php
	
	$depthnum = 2;
	$pagenum = 1;

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
                <input type="hidden" id="mode" name="mode" value="">
                <section class="no-content">
                    <!-- Page Title -->
                    <div class="no-toolbar">
                        <div class="no-toolbar-container no-flex-stack">
                            <div class="no-page-indicator">
                                <h1 class="no-page-title">배너 관리</h1>
                                <div class="no-breadcrumb-container">
                                    <ul class="no-breadcrumb-list">
                                        <li class="no-breadcrumb-item"><span>디자인</span></li>
                                        <li class="no-breadcrumb-item"><span>배너 관리</span></li>
                                    </ul>
                                </div>
                            </div>
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
                                        <label for="b_loc"> 배너구분 </label>
                                    </h3>
                                    <div class="no-admin-content">
                                        <select name="b_loc" id="b_loc">
                                            <option value="">선택</option>
                                            <?php
                                                foreach ($arr_banner_loc as $key => $val) {
                                                    $selected = ($data['b_loc'] ?? '') == $key ? 'selected' : '';
                                                    echo "<option value='" . htmlspecialchars($key) . "' $selected>" . htmlspecialchars($val) . "</option>";
                                                }
                                            ?>
                                        </select>
                                        <span class="no-admin-info">글을 등록하시려는 게시판을 선택하세요</span>
                                    </div>
                                </div>
                                <!-- admin-block -->

                                <div class="no-admin-block">
                                    <h3 class="no-admin-title"><label for="title">배너노출</label></h3>
                                    <div class="no-admin-content">
                                        <div class="no-radio-form">
                                            <label for="input1">
                                                <div class="no-radio-box">
                                                    <input type="radio" name="b_view" id="input1" value="Y" checked />
                                                    <span><i class="bx bx-radio-circle-marked"></i></span>
                                                </div>
                                                <span class="no-radio-text">노출</span>
                                            </label>

                                            <label for="input2">
                                                <div class="no-radio-box">
                                                    <input type="radio" name="b_view" id="input2" value="N" />
                                                    <span><i class="bx bx-radio-circle-marked"></i></span>
                                                </div>
                                                <span class="no-radio-text">숨김</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <!-- admin-block -->

                                <div class="no-admin-block">
                                    <h3 class="no-admin-title"><label for="b_idx">노출순위</label></h3>
                                    <div class="no-admin-content">
                                        <input type="text" name="b_idx" id="b_idx" class="no-input--detail" placeholder="0" />
                                        <span class="no-admin-info"><i class="bx bxs-info-circle"></i> 미입력시 자동으로 부여됩니다.</span>
                                    </div>
                                </div>

                                <!-- admin-block -->
			<!--
                                <div class="no-admin-block">
                                    <h3 class="no-admin-title"><span>공연기간</span></h3>
                                    <div class="no-admin-content">
                                        <div class="no-radio-form no-radio-flex">
                                            <div class="no-radio-link">
                                                <label for="input3">
                                                    <div class="no-radio-box">
                                                        <input type="radio" name="b_none_limit" id="input3" value="Y" checked onClick="doAttrChange('b_date', 'disabled', true);" />
                                                        <span><i class="bx bx-radio-circle-marked"></i></span>
                                                    </div>
                                                    <span class="no-radio-text">무기한</span>
                                                </label>

                                                <label for="input4">
                                                    <div class="no-radio-box">
                                                        <input type="radio" name="b_none_limit" id="input4" value="N" onClick="doAttrChange('b_date', 'disabled', false);" />
                                                        <span><i class="bx bx-radio-circle-marked"></i></span>
                                                    </div>
                                                    <span class="no-radio-text">기한지정</span>
                                                </label>
                                            </div>

                                            <div class="no-admin-content no-admin-date no-pd no-flex-row">
                                                <input type="text" name="b_sdate" id="b_sdate" disabled />
                                                <span></span>
                                                <input type="text" name="b_edate" id="b_edate" disabled />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- admin-block -->
								<!--
								<div class="no-admin-block">
									<h3 class="no-admin-title"><span>노출기간</span></h3>
									<div class="no-admin-content">
										<div class="no-radio-form no-radio-flex">
											<div class="no-radio-link">
												<label for="input5">
													<div class="no-radio-box">
														<input type="radio" name="b_none_view" id="input5" value="Y" checked onClick="doAttrChange('b_date', 'disabled', true);" />
														<span><i class="bx bx-radio-circle-marked"></i></span>
													</div>
													<span class="no-radio-text">무기한</span>
												</label>

												<label for="input6">
													<div class="no-radio-box">
														<input type="radio" name="b_none_view" id="input6" value="N" onClick="doAttrChange('b_date', 'disabled', false);" />
														<span><i class="bx bx-radio-circle-marked"></i></span>
													</div>
													<span class="no-radio-text">기한지정</span>
												</label>
											</div>
											<div class="no-admin-content no-admin-date no-pd no-flex-row">
												<input type="text" name="b_sdate_view" id="b_sdate_view" disabled />
												<span></span>
												<input type="text" name="b_edate_view" id="b_edate_view" disabled />
											</div>
										</div>
									</div>
								</div>
								<!-- admin-block -->


								
								<div class="no-admin-block">
									<h3 class="no-admin-title"><span>공연기간</span></h3>
									<div class="no-admin-content">
										<div class="no-radio-form no-radio-flex">
											<div class="no-radio-link">
												<label for="input3">
													<div class="no-radio-box">
														<input type="radio" name="b_none_limit" id="input3" value="Y" checked onClick="doAttrChange('b_date', 'disabled', true);" />
														<span><i class="bx bx-radio-circle-marked"></i></span>
													</div>
													<span class="no-radio-text">무기한</span>
												</label>

												<label for="input4">
													<div class="no-radio-box">
														<input type="radio" name="b_none_limit" id="input4" value="N" onClick="doAttrChange('b_date', 'disabled', false);" />
														<span><i class="bx bx-radio-circle-marked"></i></span>
													</div>
													<span class="no-radio-text">기한지정</span>
												</label>
											</div>

											<div class="no-admin-content no-admin-date no-pd no-flex-row">
												<input type="text" name="b_sdate" id="b_sdate" class="b_date" disabled />
												<span></span>
												<input type="text" name="b_edate" id="b_edate" class="b_date" disabled />
											</div>
										</div>
									</div>
								</div>

								<div class="no-admin-block">
									<h3 class="no-admin-title"><span>노출기간</span></h3>
									<div class="no-admin-content">
										<div class="no-radio-form no-radio-flex">
											<div class="no-radio-link">
												<label for="b_none_view_input_1">
													<div class="no-radio-box">
														<input type="radio" name="b_none_view" id="b_none_view_input_1" value="Y" checked onClick="doAttrChange('b_view_date', 'disabled', true);" />
														<span><i class="bx bx-radio-circle-marked"></i></span>
													</div>
													<span class="no-radio-text">무기한</span>
												</label>

												<label for="b_none_view_input_2">
													<div class="no-radio-box">
														<input type="radio" name="b_none_view" id="b_none_view_input_2" value="N" onClick="doAttrChange('b_view_date', 'disabled', false);" />
														<span><i class="bx bx-radio-circle-marked"></i></span>
													</div>
													<span class="no-radio-text">기한지정</span>
												</label>
											</div>

											<div class="no-admin-content no-admin-date no-pd no-flex-row">
												<input type="text" name="b_sdate_view" id="b_sdate_view" class="b_view_date" disabled />
												<span></span>
												<input type="text" name="b_edate_view" id="b_edate_view" class="b_view_date" disabled />
											</div>
										</div>
									</div>
								</div>

                                <!-- Banner image upload -->
                                <div class="no-admin-block">
                                    <h3 class="no-admin-title"><label for="title">배너 이미지</label></h3>
                                    <div class="no-admin-content">
                                        <div class="no-file-control">
                                            <input type="text" class="no-fake-file" id="fake_banner_file" placeholder="파일을 선택해주세요." readonly disabled />
                                            <div class="no-file-box">
                                                <input type="file" name="b_img" id="b_img" onchange="javascript:document.getElementById('fake_banner_file').value = this.value" />
                                                <button type="button" class="no-btn no-btn--main">파일찾기</button>
                                            </div>
                                        </div>
                                        <span class="no-admin-info"><i class="bx bxs-info-circle"></i> 권장 사이즈는 가로 x 세로(1920 x 1080)입니다. (단위px)</span>
                                    </div>
                                </div>
                                <!-- admin-block -->

								<!-- Banner mobile image upload -->
                                <div class="no-admin-block">
                                    <h3 class="no-admin-title"><label for="title">배너 모바일 이미지</label></h3>
                                    <div class="no-admin-content">
                                        <div class="no-file-control">
                                            <input type="text" class="no-fake-file" id="fake_banner_file_2" placeholder="파일을 선택해주세요." readonly disabled />
                                            <div class="no-file-box">
                                                <input type="file" name="b_img_mobile" id="b_img_mobile" onchange="document.getElementById('fake_banner_file_2').value = this.value" />
                                                <button type="button" class="no-btn no-btn--main">파일찾기</button>
                                            </div>
                                        </div>
                                        <span class="no-admin-info"><i class="bx bxs-info-circle"></i> 권장 사이즈는 가로 x 세로(760 x 1030)입니다. (단위px)</span>
                                    </div>
                                </div>
								<!--
								<div class="no-admin-block">
									<h3 class="no-admin-title"><label for="title">포스터 이미지</label></h3>
									<div class="no-admin-content">
										<div class="no-file-control">
											<input type="text" class="no-fake-file" id="fake_banner_file_3" placeholder="파일을 선택해주세요." readonly disabled />
											<div class="no-file-box">
												<input type="file" name="b_poster_img" id="b_poster_img" onchange="document.getElementById('fake_banner_file_3').value = this.value" />
												<button type="button" class="no-btn no-btn--main">파일찾기</button>
											</div>
										</div>
										<span class="no-admin-info"><i class="bx bxs-info-circle"></i> 권장 사이즈는 가로 x 세로(712 x 630)입니다. (단위px)</span>
									</div>
								</div>-->




								    <div class="no-admin-block">
										<h3 class="no-admin-title">
											<label for="b_location">공연장소</label>
										</h3>
										<div class="no-admin-content">
											<input
												type="text"
												name="b_location"
												id="b_location"
												class="no-input--detail"
												placeholder="공연장소를 입력해주세요."
											/>
										</div>
									</div>


                                <!-- admin-block -->

                                <div class="no-admin-block">
                                    <h3 class="no-admin-title"><label for="b_title">타이틀</label></h3>
                                    <div class="no-admin-content">
                                        <input type="text" name="b_title" id="b_title" class="no-input--detail" placeholder="타이틀을 입력해주세요." />
                                    </div>
                                </div>
                                <!-- admin-block -->
								<!--
                                <div class="no-admin-block">
                                    <h3 class="no-admin-title"><label for="b_desc">서브 타이틀</label></h3>
                                    <div class="no-admin-content">
                                        <input type="text" name="b_desc" id="b_desc" class="no-input--detail" placeholder="서브 타이틀을 입력해주세요." />
                                    </div>
                                </div>
                                <!-- admin-block -->
								<!--
								<div class="no-admin-block">
                                    <h3 class="no-admin-title"><label for="content">내용</label></h3>
                                     <div class="no-admin-content">
										<textarea name="content" id="content" class="SEditor"></textarea>
                                    </div>
                                </div>
                                <!-- admin-block -->

                                <div class="no-admin-block">
                                    <h3 class="no-admin-title"><span>링크타겟</span></h3>
                                    <div class="no-admin-content">
                                        <div class="no-radio-form">
                                            <label for="input5">
                                                <div class="no-radio-box">
                                                    <input type="radio" name="b_target" id="input5" value="_none" checked onClick="doHideDiv('no_linkaddress');" />
                                                    <span><i class="bx bx-radio-circle-marked"></i></span>
                                                </div>
                                                <span class="no-radio-text">링크없음</span>
                                            </label>

                                            <label for="input6">
                                                <div class="no-radio-box">
													<input type="radio" name="b_target" id="input6" value="_self" onClick="doOpenDiv('no_link_target_box');" />
                                                    <span><i class="bx bx-radio-circle-marked"></i></span>
                                                </div>
                                                <span class="no-radio-text">같은창</span>
                                            </label>

                                            <label for="input7">
                                                <div class="no-radio-box">
													<input type="radio" name="b_target" id="input7" value="_blank" onClick="doOpenDiv('no_link_target_box');" />
                                                    <span><i class="bx bx-radio-circle-marked"></i></span>
                                                </div>
                                                <span class="no-radio-text">새창</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <!-- admin-block -->

                                <div id="no_link_target_box" class="no-admin-block no_linkaddress" style="display: none;">
                                    <h3 class="no-admin-title"><label for="b_link">링크주소</label></h3>
                                    <div class="no-admin-content">
                                        <input type="text" name="b_link" id="b_link" class="no-input--detail" placeholder="링크주소를 입력해주세요." />
                                    </div>
                                </div>
                                <!-- admin-block -->

                                <div class="no-items-center center">
                                    <a href="./banner.list.php" class="no-btn no-btn--big no-btn--normal">목록</a>
                                    <a href="javascript:void(0);" class="no-btn no-btn--big no-btn--main" onClick="doRegSave();">수정</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </form>
        </main>

        <!-- Footer -->
        <script type="text/javascript" src="./js/banner.process.js?c=<?= htmlspecialchars($STATIC_ADMIN_JS_MODIFY_DATE) ?>"></script>
        <?php include_once "../../inc/admin.footer.php"; ?>
    </div>
</body>
</html>
