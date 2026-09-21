<?php
require_once '../../../../inc/lib/base.class.php';
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') { http_response_code(405); exit; }
$reason = trim((string) ($_POST['reason'] ?? ''));
if (mb_strlen($reason) < 5 || mb_strlen($reason) > 500) {
    http_response_code(422); header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['ok' => false, 'message' => '다운로드 사유를 5자 이상 입력하세요.']); exit;
}
$stmt = DB::getInstance()->prepare('SELECT no,organization_name,performance_name,manager_name,email,phone,regdate FROM nb_request WHERE sitekey = ? ORDER BY no DESC');
$stmt->execute(['BLUESQ']);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($rows as $row) \Security\PrivacyLogger::record('download', 'request', (int) $row['no'], (string) $row['manager_name'], '대관신청 목록 내보내기', $reason);
header('Content-Type: application/json; charset=utf-8');
echo json_encode(['ok' => true, 'rows' => $rows], JSON_UNESCAPED_UNICODE);
