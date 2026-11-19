<?php

ob_start();
require_once __DIR__ . '/../../../inc/lib/base.class.php';
require_once dirname(__DIR__, 2) . '/lib/PrivacyAccessLogger.php';
require_once dirname(__DIR__, 2) . '/lib/upload.guard.php';

$fail = static function (int $status, string $message): void {
    ob_clean();
    http_response_code($status);
    header('Content-Type: text/plain; charset=UTF-8');
    header('Cache-Control: private, no-store');
    exit($message);
};

$role->requireLogin();
if (!$role->acl()->allows('inquiry', 'view')) {
    $fail(403, '다운로드 권한이 없습니다.');
}

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    header('Allow: POST');
    $fail(405, '대관 상세 화면에서 다운로드 사유를 입력해 주세요.');
}

$no = filter_var($_POST['no'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
$slot = filter_var($_POST['slot'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 5]]);
$reason = is_string($_POST['reason'] ?? null) ? trim($_POST['reason']) : '';
$reasonLength = mb_strlen($reason, 'UTF-8');

if (!$no || !$slot) {
    $fail(400, '첨부파일 정보를 확인할 수 없습니다.');
}
if ($reasonLength < 2 || $reasonLength > 500) {
    $fail(400, '다운로드 사유를 2자 이상 500자 이하로 입력해 주세요.');
}

$stmt = DB::getInstance()->prepare("
    SELECT name, file_{$slot} AS stored_file, org_file_{$slot} AS original_file
    FROM nb_request
    WHERE no = :no AND sitekey = :sitekey
    LIMIT 1
");
$stmt->execute([':no' => $no, ':sitekey' => $NO_SITE_UNIQUE_KEY]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);
$storedFile = basename(str_replace('\\', '/', (string) ($row['stored_file'] ?? '')));
$path = $storedFile === '' ? null : no_upload_guard_path($UPLOAD_DIR_REQUEST, $UPLOAD_DIR_REQUEST . '/' . $storedFile);
$stream = $path === null ? false : @fopen($path, 'rb');
if ($stream === false) {
    $fail(404, '첨부파일을 찾을 수 없습니다.');
}

$stat = fstat($stream);
if (!$stat || ($stat['mode'] & 0170000) !== 0100000) {
    fclose($stream);
    $fail(404, '첨부파일을 찾을 수 없습니다.');
}

$downloadName = str_replace('\\', '/', (string) ($row['original_file'] ?? ''));
$lastSlash = strrpos($downloadName, '/');
if ($lastSlash !== false) {
    $downloadName = substr($downloadName, $lastSlash + 1);
}
$downloadName = (string) preg_replace('/[\x00-\x1F\x7F]/', '', $downloadName);
if ($downloadName === '' || $downloadName === '.' || $downloadName === '..') {
    $downloadName = $storedFile;
}
$fallbackName = preg_replace('/[^a-zA-Z0-9._-]/', '_', $storedFile);

try {
    PrivacyAccessLogger::record('download', 'inquiry', $no, (string) $row['name'], '대관 신청 첨부 다운로드', $reason);
} catch (RuntimeException $e) {
    fclose($stream);
    $fail(503, $e->getMessage());
}

if (session_status() === PHP_SESSION_ACTIVE) {
    session_write_close();
}
@ini_set('zlib.output_compression', '0');
while (ob_get_level() > 0) {
    if (!@ob_end_clean()) {
        fclose($stream);
        http_response_code(500);
        exit;
    }
}

header_remove('Content-Encoding');
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . $fallbackName . '"; filename*=UTF-8\'\'' . rawurlencode($downloadName));
header('Content-Length: ' . $stat['size']);
header('X-Content-Type-Options: nosniff');
header('Cache-Control: private, no-store');
if (fpassthru($stream) !== $stat['size']) {
    error_log('[inquiry-download] incomplete stream');
}
fclose($stream);
exit;
