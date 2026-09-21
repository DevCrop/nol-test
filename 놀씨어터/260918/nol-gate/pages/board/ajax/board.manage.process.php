<?php
include_once "../../../../inc/lib/base.class.php";
include_once "../../../lib/admin.check.ajax.php";
require_once dirname(__DIR__, 3) . "/lib/AuditLogger.php";
$role->requireLogin();
$role->requireCanModify();

$connect = DB::getInstance(); // Initialize PDO instance
$mode = $_POST['mode'];

if ($mode === "save") {
    try {
        $pdo = DB::getInstance();

        $title = $_POST['title'] ?? '';
        $skin = $_POST['skin'] ?? '';
        $view_yn = ($_POST['view_yn'] ?? '') === 'Y' ? 'Y' : 'N';
        $secret_yn = ($_POST['secret_yn'] ?? '') === 'Y' ? 'Y' : 'N';
        $list_size = isset($_POST['list_size']) && $_POST['list_size'] !== '' ? (int)$_POST['list_size'] : (int)$BOARD_DEFAULT_LIST_SIZE;
        $fileattach_yn = ($_POST['fileattach_yn'] ?? '') === 'Y' ? 'Y' : 'N';
        $fileattach_cnt = (int)($_POST['fileattach_cnt'] ?? 0);
        $comment_yn = ($_POST['comment_yn'] ?? '') === 'Y' ? 'Y' : 'N';
        $category_yn = ($_POST['category_yn'] ?? '') === 'Y' ? 'Y' : 'N';

        $extra_fields = [];
        for ($i = 1; $i <= 30; $i++) {
            $extra_fields[] = $_POST["extra_match_field{$i}"] ?? '';
        }

        $uploads_dir = $UPLOAD_DIR_BOARD;
        $origin_file = '';
        $uploadResult = imageUpload($uploads_dir, $_FILES['top_banner_image'] ?? null, $origin_file, false);
        $savedFile = $uploadResult['saved'] ?? '';

        $query = "INSERT INTO nb_board_manage (
            sitekey, title, skin, regdate, top_banner_image, view_yn, secret_yn,
            list_size, fileattach_yn, fileattach_cnt, comment_yn, category_yn,
            extra_match_field1, extra_match_field2, extra_match_field3, extra_match_field4, extra_match_field5,
            extra_match_field6, extra_match_field7, extra_match_field8, extra_match_field9, extra_match_field10,
            extra_match_field11, extra_match_field12, extra_match_field13, extra_match_field14, extra_match_field15,
            extra_match_field16, extra_match_field17, extra_match_field18, extra_match_field19, extra_match_field20,
            extra_match_field21, extra_match_field22, extra_match_field23, extra_match_field24, extra_match_field25,
            extra_match_field26, extra_match_field27, extra_match_field28, extra_match_field29, extra_match_field30
        ) VALUES (
            :sitekey, :title, :skin, NOW(), :top_banner_image, :view_yn, :secret_yn,
            :list_size, :fileattach_yn, :fileattach_cnt, :comment_yn, :category_yn,
            :extra1, :extra2, :extra3, :extra4, :extra5,
            :extra6, :extra7, :extra8, :extra9, :extra10,
            :extra11, :extra12, :extra13, :extra14, :extra15,
            :extra16, :extra17, :extra18, :extra19, :extra20,
            :extra21, :extra22, :extra23, :extra24, :extra25,
            :extra26, :extra27, :extra28, :extra29, :extra30
        )";

        $stmt = $pdo->prepare($query);
        $params = [
            ':sitekey' => $NO_SITE_UNIQUE_KEY,
            ':title' => $title,
            ':skin' => $skin,
            ':top_banner_image' => $savedFile,
            ':view_yn' => $view_yn,
            ':secret_yn' => $secret_yn,
            ':list_size' => $list_size,
            ':fileattach_yn' => $fileattach_yn,
            ':fileattach_cnt' => $fileattach_cnt,
            ':comment_yn' => $comment_yn,
            ':category_yn' => $category_yn,
        ];
        for ($i = 1; $i <= 30; $i++) {
            $params[":extra{$i}"] = $extra_fields[$i-1];
        }

        $result = $stmt->execute($params);
        AuditLogger::ifOk($result, 'create', 'board', AuditLogger::lastId(), (string) $title);

        echo json_encode([
            "result" => $result ? "success" : "fail",
            "msg" => $result ? "정상적으로 등록되었습니다." : "처리 중 문제가 발생하였습니다.[Error-DB]"
        ]);
    } catch (Exception $e) {
        echo json_encode(["result" => "fail", "msg" => ClientFault::message($e)]);
    }
} elseif ($mode == "edit") {
    try {
        $no = $_POST['no'] ?? null;
        $title = $_POST['title'] ?? '';
        $skin = $_POST['skin'] ?? '';
        $view_yn = (($_POST['view_yn'] ?? '') === 'Y') ? 'Y' : 'N';
        $secret_yn = (($_POST['secret_yn'] ?? '') === 'Y') ? 'Y' : 'N';
        $list_size = isset($_POST['list_size']) && $_POST['list_size'] !== '' ? (int)$_POST['list_size'] : (int)$BOARD_DEFAULT_LIST_SIZE;
        $fileattach_yn = (($_POST['fileattach_yn'] ?? '') === 'Y') ? 'Y' : 'N';
        $fileattach_cnt = (int)($_POST['fileattach_cnt'] ?? 0);
        $comment_yn = (($_POST['comment_yn'] ?? '') === 'Y') ? 'Y' : 'N';
        $category_yn = (($_POST['category_yn'] ?? '') === 'Y') ? 'Y' : 'N';

        $extra_match_fields = [];
        for ($i = 1; $i <= 30; $i++) {
            $extra_match_fields[] = $_POST["extra_match_field$i"] ?? '';
        }

        $uploads_dir = $UPLOAD_DIR_BOARD;
        $origin_file = '';
        $uploadResult = imageUpload($uploads_dir, $_FILES['top_banner_image'] ?? null, $origin_file, false);
        $savedFile = $uploadResult['saved'] ?? '';

        $query = "UPDATE nb_board_manage SET 
            title = :title, skin = :skin, view_yn = :view_yn, secret_yn = :secret_yn,
            list_size = :list_size, fileattach_yn = :fileattach_yn, fileattach_cnt = :fileattach_cnt,
            comment_yn = :comment_yn, category_yn = :category_yn,
            extra_match_field1 = :extra1, extra_match_field2 = :extra2, extra_match_field3 = :extra3,
            extra_match_field4 = :extra4, extra_match_field5 = :extra5, extra_match_field6 = :extra6, extra_match_field7 = :extra7,
            extra_match_field8 = :extra8, extra_match_field9 = :extra9, extra_match_field10 = :extra10, extra_match_field11 = :extra11,
            extra_match_field12 = :extra12, extra_match_field13 = :extra13, extra_match_field14 = :extra14, extra_match_field15 = :extra15,
            extra_match_field16 = :extra16, extra_match_field17 = :extra17, extra_match_field18 = :extra18, extra_match_field19 = :extra19, extra_match_field20 = :extra20,
            extra_match_field21 = :extra21, extra_match_field22 = :extra22, extra_match_field23 = :extra23, extra_match_field24 = :extra24, extra_match_field25 = :extra25,
            extra_match_field26 = :extra26, extra_match_field27 = :extra27, extra_match_field28 = :extra28, extra_match_field29 = :extra29, extra_match_field30 = :extra30";
        if ($savedFile) {
            $query .= ", top_banner_image = :top_banner_image";
        }
        $query .= " WHERE no = :no";

        $stmt = $connect->prepare($query);

        $params = [
            ':title' => $title,
            ':skin' => $skin,
            ':view_yn' => $view_yn,
            ':secret_yn' => $secret_yn,
            ':list_size' => $list_size,
            ':fileattach_yn' => $fileattach_yn,
            ':fileattach_cnt' => $fileattach_cnt,
            ':comment_yn' => $comment_yn,
            ':category_yn' => $category_yn,
            ':no' => $no,
        ];
        for ($i = 1; $i <= 30; $i++) {
            $params[":extra{$i}"] = $extra_match_fields[$i-1];
        }
        if ($savedFile) {
            $params[':top_banner_image'] = $savedFile;
        }

        $result = $stmt->execute($params);
        AuditLogger::ifOk($result, 'update', 'board', (int) $no, (string) $title);

        echo json_encode([
            "result" => $result ? "success" : "fail",
            "msg" => $result ? "정상적으로 수정되었습니다." : "처리 중 문제가 발생하였습니다.[Error-DB]"
        ]);
    } catch (PDOException $e) {
        echo json_encode([
            "result" => "fail",
            "msg" => ClientFault::message($e)
        ]);
    } catch (Exception $e) {
        echo json_encode([
            "result" => "fail",
            "msg" => ClientFault::message($e)
        ]);
    }
}

 elseif ($mode == "delete") {

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
        AuditLogger::ifOk($result, 'delete', 'board', (int) $no, (string) $no);

		echo json_encode([
			"result" => $result ? "success" : "fail",
			"msg" => $result ? "정상적으로 삭제되었습니다." : "데이터 삭제에 실패했습니다."
		]);
	} catch (PDOException $e) {
		// PDO 예외 처리
		echo json_encode(["result" => "fail", "msg" => ClientFault::message($e)]);
	} catch (Exception $e) {
		// 기타 예외 처리
		echo json_encode(["result" => "fail", "msg" => ClientFault::message($e)]);
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
		echo json_encode(["result" => "fail", "msg" => ClientFault::message($e)]);
	} catch (Exception $e) {
		// 일반 예외 처리
		echo json_encode(["result" => "fail", "msg" => ClientFault::message($e)]);
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
        echo json_encode(["result" => "fail", "msg" => ClientFault::message($e)]);
    } catch (Exception $e) {
        echo json_encode(["result" => "fail", "msg" => ClientFault::message($e)]);
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
        echo json_encode(["result" => "fail", "msg" => ClientFault::message($e)]);
    } catch (Exception $e) {
        echo json_encode(["result" => "fail", "msg" => ClientFault::message($e)]);
    }
}
?>