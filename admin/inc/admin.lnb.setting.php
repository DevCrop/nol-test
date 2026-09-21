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
	
	<div class="no_sidemenu_list">
		<span class="no_sl">관리자 설정
			<span class="icon-up-open-big"></span>
		</span>
		<ul class="no_sl_down">
			<li><a href="./setting.php" title="" <?=$lnbActive_1?>>비밀번호 변경</a></li>
			<li><a href="./site.config.php" title="" <?=$lnbActive_2?>>사이트 정보관리</a></li>
			<li><a href="./site.data.list.php" title="" <?=$lnbActive_3?>>사이트 데이터 관리</a></li>
		</ul>
	</div>