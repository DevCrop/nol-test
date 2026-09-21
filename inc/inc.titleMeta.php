<?php
$protocol = stripos($_SERVER['SERVER_PROTOCOL'], 'https') === 0 ? 'https://' : 'http://';

$NO_STATIC_TITLE = $SITEINFO_TITLE;
$NO_META_KEYWORDS = $SITEINFO_META_KEYWORDS;
$NO_META_DESCRIPTION = $SITEINFO_META_DESCRIPTION;

$NO_META_URL = $protocol . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
$NO_META_TWITTER_CARD = "Summary";
$NO_META_TWITTER_URL = $NO_META_URL;
$NO_META_TWITTER_TITLE = $NO_STATIC_TITLE;
$NO_META_TWITTER_DESCRIPTION = $NO_META_DESCRIPTION;
$NO_META_TWITTER_IMAGE = $NO_IS_SUBDIR . "/uploads/meta/" . ($SITEINFO_META_THUMB ?? '');
$NO_META_OG_URL = $protocol . $_SERVER['HTTP_HOST'];
$NO_META_OG_TYPE = "website";
$NO_META_OG_IMAGE = $NO_META_OG_URL . "/uploads/meta/" . ($SITEINFO_META_THUMB ?? '');
$NO_META_OG_SITE_NAME = $NO_STATIC_TITLE;
$NO_META_OG_LOCALE = "ko";
$NO_META_OG_TITLE = $NO_STATIC_TITLE;
$NO_META_OG_DESCRIPTION = $NO_META_DESCRIPTION;
$NO_META_OG_COUNTRY_NAME = "";
$NO_META_ITEMPROP_NAME = $NO_STATIC_TITLE;
$NO_META_ITEMPROP_IMAGE = "";							
$NO_META_ITEMPROP_URL = $NO_META_URL;
$NO_META_ITEMPROP_DESCRIPTION = $NO_META_DESCRIPTION;
$NO_META_ITEMPROP_KEYWORD = $NO_META_KEYWORDS;
$NO_META_SHORTCUT_ICON = $NO_IS_SUBDIR . "/uploads/meta/" . ($SITEINFO_META_FAVICON_ICO ?? '');
$NO_META_APPLE_TOUCH_ICON = "";



?>

<title><?= htmlspecialchars($PAGE_TITLE, ENT_QUOTES, 'UTF-8') ?></title>
<meta charset="UTF-8">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="autocomplete" content="off" />
<meta name="keywords" content="<?= htmlspecialchars($NO_META_KEYWORDS, ENT_QUOTES, 'UTF-8') ?>">
<meta name="description" content="<?= htmlspecialchars($NO_META_DESCRIPTION, ENT_QUOTES, 'UTF-8') ?>">
<meta name="image" content="<?= htmlspecialchars($NO_META_OG_IMAGE, ENT_QUOTES, 'UTF-8') ?>">
<meta name="robots" content="index, follow" />
<meta property="og:locale" content="ko_KR" />
<meta property="og:url" content="<?= htmlspecialchars($NO_META_OG_URL, ENT_QUOTES, 'UTF-8') ?>">
<meta property="og:image" content="<?= htmlspecialchars($NO_META_OG_IMAGE, ENT_QUOTES, 'UTF-8') ?>">
<meta property="og:type" content="website">
<meta property="og:site_name" content="<?= htmlspecialchars($NO_META_OG_SITE_NAME, ENT_QUOTES, 'UTF-8') ?>">
<meta property="og:title" content="<?= htmlspecialchars($NO_META_OG_TITLE, ENT_QUOTES, 'UTF-8') . ($NO_STATIC_SUBTITLE ?? '') ?>">
<meta property="og:description" content="<?= htmlspecialchars($NO_META_OG_DESCRIPTION, ENT_QUOTES, 'UTF-8') ?>">
<link rel="shortcut icon" href="<?= htmlspecialchars($NO_META_SHORTCUT_ICON, ENT_QUOTES, 'UTF-8') ?>">
<meta name="naver-site-verification" content="598f48a7b37b36f8679d357ca96caf36a1750767" />
<meta name="google-site-verification" content="S6zqIaNuSFt2w7Ck54uopSoz6EHkj4yiUkKqmNHZloE" />




