<?php

include_once "../../../../inc/lib/base.class.php";
include_once "../../../lib/admin.check.ajax.php";

$mode = $_POST['mode'];

if ($mode == "save") {

    $board_no = $_POST['board_no'];
    $title = htmlspecialchars($_POST['title'], ENT_QUOTES, 'UTF-8');
    $direct_url = htmlspecialchars($_POST['direct_url'], ENT_QUOTES, 'UTF-8');
    $write_name = htmlspecialchars($_POST['write_name'], ENT_QUOTES, 'UTF-8');
    $contents = htmlspecialchars($_POST['contents'], ENT_QUOTES, 'UTF-8');

	

    $category_no = $_POST['category_no'] ?? 0;
    $is_notice = $_POST['is_notice'] ?? "N";
    $is_view = $_POST['is_view'];
    $regdate = $_POST['regdate'] ?? null;

    $extras = [];
    for ($i = 1; $i <= 15; $i++) {
        $extras["extra$i"] = $_POST["extra$i"] ?? '';
    }

    $depths = [
        'depth1' => $_POST['depth1'] ?? -1,
        'depth2' => $_POST['depth2'] ?? -1,
        'depth3' => $_POST['depth3'] ?? -1,
        'depth4' => $_POST['depth4'] ?? -1,
    ];

    $user_no = -1; // Assuming a user no match is -1
    $allow = $board_file_allow;
    $uploads_dir = $UPLOAD_DIR_BOARD;
	
	//var_dump($_FILES['thumb_image']);exit; 
    // Handle file uploads
    $uploadResult = imageUpload($uploads_dir, $_FILES['thumb_image'], '' , false, $allow);
    $thumb_image_saved = $uploadResult['saved'];

	$file_attachments = [];
	for ($i = 1; $i <= 5; $i++) {
		$file_attachments[$i] = [
			'saved' => '',  // 기본값 추가
			'origin' => ''
		];
	}

	// 파일 업로드 처리
	for ($i = 1; $i <= 5; $i++) {
		if (isset($_FILES["addFile$i"]) && $_FILES["addFile$i"]['error'] === UPLOAD_ERR_OK) {
			$uploadResult = imageUpload($uploads_dir, $_FILES["addFile$i"], '', true, $allow);
			$file_attachments[$i]['saved'] = $uploadResult['saved'] ?? '';
			$file_attachments[$i]['origin'] = $uploadResult['origin'] ?? '';
		}
	}


	// echo json_encode($file_attachments); exit; 

   try {
    $query = "INSERT INTO nb_board (
                sitekey, 
                board_no, 
                user_no, 
                category_no, 
                title, 
                contents, 
                regdate, 
                is_notice, 
				is_view,
                write_name, 
                direct_url,
                thumb_image, 
                file_attach_1, 
                file_attach_2, 
                file_attach_3, 
                file_attach_4, 
                file_attach_5,
                file_attach_origin_1,
                file_attach_origin_2,
                file_attach_origin_3,
                file_attach_origin_4,
                file_attach_origin_5,
                extra1, extra2, extra3, extra4, extra5, extra6, extra7, extra8, extra9, extra10, extra11, extra12, extra13, extra14, extra15
              ) VALUES (
                :sitekey, 
                :board_no, 
                :user_no, 
                :category_no, 
                :title, 
                :contents, 
                NOW(), 
                :is_notice, 
				:is_view,
                :write_name, 
                :direct_url,
                :thumb_image, 
                :file_attach_1, 
                :file_attach_2, 
                :file_attach_3, 
                :file_attach_4, 
                :file_attach_5,
                :file_attach_origin_1,
                :file_attach_origin_2,
                :file_attach_origin_3,
                :file_attach_origin_4,
                :file_attach_origin_5,
                :extra1, :extra2, :extra3, :extra4, :extra5, :extra6, :extra7, :extra8, :extra9, :extra10, :extra11, :extra12, :extra13, :extra14, :extra15
              )";

    $stmt = DB::getInstance()->prepare($query);
	$pendingData = [
        ':sitekey' => $NO_SITE_UNIQUE_KEY,
        ':board_no' => $board_no,
        ':user_no' => $user_no,
        ':category_no' => $category_no,
        ':title' => $title,
        ':contents' => $contents,
        ':is_notice' => $is_notice,
        ':is_view' => $is_view,
        ':write_name' => $write_name,
        ':direct_url' => $direct_url,
        ':thumb_image' => $thumb_image_saved,
		':file_attach_1' => $file_attachments[1]['saved'] ?? '',
		':file_attach_2' => $file_attachments[2]['saved'] ?? '',
		':file_attach_3' => $file_attachments[3]['saved'] ?? '',
		':file_attach_4' => $file_attachments[4]['saved'] ?? '',
		':file_attach_5' => $file_attachments[5]['saved'] ?? '',
        ':file_attach_origin_1' => $file_attachments[1]['origin'],
        ':file_attach_origin_2' => $file_attachments[2]['origin'],
        ':file_attach_origin_3' => $file_attachments[3]['origin'],
        ':file_attach_origin_4' => $file_attachments[4]['origin'],
        ':file_attach_origin_5' => $file_attachments[5]['origin'],
        ':extra1' => $extras['extra1'],
        ':extra2' => $extras['extra2'],
        ':extra3' => $extras['extra3'],
        ':extra4' => $extras['extra4'],
        ':extra5' => $extras['extra5'],
        ':extra6' => $extras['extra6'],
        ':extra7' => $extras['extra7'],
        ':extra8' => $extras['extra8'],
        ':extra9' => $extras['extra9'],
        ':extra10' => $extras['extra10'],
        ':extra11' => $extras['extra11'],
        ':extra12' => $extras['extra12'],
        ':extra13' => $extras['extra13'],
        ':extra14' => $extras['extra14'],
        ':extra15' => $extras['extra15']
    ];
	// echo json_encode($pendingData); exit; 
    $stmt->execute($pendingData);

    echo json_encode(["result" => "success", "msg" => "정상적으로 등록되었습니다."]);
} catch (PDOException $e) {
    echo json_encode(["result" => "fail", "msg" => "DB 처리중 오류가 발생하였습니다.[Error: " . blue_safe_error($e) . "]"]);
}

}else if ($mode == "edit") {
    try {
        // 기본 정보 수집
        $no = $_POST['no'] ?? null;
        $board_no = htmlspecialchars($_POST['board_no'] ?? '', ENT_QUOTES, 'UTF-8');
        $title = htmlspecialchars($_POST['title'] ?? '', ENT_QUOTES, 'UTF-8');
        $write_name = htmlspecialchars($_POST['write_name'] ?? '', ENT_QUOTES, 'UTF-8');
        $contents = htmlspecialchars($_POST['contents'] ?? '', ENT_QUOTES, 'UTF-8');
        $category_no = $_POST['category_no'] ?? 0;
        $is_notice = ($_POST['is_notice'] ?? 'N') === 'Y' ? 'Y' : 'N';
        $is_view = ($_POST['is_view'] ?? 'N') === 'Y' ? 'Y' : 'N';
        $regdate = $_POST['regdate'] ?? date("Y-m-d H:i:s");
        $direct_url = htmlspecialchars($_POST['direct_url'] ?? '', ENT_QUOTES, 'UTF-8');

        // 추가 필드
        $extraFields = [];
        for ($i = 1; $i <= 15; $i++) {
            $extraFields["extra{$i}"] = htmlspecialchars($_POST["extra{$i}"] ?? '', ENT_QUOTES, 'UTF-8');
        }

        // 파일 업로드 처리
        $uploads_dir = $UPLOAD_DIR_BOARD;
        $allow = $board_file_allow;

        // 썸네일 처리
        $thumb_image_saved = null;
        if (isset($_FILES['thumb_image']) && $_FILES['thumb_image']['error'] === UPLOAD_ERR_OK) {
            $uploadResult = imageUpload($uploads_dir, $_FILES['thumb_image'], '', false, $allow);
            $thumb_image_saved = $uploadResult['saved'] ?? null;
        }

        // 추가 파일 첨부 (배열 초기화)
        $fileAttachments = [];
        for ($i = 1; $i <= 5; $i++) {
            $fileAttachments["file_attach_{$i}_saved"] = '';
            $fileAttachments["file_attach_{$i}_origin"] = '';
            
            if (isset($_FILES["addFile{$i}"]) && $_FILES["addFile{$i}"]['error'] === UPLOAD_ERR_OK) {
                $uploadResult = imageUpload($uploads_dir, $_FILES["addFile{$i}"], '', true, $allow);
                $fileAttachments["file_attach_{$i}_saved"] = $uploadResult['saved'] ?? '';
                $fileAttachments["file_attach_{$i}_origin"] = $uploadResult['origin'] ?? '';
            }
        }

        // 기존 데이터 확인
        $db = DB::getInstance();
        $stmt = $db->prepare("SELECT thumb_image, file_attach_1, file_attach_2, file_attach_3, file_attach_4, file_attach_5 FROM nb_board WHERE no = :no");
        $stmt->execute(['no' => $no]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$data) {
            echo json_encode(["result" => "fail", "msg" => "정보를 찾을 수 없습니다"]);
            exit;
        }

        // 삭제할 파일 처리
        $attach_file_del = $_POST['attach_file_del'] ?? [];
        foreach ($attach_file_del as $val) {
            $fileToDelete = null;
            $field = null;

            switch ($val) {
                case "0":
                    $fileToDelete = $data['thumb_image'] ?? null;
                    $field = "thumb_image";
                    break;
                case "1":
                    $fileToDelete = $data['file_attach_1'] ?? null;
                    $field = "file_attach_1";
                    break;
                case "2":
                    $fileToDelete = $data['file_attach_2'] ?? null;
                    $field = "file_attach_2";
                    break;
                case "3":
                    $fileToDelete = $data['file_attach_3'] ?? null;
                    $field = "file_attach_3";
                    break;
                case "4":
                    $fileToDelete = $data['file_attach_4'] ?? null;
                    $field = "file_attach_4";
                    break;
                case "5":
                    $fileToDelete = $data['file_attach_5'] ?? null;
                    $field = "file_attach_5";
                    break;
            }

            if ($fileToDelete && $field) {
                imageDelete($UPLOAD_DIR_BOARD . "/" . $fileToDelete);
                $db->prepare("UPDATE nb_board SET {$field} = NULL WHERE no = :no")->execute(['no' => $no]);
            }
        }

        // 새로 업로드된 파일이 기존 파일을 덮어쓸 경우 처리
        if ($thumb_image_saved && !empty($data['thumb_image'])) {
            imageDelete($UPLOAD_DIR_BOARD . "/" . $data['thumb_image']);
        }

        for ($i = 1; $i <= 5; $i++) {
            if (!empty($fileAttachments["file_attach_{$i}_saved"]) && !empty($data["file_attach_{$i}"])) {
                imageDelete($UPLOAD_DIR_BOARD . "/" . $data["file_attach_{$i}"]);
            }
        }

        // 업데이트 필드 준비
        $updateFields = [
            'board_no' => $board_no,
            'title' => $title,
            'contents' => $contents,
            'is_notice' => $is_notice,
            'is_view' => $is_view,
            'direct_url' => $direct_url,
            'regdate' => $regdate,
        ];

        foreach ($extraFields as $key => $value) {
            $updateFields[$key] = $value;
        }

        if (!empty($category_no)) {
            $updateFields['category_no'] = $category_no;
        }

        if (!empty($thumb_image_saved)) {
            $updateFields['thumb_image'] = $thumb_image_saved;
        }

        for ($i = 1; $i <= 5; $i++) {
            if (!empty($fileAttachments["file_attach_{$i}_saved"])) {
                $updateFields["file_attach_{$i}"] = $fileAttachments["file_attach_{$i}_saved"];
                $updateFields["file_attach_origin_{$i}"] = $fileAttachments["file_attach_{$i}_origin"];
            }
        }

        // SQL 업데이트 쿼리 생성
        $query = "UPDATE nb_board SET ";
        $query .= implode(", ", array_map(function ($field) {
            return "$field = :$field";
        }, array_keys($updateFields)));
        $query .= " WHERE no = :no";

        $updateFields['no'] = $no;

        // 쿼리 실행
        $stmt = $db->prepare($query);
        $result = $stmt->execute($updateFields);

        echo $result
            ? json_encode(["result" => "success", "msg" => "정상적으로 수정되었습니다."])
            : json_encode(["result" => "fail", "msg" => "처리중 문제가 발생하였습니다.[Error-DB]"]);

    } catch (Exception $e) {
        echo json_encode(["result" => "fail", "msg" => "Exception occurred: " . blue_safe_error($e)]);
    }
}
 else if ($mode == "delete") {
    $no = $_POST['no'];

    try {
        $query = "SELECT thumb_image, file_attach_1, file_attach_2, file_attach_3, file_attach_4, file_attach_5
                  FROM nb_board WHERE no = :no";
        $stmt = DB::getInstance()->prepare($query);
        $stmt->execute([':no' => $no]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$data) {
            throw new Exception("정보를 찾을 수 없습니다");
        }

        // Delete files
        foreach ($data as $key => $file) {
            if ($file) {
                imageDelete($UPLOAD_DIR_BOARD . "/" . $file);
            }
        }

        // Delete board record
        $query = "DELETE FROM nb_board WHERE no = :no";
        $stmt = DB::getInstance()->prepare($query);
        $stmt->execute([':no' => $no]);

        // Delete associated comments
        $query = "DELETE FROM nb_board_comment WHERE parent_no = :parent_no";
        $stmt = DB::getInstance()->prepare($query);
        $stmt->execute([':parent_no' => $no]);

        echo json_encode(["result" => "success", "msg" => "정상적으로 삭제되었습니다."]);
    } catch (PDOException $e) {
        echo json_encode(["result" => "fail", "msg" => "DB 처리중 오류가 발생하였습니다.[Error: " . blue_safe_error($e) . "]"]);
    } catch (Exception $e) {
        echo json_encode(["result" => "fail", "msg" => blue_safe_error($e)]);
    }
} else if ($mode == "delete.array") {
    $board_file_check_no = $_POST['board_file_check_no'];

    try {
        foreach ($board_file_check_no as $val) {
            $no = $val;

            $query = "SELECT thumb_image, file_attach_1, file_attach_2, file_attach_3, file_attach_4, file_attach_5
                      FROM nb_board WHERE no = :no";
            $stmt = DB::getInstance()->prepare($query);
            $stmt->execute([':no' => $no]);
            $data = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$data) {
                throw new Exception("정보를 찾을 수 없습니다");
            }

            // Delete files
            foreach ($data as $key => $file) {
                if ($file) {
                    imageDelete($UPLOAD_DIR_BOARD . "/" . $file);
                }
            }

            // Delete board record
            $query = "DELETE FROM nb_board WHERE no = :no";
            $stmt = DB::getInstance()->prepare($query);
            $stmt->execute([':no' => $no]);

            // Delete associated comments
            $query = "DELETE FROM nb_board_comment WHERE parent_no = :parent_no";
            $stmt = DB::getInstance()->prepare($query);
            $stmt->execute([':parent_no' => $no]);
        }

        echo json_encode(["result" => "success", "msg" => "정상적으로 삭제되었습니다."]);
    } catch (PDOException $e) {
        echo json_encode(["result" => "fail", "msg" => "DB 처리중 오류가 발생하였습니다.[Error: " . blue_safe_error($e) . "]"]);
    } catch (Exception $e) {
        echo json_encode(["result" => "fail", "msg" => blue_safe_error($e)]);
    }
} else if ($mode == "board.category") {
    $board_no = $_REQUEST['board_no'];
    $boardManage_info = getBoardManageInfoByNo($board_no);
    $category_yn = $boardManage_info[0]['category_yn'];

    try {
        $query = "SELECT no, name FROM nb_board_category WHERE sitekey = :sitekey AND board_no = :board_no ORDER BY sort_no ASC";
        $stmt = DB::getInstance()->prepare($query);
        $stmt->execute([':sitekey' => $NO_SITE_UNIQUE_KEY, ':board_no' => $board_no]);

        $rows = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $rows[] = ["no" => $row['no'], "name" => $row['name']];
        }

        echo json_encode([
            "result" => "success",
            "category_yn" => $category_yn,
            "rows" => $rows,
            "msg" => "정상적으로 불러왔습니다."
        ]);
    } catch (PDOException $e) {
        echo json_encode(["result" => "fail", "msg" => "DB 처리중 오류가 발생하였습니다.[Error: " . blue_safe_error($e) . "]"]);
    }
} else if ($mode == "category.depth") {
    $board_no = $_REQUEST['board_no'];
    $boardManage_info = getBoardManageInfoByNo($board_no);
    $depth_category_yn = $boardManage_info[0]['depth_category_yn'];

    echo json_encode([
        "result" => "success",
        "depth_category_yn" => $depth_category_yn,
        "msg" => "정상적으로 불러왔습니다."
    ]);
} else if ($mode == "board.field") {
    $board_no = $_REQUEST['board_no'];
    $boardManage_info = getBoardManageInfoByNo($board_no);

    $rows = [];

    foreach ($boardManage_info[0] as $k => $v) {
        if (strpos($k, 'extra_match_field') !== false && !empty($v)) {
            $arr = explode('extra_match_field', $k);
            $i = $arr[1]; 
			$rows[] = ["name" => $v, "fields" => "extra$i"];
        }
    }

    echo json_encode([
        'test' => true,
        'result' => 'success',
        'rows' => $rows,
        'msg' => '정상적으로 불러왔습니다.',
    ]);
} else if ($mode == "board.copy") {
    function copyAndRenameFile($originalPath) {
        if (!file_exists($originalPath)) {
            return false;
        }

        $directory = dirname($originalPath);
        $extension = pathinfo($originalPath, PATHINFO_EXTENSION);
        $newPath = $directory . '/' . uniqid() . '.' . $extension;

        if (!copy($originalPath, $newPath)) {
            return false;
        }

        return basename($newPath);
    }

    function copyAndUpdateImageSrc($htmlContent) {
        global $UPLOAD_DIR_BOARD;
        $updatedSrcArray = [];
        $dom = new DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML('<?xml encoding="utf-8" ?>' . $htmlContent);
        $images = $dom->getElementsByTagName('img');

        foreach ($images as $img) {
            $originalSrc = $img->getAttribute('src');
            $baseSrc = basename($originalSrc);
            $updatedSrc = copyAndRenameFile($UPLOAD_DIR_BOARD . '/' . $baseSrc);

            if (file_exists($originalSrc)) {
                $newSrc = $UPLOAD_DIR_BOARD . '/' . $updatedSrc;

                if (copy($originalSrc, $newSrc)) {
                    $img->setAttribute('src', '/uploads/board/' . $updatedSrc);
                    $updatedSrcArray[] = $updatedSrc;
                }
            }
        }

        $updatedHtmlContent = $dom->saveHTML();
        libxml_clear_errors();

        return $updatedHtmlContent;
    }

    $no = $_REQUEST['no'];

    try {
        $sql = "SELECT thumb_image, file_attach_1, file_attach_2, file_attach_3, file_attach_4, file_attach_5, contents FROM nb_board WHERE no = :no";
        $stmt = DB::getInstance()->prepare($sql);
        $stmt->execute([':no' => $no]);

        $orgData = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$orgData) {
            throw new Exception("정보를 찾을 수 없습니다.");
        }

        $newData = [];
        foreach ($orgData as $k => $v) {
            if ($k === 'contents') {
                $contents = stripslashes($v);
                $contents = htmlspecialchars_decode($contents);
                $newContents = copyAndUpdateImageSrc($contents);
                $newData[$k] = htmlspecialchars($newContents);
            } else if (!empty($v)) {
                $newData[$k] = copyAndRenameFile($UPLOAD_DIR_BOARD . '/' . $v);
            } else {
                $newData[$k] = '';
            }
        }

        $query = "INSERT INTO nb_board (
                    sitekey, 
                    board_no, 
                    user_no, 
                    category_no, 
                    title, 
                    contents, 
                    regdate, 
                    is_notice, 
					is_view,
                    write_name, 
                    direct_url,
                    thumb_image, 
                    file_attach_1, 
                    file_attach_2, 
                    file_attach_3, 
                    file_attach_4, 
                    file_attach_5,
                    file_attach_origin_1,
                    file_attach_origin_2,
                    file_attach_origin_3,
                    file_attach_origin_4, 
                    file_attach_origin_5,
                    extra1,
                    extra2, 
                    extra3, 
                    extra4,
                    extra5,
                    extra6,
                    extra7,
                    extra8,
                    extra9,
                    extra10,
                    extra11,
                    extra12,
                    extra13,
                    extra14,
                    extra15
                    ) 
                    SELECT 
                    sitekey, 
                    board_no, 
                    user_no, 
                    category_no, 
                    title,
                    :contents, 
                    regdate, 
                    is_notice, 
					is_view,
                    write_name, 
                    direct_url,
                    :thumb_image, 
                    :file_attach_1, 
                    :file_attach_2, 
                    :file_attach_3, 
                    :file_attach_4, 
                    :file_attach_5,
                    file_attach_origin_1,
                    file_attach_origin_2, 
                    file_attach_origin_3,
                    file_attach_origin_4,
                    file_attach_origin_5, 
                    extra1, 
                    extra2, 
                    extra3, 
                    extra4, 
                    extra5,
                    extra6,
                    extra7,
                    extra8,
                    extra9,
                    extra10,
                    extra11,
                    extra12,
                    extra13,
                    extra14,
                    extra15
                    FROM nb_board WHERE no = :no";

        $stmt = DB::getInstance()->prepare($query);
        $stmt->execute([
            ':contents' => $newData['contents'],
            ':thumb_image' => $newData['thumb_image'],
            ':file_attach_1' => $newData['file_attach_1'],
            ':file_attach_2' => $newData['file_attach_2'],
            ':file_attach_3' => $newData['file_attach_3'],
            ':file_attach_4' => $newData['file_attach_4'],
            ':file_attach_5' => $newData['file_attach_5'],
            ':no' => $no
        ]);

        echo json_encode(["result" => "success", "msg" => "정상적으로 복사 되었습니다."]);
    } catch (PDOException $e) {
        echo json_encode(["result" => "fail", "msg" => "DB 처리중 오류가 발생하였습니다.[Error: " . blue_safe_error($e) . "]"]);
    } catch (Exception $e) {
        echo json_encode(["result" => "fail", "msg" => blue_safe_error($e)]);
    }
}
