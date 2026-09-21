<?php
	include_once $_SERVER['DOCUMENT_ROOT'] . "/inc/lib/base.class.php";
	ini_set('display_errors', 'on');
	error_reporting(-1);

	$path = $_SERVER['DOCUMENT_ROOT'] . "/upload";
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
