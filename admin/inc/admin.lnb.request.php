<? 

	$lnbActive_1 = "";
	
	if ($pagenum == "1" || $pagenum == 1) {
		$lnbActive_1 = "class=\"no_sl_down_active\"";
	} 

?>
			
	<!-- 사이드 메뉴 영역 -->
	<div class="no_sidemenu_list">
		<span class="no_sl">상담 문의
			<span class="icon-up-open-big"></span>
		</span>
		<ul class="no_sl_down">
			<li><a href="./request.list.php" title="" <?=$lnbActive_1?>>상담 문의</a></li>
		</ul>
	</div>