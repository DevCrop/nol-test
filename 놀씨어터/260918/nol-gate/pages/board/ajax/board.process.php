<?php

include_once "../../../../inc/lib/base.class.php";
include_once "../../../lib/admin.check.ajax.php";
require_once dirname(__DIR__, 3) . "/lib/AuditLogger.php";
$role->requireLogin();
$role->requireCanModify();

$mode = $_POST['mode'];

// base64 인코딩된 contents 디코딩
$raw_contents = $_POST['contents'] ?? '';
if (isset($_POST['contents_encoding']) && $_POST['contents_encoding'] === 'base64') {
    $raw_contents = base64_decode($raw_contents);
}

if ($mode == "save") {
    $board_no    = isset($_POST['board_no']) && $_POST['board_no'] !== '' ? (int)$_POST['board_no'] : 0;
    $title       = htmlspecialchars($_POST['title'] ?? '', ENT_QUOTES, 'UTF-8');
    $direct_url  = htmlspecialchars($_POST['direct_url'] ?? '', ENT_QUOTES, 'UTF-8');
    $write_name  = htmlspecialchars($_POST['write_name'] ?? '', ENT_QUOTES, 'UTF-8');
    $contents    = $raw_contents;
    $category_no = isset($_POST['category_no']) && $_POST['category_no'] !== '' ? (int)$_POST['category_no'] : 0;
    $is_notice   = ($_POST['is_notice'] ?? 'N') === 'Y' ? 'Y' : 'N';
    $regdate     = $_POST['regdate'] ?? null;

    // extra1 ~ extra30
    $extras = [];
    for ($i = 1; $i <= 30; $i++) $extras["extra$i"] = $_POST["extra$i"] ?? '';

    $user_no     = -1;
    $allow       = $board_file_allow;
    $uploads_dir = $UPLOAD_DIR_BOARD;

    // 썸네일 업로드
    $origin_file = '';
    $uploadResult = imageUpload($uploads_dir, $_FILES['thumb_image'] ?? null, $origin_file, false, $allow);
    $thumb_image_saved = $uploadResult['saved'] ?? null;

    // 첨부파일 업로드 (최대 5개)
    $file_attachments = [];
    for ($i = 1; $i <= 5; $i++) {
        if (isset($_FILES["addFile$i"]) && $_FILES["addFile$i"]['error'] === UPLOAD_ERR_OK) {
            $uploadResult = imageUpload($uploads_dir, $_FILES["addFile$i"], $origin_file, true, $allow);
            $file_attachments[$i] = [
                'saved'  => $uploadResult['saved']  ?? null,
                'origin' => $uploadResult['origin'] ?? null,
            ];
        }
    }

    try {
        $db = DB::getInstance();
        $db->exec("LOCK TABLES nb_board WRITE");

        // sort_no 맨 위로 밀기
        $shift = $db->prepare("UPDATE nb_board SET sort_no = sort_no + 1 WHERE sitekey = :sitekey");
        $shift->execute([':sitekey' => $NO_SITE_UNIQUE_KEY]);

        // INSERT
        $sql = "
          INSERT INTO nb_board (
            sitekey, board_no, user_no, category_no, title, contents, regdate,
            is_notice, write_name, direct_url, thumb_image,
            file_attach_1, file_attach_2, file_attach_3, file_attach_4, file_attach_5,
            file_attach_origin_1, file_attach_origin_2, file_attach_origin_3, file_attach_origin_4, file_attach_origin_5,
            sort_no,
            extra1, extra2, extra3, extra4, extra5, extra6, extra7, extra8, extra9, extra10,
            extra11, extra12, extra13, extra14, extra15, extra16, extra17, extra18, extra19, extra20,
            extra21, extra22, extra23, extra24, extra25, extra26, extra27, extra28, extra29, extra30
          ) VALUES (
            :sitekey, :board_no, :user_no, :category_no, :title, :contents, COALESCE(:regdate, NOW()),
            :is_notice, :write_name, :direct_url, :thumb_image,
            :file_attach_1, :file_attach_2, :file_attach_3, :file_attach_4, :file_attach_5,
            :file_attach_origin_1, :file_attach_origin_2, :file_attach_origin_3, :file_attach_origin_4, :file_attach_origin_5,
            1,
            :extra1, :extra2, :extra3, :extra4, :extra5, :extra6, :extra7, :extra8, :extra9, :extra10,
            :extra11, :extra12, :extra13, :extra14, :extra15, :extra16, :extra17, :extra18, :extra19, :extra20,
            :extra21, :extra22, :extra23, :extra24, :extra25, :extra26, :extra27, :extra28, :extra29, :extra30
          )
        ";
        $ins = $db->prepare($sql);

        $params = [
          ':sitekey' => $NO_SITE_UNIQUE_KEY,
          ':board_no' => $board_no,
          ':user_no' => $user_no,
          ':category_no' => $category_no,
          ':title' => $title,
          ':contents' => $contents,
          ':regdate' => $regdate ?: null,
          ':is_notice' => $is_notice,
          ':write_name' => $write_name,
          ':direct_url' => $direct_url,
          ':thumb_image' => $thumb_image_saved,
          ':file_attach_1' => $file_attachments[1]['saved'] ?? null,
          ':file_attach_2' => $file_attachments[2]['saved'] ?? null,
          ':file_attach_3' => $file_attachments[3]['saved'] ?? null,
          ':file_attach_4' => $file_attachments[4]['saved'] ?? null,
          ':file_attach_5' => $file_attachments[5]['saved'] ?? null,
          ':file_attach_origin_1' => $file_attachments[1]['origin'] ?? null,
          ':file_attach_origin_2' => $file_attachments[2]['origin'] ?? null,
          ':file_attach_origin_3' => $file_attachments[3]['origin'] ?? null,
          ':file_attach_origin_4' => $file_attachments[4]['origin'] ?? null,
          ':file_attach_origin_5' => $file_attachments[5]['origin'] ?? null,
        ];
        for ($i = 1; $i <= 30; $i++) {
          $params[":extra{$i}"] = $extras["extra{$i}"];
        }

        $ins->execute($params);

        $db->exec("UNLOCK TABLES");
        AuditLogger::record('create', 'board', AuditLogger::lastId(), (string) $title);
        echo json_encode(["result" => "success", "msg" => "정상적으로 등록되었습니다."]);
    } catch (PDOException $e) {
        $db->exec("UNLOCK TABLES");
        echo json_encode(["result" => "fail", "msg" => ClientFault::message($e)]);
    }
}
else if ($mode == "edit") {
    try {
        $no         = isset($_POST['no']) ? (int)$_POST['no'] : 0;
        $board_no   = isset($_POST['board_no']) && $_POST['board_no'] !== '' ? (int)$_POST['board_no'] : 0;
        $title      = $_POST['title'] ?? '';
        $write_name = $_POST['write_name'] ?? '';
        $contents   = htmlspecialchars($raw_contents, ENT_QUOTES, 'UTF-8');
        $category_no= isset($_POST['category_no']) && $_POST['category_no'] !== '' ? (int)$_POST['category_no'] : 0;
        $is_notice  = (($_POST['is_notice'] ?? '') === 'Y') ? 'Y' : 'N';
        $regdate    = $_POST['regdate'] ?? null;
        $direct_url = $_POST['direct_url'] ?? '';

        if (!$no || !$board_no) {
            echo json_encode(["result" => "fail", "msg" => "잘못된 요청입니다."]);
            exit;
        }

        // extra1 ~ extra30
        $extraFields = [];
        for ($i = 1; $i <= 30; $i++) {
            $extraFields["extra{$i}"] = $_POST["extra{$i}"] ?? '';
        }

        $uploads_dir = $UPLOAD_DIR_BOARD;
        $allow       = $board_file_allow;
        $origin_file = '';

        // 썸네일 업로드(선택)
        $thumb_image_saved = null;
        if (isset($_FILES['thumb_image']) && $_FILES['thumb_image']['error'] === UPLOAD_ERR_OK) {
            $thumb_result = imageUpload($uploads_dir, $_FILES['thumb_image'], $origin_file, false, $allow);
            $thumb_image_saved = $thumb_result['saved'] ?? null;
        }

        // 첨부파일 업로드(선택)
        $fileAttachments = [];
        for ($i = 1; $i <= 5; $i++) {
            if (isset($_FILES["addFile{$i}"]) && $_FILES["addFile{$i}"]['error'] === UPLOAD_ERR_OK) {
                $uploadResult = imageUpload($uploads_dir, $_FILES["addFile{$i}"], null, true, $allow);
                $fileAttachments["file_attach_{$i}_saved"]  = $uploadResult['saved']  ?? null;
                $fileAttachments["file_attach_{$i}_origin"] = $uploadResult['origin'] ?? null;
            }
        }

        $db = DB::getInstance();

        // 기존 파일 조회
        $stmt = $db->prepare("SELECT thumb_image, file_attach_1, file_attach_2, file_attach_3, file_attach_4, file_attach_5 FROM nb_board WHERE no = :no");
        $stmt->execute(['no' => $no]);
        $oldFiles = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$oldFiles) {
            echo json_encode(["result" => "fail", "msg" => "정보를 찾을 수 없습니다"]);
            exit;
        }

        // 개별 파일 삭제 처리
        $attach_file_del = $_POST['attach_file_del'] ?? [];
        foreach ($attach_file_del as $val) {
            $fileToDelete = null;
            switch ($val) {
                case "0": $fileToDelete = $oldFiles['thumb_image']; break;
                case "1": $fileToDelete = $oldFiles['file_attach_1']; break;
                case "2": $fileToDelete = $oldFiles['file_attach_2']; break;
                case "3": $fileToDelete = $oldFiles['file_attach_3']; break;
                case "4": $fileToDelete = $oldFiles['file_attach_4']; break;
                case "5": $fileToDelete = $oldFiles['file_attach_5']; break;
            }
            if ($fileToDelete) {
                imageDelete($UPLOAD_DIR_BOARD . "/" . $fileToDelete);
                $field = $val === "0" ? "thumb_image" : "file_attach_" . $val;
                $db->prepare("UPDATE nb_board SET {$field} = NULL WHERE no = :no")->execute(['no' => $no]);
            }
        }

        // 새 썸네일 업로드 시 기존 썸네일 삭제
        if ($thumb_image_saved && !empty($oldFiles['thumb_image'])) {
            imageDelete($UPLOAD_DIR_BOARD . "/" . $oldFiles['thumb_image']);
        }

        // 새 첨부 업로드 시 기존 파일 삭제
        for ($i = 1; $i <= 5; $i++) {
            $saved = $fileAttachments["file_attach_{$i}_saved"] ?? null;
            if ($saved && !empty($oldFiles["file_attach_{$i}"])) {
                imageDelete($UPLOAD_DIR_BOARD . "/" . $oldFiles["file_attach_{$i}"]);
            }
        }

        // 업데이트 필드 구성
        $updateFields = [
            'board_no'               => $board_no,
            'title'                  => $title,
            'write_name'             => $write_name,
            'contents'               => $contents,
            'is_notice'              => $is_notice,
            'direct_url'             => $direct_url,
            'regdate'                => $regdate,
            'category_no'            => $category_no,
        ];

        // extra1~30 반영
        foreach ($extraFields as $key => $value) {
            $updateFields[$key] = $value;
        }

        // 선택 항목
        if ($thumb_image_saved) {
            $updateFields['thumb_image'] = $thumb_image_saved;
        }
        for ($i = 1; $i <= 5; $i++) {
            $saved  = $fileAttachments["file_attach_{$i}_saved"]  ?? null;
            $origin = $fileAttachments["file_attach_{$i}_origin"] ?? null;
            if ($saved) {
                $updateFields["file_attach_{$i}"] = $saved;
                $updateFields["file_attach_origin_{$i}"] = $origin;
            }
        }

        // 동적 UPDATE 쿼리
        $query = "UPDATE nb_board SET ";
        $query .= implode(", ", array_map(fn($field) => "$field = :$field", array_keys($updateFields)));
        $query .= " WHERE no = :no";

        $updateFields['no'] = $no;

        $stmt = $db->prepare($query);
        $result = $stmt->execute($updateFields);
        AuditLogger::ifOk($result, 'update', 'board', $no, (string) $title);

        echo $result
            ? json_encode(["result" => "success", "msg" => "정상적으로 수정되었습니다."])
            : json_encode(["result" => "fail", "msg" => "처리중 문제가 발생하였습니다.[Error-DB]"]);

    } catch (Exception $e) {
        echo json_encode(["result" => "fail", "msg" => ClientFault::message($e) ]);
    }
}




else if ($mode == "delete") {
    $no = $_REQUEST['no'];

    try {
        $db = DB::getInstance();

        // 1. 삭제 전 sort_no 가져오기
        $stmt = $db->prepare("SELECT sort_no FROM nb_board WHERE no = :no");
        $stmt->execute([':no' => $no]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $deletedSortNo = $row['sort_no'] ?? null;

        // 2. 파일 삭제
        $stmt = $db->prepare("SELECT thumb_image, file_attach_1, file_attach_2, file_attach_3, file_attach_4, file_attach_5
                              FROM nb_board WHERE no = :no");
        $stmt->execute([':no' => $no]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($data) {
            foreach ($data as $file) {
                if ($file) imageDelete($UPLOAD_DIR_BOARD . "/" . $file);
            }
        }

        // 3. 글 삭제
        $stmt = $db->prepare("DELETE FROM nb_board WHERE no = :no");
        $stmt->execute([':no' => $no]);

        // 4. 연속 sort_no 재정렬
        $stmt = $db->prepare("UPDATE nb_board 
                              SET sort_no = sort_no - 1 
                              WHERE sort_no > :deletedSortNo");
        $stmt->execute([':deletedSortNo' => $deletedSortNo]);

        // 5. 댓글 삭제
        $stmt = $db->prepare("DELETE FROM nb_board_comment WHERE parent_no = :parent_no");
        $stmt->execute([':parent_no' => $no]);

        AuditLogger::record('delete', 'board', $no, (string) $no);
        echo json_encode(["result" => "success", "msg" => "정상적으로 삭제되었습니다."]);
    } catch (PDOException $e) {
        echo json_encode(["result" => "fail", "msg" => ClientFault::message($e)]);
    } catch (Exception $e) {
        echo json_encode(["result" => "fail", "msg" => ClientFault::message($e)]);
    }
} else if ($mode == "delete.array") {
    $board_file_check_no = $_POST['board_file_check_no'];

    try {
        if (empty($board_file_check_no)) {
            throw new Exception("삭제할 항목이 없습니다.");
        }

        $db = DB::getInstance();

        // 1. 삭제 대상의 sort_no 가져오기
        $placeholders = [];
        $params = [];
        foreach ($board_file_check_no as $index => $no) {
            $key = ":no$index";
            $placeholders[] = $key;
            $params[$key] = (int)$no;
        }

        $stmt = $db->prepare("SELECT sort_no FROM nb_board WHERE no IN (" . implode(',', $placeholders) . ")");
        $stmt->execute($params);
        $sortNos = $stmt->fetchAll(PDO::FETCH_COLUMN);

        if (empty($sortNos)) {
            throw new Exception("삭제 대상이 존재하지 않습니다.");
        }

        $minSortNo = min($sortNos);
        $deletedCount = count($sortNos);

        // 2. 삭제 대상의 파일 삭제
        $stmt = $db->prepare("SELECT thumb_image, file_attach_1, file_attach_2, file_attach_3, file_attach_4, file_attach_5
                              FROM nb_board WHERE no IN (" . implode(',', $placeholders) . ")");
        $stmt->execute($params);
        $files = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($files as $data) {
            foreach ($data as $file) {
                if ($file) imageDelete($UPLOAD_DIR_BOARD . "/" . $file);
            }
        }

        // 3. 레코드 삭제
        $stmt = $db->prepare("DELETE FROM nb_board WHERE no IN (" . implode(',', $placeholders) . ")");
        $stmt->execute($params);

        // 4. sort_no 재조정
        $stmt = $db->prepare("
            UPDATE nb_board 
            SET sort_no = sort_no - :deletedCount 
            WHERE sort_no > :minSortNo
        ");
        $stmt->execute([
            ':deletedCount' => $deletedCount,
            ':minSortNo' => $minSortNo
        ]);

        // 5. 댓글 삭제
        $stmt = $db->prepare("DELETE FROM nb_board_comment WHERE parent_no IN (" . implode(',', $placeholders) . ")");
        $stmt->execute($params);

        AuditLogger::record('delete', 'board', 0, count($params) . '건', ['ids' => array_values($params)]);
        echo json_encode(["result" => "success", "msg" => "정상적으로 삭제되었습니다."]);
    } catch (PDOException $e) {
        echo json_encode(["result" => "fail", "msg" => ClientFault::message($e)]);
    } catch (Exception $e) {
        echo json_encode(["result" => "fail", "msg" => ClientFault::message($e)]);
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
        echo json_encode(["result" => "fail", "msg" => ClientFault::message($e)]);
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
			if (preg_match('/^extra_match_field(\d{1,2})$/', $k, $matches) && !empty($v)) {
				$i = $matches[1]; 
				$rows[] = ["name" => $v, "fields" => "extra{$i}"];
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
$sql = "SELECT thumb_image, file_attach_1, file_attach_2, file_attach_3, file_attach_4, file_attach_5, contents FROM
nb_board WHERE no = :no";
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
echo json_encode(["result" => "fail", "msg" => ClientFault::message($e)]);
} catch (Exception $e) {
echo json_encode(["result" => "fail", "msg" => ClientFault::message($e)]);
}
}