<!DOCTYPE html>
<html lang="ko">
<head>
<?

	include_once "../../../inc/lib/base.class.php";


	$depthnum = 9;
	$pagenum = 1;
	
	include_once "../../inc/admin.title.php";
	include_once "../../inc/admin.css.php";
	include_once "../../inc/admin.js.php";
?>
</head>

<body>
    <div class="no-wrap">
        <!-- Header -->
		<?
		include_once "../../inc/admin.header.php";
		?>

        <!-- Main -->
        <main class="no-app no-container">
            <!-- Drawer -->
            <?
                include_once "../../inc/admin.drawer.php";
            ?>
            <!-- Contents -->
            <form id="frm" name="frm" method="post">
			<input type="hidden" id="act" name="act" value="">
			<input type="hidden" id="kind" name="kind" value="">
			<input type="hidden" id="big" name="big" value="0">
			<input type="hidden" id="mid" name="mid" value="0">
			<input type="hidden" id="sml" name="sml" value="0">
			<input type="hidden" id="no" name="no" value="0">
			<input type="hidden" id="mode" name="mode" value="">
                <section class="no-content">
                    <!-- Page Title -->
                    <div class="no-toolbar">
                        <div class="no-toolbar-container no-flex-stack">
                            <div class="no-page-indicator">
								<h1 class="no-page-title">카테고리</h1>
								<div class="no-breadcrumb-container">
                                    <ul class="no-breadcrumb-list">
                                        <li class="no-breadcrumb-item">
                                            <span>대분류</span>
                                        </li>
                                        <li class="no-breadcrumb-item">
                                            <span>중분류</span>
                                        </li>
										<li class="no-breadcrumb-item">
                                            <span>소분류</span>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <!-- page indicator -->
                        </div>
                    </div>

                    <!-- Contents -->
                    <div class="no-content-container">
                        <div class="no-card">
							<form name ="" id ="">
								<!-- Category -->
								<ul class="no-category-select__con">
									<li>
										<span class="no-category__title">
											대분류
										</span>
										<select multiple id="category_big" name="category_big" class="no-category-select__select" size="20" onClick="getCategory('mid');">
											<option value="">대분류</option>
											<option value="">대분류01</option>
											<option value="">대분류02</option>
											<option value="">대분류03</option>
											<option value="">대분류04</option>
											<option value="">대분류05</option>
											<option value="">대분류06</option>
											<option value="">대분류07</option>
											<option value="">대분류08</option>
											<option value="">대분류09</option>
											<option value="">대분류10</option>
										</select>
										<div class="no-category-button__con">
											<button type="button" id="no-modal_btn1" class="no-category-button__button no-catg-btn" onClick="add('big');">등록</button>
											<button type="button" class="no-category-button__button no-catg-edit" onClick="edit('big');">수정</button>
											<button type="button" class="no-category-button__button no-category-button--delete" onClick="doDelete('big');">삭제</button>
											<button type="button" class="no-category-button__button no-category-button--delete" onClick="doDisplay('big','show');">노출</button>
											<button type="button" class="no-category-button__button no-category-button--delete" onClick="doDisplay('big','hide');">숨김</button>
											<button type="button" class="no-category-button__top" onClick="sortChange('big','up');">
												<i class='bx bxs-up-arrow'></i>
											</button>
											<button type="button" class="no-category-button__bot" onClick="sortChange('big','down');">
												<i class='bx bxs-down-arrow' ></i>
											</button>
											<button type="button" class="no-category-button__button no-category-button--delete" onClick="sortChangeUpdate('big');">저장</button>
										</div>
									</li>
									<li>
										<span class="no-category__title">
											중분류
										</span>
										<select multiple id="category_mid" name="category_mid" class="no-category-select__select" size="20" onClick="getCategory('sml');">
											<option value="">중분류</option>
											<option value="">중분류01</option>
											<option value="">중분류02</option>
											<option value="">중분류03</option>
											<option value="">중분류04</option>
											<option value="">중분류05</option>
										</select>
										<div class="no-category-button__con">
											<button type="button" id="no-modal_btn1" class="no-category-button__button no-catg-btn" onClick="add('mid');">등록</button>
											<button type="button" class="no-category-button__button no-catg-edit" onClick="edit('mid');">수정</button>
											<button type="button" class="no-category-button__button no-category-button--delete" onClick="doDelete('mid');">삭제</button>
											<button type="button" class="no-category-button__button no-category-button--delete" onClick="doDisplay('mid','show');">노출</button>
											<button type="button" class="no-category-button__button no-category-button--delete" onClick="doDisplay('mid','hide');">숨김</button>
											<button type="button" class="no-category-button__top" onClick="sortChange('mid','up');">
												<i class='bx bxs-up-arrow'></i>
											</button>
											<button type="button" class="no-category-button__bot" onClick="sortChange('mid','down');">
												<i class='bx bxs-down-arrow' ></i>
											</button>
											<button type="button" class="no-category-button__button no-category-button--delete" onClick="sortChangeUpdate('mid');">저장</button>
										</div>
									</li>
									<li>
										<span class="no-category__title">
											소분류
										</span>
										<select multiple id="category_sml" name="category_sml" class="no-category-select__select" size="20" onClick="getCategory('itm');">
											<option value="">소분류</option>
											<option value="">소분류01</option>
											<option value="">소분류02</option>
											<option value="">소분류03</option>
											<option value="">소분류04</option>
											<option value="">소분류05</option>
											<option value="">소분류06</option>
											<option value="">소분류07</option>
											<option value="">소분류08</option>
											<option value="">소분류09</option>
											<option value="">소분류10</option>
										</select>
										<div class="no-category-button__con">
											<button type="button" id="no-modal_btn1" class="no-category-button__button no-catg-btn" onClick="add('sml');">등록</button>
											<button type="button" class="no-category-button__button no-catg-edit" onClick="edit('sml');">수정</button>
											<button type="button" class="no-category-button__button no-category-button--delete" onClick="doDelete('sml');">삭제</button>
											<button type="button" class="no-category-button__button no-category-button--delete" onClick="doDisplay('sml','show');">노출</button>
											<button type="button" class="no-category-button__button no-category-button--delete" onClick="doDisplay('sml','hide');">숨김</button>
											<button type="button" class="no-category-button__top" onClick="sortChange('sml','up');">
												<i class='bx bxs-up-arrow'></i>
											</button>
											<button type="button" class="no-category-button__bot" onClick="sortChange('sml','down');">
												<i class='bx bxs-down-arrow' ></i>
											</button>
											<button type="button" class="no-category-button__button no-category-button--delete" onClick="sortChangeUpdate('sml');">저장</button>
										</div>
									</li>
									
									
									<li>
										<span class="no-category__title">
											상세분류
										</span>
										<select multiple id="category_itm" name="category_itm" class="no-category-select__select" size="20">
											<option value="">상세분류</option>
											<option value="">상세분류01</option>
											<option value="">상세분류02</option>
											<option value="">상세분류03</option>
											<option value="">상세분류04</option>
											<option value="">상세분류05</option>
											<option value="">상세분류06</option>
											<option value="">상세분류07</option>
											<option value="">상세분류08</option>
											<option value="">상세분류09</option>
											<option value="">상세분류10</option>
										</select>
										<div class="no-category-button__con">
											<button type="button" id="no-modal_btn1" class="no-category-button__button no-catg-btn" onClick="add('itm');">등록</button>
											<button type="button" class="no-category-button__button no-catg-edit" onClick="edit('itm');">수정</button>
											<button type="button" class="no-category-button__button no-category-button--delete" onClick="doDelete('itm');">삭제</button>
											<button type="button" class="no-category-button__button no-category-button--delete" onClick="doDisplay('itm','show');">노출</button>
											<button type="button" class="no-category-button__button no-category-button--delete" onClick="doDisplay('itm','hide');">숨김</button>
											<button type="button" class="no-category-button__top" onClick="sortChange('itm','up');">
												<i class='bx bxs-up-arrow'></i>
											</button>
											<button type="button" class="no-category-button__bot" onClick="sortChange('itm','down');">
												<i class='bx bxs-down-arrow' ></i>
											</button>
											<button type="button" class="no-category-button__button no-category-button--delete" onClick="sortChangeUpdate('itm');">저장</button>
										</div>
									</li>
									
								</ul>

								<!-- Modal -->
								<div class="no-modal__wrap" id="no-modal__lg">
									<div class="no-modal__close" id="no-modal__close" >
										<a href="#none" onClick="closeAddBox();"><i class='bx bx-x'></i></a>
									</div>
									<h3 class="no-modal__title">
										<span class="no-modal__name"></span>
										카테고리를 입력해주세요.
									</h3>
									<div class="no-modal__contents">
										<input type="text" name="categoryName" class="no-modal__input" id="categoryName" value="">
										<button type="button" class="no-modal__button" onClick="doAddSave();">
											저장
										</button>
									</div>
								</div>


							</form>
                        </div>
                        <!-- card -->
                    </div>
                    <!-- contents -->
                </section>
            </form>
        </main>

       

        <!-- Footer -->
        <script type="text/javascript" src="./js/process.js?c=<?=$STATIC_ADMIN_JS_MODIFY_DATE?>"></script>
		<script type="text/javascript" src="./js/modal.js?c=<?=$STATIC_ADMIN_JS_MODIFY_DATE?>"></script>
        <?
            include_once "../../inc/admin.footer.php";
        ?>
    </div>
</body>
</html>