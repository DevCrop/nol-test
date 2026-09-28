<?php
include_once "../../../inc/lib/base.class.php";
require_once dirname(__DIR__, 2) . "/lib/PrivacyAccessLogger.php";

$role->requireLogin();
if (!$role->canView()) {
    http_response_code(403);
    exit('Forbidden');
}

$src = $_SERVER['REQUEST_METHOD'] === 'POST' ? $_POST : $_GET;
$no = isset($src['no']) ? (int) $src['no'] : 0;
$slot = isset($src['slot']) ? (int) $src['slot'] : 0;
$fileParam = isset($src['file']) ? basename((string) $src['file']) : '';
$reason = trim((string) ($src['reason'] ?? ''));
$reasonLen = function_exists('mb_strlen') ? mb_strlen($reason, 'UTF-8') : strlen($reason);

if ($no <= 0) {
    http_response_code(400);
    exit('Bad Request');
}

if ($reasonLen < 2) {
    echo "<script>alert('다운로드 사유를 입력하세요.'); history.back();</script>";
    exit;
}

$db = DB::getInstance();
$stmt = $db->prepare("
    SELECT name, file_1, file_2, file_3, file_4, file_5, org_file_1, org_file_2, org_file_3, org_file_4, org_file_5
    FROM nb_request
    WHERE no = :no
    LIMIT 1
");
$stmt->execute([':no' => $no]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$row) {
    http_response_code(404);
    exit('Not Found');
}

$serverFile = '';
$originalFile = '';

if ($slot >= 1 && $slot <= 5) {
    $serverFile = (string)($row['file_' . $slot] ?? '');
    $originalFile = (string)($row['org_file_' . $slot] ?? '');
} elseif ($fileParam !== '') {
    for ($i = 1; $i <= 5; $i++) {
        $candidate = basename((string)($row['file_' . $i] ?? ''));
        if ($candidate !== '' && hash_equals($candidate, $fileParam)) {
            $serverFile = $candidate;
            $originalFile = (string)($row['org_file_' . $i] ?? '');
            break;
        }
    }
}

$serverFile = basename($serverFile);
if ($serverFile === '') {
    http_response_code(404);
    exit('Not Found');
}

$storagePath = rtrim($UPLOAD_DIR_REQUEST, '/\\') . '/' . $serverFile;
if (!is_file($storagePath)) {
    http_response_code(404);
    exit('Not Found');
}

PrivacyAccessLogger::record(
    'download',
    'inquiry',
    $no,
    (string) ($row['name'] ?? ''),
    '대관 신청 첨부 다운로드',
    $reason
);

$downloadName = $originalFile !== '' ? $originalFile : $serverFile;
$downloadName = str_replace(["\r", "\n"], '', $downloadName);

header('Content-Description: File Transfer');
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . rawurlencode($downloadName) . '"');
header('Content-Length: ' . filesize($storagePath));
header('X-Content-Type-Options: nosniff');
readfile($storagePath);
exit;
