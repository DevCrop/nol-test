<?php
namespace Security;

final class PrivacyLogger
{
    public static function record(string $action, string $entity, int $targetNo, string $subject, string $task, string $reason = ''): int
    {
        if (!in_array($action, ['view', 'download', 'create', 'update', 'delete'], true) || ($action === 'download' && trim($reason) === '')) return 0;
        try {
            $db = \DB::getInstance();
            $stmt = $db->prepare('INSERT INTO nb_admin_privacy_access (sitekey, actor_no, actor_uid, actor_ip, action, entity, target_no, subject_label, task, reason, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())');
            $stmt->execute(['BLUESQ', (int) ($_SESSION['no_adm_login_no'] ?? 0), (string) ($_SESSION['no_adm_login_uid'] ?? '비회원'), ClientIp::get(), $action, substr($entity, 0, 64), max(0, $targetNo), PiiMask::name($subject), mb_substr($task, 0, 255), mb_substr($reason, 0, 500)]);
            return (int) $db->lastInsertId();
        } catch (\Throwable $e) {
            error_log('[privacy] write failed');
            http_response_code(503);
            throw new \RuntimeException('개인정보 접근 기록을 저장할 수 없습니다.');
        }
    }

    /** Finish a reserved public-create log inside the caller's transaction. */
    public static function attachTarget(int $logNo, int $targetNo): void
    {
        $db = \DB::getInstance();
        if ($logNo < 1 || $targetNo < 1 || !$db->inTransaction()) throw new \RuntimeException('개인정보 처리 기록을 연결하지 못했습니다.');
        $stmt = $db->prepare("UPDATE nb_admin_privacy_access SET target_no=? WHERE no=? AND sitekey='BLUESQ' AND entity='request' AND action='create' AND target_no=0");
        $stmt->execute([$targetNo, $logNo]);
        if ($stmt->rowCount() !== 1) throw new \RuntimeException('개인정보 처리 기록을 연결하지 못했습니다.');
    }
}
