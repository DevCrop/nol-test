<?php
ob_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/inc/lib/base.class.php';
if (!in_array($_SERVER['REQUEST_METHOD'] ?? 'GET', ['GET','HEAD'], true)) { http_response_code(405); header('Allow: GET, HEAD'); exit; }
$no = filter_input(INPUT_GET, 'no', FILTER_VALIDATE_INT, ['options'=>['min_range'=>1]]);
$field = $_GET['fld'] ?? '';
if (!$no || !is_string($field) || !\Security\BoardAttachment::supports($field)) { http_response_code(400); exit('잘못된 요청입니다.'); }
try {
    $file = \Security\BoardAttachment::find(DB::getInstance(), $UPLOAD_DIR_BOARD, $no, $field, false);
    if ($file === null) { http_response_code(404); exit('파일을 찾을 수 없습니다.'); }
    \Security\BinaryDownload::send($file['path'], $file['name']);
} catch (Throwable $e) {
    error_log('[download] failed'); http_response_code(500); exit('파일을 처리할 수 없습니다.');
}
