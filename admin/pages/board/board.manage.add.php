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
<style>

</style>
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
                            <h1 class="no-page-title">게시글 관리</h1>
                            <div class="no-breadcrumb-container">
                                <ul class="no-breadcrumb-list">
                                <li class="no-breadcrumb-item">
                                    <span>게시판</span>
                                </li>
                                <li class="no-breadcrumb-item">
                                    <span>게시글 관리</span>
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
                                <h2 class="no-card-title">게시판 등록</h2>
                            </div>
                            <div
                                class="no-card-body no-admin-column no-admin-column--detail"
                            >
                                <div class="no-admin-block">
                                    <h3 class="no-admin-title">
                                        <label for="skin"> 게시판 선택 </label>
                                    </h3>
                                    <div class="no-admin-content">
                                        <select name="skin" id="skin">
                                            <option value="">
                                                타입선택
                                            </option>
											<?php 
											$board_type = $board_type ?? [];
											foreach ($board_type as $key => $val): ?>
											<option value="<?=$key?>" <?= (isset($skin) && $skin == $key) ? "selected" : "" ?>><?=$val?></option>
											<?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <!-- admin-block -->

                                <div class="no-admin-block">
                                    <h3 class="no-admin-title">
                                        <label for="title">게시판 이름</label>
                                    </h3>
                                    <div class="no-admin-content">
                                        <input
                                            type="text"
                                            name="title"
                                            id="title"
                                            class="no-input--detail"
                                            placeholder="제목을 입력해주세요."
                                        />
                                    </div>
                                </div>
                                <!-- admin-block -->


                                <div class="no-admin-block">
                                    <h3 class="no-admin-title">
                                        <label for="title">상단 이미지</label>
                                    </h3>
                                    <div class="no-admin-content">
                                        <div class="no-file-control">
                                            <input
                                                type="text"
                                                class="no-fake-file"
                                                id="fakeThumbFileTxt"
                                                placeholder="파일을 선택해주세요."
                                                readonly
                                                disabled
                                            />
                                            <div class="no-file-box">
                                                <input
                                                    type="file"
                                                    name="top_banner_image"
                                                    id="top_banner_image"
                                                    onchange="javascript:document.getElementById('fakeThumbFileTxt').value = this.value"
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
                                        <label for="content" >상단 HTML</label>
                                    </h3>
                                    <div class="no-admin-content">
                                        <div class="no-admin-check">
                                            <textarea
                                                name="content"
                                                id="content"
                                                class="SEditor"
                                            >
                                            </textarea>
                                        </div>
                                    </div>
                                </div>
                                <!-- admin-block -->

                                <div class="no-admin-block">
                                    <h3 class="no-admin-title">
                                        <label for="title">노출</label>
                                    </h3>
                                    <div class="no-admin-content">
                                        <div class="no-radio-form">
                                            <label for="view_yn1">
                                                <div class="no-radio-box">
                                                    <input type="radio" name="view_yn" id="view_yn1" value="Y" checked>
                                                    <span>
                                                        <i class="bx bx-radio-circle-marked"></i>
                                                    </span>
                                                </div>
                                                <span class="no-radio-text">사용</span>
                                            </label>

                                            <label for="view_yn2">
                                                <div class="no-radio-box">
                                                    <input type="radio" name="view_yn" id="view_yn2" value="N">
                                                    <span>
                                                        <i class="bx bx-radio-circle-marked"></i>
                                                    </span>
                                                </div>
                                                <span class="no-radio-text">미사용</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <!-- admin block -->

                                <div class="no-admin-block">
                                    <h3 class="no-admin-title">
                                        <label for="title">비밀글 사용여부</label>
                                    </h3>
                                    <div class="no-admin-content">
                                        <div class="no-radio-form">
                                            <label for="secret_yn1">
                                                <div class="no-radio-box">
                                                    <input type="radio" name="secret_yn" id="secret_yn1" value="Y" >
                                                    <span>
                                                        <i class="bx bx-radio-circle-marked"></i>
                                                    </span>
                                                </div>
                                                <span class="no-radio-text">사용</span>
                                            </label>

                                            <label for="secret_yn2">
                                                <div class="no-radio-box">
                                                    <input type="radio" name="secret_yn" id="secret_yn2" value="N" checked>
                                                    <span>
                                                        <i class="bx bx-radio-circle-marked"></i>
                                                    </span>
                                                </div>
                                                <span class="no-radio-text">미사용</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <!-- admin block -->

                                <div class="no-admin-block">
                                    <h3 class="no-admin-title">
                                        <label for="title">댓글 사용여부</label>
                                    </h3>
                                    <div class="no-admin-content">
                                        <div class="no-radio-form">
                                            <label for="comment_yn1">
                                                <div class="no-radio-box">
                                                    <input type="radio" name="comment_yn" id="comment_yn1" value="Y" >
                                                    <span>
                                                        <i class="bx bx-radio-circle-marked"></i>
                                                    </span>
                                                </div>
                                                <span class="no-radio-text">사용</span>
                                            </label>

                                            <label for="comment_yn2">
                                                <div class="no-radio-box">
                                                    <input type="radio" name="comment_yn" id="comment_yn2" value="N" checked>
                                                    <span>
                                                        <i class="bx bx-radio-circle-marked"></i>
                                                    </span>
                                                </div>
                                                <span class="no-radio-text">미사용</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <!-- admin block -->

                                <div class="no-admin-block">
                                    <h3 class="no-admin-title">
                                        <label for="title">카테고리 사용여부</label>
                                    </h3>
                                    <div class="no-admin-content">
                                        <div class="no-radio-form">
                                            <label for="category_yn1">
                                                <div class="no-radio-box">
                                                    <input type="radio" name="category_yn" id="category_yn1" value="Y" >
                                                    <span>
                                                        <i class="bx bx-radio-circle-marked"></i>
                                                    </span>
                                                </div>
                                                <span class="no-radio-text">사용</span>
                                            </label>

                                            <label for="category_yn2">
                                                <div class="no-radio-box">
                                                    <input type="radio" name="category_yn" id="category_yn2" value="N" checked>
                                                    <span>
                                                        <i class="bx bx-radio-circle-marked"></i>
                                                    </span>
                                                </div>
                                                <span class="no-radio-text">미사용</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <!-- admin block -->


								<div class="no-admin-block">
                                    <h3 class="no-admin-title">
                                        <label for="title">카테고리 분류 사용여부</label>
                                    </h3>
                                    <div class="no-admin-content">
                                        <div class="no-radio-form">
                                            <label for="depth_category_yn1">
                                                <div class="no-radio-box">
                                                    <input type="radio" name="depth_category_yn" id="depth_category_yn1" value="Y" >
                                                    <span>
                                                        <i class="bx bx-radio-circle-marked"></i>
                                                    </span>
                                                </div>
                                                <span class="no-radio-text">사용</span>
                                            </label>

                                            <label for="depth_category_yn2">
                                                <div class="no-radio-box">
                                                    <input type="radio" name="depth_category_yn" id="depth_category_yn2" value="N" checked>
                                                    <span>
                                                        <i class="bx bx-radio-circle-marked"></i>
                                                    </span>
                                                </div>
                                                <span class="no-radio-text">미사용</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <!-- admin block -->

                                <div class="no-admin-block">
                                    <h3 class="no-admin-title">
                                        <label for="title">파일첨부 사용여부</label>
                                    </h3>
                                    <div class="no-admin-content">
                                        <div class="no-radio-form">
                                            <label for="fileattach_yn1">
                                                <div class="no-radio-box">
                                                    <input type="radio" name="fileattach_yn" id="fileattach_yn1" value="Y" onClick="doAttrChange('fileattach_cnt', 'disabled', false);">
                                                    <span>
                                                        <i class="bx bx-radio-circle-marked"></i>
                                                    </span>
                                                </div>
                                                <span class="no-radio-text">사용</span>
                                            </label>

                                            <label for="fileattach_yn2">
                                                <div class="no-radio-box">
                                                    <input type="radio" name="fileattach_yn" id="fileattach_yn2" value="N" checked onClick="doAttrChange('fileattach_cnt', 'disabled', true);" >
                                                    <span>
                                                        <i class="bx bx-radio-circle-marked"></i>
                                                    </span>
                                                </div>
                                                <span class="no-radio-text">미사용</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <!-- admin block -->

                                <div class="no-admin-block">
                                    <h3 class="no-admin-title">
                                        <label for="fileattach_cnt"> 파일첨부 갯수 </label>
                                    </h3>
                                    <div class="no-admin-content">
                                        <select name="fileattach_cnt" id="fileattach_cnt" disabled>
                                            <option value="">선택</option>
                                            <option value="1" >1개</option>
                                            <option value="2" >2개</option>
                                            <option value="3" >3개</option>
                                            <option value="4" >4개</option>
                                            <option value="5" >5개</option>
                                        </select>
                                    </div>
                                </div>
                                <!-- admin-block -->

                                <div class="no-admin-block">
                                    <h3 class="no-admin-title">
                                        <label for="list_size">목표 출력 카운트</label>
                                    </h3>
                                    <div class="no-admin-content">
                                        <input
                                            type="text"
                                            name="list_size"
                                            id="list_size"
                                            class="no-input--detail"
                                            placeholder="목록에 노출될 데이터 숫자"
                                        />
                                    </div>
                                </div>
                                <!-- admin-block -->

                                <div class="no-admin-block">
                                    <h3 class="no-admin-title">
                                        <label for="depth1">1차 메뉴명</label>
                                    </h3>
                                    <div class="no-admin-content">
                                        <input
                                            type="text"
                                            name="depth1"
                                            id="depth1"
                                            class="no-input--detail"
                                            placeholder="1차 메뉴명"
                                        />
                                    </div>
                                </div>
                                <!-- admin-block -->

                                <div class="no-admin-block">
                                    <h3 class="no-admin-title">
                                        <label for="depth2">2차 메뉴명</label>
                                    </h3>
                                    <div class="no-admin-content">
                                        <input
                                            type="text"
                                            name="depth2"
                                            id="depth2"
                                            class="no-input--detail"
                                            placeholder="2차 메뉴명"
                                        />
                                    </div>
                                </div>
                                <!-- admin-block -->


                                <div class="no-admin-block">
                                    <h3 class="no-admin-title">
                                        <label for="depth3">3차 메뉴명</label>
                                    </h3>
                                    <div class="no-admin-content">
                                        <input
                                            type="text"
                                            name="depth3"
                                            id="depth3"
                                            class="no-input--detail"
                                            placeholder="3차 메뉴명"
                                        />
                                    </div>
                                </div>
                                <!-- admin-block -->

                                <div class="no-admin-block">
                                    <h3 class="no-admin-title">
                                        <label for="lnb_path">좌측 메뉴 파일경로</label>
                                    </h3>
                                    <div class="no-admin-content">
                                        <input
                                            type="text"
                                            name="lnb_path"
                                            id="lnb_path"
                                            class="no-input--detail"
                                            placeholder="좌측 메뉴 파일경로"
                                        />
                                    </div>
                                </div>
                                <!-- admin-block -->
								<?php 		
									for($i=1;$i<=$NO_EXTRA_FIELDS_COUNT;$i++): ?>
								<div class="no-admin-block">
                                    <h3 class="no-admin-title">
                                        <label for="title">추가필드<?=$i?></label>
                                    </h3>
                                    <div class="no-admin-content">
                                        <input
                                            type="text"
                                            name="extra_match_field<?=$i?>"
                                            id="extra_match_field<?=$i?>"
                                            class="no-input--detail"
                                            placeholder="추가필드<?=$i?>"
                                        />
                                    </div>
                                </div>	
								<?php endfor; ?>

                                


                                <div class="no-items-center center">
                                    <a
                                        href="./board.manage.list.php"
                                        class="no-btn no-btn--big no-btn--normal"
                                    >
                                        목록
                                    </a>
                                    <a
                                        href="javascript:void(0);"
                                        class="no-btn no-btn--big no-btn--main"
                                        onClick="doRegSave();"
                                    >
                                        확인
                                    </a>
                                </div>
                            </div>
                            <!-- card-body -->
                        </div>
                    </div>
                </section>
            </form>
        </main>

        

        <!-- Footer -->
        <script type="text/javascript" src="./js/board.manage.process.js?c=<?=$STATIC_ADMIN_JS_MODIFY_DATE?>"></script>
        <?
            include_once "../../inc/admin.footer.php";
        ?>
        
    </div>
</body>
</html>
