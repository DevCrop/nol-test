<?php
require_once __DIR__ . '/AuthSession.php';
require_once __DIR__ . '/SmtpMailer.php';
require_once __DIR__ . '/MfaEmail.php';

class Mfa
{
    public static function enabled(): bool { return defined('MFA_ENABLED') && MFA_ENABLED === true; }
    public static function codeLength(): int { return 6; }
    public static function ttlSeconds(): int { return defined('MFA_TTL_SECONDS') ? max(60, (int) MFA_TTL_SECONDS) : 900; }
    public static function ttlMinutes(): int { return max(1, (int) ceil(self::ttlSeconds() / 60)); }
    public static function pending(): ?array { $p = $_SESSION['mfa_pending'] ?? null; return is_array($p) ? $p : null; }
    public static function sent(array $p): bool { return !empty($p['code_hash']); }
    public static function maskEmail(string $email): string
    {
        require_once __DIR__ . '/PiiMask.php';
        return PiiMask::email($email);
    }
    public static function begin(array $admin): void
    {
        AuthSession::cancel();
        $email = trim((string) ($admin['email'] ?? ''));
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) throw new RuntimeException('관리자 계정에 인증 이메일을 등록한 후 다시 로그인하세요.');
        AuthSession::rotate();
        $_SESSION['mfa_pending'] = [
            'no' => (int) $admin['no'], 'uid' => (string) $admin['uid'],
            'registered_email' => $email, 'email' => $email,
            'credential_version' => hash('sha256', (string) $admin['upwd']),
            'code_hash' => '', 'expires' => time() + self::ttlSeconds(),
            'attempts' => 0, 'email_attempts' => 0, 'sent_at' => 0,
        ];
    }
    public static function bindEmail(string $inputEmail): array
    {
        $p = self::requirePending();
        if (self::sent($p)) throw new RuntimeException('인증번호 다시 받기를 이용하세요.');
        if (!filter_var(trim($inputEmail), FILTER_VALIDATE_EMAIL) || strcasecmp(trim($inputEmail), $p['registered_email']) !== 0) {
            $_SESSION['mfa_pending']['email_attempts'] = (int) ($p['email_attempts'] ?? 0) + 1;
            if ($_SESSION['mfa_pending']['email_attempts'] >= self::maxAttempts()) self::clear();
            throw new RuntimeException('계정에 등록된 이메일을 입력하세요.');
        }
        return static::issue($p);
    }
    private static function requirePending(): array
    {
        $p = self::pending();
        if ($p === null || (int) ($p['expires'] ?? 0) <= time()) {
            self::clear();
            throw new RuntimeException('인증 세션이 만료되었습니다. 다시 로그인하세요.');
        }
        return $p;
    }
    private static function currentAccount(array $p): array
    {
        $row = AccountModel::authenticationRow((int) ($p['no'] ?? 0));
        if (!$row || ($row['active_status'] ?? '') !== 'Y'
            || (string) ($row['sitekey'] ?? '') !== (string) ($GLOBALS['NO_SITE_UNIQUE_KEY'] ?? '')
            || !hash_equals((string) ($p['credential_version'] ?? ''), hash('sha256', (string) $row['upwd']))
            || (string) ($p['registered_email'] ?? '') !== trim((string) $row['email'])) {
            self::clear();
            throw new RuntimeException('계정 정보가 변경되었습니다. 다시 로그인하세요.');
        }
        try { (new AccountIdleLock())->guard($row); }
        catch (RuntimeException $e) { self::clear(); throw $e; }
        return $row;
    }
    // Compatibility signature retained; authoritative state is always the server session.
    public static function issue(array $unused = []): array
    {
        $p = self::requirePending();
        if (time() - (int) $p['sent_at'] < 30) throw new RuntimeException('잠시 후 다시 요청하세요.');
        $row = self::currentAccount($p);
        $code = (string) random_int(100000, 999999);
        $message = MfaEmail::compose('놀씨어터', $code, (int) $p['expires']);
        $outbox = ['to' => trim((string) $row['email']), 'subject' => $message['subject'],
            'body' => $message['text'], 'html' => $message['html'], 'skip' => false];
        // Keep the session lock through delivery: parallel resends cannot bypass cooldown.
        static::deliver($outbox);
        $_SESSION['mfa_pending']['code_hash'] = password_hash($code, PASSWORD_DEFAULT);
        $_SESSION['mfa_pending']['sent_at'] = time();
        // Resends never reset expiry or attempts; plaintext codes are not persisted.
        return $outbox;
    }
    public static function resend(): void
    {
        $p = self::requirePending();
        if (!self::sent($p)) throw new RuntimeException('이메일을 먼저 입력하세요.');
        static::issue();
    }
    private static function maxAttempts(): int { return defined('MFA_MAX_ATTEMPTS') ? max(1, (int) MFA_MAX_ATTEMPTS) : 5; }
    public static function verify(string $inputCode): void
    {
        $p = self::requirePending();
        if (!self::sent($p)) throw new RuntimeException('이메일을 먼저 입력하세요.');
        if (!preg_match('/^[0-9]{6}$/D', $inputCode) || !password_verify($inputCode, $p['code_hash'])) {
            $_SESSION['mfa_pending']['attempts'] = (int) $p['attempts'] + 1;
            if ($_SESSION['mfa_pending']['attempts'] >= self::maxAttempts()) self::clear();
            throw new RuntimeException('인증번호가 올바르지 않습니다.');
        }
        self::complete($p);
    }
    private static function complete(array $p): void
    {
        $row = self::currentAccount($p);
        global $admin_roles;
        self::clear();
        AuthSession::establish($row, $admin_roles[(int) $row['role_id']]['code'] ?? 'guest');
    }
    public static function clear(): void { AuthSession::forgetMfa(); }
    protected static function deliver(array $outbox): void
    {
        SmtpMailer::send($outbox['to'], $outbox['subject'], $outbox['body'], $outbox['html'] ?? null);
    }
}
