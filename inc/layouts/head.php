<?php

$MENU = new Menu();
$APP_NAME = $MENU->getSiteName();
$PAGE_TITLE = $MENU->getPageTitle();
$MENU_ITEMS = $MENU->getMenuItems();
$CUR_PAGE = $MENU->getCurPage();
$CUR_PAGE_LIST = $MENU->getCurPageList();
$CUR_PAGE_INDEX = $MENU->getCurPageIndex();

// $LOCALE이 정의되지 않았을 경우 기본값 설정
$LOCALE = $LOCALE ?? 'en';
?>

<!DOCTYPE html>
<html lang="<?= htmlspecialchars($LOCALE, ENT_QUOTES, 'UTF-8') ?>">

<head>

<?php
$theme = $_COOKIE['theme'] ?? 'dark';
?>

<script>
	const currentTheme = "<?= htmlspecialchars($theme, ENT_QUOTES, 'UTF-8') ?>";
	document.documentElement.setAttribute("data-theme", currentTheme);
</script>

<!-- Google tag (gtag.js) --> 
<script async src="https://www.googletagmanager.com/gtag/js?id=G-MX9TKDTN9H"></script> 
<script> window.dataLayer = window.dataLayer || []; function gtag(){dataLayer.push(arguments);} gtag('js', new Date()); gtag('config', 'G-MX9TKDTN9H'); </script>

<script type="text/javascript" src="//wcs.naver.net/wcslog.js"></script>
<script type="text/javascript">
	if(!wcs_add) var wcs_add = {};
	wcs_add["wa"] = "12b4cfaa37cbed0";
	if(window.wcs) {
	  wcs_do();
	}
</script>



<?php


include_once $STATIC_ROOT . '/inc/inc.titleMeta.php';
include_once $STATIC_ROOT . '/inc/inc.css.php';
include_once $STATIC_ROOT . '/inc/inc.script.php';
?>

