
<?php
include_once "../../../../inc/lib/base.class.php";
include_once "../../../lib/admin.check.ajax.php";

$pdo = DB::getInstance();
$mode = $_POST['mode'] ?? '';
$b_location = $_POST['b_location'] ?? ''; // b_location 기본값 설정

if ($mode === "save") {
    try {
        $b_loc = $_POST['b_loc'] ?? '';
        $b_link = $_POST['b_link'] ?? '';
        $b_target = $_POST['b_target'] ?? '';
        $b_view = $_POST['b_view'] ?? '';
        $b_title = $_POST['b_title'] ?? '';

        $b_none_view = $_POST['b_none_view'] ?? '';
        $b_none_limit = $_POST['b_none_limit'] ?? '';
        $b_sdate = $_POST['b_sdate'] ?? '';

        $b_sdate_view = $_POST['b_sdate_view'] ?? '';
        $b_edate_view = $_POST['b_edate_view'] ?? '';

        $b_edate = $_POST['b_edate'] ?? '';
        $b_desc = $_POST['b_desc'] ?? '';
        $b_contents = $_POST['content'] ?? '';

        $uploads_dir = $UPLOAD_DIR_BANNER;

        $uploadResult = isset($_FILES['b_img']) ? imageUpload($uploads_dir, $_FILES['b_img'], '', false) : null;
        $savedFile = $uploadResult['saved'] ?? '';

        $uploadResult2 = isset($_FILES['b_img_mobile']) ? imageUpload($uploads_dir, $_FILES['b_img_mobile'], '', false) : null;
        $savedFile2 = $uploadResult2['saved'] ?? '';

        $uploadResult3 = isset($_FILES['b_poster_img']) ? imageUpload($uploads_dir, $_FILES['b_poster_img'], '', false) : null;
        $savedFile3 = $uploadResult3['saved'] ?? '';

        // 최대 index 조회
        $query = "SELECT IFNULL(MAX(b_idx) + 1, 1) AS maxcnt FROM nb_banner WHERE sitekey = :sitekey AND b_loc = :b_loc AND b_location = :b_location";
        $stmt = $pdo->prepare($query);
        $stmt->execute(['sitekey' => $NO_SITE_UNIQUE_KEY, 'b_loc' => $b_loc, 'b_location' => $b_location]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        $maxcnt = $data['maxcnt'] ?? 1;

        // 배너 추가
		$query = "INSERT INTO nb_banner 
			(sitekey, b_loc, b_img, b_img_mobile, b_poster_img, b_link, b_target, b_view, b_title, b_idx, 
			b_none_limit, b_none_view, b_sdate, b_edate, b_sdate_view, b_edate_view, b_rdate, b_desc, b_contents, b_location) 
		VALUES 
			(:sitekey, :b_loc, :b_img, :b_img_mobile, :b_poster_img, :b_link, :b_target, :b_view, :b_title, :b_idx, 
			:b_none_limit, :b_none_view, :b_sdate, :b_edate, :b_sdate_view, :b_edate_view, NOW(), :b_desc, :b_contents, :b_location)";

        $stmt = $pdo->prepare($query);

		$result = $stmt->execute([
			'sitekey' => $NO_SITE_UNIQUE_KEY,
			'b_loc' => $b_loc,
			'b_img' => $savedFile,
			'b_img_mobile' => $savedFile2,
			'b_poster_img' => $savedFile3,
			'b_link' => $b_link,
			'b_target' => $b_target,
			'b_view' => $b_view,
			'b_title' => $b_title,
			'b_idx' => $maxcnt,
			'b_none_limit' => $b_none_limit,
			'b_none_view' => $b_none_view, // ✅ 바인딩 추가
			'b_sdate' => $b_sdate,
			'b_edate' => $b_edate,
			'b_sdate_view' => $b_sdate_view,
			'b_edate_view' => $b_edate_view,
			'b_desc' => $b_desc,
			'b_contents' => $b_contents,
			'b_location' => $b_location,
		]);


        echo json_encode(["result" => $result ? "success" : "fail", "msg" => $result ? "정상적으로 등록되었습니다." : "처리 중 문제가 발생하였습니다. [Error-DB]"]);
    } catch (Exception $e) {
        echo json_encode(["result" => "fail", "msg" => "Error: " . blue_safe_error($e)]);
    }

} elseif ($mode === "edit") {
    $no = $_POST['no'] ?? '';
    $b_loc = $_POST['b_loc'] ?? '';
    $b_link = $_POST['b_link'] ?? '';
    $b_target = $_POST['b_target'] ?? '';
    $b_view = $_POST['b_view'] ?? '';
    $b_title = $_POST['b_title'] ?? '';
    $b_idx = $_POST['b_idx'] ?? '';
    $b_none_view = $_POST['b_none_view'] ?? '';
    $b_none_limit = $_POST['b_none_limit'] ?? '';
    $b_sdate = $_POST['b_sdate'] ?? '';
    $b_edate = $_POST['b_edate'] ?? '';
    $b_sdate_view = $_POST['b_sdate_view'] ?? '';
    $b_edate_view = $_POST['b_edate_view'] ?? '';
    $b_desc = $_POST['b_desc'] ?? '';
    $b_contents = $_POST['content'] ?? '';

		/// 기존 데이터 조회
	$query = "SELECT b_img, b_img_mobile, b_poster_img FROM nb_banner WHERE no = :no";
	$stmt = $pdo->prepare($query);
	$stmt->execute(['no' => $no]);
	$data = $stmt->fetch(PDO::FETCH_ASSOC);

	if (!$data) {
		echo json_encode(["result" => "fail", "msg" => "정보를 찾을 수 없습니다."]);
		exit;
	}

	$uploads_dir = $UPLOAD_DIR_BANNER;

	// 새 이미지 업로드 처리
	$uploadResult = isset($_FILES['b_img']) && $_FILES['b_img']['error'] === UPLOAD_ERR_OK 
		? imageUpload($uploads_dir, $_FILES['b_img'], '', false) 
		: null;
	$savedFile = $uploadResult['saved'] ?? $data['b_img']; // 기존 이미지 유지

	$uploadResult2 = isset($_FILES['b_img_mobile']) && $_FILES['b_img_mobile']['error'] === UPLOAD_ERR_OK
		? imageUpload($uploads_dir, $_FILES['b_img_mobile'], '', false)
		: null;
	$savedFile2 = $uploadResult2['saved'] ?? $data['b_img_mobile']; // 기존 이미지 유지

	// ✅ 업데이트 쿼리 실행 전에 기존 파일 삭제 조건 분기 처리
	$query = "UPDATE nb_banner SET 
				b_loc = :b_loc, b_img = :b_img, b_img_mobile = :b_img_mobile,
				b_link = :b_link, b_target = :b_target, b_view = :b_view, 
				b_title = :b_title, b_idx = :b_idx, b_none_limit = :b_none_limit, 
				b_none_view = :b_none_view, b_sdate = :b_sdate, b_edate = :b_edate, 
				b_sdate_view = :b_sdate_view, b_edate_view = :b_edate_view, b_desc = :b_desc, 
				b_contents = :b_contents, b_location = :b_location 
			  WHERE no = :no";

	$stmt = $pdo->prepare($query);
	$result = $stmt->execute([
		'b_loc' => $b_loc, 
		'b_img' => $savedFile, 
		'b_img_mobile' => $savedFile2, 
		'b_link' => $b_link, 
		'b_target' => $b_target, 
		'b_view' => $b_view,
		'b_title' => $b_title, 
		'b_idx' => $b_idx, 
		'b_none_limit' => $b_none_limit, 
		'b_none_view' => $b_none_view, 
		'b_sdate' => $b_sdate,
		'b_edate' => $b_edate,
		'b_sdate_view' => $b_sdate_view,
		'b_edate_view' => $b_edate_view,
		'b_desc' => $b_desc, 
		'b_contents' => $b_contents,
		'b_location' => $b_location, 
		'no' => $no
	]);

	// ✅ 기존 파일 삭제 (업로드 성공 시에만)
	if ($result) {
		if ($uploadResult && $savedFile !== $data['b_img'] && !empty($data['b_img']) && file_exists($data['b_img'])) {
			unlink($data['b_img']);
		}
		if ($uploadResult2 && $savedFile2 !== $data['b_img_mobile'] && !empty($data['b_img_mobile']) && file_exists($data['b_img_mobile'])) {
			unlink($data['b_img_mobile']);
		}
	}

	echo json_encode(["result" => $result ? "success" : "fail", "msg" => $result ? "정상적으로 수정되었습니다." : "처리 중 문제가 발생하였습니다. [Error-DB]"]);

} elseif ($mode === "delete") {
    $no = $_POST['no'] ?? '';

    $query = "DELETE FROM nb_banner WHERE no = :no";
    $stmt = $pdo->prepare($query);
    $result = $stmt->execute(['no' => $no]);

    echo json_encode(["result" => $result ? "success" : "fail", "msg" => $result ? "정상적으로 삭제되었습니다." : "삭제 중 오류 발생"]);
}

?>


