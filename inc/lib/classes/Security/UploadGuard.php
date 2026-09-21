<?php
namespace Security;

final class UploadGuard
{
    private const IMAGE_MIMES = [
        'jpg' => ['image/jpeg'], 'jpeg' => ['image/jpeg'], 'png' => ['image/png'],
        'gif' => ['image/gif'], 'webp' => ['image/webp'],
    ];
    private const ATTACHMENT_MIMES = [
        'jpg'=>['image/jpeg'], 'jpeg'=>['image/jpeg'], 'png'=>['image/png'], 'gif'=>['image/gif'], 'webp'=>['image/webp'],
        'ico'=>['image/x-icon','image/vnd.microsoft.icon'],
        'pdf'=>['application/pdf'], 'zip'=>['application/zip','application/x-zip-compressed'], 'txt'=>['text/plain'],
        'xls'=>['application/vnd.ms-excel','application/CDFV2','application/x-ole-storage'],
        'xlsx'=>['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet','application/zip'],
        'doc'=>['application/msword','application/CDFV2','application/x-ole-storage'],
        'docx'=>['application/vnd.openxmlformats-officedocument.wordprocessingml.document','application/zip'],
        'ppt'=>['application/vnd.ms-powerpoint','application/CDFV2','application/x-ole-storage'],
        'pptx'=>['application/vnd.openxmlformats-officedocument.presentationml.presentation','application/zip'],
        'hwp'=>['application/x-hwp','application/haansofthwp','application/CDFV2','application/x-ole-storage'],
        'mp4'=>['video/mp4'], 'mov'=>['video/quicktime'], 'avi'=>['video/x-msvideo'],
    ];

    public static function image(array $file): array
    {
        self::rejectExecutableName((string) ($file['name'] ?? ''));
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK || !is_uploaded_file((string) ($file['tmp_name'] ?? ''))) throw new \RuntimeException('파일 업로드에 실패했습니다.');
        if ((int) ($file['size'] ?? 0) > 20 * 1024 * 1024) throw new \RuntimeException('파일 크기는 20MB를 넘을 수 없습니다.');
        $ext = strtolower(pathinfo((string) ($file['name'] ?? ''), PATHINFO_EXTENSION));
        $mime = (new \finfo(FILEINFO_MIME_TYPE))->file((string) $file['tmp_name']);
        if (!isset(self::IMAGE_MIMES[$ext]) || !in_array($mime, self::IMAGE_MIMES[$ext], true)) throw new \RuntimeException('허용되지 않는 파일 형식입니다.');
        return [$ext, $mime];
    }

    public static function attachment(array $file, int $maxBytes = 20971520): array
    {
        self::rejectExecutableName((string) ($file['name'] ?? ''));
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK || !is_uploaded_file((string) ($file['tmp_name'] ?? ''))) throw new \RuntimeException('파일 업로드에 실패했습니다.');
        if ((int) ($file['size'] ?? 0) > $maxBytes) throw new \RuntimeException('파일 크기가 허용 범위를 넘었습니다.');
        $ext = strtolower(pathinfo((string) ($file['name'] ?? ''), PATHINFO_EXTENSION));
        $mime = (new \finfo(FILEINFO_MIME_TYPE))->file((string) $file['tmp_name']);
        if (!isset(self::ATTACHMENT_MIMES[$ext]) || !in_array($mime, self::ATTACHMENT_MIMES[$ext], true)) throw new \RuntimeException('허용되지 않는 파일 형식입니다.');
        return [$ext, $mime];
    }

    private static function rejectExecutableName(string $name): void
    {
        if (preg_match('/(?:^|\.)(?:php[0-9]?|phtml|phar|cgi|pl|asp|aspx|jsp|html?|svg|js|exe|sh)(?:\.|$)/i', $name)) throw new \RuntimeException('허용되지 않는 파일명입니다.');
    }

    public static function pathInside(string $base, string $relative): ?string
    {
        $baseReal = realpath($base); if ($baseReal === false) return null;
        $candidate = realpath($baseReal . DIRECTORY_SEPARATOR . ltrim(str_replace('\\', '/', $relative), '/'));
        if ($candidate === false || strpos($candidate, $baseReal . DIRECTORY_SEPARATOR) !== 0 || !is_file($candidate)) return null;
        return $candidate;
    }
}
