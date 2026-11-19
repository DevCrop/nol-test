<?php

require_once __DIR__ . '/../../../../inc/lib/base.class.php';
require_once dirname(__DIR__, 3) . '/lib/admin.check.ajax.php';
require_once dirname(__DIR__, 3) . '/lib/AuditLogger.php';
require_once dirname(__DIR__, 3) . '/lib/upload.guard.php';

$role->requireLogin();
$role->requireCanModify();

$respond = static function (bool $success, string $message): void {
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode(['result' => $success ? 'success' : 'fail', 'msg' => $message]);
    exit;
};

$mode = $_POST['mode'] ?? '';
if ($mode === 'delete') {
    $ids = [(int) ($_POST['no'] ?? 0)];
} elseif ($mode === 'delete.array' && is_string($_POST['nos'] ?? null)) {
    $ids = array_map('intval', explode(',', $_POST['nos']));
} else {
    $respond(false, '잘못된 요청입니다.');
}
$ids = array_values(array_unique(array_filter($ids, static function (int $id): bool {
    return $id > 0;
})));
if (!$ids) {
    $respond(false, '삭제할 항목을 선택해 주세요.');
}

$db = DB::getInstance();
$fields = ['file_1', 'file_2', 'file_3', 'file_4', 'file_5'];
$placeholders = implode(',', array_fill(0, count($ids), '?'));
$deleted = [];
try {
    PrivacyAccessModel::requireTransactionalRental($db);
    $db->beginTransaction();
    $stmt = $db->prepare('SELECT no, name, ' . implode(',', $fields) .
        " FROM nb_request WHERE sitekey = ? AND no IN ($placeholders) FOR UPDATE");
    $stmt->execute(array_merge([$NO_SITE_UNIQUE_KEY], $ids));

    $delete = $db->prepare('DELETE FROM nb_request WHERE sitekey = ? AND no = ?');
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $no = (int) $row['no'];
        $delete->execute([$NO_SITE_UNIQUE_KEY, $no]);
        if ($delete->rowCount() === 1) {
            PrivacyAccessLogger::record('delete', 'inquiry', $no, (string) $row['name'], '대관 신청 삭제');
            $deleted[$no] = $row;
        }
    }
    $db->commit();
} catch (Throwable $e) {
    if ($db->inTransaction()) $db->rollBack();
    http_response_code(503);
    $respond(false, '대관 신청을 삭제하지 못했습니다. 잠시 후 다시 시도해 주세요.');
}

foreach ($deleted as $no => $row) {
    foreach ($fields as $field) {
        $name = (string) $row[$field];
        if ($name === '') continue;
        $candidate = $UPLOAD_DIR_REQUEST . '/' . $name;
        if (no_upload_guard_path($UPLOAD_DIR_REQUEST, $candidate) !== null && !@unlink($candidate)) {
            error_log('[inquiry-delete] unable to remove attachment for request ' . $no);
        }
    }
}
$deleted = array_keys($deleted);

if (!$deleted) {
    $respond(false, '삭제할 항목을 찾을 수 없습니다.');
}
if ($mode === 'delete') {
    AuditLogger::record('delete', 'inquiry', $deleted[0], (string) $deleted[0]);
    $respond(true, '삭제되었습니다.');
}
AuditLogger::record('delete', 'inquiry', 0, count($deleted) . '건', ['ids' => $deleted]);
$respond(true, '선택한 항목이 삭제되었습니다.');