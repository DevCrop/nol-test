<?php

require_once dirname(__DIR__) . '/Model/PrivacyAccessModel.php';
require_once __DIR__ . '/AdminClock.php';
require_once __DIR__ . '/PiiMask.php';

class PrivacyAccessLogger
{
    public const ACTIONS = ['view', 'download'];

    /**
     * 열람/다운로드 성공 후 호출. 실패해도 본 작업은 막지 않는다.
     */
    public static function record(string $action, string $entity, $targetNo, string $subject, string $task, string $reason = ''): void
    {
        try {
            if (!in_array($action, self::ACTIONS, true)) {
                return;
            }
            global $NO_SITE_UNIQUE_KEY;
            PrivacyAccessModel::insert([
                'sitekey' => (string) $NO_SITE_UNIQUE_KEY,
                'actor_no' => (int) ($_SESSION['no_adm_login_no'] ?? 0),
                'actor_uid' => (string) ($_SESSION['no_adm_login_uid'] ?? ''),
                'actor_ip' => (string) ($_SERVER['REMOTE_ADDR'] ?? ''),
                'action' => $action,
                'entity' => $entity,
                'target_no' => (int) $targetNo,
                'subject_label' => self::clip(PiiMask::name($subject), 255),
                'task' => self::clip($task, 255),
                'reason' => self::clip($reason, 500),
                'created_at' => (new AdminClock())->stamp(),
            ]);
        } catch (Throwable $e) {
            error_log('[privacy-access] ' . $e->getMessage());
        }
    }

    public static function actionLabel(string $action): string
    {
        $map = ['view' => '열람', 'download' => '다운로드'];
        return $map[$action] ?? $action;
    }

    private static function clip(string $s, int $max): string
    {
        if (function_exists('mb_substr')) {
            return mb_substr($s, 0, $max);
        }
        return substr($s, 0, $max);
    }
}
