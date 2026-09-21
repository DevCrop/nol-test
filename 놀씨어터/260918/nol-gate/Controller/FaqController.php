<?php
require_once "../../inc/lib/base.class.php";
require_once "../Model/FaqModel.php";
require_once "../lib/AuditLogger.php";
require_once "../core/Validator.php";
$role->requireLogin();
$role->requireCanModify();

header('Content-Type: application/json');

try {
    $input = $_POST;
    $mode = $input['mode'] ?? '';

    if ($mode === 'insert') {
        $validator = new Validator();
        $validator->require('categories', $input['categories'] ?? '', '카테고리');
        $validator->require('question', $input['question'] ?? '', '질문');
        $validator->require('answer', $input['answer'] ?? '', '답변');

        if ($validator->fails()) {
            echo json_encode([
                'success' => false,
                'message' => implode("\n", $validator->getErrors())
            ]);
            exit;
        }
        
		$data = [
            'categories' => (int)$input['categories'],
            'question'   => trim((string)$input['question']),
            'answer'     => trim((string)$input['answer']),
            'is_active'  => (int)($input['is_active'] ?? 1)
        ];

		FaqModel::bumpSortNosOnInsert();

		$data['sort_no'] = 1;

		$result = FaqModel::insert($data);
        AuditLogger::ifOk($result, 'create', 'faq', AuditLogger::lastId(), (string) $data['question']);

        echo json_encode([
            'success' => (bool)$result,
            'message' => $result ? 'FAQ가 등록되었습니다.' : '등록 실패'
        ]);
        exit;
    }

    if ($mode === 'update') {
        $id = (int)($input['id'] ?? 0);
        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'ID가 없습니다.']);
            exit;
        }

        $validator = new Validator();
        $validator->require('categories', $input['categories'] ?? '', '카테고리');
        $validator->require('question', $input['question'] ?? '', '질문');
        $validator->require('answer', $input['answer'] ?? '', '답변');

        if ($validator->fails()) {
            echo json_encode([
                'success' => false,
                'message' => implode("\n", $validator->getErrors())
            ]);
            exit;
        }

        $newSortNo = (int)($input['sort_no'] ?? 0);

        $stmt = DB::getInstance()->prepare("SELECT sort_no FROM nb_faqs WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $oldSortNo = (int)$stmt->fetchColumn();

        if ($newSortNo !== $oldSortNo && $newSortNo > 0) {
            FaqModel::shiftSortNosForUpdate($oldSortNo, $newSortNo, $id);
        }

        $data = [
            'categories' => (int)$input['categories'],
            'question'   => trim((string)$input['question']),
            'answer'     => trim((string)$input['answer']),
            'sort_no'    => $newSortNo,
            'is_active'  => (int)($input['is_active'] ?? 1)
        ];

        $result = FaqModel::update($id, $data);
        AuditLogger::ifOk($result, 'update', 'faq', $id, (string) $data['question']);

        echo json_encode([
            'success' => (bool)$result,
            'message' => $result ? '수정되었습니다.' : '수정 실패'
        ]);
        exit;
    }

    if ($mode === 'delete') {
        $id = (int)($input['id'] ?? 0);
        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'ID가 없습니다.']);
            exit;
        }

        $result = FaqModel::delete($id);
        AuditLogger::ifOk($result, 'delete', 'faq', $id, (string) $id);

        echo json_encode([
            'success' => (bool)$result,
            'message' => $result ? '삭제되었습니다.' : '삭제 실패'
        ]);
        exit;
    }

    if ($mode === 'delete_array') {
        $ids = json_decode($input['ids'] ?? '[]', true);

        if (!is_array($ids) || count($ids) === 0) {
            echo json_encode(['success' => false, 'message' => '삭제할 항목이 없습니다.']);
            exit;
        }

        $result = FaqModel::deleteMultiple($ids);
        AuditLogger::ifOk($result, 'delete', 'faq', 0, count($ids) . '건', ['ids' => $ids]);

        echo json_encode([
            'success' => (bool)$result,
            'message' => $result ? '선택 항목이 삭제되었습니다.' : '삭제 실패'
        ]);
        exit;
    }


    // ===================== 추가: sort 모드 =====================
    /*
    if ($mode === 'sort') {
        $id = (int)($input['id'] ?? 0);
        $newNo = (int)($input['new_no'] ?? 0);

        if (!$id || !$newNo) {
            echo json_encode(['success' => false, 'message' => '잘못된 데이터']);
            exit;
        }

        $db = DB::getInstance();
        // 현재 레코드 전체 데이터 조회
        $stmt = $db->prepare("SELECT * FROM nb_faqs WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $currentData = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$currentData) {
            echo json_encode(['success' => false, 'message' => '존재하지 않는 항목입니다.']);
            exit;
        }

        $oldNo = (int)$currentData['sort_no'];

        if ($oldNo === $newNo) {
            echo json_encode(['success' => true, 'message' => '변경 없음']);
            exit;
        }

        FaqModel::shiftSortNosForUpdate($oldNo, $newNo, $id);

        $dataToUpdate = [
            'branch_id'  => $currentData['branch_id'],
            'categories' => $currentData['categories'],
            'question'   => $currentData['question'],
            'answer'     => $currentData['answer'],
            'is_active'  => $currentData['is_active'],
            'sort_no'    => $newNo,
        ];

        $updateResult = FaqModel::update($id, $dataToUpdate);

        echo json_encode([
            'success' => (bool)$updateResult,
            'message' => $updateResult ? '순서가 변경되었습니다.' : '변경 실패'
        ]);
        exit;
    }*/

    if ($mode === 'sort') {
        $id = (int)($input['id'] ?? 0);
        $newNo = (int)($input['new_no'] ?? 0);

        if (!$id || $newNo <= 0) {  // 0 이하는 잘못된 데이터로 처리
            echo json_encode(['success' => false, 'message' => '잘못된 데이터']);
            exit;
        }

        $db = DB::getInstance();
        $stmt = $db->prepare("SELECT * FROM nb_faqs WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $currentData = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$currentData) {
            echo json_encode(['success' => false, 'message' => '존재하지 않는 항목입니다.']);
            exit;
        }

        $oldNo = (int)$currentData['sort_no'];

        if ($oldNo === $newNo) {
            echo json_encode(['success' => true, 'message' => '변경 없음']);
            exit;
        }

        $minSortNo = FaqModel::getMinSortNo();
        $maxSortNo = FaqModel::getMaxSortNo();

        if ($newNo > $maxSortNo) {
            echo json_encode(['success' => false, 'message' => '제일 높은 순서의 게시물입니다.']);
            exit;
        }

        if ($newNo < $minSortNo) {
            echo json_encode(['success' => false, 'message' => '제일 낮은 순서의 게시물입니다.']);
            exit;
        }


        FaqModel::shiftSortNosForUpdate($oldNo, $newNo, $id);

        $dataToUpdate = [
            'categories' => $currentData['categories'],
            'question'   => $currentData['question'],
            'answer'     => $currentData['answer'],
            'is_active'  => $currentData['is_active'],
            'sort_no'    => $newNo,
        ];

        $updateResult = FaqModel::update($id, $dataToUpdate);
        AuditLogger::ifOk($updateResult, 'update', 'faq', $id, (string) ($currentData['question'] ?? $id), [
            'sort_no' => $newNo,
        ]);

        echo json_encode([
            'success' => (bool)$updateResult,
            'message' => $updateResult ? '순서가 변경되었습니다.' : '변경 실패'
        ]);
        exit;
    }



    

    // ===================== 추가 끝 =====================





    echo json_encode(['success' => false, 'message' => '유효하지 않은 요청입니다.']);
    exit;
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => ClientFault::message($e)
    ]);
}