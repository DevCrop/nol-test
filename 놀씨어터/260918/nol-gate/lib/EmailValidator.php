<?php

class EmailValidator
{
    public const MSG_FORMAT = '올바른 이메일을 입력하세요.';
    public const MSG_DOMAIN = '수신할 수 없는 이메일입니다. 주소를 확인하세요.';
    public const MSG_MAILBOX = '존재하지 않는 메일 주소입니다. 주소를 확인하세요.';

    public static function assert(string $input, bool $probeMailbox = true): string
    {
        $email = self::normalize($input);
        self::assertFormat($email);
        $domain = self::asciiDomain($email);
        $hosts = self::mxHosts($domain);
        if ($hosts === []) {
            throw new RuntimeException(self::MSG_DOMAIN);
        }
        if ($probeMailbox) {
            self::assertMailbox($email, $hosts);
        }
        return $email;
    }

    public static function normalize(string $input): string
    {
        $email = trim($input);
        $email = preg_replace('/\s+/', '', $email);
        return strtolower((string) $email);
    }

    private static function assertFormat(string $email): void
    {
        if ($email === '' || strlen($email) > 254) {
            throw new RuntimeException(self::MSG_FORMAT);
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new RuntimeException(self::MSG_FORMAT);
        }

        $at = strrpos($email, '@');
        if ($at === false) {
            throw new RuntimeException(self::MSG_FORMAT);
        }

        $local = substr($email, 0, $at);
        $domain = substr($email, $at + 1);
        if ($local === '' || $domain === '' || strlen($local) > 64) {
            throw new RuntimeException(self::MSG_FORMAT);
        }
        if ($local[0] === '.' || substr($local, -1) === '.' || strpos($local, '..') !== false) {
            throw new RuntimeException(self::MSG_FORMAT);
        }
        if (strpos($domain, '.') === false || strpos($domain, '..') !== false) {
            throw new RuntimeException(self::MSG_FORMAT);
        }
        if ($domain[0] === '.' || substr($domain, -1) === '.' || substr($domain, -1) === '-') {
            throw new RuntimeException(self::MSG_FORMAT);
        }
        if (!preg_match('/\.[a-z]{2,}$/', $domain)) {
            throw new RuntimeException(self::MSG_FORMAT);
        }
    }

    private static function asciiDomain(string $email): string
    {
        $domain = substr($email, strrpos($email, '@') + 1);
        if (function_exists('idn_to_ascii')) {
            $ascii = idn_to_ascii($domain, IDNA_DEFAULT, INTL_IDNA_VARIANT_UTS46);
            if (is_string($ascii) && $ascii !== '') {
                return strtolower($ascii);
            }
        }
        return $domain;
    }

    /** @return list<string> */
    private static function mxHosts(string $domain): array
    {
        $hosts = [];
        $weights = [];
        if (function_exists('getmxrr') && @getmxrr($domain, $hosts, $weights) && $hosts !== []) {
            array_multisort($weights, SORT_ASC, $hosts);
            $clean = [];
            foreach ($hosts as $host) {
                $host = rtrim(strtolower((string) $host), '.');
                if ($host !== '' && $host !== 'localhost') {
                    $clean[] = $host;
                }
            }
            return array_values(array_unique($clean));
        }
        if (@checkdnsrr($domain, 'A') || @checkdnsrr($domain, 'AAAA')) {
            return [$domain];
        }
        return [];
    }

    /** @param list<string> $hosts */
    private static function assertMailbox(string $email, array $hosts): void
    {
        $from = 'noreply@localhost';
        if (defined('SMTP_FROM') && SMTP_FROM !== '') {
            $from = (string) SMTP_FROM;
        } elseif (defined('SMTP_USER') && SMTP_USER !== '') {
            $from = (string) SMTP_USER;
        }

        $sawReject = false;
        foreach (array_slice($hosts, 0, 2) as $host) {
            $result = self::rcpt($host, $from, $email);
            if ($result === 'ok') {
                return;
            }
            if ($result === 'reject') {
                $sawReject = true;
                break;
            }
        }
        if ($sawReject) {
            throw new RuntimeException(self::MSG_MAILBOX);
        }
    }

    private static function rcpt(string $host, string $from, string $to): string
    {
        $errno = 0;
        $errstr = '';
        $socket = @stream_socket_client(
            'tcp://' . $host . ':25',
            $errno,
            $errstr,
            4,
            STREAM_CLIENT_CONNECT
        );
        if ($socket === false) {
            return 'unknown';
        }

        stream_set_timeout($socket, 5);
        try {
            if (!self::expect($socket, [220])) {
                return 'unknown';
            }
            $ehlo = self::command($socket, 'EHLO nol-gate', [250, 220]);
            if ($ehlo === null) {
                return 'unknown';
            }
            if (stripos($ehlo, 'STARTTLS') !== false) {
                if (self::command($socket, 'STARTTLS', [220]) === null) {
                    return 'unknown';
                }
                if (!@stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                    return 'unknown';
                }
                if (self::command($socket, 'EHLO nol-gate', [250]) === null) {
                    return 'unknown';
                }
            }
            if (self::command($socket, 'MAIL FROM:<' . $from . '>', [250]) === null) {
                return 'unknown';
            }
            $rcpt = self::command($socket, 'RCPT TO:<' . $to . '>', [250, 251, 450, 451, 452, 550, 551, 552, 553, 554]);
            @fwrite($socket, "RSET\r\nQUIT\r\n");
            if ($rcpt === null) {
                return 'unknown';
            }
            $code = (int) substr($rcpt, 0, 3);
            if ($code === 250 || $code === 251) {
                return 'ok';
            }
            if ($code >= 550 && $code <= 554) {
                return 'reject';
            }
            return 'unknown';
        } finally {
            fclose($socket);
        }
    }

    /** @param list<int> $ok */
    private static function expect($socket, array $ok): bool
    {
        $line = self::read($socket);
        return $line !== '' && in_array((int) substr($line, 0, 3), $ok, true);
    }

    /** @param list<int> $ok */
    private static function command($socket, string $cmd, array $ok): ?string
    {
        if (@fwrite($socket, $cmd . "\r\n") === false) {
            return null;
        }
        $reply = self::read($socket);
        if ($reply === '' || !in_array((int) substr($reply, 0, 3), $ok, true)) {
            return null;
        }
        return $reply;
    }

    private static function read($socket): string
    {
        $buf = '';
        while (!feof($socket)) {
            $line = (string) @fgets($socket, 2048);
            if ($line === '') {
                break;
            }
            $buf .= $line;
            if (isset($line[3]) && $line[3] === ' ') {
                break;
            }
        }
        return $buf;
    }
}
