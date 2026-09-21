<?php

include_once "../../../../inc/lib/base.class.php";
include_once "../../../lib/admin.check.ajax.php";
require_once dirname(__DIR__, 3) . "/lib/AuditLogger.php";
$role->requireLogin();
$role->requireCanModify();

$pdo = DB::getInstance();
$mode = $_POST['mode'] ?? '';

if ($mode === 'delete') {
    $no = (int)($_POST['no'] ?? 0);
    if ($no <= 0) {
        echo json_encode(["result" => "fail", "msg" => "잘못된 요청입니다."]);
        exit;
    }

    $fileFields = ['file_1', 'file_2', 'file_3', 'file_4', 'file_5'];
    $stmt = $pdo->prepare("SELECT " . implode(',', $fileFields) . " FROM nb_request WHERE no = :no");
    $stmt->execute([':no' => $no]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row) {
        foreach ($fileFields as $field) {
            if (!empty($row[$field])) {
                $filePath = rtrim($UPLOAD_DIR_REQUEST, '/\\') . '/' . $row[$field];
                if (file_exists($filePath)) {
                    @unlink($filePath);
                }
            }
        }
    }

    $stmt = $pdo->prepare("DELETE FROM nb_request WHERE no = :no");
    $result = $stmt->execute([':no' => $no]);

    echo json_encode([
        "result" => $result ? "success" : "fail",
        "msg" => $result ? "삭제되었습니다." : "삭제 중 오류가 발생했습니다."
    ]);
    if ($result) {
        AuditLogger::record('delete', 'inquiry', $no, (string) $no);
    }
    exit;
}

if ($mode === 'delete.array') {
    $nos = $_POST['nos'] ?? '';
    if (empty($nos)) {
        echo json_encode(["result" => "fail", "msg" => "삭제할 항목을 선택해주세요."]);
        exit;
    }

    $noArr = array_filter(array_map('intval', explode(',', $nos)));
    if (empty($noArr)) {
        echo json_encode(["result" => "fail", "msg" => "잘못된 요청입니다."]);
        exit;
    }

    $placeholders = implode(',', array_fill(0, count($noArr), '?'));

    $fileFields = ['file_1', 'file_2', 'file_3', 'file_4', 'file_5'];
    $stmt = $pdo->prepare("SELECT " . implode(',', $fileFields) . " FROM nb_request WHERE no IN ($placeholders)");
    $stmt->execute($noArr);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($rows as $row) {
        foreach ($fileFields as $field) {
            if (!empty($row[$field])) {
                $filePath = rtrim($UPLOAD_DIR_REQUEST, '/\\') . '/' . $row[$field];
                if (file_exists($filePath)) {
                    @unlink($filePath);
                }
            }
        }
    }

    $stmt = $pdo->prepare("DELETE FROM nb_request WHERE no IN ($placeholders)");
    $result = $stmt->execute($noArr);

    echo json_encode([
        "result" => $result ? "success" : "fail",
        "msg" => $result ? "선택한 항목이 삭제되었습니다." : "삭제 중 오류가 발생했습니다."
    ]);
    if ($result) {
        AuditLogger::record('delete', 'inquiry', 0, count($noArr) . '건', ['ids' => $noArr]);
    }
    exit;
}

echo json_encode(["result" => "fail", "msg" => "잘못된 요청입니다."]);
