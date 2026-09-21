<?php
namespace Security;

final class AuditLogger
{
    private static $capturing = false;
    public static function isCapturing(): bool { return self::$capturing; }

    public static function captureRequest(): void
    {
        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST' || empty($_SESSION['no_adm_login_uid'])) return;
        $path = strtolower((string) ($_SERVER['SCRIPT_NAME'] ?? ''));
        if (strpos($path, '/admin/pages/') !== 0 || strpos($path, '/account/ajax/password.process.php') !== false || strpos($path, '/account/process.php') !== false) return;
        self::$capturing = true;
    }

    public static function record(string $action, string $entity, int $targetNo = 0, string $label = '', array $detail = []): void
    {
        if (!in_array($action, ['create', 'update', 'delete'], true)) return;
        foreach (['password','upwd','token','session','code','email','phone','contents'] as $key) unset($detail[$key]);
        try {
            $stmt = \DB::getInstance()->prepare('INSERT INTO nb_admin_audit (sitekey, actor_no, actor_uid, actor_ip, action, entity, target_no, target_label, detail_json, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())');
            $stmt->execute(['BLUESQ', (int) ($_SESSION['no_adm_login_no'] ?? 0), (string) ($_SESSION['no_adm_login_uid'] ?? ''), ClientIp::get(), $action, substr($entity, 0, 64), $targetNo, mb_substr($label, 0, 255), $detail ? json_encode($detail, JSON_UNESCAPED_UNICODE) : null]);
        } catch (\Throwable $e) { error_log('[audit] write failed'); }
    }
}
