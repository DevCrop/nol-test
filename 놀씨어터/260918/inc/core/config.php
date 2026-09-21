<?php

if (!defined('ROOT')) {
    define('ROOT', dirname(__DIR__, 2));
}

if (!defined('BASE_DIR')) {
    define('BASE_DIR', '');
}

if (!defined('DS')) {
    define('DS', '/');
}

$NO_IS_SUBDIR = BASE_DIR;

if (!defined('CACHE_CONTROL')) {
    define('CACHE_CONTROL', true);
}

if (!defined('BASE_IMG_DIR')) {
    define('BASE_IMG_DIR', BASE_DIR . DS . 'resource' . DS . 'images');
}

if (!defined('BASE_VENDOR_DIR')) {
    define('BASE_VENDOR_DIR', BASE_DIR . DS . 'resource' . DS . 'vendor');
}
