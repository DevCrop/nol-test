<?php
require_once "../../inc/lib/base.class.php";
require_once "../core/Validator.php";
require_once "../Model/PopupModel.php";
require_once "../lib/AuditLogger.php";
$role->requireLogin();
$role->requireCanModify();

header('Content-Type: application/json');

try {
    $input = $_POST;
    $mode = $input['mode'] ?? '';
    $upload_path = $UPLOAD_DIR_POPUP;

    $validator = new Validator();

    if ($mode === 'insert') {
        $data = [
            'title'         => trim($input['title'] ?? ''),
            'popup_type'   => (int)($input['popup_type'] ?? 1),
            'popup_path'    => trim($input['popup_path'] ?? ''),
            'has_link'      => (int)($input['has_link'] ?? 2),
            'link_url'      => safe_url(trim($input['link_url'] ?? ''), ['http', 'https'], true) ?: null,
            'is_target'     => (int)($input['is_target'] ?? 1),
            'is_active'     => (int)($input['is_active'] ?? 1),
            'description'   => sanitize_html_fragment(trim($input['description'] ?? '')) ?: null,
            'start_at'      => !empty(trim($input['start_at'] ?? '')) ? trim($input['start_at']) : null,
            'end_at'        => !empty(trim($input['end_at'] ?? '')) ? trim($input['end_at']) : null,
            'is_unlimited'  => (int)($input['is_unlimited'] ?? 1),
        ];

        $validator->require('title', $data['title'], '제목');
        $validator->require('popup_path', $data['popup_path'], '팝업 위치');

        // 텍스트 팝업(popup_type = 2)이 아닐 때만 이미지 검증
        if ($data['popup_type'] !== 2) {
            $image = imageUpload($upload_path, $_FILES['popup_image'] ?? []);
            if (empty($image['saved'])) {
                $validator->require('popup_image', '', '팝업 이미지');
            } else {
                $data['popup_image'] = $image['saved'];
            }
        }

        if ($validator->fails()) {
            echo json_encode([
                'success' => false,
                'message' => implode("\n", $validator->getErrors())
            ]);
            exit;
        }

        PopupModel::bumpSortNosOnInsert($data['popup_path']);
        $data['sort_no'] = 1;
        $result = PopupModel::insert($data);
        AuditLogger::ifOk($result, 'create', 'popup', AuditLogger::lastId(), (string) $data['title']);

        echo json_encode([
            'success' => $result,
            'message' => $result ? '팝업이 등록되었습니다.' : '등록 실패'
        ]);
        exit;
    }

    if ($mode === 'update') {
        $id = (int)($input['id'] ?? 0);
        if (!$id) throw new Exception("ID가 없습니다.");

        $data = [
            'title'         => trim($input['title'] ?? ''),
            'popup_type'   => (int)($input['popup_type'] ?? 1),
            'popup_path'    => trim($input['popup_path'] ?? ''),
            'has_link'      => (int)($input['has_link'] ?? 2),
            'link_url'      => safe_url(trim($input['link_url'] ?? ''), ['http', 'https'], true) ?: null,
            'is_target'     => (int)($input['is_target'] ?? 1),
            'is_active'     => (int)($input['is_active'] ?? 1),
            'description'   => sanitize_html_fragment(trim($input['description'] ?? '')) ?: null,
            'start_at'      => !empty(trim($input['start_at'] ?? '')) ? trim($input['start_at']) : null,
            'end_at'        => !empty(trim($input['end_at'] ?? '')) ? trim($input['end_at']) : null,
            'is_unlimited'  => (int)($input['is_unlimited'] ?? 1),
        ];

        $newSortNo = (int)($input['sort_no'] ?? 0);
        $data['sort_no'] = $newSortNo;

        $validator->require('title', $data['title'], '제목');
        $validator->require('popup_path', $data['popup_path'], '팝업 위치');

        $existing = PopupModel::find($id);
        if (!$existing) {
            echo json_encode(['success' => false, 'message' => '데이터를 찾을 수 없습니다.']);
            exit;
        }

        $oldSortNo = (int)$existing['sort_no'];
        if ($newSortNo !== $oldSortNo && $newSortNo > 0) {
            PopupModel::shiftSortNosForUpdate($oldSortNo, $newSortNo, $id);
        }

        // 텍스트 팝업(popup_type = 2)이 아닐 때만 이미지 검증
        if ($data['popup_type'] !== 2) {
            // 새 이미지가 업로드되었는지 확인
            $hasNewImage = !empty($_FILES['popup_image']) &&
                isset($_FILES['popup_image']['error']) &&
                $_FILES['popup_image']['error'] === UPLOAD_ERR_OK;

            if ($hasNewImage) {
                // 기존 이미지 삭제
                if (!empty($existing['popup_image']) && file_exists($upload_path . '/' . $existing['popup_image'])) {
                    imageDelete($upload_path . '/' . $existing['popup_image']);
                }

                // 새 이미지 업로드
                $image = imageUpload($upload_path, $_FILES['popup_image']);

                // imageUpload가 실패하면 exit되므로 여기까지 왔다면 성공
                // saved 값이 있는지 확인
                if (!empty($image['saved'])) {
                    $data['popup_image'] = $image['saved'];
                }
            } elseif (empty($existing['popup_image'])) {
                // 새 이미지도 없고 기존 이미지도 없으면 에러
                $validator->require('popup_image', '', '팝업 이미지');
            }
            // else: 새 이미지가 없고 기존 이미지가 있으면 기존 이미지 유지 (아무것도 하지 않음)
        } else {
            // 텍스트 팝업인 경우 기존 이미지가 있으면 삭제
            if (!empty($existing['popup_image']) && file_exists($upload_path . '/' . $existing['popup_image'])) {
                imageDelete($upload_path . '/' . $existing['popup_image']);
                $data['popup_image'] = null;
            }
        }

        if ($validator->fails()) {
            echo json_encode([
                'success' => false,
                'message' => implode("\n", $validator->getErrors())
            ]);
            exit;
        }

        $result = PopupModel::update($id, $data);
        AuditLogger::ifOk($result, 'update', 'popup', $id, (string) $data['title']);

        echo json_encode([
            'success' => $result,
            'message' => $result ? '수정되었습니다.' : '수정 실패'
        ]);
        exit;
    }

    if ($mode === 'delete') {
        $id = (int)($input['id'] ?? 0);
        if (!$id) throw new Exception("ID가 없습니다.");

        $existing = PopupModel::find($id);
        if ($existing) {
            imageDelete($upload_path . '/' . $existing['popup_image']);
        }

        $result = PopupModel::delete($id);
        AuditLogger::ifOk($result, 'delete', 'popup', $id, (string) ($existing['title'] ?? $id));

        echo json_encode([
            'success' => $result,
            'message' => $result ? '삭제되었습니다.' : '삭제 실패'
        ]);
        exit;
    }

    if ($mode === 'delete_array') {
        $ids = json_decode($input['ids'] ?? '[]', true);

        if (!is_array($ids) || empty($ids)) {
            throw new Exception("삭제할 ID 목록이 없습니다.");
        }

        foreach ($ids as $id) {
            $existing = PopupModel::find((int)$id);
            if ($existing) {
                imageDelete($upload_path . '/' . $existing['popup_image']);
            }
        }

        $result = PopupModel::deleteMultiple($ids);
        AuditLogger::ifOk($result, 'delete', 'popup', 0, count($ids) . '건', ['ids' => $ids]);

        echo json_encode([
            'success' => $result,
            'message' => $result ? '선택 항목이 삭제되었습니다.' : '삭제 실패'
        ]);
        exit;
    }

    if ($mode === 'sort') {
        $id = (int)($input['id'] ?? 0);
        $newNo = (int)($input['new_no'] ?? 0);

        if (!$id || $newNo <= 0) {
            echo json_encode(['success' => false, 'message' => '잘못된 데이터']);
            exit;
        }

        $db = DB::getInstance();
        $stmt = $db->prepare("SELECT * FROM nb_popups WHERE id = :id");
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

        $minSortNo = PopupModel::getMinSortNo();
        $maxSortNo = PopupModel::getMaxSortNo();

        if ($newNo > $maxSortNo) {
            echo json_encode(['success' => false, 'message' => '제일 높은 순서의 게시물입니다.']);
            exit;
        }

        if ($newNo < $minSortNo) {
            echo json_encode(['success' => false, 'message' => '제일 낮은 순서의 게시물입니다.']);
            exit;
        }

        PopupModel::shiftSortNosForUpdate($oldNo, $newNo, $id);

        $dataToUpdate = [
            'title'        => $currentData['title'],
            'popup_type'   => (int)($currentData['popup_type'] ?? 1),
            'popup_path'   => $currentData['popup_path'] ?? '',
            'has_link'     => (int)$currentData['has_link'],
            'link_url'     => $currentData['link_url'],
            'is_target'    => (int)$currentData['is_target'],
            'is_active'    => (int)$currentData['is_active'],
            'description'  => $currentData['description'],
            'start_at'     => $currentData['start_at'],
            'end_at'       => $currentData['end_at'],
            'is_unlimited' => (int)$currentData['is_unlimited'],
            'popup_image'  => $currentData['popup_image'],
            'sort_no'      => $newNo,
        ];

        $updateResult = PopupModel::update($id, $dataToUpdate);
        AuditLogger::ifOk($updateResult, 'update', 'popup', $id, (string) ($currentData['title'] ?? $id), [
            'sort_no' => $newNo,
        ]);

        echo json_encode([
            'success' => (bool)$updateResult,
            'message' => $updateResult ? '순서가 변경되었습니다.' : '변경 실패'
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
