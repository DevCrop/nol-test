<?php

include_once "../../../../inc/lib/base.class.php";
include_once "../../../lib/admin.check.ajax.php";
$role->requireLogin();
$role->requireCanModify();

$root = rtrim($NO_PROJECT_ROOT, '/\\');
$uploadDir = $UPLOAD_DIR_WORKS;
$uploadBaseDir = $UPLOAD_WDIR_WORKS;

// 업로드 허용 확장자 화이트리스트 (악성파일 업로드 방지 - 이미지만)
$allowed_ext = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

$method = $_POST['_method'] ?? $_SERVER['REQUEST_METHOD'];
$method = strtolower($method);

$result = array();
$jsonData = array('success' => false, 'filename' => null);

if ($method === 'post') {

    $ext = isset($_POST['extension']) ? strtolower(trim($_POST['extension'])) : '';

    if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {

        $realExt = strtolower(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));

        if ($ext === '' || !in_array($ext, $allowed_ext, true)) {
            $jsonData = array('success' => false, 'filename' => null, 'error' => '허용되지 않는 확장자입니다.');
        } elseif ($realExt !== $ext || !in_array($realExt, $allowed_ext, true)) {
            $jsonData = array('success' => false, 'filename' => null, 'error' => '파일 확장자가 일치하지 않거나 허용되지 않습니다.');
        } else {
            $newName = uniqid() . '.' . $ext;
            $uploadFile = $uploadDir . '/' . $newName;

            if (move_uploaded_file($_FILES['file']['tmp_name'], $uploadFile)) {
                $jsonData = array(
                    'filename' => $uploadBaseDir . '/' . $newName,
                    'success' => true
                );
            } else {
                $jsonData = array('success' => false, 'filename' => null, 'error' => '저장 실패');
            }
        }
    } else {
        $jsonData = array(
            'filename' => null,
            'success' => false,
            'error' => isset($_FILES['file']) ? 'File upload error' : '파일이 없습니다.'
        );
    }
}

if ($method === 'delete') {
    $link = trim($_POST['link'] ?? '', '/');
    $targetPath = $link !== '' ? realpath($root . '/' . $link) : false;
    $uploadDirReal = realpath($uploadDir);
    $isInsideUploadDir = $targetPath && $uploadDirReal && strpos($targetPath, $uploadDirReal) === 0;

    if ($link !== '' && strpos($link, 'uploads/works/') === 0 && $isInsideUploadDir && file_exists($targetPath)) {
        $result = unlink($targetPath);
        $jsonData = array('success' => (bool)$result);
    } else {
        $jsonData = array('success' => false);
    }
}

echo json_encode($jsonData);

