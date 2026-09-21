<?php
include_once "../../../../inc/lib/base.class.php";
include_once "../../../lib/admin.check.ajax.php";

$connect = DB::getInstance(); // Initialize PDO instance
$mode = $_POST['mode'];

if ($mode == "save") {
		try {
			// PDO 인스턴스 가져오기
			$pdo = DB::getInstance();

			// 변수 설정
			$title = $_POST['title'];
			$skin = $_POST['skin'];
			$content = $_POST['content'];
			$view_yn = $_POST['view_yn'];
			$secret_yn = $_POST['secret_yn'];
			$list_size = isset($_POST['list_size']) && $_POST['list_size'] !== '' ? (int)$_POST['list_size'] : $BOARD_DEFAULT_LIST_SIZE;
			$fileattach_yn = $_POST['fileattach_yn'];
			$fileattach_cnt = $_POST['fileattach_cnt'] ?? 0;
			$comment_yn = $_POST['comment_yn'];
			$category_yn = $_POST['category_yn'];
			$depth1 = $_POST['depth1'];
			$depth2 = $_POST['depth2'];
			$depth3 = $_POST['depth3'];
			$lnb_path = $_POST['lnb_path'];

			// 추가 필드
			$extra_fields = [];
			for ($i = 1; $i <= 15; $i++) {
				$extra_fields[] = $_POST["extra_match_field{$i}"];
			}


			// 파일 업로드 처리
			$uploads_dir = $UPLOAD_DIR_BOARD;
			$uploadResult = imageUpload($uploads_dir, $_FILES['top_banner_image'], $origin_file, false);
			$savedFile = $uploadResult['saved'];

			// SQL 쿼리 준비
			$query = "INSERT INTO nb_board_manage (
							sitekey, title, skin, regdate, top_banner_image, contents, view_yn, secret_yn, 
							list_size, fileattach_yn, fileattach_cnt, comment_yn, depth1, depth2, depth3, lnb_path, category_yn,
							extra_match_field1, extra_match_field2, extra_match_field3, extra_match_field4, extra_match_field5,
							extra_match_field6, extra_match_field7, extra_match_field8, extra_match_field9, extra_match_field10,
							extra_match_field11, extra_match_field12, extra_match_field13, extra_match_field14, extra_match_field15
						) VALUES (
							:sitekey, :title, :skin, NOW(), :top_banner_image, :contents, :view_yn, :secret_yn, 
							:list_size, :fileattach_yn, :fileattach_cnt, :comment_yn, :depth1, :depth2, :depth3, :lnb_path, :category_yn,
							:extra1, :extra2, :extra3, :extra4, :extra5, :extra6, :extra7, :extra8, :extra9, :extra10,
							:extra11, :extra12, :extra13, :extra14, :extra15
						)";
			
			// 쿼리 실행
			$stmt = $pdo->prepare($query);
			$result = $stmt->execute([
				':sitekey' => $NO_SITE_UNIQUE_KEY,
				':title' => $title,
				':skin' => $skin,
				':top_banner_image' => $savedFile,
				':contents' => $content,
				':view_yn' => $view_yn,
				':secret_yn' => $secret_yn,
				':list_size' => $list_size,
				':fileattach_yn' => $fileattach_yn,
				':fileattach_cnt' => $fileattach_cnt,
				':comment_yn' => $comment_yn,
				':depth1' => $depth1,
				':depth2' => $depth2,
				':depth3' => $depth3,
				':lnb_path' => $lnb_path,
				':category_yn' => $category_yn,
				':extra1' => $extra_fields[0],
				':extra2' => $extra_fields[1],
				':extra3' => $extra_fields[2],
				':extra4' => $extra_fields[3],
				':extra5' => $extra_fields[4],
				':extra6' => $extra_fields[5],
				':extra7' => $extra_fields[6],
				':extra8' => $extra_fields[7],
				':extra9' => $extra_fields[8],
				':extra10' => $extra_fields[9],
				':extra11' => $extra_fields[10],
				':extra12' => $extra_fields[11],
				':extra13' => $extra_fields[12],
				':extra14' => $extra_fields[13],
				':extra15' => $extra_fields[14]
			]);

			// 성공 메시지
			if ($result) {
				echo json_encode(["result" => "success", "msg" => "정상적으로 등록되었습니다."]);
			} else {
				echo json_encode(["result" => "fail", "msg" => "처리 중 문제가 발생하였습니다.[Error-DB]"]);
			}
		} catch (Exception $e) {
			// 예외 처리
			echo json_encode(["result" => "fail", "msg" => "처리 중 문제가 발생하였습니다: " . blue_safe_error($e)]);
		}

} elseif ($mode == "edit") {
	try {
		$no = $_POST['no'];
		$title = $_POST['title'];
		$skin = $_POST['skin'];
		$content = $_POST['content'];
		$view_yn = $_POST['view_yn'];
		$secret_yn = $_POST['secret_yn'];
		$list_size = isset($_POST['list_size']) && $_POST['list_size'] !== '' ? (int)$_POST['list_size'] : $BOARD_DEFAULT_LIST_SIZE;
		$fileattach_yn = $_POST['fileattach_yn'];
		$fileattach_cnt = $_POST['fileattach_cnt'] ?? 0;
		$comment_yn = $_POST['comment_yn'];
		$category_yn = $_POST['category_yn'];
		$depth1 = $_POST['depth1'];
		$depth2 = $_POST['depth2'];
		$depth3 = $_POST['depth3'];
		$lnb_path = $_POST['lnb_path'];

		$extra_match_fields = [];
		for ($i = 1; $i <= 15; $i++) {
			$extra_match_fields[] = $_POST["extra_match_field$i"] ?? '';
		}

		$uploads_dir = $UPLOAD_DIR_BOARD;
		$origin_file = ''; 
		$uploadResult = imageUpload($uploads_dir, $_FILES['top_banner_image'] ?? null, $origin_file, false);
		$savedFile = $uploadResult['saved'] ?? '';

		// 기본 업데이트 쿼리 문자열
		$query = "UPDATE nb_board_manage SET 
			title = :title, skin = :skin, contents = :contents, view_yn = :view_yn, secret_yn = :secret_yn, 
			list_size = :list_size, fileattach_yn = :fileattach_yn, fileattach_cnt = :fileattach_cnt, 
			comment_yn = :comment_yn, depth1 = :depth1, depth2 = :depth2, depth3 = :depth3, lnb_path = :lnb_path, 
			category_yn = :category_yn, extra_match_field1 = :extra1, extra_match_field2 = :extra2, extra_match_field3 = :extra3, 
			extra_match_field4 = :extra4, extra_match_field5 = :extra5, extra_match_field6 = :extra6, extra_match_field7 = :extra7, 
			extra_match_field8 = :extra8, extra_match_field9 = :extra9, extra_match_field10 = :extra10, extra_match_field11 = :extra11, 
			extra_match_field12 = :extra12, extra_match_field13 = :extra13, extra_match_field14 = :extra14, 
			extra_match_field15 = :extra15";

		// top_banner_image가 있을 경우 쿼리 및 파라미터에 추가
		if ($savedFile) {
			$query .= ", top_banner_image = :top_banner_image";
		}
		$query .= " WHERE no = :no";

		$stmt = $connect->prepare($query);

		$params = [
			':title' => $title,
			':skin' => $skin,
			':contents' => $content,
			':view_yn' => $view_yn,
			':secret_yn' => $secret_yn,
			':list_size' => $list_size,
			':fileattach_yn' => $fileattach_yn,
			':fileattach_cnt' => $fileattach_cnt,
			':comment_yn' => $comment_yn,
			':depth1' => $depth1,
			':depth2' => $depth2,
			':depth3' => $depth3,
			':lnb_path' => $lnb_path,
			':category_yn' => $category_yn,
			':no' => $no,
		];

		foreach ($extra_match_fields as $index => $field) {
			$params[":extra" . ($index + 1)] = $field;
		}

		// top_banner_image 파라미터를 추가
		if ($savedFile) {
			$params[':top_banner_image'] = $savedFile;
		}

		$result = $stmt->execute($params);

		echo json_encode([
			"result" => $result ? "success" : "fail",
			"msg" => $result ? "정상적으로 수정되었습니다." : "처리 중 문제가 발생하였습니다.[Error-DB]"
		]);

	} catch (PDOException $e) {
		echo json_encode([
			"result" => "fail",
			"msg" => "데이터베이스 오류가 발생하였습니다: " . blue_safe_error($e)
		]);
	} catch (Exception $e) {
		echo json_encode([
			"result" => "fail",
			"msg" => "처리 중 문제가 발생하였습니다: " . blue_safe_error($e)
		]);
	}


} elseif ($mode == "delete") {

	$no = $_POST['no'];

	try {
		// 데이터베이스 연결
		$connect = DB::getInstance();
		
		// 삭제할 파일명을 가져오는 쿼리
		$query = "SELECT top_banner_image FROM nb_board_manage WHERE no = :no";
		$stmt = $connect->prepare($query);
		$stmt->execute([':no' => $no]);
		$data = $stmt->fetch(PDO::FETCH_ASSOC);

		// 파일 정보가 없을 경우 에러 메시지 반환
		if (!$data) {
			echo json_encode(["result" => "fail", "msg" => "정보를 찾을 수 없습니다"]);
			exit;
		}

		// 이미지 파일 삭제
		$filename = $data['top_banner_image'];
		if ($filename) {
			$filePath = $UPLOAD_DIR_BOARD . "/" . $filename;
			
			if (file_exists($filePath) && !unlink($filePath)) {
				echo json_encode(["result" => "fail", "msg" => "이미지 파일 삭제에 실패했습니다."]);
				exit;
			}
		}

		// 데이터베이스에서 해당 레코드 삭제
		$query = "DELETE FROM nb_board_manage WHERE no = :no";
		$stmt = $connect->prepare($query);
		$result = $stmt->execute([':no' => $no]);

		echo json_encode([
			"result" => $result ? "success" : "fail",
			"msg" => $result ? "정상적으로 삭제되었습니다." : "데이터 삭제에 실패했습니다."
		]);
	} catch (PDOException $e) {
		// PDO 예외 처리
		echo json_encode(["result" => "fail", "msg" => "데이터베이스 오류: " . blue_safe_error($e)]);
	} catch (Exception $e) {
		// 기타 예외 처리
		echo json_encode(["result" => "fail", "msg" => "오류가 발생했습니다: " . blue_safe_error($e)]);
	}


} elseif ($mode == "category.add") {
    $board_no = $_POST['board_no'];
	$name = $_POST['name'];

	try {
		// 데이터베이스 연결
		$connect = DB::getInstance();

		// `sort_no`를 가져오는 쿼리 실행
		$query = "SELECT IFNULL(MAX(sort_no), 0) + 1 AS sort_no FROM nb_board_category WHERE sitekey = :sitekey AND board_no = :board_no";
		$stmt = $connect->prepare($query);
		$stmt->execute([':sitekey' => $NO_SITE_UNIQUE_KEY, ':board_no' => $board_no]);
		$data = $stmt->fetch(PDO::FETCH_ASSOC);

		// 데이터가 없을 경우 에러 메시지 반환
		if (!$data) {
			echo json_encode(["result" => "fail", "msg" => "정보를 찾을 수 없습니다"]);
			exit;
		}

		// `sort_no` 가져오기
		$sort_no = $data['sort_no'];

		// `nb_board_category` 테이블에 데이터 삽입
		$query = "INSERT INTO nb_board_category (sitekey, board_no, name, sort_no) VALUES (:sitekey, :board_no, :name, :sort_no)";
		$stmt = $connect->prepare($query);
		$result = $stmt->execute([
			':sitekey' => $NO_SITE_UNIQUE_KEY,
			':board_no' => $board_no,
			':name' => $name,
			':sort_no' => $sort_no
		]);

		// 결과 반환
		echo json_encode([
			"result" => $result ? "success" : "fail",
			"msg" => $result ? "정상적으로 등록되었습니다." : "처리 중 문제가 발생하였습니다. [Error-DB]"
		]);

	} catch (PDOException $e) {
		// PDO 예외 처리
		echo json_encode(["result" => "fail", "msg" => "데이터베이스 오류: " . blue_safe_error($e)]);
	} catch (Exception $e) {
		// 일반 예외 처리
		echo json_encode(["result" => "fail", "msg" => "오류가 발생했습니다: " . blue_safe_error($e)]);
	}

} elseif ($mode == "category.save") {
    $no = $_POST['no'];
    $name = urldecode($_POST['name']);
    $sort_no = $_POST['sort_no'];

    try {
        $query = "UPDATE nb_board_category SET name = :name, sort_no = :sort_no WHERE no = :no";
        $stmt = $connect->prepare($query);
        $result = $stmt->execute([':name' => $name, ':sort_no' => $sort_no, ':no' => $no]);

        echo json_encode([
            "result" => $result ? "success" : "fail",
            "msg" => $result ? "정상적으로 수정 되었습니다." : "처리 중 문제가 발생하였습니다.[Error-DB]"
        ]);
    } catch (PDOException $e) {
        echo json_encode(["result" => "fail", "msg" => "데이터베이스 오류: " . blue_safe_error($e)]);
    } catch (Exception $e) {
        echo json_encode(["result" => "fail", "msg" => "오류가 발생했습니다: " . blue_safe_error($e)]);
    }

} elseif ($mode == "category.delete") {
    $no = $_POST['no'];

    try {
        $query = "DELETE FROM nb_board_category WHERE no = :no";
        $stmt = $connect->prepare($query);
        $result = $stmt->execute([':no' => $no]);

        echo json_encode([
            "result" => $result ? "success" : "fail",
            "msg" => $result ? "정상적으로 삭제되었습니다." : "파일 삭제에 실패했습니다."
        ]);
    } catch (PDOException $e) {
        echo json_encode(["result" => "fail", "msg" => "데이터베이스 오류: " . blue_safe_error($e)]);
    } catch (Exception $e) {
        echo json_encode(["result" => "fail", "msg" => "오류가 발생했습니다: " . blue_safe_error($e)]);
    }
}
?>
