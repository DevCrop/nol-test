<?php 

use Core\Application;

require_once dirname(__DIR__).'/config/setup.php';
require_once dirname(__DIR__).'/config/functions.php';
require_once dirname(__DIR__).'/config/helpers.php';


spl_autoload_register(function ($class) {
    $path = ROOT_PATH . '/src/' . str_replace('\\', '/', $class) . '.php';

    
    if (file_exists($path)) {
        require_once $path;
    }
});

require_once dirname(__DIR__).'/config/utils.php';
require_once dirname(__DIR__).'/config/variables.php';
require_once dirname(__DIR__).'/config/configuration.php';

$app = new Application(ROOT_PATH);

$path = trim($app->request()->path(), '/');
$segments = $path === '' ? [] : array_values(array_filter(explode('/', $path), 'strlen'));

$idx = (isset($segments[0]) && in_array($segments[0], locales(), true)) ? 1 : 0;
$firstAfterLocale = $segments[$idx] ?? null;
$isAdmin = (strtolower((string)$firstAfterLocale) === 'admin');

if (!$isAdmin) {
    include_once ROOT_PATH.'/routes/web.php';
    include_once ROOT_PATH.'/bootstrap/menu.php';
}
