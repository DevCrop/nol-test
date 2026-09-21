<?php include_once "../../../inc/lib/base.class.php"; ?>
<!DOCTYPE html>
<html lang="ko">
<?php

    $depthnum = 2;
    $pagenum = 1;

    $no = $_REQUEST['no'];
    $pdo = DB::getInstance();
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$query = "SELECT * 
          FROM nb_banner a 
          WHERE a.no = :no";

    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':no', $no, PDO::PARAM_INT);
    $stmt->execute();
    $data = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$data) {
        die("정보를 찾을 수 없습니다");
    }

    include_once "../../inc/admin.title.php";
    include_once "../../inc/admin.css.php";
    include_once "../../inc/admin.js.php";
?>
<script type="text/javascript" src="./js/banner.process.js?c=<?= htmlspecialchars($STATIC_ADMIN_JS_MODIFY_DATE) ?>"></script>
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
            <?php
                $query = "SELECT no, title, skin, sort_no FROM nb_board_manage ORDER BY no ASC";
                $stmt2 = $pdo->query($query);
                $arrBoardList = $stmt2->fetchAll(PDO::FETCH_ASSOC);
            ?>
            <form id="frm" name="frm" method="post" enctype="multipart/form-data">
                <input type="hidden" id="mode" name="mode" value="">
                <input type="hidden" id="no" name="no" value="<?= htmlspecialchars($data['no']) ?>">

                <section class="no-content">
                    <!-- Page Title -->
                    <div class="no-toolbar">
                        <div class="no-toolbar-container no-flex-stack">
                            <div class="no-page-indicator">
                                <h1 class="no-page-title">게시글 관리</h1>
                                <div class="no-breadcrumb-container">
                                    <ul class="no-breadcrumb-list">
                                        <li class="no-breadcrumb-item"><span>게시판</span></li>
                                        <li class="no-breadcrumb-item"><span>게시글 관리</span></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
				
                    <!-- Content Blocks -->
                    <div class="no-toolbar-container">
                        <div class="no-card">
                            <div class="no-card-header no-card-header--detail">
                                <h2 class="no-card-title">게시글 등록</h2>
                            </div>

							<div class="no-card-body no-admin-column no-admin-column--detail">
								<div class="no-admin-block">
									<h3 class="no-admin-title">
										<label for="b_loc"> 배너구분 </label>
									</h3>
									<div class="no-admin-content">
										<select name="b_loc" id="b_loc">
											<option value="">선택</option>
											<?php foreach ($arr_banner_loc as $key => $val): ?>
												<option value="<?= htmlspecialchars($key) ?>" <?= $data['b_loc'] == $key ? 'selected' : '' ?>><?= htmlspecialchars($val) ?></option>
											<?php endforeach; ?>
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
													<input type="radio" name="b_view" id="input1" value="Y" <?= $data['b_view'] == "Y" ? 'checked' : '' ?> />
													<span><i class="bx bx-radio-circle-marked"></i></span>
												</div>
												<span class="no-radio-text">노출</span>
											</label>
											<label for="input2">
												<div class="no-radio-box">
													<input type="radio" name="b_view" id="input2" value="N" <?= $data['b_view'] == "N" ? 'checked' : '' ?> />
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
										<input type="text" name="b_idx" id="b_idx" class="no-input--detail" placeholder="0" value="<?= htmlspecialchars($data['b_idx']) ?>" />
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
														<input type="radio" name="b_none_limit" id="input3" value="Y" <?= $data['b_none_limit'] == "Y" ? 'checked' : '' ?> onClick="doAttrChange('b_date', 'disabled', true);" />
														<span><i class="bx bx-radio-circle-marked"></i></span>
													</div>
													<span class="no-radio-text">무기한</span>
												</label>

												<label for="input4">
													<div class="no-radio-box">
														<input type="radio" name="b_none_limit" id="input4" value="N" <?= $data['b_none_limit'] == "N" ? 'checked' : '' ?> onClick="doAttrChange('b_date', 'disabled', false);" />
														<span><i class="bx bx-radio-circle-marked"></i></span>
													</div>
													<span class="no-radio-text">기한지정</span>
												</label>
											</div>

											<div class="no-admin-content no-admin-date no-pd no-flex-row">
												<input type="text" name="b_sdate" id="b_sdate" <?= $data['b_none_limit'] == "N" ? '' : 'disabled' ?> value="<?= $data['b_sdate'] == "0000-00-00" ? "" : htmlspecialchars($data['b_sdate']) ?>" />
												<span></span>
												<input type="text" name="b_edate" id="b_edate" <?= $data['b_none_limit'] == "N" ? '' : 'disabled' ?> value="<?= $data['b_edate'] == "0000-00-00" ? "" : htmlspecialchars($data['b_edate']) ?>" />
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
														<input type="radio" name="b_none_view" id="b_none_view_input_1" value="Y" <?= $data['b_none_view'] == "Y" ? 'checked' : '' ?> checked onClick="doAttrChange('b_view_date', 'disabled', true);" />
														<span><i class="bx bx-radio-circle-marked"></i></span>
													</div>
													<span class="no-radio-text">무기한</span>
												</label>

												<label for="b_none_view_input_2">
													<div class="no-radio-box">
														<input type="radio" name="b_none_view" id="b_none_view_input_2" value="N" <?= $data['b_none_view'] == "N" ? 'checked' : '' ?> onClick="doAttrChange('b_view_date', 'disabled', false);" />
														<span><i class="bx bx-radio-circle-marked"></i></span>
													</div>
													<span class="no-radio-text">기한지정</span>
												</label>
											</div>

											<div class="no-admin-content no-admin-date no-pd no-flex-row">
												<input type="text" name="b_sdate_view" id="b_sdate_view" class="b_view_date" <?= $data['b_none_view'] == "N" ? '' : 'disabled' ?> value="<?= $data['b_sdate_view'] == "0000-00-00" ? "" : htmlspecialchars($data['b_sdate_view']) ?>" />
												<span></span>
												<input type="text" name="b_edate_view" id="b_edate_view" class="b_view_date" <?= $data['b_none_view'] == "N" ? '' : 'disabled' ?> value="<?= $data['b_edate_view'] == "0000-00-00" ? "" : htmlspecialchars($data['b_edate_view']) ?>"  />
											</div>
										</div>
									</div>
								</div>

								<!-- admin-block -->
								<div class="no-admin-block">
									<h3 class="no-admin-title"><label for="title">배너 이미지</label></h3>
									<div class="no-admin-content">
										<?php if ($data['b_img']): ?>
											<div class="no-banner-image">
												<img src="<?= htmlspecialchars($UPLOAD_WDIR_BANNER) ?>/<?= htmlspecialchars($data['b_img']) ?>" alt="<?= htmlspecialchars($data['b_title']) ?>" />
											</div>
										<?php endif; ?>

										<div class="no-file-control">
											<input type="text" class="no-fake-file" id="fake_banner_file" placeholder="파일을 선택해주세요." readonly disabled />
											<div class="no-file-box">
												<input type="file" name="b_img" id="b_img" onchange="document.getElementById('fake_banner_file').value = this.value" />
												<button type="button" class="no-btn no-btn--main">파일찾기</button>
											</div>
										</div>
										<span class="no-admin-info"><i class="bx bxs-info-circle"></i> 권장 사이즈는 가로 x 세로<b>(4096 x 960)</b>입니다. (단위px)</span>
										<span class="no-admin-info"><i class="bx bxs-info-circle"></i> 크기는 자동 조절될 수 있습니다.</span>
									</div>
								</div>

								<!-- admin-block -->
								<div class="no-admin-block">
									<h3 class="no-admin-title"><label for="title">배너 모바일 이미지</label></h3>
									<div class="no-admin-content">
										<?php if ($data['b_img_mobile']): ?>
											<div class="no-banner-image">
												<img src="<?= htmlspecialchars($UPLOAD_WDIR_BANNER) ?>/<?= htmlspecialchars($data['b_img_mobile']) ?>" alt="<?= htmlspecialchars($data['b_title']) ?>" />
											</div>
										<?php endif; ?>

										<div class="no-file-control">
											<input type="text" class="no-fake-file" id="fake_banner_file_2" placeholder="파일을 선택해주세요." readonly disabled />
											<div class="no-file-box">
												<input type="file" name="b_img_mobile" id="b_img_mobile" onchange="document.getElementById('fake_banner_file_2').value = this.value" />
												<button type="button" class="no-btn no-btn--main">파일찾기</button>
											</div>
										</div>
										<span class="no-admin-info"><i class="bx bxs-info-circle"></i> 권장 사이즈는 가로 x 세로(712 x 630)입니다. (단위px)</span>
										<span class="no-admin-info"><i class="bx bxs-info-circle"></i> 크기는 자동 조절될 수 있습니다.</span>
									</div>
								</div>
								<!--
								<div class="no	-admin-block">
									<h3 class="no-admin-title"><label for="title">포스터 이미지</label></h3>
									<div class="no-admin-content">
										<?php if ($data['b_poster_img']): ?>
											<div class="no-banner-image">
												<img src="/uploads/banner/<?= htmlspecialchars($data['b_poster_img']) ?>" alt="<?= htmlspecialchars($data['b_title']) ?>" />
											</div>
										<?php endif; ?>


										<div class="no-file-control">
											<input type="text" class="no-fake-file" id="fake_banner_file_3" placeholder="파일을 선택해주세요." readonly disabled />
											<div class="no-file-box">
												<input type="file" name="b_poster_Img" id="b_poster_Img" onchange="document.getElementById('fake_banner_file_3').value = this.value" />
												<button type="button" class="no-btn no-btn--main">파일찾기</button>
											</div>
										</div>
										<span class="no-admin-info"><i class="bx bxs-info-circle"></i> 권장 사이즈는 가로 x 세로(712 x 630)입니다. (단위px)</span>
										<span class="no-admin-info"><i class="bx bxs-info-circle"></i> 크기는 자동 조절될 수 있습니다.</span>
									</div>
								</div>-->
								

								<div class="no-admin-block">
                                    <h3 class="no-admin-title">
                                        <label for="title">공연 장소</label>
                                    </h3>
                                    <div class="no-admin-content">
                                        <input
                                            type="text"
                                            name="b_location"
                                            id="b_location"
                                            class="no-input--detail"
                                            placeholder="제목을 입력해주세요."
                                            value="<?=$data['b_location']?>" 
                                        />
                                    </div>
                                </div>



								<div class="no-admin-block">
                                    <h3 class="no-admin-title">
                                        <label for="title">타이틀</label>
                                    </h3>
                                    <div class="no-admin-content">
                                        <input
                                            type="text"
                                            name="b_title"
                                            id="b_title"
                                            class="no-input--detail"
                                            placeholder="제목을 입력해주세요."
                                            value="<?=$data['b_title']?>" 
                                        />
                                    </div>
                                </div>
                                <!-- admin-block -->
									<!--
                                <div class="no-admin-block">
                                    <h3 class="no-admin-title">
                                        <label for="b_desc">서브 타이틀</label>
                                    </h3>
                                    <div class="no-admin-content">
                                        <input
                                            type="text"
                                            name="b_desc"
                                            id="b_desc"
                                            class="no-input--detail"
                                            placeholder="제목을 입력해주세요."
                                            value="<?=$data['b_desc']?>"
                                        />
                                    </div>
                                </div>
                                <!-- admin-block -->	
							<!--
								<div class="no-admin-block">
                                    <h3 class="no-admin-title">
                                        <label for="content">내용</label>
                                    </h3>
                                     <div class="no-admin-content">
										<textarea name="content" id="content" class="SEditor" ><?=$data['b_contents']?></textarea>
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
                                                        name="b_target"
                                                        id="input5"
                                                        value="_none"
                                                        onClick="doHideDiv('no_linkaddress');doResetInput('b_link');"
                                                        <?php if($data['b_target'] == "_none") echo "checked";?>
                                                    />
                                                    <span>
                                                        <i
                                                            class="bx bx-radio-circle-marked"
                                                        ></i>
                                                    </span>
                                                </div>
                                                <span class="no-radio-text"
                                                    >링크없음</span
                                                >
                                            </label>

                                            <label for="input6">
                                                <div class="no-radio-box">
                                                    <input
                                                        type="radio"
                                                        name="b_target"
                                                        id="input6"
                                                        value="_self"
                                                        onClick="doOpenDiv('no_link_target_box');"
                                                        <?php if($data['b_target'] == "_self") echo "checked";?>  
                                                    />
                                                    <span>
                                                        <i
                                                            class="bx bx-radio-circle-marked"
                                                        ></i>
                                                    </span>
                                                </div>
                                                <span class="no-radio-text"
                                                    >같은창</span
                                                >
                                            </label>

                                            <label for="input7">
                                                <div class="no-radio-box">
                                                    <input
                                                        type="radio"
                                                        name="b_target"
                                                        id="input7"
                                                        value="_blank"
                                                        onClick="doOpenDiv('no_link_target_box');"
                                                        <?php if($data['b_target'] == "_blank") echo "checked";?>  
                                                    />
                                                    <span>
                                                        <i
                                                            class="bx bx-radio-circle-marked"
                                                        ></i>
                                                    </span>
                                                </div>
                                                <span class="no-radio-text"
                                                    >새창</span
                                                >
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <!-- admin-block -->

                                <div id="no_link_target_box" class="no-admin-block no_linkaddress" <?=($data['b_target'] == "_self" || $data['b_target'] == "_blank") ? "style='display:flex'" : "style='display:none'" ?>>
                                    <h3 class="no-admin-title">
                                        <label for="b_link">링크주소</label>
                                    </h3>
                                    <div class="no-admin-content">
                                        <input
                                            type="text"
                                            name="b_link"
                                            id="b_link"
                                            class="no-input--detail"
                                            placeholder="링크주소를 입력해주세요."
                                            value="<?=$data['b_link']?>"
                                        />
                                    </div>
                                </div>
                                <!-- admin-block -->

                                <div class="no-items-center center">
                                    <a
                                        href="javascript:void(0);"
                                        class="no-btn no-btn--big no-btn--delete-outline"
                                        onClick="doDelete(<?=$data['no']?>);"
                                    >
                                        삭제
                                    </a>
                                    <a
                                        href="./banner.list.php"
                                        class="no-btn no-btn--big no-btn--normal"
                                    >
                                        목록
                                    </a>
                                    <a
                                        href="javascript:void(0);"
                                        class="no-btn no-btn--big no-btn--main"
                                        onClick="doEditSave();"
                                    >
                                        수정
                                    </a>
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
