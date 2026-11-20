<?php

/**
 * 대관 신청 처리
 */

ob_start();
require_once __DIR__ . '/../inc/lib/base.class.php';
ob_end_clean();
require_once __DIR__ . '/../nol-gate/lib/upload.guard.php';
require_once __DIR__ . '/../nol-gate/lib/PrivacyAccessLogger.php';
BrowserFormResponse::handleJson('/rental/apply');

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => '잘못된 요청입니다.']);
    exit;
}

$db = DB::getInstance();

$stmt = $db->prepare("SELECT rental_is_open, rental_start_date, rental_end_date FROM nb_siteinfo WHERE sitekey = :sitekey LIMIT 1");
$stmt->execute([':sitekey' => $NO_SITE_UNIQUE_KEY ?? 'DEFAULT']);
$siteinfo = $stmt->fetch(PDO::FETCH_ASSOC);
$isRentalOpen = isset($siteinfo['rental_is_open']) && $siteinfo['rental_is_open'] == 1;
$rentalStartDate = !empty($siteinfo['rental_start_date']) ? $siteinfo['rental_start_date'] : null;
$rentalEndDate = !empty($siteinfo['rental_end_date']) ? $siteinfo['rental_end_date'] : null;

if (!$isRentalOpen) {
    echo json_encode([
        'success' => false,
        'message' => '현재 대관 신청이 마감되었습니다.'
    ]);
    exit;
}

if ($rentalStartDate && $rentalEndDate) {
    $today = date('Y-m-d');
    $startDate = date('Y-m-d', strtotime($rentalStartDate));
    $endDate = date('Y-m-d', strtotime($rentalEndDate));

    if ($today < $startDate || $today > $endDate) {
        echo json_encode([
            'success' => false,
            'message' => "대관 신청 기간이 아닙니다. (신청 기간: {$startDate} ~ {$endDate})"
        ]);
        exit;
    }
}

$venue = trim($_POST['venue'] ?? '');
$organization = trim($_POST['organization'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$performanceName = trim($_POST['performance_name'] ?? '');
$contactName = trim($_POST['contact_name'] ?? '');
$email = trim($_POST['email'] ?? '');
$content = trim($_POST['content'] ?? '');
$privacyAgree = isset($_POST['privacy_agree']) ? 1 : 0;
$captcha = trim($_POST['captcha'] ?? '');

$errors = [];

if ($venue === '' || !in_array($venue, ['woori-card', 'woori-securities'], true)) {
    $errors[] = '대관구분을 선택해주세요.';
}
if ($organization === '') {
    $errors[] = '단체(공연제작사명)를 입력해주세요.';
}
if ($phone === '') {
    $errors[] = '담당자 연락처를 입력해주세요.';
}
if ($performanceName === '') {
    $errors[] = '공연명을 입력해주세요.';
}
if ($contactName === '') {
    $errors[] = '담당자명을 입력해주세요.';
}
if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = '올바른 이메일을 입력해주세요.';
}
if ($content === '') {
    $errors[] = '내용을 입력해주세요.';
}
if ($captcha === '') {
    $errors[] = '보안문자를 입력해주세요.';
}
if ($privacyAgree !== 1) {
    $errors[] = '개인정보처리방침에 동의해주세요.';
}

$sessionCaptcha = $_SESSION['captcha_secure'] ?? '';
if ($captcha !== '' && strcasecmp($sessionCaptcha, $captcha) !== 0) {
    $errors[] = '보안문자가 일치하지 않습니다.';
}

if (!empty($errors)) {
    echo json_encode([
        'success' => false,
        'message' => implode(' ', $errors),
        'errors' => $errors
    ]);
    exit;
}

$allowedExtensions = ['zip', 'xls', 'xlsx', 'pdf', 'ppt', 'pptx', 'doc', 'docx', 'hwp'];
$maxFileSize = 20 * 1024 * 1024;
$files = $_FILES['files'] ?? ['error' => []];
$pending = [];
if (!is_array($files['error'] ?? null)) {
    $errors[] = '첨부파일 정보를 확인할 수 없습니다.';
} else {
    $fileErrors = array_filter($files['error'], static function ($code) {
        return $code !== UPLOAD_ERR_NO_FILE;
    });
    if (count($fileErrors) > 5) {
        $errors[] = '첨부파일은 최대 5개까지 가능합니다.';
    } else {
        foreach ($fileErrors as $index => $code) {
            $name = (string) ($files['name'][$index] ?? '');
            $tmpPath = (string) ($files['tmp_name'][$index] ?? '');
            $size = (int) ($files['size'][$index] ?? 0);
            $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));

            if ($code === UPLOAD_ERR_INI_SIZE || $code === UPLOAD_ERR_FORM_SIZE || $size > $maxFileSize) {
                $errors[] = '파일당 20MB 이하만 첨부할 수 있습니다.';
                continue;
            }
            if ($code === UPLOAD_ERR_PARTIAL) {
                $errors[] = '첨부파일 전송이 완료되지 않았습니다. 다시 선택해 주세요.';
                continue;
            }
            if ($code !== UPLOAD_ERR_OK || !is_uploaded_file($tmpPath)) {
                $errors[] = '첨부파일을 받지 못했습니다. 다시 선택해 주세요.';
                continue;
            }
            if (!in_array($ext, $allowedExtensions, true)) {
                $errors[] = '첨부 가능한 파일은 ZIP, XLS, XLSX, PDF, PPT, PPTX, DOC, DOCX, HWP입니다.';
                continue;
            }
            [$valid, $error] = no_upload_guard_check($name, $ext, $tmpPath, $size, $maxFileSize);
            if (!$valid) {
                $errors[] = $error;
                continue;
            }
            $pending[] = ['original' => $name, 'temp' => $tmpPath, 'ext' => $ext];
        }
    }
}

if ($errors) {
    echo json_encode([
        'success' => false,
        'message' => implode(' ', $errors),
        'errors' => $errors,
    ]);
    exit;
}

$uploadedFiles = [];
$uploadDir = rtrim($UPLOAD_DIR_REQUEST, '/\\') . '/';

try {
    PrivacyAccessModel::requireTransactionalRental($db);
    if ($pending && !is_dir($uploadDir) && !@mkdir($uploadDir, 0755, true) && !is_dir($uploadDir)) {
        throw new RuntimeException('Rental upload directory unavailable');
    }
    foreach ($pending as $file) {
        $serverName = bin2hex(random_bytes(16)) . '.' . $file['ext'];
        if (!@move_uploaded_file($file['temp'], $uploadDir . $serverName)) {
            throw new RuntimeException('Rental attachment storage failed');
        }
        $uploadedFiles[] = ['server' => $serverName, 'original' => $file['original']];
    }

    $venueName = $venue === 'woori-card' ? '우리카드홀' : '우리투자증권홀';
    $fullContent = "대관구분\n{$venueName}\n\n";
    $fullContent .= "공연명\n{$performanceName}\n\n";
    $fullContent .= "내용\n{$content}\n\n";

    $sitekey = $NO_SITE_UNIQUE_KEY ?? 'DEFAULT';

    $file1 = isset($uploadedFiles[0]) ? $uploadedFiles[0]['server'] : null;
    $file2 = isset($uploadedFiles[1]) ? $uploadedFiles[1]['server'] : null;
    $file3 = isset($uploadedFiles[2]) ? $uploadedFiles[2]['server'] : null;
    $file4 = isset($uploadedFiles[3]) ? $uploadedFiles[3]['server'] : null;
    $file5 = isset($uploadedFiles[4]) ? $uploadedFiles[4]['server'] : null;

    $orgFile1 = isset($uploadedFiles[0]) ? $uploadedFiles[0]['original'] : null;
    $orgFile2 = isset($uploadedFiles[1]) ? $uploadedFiles[1]['original'] : null;
    $orgFile3 = isset($uploadedFiles[2]) ? $uploadedFiles[2]['original'] : null;
    $orgFile4 = isset($uploadedFiles[3]) ? $uploadedFiles[3]['original'] : null;
    $orgFile5 = isset($uploadedFiles[4]) ? $uploadedFiles[4]['original'] : null;

    $db->beginTransaction();
    $stmt = $db->prepare("
        INSERT INTO nb_request
        (sitekey, venue, name, phone, email, company, performance_name, rental_start_date, rental_end_date, contents, file_1, file_2, file_3, file_4, file_5, org_file_1, org_file_2, org_file_3, org_file_4, org_file_5, regdate)
        VALUES (:sitekey, :venue, :name, :phone, :email, :company, :performance_name, :rental_start_date, :rental_end_date, :contents, :file_1, :file_2, :file_3, :file_4, :file_5, :org_file_1, :org_file_2, :org_file_3, :org_file_4, :org_file_5, NOW())
    ");

    $stmt->execute([
        ':sitekey' => $sitekey,
        ':venue' => $venue,
        ':name' => $contactName,
        ':phone' => $phone,
        ':email' => $email,
        ':company' => $organization,
        ':performance_name' => $performanceName,
        ':rental_start_date' => $rentalStartDate,
        ':rental_end_date' => $rentalEndDate,
        ':contents' => $fullContent,
        ':file_1' => $file1,
        ':file_2' => $file2,
        ':file_3' => $file3,
        ':file_4' => $file4,
        ':file_5' => $file5,
        ':org_file_1' => $orgFile1,
        ':org_file_2' => $orgFile2,
        ':org_file_3' => $orgFile3,
        ':org_file_4' => $orgFile4,
        ':org_file_5' => $orgFile5,
    ]);

    $requestNo = (int) $db->lastInsertId();
    PrivacyAccessLogger::record('create', 'inquiry', $requestNo, $contactName, '대관 신청 등록');
    $db->commit();

    unset($_SESSION['captcha_secure']);

    echo json_encode([
        'success' => true,
        'message' => '대관 신청이 완료되었습니다. 감사합니다.'
    ]);
} catch (Throwable $e) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    foreach ($uploadedFiles as $file) {
        if (!@unlink($uploadDir . $file['server'])) {
            error_log('[rental] failed to remove temporary attachment');
        }
    }
    error_log('[rental] submission persistence failed');
    echo json_encode([
        'success' => false,
        'message' => '요청 처리 중 오류가 발생했습니다. 다시 시도해주세요.'
    ]);
}
