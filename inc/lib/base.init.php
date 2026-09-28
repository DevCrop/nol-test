<?php
	include_once __DIR__ . "/base.class.php";
	ini_set('display_errors', '0');
	ini_set('display_startup_errors', '0');
	ini_set('log_errors', '1');
	error_reporting(E_ALL);

	$path = $NO_PROJECT_ROOT . "/upload";
	if (is_dir($path)) {
		echo "ok";
	} else {
		if (mkdir($path, 0777, true)) {
			echo "maked";
		} else {
			die('Failed to create folders...');
		}
	}

	// 추가적인 확인을 위해 디렉토리 존재 여부 재확인
	if (!file_exists($path)) {
		if (!mkdir($path, 0777, true)) {
			die('Failed to create folders...');
		}
	}
?>
