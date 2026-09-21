<?php
require_once '../../../../inc/lib/base.class.php';
require_once '../../../lib/admin.check.ajax.php';
header('Content-Type: application/json; charset=utf-8');

$root = rtrim((string) $_SERVER['DOCUMENT_ROOT'], '/\\');
$uploadDir = $root . '/uploads/board';
$method = strtolower((string) ($_POST['_method'] ?? $_SERVER['REQUEST_METHOD'] ?? ''));
$reply = ['success' => false];

try {
    if ($method === 'post') {
        [$ext] = \Security\UploadGuard::image((array) ($_FILES['file'] ?? []));
        if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true)) throw new RuntimeException('업로드 폴더를 준비할 수 없습니다.');
        $name = bin2hex(random_bytes(16)) . '.' . $ext;
        if (!move_uploaded_file((string) $_FILES['file']['tmp_name'], $uploadDir . '/' . $name)) throw new RuntimeException('파일 저장에 실패했습니다.');
        $reply = ['success' => true, 'filename' => '/uploads/board/' . $name];
        \Security\AuditLogger::record('create', 'board_upload', 0, $name);
    } elseif ($method === 'delete') {
        $link = (string) ($_POST['link'] ?? '');
        if (strpos($link, '/uploads/board/') === 0) $link = substr($link, strlen('/uploads/board/'));
        $path = \Security\UploadGuard::pathInside($uploadDir, $link);
        if ($path === null) throw new RuntimeException('잘못된 파일 경로입니다.');
        $reply = ['success' => unlink($path)];
        if ($reply['success']) \Security\AuditLogger::record('delete', 'board_upload', 0, basename($path));
    } else {
        http_response_code(405);
    }
} catch (Throwable $e) {
    http_response_code(400); $reply['error'] = blue_safe_error($e);
}
echo json_encode($reply, JSON_UNESCAPED_UNICODE);
