<? 

	$lnbActive_1 = "";
	$lnbActive_2 = "";
	$lnbActive_3 = "";

	if ($pagenum == "1" || $pagenum == 1) {
		$lnbActive_1 = "class=\"no_sl_down_active\"";
	} else if ($pagenum == "2" || $pagenum == 2) {
		$lnbActive_2 = "class=\"no_sl_down_active\"";
	} else if ($pagenum == "3" || $pagenum == 3) {
		$lnbActive_3 = "class=\"no_sl_down_active\"";
	} 

?>
			
	<!-- 사이드 메뉴 영역 -->
	<div class="no_sidemenu_list">
		<span class="no_sl">게시판 관리
			<span class="icon-up-open-big"></span>
		</span>
		<ul class="no_sl_down">
			<? if ($NO_ADMIN_LNB_BOARD_MENU_OPEN) {?>
			<li><a href="./board.manage.list.php" title="" <?=$lnbActive_1?>>게시판 관리</a></li>
			<? } ?>
			<li><a href="./board.list.php" title="" <?=$lnbActive_2?>>게시글 관리</a></li>
			<? if ($NO_ADMIN_LNB_BOARD_MENU_ROLE_OPEN) {?>
			<li><a href="./board.role.php" title="" <?=$lnbActive_3?>>게시판 권한관리</a></li>
			<? } ?>
		</ul>
	</div>