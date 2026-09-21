<?php
require_once '../../../../inc/lib/base.class.php';
$no = filter_input(INPUT_POST, 'no', FILTER_VALIDATE_INT); $field = (string) ($_POST['field'] ?? ''); $reason = trim((string) ($_POST['reason'] ?? ''));
$allowed = ['file_1','file_2','file_3','file_4','file_5'];
if (!$no || !in_array($field, $allowed, true) || mb_strlen($reason, 'UTF-8') < 5) { http_response_code(400); exit('다운로드 사유를 5자 이상 입력하세요.'); }
$stmt = DB::getInstance()->prepare('SELECT * FROM nb_request WHERE no = ? AND sitekey = ? LIMIT 1'); $stmt->execute([$no, 'BLUESQ']); $row = (array) $stmt->fetch(PDO::FETCH_ASSOC);
$stored = basename((string) ($row[$field] ?? '')); $origin = basename((string) ($row[$field . '_origin'] ?? $stored));
$path = \Security\UploadGuard::pathInside($_SERVER['DOCUMENT_ROOT'] . '/uploads/board', $stored);
if (!$row || $stored === '' || $path === null) { http_response_code(404); exit('파일을 찾을 수 없습니다.'); }
\Security\PrivacyLogger::record('download', 'request', (int) $no, (string) ($row['manager_name'] ?? ''), '대관신청 첨부파일', $reason);
header('Content-Type: application/octet-stream'); header('X-Content-Type-Options: nosniff'); header("Content-Disposition: attachment; filename*=UTF-8''" . rawurlencode($origin)); header('Content-Length: ' . filesize($path)); readfile($path); exit;

