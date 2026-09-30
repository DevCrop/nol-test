<?php
namespace Security;

final class Mfa
{
    public static function begin(array $row): void
    {
        AdminAccount::assertLoginAllowed($row);
        $email = filter_var((string) ($row['email'] ?? ''), FILTER_VALIDATE_EMAIL);
        if (!$email) throw new \RuntimeException('관리자 계정에 인증용 이메일이 등록되어 있지 않습니다.');
        foreach (array_keys($_SESSION) as $key) if (strpos($key, 'no_adm_') === 0) unset($_SESSION[$key]);
        unset($_SESSION['mfa_pending']);
        if (!headers_sent()) session_regenerate_id(true);
        $pending = [
            'admin_no' => (int) $row['no'], 'code_hash' => '',
            'expires' => time() + MFA_TTL_SECONDS, 'attempts' => 0, 'sent_at' => 0, 'email' => $email,
            'credential_version' => hash('sha256', (string) $row['upwd']),
        ];
        $_SESSION['mfa_pending'] = $pending;
    }

    // NOL의 begin → 이메일 입력 → issue 흐름. 수신처는 계정 이메일에 바인딩한다.
    public static function bindEmail(string $input): void
    {
        $p = $_SESSION['mfa_pending'] ?? null;
        if (!is_array($p)) throw new \RuntimeException('인증 세션이 없습니다. 다시 로그인하세요.');
        if (!empty($p['code_hash'])) throw new \RuntimeException('인증번호 다시 받기를 이용하세요.');
        if (!filter_var(trim($input), FILTER_VALIDATE_EMAIL) || strcasecmp(trim($input), (string) $p['email']) !== 0) {
            $_SESSION['mfa_pending']['email_attempts'] = (int) ($p['email_attempts'] ?? 0) + 1;
            if ($_SESSION['mfa_pending']['email_attempts'] >= MFA_MAX_ATTEMPTS) unset($_SESSION['mfa_pending']);
            throw new \RuntimeException('계정에 등록된 이메일을 입력하세요.');
        }
        self::issue();
    }

    private static function issue(): void
    {
        $p = $_SESSION['mfa_pending'] ?? null;
        if (!is_array($p) || (int) $p['expires'] <= time()) { unset($_SESSION['mfa_pending']); throw new \RuntimeException('인증번호가 만료되었습니다. 다시 로그인하세요.'); }
        if (time() - (int) $p['sent_at'] < MFA_RESEND_SECONDS) throw new \RuntimeException('잠시 후 다시 요청하세요.');
        $row = AdminAccount::findByNo((int) $p['admin_no']);
        if (!$row || !hash_equals((string) ($p['credential_version'] ?? ''), hash('sha256', (string) $row['upwd'])) || $p['email'] !== $row['email']) { unset($_SESSION['mfa_pending']); throw new \RuntimeException('계정 정보가 변경되었습니다. 다시 로그인하세요.'); }
        AdminAccount::assertLoginAllowed($row);
        $code = (string) random_int(100000, 999999);
        self::deliver((string) $row['email'], $code);
        $_SESSION['mfa_pending']['code_hash'] = password_hash($code, PASSWORD_DEFAULT);
        $_SESSION['mfa_pending']['sent_at'] = time();
        // 재발송으로 만료 시간이나 검증 횟수 예산을 초기화하지 않는다.
    }

    public static function verify(string $code): array
    {
        $p = $_SESSION['mfa_pending'] ?? null;
        if (!is_array($p) || (int) ($p['expires'] ?? 0) <= time()) { unset($_SESSION['mfa_pending']); throw new \RuntimeException('인증번호가 만료되었습니다.'); }
        if (!preg_match('/^[0-9]{6}$/D', $code) || !password_verify($code, (string) $p['code_hash'])) {
            $_SESSION['mfa_pending']['attempts'] = (int) $p['attempts'] + 1;
            if ($_SESSION['mfa_pending']['attempts'] >= MFA_MAX_ATTEMPTS) unset($_SESSION['mfa_pending']);
            throw new \RuntimeException('인증번호가 올바르지 않습니다.');
        }
        $row = AdminAccount::findByNo((int) $p['admin_no']); unset($_SESSION['mfa_pending']);
        if (!$row) throw new \RuntimeException('계정을 찾을 수 없습니다.');
        AdminAccount::assertLoginAllowed($row);
        if (!hash_equals((string) ($p['credential_version'] ?? ''), hash('sha256', (string) $row['upwd'])) || $p['email'] !== $row['email']) throw new \RuntimeException('계정 정보가 변경되었습니다. 다시 로그인하세요.');
        return $row;
    }

    public static function resend(): void
    {
        $p = $_SESSION['mfa_pending'] ?? null;
        if (!is_array($p) || empty($p['code_hash'])) throw new \RuntimeException('이메일을 먼저 입력하세요.');
        self::issue();
    }

    public static function maskedEmail(): string
    {
        $email = (string) ($_SESSION['mfa_pending']['email'] ?? '');
        return PiiMask::email($email);
    }

    private static function deliver(string $to, string $code): void
    {
        $user = (string) blue_env('SMTP_USER', ''); $pass = (string) blue_env('SMTP_PASS', '');
        if ($user === '' || $pass === '') {
            // Offline QA may exercise challenge state without external delivery.
            // Browser requests must never silently bypass a missing SMTP setup.
            if (APP_ENV === 'development' && PHP_SAPI === 'cli' && is_file('/.dockerenv')) return;
            throw new \RuntimeException('인증 메일 설정이 완료되지 않았습니다.');
        }
        $root = dirname(__DIR__, 2) . '/PHPMailer_new/src/';
        require_once $root . 'Exception.php'; require_once $root . 'PHPMailer.php'; require_once $root . 'SMTP.php';
        $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
        $mail->isSMTP(); $mail->Host = (string) blue_env('SMTP_HOST', 'smtp.gmail.com'); $mail->Port = (int) blue_env('SMTP_PORT', 587);
        $mail->SMTPAuth = true; $mail->Username = $user; $mail->Password = $pass;
        $mail->SMTPSecure = $mail->Port === 465
            ? \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS
            : \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        $mail->CharSet = 'UTF-8'; $mail->setFrom((string) blue_env('SMTP_FROM', $user), (string) blue_env('SMTP_FROM_NAME', 'Blue Square'));
        $message = MfaEmail::compose('블루스퀘어', $code, (int) $_SESSION['mfa_pending']['expires']);
        $mail->addAddress($to); $mail->Subject = $message['subject'];
        $mail->isHTML(true); $mail->Body = $message['html']; $mail->AltBody = $message['text'];
        $mail->send();
    }
}
