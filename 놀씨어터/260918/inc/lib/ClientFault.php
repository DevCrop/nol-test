<?php

class ClientFault
{
    public static function message(Throwable $e): string
    {
        $text = trim($e->getMessage());
        if ($text !== '' && !self::looksTechnical($text)) {
            return $text;
        }
        error_log('[fault] ' . $e->getMessage());
        return '처리할 수 없습니다.';
    }

    public static function abortPage(Throwable $e): void
    {
        error_log('[fault] ' . $e->getMessage());
        echo "<script>alert('처리할 수 없습니다.'); history.back();</script>";
        exit;
    }

    private static function looksTechnical(string $text): bool
    {
        return (bool) preg_match(
            '/SQLSTATE|PDOException|Stack trace|mysqli|on line \d+|\/var\/www|\.php|php\.net/i',
            $text
        );
    }
}
