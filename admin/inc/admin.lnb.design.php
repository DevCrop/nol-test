<? 

	$lnbActive_1 = "";
	$lnbActive_2 = "";

	if ($pagenum == "1" || $pagenum == 1) {
		$lnbActive_1 = "class=\"no_sl_down_active\"";
	} else if ($pagenum == "2" || $pagenum == 2) {
		$lnbActive_2 = "class=\"no_sl_down_active\"";
	} 

?>
	
	<div class="no_sidemenu_list">
		<span class="no_sl">디자인 관리
			<span class="icon-up-open-big"></span>
		</span>
		<ul class="no_sl_down">
			<li><a href="./banner.list.php" title="" <?=$lnbActive_1?>>배너 </a></li>
			<li><a href="./popup.list.php" title="" <?=$lnbActive_2?>>팝업 </a></li>
		</ul>
	</div>