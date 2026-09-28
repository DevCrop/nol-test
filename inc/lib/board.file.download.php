<?php
declare(strict_types=1);

include_once __DIR__ . "/base.class.php";

$no = isset($_REQUEST['no']) ? (int)$_REQUEST['no'] : 0;
$fld = isset($_REQUEST['fld']) ? (string)$_REQUEST['fld'] : '';

if ($no <= 0) {
    http_response_code(400);
    exit('잘못된 요청입니다. (no)');
}

$allowedFld = ['thumb', 'thumb_2', 'attach1', 'attach2', 'attach3', 'attach4', 'attach5'];
if (!in_array($fld, $allowedFld, true)) {
    http_response_code(400);
    exit('유효하지 않은 필드 값입니다.');
}

$query = "
    SELECT
        thumb_image,
        thumb_image_2,
        file_attach_1, file_attach_2, file_attach_3, file_attach_4, file_attach_5,
        file_attach_origin_1, file_attach_origin_2, file_attach_origin_3, file_attach_origin_4, file_attach_origin_5
    FROM nb_board
    WHERE no = ?
";

$stmt = $connect->prepare($query);
$stmt->bind_param("i", $no);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();
$stmt->close();

if (!$data) {
    http_response_code(404);
    exit('정보를 찾을 수 없습니다.');
}

$filename = '';
$filenameOrigin = '';

switch ($fld) {
    case 'thumb':
        $filename = (string)($data['thumb_image'] ?? '');
        $filenameOrigin = $filename;
        break;
    case 'thumb_2':
        $filename = (string)($data['thumb_image_2'] ?? '');
        $filenameOrigin = $filename;
        break;
    case 'attach1':
        $filename = (string)($data['file_attach_1'] ?? '');
        $filenameOrigin = (string)($data['file_attach_origin_1'] ?? '');
        break;
    case 'attach2':
        $filename = (string)($data['file_attach_2'] ?? '');
        $filenameOrigin = (string)($data['file_attach_origin_2'] ?? '');
        break;
    case 'attach3':
        $filename = (string)($data['file_attach_3'] ?? '');
        $filenameOrigin = (string)($data['file_attach_origin_3'] ?? '');
        break;
    case 'attach4':
        $filename = (string)($data['file_attach_4'] ?? '');
        $filenameOrigin = (string)($data['file_attach_origin_4'] ?? '');
        break;
    case 'attach5':
        $filename = (string)($data['file_attach_5'] ?? '');
        $filenameOrigin = (string)($data['file_attach_origin_5'] ?? '');
        break;
}

if ($filename === '') {
    http_response_code(404);
    exit('해당 필드에 파일이 없습니다.');
}

if ($filenameOrigin === '') {
    $filenameOrigin = $filename;
}

$baseDir = rtrim($UPLOAD_DIR_BOARD ?? '', '/');
$filepath = $baseDir . '/' . $filename;

if (!is_file($filepath)) {
    http_response_code(404);
    exit('파일을 찾을 수 없습니다.');
}

$filesize = filesize($filepath);
if ($filesize === false) {
    http_response_code(500);
    exit('파일 크기를 확인할 수 없습니다.');
}

$ua = $_SERVER['HTTP_USER_AGENT'] ?? '';

if (preg_match('/MSIE|Trident/i', $ua)) {
    $disposition = 'attachment; filename="' . rawurlencode($filenameOrigin) . '"';
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Pragma: public');
} elseif (preg_match('/Firefox/i', $ua)) {
    $disposition = "attachment; filename*=UTF-8''" . rawurlencode($filenameOrigin);
    header('Cache-Control: no-cache, must-revalidate');
    header('Pragma: no-cache');
} else {
    $safe = addcslashes($filenameOrigin, "\"\\");
    $disposition = 'attachment; filename="' . $safe . '"; filename*=UTF-8\'\'' . rawurlencode($filenameOrigin);
    header('Cache-Control: no-cache, must-revalidate');
    header('Pragma: no-cache');
}

header('Expires: 0');
header('Content-Type: application/octet-stream');
header("Content-Disposition: $disposition");
header('Content-Transfer-Encoding: binary');
header('Content-Length: ' . $filesize);

if (function_exists('ob_get_level')) {
    while (ob_get_level() > 0) {
        ob_end_clean();
    }
} else {
    ob_clean();
}

flush();

$fp = fopen($filepath, 'rb');
if ($fp !== false) {
    while (!feof($fp)) {
        echo fread($fp, 8192);
        flush();
    }
    fclose($fp);
} else {
    readfile($filepath);
}

exit;
