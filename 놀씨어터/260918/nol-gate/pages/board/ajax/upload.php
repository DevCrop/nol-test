<?php

include_once "../../../../inc/lib/base.class.php";
include_once "../../../lib/admin.check.ajax.php";
include_once "../../../lib/upload.guard.php";
$role->requireLogin();
$role->requireCanModify();

$root = rtrim($NO_PROJECT_ROOT, '/\\');
$uploadDir = $UPLOAD_DIR_BOARD;
$uploadBaseDir = $UPLOAD_WDIR_BOARD;
$maxFileSize = 20 * 1024 * 1024;

if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true) && !is_dir($uploadDir)) {
    echo json_encode(['success' => false, 'filename' => null, 'error' => '업로드 경로 생성 실패']);
    exit;
}

if (!is_writable($uploadDir)) {
    echo json_encode(['success' => false, 'filename' => null, 'error' => '업로드 경로 권한 오류']);
    exit;
}

$method = $_POST['_method'] ?? $_SERVER['REQUEST_METHOD'];
$method = strtolower($method);

$jsonData = ['success' => false, 'filename' => null];

if ($method === 'post') {
    $ext = isset($_POST['extension']) ? strtolower(trim((string)$_POST['extension'])) : '';

    if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
        $tmpName = $_FILES['file']['tmp_name'];
        $fileSize = (int)($_FILES['file']['size'] ?? 0);
        [$ok, $error] = no_upload_guard_check(
            (string) ($_FILES['file']['name'] ?? ''),
            $ext,
            (string) $tmpName,
            $fileSize,
            $maxFileSize
        );

        if (!$ok) {
            $jsonData = ['success' => false, 'filename' => null, 'error' => $error];
        } elseif (!is_uploaded_file($tmpName)) {
            $jsonData = ['success' => false, 'filename' => null, 'error' => '유효한 업로드 파일이 아닙니다.'];
        } else {
            $newName = uniqid('', true) . '.' . $ext;
            $uploadFile = $uploadDir . '/' . $newName;

            if (move_uploaded_file($tmpName, $uploadFile)) {
                $jsonData = [
                    'filename' => $uploadBaseDir . '/' . $newName,
                    'success' => true,
                ];
            } else {
                $jsonData = ['success' => false, 'filename' => null, 'error' => '업로드 실패'];
            }
        }
    } else {
        $jsonData = [
            'filename' => null,
            'success' => false,
            'error' => isset($_FILES['file']) ? 'File upload error' : '파일이 없습니다.',
        ];
    }
}

if ($method === 'delete') {
    $link = trim((string)($_POST['link'] ?? ''), '/');
    $targetPath = $link !== '' ? realpath($root . '/' . $link) : false;
    $uploadDirReal = realpath($uploadDir);
    $isInsideUploadDir = $targetPath && $uploadDirReal && strpos($targetPath, $uploadDirReal) === 0;

    if ($link !== '' && strpos($link, 'uploads/board/') === 0 && $isInsideUploadDir && file_exists($targetPath)) {
        $result = unlink($targetPath);
        $jsonData = ['success' => (bool)$result];
    } else {
        $jsonData = ['success' => false];
    }
}

echo json_encode($jsonData);
