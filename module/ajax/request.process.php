<?php

include_once "../../inc/lib/base.class.php";

header('Content-Type: application/json; charset=utf-8');
if (!\Security\Csrf::valid((string) ($_POST['_csrf'] ?? ''))) {
    http_response_code(403); echo json_encode(['result' => 'fail', 'msg' => '잘못된 요청입니다.']); exit;
}

// POST 데이터 가져오기 및 보안 처리
$performance_name = xss_clean($_POST['performance_name']);
$organization_name = xss_clean($_POST['organization_name']);
$manager_name = xss_clean($_POST['manager_name']);
$phone = xss_clean($_POST['phone']);
$email = xss_clean($_POST['email']);
$contents = xss_clean($_POST['contents']);
$r_captcha = xss_clean($_POST['r_captcha']);

// 캡차 확인
if (!isset($_SESSION['captcha_secure']) || $_SESSION['captcha_secure'] !== $r_captcha) {
    echo json_encode([
        "result" => "fail",
        "msg" => "보안코드가 일치하지 않습니다. 다시 입력해주세요."
    ]);
    exit;
}

// 캡차가 맞으면 세션 초기화
unset($_SESSION['captcha_secure']);

// 파일 업로드 설정
$uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/uploads/board';

if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

// 파일 개수 초과 확인
if (count($_FILES) > 10) {
    echo json_encode([
        "result" => "fail",
        "msg" => "첨부할 수 있는 파일은 최대 10개입니다."
    ]);
    exit;
}

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
    if (preg_match('/^file_[1-5]$/D', $key) && $file['error'] == UPLOAD_ERR_OK) {
        $fileTmpName = $file['tmp_name'];
        $originalName = basename($file['name']);
        try { [$fileExt] = \Security\UploadGuard::attachment($file, $maxFileSize); }
        catch (Throwable $e) { $message = $e instanceof RuntimeException ? $e->getMessage() : blue_safe_error($e); echo json_encode(['result' => 'fail', 'msg' => $message]); exit; }

        if ($file['size'] > $maxFileSize){
            echo json_encode([
                "result" => "fail",
                "msg" => "각 파일의 최대 크기는 20MB입니다. [$key] 파일을 확인해주세요."
            ]);
            exit;
        }

        $savedName = uniqid("file_", true) . '.' . $fileExt;
        $targetPath = $uploadDir . '/' . $savedName;

        if (move_uploaded_file($fileTmpName, $targetPath)) {
            $filePaths[$key] = $savedName;
            $fileOriginNames[$key] = $originalName;
        } else {
            echo json_encode([
                "result" => "fail",
                "msg" => "파일 업로드에 실패했습니다."
            ]);
            exit;
        }
    }
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
    $service = new \Security\RentalRequestService(DB::getInstance(), $NO_SITE_UNIQUE_KEY);
    $service->create(compact('performance_name', 'organization_name', 'manager_name', 'phone', 'email', 'contents'), $filePaths, $fileOriginNames);
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
