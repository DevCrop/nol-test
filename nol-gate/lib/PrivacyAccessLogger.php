<?php

require_once dirname(__DIR__) . '/Model/PrivacyAccessModel.php';
require_once __DIR__ . '/AdminClock.php';
require_once __DIR__ . '/PiiMask.php';

class PrivacyAccessLogger
{
    public const ACTIONS = ['view', 'download'];

    /**
     * 개인정보 출력 전에 호출. 기록을 보장하지 못하면 공개를 중단한다.
     */
    public static function record(string $action, string $entity, $targetNo, string $subject, string $task, string $reason = ''): void
    {
        try {
            if (!in_array($action, self::ACTIONS, true)) {
                return;
            }
            global $NO_SITE_UNIQUE_KEY;
            $recorded = PrivacyAccessModel::insert([
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
            if (!$recorded) throw new RuntimeException('Privacy log insert failed');
        } catch (Throwable $e) {
            error_log('[privacy-access] recording failed');
            http_response_code(503);
            throw new RuntimeException('개인정보 열람 기록을 저장할 수 없습니다. 잠시 후 다시 시도하세요.');
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
