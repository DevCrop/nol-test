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
		<span class="no_sl">문자 관리
			<span class="icon-up-open-big"></span>
		</span>
		<ul class="no_sl_down">
			<li><a href="./sms.php" title="" <?=$lnbActive_1?>>문자 발송</a></li>
			<li><a href="./sms.php" title="" <?=$lnbActive_2?>>발송 내역</a></li>
		</ul>
	</div>