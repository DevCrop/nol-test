<?php include_once "../../../inc/lib/base.class.php"; ?>
<!DOCTYPE html>
<html lang="ko">
<?php


$depthnum = 1;
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
                                <h1 class="no-page-title">대관 일정 관리</h1>
                                <div class="no-breadcrumb-container">
                                    <ul class="no-breadcrumb-list">
                                        <li class="no-breadcrumb-item"><span>대관</span></li>
                                        <li class="no-breadcrumb-item"><span>대관 일정 관리</span></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- card-title -->
                    <div class="no-toolbar-container">
                        <div class="no-card">
                            <div class="no-card-header no-card-header--detail">
                                <h2 class="no-card-title">일정 등록</h2>
                            </div>
                            <div class="no-card-body no-admin-column no-admin-column--detail">

                                <!-- admin-block -->

                                <div class="no-admin-block">
                                    <h3 class="no-admin-title"><label for="title">일정 노출</label></h3>
                                    <div class="no-admin-content">
                                        <div class="no-radio-form">
                                            <label for="input1">
                                                <div class="no-radio-box">
                                                    <input type="radio" name="r_view" id="input1" value="Y" checked />
                                                    <span><i class="bx bx-radio-circle-marked"></i></span>
                                                </div>
                                                <span class="no-radio-text">노출</span>
                                            </label>

                                            <label for="input2">
                                                <div class="no-radio-box">
                                                    <input type="radio" name="r_view" id="input2" value="N" />
                                                    <span><i class="bx bx-radio-circle-marked"></i></span>
                                                </div>
                                                <span class="no-radio-text">숨김</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <!-- admin-block -->
								<div class="no-admin-block">
                                    <h3 class="no-admin-title"><span>기한설정</span></h3>
                                    <div class="no-admin-content">
									   <div class="no-admin-content no-admin-date no-pd no-flex-row">
											<input type="text" name="r_sdate" id="r_sdate" >
											<span></span>
											<input type="text" name="r_edate" id="r_edate" >
										</div>
                                    </div>
                                </div>

                                <div class="no-admin-block">
                                    <h3 class="no-admin-title"><label for="r_title">타이틀</label></h3>
                                    <div class="no-admin-content">
                                        <input type="text" name="r_title" id="r_title" class="no-input--detail" placeholder="타이틀을 입력해주세요." />
                                    </div>
                                </div>

								<div class="no-admin-block">
									<h3 class="no-admin-title"><label for="contents">공지 내용</label></h3>
									<div class="no-admin-content">
										  <textarea name="contents" id="contents"></textarea>
									</div>
								</div>
                                <!-- admin-block -->
                                <div class="no-items-center center">
                                    <a href="./banner.list.php" class="no-btn no-btn--big no-btn--normal">목록</a>
                                    <a href="javascript:void(0);" class="no-btn no-btn--big no-btn--main" onClick="doManageSave();">수정</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </form>
        </main>

        <!-- Footer -->
        <script type="text/javascript" src="./js/request.process.js?c=<?= htmlspecialchars($STATIC_ADMIN_JS_MODIFY_DATE) ?>"></script>
        <?php include_once "../../inc/admin.footer.php"; ?>
    </div>
</body>
</html>
