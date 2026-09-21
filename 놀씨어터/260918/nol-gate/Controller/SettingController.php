<?php

require_once "../../inc/lib/base.class.php";
require_once "../Model/SettingModel.php";
require_once "../lib/AuditLogger.php";
require_once "../core/Validator.php";
$role->requireLogin();
$role->requireCanModify();

header('Content-Type: application/json');

try {
    $input = $_POST;
    $mode = $input['mode'] ?? '';

    // ============ insert ============
    if ($mode === 'insert') {
        $validator = new Validator();
        $validator->require('title', $input['title'] ?? '', '제목');
        $validator->require('tag_content', $input['tag_content'] ?? '', '태그 내용');

        if ($validator->fails()) {
            echo json_encode([
                'success' => false,
                'message' => implode("\n", $validator->getErrors())
            ]);
            exit;
        }

        $result = SiteTagModel::insert([
            'title' => trim($input['title']),
            'tag_content' => trim($input['tag_content']),
            'location' => isset($input['location']) ? (int)$input['location'] : 1,
            'is_active' => 1
        ]);
        AuditLogger::ifOk($result, 'create', 'setting', AuditLogger::lastId(), trim($input['title']));

        echo json_encode([
            'success' => $result,
            'message' => $result ? '등록되었습니다.' : '등록 실패'
        ]);
        exit;
    }


    // ============ update ============
    if ($mode === 'update') {
        $id = (int)($input['id'] ?? 0);
        $validator = new Validator();

        $validator->require('title', $input['title'] ?? '', '제목');
        $validator->require('tag_content', $input['tag_content'] ?? '', '태그 내용');

        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'ID가 없습니다.']);
            exit;
        }

        if ($validator->fails()) {
            echo json_encode([
                'success' => false,
                'message' => implode("\n", $validator->getErrors())
            ]);
            exit;
        }

        $result = SiteTagModel::update($id, [
            'title' => trim($input['title']),
            'tag_content' => trim($input['tag_content']),
            'location' => isset($input['location']) ? (int)$input['location'] : 1,
            'is_active' => isset($input['is_active']) ? (int)$input['is_active'] : 1
        ]);
        AuditLogger::ifOk($result, 'update', 'setting', $id, trim($input['title']));

        echo json_encode([
            'success' => $result,
            'message' => $result ? '수정되었습니다.' : '수정 실패'
        ]);
        exit;
    }


    // ============ delete ============
    if ($mode === 'delete') {
        $id = (int)($input['id'] ?? 0);

        if (!$id) {
            echo json_encode([
                'success' => false,
                'message' => 'ID가 없습니다.'
            ]);
            exit;
        }

        $result = SiteTagModel::delete($id);
        AuditLogger::ifOk($result, 'delete', 'setting', $id, (string) $id);

        echo json_encode([
            'success' => $result,
            'message' => $result ? '삭제되었습니다.' : '삭제 실패'
        ]);
        exit;
    }

    // DELETE_ARRAY
    if ($mode === 'delete_array') {
        $ids = json_decode($input['ids'] ?? '[]', true);

        if (!is_array($ids) || count($ids) === 0) {
            echo json_encode(['success' => false, 'message' => '삭제할 항목이 없습니다.']);
            exit;
        }

         $result = SiteTagModel::deleteMultiple($ids);
        AuditLogger::ifOk($result, 'delete', 'setting', 0, count($ids) . '건', ['ids' => $ids]);
         
        echo json_encode([
            'success' => $result,
            'message' => $result ? '선택 항목이 삭제되었습니다.' : '삭제 실패'
        ]);
        exit;
    }



    echo json_encode([
        'success' => false,
        'message' => '유효하지 않은 요청입니다.'
    ]);
    exit;

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => ClientFault::message($e)
    ]);
}