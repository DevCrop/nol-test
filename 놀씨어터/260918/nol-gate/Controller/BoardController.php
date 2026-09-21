<?php
require_once "../../inc/lib/base.class.php";
require_once "../Model/BoardModel.php";
require_once "../lib/AuditLogger.php";
$role->requireLogin();
$role->requireCanModify();

header('Content-Type: application/json');

try {
    $input = $_POST;
    $mode  = $input['mode'] ?? '';

    if ($mode === 'sort') {

        $no    = isset($input['no']) ? (int)$input['no'] : null;
        $newNo = isset($input['new_no']) ? (int)$input['new_no'] : 0;

        if ($no === null || $newNo <= 0) {
            echo json_encode(['success' => false, 'message' => '잘못된 데이터']);
            exit;
        }


        $db = DB::getInstance();
        $stmt = $db->prepare("SELECT * FROM nb_board WHERE no = :no");
        $stmt->execute([':no' => $no]);
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

        $minSortNo = BoardModel::getMinSortNo();
        $maxSortNo = BoardModel::getMaxSortNo();

        if ($newNo > $maxSortNo) {
            echo json_encode(['success' => false, 'message' => '제일 높은 순서의 게시물입니다.']);
            exit;
        }

        if ($newNo < $minSortNo) {
            echo json_encode(['success' => false, 'message' => '제일 낮은 순서의 게시물입니다.']);
            exit;
        }

        BoardModel::shiftSortNosForUpdate($oldNo, $newNo, $no);

        $dataToUpdate = [
            'sort_no' => $newNo
        ];


        $updateResult = BoardModel::update($no, $dataToUpdate);
        AuditLogger::ifOk($updateResult, 'update', 'board', $no, (string) ($currentData['title'] ?? $no), [
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