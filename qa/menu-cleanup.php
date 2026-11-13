<?php
/** Pure PHP 7.4 menu acceptance; no DB, HTTP, session or SMTP. */
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
$root = dirname(__DIR__);
$_SERVER['DOCUMENT_ROOT'] = $root;
$_SERVER['REQUEST_URI'] = $argv[1] ?? '/';
$_SERVER['SCRIPT_NAME'] = (string)parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$_SERVER['PHP_SELF'] = $_SERVER['SCRIPT_NAME'];
parse_str((string)parse_url($_SERVER['REQUEST_URI'], PHP_URL_QUERY), $_GET);
$NO_IS_SUBDIR = '';
require $root.'/inc/lib/menu/menu.init.php';
$menu = new Menu();
$fail = 0;
$check = static function (bool $ok, string $name) use (&$fail): void {
    echo ($ok ? 'PASS ' : 'FAIL ').$name.PHP_EOL;
    if (!$ok) $fail++;
};
$check(!function_exists('show'), 'unused debugging function removed');
$check(!is_file($root.'/inc/lib/base.init.php'), 'standalone upload initializer absent');
$check(is_array(getJSON($root.'/json/menu.json')), 'menu JSON remains readable');
$check(is_array($menu->getMenuItems()) && count($menu->getMenuItems()) > 0, 'menu hierarchy constructed');
$check(is_string($menu->getPageTitle()) && strpos($menu->getPageTitle(), '블루스퀘어') !== false, 'page title resolved: '.$_SERVER['REQUEST_URI']);
$check(checkBoardLocale(['ko'=>['10']]) === (isset($_GET['board_no']) && $_GET['board_no'] === '10' ? 'ko' : null), 'board locale helper preserved');
$check(getImage('/sample.png') === 'style="background-image: url(/resource/images/sample.png)"', 'image path helper preserved');
ob_start(); getLordIcon('sample.json'); $icon = ob_get_clean();
$check(strpos($icon, 'src="/resource/vendor/lordicon/json/sample.json"') !== false && strpos($icon, 'trigger="hover"') !== false, 'icon helper preserved');
echo $fail ? 'FAIL '.$fail.PHP_EOL : 'OK 8'.PHP_EOL;
exit($fail ? 1 : 0);
