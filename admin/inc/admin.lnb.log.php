<? 

	$lnbActive_1 = "";
	$lnbActive_2 = "";
	$lnbActive_3 = "";
	$lnbActive_4 = "";

	if ($pagenum == "1" || $pagenum == 1) {
		$lnbActive_1 = "class=\"no_sl_down_active\"";
	} else if ($pagenum == "2" || $pagenum == 2) {
		$lnbActive_2 = "class=\"no_sl_down_active\"";
	} else if ($pagenum == "2" || $pagenum == 3) {
		$lnbActive_3 = "class=\"no_sl_down_active\"";
	} else if ($pagenum == "2" || $pagenum == 4) {
		$lnbActive_4 = "class=\"no_sl_down_active\"";
	} 

?>
	
	<div class="no_sidemenu_list">
		<span class="no_sl">접속통계
			<span class="icon-up-open-big"></span>
		</span>
		<ul class="no_sl_down">
			<li><a href="./log.day.php" title="" <?=$lnbActive_1?>>일자별</a></li>
			<li><a href="./log.time.php" title="" <?=$lnbActive_2?>>시간별</a></li>
			<li><a href="./log.month.php" title="" <?=$lnbActive_3?>>월별</a></li>
			<li><a href="./log.year.php" title="" <?=$lnbActive_4?>>년도별</a></li>
		</ul>
	</div>