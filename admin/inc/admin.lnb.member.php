<? 

	$lnbActive_1 = "";
	$lnbActive_2 = "";

	if ($pagenum == "1" || $pagenum == 1) {
		$lnbActive_1 = "active";
	} else if ($pagenum == "2" || $pagenum == 2) {
		$lnbActive_2 = "active";
	} 

?>

<div class="no-admin-tab">
    <ul class="no-admin-tab__list">
        <li class="no-admin-tab__item <?=$lnbActive_1?>">
            <a href="./member.list.php" class="no-btn ">회원관리</a>
        </li>
		<!--
        <li class="no-admin-tab__item <?=$lnbActive_2?>">
            <a href="./member.level.php" class="no-btn ">등급관리</a>
        </li>-->
    </ul>
</div>