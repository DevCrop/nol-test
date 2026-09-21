<?php

include_once $_SERVER['DOCUMENT_ROOT'] . "/inc/lib/db.php"; // DB 클래스 포함

$no = isset($_REQUEST['no']) ? intval($_REQUEST['no']) : 0;
$fld = isset($_REQUEST['fld']) ? $_REQUEST['fld'] : '';

if ($no <= 0 || empty($fld)) {
    die("잘못된 요청입니다.");
}

// PDO 인스턴스 가져오기
$db = DB::getInstance();
if ($db === null) {
    die("DB 연결 실패");
}

// SQL 실행
$query = "SELECT thumb_image, file_attach_1, file_attach_2, file_attach_3, file_attach_4, file_attach_5,
                 file_attach_origin_1, file_attach_origin_2, file_attach_origin_3, file_attach_origin_4, file_attach_origin_5
          FROM nb_board 
          WHERE no = :no";

$stmt = $db->prepare($query);
$stmt->bindValue(':no', $no, PDO::PARAM_INT);
$stmt->execute();
$data = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$data) {
    die("정보를 찾을 수 없습니다.");
}

$filename = "";
$filename_origin = ""; // 원본 파일명 저장

switch ($fld) {
    case "thumb":
        $filename = $data['thumb_image'];
        $filename_origin = "thumbnail.jpg";
        break;
    case "attach1":
        $filename = $data['file_attach_1'];
        $filename_origin = $data['file_attach_origin_1'];
        break;
    case "attach2":
        $filename = $data['file_attach_2'];
        $filename_origin = $data['file_attach_origin_2'];
        break;
    case "attach3":
        $filename = $data['file_attach_3'];
        $filename_origin = $data['file_attach_origin_3'];
        break;
    case "attach4":
        $filename = $data['file_attach_4'];
        $filename_origin = $data['file_attach_origin_4'];
        break;
    case "attach5":
        $filename = $data['file_attach_5'];
        $filename_origin = $data['file_attach_origin_5'];
        break;
    default:
        die("유효하지 않은 필드 값입니다.");
}

if (!isset($UPLOAD_DIR_BOARD) || empty($UPLOAD_DIR_BOARD)) {
    die("파일 경로가 설정되지 않았습니다.");
}

$filepath = $UPLOAD_DIR_BOARD . "/" . $filename;

if (!file_exists($filepath)) {
    die("파일을 찾을 수 없습니다.");
}

$filesize = filesize($filepath);
$path_parts = pathinfo($filepath);
$filename = $path_parts['basename'];

// 파일명이 없을 경우 기본 이름 설정
if (!$filename_origin) {
    $filename_origin = "download." . $path_parts['extension'];
}

// 다운로드 헤더 설정
header("Pragma: public");
header("Expires: 0");
header("Content-Type: application/octet-stream");
header("Content-Disposition: attachment; filename=\"$filename_origin\"");
header("Content-Transfer-Encoding: binary");
header("Content-Length: $filesize");

// 출력 버퍼 정리 후 파일 전송
if (ob_get_length()) {
    ob_end_clean();
}
flush();
readfile($filepath);

exit;

?>
