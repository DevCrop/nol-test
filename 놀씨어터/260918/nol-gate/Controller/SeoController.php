<?php

require_once "../../inc/lib/base.class.php";
require_once "../Model/SeoModel.php";
require_once "../lib/AuditLogger.php";
require_once "../core/Validator.php";
$role->requireLogin();
$role->requireCanModify();

header('Content-Type: application/json');

try {
    $input = $_POST;
    $mode = $input['mode'] ?? '';

    // ============ INSERT ============
    if ($mode === 'insert') {
        $validator = new Validator();
        $validator->require('path', $input['path'] ?? '', '경로');
        $validator->require('page_title', $input['page_title'] ?? '', '페이지 제목');
        $validator->require('meta_title', $input['meta_title'] ?? '', '메타 타이틀');
        $validator->require('meta_description', $input['meta_description'] ?? '', '메타 설명');
        $validator->require('meta_keywords', $input['meta_keywords'] ?? '', '메타 키워드');

        if ($validator->fails()) {
            echo json_encode([
                'success' => false,
                'message' => implode("\n", $validator->getErrors())
            ]);
            exit;
        }

        // 유효성 통과 후 실제 저장
        $result = SeoModel::insert([
            'path' => trim($input['path']),
            'page_title' => trim($input['page_title']),
            'meta_title' => trim($input['meta_title']),
            'meta_description' => trim($input['meta_description']),
            'meta_keywords' => trim($input['meta_keywords']),
        ]);
        AuditLogger::ifOk($result, 'create', 'seo', AuditLogger::lastId(), trim($input['path']));

        echo json_encode([
            'success' => $result,
            'message' => $result ? '등록되었습니다.' : '등록 실패'
        ]);
        exit;
    }


    // ============ UPDATE ============
    if ($mode === 'update') {
        $id = (int)($input['id'] ?? 0);

        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'ID가 없습니다.']);
            exit;
        }

        $validator = new Validator();
        $validator->require('path', $input['path'] ?? '', '경로');
        $validator->require('page_title', $input['page_title'] ?? '', '페이지 제목');
        $validator->require('meta_title', $input['meta_title'] ?? '', '메타 타이틀');
        $validator->require('meta_description', $input['meta_description'] ?? '', '메타 설명');
        $validator->require('meta_keywords', $input['meta_keywords'] ?? '', '메타 키워드');

        if ($validator->fails()) {
            echo json_encode([
                'success' => false,
                'message' => implode("\n", $validator->getErrors())
            ]);
            exit;
        }

        $data = [
            'path' => trim($input['path']),
            'page_title' => trim($input['page_title']),
            'meta_title' => trim($input['meta_title']),
            'meta_description' => trim($input['meta_description']),
            'meta_keywords' => trim($input['meta_keywords']),
        ];

        $result = SeoModel::update($id, $data);
        AuditLogger::ifOk($result, 'update', 'seo', $id, $data['path']);

        echo json_encode([
            'success' => $result,
            'message' => $result ? '수정되었습니다.' : '수정 실패'
        ]);
        exit;
    }

    // ============ DELETE ============
    if ($mode === 'delete') {
        $id = (int)($input['id'] ?? 0);

        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'ID가 없습니다.']);
            exit;
        }

        $result = SeoModel::delete($id);
        AuditLogger::ifOk($result, 'delete', 'seo', $id, (string) $id);

        echo json_encode([
            'success' => $result,
            'message' => $result ? '삭제되었습니다.' : '삭제 실패'
        ]);
        exit;
    }

    // ============ DELETE_ARRAY ============
    if ($mode === 'delete_array') {
        $ids = json_decode($input['ids'] ?? '[]', true);

        if (!is_array($ids) || count($ids) === 0) {
            echo json_encode(['success' => false, 'message' => '삭제할 항목이 없습니다.']);
            exit;
        }

        $result = SeoModel::deleteMultiple($ids);
        AuditLogger::ifOk($result, 'delete', 'seo', 0, count($ids) . '건', ['ids' => $ids]);

        echo json_encode([
            'success' => $result,
            'message' => $result ? '선택 항목이 삭제되었습니다.' : '삭제 실패'
        ]);
        exit;
    }

    echo json_encode(['success' => false, 'message' => '유효하지 않은 요청입니다.']);
    exit;
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => ClientFault::message($e)
    ]);
}