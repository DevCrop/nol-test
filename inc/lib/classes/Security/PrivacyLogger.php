<?php
namespace Security;

final class PrivacyLogger
{
    public static function record(string $action, string $entity, int $targetNo, string $subject, string $task, string $reason = ''): void
    {
        if (!in_array($action, ['view', 'download'], true) || ($action === 'download' && trim($reason) === '')) return;
        try {
            $stmt = \DB::getInstance()->prepare('INSERT INTO nb_admin_privacy_access (sitekey, actor_no, actor_uid, actor_ip, action, entity, target_no, subject_label, task, reason, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())');
            $stmt->execute(['BLUESQ', (int) ($_SESSION['no_adm_login_no'] ?? 0), (string) ($_SESSION['no_adm_login_uid'] ?? ''), ClientIp::get(), $action, substr($entity, 0, 64), $targetNo, PiiMask::name($subject), mb_substr($task, 0, 255), mb_substr($reason, 0, 500)]);
        } catch (\Throwable $e) {
            error_log('[privacy] write failed');
            http_response_code(503);
            throw new \RuntimeException('개인정보 접근 기록을 저장할 수 없습니다.');
        }
    }
}
