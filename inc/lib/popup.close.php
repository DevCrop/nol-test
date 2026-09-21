<?php

	// URL 파라미터에서 'uid' 값을 가져옵니다.
	$id_list = $_GET['uid'] ?? '';

	// 'uid'가 제공되지 않은 경우 종료합니다.
	if (empty($id_list)) {
		exit;
	}

	// 콤마로 구분된 'uid' 값을 배열로 변환합니다.
	$id_list = explode(',', $id_list);

	foreach ($id_list as $uid) {
		// 각 'uid'에 대해 쿠키를 설정합니다.
		setcookie(
			"AuthPopupClose_" . trim($uid),
			"Y",
			[
				'expires' => time() + 3600 * 24,
				'path' => '/',
				'domain' => '.' . str_replace("www.", "", $_SERVER['HTTP_HOST'])
			]
		);
	}

	// 부모 문서에서 팝업 요소를 숨깁니다.
	echo "<script>parent.document.querySelector('.no-popup').style.display = 'none';</script>";

?>
