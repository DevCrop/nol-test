<?php
namespace Security;

final class SmtpMailTransport implements MailTransport
{
    public function send(string $to, array $message): void
    {
        $user = (string) blue_env('SMTP_USER', ''); $pass = (string) blue_env('SMTP_PASS', '');
        if ($user === '' || $pass === '') {
            // Only isolated CLI QA may omit delivery; browser requests fail closed.
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
        $mail->addAddress($to); $mail->Subject = $message['subject'];
        $mail->isHTML(true); $mail->Body = $message['html']; $mail->AltBody = $message['text'];
        $mail->send();
    }
}
