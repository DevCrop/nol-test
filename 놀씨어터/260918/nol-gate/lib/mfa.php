<?php

require_once __DIR__ . '/AuthSession.php';
require_once __DIR__ . '/EmailValidator.php';
require_once __DIR__ . '/SmtpMailer.php';

class Mfa
{
    public static function enabled(): bool
    {
        return defined('MFA_ENABLED') && MFA_ENABLED === true;
    }

    public static function codeLength(): int
    {
        return defined('MFA_CODE_LENGTH') ? (int) MFA_CODE_LENGTH : 6;
    }

    public static function ttlSeconds(): int
    {
        return defined('MFA_TTL_SECONDS') ? (int) MFA_TTL_SECONDS : 900;
    }

    public static function ttlMinutes(): int
    {
        return max(1, (int) ceil(self::ttlSeconds() / 60));
    }

    public static function pending(): ?array
    {
        $pending = $_SESSION['mfa_pending'] ?? null;
        return is_array($pending) ? $pending : null;
    }

    public static function sent(array $pending): bool
    {
        return (string) ($pending['code_hash'] ?? '') !== '';
    }

    public static function maskEmail(string $email): string
    {
        $parts = explode('@', $email, 2);
        if (count($parts) !== 2 || $parts[0] === '') {
            return '등록된 이메일';
        }
        $name = $parts[0];
        return substr($name, 0, 1) . str_repeat('*', max(strlen($name) - 1, 1)) . '@' . $parts[1];
    }

    public static function begin(array $admin): void
    {
        AuthSession::cancel();
        AuthSession::rotate();
        $_SESSION['mfa_pending'] = [
            'no' => (int) $admin['no'],
            'uid' => (string) $admin['uid'],
            'uname' => (string) $admin['uname'],
            'role_id' => (int) ($admin['role_id'] ?? 3),
            'registered_email' => '',
            'email' => '',
            'code_plain' => '',
            'code_hash' => '',
            'expires' => 0,
            'attempts' => 0,
            'sent_at' => 0,
        ];
    }

    public static function bindEmail(string $inputEmail): array
    {
        $pending = self::pending();
        if ($pending === null) {
            throw new RuntimeException('인증 세션이 없습니다. 다시 로그인하세요.');
        }
        return self::issue(array_merge($pending, ['email' => $inputEmail]));
    }

    public static function issue(array $admin): array
    {
        $email = EmailValidator::assert((string) ($admin['email'] ?? ''));

        $lockPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'nol-mfa-' . preg_replace('/[^a-zA-Z0-9,-]/', '', (string) session_id()) . '.lock';
        $lock = fopen($lockPath, 'c');
        if ($lock !== false) {
            flock($lock, LOCK_EX);
        }

        try {
            if (session_status() === PHP_SESSION_ACTIVE) {
                session_write_close();
            }
            session_start();

            $length = self::codeLength();
            $ttl = self::ttlSeconds();
            $pending = self::pending() ?? [];
            $reuse = (string) ($pending['code_plain'] ?? '');
            if ($reuse === '' || (int) ($pending['expires'] ?? 0) < time()) {
                $min = $length > 1 ? 10 ** ($length - 1) : 0;
                $reuse = (string) random_int($min, (10 ** $length) - 1);
                $pending['expires'] = time() + $ttl;
                $pending['attempts'] = 0;
            }
            $alreadySent = $reuse !== '' && (int) ($pending['sent_at'] ?? 0) > 0 && (time() - (int) $pending['sent_at']) < 15;

            $_SESSION['mfa_pending'] = array_merge($pending, [
                'no' => (int) ($admin['no'] ?? ($pending['no'] ?? 0)),
                'uid' => (string) ($admin['uid'] ?? ($pending['uid'] ?? '')),
                'uname' => (string) ($admin['uname'] ?? ($pending['uname'] ?? '')),
                'role_id' => (int) ($admin['role_id'] ?? ($pending['role_id'] ?? 3)),
                'registered_email' => (string) ($pending['registered_email'] ?? ''),
                'email' => $email,
                'code_plain' => $reuse,
                'code_hash' => hash('sha256', $reuse),
                'expires' => (int) ($pending['expires'] ?? (time() + $ttl)),
                'sent_at' => time(),
            ]);

            $minutes = self::ttlMinutes();
            $outbox = [
                'to' => $email,
                'subject' => '관리자 로그인 인증번호 ' . date('H:i:s'),
                'body' => "관리자 로그인 인증번호는 {$reuse} 입니다.\n{$minutes}분 안에 입력해 주세요.",
                'skip' => $alreadySent,
            ];
            session_write_close();
        } finally {
            if ($lock !== false) {
                flock($lock, LOCK_UN);
                fclose($lock);
            }
        }

        self::deliver($outbox);
        return $outbox;
    }

    public static function resend(): void
    {
        $pending = self::pending();
        if ($pending === null) {
            throw new RuntimeException('인증 세션이 없습니다. 다시 로그인하세요.');
        }
        if (!self::sent($pending)) {
            throw new RuntimeException('이메일을 먼저 입력하세요.');
        }
        $sentAt = (int) ($pending['sent_at'] ?? 0);
        if ($sentAt > 0 && (time() - $sentAt) < 30) {
            throw new RuntimeException('잠시 후 다시 요청하세요.');
        }
        self::issue($pending);
    }

    public static function verify(string $inputCode): void
    {
        $pending = self::pending();
        if ($pending === null) {
            throw new RuntimeException('인증 세션이 없습니다. 다시 로그인하세요.');
        }
        if (!self::sent($pending)) {
            throw new RuntimeException('이메일을 먼저 입력하세요.');
        }
        if ((int) ($pending['expires'] ?? 0) < time()) {
            self::clear();
            throw new RuntimeException('인증번호가 만료되었습니다. 다시 로그인하세요.');
        }

        $code = self::normalizeCode($inputCode);
        $hash = (string) ($pending['code_hash'] ?? '');
        if ($code === '' || !self::codeMatches($code, $hash)) {
            self::log('verify-fail', 'in_len=' . strlen($code) . ' attempts=' . (int) ($pending['attempts'] ?? 0));
            $_SESSION['mfa_pending']['attempts'] = (int) ($pending['attempts'] ?? 0) + 1;
            $max = defined('MFA_MAX_ATTEMPTS') ? (int) MFA_MAX_ATTEMPTS : 5;
            if ($_SESSION['mfa_pending']['attempts'] >= $max) {
                self::clear();
                throw new RuntimeException('인증 실패 횟수를 초과했습니다. 다시 로그인하세요.');
            }
            throw new RuntimeException('인증번호가 올바르지 않습니다.');
        }

        self::complete($pending);
    }

    public static function complete(array $pending): void
    {
        require_once __DIR__ . '/AccountIdleLock.php';
        $id = (int) ($pending['no'] ?? 0);
        $row = AccountModel::findByNo($id);
        if ($row === []) {
            AuthSession::forgetMfa();
            throw new RuntimeException('계정을 찾을 수 없습니다. 다시 로그인하세요.');
        }
        if (($row['active_status'] ?? '') === 'N') {
            AuthSession::forgetMfa();
            throw new RuntimeException('사용이 중지된 계정입니다.');
        }
        try {
            (new AccountIdleLock())->guard($row);
        } catch (RuntimeException $e) {
            AuthSession::forgetMfa();
            throw $e;
        }

        global $admin_roles;
        $roleId = (int) ($row['role_id'] ?? 3);
        $roleCode = $admin_roles[$roleId]['code'] ?? 'guest';
        AuthSession::forgetMfa();
        AuthSession::establish($row, $roleCode);
    }

    public static function clear(): void
    {
        AuthSession::forgetMfa();
    }

    private static function deliver(array $outbox): void
    {
        if (!empty($outbox['skip'])) {
            return;
        }
        SmtpMailer::send((string) $outbox['to'], (string) $outbox['subject'], (string) $outbox['body']);
        self::log('issued', 'to=' . $outbox['to']);
    }

    private static function normalizeCode(string $code): string
    {
        $digits = preg_replace('/\D+/', '', $code);
        return is_string($digits) ? $digits : '';
    }

    private static function codeMatches(string $code, string $hash): bool
    {
        if ($code === '' || $hash === '') {
            return false;
        }
        if (strpos($hash, '$2y$') === 0 || strpos($hash, '$2a$') === 0) {
            return password_verify($code, $hash);
        }
        return hash_equals($hash, hash('sha256', $code));
    }

    private static function log(string $step, string $detail): void
    {
        $line = date('c') . ' [mfa] sid=' . session_id() . ' ' . $step . ' ' . $detail;
        error_log($line);
        $dir = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'storage';
        if (!is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }
        @file_put_contents($dir . DIRECTORY_SEPARATOR . 'mfa-smtp.log', $line . PHP_EOL, FILE_APPEND | LOCK_EX);
    }
}
