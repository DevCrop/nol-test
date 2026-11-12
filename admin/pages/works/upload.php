<?php

$root = $_SERVER['DOCUMENT_ROOT'];
include_once $root . "/inc/lib/base.class.php";

// admin.check.ajax.php 경로 수정 및 파일 존재 확인
$adminFile = $root . "/admin/lib/admin.check.ajax.php";
if (file_exists($adminFile)) {
    include_once $adminFile;
} else {
    error_log("Warning: $adminFile not found.");
}

$uploadDir = $root . '/uploads/works';

// 업로드 디렉터리 존재 여부 확인 후 생성
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

$jsonData = [
    'success' => false,
    'url' => null,
    'message' => '',
];

$method = strtoupper(isset($_REQUEST['_method']) ? $_REQUEST['_method'] : $_SERVER['REQUEST_METHOD']);

try {
    if ($method === 'POST') {
        if (function_exists('hasImage') && hasImage('file')) {
            if (function_exists('uploadImage')) {
                $newName = uploadImage('file', $uploadDir);
                
                if (!$newName) {
                    throw new Exception('이미지 업로드에 실패했습니다.');
                }

                $jsonData['url'] = '/uploads/works/' . $newName;
                $jsonData['message'] = '이미지 업로드에 성공했습니다.';
                $jsonData['success'] = true;
            } else {
                throw new Exception('uploadImage 함수가 정의되지 않았습니다.');
            }
        } else {
            throw new Exception('업로드할 파일이 없습니다.');
        }
    }
} catch (Throwable $e) {
    $jsonData['message'] = $e instanceof RuntimeException ? $e->getMessage() : '이미지를 업로드하지 못했습니다.';
}

header('Content-Type: application/json; charset=utf-8');
if(ob_get_level()>0)ob_clean();
echo json_encode($jsonData);
exit;
