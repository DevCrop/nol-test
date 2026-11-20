<?php

include_once "../../inc/lib/base.class.php";

\Security\BrowserFormResponse::handleJson('/pages/rental/inquiry.php');
header('Content-Type: application/json; charset=utf-8');
$fail = static function (int $status, string $message): void {
    http_response_code($status);
    echo json_encode(['result' => 'fail', 'msg' => $message]);
    exit;
};
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    header('Allow: POST');
    $fail(405, '잘못된 요청입니다.');
}
$token = $_POST['_csrf'] ?? '';
if (!is_string($token) || !\Security\Csrf::valid($token)) {
    $fail(403, '잘못된 요청입니다.');
}

try {
    $service = new \Security\RentalRequestService(DB::getInstance(), $NO_SITE_UNIQUE_KEY);
    $notice = $service->currentNotice();
} catch (Throwable $e) {
    blue_safe_error($e);
    $fail(500, '대관 신청을 처리할 수 없습니다. 잠시 후 다시 시도해주세요.');
}
if (!$notice) $fail(422, '현재 대관 문의 기간이 아닙니다.');

$required = [
    'performance_name' => '공연명', 'organization_name' => '단체명', 'manager_name' => '담당자명',
    'phone' => '담당자 연락처', 'email' => '담당자 이메일', 'contents' => '문의 내용',
];
$fields = [];
foreach ($required as $key => $label) {
    $value = $_POST[$key] ?? '';
    if (!is_string($value) || trim($value) === '') $fail(422, $label . ' 항목을 입력해주세요.');
    $value = trim($value);
    if ($key === 'email' && !filter_var($value, FILTER_VALIDATE_EMAIL)) $fail(422, '올바른 이메일 주소를 입력해주세요.');
    $fields[$key] = htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
if (($_POST['check'] ?? '') !== '1') $fail(422, '개인정보 수집 및 이용에 동의해주세요.');

$captcha = $_POST['r_captcha'] ?? '';
$expected = $_SESSION['captcha_secure'] ?? '';
if (!is_string($captcha) || !is_string($expected) || $expected === '' || !hash_equals($expected, trim($captcha))) {
    $fail(422, '보안문자가 일치하지 않습니다. 다시 입력해주세요.');
}
unset($_SESSION['captcha_secure']);
['performance_name' => $performance_name, 'organization_name' => $organization_name,
 'manager_name' => $manager_name, 'phone' => $phone, 'email' => $email, 'contents' => $contents] = $fields;

// 파일 업로드 설정
$uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/uploads/board';

if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

if (count($_FILES) > 5) $fail(422, '첨부할 수 있는 파일은 최대 5개입니다.');

$filePaths = [];
$persisted = false;
register_shutdown_function(static function () use (&$filePaths, &$persisted, $uploadDir): void {
    if ($persisted) return;
    foreach ($filePaths as $name) {
        if (basename($name) !== $name || !preg_match('/^file_[a-zA-Z0-9_.]+$/D', $name)) continue;
        $path = $uploadDir.'/'.$name;
        if (is_file($path)) unlink($path);
    }
});
$fileOriginNames = [];
$maxFileSize = 20 * 1024 * 1024; // 20MB

foreach ($_FILES as $key => $file) {
    $error = $file['error'] ?? null;
    if (!preg_match('/^file_[1-5]$/D', $key) || !is_int($error)) $fail(422, '잘못된 첨부파일 요청입니다.');
    if ($error === UPLOAD_ERR_NO_FILE) continue;
    if ($error === UPLOAD_ERR_INI_SIZE || $error === UPLOAD_ERR_FORM_SIZE || (int) ($file['size'] ?? 0) > $maxFileSize) {
        $fail(413, '첨부파일은 파일당 20MB 이하만 등록할 수 있습니다.');
    }
    if ($error !== UPLOAD_ERR_OK) $fail(422, '첨부파일 업로드가 완료되지 않았습니다. 다시 선택해주세요.');

    try {
        [$extension] = \Security\UploadGuard::attachment($file, $maxFileSize);
    } catch (Throwable $e) {
        $fail(422, $e instanceof RuntimeException ? $e->getMessage() : blue_safe_error($e));
    }
    $savedName = uniqid('file_', true) . '.' . $extension;
    if (!move_uploaded_file($file['tmp_name'], $uploadDir . '/' . $savedName)) {
        $fail(500, '파일 업로드에 실패했습니다.');
    }
    $filePaths[$key] = $savedName;
    $fileOriginNames[$key] = basename($file['name']);
}

$targetEmails = [];
foreach (array_filter(array_map('trim', explode(',', (string) blue_env('REQUEST_NOTIFY_EMAILS', '')))) as $address) {
    if (filter_var($address, FILTER_VALIDATE_EMAIL)) $targetEmails[] = ['name' => '블루스퀘어 담당자', 'mail' => $address];
}
$content = "
    <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 20px auto; border: 1px solid #ddd; border-radius: 10px; padding: 20px; background-color: #f9f9f9;'>
        <h2 style='text-align: center; color: #333; margin-bottom: 20px;'>🎭 문의하기 접수 내용</h2>
        <table style='width: 100%; border-collapse: collapse;'>
            <tr>
                <td style='background-color: #007BFF; color: #fff; font-weight: bold; padding: 10px; border-radius: 6px 6px 0 0; text-align: center;' colspan='2'>
                    문의 상세 정보
                </td>
            </tr>
            <tr>
                <td style='padding: 12px; border-bottom: 1px solid #ddd; font-weight: bold;'>공연명</td>
                <td style='padding: 12px; border-bottom: 1px solid #ddd;'>$performance_name</td>
            </tr>
            <tr>
                <td style='padding: 12px; border-bottom: 1px solid #ddd; font-weight: bold;'>기관명</td>
                <td style='padding: 12px; border-bottom: 1px solid #ddd;'>$organization_name</td>
            </tr>
            <tr>
                <td style='padding: 12px; border-bottom: 1px solid #ddd; font-weight: bold;'>담당자명</td>
                <td style='padding: 12px; border-bottom: 1px solid #ddd;'>$manager_name</td>
            </tr>
            <tr>
                <td style='padding: 12px; border-bottom: 1px solid #ddd; font-weight: bold;'>전화번호</td>
                <td style='padding: 12px; border-bottom: 1px solid #ddd;'>$phone</td>
            </tr>
            <tr>
                <td style='padding: 12px; border-bottom: 1px solid #ddd; font-weight: bold;'>이메일</td>
                <td style='padding: 12px; border-bottom: 1px solid #ddd;'>$email</td>
            </tr>
            <tr>
                <td style='padding: 12px; font-weight: bold; vertical-align: top;'>문의 내용</td>
                <td style='padding: 12px; background-color: #fff; border-radius: 6px;'>
                    <p style='margin: 0; padding: 10px; background-color: #f1f1f1; border-radius: 6px;'>
                        $contents
                    </p>
                </td>
            </tr>
        </table>
    </div>
";


$failedMails = [];


// Persist before notifying: failed storage must never produce a receipt email.
try {
    $service->create($fields, $filePaths, $fileOriginNames);
    $persisted = true;
    $transport = new \Security\SmtpMailTransport();
    foreach ($targetEmails as $mailInfo) {
        try {
            $transport->send($mailInfo['mail'], ['subject' => '대관신청이 접수되었습니다.', 'html' => $content, 'text' => trim(strip_tags($content))]);
        } catch (Throwable $mailError) {
            error_log('Rental request notification delivery failed');
            $failedMails[] = $mailInfo['mail'];
        }
    }
    echo json_encode(['result' => 'success', 'msg' => '정상적으로 등록되었습니다. 담당자가 확인 후 연락드리겠습니다.', 'failedMails' => $failedMails]);
} catch (Throwable $e) {
    error_log('Rental request persistence failed');
    if (http_response_code() < 400) http_response_code(500);
    echo json_encode(['result' => 'fail', 'msg' => '처리 중 문제가 발생하였습니다. 관리자에게 문의해주세요.']);
}
