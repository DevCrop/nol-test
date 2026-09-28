<?php

class SmtpMailer
{
    /** @var list<string> */
    private static $trace = [];

    public static function send(string $to, string $subject, string $body): void
    {
        self::$trace = [];
        $host = SMTP_HOST;
        $port = (int) SMTP_PORT;
        $user = SMTP_USER;
        $pass = SMTP_PASS;
        $from = SMTP_FROM !== '' ? SMTP_FROM : SMTP_USER;
        $fromName = SMTP_FROM_NAME;

        self::debug('start', 'to=' . $to . ' from=' . $from . ' host=' . $host . ':' . $port . ' user=' . $user);

        if ($user === '' || $pass === '') {
            throw new RuntimeException('SMTP 계정이 없습니다.');
        }

        $errno = 0;
        $errstr = '';
        $transport = $port === 465 ? 'ssl://' : 'tcp://';
        $socket = @stream_socket_client(
            $transport . $host . ':' . $port,
            $errno,
            $errstr,
            8,
            STREAM_CLIENT_CONNECT
        );
        if ($socket === false) {
            throw self::fail('connect', '메일 서버에 연결하지 못했습니다. ' . $errno . ' ' . $errstr);
        }

        stream_set_timeout($socket, 12);
        try {
            self::expect($socket, [220], 'banner');
            self::command($socket, 'EHLO localhost', [250], 'EHLO');
            if ($port !== 465) {
                self::command($socket, 'STARTTLS', [220], 'STARTTLS');
                if (!stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                    throw self::fail('tls', '메일 암호화에 실패했습니다.');
                }
                self::command($socket, 'EHLO localhost', [250], 'EHLO2');
            }
            self::command($socket, 'AUTH LOGIN', [334], 'AUTH');
            self::command($socket, base64_encode($user), [334], 'USER');
            self::command($socket, base64_encode($pass), [235], 'PASS');
            self::command($socket, 'MAIL FROM:<' . $from . '>', [250], 'FROM');
            self::command($socket, 'RCPT TO:<' . $to . '>', [250, 251], 'RCPT');
            self::command($socket, 'DATA', [354], 'DATA');

            $headers = [
                'From: ' . self::encodeHeader($fromName) . ' <' . $from . '>',
                'To: <' . $to . '>',
                'Subject: ' . self::encodeHeader($subject),
                'Date: ' . date('r'),
                'Message-ID: <' . bin2hex(random_bytes(12)) . '@nol-gate>',
                'MIME-Version: 1.0',
                'Content-Type: text/plain; charset=UTF-8',
                'Content-Transfer-Encoding: 8bit',
            ];

            $data = implode("\r\n", $headers) . "\r\n\r\n" . $body . "\r\n.";
            self::command($socket, $data, [250], 'BODY');
            fwrite($socket, "QUIT\r\n");
        } catch (Throwable $e) {
            fclose($socket);
            throw $e;
        }

        fclose($socket);
        self::debug('done', 'sent');
    }

    private static function encodeHeader(string $value): string
    {
        return '=?UTF-8?B?' . base64_encode($value) . '?=';
    }

    private static function command($socket, string $line, array $ok, string $step): void
    {
        fwrite($socket, $line . "\r\n");
        self::expect($socket, $ok, $step);
    }

    private static function expect($socket, array $ok, string $step): void
    {
        $code = 0;
        $text = '';
        while (($line = fgets($socket, 515)) !== false) {
            $text .= rtrim($line) . ' | ';
            $code = (int) substr($line, 0, 3);
            if (isset($line[3]) && $line[3] === ' ') {
                break;
            }
        }
        $text = rtrim($text, ' | ');
        if ($step === 'fail' || $step === 'start' || $step === 'done' || $step === 'BODY') {
            self::debug($step, $text !== '' ? $text : '(empty)');
        }
        if (!in_array($code, $ok, true)) {
            throw self::fail($step, 'SMTP ' . $step . ' 실패: ' . ($text !== '' ? $text : '응답 없음'));
        }
    }

    private static function fail(string $step, string $message): RuntimeException
    {
        self::debug('fail', $step . ' ' . $message);
        return new RuntimeException($message);
    }

    private static function debug(string $step, string $detail): void
    {
        $line = date('c') . " [smtp] {$step} {$detail}";
        self::$trace[] = $line;
        error_log($line);

        $dir = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'storage';
        if (!is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }
        @file_put_contents($dir . DIRECTORY_SEPARATOR . 'mfa-smtp.log', $line . PHP_EOL, FILE_APPEND | LOCK_EX);
    }
}
