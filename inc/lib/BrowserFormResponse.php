<?php

final class BrowserFormResponse
{
    public static function isNavigation(): bool
    {
        $mode = strtolower((string) ($_SERVER['HTTP_SEC_FETCH_MODE'] ?? ''));
        if ($mode !== '') {
            return $mode === 'navigate';
        }
        if (strtolower((string) ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '')) === 'xmlhttprequest') {
            return false;
        }
        return stripos((string) ($_SERVER['HTTP_ACCEPT'] ?? ''), 'text/html') !== false;
    }

    /** Preserve the JSON contract for fetch/XHR and provide an HTML result for direct form navigation. */
    public static function handleJson(string $backUrl): void
    {
        if (!self::isNavigation()) return;

        ob_start(static function (string $body) use ($backUrl): string {
            $type = '';
            foreach (headers_list() as $header) {
                if (stripos($header, 'Content-Disposition:') === 0) return $body;
                if (stripos($header, 'Content-Type:') === 0) $type = strtolower(substr($header, 13));
            }
            if ($type !== '' && strpos($type, 'application/json') === false && strpos($type, 'text/html') === false) {
                return $body;
            }
            $result = json_decode($body, true);
            if (!is_array($result)) return $body;
            if (strpos($type, 'application/json') === false && !isset($result['success']) && !isset($result['result'])) {
                return $body;
            }

            $success = ($result['success'] ?? null) === true || ($result['result'] ?? '') === 'success';
            $message = $result['message'] ?? $result['msg'] ?? '';
            if (!is_string($message) || $message === '') {
                $message = $success ? '처리되었습니다.' : '요청을 처리할 수 없습니다. 화면을 다시 열어 확인해 주세요.';
            }
            $escape = static function (string $value): string {
                return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            };
            header('Content-Type: text/html; charset=UTF-8');
            header('Cache-Control: no-store, private');
            header_remove('Content-Length');

            $title = $success ? '처리 결과' : '요청 안내';
            return '<!doctype html><html lang="ko"><meta charset="utf-8">'
                . '<meta name="viewport" content="width=device-width,initial-scale=1">'
                . '<title>' . $title . '</title><main style="max-width:38rem;margin:12vh auto;padding:1rem;font-family:system-ui,sans-serif">'
                . '<h1>' . $title . '</h1><p>' . $escape($message) . '</p>'
                . '<p><a href="' . $escape($backUrl) . '">화면으로 돌아가기</a></p></main></html>';
        });
    }
}
