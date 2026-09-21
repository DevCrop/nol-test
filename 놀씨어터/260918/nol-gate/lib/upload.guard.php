<?php

function no_upload_guard_exts(): array
{
    return [
        'jpg', 'jpeg', 'png', 'gif', 'webp',
        'pdf', 'zip',
        'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'hwp',
        'txt', 'mp4', 'mov', 'avi',
    ];
}

function no_upload_guard_mimes(): array
{
    return [
        'jpg' => ['image/jpeg'],
        'jpeg' => ['image/jpeg'],
        'png' => ['image/png'],
        'gif' => ['image/gif'],
        'webp' => ['image/webp'],
        'pdf' => ['application/pdf'],
        'zip' => ['application/zip', 'application/x-zip-compressed'],
        'doc' => ['application/msword'],
        'docx' => ['application/vnd.openxmlformats-officedocument.wordprocessingml.document'],
        'xls' => ['application/vnd.ms-excel', 'application/vnd.ms-office'],
        'xlsx' => ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'],
        'ppt' => ['application/vnd.ms-powerpoint'],
        'pptx' => ['application/vnd.openxmlformats-officedocument.presentationml.presentation'],
        'hwp' => ['application/x-hwp', 'application/haansofthwp'],
        'txt' => ['text/plain'],
        'mp4' => ['video/mp4'],
        'mov' => ['video/quicktime'],
        'avi' => ['video/x-msvideo', 'video/avi'],
    ];
}

function no_upload_guard_blocked_inner(): array
{
    return [
        'php', 'phtml', 'phar', 'php3', 'php4', 'php5', 'php7', 'php8',
        'cgi', 'pl', 'py', 'jsp', 'asp', 'aspx', 'shtml',
        'htaccess', 'htpasswd', 'htm', 'html', 'js', 'svg',
    ];
}

function no_upload_guard_check(string $originalName, string $clientExt, string $tmpPath, int $fileSize, int $maxFileSize = 20971520): array
{
    $allowed = no_upload_guard_exts();
    $clientExt = strtolower(trim($clientExt));
    $originalName = str_replace("\0", '', $originalName);
    $realExt = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

    if ($clientExt === '' || !in_array($clientExt, $allowed, true)) {
        return [false, '허용되지 않는 확장자입니다.'];
    }
    if ($realExt !== $clientExt || !in_array($realExt, $allowed, true)) {
        return [false, '파일 확장자가 일치하지 않거나 허용되지 않습니다.'];
    }
    if ($fileSize <= 0 || $fileSize > $maxFileSize) {
        return [false, '파일 크기 제한을 초과했습니다.'];
    }
    if ($tmpPath === '' || !is_file($tmpPath)) {
        return [false, '유효한 업로드 파일이 아닙니다.'];
    }

    $parts = array_values(array_filter(explode('.', strtolower($originalName)), 'strlen'));
    array_pop($parts);
    foreach ($parts as $part) {
        if (in_array($part, no_upload_guard_blocked_inner(), true)) {
            return [false, '허용되지 않는 파일명입니다.'];
        }
    }

    $mimeType = no_upload_guard_detect_mime($tmpPath);
    $allowedMimes = no_upload_guard_mimes()[$clientExt] ?? [];
    if ($allowedMimes === []) {
        return [false, '허용되지 않는 파일 형식입니다.'];
    }
    if ($mimeType !== '' && !in_array($mimeType, $allowedMimes, true)) {
        return [false, '허용되지 않는 파일 형식입니다.'];
    }
    if ($mimeType === '' && !no_upload_guard_magic_ok($tmpPath, $clientExt)) {
        return [false, '허용되지 않는 파일 형식입니다.'];
    }

    if (in_array($clientExt, ['jpg', 'jpeg', 'png', 'gif', 'webp'], true)) {
        $info = @getimagesize($tmpPath);
        if ($info === false) {
            return [false, '유효한 이미지가 아닙니다.'];
        }
    }

    return [true, ''];
}

function no_upload_guard_detect_mime(string $tmpPath): string
{
    $mimeType = '';
    if (function_exists('finfo_open')) {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        if ($finfo !== false) {
            $mimeType = (string) finfo_file($finfo, $tmpPath);
            finfo_close($finfo);
        }
    } elseif (function_exists('mime_content_type')) {
        $mimeType = (string) mime_content_type($tmpPath);
    }

    return strtolower(trim(explode(';', $mimeType)[0]));
}

function no_upload_guard_magic_ok(string $tmpPath, string $ext): bool
{
    $fh = fopen($tmpPath, 'rb');
    if ($fh === false) {
        return false;
    }
    $head = fread($fh, 16);
    fclose($fh);
    if ($head === false || $head === '') {
        return false;
    }

    switch ($ext) {
        case 'jpg':
        case 'jpeg':
            return strncmp($head, "\xFF\xD8\xFF", 3) === 0;
        case 'png':
            return strncmp($head, "\x89PNG\r\n\x1A\n", 8) === 0;
        case 'gif':
            return strncmp($head, 'GIF8', 4) === 0;
        case 'webp':
            return strncmp($head, 'RIFF', 4) === 0 && strpos($head, 'WEBP') !== false;
        case 'pdf':
            return strncmp($head, '%PDF', 4) === 0;
        case 'zip':
        case 'docx':
        case 'xlsx':
        case 'pptx':
            return strncmp($head, "PK", 2) === 0;
        case 'txt':
            if (strpos($head, "\0") !== false) {
                return false;
            }
            $sample = (string) file_get_contents($tmpPath, false, null, 0, 2048);
            return stripos($sample, '<?php') === false && stripos($sample, '<script') === false;
        default:
            return false;
    }
}
