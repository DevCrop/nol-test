<?php
namespace Security;

/** Send original file bytes; bootstrap HTML and router buffers must never enter them. */
final class BinaryDownload
{
    public static function send(string $path, string $name): void
    {
        $stream = @fopen($path, 'rb');
        if ($stream === false) throw new \RuntimeException('파일을 열 수 없습니다.');
        $stat = fstat($stream);
        if (!$stat || ($stat['mode'] & 0170000) !== 0100000) { fclose($stream); throw new \RuntimeException('파일을 열 수 없습니다.'); }
        $name = basename(str_replace('\\', '/', $name));
        $name = (string) preg_replace('/[\x00-\x1f\x7f]/', '', $name);
        if ($name === '' || $name === '.' || $name === '..') $name = basename($path);
        $fallback = (string) preg_replace('/[^A-Za-z0-9._-]/', '_', $name);
        if (session_status() === PHP_SESSION_ACTIVE) session_write_close();
        @ini_set('zlib.output_compression', '0');
        while (ob_get_level() > 0) {
            if (!@ob_end_clean()) { fclose($stream); throw new \RuntimeException('파일 응답을 준비할 수 없습니다.'); }
        }
        if (headers_sent()) { fclose($stream); throw new \RuntimeException('파일 응답을 준비할 수 없습니다.'); }
        header_remove('Content-Encoding');
        header('Content-Type: application/octet-stream');
        header('X-Content-Type-Options: nosniff');
        header('Cache-Control: private, no-store, no-cache, must-revalidate');
        header('Content-Disposition: attachment; filename="' . $fallback . '"; filename*=UTF-8\'\'' . rawurlencode($name));
        header('Content-Length: ' . (string) $stat['size']);
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'HEAD') {
            $sent = fpassthru($stream);
            if ($sent === false || $sent !== $stat['size']) error_log('[download] incomplete stream');
        }
        fclose($stream);
        exit;
    }
}
