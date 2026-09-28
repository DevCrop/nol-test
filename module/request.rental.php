<?php

/**
 * 대관 신청 처리
 */

ob_start();
require_once __DIR__ . '/../inc/lib/base.class.php';
ob_end_clean();

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
if ($content !== '' && preg_match('/<[^>]*>/', $content)) {
    $errors[] = '내용에는 HTML 태그를 사용할 수 없습니다.';
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

$uploadedFiles = [];
if (!empty($_FILES['files']['name'][0])) {
    $allowedExtensions = ['zip', 'xls', 'xlsx', 'pdf', 'ppt', 'pptx', 'doc', 'docx', 'hwp'];
    $maxFileSize = 20 * 1024 * 1024;
    $maxFiles = 5;
    $fileCount = count($_FILES['files']['name']);

    if ($fileCount > $maxFiles) {
        $errors[] = "첨부파일은 최대 {$maxFiles}개까지 가능합니다.";
    }

    for ($i = 0; $i < $fileCount; $i++) {
        if ($_FILES['files']['error'][$i] !== UPLOAD_ERR_OK) {
            continue;
        }

        $fileName = $_FILES['files']['name'][$i];
        $fileSize = $_FILES['files']['size'][$i];
        $fileTmp = $_FILES['files']['tmp_name'][$i];
        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        if (!in_array($fileExt, $allowedExtensions, true)) {
            $errors[] = "{$fileName}은(는) 허용되지 않는 파일 형식입니다.";
            continue;
        }

        if ($fileSize > $maxFileSize) {
            $errors[] = "{$fileName}은(는) 20MB 이하 파일만 업로드 가능합니다.";
            continue;
        }

        $uploadDir = rtrim($UPLOAD_DIR_REQUEST, '/\\') . '/';
        if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true)) {
            $errors[] = '업로드 디렉터리 생성에 실패했습니다.';
            continue;
        }

        if (!is_writable($uploadDir)) {
            $errors[] = '업로드 디렉터리에 쓰기 권한이 없습니다.';
            continue;
        }

        $newFileName = date('YmdHis') . '_' . uniqid() . '.' . $fileExt;
        $uploadPath = $uploadDir . $newFileName;

        if (!file_exists($fileTmp)) {
            $errors[] = "{$fileName}: 임시 파일을 찾을 수 없습니다.";
            continue;
        }

        if (move_uploaded_file($fileTmp, $uploadPath)) {
            $uploadedFiles[] = [
                'server' => $newFileName,
                'original' => $fileName
            ];
            continue;
        }

        $errorCode = $_FILES['files']['error'][$i];
        $errorMsg = "{$fileName} 업로드에 실패했습니다.";

        switch ($errorCode) {
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                $errorMsg = "{$fileName}: 파일 크기가 너무 큽니다.";
                break;
            case UPLOAD_ERR_PARTIAL:
                $errorMsg = "{$fileName}: 파일이 일부만 업로드되었습니다.";
                break;
            case UPLOAD_ERR_NO_FILE:
                $errorMsg = "{$fileName}: 파일이 업로드되지 않았습니다.";
                break;
            case UPLOAD_ERR_NO_TMP_DIR:
                $errorMsg = "{$fileName}: 임시 폴더가 없습니다.";
                break;
            case UPLOAD_ERR_CANT_WRITE:
                $errorMsg = "{$fileName}: 파일 쓰기에 실패했습니다.";
                break;
            case UPLOAD_ERR_EXTENSION:
                $errorMsg = "{$fileName}: PHP 확장에 의해 업로드가 중단되었습니다.";
                break;
        }

        $errors[] = $errorMsg;
    }
}

if (!empty($errors)) {
    echo json_encode([
        'success' => false,
        'message' => implode(' ', $errors)
    ]);
    exit;
}

try {
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

    unset($_SESSION['captcha_secure']);

    echo json_encode([
        'success' => true,
        'message' => '대관 신청이 완료되었습니다. 감사합니다.'
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => '요청 처리 중 오류가 발생했습니다. 다시 시도해주세요.'
    ]);
}
