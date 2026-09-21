<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/inc/lib/base.class.php';

$no = filter_input(INPUT_GET, 'no', FILTER_VALIDATE_INT);
$field = (string) ($_GET['fld'] ?? '');
$map = [
    'thumb' => ['thumb_image', 'thumb_image'],
    'attach1' => ['file_attach_1', 'file_attach_origin_1'],
    'attach2' => ['file_attach_2', 'file_attach_origin_2'],
    'attach3' => ['file_attach_3', 'file_attach_origin_3'],
    'attach4' => ['file_attach_4', 'file_attach_origin_4'],
    'attach5' => ['file_attach_5', 'file_attach_origin_5'],
];
if (!$no || !isset($map[$field])) { http_response_code(400); exit('잘못된 요청입니다.'); }

try {
    $stmt = DB::getInstance()->prepare('SELECT * FROM nb_board WHERE no = ? AND sitekey = ? LIMIT 1');
    $stmt->execute([$no, 'BLUESQ']); $row = (array) $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$row || ($row['is_view'] ?? 'N') !== 'Y' || ($row['is_secret'] ?? 'N') === 'Y') { http_response_code(404); exit('파일을 찾을 수 없습니다.'); }
    [$storedKey, $originKey] = $map[$field]; $stored = basename((string) ($row[$storedKey] ?? ''));
    $path = \Security\UploadGuard::pathInside($UPLOAD_DIR_BOARD, $stored);
    if ($stored === '' || $path === null) { http_response_code(404); exit('파일을 찾을 수 없습니다.'); }
    $name = basename((string) ($row[$originKey] ?? $stored));
    header('Content-Type: application/octet-stream'); header('X-Content-Type-Options: nosniff');
    header("Content-Disposition: attachment; filename*=UTF-8''" . rawurlencode($name));
    header('Content-Length: ' . filesize($path)); readfile($path); exit;
} catch (Throwable $e) {
    error_log('[download] failed'); http_response_code(500); exit('파일을 처리할 수 없습니다.');
}
